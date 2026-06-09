<?php
ob_start();
session_start();
if (!isset($_SESSION['usuario'])) {
    ob_end_clean();
    http_response_code(403);
    echo json_encode(['erro' => 'Não autorizado']);
    exit;
}

require_once '../../db/conexao_motoristas.php';

$limite = isset($_GET['limite']) ? min((int)$_GET['limite'], 200) : 50;

try {
    $stmt = $pdo->prepare(
        "SELECT id, usuario, acao, descricao, ip, created_at
         FROM log_acoes
         ORDER BY created_at DESC
         LIMIT $limite"
    );
    $stmt->execute([]);
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($registros as &$r) {
        $r['created_at'] = date('d/m/Y H:i:s', strtotime($r['created_at']));
    }

    $lixo = ob_get_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($registros);
} catch (Throwable $e) {
    $lixo = ob_get_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['erro' => $e->getMessage()]);
}
