<?php
require_once 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
include 'header_noacconunt.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email']);

    if (empty($email)) {
        $message = "Por favor, digite seu E-mail cadastrado.";
    } else {
        $stmt = $pdo->prepare('SELECT * FROM user WHERE email = ? AND is_verified = 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Gerar token de recuperação de senha
            $token = bin2hex(random_bytes(16));
            $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Atualizar o usuário com o token de recuperação
            $update_stmt = $pdo->prepare('UPDATE user SET reset_token = ?, reset_token_expiry = ? WHERE id = ?');
            $update_stmt->execute([$token, $token_expiry, $user['id']]);

            // Enviar e-mail de recuperação
            $mail = new PHPMailer(true);

            try {
                //Configurações do servidor
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'enviosluzes@gmail.com'; // Seu email do Gmail
                $mail->Password   = 'cmqv dzuq bucv jify'; // Sua senha do Gmail ou senha de app
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                //Destinatários
                $mail->setFrom('enviosluzes@gmail.com', 'Envio do Site');
                $mail->addAddress($email, $user['username']);

                //Conteúdo
                $mail->isHTML(true);
                $mail->Subject = 'Recuperação de Senha';
                $mail->Body    = "Olá {$user['username']},<br><br>Para redefinir sua senha, clique no link abaixo:<br><a href='http://localhost/login-system/public/redefinir_senha.php?token=$token'>Redefinir Senha</a><br><br>Este link expirará em 1 hora.";

                $mail->send();
                $message = "Um e-mail de recuperação de senha foi enviado ao E-mail digitado.";
            } catch (Exception $e) {
                $message = "Erro ao enviar o e-mail de recuperação. Por favor, tente novamente mais tarde.";
            }
        } else {
            $message = "E-mail não cadastrado ou conta não verificada.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha</title>
</head>
<body>
    <h2>Recuperar Senha</h2>
    <?php
    if (!empty($message)) {
        echo "<p>" . htmlspecialchars($message) . "</p>";
    }
    ?>
    <form action="recuperar_senha.php" method="POST">
        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" required><br>

        <button type="submit">Enviar E-mail de Recuperação</button>
    </form>
    <a href="login.php">Voltar para o Login</a>
</body>
</html>