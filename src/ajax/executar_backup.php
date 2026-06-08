<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    http_response_code(403);
    echo json_encode(['erro' => 'Não autorizado']);
    exit;
}

ob_start();
require_once '../../backup/backup.php';
$resultado = ob_get_clean();

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['sucesso' => true, 'mensagem' => trim($resultado)]);
