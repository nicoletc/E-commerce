// /js/products.js
document.addEventListener('DOMContentLoaded', () => {
  // Smooth scroll to catalog
  const btn = document.getElementById('shopNow');
  const cat = document.getElementById('catalog');
  if (btn && cat) btn.addEventListener('click', () => cat.scrollIntoView({behavior:'smooth'}));

  // Reveal on view
  const els = document.querySelectorAll('.reveal');
  const io = new IntersectionObserver((entries)=>{
    entries.forEach(e=>{ if(e.isIntersecting) e.target.classList.add('in'); });
  }, {threshold: 0.08});
  els.forEach(el=>io.observe(el));

  // Auth gate for "View" + "Add to cart"
  function ensureAuth(e){
    const auth = e.currentTarget.getAttribute('data-auth');
    if (auth !== '1') {
      e.preventDefault();
      Swal.fire({
        icon:'info',
        title:'Please log in',
        text:'You need to be logged in to view product details or add to cart.',
        confirmButtonText:'Login'
      }).then(()=> { window.location.href = '../view/login.php?next=' + encodeURIComponent(window.location.pathname); });
    }
  }
  document.querySelectorAll('.view-btn,.add-btn').forEach(b => {
    b.addEventListener('click', ensureAuth);
  });
});
