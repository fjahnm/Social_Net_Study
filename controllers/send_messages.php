<?php
session_start();
require_once 'config.php';

// Função de sanitização para proteger contra XSS e SQL Injection
function sanitize_input($data) {
    return htmlspecialchars(trim($data));  // Remove espaços e caracteres especiais
}

// Verificar se o usuário está logado e se todos os dados necessários foram enviados
if (!isset($_SESSION['user_id']) || !isset($_POST['receiver_id']) || !isset($_POST['message_content'])) {
    echo json_encode(['status' => 'error', 'message' => 'Dados inválidos']);
    exit;
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];
$message_content = sanitize_input($_POST['message_content']);

// Inserir a nova mensagem no banco de dados
try {
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$sender_id, $receiver_id, $message_content]);

    // Obter o ID da mensagem recém-inserida
    $message_id = $pdo->lastInsertId();

    // Obter o nome do remetente para a notificação
    $stmt = $pdo->prepare("SELECT username FROM user WHERE id = ?");
    $stmt->execute([$sender_id]);
    $sender_name = $stmt->fetchColumn();

    // Criar notificação para o receptor
    $notification_message = $sender_name . " enviou uma nova mensagem";
    $notification_link = "messages.php?user_id=" . $sender_id;
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
    $stmt->execute([$receiver_id, $notification_message, $notification_link]);

    // Buscar a mensagem recém-criada para retornar ao cliente
    $stmt = $pdo->prepare("
        SELECT m.*, u.username as sender_name
        FROM messages m
        JOIN user u ON m.sender_id = u.id
        WHERE m.id = ?
    ");
    $stmt->execute([$message_id]);
    $new_message = $stmt->fetch(PDO::FETCH_ASSOC);

    // Formatar a mensagem para retornar ao cliente
    $formatted_message = [
        'id' => $new_message['id'],
        'sender_id' => $new_message['sender_id'],
        'sender_name' => $new_message['sender_name'],
        'message' => $new_message['message'],
        'created_at' => $new_message['created_at']
    ];

    // Retornar a mensagem formatada como JSON
    echo json_encode(['status' => 'success', 'message' => $formatted_message]);

} catch (PDOException $e) {
    // Em caso de erro no banco, exibe uma mensagem de erro
    echo json_encode(['status' => 'error', 'message' => 'Erro no banco de dados: ' . $e->getMessage()]);
}
?>