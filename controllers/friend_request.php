<?php
session_start();
require_once 'config.php';
include 'header.php';

// Verifique se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Consulta para obter as solicitações de amizade pendentes junto com as informações do remetente
$stmt = $pdo->prepare("
    SELECT fr.*, u.username, u.profile_picture, u.description
    FROM friend_requests fr
    JOIN user u ON fr.sender_id = u.id
    WHERE fr.receiver_id = ? AND fr.status = 'pending'
");
$stmt->execute([$user_id]);

// Fetch all the requests into an array
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verifique se há solicitações
if (!$requests) {
    $requests = []; // Inicializa como array vazio se não houver solicitações
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitações de Amizade</title>
    <link rel="stylesheet" href="style.css"> <!-- Inclui o CSS -->
</head>
<body>

<main>
    <h1>Solicitações de Amizade</h1>

    <?php if (count($requests) > 0): ?>
        <ul class="friend-request-list">
            <?php foreach ($requests as $request): ?>
                <li class="friend-request-item">
                    <div class="friend-request-content">
                        <img src="<?php echo htmlspecialchars($request['profile_picture']); ?>" alt="Foto de perfil" class="profile-pic-small">
                        <div class="friend-request-info">
                            <h2><?php echo htmlspecialchars($request['username']); ?></h2>
                            <p><?php echo htmlspecialchars($request['description']); ?></p>
                            <div class="friend-request-actions">
                                <form method="POST" action="handle_friend_request.php">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                    <button type="submit" name="action" value="accept">Aceitar</button>
                                    <button type="submit" name="action" value="reject">Rejeitar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Você não tem novas solicitações de amizade.</p>
    <?php endif; ?>

</main>

</body>
</html>