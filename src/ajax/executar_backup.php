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

require_once '../helpers/log.php';
require_once '../../db/conexao_motoristas.php';
registrarLog($pdo, 'Backup', trim($resultado));

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['sucesso' => true, 'mensagem' => trim($resultado)]);
