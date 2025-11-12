<?php
require_once '../settings/db_class.php';

class order_class extends Db {

  public function create_order(int $customer_id, int $invoice_no, string $status='paid'): int {
    $sql = "INSERT INTO orders (customer_id, invoice_no, order_date, order_status)
            VALUES (?, ?, CURDATE(), ?)";
    $st  = $this->db->prepare($sql);
    $st->execute([$customer_id, $invoice_no, $status]);
    return (int)$this->db->lastInsertId();
  }

  public function add_detail(int $order_id, int $product_id, int $qty): bool {
    $sql = "INSERT INTO orderdetails (order_id, product_id, qty) VALUES (?, ?, ?)";
    $st  = $this->db->prepare($sql);
    return $st->execute([$order_id, $product_id, $qty]);
  }

  public function record_payment(float $amount, int $customer_id, int $order_id, string $currency='GHS'): bool {
    $sql = "INSERT INTO payment (amt, customer_id, order_id, currency, payment_date)
            VALUES (?, ?, ?, ?, CURDATE())";
    $st  = $this->db->prepare($sql);
    return $st->execute([$amount, $customer_id, $order_id, $currency]);
  }

  public function user_orders(int $customer_id): array {
    $st = $this->db->prepare("SELECT * FROM orders WHERE customer_id=? ORDER BY order_id DESC");
    $st->execute([$customer_id]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }
}
