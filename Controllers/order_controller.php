<?php
require_once __DIR__.'/../Classes/order_class.php';

function create_order_ctr(int $customer_id, int $invoice_no, string $status='paid'){ $o=new order_class(); return $o->create_order($customer_id,$invoice_no,$status); }
function add_order_detail_ctr(int $order_id, int $product_id, int $qty){ $o=new order_class(); return $o->add_detail($order_id,$product_id,$qty); }
function record_payment_ctr(float $amt, int $customer_id, int $order_id, string $ccy='GHS'){ $o=new order_class(); return $o->record_payment($amt,$customer_id,$order_id,$ccy); }
