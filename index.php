<?php

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$loggedIn = !empty($_SESSION['customer_id']);
$customer = $_SESSION['customer_name'] ?? null;
$isAdmin  = isset($_SESSION['user_role']) && (int)$_SESSION['user_role'] === 1;
$first    = $customer ? explode(' ', trim($customer))[0] : null;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Re.vert · Home</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="Css/index.css">
</head>
<body>

  
  <div class="bg-bubbles" aria-hidden="true">
    <span class="b b1"></span><span class="b b2"></span><span class="b b3"></span>
    <span class="b b4"></span><span class="b b5"></span><span class="b b6"></span>
  </div>


  <header class="nav">
    <a class="brand" href="index.php">
      <span class="logo">🛍️</span><span class="name">Re.vert</span>
    </a>

    <nav class="links">
      <a href="index.php">Home</a>
      <a href="view/products.php">Shop Now</a>
      <a href="#testimonials">Stories</a>
      <a href="#faq">Help</a>
    </nav>

    <div class="actions">
      <?php if (!$loggedIn): ?>
        <a class="btn ghost" href="view/login.php">Log in</a>
        <a class="btn" href="view/register.php">Get Started</a>
      <?php else: ?>
        <?php if ($isAdmin): ?>
          <a class="btn ghost" href="Admin/category.php">Dashboard</a>
        <?php endif; ?>
        <a class="btn" href="Actions/logout.php">Logout</a>
      <?php endif; ?>
    </div>
  </header>

  
  <section class="hero container reveal" data-reveal="left">
    <div class="hero-copy">
      <h1 class="blur-words">
        <?php if ($first): ?>Hey <?= htmlspecialchars($first) ?> —<?php endif; ?>
         <span class="accent">Discover the best Products & Deals</span>
      </h1>
      <p class="sub blur-words delay-150">Curated items, fast delivery, and secure checkout — all in one place.</p>

      <div class="cta reveal" data-reveal="up">
        <a class="btn" href="view/register.php">Get Started</a>
        <a class="btn ghost" href="#features">Explore Features</a>
        <a class="btn" href="view/products.php">Shop Now</a>
      </div>
  </section>



  <section class="trust container reveal" data-reveal="up">
    <h2 class="blur-words">Secure & Private</h2>
    <p class="blur-words delay-100">24/7 dedicated support team</p>
  </section>


  <section id="features" class="features container">
    <article class="card frosted reveal" data-reveal="up">
      <div class="chip chip--gold">🛒</div>
      <h3 class="blur-words">Curated Products</h3>
      <p class="blur-words delay-100">Carefully selected items across fashion, electronics, beauty, and more.</p>
      <a class="link" href="#">Shop Collections →</a>
    </article>

    <article class="card frosted reveal" data-reveal="up">
      <div class="chip chip--violet">🚚</div>
      <h3 class="blur-words">Fast Delivery</h3>
      <p class="blur-words delay-100">Reliable shipping and easy order tracking from your account.</p>
      <a class="link" href="#">Track Orders →</a>
    </article>

    <article class="card frosted reveal" data-reveal="up">
      <div class="chip chip--red">🔒</div>
      <h3 class="blur-words">Secure Checkout</h3>
      <p class="blur-words delay-100">Multiple payment options with industry-standard encryption.</p>
      <a class="link" href="#">How We Protect You →</a>
    </article>
  </section>


  <section class="stats container reveal" data-reveal="scale">
    <div class="stat"><strong>50k+</strong><span>Happy Customers</span></div>
    <div class="stat"><strong>2k+</strong><span>Products</span></div>
    <div class="stat"><strong>99.9%</strong><span>Uptime</span></div>
    <div class="stat"><strong>24/7</strong><span>Support</span></div>
  </section>


  <section class="callout container frosted reveal" data-reveal="right">
    <div class="callout-copy">
      <h2 class="blur-words">Shop with confidence on our <span class="accent">platform</span>.</h2>
      <p class="blur-words delay-100">Follow collections and never miss a deal. New drops every week.</p>
      <a class="btn small" href="view/register.php">Get Started</a>
    </div>

  </section>


  <section id="testimonials" class="testimonials container">
    <h2 class="blur-words">What shoppers <span class="accent">say</span></h2>
    <div class="t-grid">
      <article class="t-card frosted reveal" data-reveal="up">
        <p>“Super fast delivery and great quality. My new favorite store!”</p>
        <div class="who"><img src="images/avatar2.png" alt=""> Nadia • Accra</div>
      </article>
      <article class="t-card frosted reveal" data-reveal="up">
        <p>“Checkout felt safe and easy. Customer service was on point.”</p>
        <div class="who"><img src="images/avatar3.png" alt=""> Eric • Kumasi</div>
      </article>
      <article class="t-card frosted reveal" data-reveal="up">
        <p>“The collections are 🔥. I always find something unique.”</p>
        <div class="who"><img src="images/avatar1.png" alt=""> Ama • Takoradi</div>
      </article>
    </div>
  </section>


  <section id="faq" class="faq container">
    <h2 class="blur-words">Frequently Asked <span class="accent">Questions</span></h2>
    <div class="faq-list">
      <details class="reveal" data-reveal="up">
        <summary>How do I track my order?</summary>
        <p>Head to your account → Orders, then click “Track” to view real-time status.</p>
      </details>
      <details class="reveal" data-reveal="up">
        <summary>What payments do you accept?</summary>
        <p>We support major cards and mobile money. All payments are securely processed.</p>
      </details>
      <details class="reveal" data-reveal="up">
        <summary>How fast is delivery?</summary>
        <p>Most orders ship within 24–48 hours. Delivery speed depends on your location.</p>
      </details>
    </div>
  </section>


  <section class="newsletter container frosted reveal" data-reveal="up">
    <h3 class="blur-words">Get weekly deals & new drops</h3>
    <form class="nl-form" onsubmit="return false;">
      <input type="email" placeholder="Enter your email" aria-label="Email">
      <button class="btn small">Subscribe</button>
    </form>
    <small class="note">No spam. Unsubscribe any time.</small>
  </section>

  <footer class="footer container">
    <p>© <?= date('Y') ?> Re.vert. All rights reserved.</p>
  </footer>

  <script src="js/land_animate.js"></script>
</body>
</html>
