<?php
// Classes/product_class.php
require_once __DIR__ . '/../settings/db_class.php'; // class Db (PDO wrapper)

class product_class extends Db
{
    /** Get all products with category/brand names */
    public function get_products(): array {
        $sql = "SELECT p.*, c.cat_name, b.brand_name
                  FROM products p
             LEFT JOIN categories c ON c.cat_id   = p.product_cat
             LEFT JOIN brands     b ON b.brand_id = p.product_brand
              ORDER BY p.product_id DESC";
        return $this->pdo()->query($sql)->fetchAll();
    }

    /** Get a single product */
    public function get_product(int $id): ?array {
        $st = $this->pdo()->prepare(
            "SELECT p.*, c.cat_name, b.brand_name
               FROM products p
          LEFT JOIN categories c ON c.cat_id   = p.product_cat
          LEFT JOIN brands     b ON b.brand_id = p.product_brand
              WHERE p.product_id = :id"
        );
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    /** CREATE */
    public function add_product(array $p): int {
        $sql = "INSERT INTO products
                (product_cat, product_brand, product_title, product_price,
                 product_desc, product_image, product_keywords)
                VALUES (:cat,:brand,:title,:price,:descr,:image,:kw)";
        $st = $this->pdo()->prepare($sql);
        $st->execute([
            ':cat'   => (int)$p['product_cat'],
            ':brand' => (int)$p['product_brand'],
            ':title' => $p['product_title'],
            ':price' => $p['product_price'],       // keep as string/decimal
            ':descr' => $p['product_desc'],
            ':image' => $p['product_image'],       // relative path or null
            ':kw'    => $p['product_keywords'],
        ]);
        return (int)$this->pdo()->lastInsertId();
    }

    /** UPDATE */
    public function update_product(array $p): bool {
        $sql = "UPDATE products
                   SET product_cat      = :cat,
                       product_brand    = :brand,
                       product_title    = :title,
                       product_price    = :price,
                       product_desc     = :descr,
                       product_image    = :image,
                       product_keywords = :kw
                 WHERE product_id      = :id";
        $st = $this->pdo()->prepare($sql);
        return $st->execute([
            ':cat'   => (int)$p['product_cat'],
            ':brand' => (int)$p['product_brand'],
            ':title' => $p['product_title'],
            ':price' => $p['product_price'],
            ':descr' => $p['product_desc'],
            ':image' => $p['product_image'],
            ':kw'    => $p['product_keywords'],
            ':id'    => (int)$p['product_id'],
        ]);
    }

    /** Dropdown helpers */
    public function get_categories(): array {
        return $this->pdo()
            ->query("SELECT cat_id, cat_name FROM categories ORDER BY cat_name")
            ->fetchAll();
    }
    public function get_brands(): array {
        return $this->pdo()
            ->query("SELECT brand_id, brand_name FROM brands ORDER BY brand_name")
            ->fetchAll();
    }
}
