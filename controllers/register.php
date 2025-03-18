<?php
require_once 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$error_message = '';
$success_message = '';
$page_title = 'Bem-Vindo à Luzes!';
include 'header_noacconunt.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error_message = "Por favor, preencha todos os campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Por favor, forneça um email válido.";
    } else {
        // Verificar se o nome de usuário já existe
        $stmt = $pdo->prepare('SELECT * FROM user WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            $error_message = "Nome de usuário já existente, selecione outro.";
        } else {
            // Verificar se o email já está cadastrado
            $stmt = $pdo->prepare('SELECT * FROM user WHERE email = ?');
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $error_message = "E-mail já se encontra cadastrado!";
            } else {
                // Inserir o usuário na tabela de pending_registrations
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $verification_code = bin2hex(random_bytes(16));
                $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));

                $stmt = $pdo->prepare('INSERT INTO pending_registrations (username, email, password, verification_code, expires_at) VALUES(?, ?, ?, ?, ?)');
                if ($stmt->execute([$username, $email, $password_hash, $verification_code, $expires_at])) {
                    $verification_link = "http://localhost/login-system/public/verify.php?code=" . $verification_code;
                    
                    $mail = new PHPMailer(true);

                    try {
                        // Configurações do servidor
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'enviosluzes@gmail.com';
                        $mail->Password   = 'cmqv dzuq bucv jify';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;
                
                        // Destinatários
                        $mail->setFrom('enviosluzes@gmail.com', 'Envio do Site');
                        $mail->addAddress($email, $username);
                
                        // Conteúdo
                        $mail->isHTML(true);
                        $mail->Subject = 'Confirmação de E-mail';
                        $mail->Body    = "Olá $username,<br><br>Por favor, clique no link abaixo para verificar seu e-mail:<br><a href='$verification_link'>$verification_link</a>";
                
                        $mail->send();
                        $success_message = "Por favor, verifique seu e-mail para ativar sua conta.";
                    } catch (Exception $e) {
                        $error_message = "Erro ao enviar o e-mail de confirmação. Por favor, tente novamente mais tarde. Erro: {$mail->ErrorInfo}";
                    }
                } else {
                    $error_message = "Erro ao cadastrar. Por favor, tente novamente.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
</head>
<body>
<main class="center-content">
    <section class="form-container">
        <h2 class="form-title">Cadastre-se</h2>
        <?php
        if (!empty($error_message)) {
            echo "<p class='error-message'>" . htmlspecialchars($error_message) . "</p>";
        }
        if (!empty($success_message)) {
            echo "<p class='success-message'>" . htmlspecialchars($success_message) . "</p>";
        }
        ?>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Usuário:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-primary">Registrar</button>
        </form>
        <div class="form-links">
            <a href="login.php">Já possui uma conta?</a>
        </div>
    </section>
</main>
</body>
</html>