<?php
ini_set('display_errors',1); error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) session_start();

$ref = htmlspecialchars((string)($_GET['ref'] ?? '')); 
$order_id = (int)($_GET['order_id'] ?? 0);
$total = isset($_GET['total']) ? number_format((float)$_GET['total'], 2) : '';

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Payment Successful · Re.vert</title>
  <link rel="stylesheet" href="../Css/products.css">
</head>
<body class="dark">
<header class="nav">
  <a class="brand" href="../index.php"><span class="logo">🛍️</span><span class="name">Re.vert</span></a>
  <nav class="links"><a href="all_product.php">Shop</a></nav>
</header>

<section class="container">
  <div class="frosted" style="padding:24px;border-radius:12px;text-align:center;max-width:720px;margin:40px auto;">
    <h1>Thank you for your order!</h1>
    <p>Your payment was received and your order has been placed successfully.</p>
    <?php if ($ref): ?>
      <p style="margin-top:8px"><strong>Order reference:</strong> <?= $ref ?></p>
    <?php endif; ?>
    <?php if ($order_id): ?>
      <p><strong>Order id:</strong> <?= (int)$order_id ?></p>
    <?php endif; ?>
    <?php if ($total !== ''): ?>
      <p><strong>Total paid:</strong> ₵<?= $total ?></p>
    <?php endif; ?>

    <div style="margin-top:18px;display:flex;gap:10px;justify-content:center;">
      <a class="btn" href="all_product.php">Continue shopping</a>
      <a class="btn ghost" href="cart.php">View cart</a>
    </div>
  </div>
</section>
</body>
</html>
