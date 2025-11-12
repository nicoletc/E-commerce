async function post(url, data) {
  const r = await fetch(url, { method:'POST', body: new URLSearchParams(data) });
  const json = await r.json().catch(() => ({}));
  // attach http status so callers can react to 401/403
  json.__httpStatus = r.status;
  return json;
}

export async function simulatePayment() {

  console.log('simulatePayment invoked');
  // Try to obtain SweetAlert2; if unavailable fall back to window.confirm/alert
  let Swal = window.Swal || null;
  try {
    if (!Swal) {
      const mod = await import('https://cdn.jsdelivr.net/npm/sweetalert2@11');
      Swal = mod && mod.default ? mod.default : null;
    }
  } catch (err) {
    console.warn('Could not load SweetAlert2 dynamically, falling back to native confirm/alert', err);
    Swal = null;
  }

  let confirmed = false;
  if (Swal) {
    const result = await Swal.fire({
      title: 'Simulate payment',
      text: 'This is a demo. Click Confirm when you have "paid" to complete checkout.',
      icon: 'info',
      showCancelButton: true,
      confirmButtonText: 'Yes, I\'ve paid',
      cancelButtonText: 'Cancel'
    });
    if (!result.isConfirmed) {
      await Swal.fire({ title: 'Payment cancelled', icon: 'warning', timer: 1200, showConfirmButton: false });
      return;
    }
    confirmed = true;
    // show loading while backend processes the order
    Swal.fire({ title: 'Processing order…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
  } else {
    // native fallback
    confirmed = window.confirm('This is a demo. Click OK to simulate payment.');
    if (!confirmed) {
      window.alert('Payment cancelled');
      return;
    }
    // show simple loading via console (no blocking UI)
    console.log('Processing order...');
  }

  try {
    const res = await post('../Actions/process_checkout_action.php', {});
    if (Swal) Swal.close();
    // If server indicates auth required, prompt to login and redirect back
    if (res.__httpStatus === 401 || (res.status === 'error' && /log in/i.test(res.message || ''))) {
      if (Swal) {
        const r = await Swal.fire({
          title: 'Please log in',
          text: 'You need to log in to complete checkout. Would you like to log in now?',
          icon: 'info',
          showCancelButton: true,
          confirmButtonText: 'Log in'
        });
        if (r.isConfirmed) {
          // redirect back to current page after login
          const next = encodeURIComponent(window.location.pathname + window.location.search);
          window.location.href = 'login.php?next=' + next;
        }
      } else {
        if (confirm('Please log in to complete checkout. Log in now?')) {
          const next = encodeURIComponent(window.location.pathname + window.location.search);
          window.location.href = 'login.php?next=' + next;
        }
      }
      return;
    }

    if (res.status === 'ok') {
      if (Swal) {
        await Swal.fire({
          title: 'Payment successful',
          html: `Thank you!<br>Your order reference: <strong>${res.order_ref ?? res.invoice ?? res.order_id}</strong><br>Total: ₵${Number(res.total).toFixed(2)}`,
          icon: 'success',
          confirmButtonText: 'Continue shopping'
        });
      } else {
        window.alert(`Thank you! Order reference: ${res.order_ref ?? res.invoice ?? res.order_id}\nTotal: ₵${Number(res.total).toFixed(2)}`);
      }
  // redirect to a payment success page showing the order reference
  const ref = encodeURIComponent(res.order_ref ?? res.invoice ?? res.order_id);
  const oid = encodeURIComponent(res.order_id ?? '');
  const tot = encodeURIComponent(res.total ?? '');
  window.location.href = `payment_success.php?ref=${ref}&order_id=${oid}&total=${tot}`;
    } else {
      if (Swal) await Swal.fire({ title: 'Checkout failed', text: res.message || 'An error occurred', icon: 'error' });
      else window.alert(res.message || 'Checkout failed');
    }
  } catch (err) {
    console.error(err);
    if (Swal) {
      Swal.close();
      await Swal.fire({ title: 'Network error', text: 'Could not contact server. Try again.', icon: 'error' });
    } else {
      window.alert('Network error: Could not contact server. Try again.');
    }
  }
}
