<?php
// vote.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'], $_POST['vote_type'])) {
    $post_id = $_POST['post_id'];
    $vote_type = $_POST['vote_type'];
    $user_id = $_SESSION['user_id']; // Supondo que o usuário esteja logado

    // Verificar se o usuário já votou neste post
    $stmt = $pdo->prepare("SELECT id FROM votes WHERE user_id = ? AND post_id = ?");
    $stmt->execute([$user_id, $post_id]);
    $existing_vote = $stmt->fetchColumn();

    if ($existing_vote) {
        // Atualizar o voto existente
        $stmt = $pdo->prepare("UPDATE votes SET vote_type = ? WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$vote_type, $user_id, $post_id]);
    } else {
        // Inserir novo voto
        $stmt = $pdo->prepare("INSERT INTO votes (user_id, post_id, vote_type) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $post_id, $vote_type]);
    }

    // Contar os votos atualizados
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN vote_type = 'upvote' THEN 1 ELSE -1 END), 0) as vote_count
        FROM votes
        WHERE post_id = ?
    ");
    $stmt->execute([$post_id]);
    $new_vote_count = $stmt->fetchColumn();

    // Retornar o novo contador de votos como resposta JSON
    echo json_encode([
        'success' => true,
        'new_vote_count' => $new_vote_count
    ]);
}