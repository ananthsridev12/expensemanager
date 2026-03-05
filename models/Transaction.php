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
    CASE
        WHEN t.account_type = 'loan' THEN 'Loan'
        WHEN t.account_type = 'lending' THEN 'Lending'
        WHEN t.account_type = 'rental' THEN 'Rental'
        WHEN a.account_type = 'credit_card' THEN COALESCE(cc.bank_name, a.bank_name)
        ELSE a.bank_name
    END AS bank_name,
    CASE
        WHEN t.account_type = 'loan' THEN l.loan_name
        WHEN t.account_type = 'lending' THEN ct.name
        WHEN t.account_type = 'rental' THEN CONCAT(COALESCE(rt_tenant.name, 'Tenant'), ' / ', COALESCE(rt_property.property_name, 'Property'))
        WHEN a.account_type = 'credit_card' THEN COALESCE(cc.card_name, a.account_name)
        ELSE a.account_name
    END AS account_name,
    c.name AS category_name,
    sc.name AS subcategory_name
FROM transactions t
LEFT JOIN categories c ON c.id = t.category_id
LEFT JOIN subcategories sc ON sc.id = t.subcategory_id
LEFT JOIN accounts a ON a.id = t.account_id
LEFT JOIN credit_cards cc ON cc.account_id = a.id
LEFT JOIN loans l ON l.id = CASE
    WHEN t.account_type = 'loan' AND t.reference_type = 'loan' THEN t.reference_id
    ELSE NULL
END
LEFT JOIN lending_records lr ON lr.id = CASE
    WHEN t.reference_type = 'lending' THEN t.reference_id
    ELSE NULL
END
LEFT JOIN contacts ct ON ct.id = lr.contact_id
LEFT JOIN rental_transactions rt ON rt.id = CASE
    WHEN t.reference_type = 'rental' THEN t.reference_id
    ELSE NULL
END
LEFT JOIN rental_contracts rtc ON rtc.id = rt.contract_id
LEFT JOIN tenants rt_tenant ON rt_tenant.id = rtc.tenant_id
LEFT JOIN properties rt_property ON rt_property.id = rtc.property_id
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
