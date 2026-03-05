<?php

namespace Models;

class Account extends BaseModel
{
    public function getAllWithBalances(): array
    {
        $sql = <<<SQL
SELECT
    a.*,
    cc.id AS credit_card_id,
    cc.credit_limit,
    cc.outstanding_balance,
    cc.outstanding_principal,
    COALESCE(a.opening_balance + SUM(CASE
        WHEN t.transaction_type = 'income' THEN t.amount
        WHEN t.transaction_type = 'expense' THEN -t.amount
        ELSE 0
    END), a.opening_balance) AS balance,
    COALESCE(SUM(CASE WHEN t.transaction_type = 'income' THEN t.amount ELSE 0 END), 0) AS total_income,
    COALESCE(SUM(CASE WHEN t.transaction_type = 'expense' THEN t.amount ELSE 0 END), 0) AS total_expense
FROM accounts a
LEFT JOIN transactions t ON t.account_id = a.id
LEFT JOIN credit_cards cc ON cc.account_id = a.id
GROUP BY a.id
ORDER BY a.created_at DESC
SQL;
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function create(array $input): bool
    {
        $accountType = trim((string) ($input['account_type'] ?? 'savings'));
        $allowedTypes = ['savings', 'current', 'credit_card', 'cash', 'other'];
        if (!in_array($accountType, $allowedTypes, true)) {
            $accountType = 'savings';
        }

        $this->db->beginTransaction();
        try {
            $sql = 'INSERT INTO accounts (bank_name, account_name, account_type, account_number, ifsc, opening_balance) VALUES (:bank_name, :account_name, :account_type, :account_number, :ifsc, :opening_balance)';
            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                ':bank_name' => trim((string) ($input['bank_name'] ?? '')),
                ':account_name' => trim((string) ($input['account_name'] ?? '')),
                ':account_type' => $accountType,
                ':account_number' => !empty($input['account_number']) ? trim((string) $input['account_number']) : null,
                ':ifsc' => !empty($input['ifsc']) ? trim((string) $input['ifsc']) : null,
                ':opening_balance' => is_numeric($input['opening_balance'] ?? null) ? (float) $input['opening_balance'] : 0.0,
            ]);

            $accountId = (int) $this->db->lastInsertId();

            if ($accountType === 'credit_card') {
                $cardSql = 'INSERT INTO credit_cards (account_id, bank_name, card_name, credit_limit, billing_date, due_date, outstanding_balance, outstanding_principal, interest_rate, tenure_months, processing_fee, gst_rate, emi_amount, emi_start_date)
                            VALUES (:account_id, :bank_name, :card_name, :credit_limit, :billing_date, :due_date, :outstanding_balance, :outstanding_principal, :interest_rate, :tenure_months, :processing_fee, :gst_rate, :emi_amount, :emi_start_date)';
                $cardStmt = $this->db->prepare($cardSql);
                $cardStmt->execute([
                    ':account_id' => $accountId,
                    ':bank_name' => trim((string) ($input['bank_name'] ?? '')),
                    ':card_name' => !empty($input['card_name']) ? trim((string) $input['card_name']) : trim((string) ($input['account_name'] ?? '')),
                    ':credit_limit' => is_numeric($input['credit_limit'] ?? null) ? (float) $input['credit_limit'] : 0.0,
                    ':billing_date' => (int) ($input['billing_date'] ?? 1),
                    ':due_date' => (int) ($input['due_date'] ?? 1),
                    ':outstanding_balance' => is_numeric($input['outstanding_balance'] ?? null) ? (float) $input['outstanding_balance'] : 0.0,
                    ':outstanding_principal' => is_numeric($input['outstanding_principal'] ?? null) ? (float) $input['outstanding_principal'] : 0.0,
                    ':interest_rate' => is_numeric($input['interest_rate'] ?? null) ? (float) $input['interest_rate'] : 0.0,
                    ':tenure_months' => (int) ($input['tenure_months'] ?? 0),
                    ':processing_fee' => is_numeric($input['processing_fee'] ?? null) ? (float) $input['processing_fee'] : 0.0,
                    ':gst_rate' => is_numeric($input['gst_rate'] ?? null) ? (float) $input['gst_rate'] : 0.0,
                    ':emi_amount' => is_numeric($input['emi_amount'] ?? null) ? (float) $input['emi_amount'] : 0.0,
                    ':emi_start_date' => !empty($input['emi_start_date']) ? $input['emi_start_date'] : null,
                ]);
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getSummary(): array
    {
        $accounts = array_filter(
            $this->getAllWithBalances(),
            fn (array $row): bool => ($row['account_type'] ?? 'savings') !== 'credit_card'
        );
        $totalBalance = array_sum(array_column($accounts, 'balance'));

        return [
            'count' => count($accounts),
            'total_balance' => $totalBalance,
        ];
    }

    public function getList(): array
    {
        $stmt = $this->db->query('SELECT id, bank_name, account_name, account_type FROM accounts ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }
}
