<?php
require_once 'config.php';

$stmt = $pdo->prepare('DELETE FROM pending_registrations WHERE expires_at < NOW()');
$stmt->execute();

echo "Limpeza concluída. " . $stmt->rowCount() . " registros pendentes removidos";
?>