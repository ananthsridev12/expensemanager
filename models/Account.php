<?php

namespace Models;

class Account extends BaseModel
{
    public function getAllWithBalances(): array
    {
        $sql = <<<SQL
SELECT
    a.*,
    COALESCE(a.opening_balance + SUM(CASE
        WHEN t.transaction_type = 'income' THEN t.amount
        WHEN t.transaction_type = 'expense' THEN -t.amount
        ELSE 0
    END), a.opening_balance) AS balance,
    COALESCE(SUM(CASE WHEN t.transaction_type = 'income' THEN t.amount ELSE 0 END), 0) AS total_income,
    COALESCE(SUM(CASE WHEN t.transaction_type = 'expense' THEN t.amount ELSE 0 END), 0) AS total_expense
FROM accounts a
LEFT JOIN transactions t ON t.account_id = a.id
GROUP BY a.id
ORDER BY a.created_at DESC
SQL;
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function create(array $input): bool
    {
        $sql = 'INSERT INTO accounts (bank_name, account_name, account_number, ifsc, opening_balance) VALUES (:bank_name, :account_name, :account_number, :ifsc, :opening_balance)';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':bank_name' => trim($input['bank_name'] ?? ''),
            ':account_name' => trim($input['account_name'] ?? ''),
            ':account_number' => $input['account_number'] ? trim($input['account_number']) : null,
            ':ifsc' => $input['ifsc'] ? trim($input['ifsc']) : null,
            ':opening_balance' => is_numeric($input['opening_balance'] ?? null) ? (float) $input['opening_balance'] : 0.0,
        ]);
    }

    public function getSummary(): array
    {
        $accounts = $this->getAllWithBalances();
        $totalBalance = array_sum(array_column($accounts, 'balance'));

        return [
            'count' => count($accounts),
            'total_balance' => $totalBalance,
        ];
    }

    public function getList(): array
    {
        $stmt = $this->db->query('SELECT id, bank_name, account_name, "bank" AS account_type FROM accounts ORDER BY created_at DESC');
        $accounts = $stmt->fetchAll();

        $cardStmt = $this->db->query('SELECT id, bank_name, card_name AS account_name, "credit_card" AS account_type FROM credit_cards ORDER BY created_at DESC');
        $cards = $cardStmt->fetchAll();

        return array_merge($accounts, $cards);
    }
}
