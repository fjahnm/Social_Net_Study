<?php
session_start();
require_once 'config.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    
    // Verificar a senha
    $stmt = $pdo->prepare("SELECT password FROM user WHERE id = ?");
    $stmt->execute([$user_id]);
    $hash = $stmt->fetchColumn();
    
    if (password_verify($password, $hash)) {
        // Deletar todos os dados do usuário
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("DELETE FROM posts WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $stmt = $pdo->prepare("DELETE FROM community_members WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $stmt = $pdo->prepare("DELETE FROM messages WHERE sender_id = ? OR receiver_id = ?");
            $stmt->execute([$user_id, $user_id]);
            $stmt = $pdo->prepare("DELETE FROM user WHERE id = ?");
            $stmt->execute([$user_id]);
            $pdo->commit();
            
            session_destroy();
            header('Location: login.php?message=Conta deletada com sucesso');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erro ao deletar conta. Tente novamente.";
        }
    } else {
        $error = "Senha incorreta";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deletar Conta</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Deletar Conta</h1>
    <p>Atenção: Esta ação é permanente e não pode ser desfeita!</p>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form action="delete_account.php" method="POST">
        <label for="password">Confirme sua senha:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Deletar Conta</button>
    </form>
    <a href="profile.php">Cancelar</a>
</body>
</html>