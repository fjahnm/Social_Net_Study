<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['post_id'])) {
    header('Location: profile.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];

$stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$post_id, $user_id]);

header('Location: profile.php');
exit;
?>