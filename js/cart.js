// Basic helpers; keep your existing style & dark UI
async function post(url, data) {
  console.debug('POST', url, data);
  const r = await fetch(url, { method:'POST', body: new URLSearchParams(data), credentials: 'same-origin' });
  const text = await r.text();
  try {
    const json = JSON.parse(text || '{}');
    // attach status for callers
    json.__httpStatus = r.status;
    console.debug('POST response', url, json);
    return json;
  } catch (e) {
    console.error('Failed to parse JSON response from', url, text, e);
    return { status: 'error', message: 'Invalid JSON response', __httpStatus: r.status };
  }
}

export async function addToCart(productId, qty=1) {
  const res = await post('../Actions/add_to_cart_action.php', { product_id: productId, qty });
  console.debug('addToCart result', res);
  // if the action returns a new count, update the badge here for convenience
  try {
    if (res && typeof res.count !== 'undefined') {
      const el = document.getElementById('cart-count');
      if (el) el.textContent = String(res.count);
    }
  } catch (e) {
    // ignore DOM update errors
  }
  return res;
}

export async function updateQty(productId, qty) {
  const res = await post('../Actions/update_quantity_action.php', { product_id: productId, qty });
  if (res.status==='ok') location.reload();
}

export async function removeFromCart(productId) {
  const res = await post('../Actions/remove_from_cart_action.php', { product_id: productId });
  if (res.status==='ok') location.reload();
}

export async function emptyCart() {
  const res = await post('../Actions/empty_cart_action.php', {});
  if (res.status==='ok') location.reload();
}

export async function updateCartCount() {
  try {
    const r = await fetch('../Actions/cart_count_action.php', { cache: 'no-store' });
    const data = await r.json();
    const el = document.getElementById('cart-count');
    if (el) el.textContent = String(data.count ?? 0);
    return data;
  } catch (e) {
    console.error('Cart count fetch failed', e);
    return { count: 0 };
  }
}
