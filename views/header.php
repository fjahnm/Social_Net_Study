<?php
require_once 'config.php';

$logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Luzes!'; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .notification-icon {
            position: relative;
            display: inline-block;
        }
        .notification-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            display: none; /* Oculta inicialmente */
        }
        #header-nav {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <header>
        <nav id="header-nav">
            <ul>
                <?php if ($logged_in): ?>
                    <!-- Ícone de notificações de mensagens -->
                    <li>
                        <a href="notifications.php" class="notification-icon">
                            <i class="fas fa-bell"></i>
                            <span class="notification-count" id="notification-count">0</span>
                        </a>
                    </li>
                    <!-- Ícone de notificações de solicitações de amizade -->
                    <li>
                        <a href="friend_request.php" class="notification-icon">
                            <i class="fas fa-user-friends"></i>
                            <span class="notification-count" id="friend-request-count">0</span>
                        </a>
                    </li>
                    <li><a href="profile.php">Seu Perfil</a></li>
                    <li><a href="feed.php">Feed</a></li>
                    <li><a href="communities.php">Comunidades!</a></li>
                    <li><a href="logout.php">Sair</a></li>
                    <li><a href="delete_account.php">Zona perigosa!!</a></li>
                <?php else: ?>
                    <li><a href="login.php">Entrar</a></li>
                    <li><a href="register.php">Registrar</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <script src="notifications.js"></script>
</body>
</html>