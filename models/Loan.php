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
        $gst = (float) ($input['gst'] ?? 0);
        $startDate = $input['start_date'] ?? date('Y-m-d');

        $emiAmount = $this->calculateMonthlyEmi($principal, $interestRate, $tenure);

        $sql = 'INSERT INTO loans (loan_type, loan_name, principal_amount, interest_rate, tenure_months, emi_amount, processing_fee, gst, start_date, outstanding_principal) VALUES (:loan_type, :loan_name, :principal_amount, :interest_rate, :tenure_months, :emi_amount, :processing_fee, :gst, :start_date, :outstanding_principal)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':loan_type' => $input['loan_type'] ?? 'personal',
            ':loan_name' => trim($input['loan_name'] ?? 'Untitled Loan'),
            ':principal_amount' => $principal,
            ':interest_rate' => $interestRate,
            ':tenure_months' => $tenure,
            ':emi_amount' => $emiAmount,
            ':processing_fee' => $processingFee,
            ':gst' => $gst,
            ':start_date' => $startDate,
            ':outstanding_principal' => $principal,
        ]);

        $loanId = (int) $this->db->lastInsertId();
        $this->createEmiSchedule($loanId, $principal, $interestRate, $tenure, $emiAmount, $startDate);

        return $loanId;
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

    private function createEmiSchedule(int $loanId, float $principal, float $annualRate, int $tenure, float $emiAmount, string $startDate): void
    {
        if ($tenure <= 0) {
            return;
        }

        $monthlyRate = $annualRate / 12 / 100;
        $balance = $principal;
        $date = new DateTime($startDate);

        $stmt = $this->db->prepare('INSERT INTO loan_emi_schedule (loan_id, emi_date, principal_component, interest_component, status) VALUES (:loan_id, :emi_date, :principal_component, :interest_component, :status)');

        for ($month = 1; $month <= $tenure; $month++) {
            $interestComponent = $balance * $monthlyRate;
            $principalComponent = $emiAmount - $interestComponent;

            if ($month === $tenure) {
                $principalComponent = $balance;
                $emiAmount = $principalComponent + $interestComponent;
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
