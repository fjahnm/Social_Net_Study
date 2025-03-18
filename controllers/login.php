<?php
require_once 'config.php';

$error_message = '';
$page_title = 'Bem-Vindo à Luzes!';
include 'header_noacconunt.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = sanitize_input($_POST['login']);
    $password = $_POST['password'];

    if (empty($login) || empty($password)) {
        $error_message = "Por favor, preencha todos os campos.";
    } else {
        $stmt = $pdo->prepare('SELECT * FROM user WHERE (email = ? OR username = ?) AND is_verified = 1');
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: profile.php');
            exit;
        } else {
            $error_message = "Credenciais incorretas ou conta não verificada.";
        }
    }
}
?>

<main class="center-content">
    <section class="form-container">
        <h2 class="form-title">Login</h2>
        <?php
        if (!empty($error_message)) {
            echo "<p class='error-message'>" . htmlspecialchars($error_message) . "</p>";
        }
        ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="login">E-mail/Usuário:</label>
                <input type="text" id="login" name="login" required>
            </div>

            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-primary">Entrar</button>
        </form>
        <div class="form-links">
            <a href="register.php">Não tem uma conta? Registre-se</a>
            <a href="recuperar_senha.php">Esqueceu a senha?</a>
        </div>
    </section>
</main>