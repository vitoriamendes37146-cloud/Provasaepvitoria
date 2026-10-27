<?php
declare(strict_types=1);
session_start();
date_default_timezone_set('America/Recife');

const DB_HOST = '127.0.0.1';
const DB_PORT = 3307;
const DB_NAME = 'saep_db';
const DB_USER = 'root';
const DB_PASS = '';
// Troque esta chave antes de usar fora do ambiente local de demonstração.
const APP_KEY = 'troque-esta-chave-demo-2026-32bytes';

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}
function h(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403); exit('Token inválido. Atualize a página e tente novamente.');
    }
}
function logged_in(): bool {
    if (empty($_SESSION['user_id'])) return false;
    if (time() - (int)($_SESSION['last_activity'] ?? 0) > 900) {
        session_unset(); session_destroy(); return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}
function require_login(): void {
    if (!logged_in()) { header('Location: index.php'); exit; }
}
function flash(string $message): void { $_SESSION['flash'] = $message; }
function get_flash(): string {
    $msg = (string)($_SESSION['flash'] ?? '');
    unset($_SESSION['flash']);
    return $msg;
}
function cpf_encrypt(string $cpf): string {
    $key = hash('sha256', APP_KEY, true);
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($cpf, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $encrypted);
}
function cpf_decrypt(string $value): string {
    $raw = base64_decode($value, true);
    if ($raw === false || strlen($raw) < 17) return '';
    $key = hash('sha256', APP_KEY, true);
    return (string)openssl_decrypt(substr($raw,16), 'AES-256-CBC', $key, OPENSSL_RAW_DATA, substr($raw,0,16));
}
function cpf_hash(string $cpf): string { return hash_hmac('sha256', preg_replace('/\D+/', '', $cpf), APP_KEY); }
function page_header(string $title): void {
    $flash = get_flash();
    echo '<!doctype html><html lang="pt-br"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>'.h($title).'</title><link rel="stylesheet" href="assets/style.css"></head><body><header><strong>Assistência Técnica</strong><nav><a href="dashboard.php">Início</a><a href="clientes.php">Clientes</a><a href="equipamentos.php">Equipamentos</a><a href="ordens.php">Ordens de serviço</a><a href="logout.php">Sair</a></nav></header><main><h1>'.h($title).'</h1>';
    if ($flash !== '') echo '<p class="flash">'.h($flash).'</p>';
}
function page_footer(): void { echo '</main><footer>Sistema acadêmico • Vitória Mendes</footer></body></html>'; }
