<?php
require_once __DIR__ . '/../settings/db_class.php';


class cart_class extends Db
{
  public function __construct()
  {
    parent::__construct();
  }
  // Add or increment
  public function add_item(int $c_id, int $p_id, int $qty = 1, string $ip = '0.0.0.0')
  {
    // if customer id provided, operate by c_id
    if ($c_id > 0) {
      $check = $this->db->prepare("SELECT qty FROM cart WHERE c_id = ? AND p_id = ?");
      $check->execute([$c_id, $p_id]);
      $row = $check->fetch(PDO::FETCH_ASSOC);

      if ($row) {
        $upd = $this->db->prepare("UPDATE cart SET qty = qty + ? WHERE c_id = ? AND p_id = ?");
        return $upd->execute([$qty, $c_id, $p_id]);
      }

  $ins = $this->db->prepare("INSERT INTO cart (p_id, ip_add, c_id, qty) VALUES (?, ?, ?, ?)");
  // some schemas mark ip_add NOT NULL; store empty string for authenticated rows
  return $ins->execute([$p_id, '', $c_id, $qty]);
    }

    // guest token path: use ip_add as token
    $token = $ip;
    $check = $this->db->prepare("SELECT qty FROM cart WHERE ip_add = ? AND p_id = ?");
    $check->execute([$token, $p_id]);
    $row = $check->fetch(PDO::FETCH_ASSOC);
    if ($row) {
      $upd = $this->db->prepare("UPDATE cart SET qty = qty + ? WHERE ip_add = ? AND p_id = ?");
      return $upd->execute([$qty, $token, $p_id]);
    }
    $ins = $this->db->prepare("INSERT INTO cart (p_id, ip_add, c_id, qty) VALUES (?, ?, ?, ?)");
    // use NULL for c_id for guest rows so foreign key constraint is not violated
    return $ins->execute([$p_id, $token, null, $qty]);
  }

  public function update_qty(int $c_id, int $p_id, int $qty)
  {
    $st = $this->db->prepare("UPDATE cart SET qty = ? WHERE c_id = ? AND p_id = ?");
    return $st->execute([$qty, $c_id, $p_id]);
  }

  public function remove_item(int $c_id, int $p_id)
  {
    $st = $this->db->prepare("DELETE FROM cart WHERE c_id = ? AND p_id = ?");
    $c_id = (int)$c_id;
    $p_id = (int)$p_id;
    return $st->execute([$c_id, $p_id]);
  }

  public function empty_cart(int $c_id)
  {
    $st = $this->db->prepare("DELETE FROM cart WHERE c_id = ?");
    return $st->execute([$c_id]);
  }

  public function get_cart_items(int $c_id)
  {
    $sql = "SELECT c.p_id, c.qty,
                   p.product_title, p.product_price, p.product_image
            FROM cart c
            JOIN products p ON p.product_id = c.p_id
            WHERE c.c_id = ?
            ORDER BY p.product_title";
    $st = $this->db->prepare($sql);
    $st->execute([$c_id]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public function count_items(int $c_id)
  {
    $st = $this->db->prepare("SELECT COALESCE(SUM(qty),0) AS cnt FROM cart WHERE c_id = ?");
    $st->execute([$c_id]);
    return (int)($st->fetchColumn() ?: 0);
  }

  // Count items for a guest token (ip_add column contains token for guests)
  public function count_items_by_token(string $token): int
  {
    $st = $this->db->prepare("SELECT COALESCE(SUM(qty),0) AS cnt FROM cart WHERE ip_add = ?");
    $st->execute([$token]);
    return (int)($st->fetchColumn() ?: 0);
  }

  // Get cart items by guest token (join to products)
  public function get_cart_items_by_token(string $token)
  {
    $sql = "SELECT c.p_id, c.qty,
                   p.product_title, p.product_price, p.product_image
            FROM cart c
            JOIN products p ON p.product_id = c.p_id
            WHERE c.ip_add = ?
            ORDER BY p.product_title";
    $st = $this->db->prepare($sql);
    $st->execute([$token]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public function update_qty_by_token(string $token, int $p_id, int $qty)
  {
    $st = $this->db->prepare("UPDATE cart SET qty = ? WHERE ip_add = ? AND p_id = ?");
    return $st->execute([$qty, $token, $p_id]);
  }

  public function remove_item_by_token(string $token, int $p_id)
  {
    $st = $this->db->prepare("DELETE FROM cart WHERE ip_add = ? AND p_id = ?");
    return $st->execute([$token, $p_id]);
  }

  public function empty_cart_by_token(string $token)
  {
    $st = $this->db->prepare("DELETE FROM cart WHERE ip_add = ?");
    return $st->execute([$token]);
  }




  /**
   * Migrate cart items for a guest token into a customer id.
   * If $token is null, attempt to use session cookie or leave as-is.
   */
  /**
   * Migrate cart items for a guest token into a customer id.
   * Returns true on success, false otherwise.
   */
  public function migrate_guest_to_user(int $c_id, ?string $token = null): bool
  {
    if (empty($token)) {
      // try cookie or nothing
      if (!empty($_COOKIE['cart_token'])) $token = $_COOKIE['cart_token'];
      if (empty($token)) return false;
    }
    // Some schemas mark ip_add NOT NULL; set it to empty string for migrated rows
    // (this matches how authenticated rows are inserted elsewhere).
    $sql   = "UPDATE cart SET c_id=?, ip_add='' WHERE ip_add=?";
    $st    = $this->db->prepare($sql);
    $ok = $st->execute([$c_id, $token]);
    if (!$ok) return false;
    // Only consider migration successful if at least one row was updated
    return ($st->rowCount() > 0);
  }
}
