<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use RuntimeException;

final class PostingService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function createTransaction(int $userId, array $payload): int
    {
        $txnType = (string) ($payload['txn_type'] ?? 'adjustment');
        $txnDate = (string) ($payload['txn_date'] ?? date('Y-m-d'));
        $description = (string) ($payload['description'] ?? '');
        $entries = $this->buildEntries($txnType, $payload);

        $this->assertBalanced($entries);
        $this->assertAccountsBelongToUser($userId, $entries);

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO transactions (user_id, txn_type, txn_date, description, external_ref) VALUES (:user_id, :txn_type, :txn_date, :description, :external_ref)'
            );

            $stmt->execute([
                'user_id' => $userId,
                'txn_type' => $txnType,
                'txn_date' => $txnDate,
                'description' => $description,
                'external_ref' => $payload['external_ref'] ?? null,
            ]);

            $transactionId = (int) $this->pdo->lastInsertId();

            $insertEntry = $this->pdo->prepare(
                'INSERT INTO journal_entries (transaction_id, user_id, account_id, category_id, side, amount, note)
                 VALUES (:transaction_id, :user_id, :account_id, :category_id, :side, :amount, :note)'
            );

            foreach ($entries as $entry) {
                $insertEntry->execute([
                    'transaction_id' => $transactionId,
                    'user_id' => $userId,
                    'account_id' => (int) $entry['account_id'],
                    'category_id' => $entry['category_id'] ?? null,
                    'side' => $entry['side'],
                    'amount' => number_format((float) $entry['amount'], 2, '.', ''),
                    'note' => $entry['note'] ?? null,
                ]);
            }

            $this->pdo->commit();
            return $transactionId;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function buildEntries(string $txnType, array $payload): array
    {
        return match ($txnType) {
            'income' => $this->incomeEntries($payload),
            'expense_cash_or_bank' => $this->expenseEntries($payload),
            'transfer' => $this->transferEntries($payload),
            'credit_card_purchase' => $this->cardPurchaseEntries($payload),
            'credit_card_payment' => $this->cardPaymentEntries($payload),
            'loan_disbursement' => $this->loanDisbursementEntries($payload),
            'loan_emi_payment', 'card_emi_payment' => $this->emiPaymentEntries($payload),
            'investment_buy' => $this->investmentBuyEntries($payload),
            'investment_income' => $this->investmentIncomeEntries($payload),
            'investment_redeem' => $this->investmentRedeemEntries($payload),
            'adjustment' => $this->manualEntries($payload),
            default => throw new RuntimeException("Unsupported txn_type: {$txnType}"),
        };
    }

    private function incomeEntries(array $payload): array
    {
        $amount = $this->requireAmount($payload, 'amount');
        return [
            $this->entry($payload['to_account_id'], 'DEBIT', $amount, $payload['category_id'] ?? null),
            $this->entry($payload['income_account_id'], 'CREDIT', $amount, $payload['category_id'] ?? null),
        ];
    }

    private function expenseEntries(array $payload): array
    {
        $amount = $this->requireAmount($payload, 'amount');
        return [
            $this->entry($payload['expense_account_id'], 'DEBIT', $amount, $payload['category_id'] ?? null),
            $this->entry($payload['from_account_id'], 'CREDIT', $amount, $payload['category_id'] ?? null),
        ];
    }

    private function transferEntries(array $payload): array
    {
        $amount = $this->requireAmount($payload, 'amount');
        return [
            $this->entry($payload['to_account_id'], 'DEBIT', $amount, null),
            $this->entry($payload['from_account_id'], 'CREDIT', $amount, null),
        ];
    }

    private function cardPurchaseEntries(array $payload): array
    {
        $amount = $this->requireAmount($payload, 'amount');
        return [
            $this->entry($payload['expense_or_asset_account_id'], 'DEBIT', $amount, $payload['category_id'] ?? null),
            $this->entry($payload['card_principal_account_id'], 'CREDIT', $amount, $payload['category_id'] ?? null),
        ];
    }

    private function cardPaymentEntries(array $payload): array
    {
        $amount = $this->requireAmount($payload, 'amount');
        return [
            $this->entry($payload['card_principal_account_id'], 'DEBIT', $amount, null),
            $this->entry($payload['bank_account_id'], 'CREDIT', $amount, null),
        ];
    }

    private function loanDisbursementEntries(array $payload): array
    {
        $amount = $this->requireAmount($payload, 'amount');
        return [
            $this->entry($payload['bank_account_id'], 'DEBIT', $amount, null),
            $this->entry($payload['loan_principal_account_id'], 'CREDIT', $amount, null),
        ];
    }

    private function emiPaymentEntries(array $payload): array
    {
        $principal = $this->amountOrZero($payload, 'principal_amount');
        $interest = $this->amountOrZero($payload, 'interest_amount');
        $gst = $this->amountOrZero($payload, 'gst_amount');
        $fees = $this->amountOrZero($payload, 'fees_amount');
        $total = $this->requireAmount($payload, 'total_amount');

        if (round($principal + $interest + $gst + $fees, 2) !== round($total, 2)) {
            throw new RuntimeException('EMI split mismatch: principal + interest + gst + fees must equal total_amount');
        }

        $entries = [];

        if ($principal > 0) {
            $entries[] = $this->entry($payload['principal_liability_account_id'], 'DEBIT', $principal, null, 'Principal component');
        }
        if ($interest > 0) {
            $entries[] = $this->entry($payload['interest_expense_account_id'], 'DEBIT', $interest, $payload['interest_category_id'] ?? null, 'Interest component');
        }
        if ($gst > 0) {
            $entries[] = $this->entry($payload['gst_expense_account_id'], 'DEBIT', $gst, $payload['gst_category_id'] ?? null, 'GST component');
        }
        if ($fees > 0) {
            $entries[] = $this->entry($payload['fees_expense_account_id'], 'DEBIT', $fees, $payload['fees_category_id'] ?? null, 'Fees component');
        }

        $entries[] = $this->entry($payload['payment_account_id'], 'CREDIT', $total, null, 'EMI paid');

        return $entries;
    }

    private function investmentBuyEntries(array $payload): array
    {
        $amount = $this->requireAmount($payload, 'amount');
        return [
            $this->entry($payload['investment_asset_account_id'], 'DEBIT', $amount, null),
            $this->entry($payload['bank_account_id'], 'CREDIT', $amount, null),
        ];
    }

    private function investmentIncomeEntries(array $payload): array
    {
        $amount = $this->requireAmount($payload, 'amount');
        return [
            $this->entry($payload['bank_account_id'], 'DEBIT', $amount, null),
            $this->entry($payload['investment_income_account_id'], 'CREDIT', $amount, $payload['category_id'] ?? null),
        ];
    }

    private function investmentRedeemEntries(array $payload): array
    {
        $amount = $this->requireAmount($payload, 'amount');
        return [
            $this->entry($payload['bank_account_id'], 'DEBIT', $amount, null),
            $this->entry($payload['investment_asset_account_id'], 'CREDIT', $amount, null),
        ];
    }

    private function manualEntries(array $payload): array
    {
        $entries = $payload['entries'] ?? null;
        if (!is_array($entries) || $entries === []) {
            throw new RuntimeException('Adjustment transaction requires non-empty entries array.');
        }

        return array_map(function (array $row): array {
            return $this->entry(
                $row['account_id'] ?? null,
                $row['side'] ?? null,
                $row['amount'] ?? null,
                $row['category_id'] ?? null,
                $row['note'] ?? null
            );
        }, $entries);
    }

    private function entry(mixed $accountId, mixed $side, mixed $amount, mixed $categoryId = null, ?string $note = null): array
    {
        if (!is_numeric($accountId) || (int) $accountId <= 0) {
            throw new RuntimeException('Invalid account_id in entry.');
        }

        $normalizedSide = strtoupper((string) $side);
        if (!in_array($normalizedSide, ['DEBIT', 'CREDIT'], true)) {
            throw new RuntimeException('Entry side must be DEBIT or CREDIT.');
        }

        if (!is_numeric($amount) || (float) $amount <= 0) {
            throw new RuntimeException('Entry amount must be > 0.');
        }

        return [
            'account_id' => (int) $accountId,
            'side' => $normalizedSide,
            'amount' => (float) $amount,
            'category_id' => is_numeric($categoryId) ? (int) $categoryId : null,
            'note' => $note,
        ];
    }

    private function requireAmount(array $payload, string $key): float
    {
        if (!isset($payload[$key]) || !is_numeric($payload[$key]) || (float) $payload[$key] <= 0) {
            throw new RuntimeException("{$key} must be a number > 0.");
        }

        return round((float) $payload[$key], 2);
    }

    private function amountOrZero(array $payload, string $key): float
    {
        if (!isset($payload[$key])) {
            return 0.0;
        }

        if (!is_numeric($payload[$key]) || (float) $payload[$key] < 0) {
            throw new RuntimeException("{$key} must be a number >= 0.");
        }

        return round((float) $payload[$key], 2);
    }

    private function assertBalanced(array $entries): void
    {
        $debit = 0.0;
        $credit = 0.0;

        foreach ($entries as $entry) {
            if ($entry['side'] === 'DEBIT') {
                $debit += (float) $entry['amount'];
            } else {
                $credit += (float) $entry['amount'];
            }
        }

        if (round($debit, 2) !== round($credit, 2)) {
            throw new RuntimeException('Journal is not balanced.');
        }
    }

    private function assertAccountsBelongToUser(int $userId, array $entries): void
    {
        $accountIds = array_values(array_unique(array_map(
            static fn(array $entry): int => (int) $entry['account_id'],
            $entries
        )));

        $placeholders = implode(',', array_fill(0, count($accountIds), '?'));
        $params = array_merge([$userId], $accountIds);

        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS cnt FROM accounts WHERE user_id = ? AND is_active = 1 AND id IN ({$placeholders})");
        $stmt->execute($params);
        $count = (int) $stmt->fetch()['cnt'];

        if ($count !== count($accountIds)) {
            throw new RuntimeException('One or more accounts are invalid, inactive, or belong to another user.');
        }
    }
}
