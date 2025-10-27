<?php
require_once __DIR__ . '/../settings/db_class.php';

class Brand extends Db
{
    public function add(string $name): ?int {
        $sql = "INSERT INTO brands (brand_name) VALUES (:name)";
        $stmt = $this->pdo()->prepare($sql);
        try {
            $stmt->execute([':name'=>$name]);
            return (int)$this->pdo()->lastInsertId();
        } catch (PDOException $e) {
            return null; 
        }
    }

    public function update(int $id, string $name): bool {
        $sql = "UPDATE brands SET brand_name=:name WHERE brand_id=:id";
        $stmt = $this->pdo()->prepare($sql);
        return $stmt->execute([':name'=>$name, ':id'=>$id]);
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo()->prepare("DELETE FROM brands WHERE brand_id=:id");
        return $stmt->execute([':id'=>$id]);
    }

    public function listAll(): array {
        $stmt = $this->pdo()->query("SELECT brand_id, brand_name FROM brands ORDER BY brand_name ASC");
        return $stmt->fetchAll();
    }

    public function existsByName(string $name): bool {
        $stmt = $this->pdo()->prepare("SELECT 1 FROM brands WHERE brand_name=:n LIMIT 1");
        $stmt->execute([':n'=>$name]);
        return (bool)$stmt->fetchColumn();
    }
}
