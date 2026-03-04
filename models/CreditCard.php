<?php

namespace Models;

use PDO;

class CreditCard extends BaseModel
{
    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM credit_cards ORDER BY created_at DESC');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $input): bool
    {
        $sql = 'INSERT INTO credit_cards (bank_name, card_name, credit_limit, billing_date, due_date, outstanding_balance) VALUES (:bank_name, :card_name, :credit_limit, :billing_date, :due_date, :outstanding_balance)';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':bank_name' => trim($input['bank_name'] ?? ''),
            ':card_name' => trim($input['card_name'] ?? ''),
            ':credit_limit' => (float) ($input['credit_limit'] ?? 0),
            ':billing_date' => (int) ($input['billing_date'] ?? 1),
            ':due_date' => (int) ($input['due_date'] ?? 1),
            ':outstanding_balance' => (float) ($input['outstanding_balance'] ?? 0),
        ]);
    }

    public function getSummary(): array
    {
        $sql = 'SELECT COUNT(*) AS count_cards, COALESCE(SUM(credit_limit), 0) AS total_limit, COALESCE(SUM(outstanding_balance), 0) AS total_outstanding FROM credit_cards';
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'count' => (int) $row['count_cards'],
            'total_limit' => (float) $row['total_limit'],
            'total_outstanding' => (float) $row['total_outstanding'],
        ];
    }
}
