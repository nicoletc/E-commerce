
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Log in</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />

  <link rel="stylesheet" href="../Css/auth_base.css">
  <link rel="stylesheet" href="../Css/login_page.css">
</head>
<body>


  <div class="bg-bubbles" aria-hidden="true">
    <span class="b b1"></span><span class="b b2"></span><span class="b b3"></span>
    <span class="b b4"></span><span class="b b5"></span><span class="b b6"></span>
  </div>

  <div class="auth-shell">
    <div class="auth-card neon">
      <div class="auth-grid">


        <aside class="auth-visual slide-left">
          <div class="auth-caption fade-blur">WELCOME<br>BACK</div>
        </aside>


        <section class="auth-form slide-right">
          <h1 class="auth-title fade-blur">Get Right Back In!</h1>
          <p class="auth-sub fade-blur delay">New here? <a href="register.php">Create an account</a></p>

          <form id="login-form" novalidate>
            <div class="field">
              <label for="lemail">Email</label>
              <input class="input" id="lemail" name="email" type="email">
            </div>

            <div class="field">
              <label for="lpass">Password</label>
              <input class="input" id="lpass" name="password" type="password">
            </div>

            <button id="login-btn" class="btn" type="submit">Log in</button>
          </form>
        </section>

      </div>
    </div>
  </div>

 
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


  <script src="../js/login.js"></script>


  <script>
    window.addEventListener('DOMContentLoaded', () => document.body.classList.add('loaded'));
  </script>
</body>
</html>
