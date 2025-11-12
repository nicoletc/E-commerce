$(document).ready(function () {
  $('#login-form').on('submit', function (e) {
    e.preventDefault();

    const email = $('#lemail').val().trim().toLowerCase();
    const pass  = $('#lpass').val();

    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email || !pass) {
      return Swal.fire({icon:'error', title:'Oops...', text:'Please fill in both fields.'});
    }
    if (!emailRe.test(email)) {
      return Swal.fire({icon:'error', title:'Invalid email', text:'Enter a valid email address.'});
    }


    const $btn = $('#login-btn');
    $btn.prop('disabled', true);

    $.ajax({
      url: '../Actions/login_customer_action.php',
      type: 'POST',
      dataType: 'json',
      contentType: 'application/json',
      data: JSON.stringify({ email, password: pass }),
      success: function (res) {
        if (res.status === 'success') {
          Swal.fire({
            icon:'success',
            title:'Login complete',
            text:'Welcome back!',
            timer: 1200,
            showConfirmButton: false
          }).then(() => {
            // if a next parameter is present in the login URL, redirect there
            try {
              const params = new URLSearchParams(window.location.search);
              const next = params.get('next');
              if (next) {
                // decode and redirect; allow absolute or relative paths
                const dest = decodeURIComponent(next);
                window.location.href = dest;
                return;
              }
            } catch (e) {
              // fall back
            }
            window.location.href = '../index.php';
          });
        } else {
          Swal.fire({icon:'error', title:'Login failed', text: res.message || 'Check your credentials.'});
        }
      },
      error: function (xhr) {
        const msg = xhr.responseText || 'An error occurred! Please try again later.';
        Swal.fire({icon:'error', title:'Oops...', text: msg});
      },
      complete: function () {
        $btn.prop('disabled', false);
      }
    });
  });
});
