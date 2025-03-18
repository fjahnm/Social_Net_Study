<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['is_creator']) || !$_SESSION['is_creator']) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    if ($user_id != $_SESSION['user_id']) {  // Impedir que O Criador delete a si mesmo
        $stmt = $pdo->prepare("DELETE FROM user WHERE id = ?");
        $stmt->execute([$user_id]);
    }
}

$stmt = $pdo->prepare("SELECT id, username FROM user WHERE is_creator = FALSE");
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Gerenciar Usuários</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Nome de Usuário</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td>
                    <form action="manage_users.php" method="POST" onsubmit="return confirm('Tem certeza que deseja deletar este usuário?');">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" name="delete_user">Deletar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>