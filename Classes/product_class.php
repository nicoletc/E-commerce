<?php
// Classes/product_class.php
require_once __DIR__ . '/../settings/db_class.php'; // class Db (PDO wrapper)

class product_class extends Db{

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


    // Return one product (with category/brand names) or null if not found
public function view_single_product(int $id): ?array
{
    $sql = "SELECT p.*, c.cat_name, b.brand_name
              FROM products p
         LEFT JOIN categories c ON c.cat_id   = p.product_cat
         LEFT JOIN brands     b ON b.brand_id = p.product_brand
             WHERE p.product_id = :id
             LIMIT 1";
    $st = $this->pdo()->prepare($sql);
    $st->bindValue(':id', $id, \PDO::PARAM_INT);
    $st->execute();
    $row = $st->fetch(\PDO::FETCH_ASSOC);
    return $row !== false ? $row : null;
}



    public function view_all_products(array $opts = []): array
    {
        // normalize options (use same names as the view: q, cat, brand, min_price, max_price, page, limit)
        $q         = trim((string)($opts['q'] ?? ''));
        $cat       = $opts['cat'] ?? '';
        $brand     = $opts['brand'] ?? '';
        $minPrice  = isset($opts['min_price']) && $opts['min_price'] !== '' ? (float)$opts['min_price'] : null;
        $maxPrice  = isset($opts['max_price']) && $opts['max_price'] !== '' ? (float)$opts['max_price'] : null;
        $page      = max(1, (int)($opts['page'] ?? 1));
        $limit     = max(0, (int)($opts['limit'] ?? 24));
        $offset    = ($page - 1) * $limit;

        $sql = "SELECT p.*, c.cat_name, b.brand_name
                  FROM products p
             LEFT JOIN categories c ON c.cat_id   = p.product_cat
             LEFT JOIN brands     b ON b.brand_id = p.product_brand
                 WHERE 1=1";
        $count_sql = "SELECT COUNT(*) FROM products p
             LEFT JOIN categories c ON c.cat_id   = p.product_cat
             LEFT JOIN brands     b ON b.brand_id = p.product_brand
                 WHERE 1=1";

        $params = [];

        if ($cat !== '') {
            $sql .= " AND p.product_cat = :cat";
            $count_sql .= " AND p.product_cat = :cat";
            $params['cat'] = (int)$cat;
        }
        if ($brand !== '') {
            $sql .= " AND p.product_brand = :brand";
            $count_sql .= " AND p.product_brand = :brand";
            $params['brand'] = (int)$brand;
        }
        if ($minPrice !== null) {
            $sql .= " AND p.product_price >= :min_price";
            $count_sql .= " AND p.product_price >= :min_price";
            $params['min_price'] = $minPrice;
        }
        if ($maxPrice !== null) {
            $sql .= " AND p.product_price <= :max_price";
            $count_sql .= " AND p.product_price <= :max_price";
            $params['max_price'] = $maxPrice;
        }
        if ($q !== '') {
            $sql .= " AND (p.product_title LIKE :q1 OR p.product_keywords LIKE :q2)";
            $count_sql .= " AND (p.product_title LIKE :q1 OR p.product_keywords LIKE :q2)";
            $like = '%' . $q . '%';
            $params['q1'] = $like;
            $params['q2'] = $like;
        }

        // total count
        $st = $this->pdo()->prepare($count_sql);
        foreach ($params as $k => $v) {
            $type = is_int($v) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $st->bindValue(':' . $k, $v, $type);
        }
        $st->execute();
        $total = (int)$st->fetchColumn();

        // items
        $sql .= " ORDER BY p.product_id DESC";
        if ($limit > 0) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $st = $this->pdo()->prepare($sql);
        foreach ($params as $k => $v) {
            $type = is_int($v) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $st->bindValue(':' . $k, $v, $type);
        }
        if ($limit > 0) {
            $st->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
            $st->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);
        }

        $st->execute();
        $items = $st->fetchAll(\PDO::FETCH_ASSOC);

        $pages = ($limit > 0) ? (int)ceil($total / $limit) : 1;

        return [
            'items' => $items,
            'total' => $total,
            'pages' => $pages,
            'page'  => $page,
            'limit' => $limit,
        ];
    }
}