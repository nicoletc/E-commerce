<?php
require_once __DIR__ . '/../Classes/brand_class.php';
require_once __DIR__ . '/../settings/core.php';

function add_brand_ctr(string $name): array {
    $name = trim($name);
    if ($name === '') return ['status'=>'error','message'=>'Brand name is required.'];

    $m = new Brand();
    if ($m->existsByName($name)) {
        return ['status'=>'error','message'=>'Brand already exists.'];
    }
    $id = $m->add($name);
    return $id ? ['status'=>'success','message'=>'Brand added.','brand_id'=>$id]
               : ['status'=>'error','message'=>'Could not add brand.'];
}

function update_brand_ctr(int $id, string $name): array {
    $name = trim($name);
    if ($id <= 0 || $name === '') return ['status'=>'error','message'=>'Invalid data.'];
    $m = new Brand();
    return $m->update($id, $name)
      ? ['status'=>'success','message'=>'Brand updated.']
      : ['status'=>'error','message'=>'Update failed.'];
}

function delete_brand_ctr(int $id): array {
    if ($id <= 0) return ['status'=>'error','message'=>'Invalid brand id.'];
    $m = new Brand();
    return $m->delete($id)
      ? ['status'=>'success','message'=>'Brand deleted.']
      : ['status'=>'error','message'=>'Delete failed.'];
}

function fetch_brands_ctr(): array {
    $m = new Brand();
    $rows = $m->listAll();
    return ['status'=>'success','data'=>$rows];
}
