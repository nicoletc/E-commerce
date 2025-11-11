<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
if (session_status()===PHP_SESSION_NONE) session_start();

require_once __DIR__.'/../Controllers/cart_controller.php';
$items = get_user_cart_ctr();
$sum = 0.0; foreach($items as $it){ $sum += $it['product_price']*$it['qty']; }
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); }
?>
<!doctype html><html><head>
<meta charset="utf-8">
<title>Checkout · Re.vert</title>
<link rel="stylesheet" href="../Css/products.css">
<script type="module">
import {simulatePayment} from '../js/checkout.js';
window.simulatePayment = simulatePayment;
</script>
</head><body class="dark">
<header class="nav">
  <a class="brand" href="../index.php"><span class="logo">🛍️</span><span class="name">Re.vert</span></a>
  <nav class="links"><a href="cart.php">Cart</a></nav>
</header>

<section class="container">
  <h1>Order Summary</h1>
  <?php if(!$items): ?>
    <div class="frosted" style="padding:20px;border-radius:12px">Your cart is empty.</div>
  <?php else: ?>
    <div class="frosted" style="padding:16px;border-radius:12px">
      <?php foreach($items as $it): ?>
        <div style="display:flex;justify-content:space-between;margin:6px 0">
          <div><?= h($it['product_title']) ?> × <?= (int)$it['qty'] ?></div>
          <div>₵<?= number_format($it['product_price']*$it['qty'],2) ?></div>
        </div>
      <?php endforeach; ?>
      <hr class="muted">
      <div style="display:flex;justify-content:space-between">
        <strong>Total</strong><strong>₵<?= number_format($sum,2) ?></strong>
      </div>
      <div style="margin-top:14px;display:flex;gap:10px;justify-content:flex-end">
        <a class="btn ghost" href="cart.php">Back to Cart</a>
        <button class="btn" onclick="simulatePayment()">Simulate Payment</button>
      </div>
      <small class="muted">For demo: confirms instantly; creates order, orderdetails, payment, then empties cart.</small>
    </div>
  <?php endif; ?>
</section>
</body></html>
