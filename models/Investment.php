<?php

namespace Models;

use PDO;

class Investment extends BaseModel
{
    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM investments ORDER BY created_at DESC');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $input): bool
    {
        $sql = 'INSERT INTO investments (type, name, notes) VALUES (:type, :name, :notes)';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':type' => $input['type'] ?? 'mutual_fund',
            ':name' => trim($input['name'] ?? 'Untitled'),
            ':notes' => $input['notes'] ?? null,
        ]);
    }

    public function createTransaction(array $input): bool
    {
        $sql = 'INSERT INTO investment_transactions (investment_id, transaction_type, amount, units, transaction_date, account_id, notes) VALUES (:investment_id, :transaction_type, :amount, :units, :transaction_date, :account_id, :notes)';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':investment_id' => (int) ($input['investment_id'] ?? 0),
            ':transaction_type' => $input['transaction_type'] ?? 'buy',
            ':amount' => (float) ($input['amount'] ?? 0),
            ':units' => (float) ($input['units'] ?? 0),
            ':transaction_date' => $input['transaction_date'] ?? date('Y-m-d'),
            ':account_id' => !empty($input['account_id']) ? (int) $input['account_id'] : null,
            ':notes' => $input['notes'] ?? null,
        ]);
    }

    public function getRecentTransactions(int $limit = 10): array
    {
        $sql = <<<SQL
SELECT
    it.*, i.name AS investment_name, a.account_name
FROM investment_transactions it
LEFT JOIN investments i ON i.id = it.investment_id
LEFT JOIN accounts a ON a.id = it.account_id
ORDER BY it.transaction_date DESC, it.created_at DESC
LIMIT :limit
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSummary(): array
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS total_investments FROM investments');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'count' => (int) $row['total_investments'],
        ];
    }
}
