
<?php
// Persist `next` into session when register.php is visited so subsequent
// login requests can use it even if client-side JS fails or is blocked.
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_GET['next'])) {
  $next = (string)$_GET['next'];
  if (!preg_match('#^https?://#i', $next)) {
    $_SESSION['post_login_next'] = $next;
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Create an account</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="../Css/auth_base.css">
  <link rel="stylesheet" href="../Css/register_page.css">


  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/css/intlTelInput.css" />
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
          <div class="auth-caption fade-blur">GET<br>STARTED!</div>
        </aside>


        <section class="auth-form slide-right">
          <h1 class="auth-title fade-blur">Create an account</h1>
          <p class="auth-sub fade-blur delay">Already have an account? <a id="to-login-link" href="login.php">Log in</a></p>

          <div id="register-msg" class="alert info" role="status" style="display:none"></div>


          <form id="register-form" autocomplete="on" novalidate>
            <div class="field">
              <label for="name">Full name</label>
              <input class="input" id="name" name="name" type="text" maxlength="100" required placeholder="eg: Ama Mensah">
            </div>

            <div class="field">
              <label for="email">Email</label>
              <input class="input" id="email" name="email" type="email" maxlength="50" required placeholder="eg: amamensah@gmail.com">
            </div>

            <div class="field">
              <label for="password">Password</label>
              <input class="input" id="password" name="password" type="password" minlength="8" maxlength="150" required placeholder="Enter your password">

              <div class="req-box" id="pwd-req">
                <ul class="req-list">
                  <li data-rule="upper">One uppercase letter</li>
                  <li data-rule="digit">At least one digit</li>
                  <li data-rule="special">At least one special character</li>
                  <li data-rule="len">Minimum of eight characters</li>
                </ul>
              </div>
            </div>

            <div class="field">
              <label for="confirm_password">Confirm password</label>
              <input class="input" id="confirm_password" name="confirm_password" type="password" minlength="8" maxlength="150" required placeholder="Re-enter your password">
            </div>


            <input type="hidden" id="country" name="country" value="">

            <div class="field">
              <label for="city">City</label>
              <input class="input" id="city" name="city" type="text" maxlength="30" required placeholder="eg: Accra">
            </div>

            <div class="field">
              <label for="phone_number">Contact number</label>
              <input class="input" id="phone_number" name="phone_number" type="tel" maxlength="15" required placeholder="+233 24 123 4567">
              <small class="helper">Choose country and contact number</small>
            </div>

            <button id="register-btn" class="btn" type="submit">Create account</button>
          </form>
        </section>

      </div>
    </div>
  </div>


  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/js/intlTelInput.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/js/utils.js"></script>


  <script>
    (function () {
      window.iti = window.intlTelInput(document.getElementById('phone_number'), {
        initialCountry: 'gh',
        separateDialCode: true,
        utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/js/utils.js',
        preferredCountries: ['gh','ng','us','gb'],
        autoPlaceholder: 'aggressive'
      });
      function syncCountryHidden(){
        var data = window.iti.getSelectedCountryData();
        document.getElementById('country').value = data && data.name ? data.name : '';
      }
      syncCountryHidden();
      document.getElementById('phone_number').addEventListener('countrychange', syncCountryHidden);
    })();
  </script>


  <script>
    (function(){
      const $pw  = $('#password');
      const $cpw = $('#confirm_password');

      function markBox(id, key, ok){
        $('#'+id+' [data-rule="'+key+'"]').toggleClass('done', !!ok);
      }
      function checkPassword(){
        const v = $pw.val();
        markBox('pwd-req','upper', /[A-Z]/.test(v));
        markBox('pwd-req','digit', /\d/.test(v));
        markBox('pwd-req','special', /[!@#$%^&*(),.?":{}|<>_\-\\[\];'`~]/.test(v));
        markBox('pwd-req','len', v.length >= 8);
      }
      function checkMatch(){
        const ok = $pw.val() !== '' && $pw.val() === $cpw.val();

      }
      $pw.on('focus input blur', function(){ checkPassword(); checkMatch(); });
      $cpw.on('focus input blur', checkMatch);
    })();
  </script>


  <script src="../js/register.js"></script>


  <script>
    // If the register page was opened without a `next` on the login link,
    // try to fill it using a stored login URL (from sessionStorage). This
    // helps when the client navigation lost the query string on some servers.
    (function(){
      try{
        const a = document.getElementById('to-login-link');
        if (a) {
          const params = new URLSearchParams(a.search || window.location.search || '');
          // If the href already includes next, leave it alone. Otherwise try stored value.
          if (!/\bnext=/.test(a.href)) {
            const stored = sessionStorage.getItem('login_return_url');
            if (stored && stored.indexOf('login.php') !== -1) {
              // Use the stored full login URL
              a.href = stored;
            } else {
              // fallback: preserve any next passed to register.php
              const p = new URLSearchParams(window.location.search);
              const next = p.get('next');
              if (next) a.href = 'login.php?next=' + encodeURIComponent(next);
            }
          }
        }
      } catch (e) { /* ignore */ }
      window.addEventListener('DOMContentLoaded', () => document.body.classList.add('loaded'));
    })();
  </script>
</body>
</html>
