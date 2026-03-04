<?php

namespace Models;

use PDO;

class Lending extends BaseModel
{
    public function create(array $input): bool
    {
        $contactId = $this->createContact($input);
        $principal = (float) ($input['principal_amount'] ?? 0);
        $interestRate = (float) ($input['interest_rate'] ?? 0);
        $lendingDate = $input['lending_date'] ?? date('Y-m-d');
        $dueDate = $input['due_date'] ?? null;
        $totalRepaid = (float) ($input['total_repaid'] ?? 0);
        $outstanding = $principal - $totalRepaid;

        $sql = 'INSERT INTO lending_records (contact_id, principal_amount, interest_rate, lending_date, due_date, total_repaid, outstanding_amount, status, notes) VALUES (:contact_id, :principal_amount, :interest_rate, :lending_date, :due_date, :total_repaid, :outstanding_amount, :status, :notes)';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':contact_id' => $contactId,
            ':principal_amount' => $principal,
            ':interest_rate' => $interestRate,
            ':lending_date' => $lendingDate,
            ':due_date' => $dueDate,
            ':total_repaid' => $totalRepaid,
            ':outstanding_amount' => max(0, $outstanding),
            ':status' => $input['status'] ?? 'ongoing',
            ':notes' => $input['notes'] ?? null,
        ]);
    }

    public function createContact(array $input): int
    {
        $sql = 'INSERT INTO contacts (name, mobile, email, address, city, state, notes) VALUES (:name, :mobile, :email, :address, :city, :state, :notes)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name' => trim($input['contact_name'] ?? 'Unknown'),
            ':mobile' => $input['contact_mobile'] ?? null,
            ':email' => $input['contact_email'] ?? null,
            ':address' => $input['contact_address'] ?? null,
            ':city' => $input['contact_city'] ?? null,
            ':state' => $input['contact_state'] ?? null,
            ':notes' => $input['contact_notes'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getAll(): array
    {
        $sql = <<<SQL
SELECT
    lr.*, c.name AS contact_name, c.mobile, c.email
FROM lending_records lr
JOIN contacts c ON c.id = lr.contact_id
ORDER BY lr.lending_date DESC
SQL;
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSummary(): array
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS total_records, COALESCE(SUM(outstanding_amount),0) AS total_outstanding FROM lending_records');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'count' => (int) $row['total_records'],
            'outstanding' => (float) $row['total_outstanding'],
        ];
    }
}
