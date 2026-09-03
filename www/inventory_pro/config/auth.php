<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function require_login(): void {
    if (empty($_SESSION['user'])) {
        header('Location: /inventory_pro/login.php');
        exit;
    }
}

function require_role(array $roles): void {
    require_login();
    if (!in_array($_SESSION['user']['role'] ?? '', $roles, true)) {
        http_response_code(403);
        exit('Access denied.');
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function check_csrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('Invalid security token.');
    }
}
