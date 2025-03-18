<?php
session_start();
require_once 'config.php';
include 'header.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$community_id = $_GET['id'];

// Fetch community details
$stmt = $pdo->prepare("SELECT * FROM communities WHERE id = ?");
$stmt->execute([$community_id]);
$community = $stmt->fetch();

if (!$community) {
    header('Location: communities.php');
    exit;
}

// Check if user is a member
$stmt = $pdo->prepare("SELECT * FROM community_members WHERE community_id = ? AND user_id = ?");
$stmt->execute([$community_id, $user_id]);
$is_member = $stmt->fetch();

// Handle join request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_community'])) {
    $stmt = $pdo->prepare("INSERT INTO community_members (community_id, user_id) VALUES (?, ?)");
    $stmt->execute([$community_id, $user_id]);
    header('Location: community.php?id=' . $community_id);
    exit;
}

// Handle new post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_post'])) {
    $content = sanitize_input($_POST['content']);
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, community_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $community_id, $content]);
    header('Location: community.php?id=' . $community_id);
    exit;
}

// Fetch community posts
$stmt = $pdo->prepare("
    SELECT p.*, u.username 
    FROM posts p
    JOIN user u ON p.user_id = u.id
    WHERE p.community_id = ?
    ORDER BY p.created_at DESC
");

function isAdmin($pdo, $community_id, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM community_admins WHERE community_id = ? AND user_id = ?");
    $stmt->execute([$community_id, $user_id]);
    return $stmt->fetch() !== false;
}

function isCreator($pdo, $community_id, $user_id) {
    $stmt = $pdo->prepare("SELECT creator_id FROM communities WHERE id = ?");
    $stmt->execute([$community_id]);
    $creator_id = $stmt->fetchColumn();
    return $creator_id == $user_id;
}

if (isAdmin($pdo, $community_id, $user_id) || isCreator($pdo, $community_id, $user_id)) {
    echo '<h3>Ações de Administrador</h3>';
    echo '<button onclick="acceptMember()">Aceitar Membro</button>';
    echo '<button onclick="removeMember()">Remover Membro</button>';
    echo '<button onclick="deletePost()">Deletar Post</button>';
    echo '<button onclick="pinPost()">Fixar Mensagem</button>';
    
    if (isCreator($pdo, $community_id, $user_id)) {
        echo '<button onclick="deleteCommunity()">Deletar Comunidade</button>';
        echo '<button onclick="renameCommunity()">Renomear Comunidade</button>';
        echo '<button onclick="promoteAdmin()">Promover a Administrador</button>';
    }
}

$stmt->execute([$community_id]);
$posts = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($community['name']); ?> - Comunidade</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <main>
        <h1><?php echo htmlspecialchars($community['name']); ?></h1>
        <p><?php echo htmlspecialchars($community['description']); ?></p>

        <?php if (!$is_member): ?>
            <form action="community.php?id=<?php echo $community_id; ?>" method="POST">
                <button type="submit" name="join_community">Solicitar Participação</button>
            </form>
        <?php else: ?>
            <section id="new-post">
                <h2>Nova Postagem</h2>
                <form action="community.php?id=<?php echo $community_id; ?>" method="POST">
                    <textarea name="content" placeholder="O que você quer compartilhar?" required></textarea>
                    <button type="submit" name="new_post">Postar</button>
                </form>
            </section>

            <section id="community-posts">
                <h2>Postagens da Comunidade</h2>
                <?php foreach ($posts as $post): ?>
                    <article class="post">
                        <h3><?php echo htmlspecialchars($post['username']); ?></h3>
                        <p><?php echo htmlspecialchars($post['content']); ?></p>
                        <small>Postado em: <?php echo $post['created_at']; ?></small>
                        <!-- Add voting buttons here -->
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>