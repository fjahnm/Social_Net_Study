<?php
session_start();
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luzes</title>
</head>
<body>
    <h1>Bem-vindo à Luzes</h1>

    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['username'])): ?>
        <p>Olá, <?php echo htmlspecialchars($_SESSION['username']); ?>! Você já está logado.</p>
        <a href="profile.php">Acesse seu perfil</a><br>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <p>Você ainda não está logado.</p>
        <a href="login.php">Fazer Login</a> ou <a href="register.php">Registrar-se</a>
    <?php endif; ?>
</body>
</html>