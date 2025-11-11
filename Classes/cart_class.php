<?php
require_once __DIR__ . '/../settings/db_class.php';


class cart_class extends Db {

  // Add or increment
  public function add_item(int $c_id, int $p_id, int $qty = 1, string $ip = '0.0.0.0'){
    $db = $this->db_connect();

    // if line already exists, increment
    $check = $db->prepare("SELECT qty FROM cart WHERE c_id = ? AND p_id = ?");
    $check->execute([$c_id, $p_id]);
    $row = $check->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      $upd = $db->prepare("UPDATE cart SET qty = qty + ? WHERE c_id = ? AND p_id = ?");
      return $upd->execute([$qty, $c_id, $p_id]);
    }

    // create new row (ip_add is NOT NULL)
    $ins = $db->prepare("INSERT INTO cart (p_id, ip_add, c_id, qty) VALUES (?, ?, ?, ?)");
    return $ins->execute([$p_id, $ip, $c_id, $qty]);
  }

  public function update_qty(int $c_id, int $p_id, int $qty){
    $db = $this->db_connect();
    $st = $db->prepare("UPDATE cart SET qty = ? WHERE c_id = ? AND p_id = ?");
    return $st->execute([$qty, $c_id, $p_id]);
  }

  public function remove_item(int $c_id, int $p_id){
    $db = $this->db_connect();
    $st = $db->prepare("DELETE FROM cart WHERE c_id = ? AND p_id = ?");
    return $st->execute([$c_id, $p_id]);
  }

  public function empty_cart(int $c_id){
    $db = $this->db_connect();
    $st = $db->prepare("DELETE FROM cart WHERE c_id = ?");
    return $st->execute([$c_id]);
  }

  public function get_cart_items(int $c_id){
    $db = $this->db_connect();
    $sql = "SELECT c.p_id, c.qty,
                   p.product_title, p.product_price, p.product_image
            FROM cart c
            JOIN products p ON p.product_id = c.p_id
            WHERE c.c_id = ?
            ORDER BY p.product_title";
    $st = $db->prepare($sql);
    $st->execute([$c_id]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public function count_items(int $c_id){
    $db = $this->db_connect();
    $st = $db->prepare("SELECT COALESCE(SUM(qty),0) AS cnt FROM cart WHERE c_id = ?");
    $st->execute([$c_id]);
    return (int)($st->fetchColumn() ?: 0);
  }




  public function migrate_guest_to_user(int $c_id): void {
    if (!isset($_COOKIE['cart_token'])) return;
    $token = $_COOKIE['cart_token'];
    $sql   = "UPDATE cart SET c_id=?, ip_add=NULL WHERE ip_add=?";
    $st    = $this->db->prepare($sql);
    $st->execute([$c_id, $token]);
    // keep the cookie if you want; or clear it:
    // setcookie('cart_token','', time()-3600, '/');
  }

}