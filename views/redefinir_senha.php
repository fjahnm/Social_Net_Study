<?php
require_once 'config.php';
include 'header_noacconunt.php';

$message = '';

if (isset($_GET['token'])) {
    $token = sanitize_input($_GET['token']);

    $stmt = $pdo->prepare('SELECT * FROM user WHERE reset_token = ? AND reset_token_expiry > NOW()');
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $message = "Token inválido ou expirado.";
    }
} else {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $message = "As senhas não coincidem.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $update_stmt = $pdo->prepare('UPDATE user SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?');
        if ($update_stmt->execute([$password_hash, $user['id']])) {
            $message = "Senha redefinida com sucesso. Você pode agora fazer login com sua nova senha.";
        } else {
            $message = "Erro ao redefinir a senha. Por favor, tente novamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
</head>
<body>
    <h2>Redefinir Senha</h2>
    <?php
    if (!empty($message)) {
        echo "<p>" . htmlspecialchars($message) . "</p>";
    }
    ?>
    <?php if (empty($message) || strpos($message, "Erro") !== false): ?>
    <form action="redefinir_senha.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
        <label for="password">Nova Senha:</label>
        <input type="password" id="password" name="password" required><br>

        <label for="confirm_password">Confirmar Nova Senha:</label>
        <input type="password" id="confirm_password" name="confirm_password" required><br>

        <button type="submit">Redefinir Senha</button>
    </form>
    <?php endif; ?>
    <a href="login.php">Voltar para o Login</a>
</body>
</html>