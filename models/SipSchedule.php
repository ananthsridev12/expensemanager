<?php

namespace Models;

use PDO;

class SipSchedule extends BaseModel
{
    public function create(array $input): bool
    {
        $sql = 'INSERT INTO sip_schedules (investment_id, account_id, sip_amount, sip_day, frequency, start_date, end_date, next_run_date, status) VALUES (:investment_id, :account_id, :sip_amount, :sip_day, :frequency, :start_date, :end_date, :next_run_date, :status)';
        $stmt = $this->db->prepare($sql);

        $startDate = $input['start_date'] ?? date('Y-m-d');

        return $stmt->execute([
            ':investment_id' => (int) ($input['investment_id'] ?? 0),
            ':account_id' => (int) ($input['account_id'] ?? 0),
            ':sip_amount' => (float) ($input['sip_amount'] ?? 0),
            ':sip_day' => (int) ($input['sip_day'] ?? 1),
            ':frequency' => $input['frequency'] ?? 'monthly',
            ':start_date' => $startDate,
            ':end_date' => $input['end_date'] ?? null,
            ':next_run_date' => $input['next_run_date'] ?? $startDate,
            ':status' => $input['status'] ?? 'active',
        ]);
    }

    public function getAll(): array
    {
        $sql = <<<SQL
SELECT
    s.*, i.name AS investment_name, a.account_name
FROM sip_schedules s
LEFT JOIN investments i ON i.id = s.investment_id
LEFT JOIN accounts a ON a.id = s.account_id
ORDER BY s.created_at DESC
SQL;
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUpcoming(int $limit = 5): array
    {
        $sql = <<<SQL
SELECT
    s.*, i.name AS investment_name
FROM sip_schedules s
LEFT JOIN investments i ON i.id = s.investment_id
WHERE s.status = 'active' AND s.next_run_date >= CURDATE()
ORDER BY s.next_run_date ASC
LIMIT :limit
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
