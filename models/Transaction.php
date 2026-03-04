<?php

namespace Models;

use PDO;

class Transaction extends BaseModel
{
    public function countAll(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM transactions');

        return (int) $stmt->fetchColumn();
    }

    public function getRecent(int $limit = 5): array
    {
        $sql = <<<SQL
SELECT
    t.*, 
    c.name AS category_name,
    sc.name AS subcategory_name
FROM transactions t
LEFT JOIN categories c ON c.id = t.category_id
LEFT JOIN subcategories sc ON sc.id = t.subcategory_id
ORDER BY t.transaction_date DESC, t.created_at DESC
LIMIT :limit
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function create(array $input): bool
    {
        $sql = 'INSERT INTO transactions (transaction_date, account_type, account_id, transaction_type, category_id, subcategory_id, amount, reference_type, reference_id, notes) VALUES (:transaction_date, :account_type, :account_id, :transaction_type, :category_id, :subcategory_id, :amount, :reference_type, :reference_id, :notes)';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':transaction_date' => $input['transaction_date'] ?? date('Y-m-d'),
            ':account_type' => $input['account_type'] ?? 'bank',
            ':account_id' => !empty($input['account_id']) ? (int) $input['account_id'] : null,
            ':transaction_type' => $input['transaction_type'] ?? 'expense',
            ':category_id' => !empty($input['category_id']) ? (int) $input['category_id'] : null,
            ':subcategory_id' => !empty($input['subcategory_id']) ? (int) $input['subcategory_id'] : null,
            ':amount' => is_numeric($input['amount'] ?? null) ? (float) $input['amount'] : 0.00,
            ':reference_type' => $input['reference_type'] ?? null,
            ':reference_id' => !empty($input['reference_id']) ? (int) $input['reference_id'] : null,
            ':notes' => $input['notes'] ?? null,
        ]);
    }

    public function getTotalsByType(): array
    {
        $stmt = $this->db->query('SELECT transaction_type, SUM(amount) AS total FROM transactions GROUP BY transaction_type');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[$row['transaction_type']] = (float) $row['total'];
        }

        return $result;
    }
}
