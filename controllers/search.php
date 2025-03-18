<?php
require_once 'config.php';

if (isset($_GET['query'])) {
    $query = '%' . $_GET['query'] . '%';

    // Buscar usuários
    $stmt = $pdo->prepare("SELECT * FROM user WHERE username LIKE ?");
    $stmt->execute([$query]);
    $user = $stmt->fetchAll();

    // Buscar comunidades
    $stmt = $pdo->prepare("SELECT * FROM communities WHERE name LIKE ?");
    $stmt->execute([$query]);
    $communities = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Resultado da Busca</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Resultado da Busca</h1>

        <h2>Usuários</h2>
        <ul>
            <?php if (empty($user)): ?>
                <li>Nenhum usuário encontrado.</li>
            <?php else: ?>
                <?php foreach ($user as $user): ?>
                    <li><?php echo htmlspecialchars($user['username']); ?></li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>

        <h2>Comunidades</h2>
        <ul>
            <?php if (empty($communities)): ?>
                <li>Nenhuma comunidade encontrada.</li>
            <?php else: ?>
                <?php foreach ($communities as $community): ?>
                    <li><?php echo htmlspecialchars($community['name']); ?></li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</body>
</html>
