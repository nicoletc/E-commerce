<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


ob_start();

const APP_BASE   = '/lab_2';                
const PATH_LOGIN = 'view/login.php';        
const PATH_HOME  = 'index.php';            


const SESS_USER_ID   = 'customer_id';       
const SESS_USER_ROLE = 'user_role';         
const ROLE_ADMIN     = 1;
const ROLE_CUSTOMER  = 2;


function app_url(string $path): string {
    return APP_BASE . '/' . ltrim($path, '/');
}

function redirect(string $path): void {

    if (preg_match('~^https?://~i', $path)) {
        header('Location: ' . $path);
    } else {
        header('Location: ' . app_url($path));
    }
    exit;
}

function json_response(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}


function is_logged_in(): bool {
    return !empty($_SESSION[SESS_USER_ID]);
}
function is_admin(): bool {
    return isset($_SESSION[SESS_USER_ROLE]) && (int)$_SESSION[SESS_USER_ROLE] === ROLE_ADMIN;
}
function has_role(int $role): bool {
    return isset($_SESSION[SESS_USER_ROLE]) && (int)$_SESSION[SESS_USER_ROLE] === $role;
}


function require_role(array $allowed, ?string $to = null): void {
    if (!is_logged_in()) {
        redirect($to ?? PATH_LOGIN);
    }
    if (!in_array(current_user_role(), $allowed, true)) {
        redirect(PATH_HOME);
    }
}

function require_admin(): void {
    if (!is_logged_in()) {
        header('Location: ../view/login.php'); 
        exit;
    }
    if (!is_admin()) {
        header('Location: ../index.php'); 
        exit;
    }
}