<?php
session_start();
require_once 'config.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Create new community
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_community'])) {
    $name = sanitize_input($_POST['community_name']);
    $description = sanitize_input($_POST['community_description']);
    
    // Check if community already exists
    $stmt = $pdo->prepare("SELECT id FROM communities WHERE name = ?");
    $stmt->execute([$name]);
    if ($stmt->fetch()) {
        $error_message = "Comunidade já existente";
    } else {
        $stmt = $pdo->prepare("INSERT INTO communities (name, description, creator_id) VALUES (?, ?, ?)");
        $stmt->execute([$name, $description, $user_id]);
        $community_id = $pdo->lastInsertId();
        
        // Add creator as a member
        $stmt = $pdo->prepare("INSERT INTO community_members (community_id, user_id) VALUES (?, ?)");
        $stmt->execute([$community_id, $user_id]);
        
        header('Location: community.php?id=' . $community_id);
        exit;
    }
}

// Fetch all communities
$stmt = $pdo->prepare("SELECT * FROM communities ORDER BY name");
$stmt->execute();
$communities = $stmt->fetchAll();

// Fetch user's communities
$stmt = $pdo->prepare("
    SELECT c.* FROM communities c
    JOIN community_members cm ON c.id = cm.community_id
    WHERE cm.user_id = ?
");
$stmt->execute([$user_id]);
$user_communities = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comunidades</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main>
        <h1>Comunidades</h1>
        
        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <section id="create-community">
            <h2>Criar Nova Comunidade</h2>
            <form action="communities.php" method="POST">
                <input type="text" name="community_name" placeholder="Nome da Comunidade" required>
                <textarea name="community_description" placeholder="Descrição da Comunidade" required></textarea>
                <button type="submit" name="create_community">Criar Comunidade</button>
            </form>
        </section>

        <section id="user-communities">
            <h2>Suas Comunidades</h2>
            <ul>
                <?php foreach ($user_communities as $community): ?>
                    <li><a href="community.php?id=<?php echo $community['id']; ?>"><?php echo htmlspecialchars($community['name']); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section id="all-communities">
            <h2>Todas as Comunidades</h2>
            <ul>
                <?php foreach ($communities as $community): ?>
                    <li>
                        <h3><?php echo htmlspecialchars($community['name']); ?></h3>
                        <p><?php echo htmlspecialchars($community['description']); ?></p>
                        <a href="community.php?id=<?php echo $community['id']; ?>">Ver Comunidade</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </main>
</body>
</html>