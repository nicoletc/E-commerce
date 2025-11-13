<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start();

/* Paths */
const APP_BASE   = '/lab_2';
const PATH_LOGIN = 'view/login.php';
const PATH_HOME  = 'index.php';

/* Session keys & roles */
const SESS_USER_ID   = 'customer_id';
const SESS_USER_ROLE = 'user_role';
const ROLE_ADMIN     = 1;
const ROLE_CUSTOMER  = 2;

/* URL helper */
function app_url(string $path): string {
    return APP_BASE . '/' . ltrim($path, '/');
}

/* Redirect helper */
function redirect(string $path): void {
    if (preg_match('~^https?://~i', $path)) {
        header('Location: ' . $path);
    } else {
        header('Location: ' . app_url($path));
    }
    exit;
}

/* JSON responder */
function json_response(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json;');
    echo json_encode($data);
    exit;
}

/* Auth / role helpers */
function is_logged_in(): bool {
    return !empty($_SESSION[SESS_USER_ID]);
}
function current_user_role(): int {
    return isset($_SESSION[SESS_USER_ROLE]) ? (int)$_SESSION[SESS_USER_ROLE] : ROLE_CUSTOMER;
}
function is_admin(): bool {
    return current_user_role() === ROLE_ADMIN;
}
function has_role(int $role): bool {
    return current_user_role() === $role;
}

/* Guards */
function require_login(?string $to = null): void {
    if (!is_logged_in()) {
        redirect($to ?? PATH_LOGIN);
    }
}
function require_role(array $allowed, ?string $to = null): void {
    require_login($to);
    if (!in_array(current_user_role(), $allowed, true)) {
        redirect(PATH_HOME);
    }
}
function require_admin(): void {
    require_login(PATH_LOGIN);
    if (!is_admin()) {
        redirect(PATH_HOME);
    }
}
