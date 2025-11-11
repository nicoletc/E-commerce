<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
if (session_status()===PHP_SESSION_NONE) session_start();

require_once __DIR__.'/../Controllers/cart_controller.php';
$items = get_user_cart_ctr();

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); }
$sum = 0.0; foreach($items as $it){ $sum += $it['product_price']*$it['qty']; }
?>
<!doctype html><html><head>
<meta charset="utf-8">
<title>Cart · Re.vert</title>
<link rel="stylesheet" href="../Css/products.css">
<script type="module">
import {updateQty, removeFromCart, emptyCart} from '../js/cart.js';
window.updateQty = updateQty;
window.removeFromCart = removeFromCart;
window.emptyCart = emptyCart;
</script>
</head><body class="dark">
<header class="nav">
  <a class="brand" href="../index.php"><span class="logo">🛍️</span><span class="name">Re.vert</span></a>
  <nav class="links"><a href="all_product.php">Shop</a></nav>
</header>

<section class="container">
  <h1>Your Cart</h1>
  <?php if(!$items): ?>
    <div class="frosted" style="padding:20px;border-radius:12px">Your cart is empty.</div>
  <?php else: ?>
    <div class="frosted" style="padding:16px;border-radius:12px">
      <?php foreach($items as $it): ?>
        <div class="row" style="display:flex;gap:12px;align-items:center;margin:10px 0">
          <img src="../<?= h($it['product_image'] ?? '') ?>" alt="" style="width:70px;height:70px;object-fit:cover;border-radius:8px;background:#222">
          <div style="flex:1">
            <div><strong><?= h($it['product_title']) ?></strong></div>
            <div>₵<?= h($it['product_price']) ?> · Qty
              <input type="number" min="1" value="<?= (int)$it['qty'] ?>" style="width:70px"
                     onchange="updateQty(<?= (int)$it['p_id'] ?>, this.value)">
            </div>
          </div>
          <div>
            <button class="btn ghost" onclick="removeFromCart(<?= (int)$it['p_id'] ?>)">Remove</button>
          </div>
        </div>
        <hr class="muted">
      <?php endforeach; ?>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px">
        <button class="btn ghost" onclick="emptyCart()">Empty Cart</button>
        <div><strong>Total: ₵<?= number_format($sum,2) ?></strong></div>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:12px">
        <a class="btn ghost" href="all_product.php">Continue Shopping</a>
        <a class="btn" href="checkout.php">Proceed to Checkout</a>
      </div>
    </div>
  <?php endif; ?>
</section>
</body></html>
