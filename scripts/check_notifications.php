<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['message_count' => 0, 'friend_request_count' => 0]);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM messages WHERE receiver_id = ? AND read_status = 'unread'");
$stmt->execute([$user_id]);
$message_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM friend_requests WHERE receiver_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$friend_request_count = $stmt->fetchColumn();

$message_count = min($message_count, 99);
$friend_request_count = min($friend_request_count, 99);

$stmt = $pdo->prepare("
    SELECT n.*, u.username, m.content AS message_content
    FROM notifications n
    LEFT JOIN user u ON n.sender_id = u.id
    LEFT JOIN messages m ON n.message_id = m.id
    WHERE n.user_id = ? AND n.is_read = FALSE
    ORDER BY n.created_at DESC
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

$notificationData = [];
foreach ($notifications as $notification) {
    $notificationText = '';
    $link = '';

    switch ($notification['type']) {
        case 'message':
            $messageCount = 1;
            foreach ($notifications as $n) {
                if ($n['type'] === 'message' && $n['sender_id'] === $notification['sender_id'] && $n['id'] !== $notification['id']) {
                    $messageCount++;
                }
            }
            $notificationText = $notification['username'] . " enviou " . $messageCount . " mensagem" . ($messageCount > 1 ? "ns" : "");
            $link = "messages.php?user_id=" . $notification['sender_id'];
            break;

    }

    $notificationData[] = [
        'text' => $notificationText,
        'link' => $link
    ];
}

echo json_encode([
    'message_count' => $message_count,
    'friend_request_count' => $friend_request_count,
    'notifications' => $notificationData
]);
?>
