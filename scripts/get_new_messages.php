<?php
session_start();
require_once 'config.php';

$other_user_id = $_GET['other_user_id'];
$last_message_id = $_GET['last_message_id'];

$stmt = $pdo->prepare("
    SELECT m.*, u.username as sender_name
    FROM messages m
    JOIN user u ON m.sender_id = u.id
    WHERE (sender_id = ? AND receiver_id = ?) 
       OR (sender_id = ? AND receiver_id = ?)
    AND m.id > ?
    ORDER BY created_at ASC
");
$stmt->execute([$user_id, $other_user_id, $other_user_id, $user_id, $last_message_id]);
$new_messages = $stmt->fetchAll();

foreach ($new_messages as $message) {
    echo '<div class="message ' . ($message['sender_id'] == $user_id ? 'sent' : 'received') . '">';
    echo '<strong>' . htmlspecialchars($message['sender_name']) . ':</strong>';
    echo '<p>' . htmlspecialchars($message['message']) . '</p>';
    echo '<small>' . $message['created_at'] . '</small>';
    echo '</div>';
}