<?php
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
ini_set('display_errors','1'); error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../Controllers/customer_controller.php';

    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['status'=>'error','message'=>'Invalid JSON payload']); exit;
    }

    $res = login_customer_ctr($data);

    if (($res['status'] ?? '') === 'success') {
        $u = $res['user'];

                $_SESSION['customer_id']    = $u['customer_id'];
        $_SESSION['user_role']      = $u['user_role'];       // 1 admin, 2 customer
        $_SESSION['customer_name']  = $u['customer_name'];
        $_SESSION['customer_email'] = $u['customer_email'];

                // Migrate any guest cart into the logged-in customer's cart
                try {
                    require_once __DIR__ . '/../Controllers/cart_controller.php';
                    migrate_guest_cart_ctr($u['customer_id']);
                } catch (Throwable $e) {
                    // ignore migration errors; do not block login
                }

        http_response_code(200);
        echo json_encode(['status'=>'success','message'=>'Login successful.']);
    } else {
        http_response_code(401);
        echo json_encode($res);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Server error: '.$e->getMessage()]);
}
