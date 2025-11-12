<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
if (session_status()===PHP_SESSION_NONE) session_start();

$require_path = __DIR__ . '/../Controllers/cart_controller.php';
require_once __DIR__.'/../Controllers/cart_controller.php';
$customer_id = (int)($_SESSION['customer_id'] ?? 0);
// Ensure $items is always an array to avoid static analysis/warnings and foreach errors
$items = (array)get_user_cart_ctr($customer_id);
$sum = 0.0; foreach($items as $it){ $sum += $it['product_price']*$it['qty']; }
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); }
?>
<!doctype html><html><head>
<meta charset="utf-8">
<title>Checkout · Re.vert</title>
<link rel="stylesheet" href="../Css/products.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <div style="display:flex;justify-content:space-between;align-items:center;margin:8px 0;gap:12px">
          <div style="display:flex;align-items:center;gap:10px;min-width:220px;flex:1">
            <?php if (!empty($it['product_image'])): ?>
              <img src="<?= htmlspecialchars('../' . ltrim($it['product_image'], '/')) ?>" alt="<?= h($it['product_title']) ?>" style="width:60px;height:60px;object-fit:cover;border-radius:8px;background:#222">
            <?php else: ?>
              <div style="width:60px;height:60px;border-radius:8px;background:#222;display:inline-block;"></div>
            <?php endif; ?>
            <div>
              <div style="font-weight:700"><?= h($it['product_title']) ?></div>
              <div style="font-size:13px;color:#ddd">Qty: <?= (int)$it['qty'] ?></div>
            </div>
          </div>
          <div style="min-width:120px;text-align:right">₵<?= number_format($it['product_price']*$it['qty'],2) ?></div>
        </div>
      <?php endforeach; ?>
      <hr class="muted">
      <div style="display:flex;justify-content:space-between">
        <strong>Total</strong><strong>₵<?= number_format($sum,2) ?></strong>
      </div>
        <div style="margin-top:14px;display:flex;gap:10px;justify-content:flex-end">
        <a class="btn ghost" href="cart.php">Back to Cart</a>
        <?php if ($customer_id > 0): ?>
          <button id="simulate-pay-btn" class="btn">Simulate Payment</button>
        <?php else: ?>
          <a id="simulate-login-btn" class="btn" href="login.php?next=checkout.php" title="Please login to checkout">Simulate Payment</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</section>
<script>
  // Attach a resilient click handler: prefer window.simulatePayment (from module), otherwise fallback to native confirm + POST
  document.addEventListener('DOMContentLoaded', function () {
    // logged-in simulate button
    const btn = document.getElementById('simulate-pay-btn');
    if (btn) {
      btn.addEventListener('click', async function (e) {
        e.preventDefault();
        if (typeof window.simulatePayment === 'function') {
          try { await window.simulatePayment(); } catch (err) { console.error(err); }
          return;
        }
        // fallback: confirm then POST to action (minimal UX)
        if (!confirm('Simulate payment? Click OK to proceed.')) { alert('Payment cancelled'); return; }
        try {
          const res = await fetch('../Actions/process_checkout_action.php', { method: 'POST' });
          const data = await res.json();
          if (data.status === 'ok') {
            alert('Payment successful. Reference: ' + (data.order_ref ?? data.order_id));
            window.location.href = 'all_product.php';
          } else {
            alert('Checkout failed: ' + (data.message || 'Unknown'));
          }
        } catch (err) { console.error(err); alert('Network error'); }
      });
    }

    // guest simulate link (if present) — show SweetAlert prompt asking the user to log in
    const guestLink = document.getElementById('simulate-login-btn');
    if (guestLink) {
      guestLink.addEventListener('click', async function (e) {
        e.preventDefault();
        // prefer global Swal (script included above) but fall back to dynamic import or native confirm
        let Swal = window.Swal ?? null;
        try {
          if (!Swal) {
            const mod = await import('https://cdn.jsdelivr.net/npm/sweetalert2@11');
            Swal = mod && mod.default ? mod.default : null;
          }
        } catch (err) { Swal = null; }

        const doRedirect = () => {
          const next = encodeURIComponent(window.location.pathname + window.location.search);
          window.location.href = 'login.php?next=' + next;
        };

        if (Swal) {
          const r = await Swal.fire({
            title: 'Oops',
            text: 'You must be logged in to proceed with checkout.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Log in',
            cancelButtonText: 'Cancel'
          });
          if (r.isConfirmed) doRedirect();
        } else {
          if (confirm('You must be logged in to proceed with checkout. Log in now?')) doRedirect();
        }
      });
    }
  });
</script>
</body></html>
