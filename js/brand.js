/* js/brand.js */
$(function () {
  const $form = $('#brand-form');
  const $name = $('#brand-name');
  const $rows = $('#brand-rows');

  // ------------ helpers ------------
  function toast(icon, title, text) {
    return Swal.fire({ icon, title, text });
  }
  function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, m => ({
      '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'
    }[m]));
  }
  function escapeAttr(s){ return escapeHtml(s).replace(/"/g,'&quot;'); }

  // ------------ render ------------
  function renderRows(items) {
    if (!items || !items.length) {
      $rows.html('<tr><td colspan="2">No brands yet — add one above.</td></tr>');
      return;
    }
    let html = '';
    items.forEach(r => {
      html += `
        <tr data-id="${r.brand_id}">
          <td>
            <span class="bname">${escapeHtml(r.brand_name)}</span>
            <input class="edit-inp input" type="text"
                   value="${escapeAttr(r.brand_name)}"
                   style="display:none; max-width:360px;">
          </td>
          <td class="actions">
            <button class="btn btn--alt edit-btn">Edit</button>
            <button class="btn save-btn" style="display:none;">Save</button>
            <button class="btn btn--alt cancel-btn" style="display:none;">Cancel</button>
            <button class="btn del-btn">Delete</button>
          </td>
        </tr>`;
    });
    $rows.html(html);
    // admin.css animates tbody tr via @keyframes listIn
  }

  // ------------ data ------------
  function loadAll() {
    $.getJSON('../Actions/fetch_brand_action.php', res => {
      if (res.status !== 'success')
        return toast('error','Error', res.message || 'Fetch failed.');
      renderRows(res.data || []);
    }).fail(() => toast('error','Error','Request failed'));
  }

  // ------------ create ------------
  $form.on('submit', function (e) {
    e.preventDefault();
    const name = $name.val().trim();
    if (!name) return toast('error','Invalid','Brand name is required.');
    $.ajax({
      url: '../Actions/add_brand_action.php',
      type: 'POST',
      dataType: 'json',
      contentType: 'application/json',
      data: JSON.stringify({ brand_name: name }),
      success: res => {
        if (res.status === 'success') {
          $name.val('');
          loadAll();
          toast('success','Added','Brand added.');
        } else {
          toast('error','Error', res.message || 'Add failed.');
        }
      },
      error: () => toast('error','Error','Request failed')
    });
  });

  // ------------ edit UX ------------
  $rows.on('click', '.edit-btn', function () {
    const $tr = $(this).closest('tr');
    $tr.find('.bname, .edit-btn, .del-btn').hide();
    $tr.find('.edit-inp, .save-btn, .cancel-btn').show().first().focus();
  });

  $rows.on('click', '.cancel-btn', function () {
    const $tr  = $(this).closest('tr');
    const val  = $tr.find('.bname').text();
    $tr.find('.edit-inp').val(val);
    $tr.find('.bname, .edit-btn, .del-btn').show();
    $tr.find('.edit-inp, .save-btn, .cancel-btn').hide();
  });

  $rows.on('click', '.save-btn', function () {
    const $tr = $(this).closest('tr');
    const id  = parseInt($tr.data('id'), 10);
    const val = $tr.find('.edit-inp').val().trim();
    if (!val) return toast('error','Invalid','Name cannot be empty.');
    $.ajax({
      url: '../Actions/update_brand_action.php',
      type: 'POST',
      dataType: 'json',
      contentType: 'application/json',
      data: JSON.stringify({ brand_id: id, brand_name: val }),
      success: res => {
        if (res.status === 'success') {
          $tr.find('.bname').text(val);
          $tr.find('.bname, .edit-btn, .del-btn').show();
          $tr.find('.edit-inp, .save-btn, .cancel-btn').hide();
          toast('success','Updated','Brand updated.');
        } else {
          toast('error','Error', res.message || 'Update failed.');
        }
      },
      error: () => toast('error','Error','Request failed')
    });
  });

  // ------------ delete ------------
  $rows.on('click', '.del-btn', function () {
    const $tr = $(this).closest('tr');
    const id  = parseInt($tr.data('id'), 10);
    Swal.fire({
      icon:'warning',
      title:'Delete brand?',
      text:'This cannot be undone.',
      showCancelButton:true,
      confirmButtonText:'Delete',
      confirmButtonColor:'#e74c3c'
    }).then(ok => {
      if (!ok.isConfirmed) return;
      $.ajax({
        url: '../Actions/delete_brand_action.php',
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify({ brand_id: id }),
        success: res => {
          if (res.status === 'success') {
            $tr.remove();
            toast('success','Deleted','Brand removed.');
            // if table is empty, show placeholder row
            if (!$rows.children('tr').length) {
              $rows.html('<tr><td colspan="2">No brands yet — add one above.</td></tr>');
            }
          } else {
            toast('error','Error', res.message || 'Delete failed.');
          }
        },
        error: () => toast('error','Error','Request failed')
      });
    });
  });

  loadAll();
});
