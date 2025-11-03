$(function () {
  const $rows = $('#prod-rows');

  // Helpers
  const toast = (icon, title, text='') => Swal.fire({icon,title,text});
  const esc   = s => String(s ?? '').replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;' }[m]));
  const money = v => (Number(v)||0).toFixed(2);

  function loadCatsAndBrands() {
    $.getJSON('../Actions/fetch_category_action.php', (res) => {
      const $sel = $('#product_cat').empty();
      if (res.status === 'success' && res.data?.length) {
        $sel.append('<option value="" disabled selected>Select category</option>');
        res.data.forEach(c => $sel.append(`<option value="${c.cat_id}">${esc(c.cat_name)}</option>`));
      } else {
        $sel.append('<option value="">No categories</option>');
      }
    });

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

  function renderRows(items) {
    if (!items || !items.length) {
      $rows.html('<tr><td colspan="5">No products yet — add one above.</td></tr>');
      return;
    }
    let html = '';
    items.forEach(p => {
      const thumb = p.product_image
        ? `<img src="../${esc(p.product_image)}" alt="" style="width:44px;height:44px;border-radius:10px;object-fit:cover;border:1px solid rgba(255,255,255,.08)">`
        : '';
      html += `
        <tr data-id="${p.product_id}">
          <td>
            <div style="display:flex;gap:10px;align-items:center">
              ${thumb}
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

  $('#product-form').on('submit', function (e) {
    e.preventDefault();

    const fd  = new FormData(this);
    const pid = ($('#product_id').val() || '').trim();
    const url = pid ? '../Actions/update_product_action.php' : '../Actions/add_product_action.php';

    $.ajax({
      url,
      type:'POST',
      data: fd,
      processData:false,
      contentType:false,
      dataType:'json'
    }).done((res)=>{
      if (res.status !== 'success') {
        return toast('error','Error', res.message || 'Operation failed');
      }

      const productId = pid ? parseInt(pid,10) : parseInt(res.product_id,10);
      const file = ($('#product_image')[0] && $('#product_image')[0].files) ? $('#product_image')[0].files[0] : null;

      if (!file || !productId) {
        toast('success', pid ? 'Updated' : 'Added', res.message || '');
        this.reset();
        $('#product_id').val('');
        $('#form-title').text('Add product');
        $('#product-submit').text('Add');
        $('#product-cancel').hide();
        loadAll();
        return;
      }

      const up = new FormData();
      up.append('product_id', productId);
      up.append('image', file);

      $.ajax({
        url:'../Actions/upload_product_image_action.php',
        type:'POST',
        data: up,
        processData:false,
        contentType:false,
        dataType:'json'
      }).done(u=>{
        if (u.status === 'success') {
          toast('success', pid ? 'Updated' : 'Added', 'Image saved.');
        } else {
          toast('warning','Saved (no image)', u.message || 'Image upload failed.');
        }
        $('#product-form')[0].reset();
        $('#product_id').val('');
        $('#form-title').text('Add product');
        $('#product-submit').text('Add');
        $('#product-cancel').hide();
        loadAll();
      }).fail(()=>{
        toast('warning','Saved (no image)','Image upload failed.');
        $('#product-form')[0].reset();
        $('#product_id').val('');
        $('#form-title').text('Add product');
        $('#product-submit').text('Add');
        $('#product-cancel').hide();
        loadAll();
      });

    }).fail(()=> toast('error','Error','Request failed'));
  });


  $('#product-cancel').on('click', function(){
    $('#product-form')[0].reset();
    $('#product_id').val('');
    $('#form-title').text('Add product');
    $('#product-submit').text('Add');
    $(this).hide();
  });


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

  $(function () {
  $('#bulk-form').on('submit', function(e){
    e.preventDefault();
    const fd = new FormData(this);
    $.ajax({
      url: '../Actions/bulk_product_zip_action.php',
      type: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: (res)=>{
        if (res.status === 'success') {
          const lines = (res.rows || []).map(r => `${r.row}: ${r.status.toUpperCase()} — ${r.message}`).join('\n');
          Swal.fire({icon:'success', title:'Bulk upload', html:`<pre style="text-align:left;white-space:pre-wrap">${lines}</pre>`, width:700});
          if (typeof loadAll === 'function') loadAll();
          this.reset();
        } else {
          Swal.fire({icon:'error', title:'Bulk upload failed', text: res.message || 'Unknown error'});
        }
      },
      error: (xhr)=> Swal.fire({icon:'error', title:'Request failed', text: xhr.responseText || 'Network error'})
    });
  });
});


  // Init
  loadCatsAndBrands();
  loadAll();
});
