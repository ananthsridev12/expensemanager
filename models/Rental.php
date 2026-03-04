<?php

namespace Models;

use PDO;

class Rental extends BaseModel
{
    public function createProperty(array $input): bool
    {
        $sql = 'INSERT INTO properties (property_name, address, monthly_rent, security_deposit) VALUES (:property_name, :address, :monthly_rent, :security_deposit)';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':property_name' => trim($input['property_name'] ?? 'Untitled Property'),
            ':address' => $input['address'] ?? null,
            ':monthly_rent' => (float) ($input['monthly_rent'] ?? 0),
            ':security_deposit' => (float) ($input['security_deposit'] ?? 0),
        ]);
    }

    public function createTenant(array $input): bool
    {
        $sql = 'INSERT INTO tenants (name, mobile, email, id_proof, address) VALUES (:name, :mobile, :email, :id_proof, :address)';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':name' => trim($input['tenant_name'] ?? 'Tenant'),
            ':mobile' => $input['tenant_mobile'] ?? null,
            ':email' => $input['tenant_email'] ?? null,
            ':id_proof' => $input['tenant_id_proof'] ?? null,
            ':address' => $input['tenant_address'] ?? null,
        ]);
    }

    public function createContract(array $input): bool
    {
        $sql = 'INSERT INTO rental_contracts (property_id, tenant_id, start_date, end_date, deposit_amount, rent_amount) VALUES (:property_id, :tenant_id, :start_date, :end_date, :deposit_amount, :rent_amount)';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':property_id' => (int) ($input['property_id'] ?? 0),
            ':tenant_id' => (int) ($input['tenant_id'] ?? 0),
            ':start_date' => $input['start_date'] ?? date('Y-m-d'),
            ':end_date' => $input['end_date'] ?? null,
            ':deposit_amount' => (float) ($input['deposit_amount'] ?? 0),
            ':rent_amount' => (float) ($input['rent_amount'] ?? 0),
        ]);
    }

    public function recordPayment(array $input): bool
    {
        $sql = 'INSERT INTO rental_transactions (contract_id, rent_month, due_date, paid_amount, payment_status, notes) VALUES (:contract_id, :rent_month, :due_date, :paid_amount, :payment_status, :notes)';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':contract_id' => (int) ($input['contract_id'] ?? 0),
            ':rent_month' => $input['rent_month'] ?? date('Y-m-01'),
            ':due_date' => $input['due_date'] ?? date('Y-m-d'),
            ':paid_amount' => (float) ($input['paid_amount'] ?? 0),
            ':payment_status' => $input['payment_status'] ?? 'pending',
            ':notes' => $input['notes'] ?? null,
        ]);
    }

    public function getProperties(): array
    {
        $stmt = $this->db->query('SELECT * FROM properties ORDER BY created_at DESC');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTenants(): array
    {
        $stmt = $this->db->query('SELECT * FROM tenants ORDER BY created_at DESC');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getContracts(): array
    {
        $sql = <<<SQL
SELECT
    rc.*, p.property_name, t.name AS tenant_name
FROM rental_contracts rc
LEFT JOIN properties p ON p.id = rc.property_id
LEFT JOIN tenants t ON t.id = rc.tenant_id
ORDER BY rc.start_date DESC
SQL;
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTransactions(): array
    {
        $sql = <<<SQL
SELECT
    rt.*, rc.property_id, p.property_name, t.name AS tenant_name
FROM rental_transactions rt
LEFT JOIN rental_contracts rc ON rc.id = rt.contract_id
LEFT JOIN properties p ON p.id = rc.property_id
LEFT JOIN tenants t ON t.id = rc.tenant_id
ORDER BY rt.due_date DESC
SQL;
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSummary(): array
    {
        $sql = 'SELECT (SELECT COUNT(*) FROM properties) AS properties, (SELECT COUNT(*) FROM tenants) AS tenants, (SELECT COUNT(*) FROM rental_contracts) AS contracts';
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'properties' => (int) $row['properties'],
            'tenants' => (int) $row['tenants'],
            'contracts' => (int) $row['contracts'],
        ];
    }

    public function getUpcomingRent(int $limit = 5): array
    {
        $sql = <<<SQL
SELECT
    rt.*, p.property_name, t.name AS tenant_name
FROM rental_transactions rt
LEFT JOIN rental_contracts rc ON rc.id = rt.contract_id
LEFT JOIN properties p ON p.id = rc.property_id
LEFT JOIN tenants t ON t.id = rc.tenant_id
WHERE rt.payment_status IN ('pending','partial','overdue')
ORDER BY rt.due_date ASC
LIMIT :limit
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
