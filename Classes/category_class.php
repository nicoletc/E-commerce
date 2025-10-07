<?php

require_once __DIR__ . '/../settings/db_class.php';

class Category extends Db
{
    public function __construct() {
        parent::__construct(); 
    }

    public function add(string $name): ?int {
        $sql = "INSERT INTO categories (cat_name) VALUES (:name)";
        $stmt = $this->db->prepare($sql);
        $ok  = $stmt->execute([':name' => $name]);
        if ($ok) {
            return (int)$this->db->lastInsertId();
        }
        return null;
    }

    public function listAll(): array {
        $sql  = "SELECT cat_id, cat_name FROM categories ORDER BY cat_name ASC";
        $stmt = $this->db->query($sql);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function rename(int $id, string $name): bool {
        $sql  = "UPDATE categories SET cat_name = :name WHERE cat_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':name' => $name, ':id' => $id]);
    }


    public function remove(int $id): bool {
        $sql  = "DELETE FROM categories WHERE cat_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]) && $stmt->rowCount() > 0;
    }


    public function existsByName(string $name): bool {
        $sql  = "SELECT 1 FROM categories WHERE cat_name = :name LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':name' => $name]);
        return (bool)$stmt->fetchColumn();
    }
}
