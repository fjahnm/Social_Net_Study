<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

echo "Bem-Vindo," $_SESSION['username'] . "!";
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta chatset="UTF-8">
        <meta name="viewport" content="windth=device-width, initial-scale=1.0">
        <title>Inicio</title>
</head>
<body>
    <h2>Tela inicial</h2>
    <p>Bem-vindo(a) à tela inicial, <?php echo $_SESSION['username']; ?>!</p>
    <a href="logout.php">Logout</a>
</body>
</html>