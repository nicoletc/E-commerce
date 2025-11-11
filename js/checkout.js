async function post(url, data) {
  const r = await fetch(url, { method:'POST', body: new URLSearchParams(data) });
  return r.json();
}

export async function simulatePayment() {
  // show your modal; if user clicks "Yes, I’ve paid", then:
  const res = await post('../Actions/process_checkout_action.php', {});
  if (res.status === 'ok') {
    alert(`Thank you! Order #${res.order_id}\nInvoice ${res.invoice}\nTotal ₵${res.total.toFixed(2)}`);
    window.location.href = 'all_product.php';
  } else {
    alert(res.message || 'Checkout failed');
  }
}
