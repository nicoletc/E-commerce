$(document).ready(function () {
  $('#register-form').on('submit', function (e) {
    e.preventDefault();

    const name    = $('#name').val().trim();
    const email   = $('#email').val().trim().toLowerCase();
    const pass    = $('#password').val();
    const cpass   = $('#confirm_password').val();
    const country = $('#country').val().trim();
    const city    = $('#city').val().trim();


    const hasITI  = typeof window.iti !== 'undefined';
    const phoneOk = hasITI ? window.iti.isValidNumber() : /^\+?[0-9\s\-()]{7,15}$/.test($('#phone_number').val().trim());
    const phone   = hasITI ? window.iti.getNumber() : $('#phone_number').val().trim(); 


    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const passRe  = /^(?=.*\d).{8,}$/;                
    const placeRe = /^[A-Za-z\s\-'.()]{2,30}$/;       

    if (!name || !email || !pass || !cpass || !country || !city || !phone) {
      return Swal.fire({icon:'error', title:'Oops...', text:'Please fill in all fields!'});
    }
    if (!emailRe.test(email) || email.length > 50) {
      return Swal.fire({icon:'error', title:'Invalid email', text:'Please enter a valid email address (max 50 chars).'});
    }
    if (!passRe.test(pass) || pass.length > 150) {
      return Swal.fire({icon:'error', title:'Weak password', text:'Password must be at least 8 characters and include a number.'});
    }
    if (cpass !== pass) {
      return Swal.fire({icon:'error', title:'Passwords do not match', text:'Make sure both password fields are identical.'});
    }
    if (country.length === 0 || country.length > 30) {
      return Swal.fire({icon:'error', title:'Invalid country', text:'Please select a country from the dropdown.'});
    }
    if (!placeRe.test(city)) {
      return Swal.fire({icon:'error', title:'Invalid city', text:'City should be letters/spaces (2–30 characters).'});
    }
    if (!phoneOk) {
      return Swal.fire({icon:'error', title:'Invalid contact', text:'Provide a valid phone number for the selected country.'});
    }


    $.ajax({
      url: '../Actions/register_customer_action.php',
      type: 'POST',
      dataType: 'json',
      contentType: 'application/json; charset=utf-8',
      data: JSON.stringify({
        name,
        email,
        password: pass,
        country,
        city,
        phone_number: phone
      }),
      success: function (res) {
        if (res.status === 'success' || res.ok === true) {
          Swal.fire({icon:'success', title:'Success', text:res.message || 'Registration successful.'})
            .then(() => {
              // Prefer a stored login URL (saved when the user first landed on login.php)
              // This avoids losing the `next` param if it was stripped during navigation.
              try {
                const stored = sessionStorage.getItem('login_return_url');
                if (stored && stored.indexOf('login.php') !== -1) {
                  try { console.debug('register using stored login url', stored); } catch (e) {}
                  window.location.href = stored;
                  return;
                }
              } catch (e) { /* ignore */ }

              // Fallback: use next query param on register page, else plain login
              try {
                const params = new URLSearchParams(window.location.search);
                const next = params.get('next');
                const dest = next ? ('login.php?next=' + encodeURIComponent(next)) : 'login.php';
                try { console.debug('register redirect to', dest, 'parsed next=', next); } catch (e) {}
                window.location.href = dest;
              } catch (e) {
                window.location.href = 'login.php';
              }
            });
        } else {
          Swal.fire({icon:'error', title:'Oops...', text:res.message || 'Registration failed.'});
        }
      },
      error: function (xhr) {
        const msg = xhr.responseText || 'An error occurred! Please try again later.';
        Swal.fire({icon:'error', title:'Oops...', text: msg});
      }
    });
  });
});
