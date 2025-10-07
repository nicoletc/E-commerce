<?php
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
  <title>Admin · Categories</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="../Css/auth_base.css">
  <link rel="stylesheet" href="../Css/admin.css">
</head>
<body>


  <div class="bg-bubbles" aria-hidden="true">
    <span class="b b1"></span>
    <span class="b b2"></span>
    <span class="b b3"></span>
    <span class="b b4"></span>
    <span class="b b5"></span>
    <span class="b b6"></span>
  </div>

  <div class="dash">


    <aside class="dash-side">
      <div class="brand">
        <div class="brand-logo">🛍️</div>
        <div class="brand-name">E-Shop Admin</div>
      </div>

      <nav class="side-nav">
        <a class="nav-item is-active" href="category.php">Categories</a>
        <a class="nav-item" href="#">Products</a>
        <a class="nav-item" href="#">Orders</a>
        <a class="nav-item" href="#">Customers</a>
        <a class="nav-item" href="#">Reports</a>
        <a class="nav-item" href="#">Settings</a>
      </nav>

      <div class="side-foot">
        <a class="nav-item logout" href="../Actions/logout.php">Logout</a>
      </div>
    </aside>


    <main class="dash-main">


      <header class="dash-header reveal">
        <div class="hello">
          <h1>Hello, <?= $first ?>! <span class="whatsup">What’s up?</span> 👋</h1>
          <p>Manage your categories below.</p>
        </div>
      </header>


      <section class="hero reveal" role="img" aria-label="Announcement">
        <div class="hero-content">
          <div class="chip">New</div>
          <h2>Effective catalog!</h2>
          <p>Create clear categories so customers find items faster and convert better.</p>
        </div>
        <div class="hero-art">
          <span class="ring r1"></span>
          <span class="ring r2"></span>
          <span class="ring r3"></span>
        </div>
      </section>


      <article class="card reveal">
        <div class="card-head">
          <h3>Add category</h3>
        </div>
        <form id="create-form" class="row" autocomplete="off">
          <input id="cat_name" class="input" type="text" maxlength="100"
                 placeholder="Enter category name (e.g., Electronics)" required>
          <button class="btn" type="submit">Add</button>
        </form>
        <p class="muted">Names must be unique.</p>
      </article>


      <article class="card reveal">
        <div class="card-head">
          <h3>Categories</h3>
        </div>
        <div class="table-wrap">
          <table class="tbl">
            <thead>
              <tr>
                <th style="width:70%">Name</th>
                <th class="ta-right">Actions</th>
              </tr>
            </thead>
            <tbody id="tbody">
              <tr><td colspan="2">Loading…</td></tr>
            </tbody>
          </table>
        </div>
      </article>

    </main>
  </div>


  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../js/category.js"></script>

  <!-- Intersection observer to trigger slide-in once visible -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const els = document.querySelectorAll('.reveal');
      const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
          if (e.isIntersecting) e.target.classList.add('in');
        });
      }, {threshold: 0.08});
      els.forEach(el => io.observe(el));
    });
  </script>
</body>
</html>
