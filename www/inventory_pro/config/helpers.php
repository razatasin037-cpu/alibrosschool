<?php
function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function money($value): string {
    return '₹' . number_format((float)$value, 2);
}
function redirect(string $url): never {
    header("Location: $url");
    exit;
}
function flash(string $type, string $message): void {
    $_SESSION['flash'] = [$type, $message];
}
function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        [$type,$message] = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert '.$type.'">'.e($message).'</div>';
    }
}
function setting(PDO $pdo, string $field, string $default=''): string {
    $row = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch();
    return $row[$field] ?? $default;
}
