$(function () {
  const $rows = $('#prod-rows');

  // Helpers
  const toast = (icon, title, text='') => Swal.fire({icon,title,text});
  const esc = s => String(s ?? '').replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));
  const money = v => (Number(v)||0).toFixed(2);

  // Populate category + brand selects (uses your existing endpoints)
  function loadCatsAndBrands() {
    // categories
    $.getJSON('../Actions/fetch_category_action.php', (res) => {
      const $sel = $('#product_cat').empty();
      if (res.status === 'success' && res.data?.length) {
        $sel.append('<option value="" disabled selected>Select category</option>');
        res.data.forEach(c => $sel.append(`<option value="${c.cat_id}">${esc(c.cat_name)}</option>`));
      } else {
        $sel.append('<option value="">No categories</option>');
      }
    });

    // brands
    $.getJSON('../Actions/fetch_brand_action.php', (res) => {
      const $sel = $('#product_brand').empty();
      if (res.status === 'success' && res.data?.length) {
        $sel.append('<option value="" disabled selected>Select brand</option>');
        res.data.forEach(b => $sel.append(`<option value="${b.brand_id}">${esc(b.brand_name)}</option>`));
      } else {
        $sel.append('<option value="">No brands</option>');
      }
    });
  }

  // Render table
  function renderRows(items) {
    if (!items || !items.length) {
      $rows.html('<tr><td colspan="5">No products yet — add one above.</td></tr>');
      return;
    }
    let html = '';
    items.forEach(p => {
      html += `
        <tr data-id="${p.product_id}">
          <td>
            <div style="display:flex;gap:10px;align-items:center">
              ${p.product_image ? `<img src="../${esc(p.product_image)}" alt="" style="width:44px;height:44px;border-radius:10px;object-fit:cover;border:1px solid rgba(255,255,255,.08)">` : ''}
              <div>
                <div style="font-weight:600">${esc(p.product_title)}</div>
                <small class="muted">${esc(p.product_desc || '')}</small>
              </div>
            </div>
          </td>
          <td>${esc(p.cat_name || '')} / ${esc(p.brand_name || '')}</td>
          <td>$${money(p.product_price)}</td>
          <td>${esc(p.product_keywords || '')}</td>
          <td class="ta-right">
            <button class="btn ghost edit-btn">Edit</button>
          </td>
        </tr>`;
    });
    $rows.html(html);
  }

  function loadAll() {
    $.getJSON('../Actions/fetch_product_action.php', (res) => {
      if (res.status !== 'success') return toast('error','Error',res.message||'Fetch failed');
      renderRows(res.data || []);
    }).fail(()=>toast('error','Error','Request failed'));
  }

  // Add / Update form
  $('#product-form').on('submit', function (e) {
    e.preventDefault();
    const fd = new FormData(this);
    const pid = $('#product_id').val().trim();

    const url = pid ? '../Actions/update_product_action.php' : '../Actions/add_product_action.php';

    $.ajax({
      url, type:'POST', data: fd, processData:false, contentType:false, dataType:'json',
      success: (res)=>{
        if (res.status === 'success') {
          toast('success', pid ? 'Updated' : 'Added', res.message || '');
          this.reset();
          $('#product_id').val('');
          $('#form-title').text('Add product');
          $('#product-submit').text('Add');
          $('#product-cancel').hide();
          loadAll();
        } else {
          toast('error','Error', res.message || 'Operation failed');
        }
      },
      error: ()=> toast('error','Error','Request failed')
    });
  });

  // Cancel edit
  $('#product-cancel').on('click', function(){
    $('#product-form')[0].reset();
    $('#product_id').val('');
    $('#form-title').text('Add product');
    $('#product-submit').text('Add');
    $(this).hide();
  });

  // Edit
  $rows.on('click','.edit-btn', function(){
    const id = $(this).closest('tr').data('id');
    $.getJSON('../Actions/get_product_action.php',{ product_id:id }, (res)=>{
      if (res.status !== 'success' || !res.data) return toast('error','Error',res.message||'Load failed');
      const p = res.data;

      $('#product_id').val(p.product_id);
      $('#product_title').val(p.product_title);
      $('#product_price').val(p.product_price);
      $('#product_keywords').val(p.product_keywords);
      $('#product_desc').val(p.product_desc);
      $('#product_cat').val(p.product_cat);
      $('#product_brand').val(p.product_brand);
      $('#form-title').text('Edit product');
      $('#product-submit').text('Save changes');
      $('#product-cancel').show();
      window.scrollTo({top:0, behavior:'smooth'});
    }).fail(()=>toast('error','Error','Request failed'));
  });

  // Bulk ZIP
  $('#bulk-form').on('submit', function(e){
    e.preventDefault();
    const fd = new FormData(this);
    $.ajax({
      url:'../Actions/bulk_product_zip_action.php', type:'POST', data:fd, processData:false, contentType:false, dataType:'json',
      success:res=>{
        if (res.status==='success') { toast('success','Bulk upload',res.message||'Done'); loadAll(); this.reset(); }
        else toast('error','Error',res.message||'Bulk failed');
      },
      error:()=> toast('error','Error','Request failed')
    });
  });

  loadCatsAndBrands();
  loadAll();
});
