<?php

namespace Models;

class Category extends BaseModel
{
    public function getAllWithSubcategories(): array
    {
        $sql = <<<SQL
SELECT
    c.id AS category_id,
    c.name AS category_name,
    c.type AS category_type,
    c.created_at AS category_created_at,
    sc.id AS sub_id,
    sc.name AS sub_name,
    sc.created_at AS sub_created_at
FROM categories c
LEFT JOIN subcategories sc ON sc.category_id = c.id
ORDER BY c.created_at DESC, sc.created_at ASC
SQL;
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            if (!isset($result[$row['category_id']])) {
                $result[$row['category_id']] = [
                    'id' => $row['category_id'],
                    'name' => $row['category_name'],
                    'type' => $row['category_type'],
                    'created_at' => $row['category_created_at'],
                    'subcategories' => [],
                ];
            }

            if ($row['sub_id']) {
                $result[$row['category_id']]['subcategories'][] = [
                    'id' => $row['sub_id'],
                    'name' => $row['sub_name'],
                    'created_at' => $row['sub_created_at'],
                ];
            }
        }

        return array_values($result);
    }

    public function getCategoryList(): array
    {
        $stmt = $this->db->query('SELECT id, name FROM categories ORDER BY created_at DESC');

        return $stmt->fetchAll();
    }

    public function createCategory(string $name, string $type): bool
    {
        $sql = 'INSERT INTO categories (name, type) VALUES (:name, :type)';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':name' => trim($name),
            ':type' => $type,
        ]);
    }

    public function createSubcategory(int $categoryId, string $name): bool
    {
        $sql = 'INSERT INTO subcategories (category_id, name) VALUES (:category_id, :name)';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':category_id' => $categoryId,
            ':name' => trim($name),
        ]);
    }

    public function count(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM categories');

        return (int) $stmt->fetchColumn();
    }
}
