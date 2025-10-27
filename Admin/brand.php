<?php
// Admin/brand.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../settings/core.php';
if (!is_logged_in()) { header('Location: ../view/login.php'); exit; }
if (!is_admin())    { header('Location: ../index.php');     exit; }

$customer = $_SESSION['customer_name'] ?? 'Admin';
$first    = htmlspecialchars(explode(' ', trim($customer))[0] ?: 'Admin');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Admin · Brands</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="../Css/auth_base.css">
  <!-- Reuse the exact same admin skin as categories -->
  <link rel="stylesheet" href="../Css/admin.css">
</head>
<body>

  <!-- Floating background bubbles (same as categories) -->
  <div class="bg-bubbles" aria-hidden="true">
    <span class="b b1"></span><span class="b b2"></span><span class="b b3"></span>
    <span class="b b4"></span><span class="b b5"></span><span class="b b6"></span>
  </div>

  <div class="dash">
    <!-- Sidebar -->
    <aside class="dash-side">
      <div class="brand">
        <div class="brand-logo">🛍️</div>
        <div class="brand-name">E-Shop Admin</div>
      </div>

      <nav class="side-nav">
        <a class="nav-item" href="category.php">Categories</a>
        <a class="nav-item is-active" href="brand.php">Brands</a>
        <a class="nav-item" href="product.php">Products</a>
        <a class="nav-item" href="#">Customers</a>
        <a class="nav-item" href="#">Reports</a>
        <a class="nav-item" href="#">Settings</a>
      </nav>

      <div class="side-foot">
        <a class="nav-item logout" href="../Actions/logout.php">Logout</a>
      </div>
    </aside>

    <!-- Main -->
    <main class="dash-main">
      <!-- Header -->
      <header class="dash-header reveal">
        <div class="hello">
          <h1>Hello, <?= $first ?>! <span class="whatsup">What’s up?</span> 👋</h1>
          <p>Manage your brands below.</p>
        </div>
      </header>

      <!-- Hero (distinct copy, same component/animation) -->
      <section class="hero reveal" role="img" aria-label="Brands highlight">
        <div class="hero-content">
          <div class="chip">New</div>
          <h2>Build trust with recognizable brands</h2>
          <p>Create clear, memorable brands so customers shop confidently and find what they love—faster.</p>
        </div>
        <div class="hero-art">
          <span class="ring r1"></span>
          <span class="ring r2"></span>
          <span class="ring r3"></span>
        </div>
      </section>

      <!-- CREATE -->
      <article class="card reveal">
        <div class="card-head">
          <h3>Add brand</h3>
        </div>

        <form id="brand-form" class="row" autocomplete="off" novalidate>
          <input id="brand-name" class="input" type="text" maxlength="100"
                 placeholder="Enter brand name (e.g., Nike)" required>
          <button class="btn" id="brand-add" type="submit">Add</button>
        </form>
        <p class="muted">Brand names must be unique.</p>
      </article>

      <!-- LIST -->
      <article class="card reveal">
        <div class="card-head">
          <h3>Brands</h3>
        </div>

        <div class="table-wrap">
          <table class="tbl">
            <thead>
              <tr>
                <th style="width:70%">Name</th>
                <th class="ta-right">Actions</th>
              </tr>
            </thead>
            <tbody id="brand-rows">
              <tr><td colspan="2">No brands yet — add one above.</td></tr>
            </tbody>
          </table>
        </div>
      </article>

    </main>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../js/brand.js"></script>

  <!-- Same reveal-on-scroll animation as categories -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const els = document.querySelectorAll('.reveal');
      const io  = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('in'); });
      }, { threshold: 0.08 });
      els.forEach(el => io.observe(el));
    });
  </script>
</body>
</html>
