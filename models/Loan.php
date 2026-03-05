<?php

namespace Models;

use DateInterval;
use DateTime;
use PDO;

class Loan extends BaseModel
{
    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM loans ORDER BY start_date DESC');

        return $stmt->fetchAll();
    }

    public function getUpcomingEmis(int $limit = 5): array
    {
        $sql = <<<SQL
SELECT
    s.*, l.loan_name, l.interest_rate
FROM loan_emi_schedule s
JOIN loans l ON l.id = s.loan_id
WHERE s.status != 'paid'
ORDER BY s.emi_date ASC
LIMIT :limit
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $input): ?int
    {
        $principal = max(0, (float) ($input['principal_amount'] ?? 0));
        $interestRate = (float) ($input['interest_rate'] ?? 0);
        $tenure = max(1, (int) ($input['tenure_months'] ?? 12));
        $processingFee = (float) ($input['processing_fee'] ?? 0);
        $gstRate = (float) ($input['gst'] ?? 0);
        $startDate = $input['start_date'] ?? date('Y-m-d');
        $repaymentType = ($input['repayment_type'] ?? 'emi') === 'interest_only' ? 'interest_only' : 'emi';

        $emiAmount = $repaymentType === 'interest_only'
            ? $this->calculateMonthlyInterestOnlyAmount($principal, $interestRate)
            : $this->calculateMonthlyEmi($principal, $interestRate, $tenure);

        $sql = 'INSERT INTO loans (loan_type, loan_name, principal_amount, interest_rate, tenure_months, emi_amount, processing_fee, gst, repayment_type, start_date, outstanding_principal) VALUES (:loan_type, :loan_name, :principal_amount, :interest_rate, :tenure_months, :emi_amount, :processing_fee, :gst, :repayment_type, :start_date, :outstanding_principal)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':loan_type' => $input['loan_type'] ?? 'personal',
            ':loan_name' => trim($input['loan_name'] ?? 'Untitled Loan'),
            ':principal_amount' => $principal,
            ':interest_rate' => $interestRate,
            ':tenure_months' => $tenure,
            ':emi_amount' => $emiAmount,
            ':processing_fee' => $processingFee,
            ':gst' => $gstRate,
            ':repayment_type' => $repaymentType,
            ':start_date' => $startDate,
            ':outstanding_principal' => $principal,
        ]);

        $loanId = (int) $this->db->lastInsertId();
        $this->createEmiSchedule($loanId, $principal, $interestRate, $tenure, $emiAmount, $startDate, $repaymentType, $processingFee, $gstRate);
        $this->createDisbursementTransfer($loanId, trim($input['loan_name'] ?? 'Untitled Loan'), $principal, $startDate, (string) ($input['disbursement_account'] ?? ''));

        return $loanId;
    }

    public function applyTransactionMovement(int $loanId, string $transactionType, float $amount): void
    {
        if ($loanId <= 0 || $amount <= 0) {
            return;
        }

        if ($transactionType === 'expense') {
            $stmt = $this->db->prepare(
                'UPDATE loans
                 SET outstanding_principal = outstanding_principal + :amount,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :loan_id'
            );
            $stmt->execute([
                ':amount' => $amount,
                ':loan_id' => $loanId,
            ]);
            return;
        }

        if ($transactionType === 'income') {
            $stmt = $this->db->prepare(
                'UPDATE loans
                 SET outstanding_principal = GREATEST(0, outstanding_principal - :amount),
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :loan_id'
            );
            $stmt->execute([
                ':amount' => $amount,
                ':loan_id' => $loanId,
            ]);
        }
    }

    private function createDisbursementTransfer(
        int $loanId,
        string $loanName,
        float $principal,
        string $transferDate,
        string $accountToken
    ): void {
        if ($loanId <= 0 || $principal <= 0 || $accountToken === '' || strpos($accountToken, ':') === false) {
            return;
        }

        [$accountType, $accountIdRaw] = explode(':', $accountToken, 2);
        $accountId = (int) $accountIdRaw;
        $allowedTypes = ['savings', 'current', 'cash', 'other'];

        if ($accountId <= 0 || !in_array($accountType, $allowedTypes, true)) {
            return;
        }

        $notes = 'Loan disbursal - ' . $loanName;
        $stmt = $this->db->prepare(
            'INSERT INTO transactions (transaction_date, account_type, account_id, transaction_type, amount, reference_type, reference_id, notes)
             VALUES (:transaction_date, :account_type, :account_id, :transaction_type, :amount, :reference_type, :reference_id, :notes)'
        );

        // Loan ledger side (debt increases).
        $stmt->execute([
            ':transaction_date' => $transferDate,
            ':account_type' => 'loan',
            ':account_id' => null,
            ':transaction_type' => 'expense',
            ':amount' => $principal,
            ':reference_type' => 'loan',
            ':reference_id' => $loanId,
            ':notes' => $notes,
        ]);

        // Destination account side (cash/bank increases).
        $stmt->execute([
            ':transaction_date' => $transferDate,
            ':account_type' => $accountType,
            ':account_id' => $accountId,
            ':transaction_type' => 'income',
            ':amount' => $principal,
            ':reference_type' => 'loan',
            ':reference_id' => $loanId,
            ':notes' => $notes,
        ]);
    }

    private function calculateMonthlyEmi(float $principal, float $annualRate, int $months): float
    {
        if ($principal <= 0 || $months <= 0) {
            return 0.0;
        }

        $monthlyRate = $annualRate / 12 / 100;
        if ($monthlyRate == 0.0) {
            return $principal / $months;
        }

        $numerator = $principal * $monthlyRate * pow(1 + $monthlyRate, $months);
        $denominator = pow(1 + $monthlyRate, $months) - 1;

        return $denominator > 0 ? $numerator / $denominator : 0.0;
    }

    private function calculateMonthlyInterestOnlyAmount(float $principal, float $annualRate): float
    {
        if ($principal <= 0) {
            return 0.0;
        }

        return $principal * ($annualRate / 12 / 100);
    }

    private function createEmiSchedule(
        int $loanId,
        float $principal,
        float $annualRate,
        int $tenure,
        float $emiAmount,
        string $startDate,
        string $repaymentType,
        float $processingFee,
        float $gstRate
    ): void
    {
        if ($tenure <= 0) {
            return;
        }

        $monthlyRate = $annualRate / 12 / 100;
        $processingFeeGst = round($processingFee * ($gstRate / 100), 2);
        $balance = $principal;
        $date = new DateTime($startDate);

        $stmt = $this->db->prepare('INSERT INTO loan_emi_schedule (loan_id, emi_date, principal_component, interest_component, status) VALUES (:loan_id, :emi_date, :principal_component, :interest_component, :status)');

        for ($month = 1; $month <= $tenure; $month++) {
            if ($repaymentType === 'interest_only') {
                $interestComponent = $principal * $monthlyRate;
                $principalComponent = $month === $tenure ? $balance : 0.0;
            } else {
                $interestComponent = $balance * $monthlyRate;
                $principalComponent = $emiAmount - $interestComponent;
                if ($month === $tenure) {
                    $principalComponent = $balance;
                }
            }

            if ($month === 1 && ($processingFee > 0 || $processingFeeGst > 0)) {
                // Processing fee and GST are one-time charges, not monthly interest GST.
                $interestComponent += $processingFee + $processingFeeGst;
            }

            $emiDate = $date->format('Y-m-d');
            $stmt->execute([
                ':loan_id' => $loanId,
                ':emi_date' => $emiDate,
                ':principal_component' => max(0, $principalComponent),
                ':interest_component' => max(0, $interestComponent),
                ':status' => 'pending',
            ]);

            $balance -= $principalComponent;
            $date->add(new DateInterval('P1M'));
        }
    }
}
