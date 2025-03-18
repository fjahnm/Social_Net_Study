<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];

// Verificar se a solicitação já existe
$stmt = $pdo->prepare("SELECT * FROM friend_requests WHERE sender_id = ? AND receiver_id = ?");
$stmt->execute([$user_id, $receiver_id]);
$request = $stmt->fetch();

if (!$request) {
    // Inserir a solicitação de amizade
    $stmt = $pdo->prepare("INSERT INTO friend_requests (sender_id, receiver_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $receiver_id]);

    header('Location: profile.php?user_id=' . $receiver_id);
    exit;
} else {
    echo "Já mandou solicitação pra este usuário.";
}