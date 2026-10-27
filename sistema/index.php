<?php
require __DIR__ . '/config.php';
if (logged_in()) { header('Location: dashboard.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    try {
        $stmt = db()->prepare('SELECT id, nome, senha_hash FROM usuarios WHERE usuario = ? AND ativo = 1 LIMIT 1');
        $stmt->execute([$username]); $user = $stmt->fetch();
        if ($user && password_verify($password, $user['senha_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['last_activity'] = time();
            header('Location: dashboard.php'); exit;
        }
        $error = 'Usuário ou senha inválidos.';
    } catch (Throwable $e) { $error = 'Não foi possível conectar ao banco. Confira a configuração e importe saep_db.sql.'; }
}
?><!doctype html><html lang="pt-br"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Login • Assistência Técnica</title><link rel="stylesheet" href="assets/style.css"></head><body class="login"><main class="login-card"><h1>Assistência Técnica</h1><p>Acesse o sistema</p><?php if ($error) echo '<p class="error">'.h($error).'</p>'; ?><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><label>Usuário<input name="username" required autocomplete="username"></label><label>Senha<input type="password" name="password" required autocomplete="current-password"></label><button>Entrar</button></form><small>Ambiente local de avaliação</small></main></body></html>