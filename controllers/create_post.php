<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Verificar e sanitizar entrada
$content = trim($_POST['content']);
$image = isset($_FILES['image']) ? $_FILES['image'] : null;

if (empty($content)) {
    die('O conteúdo da postagem não pode estar vazio.');
}

// Inserir postagem no banco de dados
$stmt = $pdo->prepare("
    INSERT INTO posts (user_id, content, image, is_profile_post, created_at) 
    VALUES (?, ?, ?, 0, NOW())
");

$params = [
    $user_id,
    $content,
    $image ? $image['name'] : null
];

$stmt->execute($params);

// Se houver uma imagem, movê-la para a pasta de uploads
if ($image && $image['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/';
    $upload_file = $upload_dir . basename($image['name']);
    move_uploaded_file($image['tmp_name'], $upload_file);
}

header('Location: feed.php');
exit;
