<?php
require_once 'config.php';

$message = '';

if (isset($_GET['code'])) {
    $verification_code = sanitize_input($_GET['code']);

    $stmt = $pdo->prepare('SELECT * FROM pending_registrations WHERE verification_code = ? AND expires_at > NOW()');
    $stmt->execute([$verification_code]);
    $pending_user = $stmt->fetch();

    if ($pending_user) {
        // Inserir na tabela de usuários
        $stmt = $pdo->prepare('INSERT INTO user (username, email, password, is_verified) VALUES (?, ?, ?, 1)');
        if ($stmt->execute([$pending_user['username'], $pending_user['email'], $pending_user['password']])) {
            // Remover da tabela de registros pendentes
            $stmt = $pdo->prepare('DELETE FROM pending_registrations WHERE id = ?');
            $stmt->execute([$pending_user['id']]);

            $message = "E-mail verificado com sucesso! Agora você pode fazer login.";
        } else {
            $message = "Erro ao verificar o e-mail. Por favor, tente novamente mais tarde.";
        }
    } else {
        $message = "Código de verificação inválido ou expirado.";
    }
} else {
    $message = "Nenhum código de verificação fornecido.";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de E-mail</title>
</head>
<body>
    <h2>Verificação de E-mail</h2>
    <p><?php echo htmlspecialchars($message); ?></p>
    <a href="login.php">Ir para a página de login</a>
</body>
</html>