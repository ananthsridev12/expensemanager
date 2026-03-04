<?php

namespace Models;

use PDO;

class Reminder extends BaseModel
{
    public function create(array $input): bool
    {
        $sql = 'INSERT INTO reminders (name, amount, frequency, next_due_date, status, notes) VALUES (:name, :amount, :frequency, :next_due_date, :status, :notes)';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':name' => trim($input['name'] ?? ''),
            ':amount' => is_numeric($input['amount'] ?? null) ? (float) $input['amount'] : null,
            ':frequency' => $input['frequency'] ?? 'monthly',
            ':next_due_date' => $input['next_due_date'] ?? date('Y-m-d'),
            ':status' => $input['status'] ?? 'upcoming',
            ':notes' => $input['notes'] ?? null,
        ]);
    }

    public function getUpcoming(int $limit = 10): array
    {
        $stmt = $this->db->prepare('SELECT * FROM reminders WHERE status != "completed" ORDER BY next_due_date ASC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function count(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM reminders');

        return (int) $stmt->fetchColumn();
    }
}
