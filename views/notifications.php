<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = 'Notificações';

// Função para obter o nome do usuário
function get_user_name($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT username FROM user WHERE id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['username'] : 'Usuário Desconhecido';
}

// Marcar todas as notificações como lidas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    $stmt = $pdo->prepare("UPDATE messages SET read_status = 'read' WHERE receiver_id = ?");
    $stmt->execute([$user_id]);
}

// Buscar mensagens não lidas agrupadas por remetente
$stmt = $pdo->prepare("
    SELECT 
        sender_id, 
        COUNT(*) as message_count, 
        MAX(created_at) as latest_message_time,
        GROUP_CONCAT(id) as message_ids
    FROM messages 
    WHERE receiver_id = ? AND read_status = 'unread'
    GROUP BY sender_id 
    ORDER BY latest_message_time DESC
");
$stmt->execute([$user_id]);
$grouped_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// AJAX para retornar a contagem de notificações não lidas
if (isset($_GET['action']) && $_GET['action'] === 'check_notifications') {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM messages WHERE receiver_id = ? AND read_status = 'unread'");
    $stmt->execute([$user_id]);
    $unread_count = $stmt->fetchColumn();

    // Retorna a quantidade de notificações não lidas em formato JSON
    echo json_encode(['count' => $unread_count]);
    exit;
}

include 'header.php'; // Cabeçalho contendo o ícone de notificações
?>

<main>
    <h1>Suas Notificações</h1>
    
    <form method="POST">
        <button type="submit" name="mark_all_read">Marcar todas como lidas</button>
    </form>

    <ul>
        <?php if (empty($grouped_messages)): ?>
            <li>Você não tem novas mensagens no momento.</li>
        <?php else: ?>
            <?php foreach ($grouped_messages as $message): ?>
                <?php 
                $sender_name = get_user_name($pdo, $message['sender_id']);
                $message_text = $message['message_count'] == 1 
                    ? "Você recebeu 1 mensagem não lida" 
                    : "Você recebeu {$message['message_count']} mensagens não lidas";
                $time = date('d/m/Y H:i', strtotime($message['latest_message_time']));
                ?>
                <li>
                    <a href="messages.php?user_id=<?php echo $message['sender_id']; ?>&message_ids=<?php echo $message['message_ids']; ?>">
                        <?php echo "{$message_text} de {$sender_name} ({$time})"; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</main>

<script>
    function updateNotificationCount() {
        fetch('notifications.php?action=check_notifications')
            .then(response => response.json())
            .then(data => {
                const notificationIcon = document.getElementById('notification-icon');
                const count = data.count;

                if (count > 0) {
                    notificationIcon.textContent = count > 99 ? '+99' : count;
                    notificationIcon.style.display = 'block';
                } else {
                    notificationIcon.style.display = 'none';
                }
            })
            .catch(error => console.error('Erro ao atualizar notificações:', error));
    }

    // Atualizar a contagem de notificações periodicamente
    updateNotificationCount();
    setInterval(updateNotificationCount, 30000); // A cada 30 segundos
</script>