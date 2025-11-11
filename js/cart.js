// Basic helpers; keep your existing style & dark UI
async function post(url, data) {
  const r = await fetch(url, { method:'POST', body: new URLSearchParams(data) });
  return r.json();
}

export async function addToCart(productId, qty=1) {
  const res = await post('../Actions/add_to_cart_action.php', { product_id: productId, qty });
  alert(res.message || (res.status==='ok' ? 'Added!' : 'Error'));
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
