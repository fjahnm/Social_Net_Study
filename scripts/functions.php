<?php

function redirect($url) {
    header("Location: " . BASE_URL . "/" . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}


function getUserById($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

function updateProfile($userId, $postData, $files) {
    global $pdo;
    $description = sanitizeInput($postData['description']);
    $stmt = $pdo->prepare("UPDATE user SET description = ? WHERE id = ?");
    $stmt->execute([$description, $userId]);
    
    if (isset($files['profile_picture']) && $files['profile_picture']['error'] == 0) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $files['profile_picture']['name'];
        $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFilename = "profile_" . $userId . "." . $fileExtension;
            move_uploaded_file($files['profile_picture']['tmp_name'], 'uploads/' . $newFilename);
            $stmt = $pdo->prepare("UPDATE user SET profile_picture = ? WHERE id = ?");
            $stmt->execute([$newFilename, $userId]);
        }
    }
}

function createPost($userId, $content, $communityId = null) {
    global $pdo;
    $content = sanitizeInput($content);
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, community_id) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $content, $communityId]);
}

function getUserPosts($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getProfilePosts($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? AND community_id IS NULL ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getCommunityPosts($userId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as community_name 
        FROM posts p
        JOIN communities c ON p.community_id = c.id
        WHERE p.user_id = ? AND p.community_id IS NOT NULL
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getFriendRequest($senderId, $receiverId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM friend_requests 
        WHERE (sender_id = ? AND receiver_id = ?) 
        OR (sender_id = ? AND receiver_id = ?)
    ");
    $stmt->execute([$senderId, $receiverId, $receiverId, $senderId]);
    return $stmt->fetch();
}

function getUserFriends($userId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.profile_picture 
        FROM user u
        JOIN friend_requests fr ON (fr.sender_id = u.id OR fr.receiver_id = u.id)
        WHERE (fr.sender_id = ? OR fr.receiver_id = ?) 
        AND fr.status = 'accepted'
        AND u.id != ?
    ");
    $stmt->execute([$userId, $userId, $userId]);
    return $stmt->fetchAll();
}

function getCommunities() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM communities ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getUserCommunities($userId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT c.* FROM communities c
        JOIN community_members cm ON c.id = cm.community_id
        WHERE cm.user_id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getConversations($userId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT DISTINCT 
            CASE 
                WHEN sender_id = ? THEN receiver_id 
                ELSE sender_id 
            END AS other_user_id,
            u.username
        FROM messages m
        JOIN user u ON u.id = CASE 
            WHEN sender_id = ? THEN receiver_id 
            ELSE sender_id 
        END
        WHERE sender_id = ? OR receiver_id = ?
    ");
    $stmt->execute([$userId, $userId, $userId, $userId]);
    return $stmt->fetchAll();
}

function getMessages($userId, $otherUserId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT m.*, u.username as sender_name
        FROM messages m
        JOIN user u ON m.sender_id = u.id
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?)
        ORDER BY created_at ASC
    ");
    $stmt->execute([$userId, $otherUserId, $otherUserId, $userId]);
    return $stmt->fetchAll();
}

function sendMessage($senderId, $receiverId, $message) {
    global $pdo;
    $message = sanitizeInput($message);
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$senderId, $receiverId, $message]);
    
    createNotification($receiverId, "Você recebeu uma nova mensagem de " . $_SESSION['username']);
}


function createNotification($userId, $message) {
    global $pdo;
    $message = sanitizeInput($message);
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->execute([$userId, $message]);
}

function getUnreadNotificationsCount($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read = 0");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}
