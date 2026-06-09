<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    http_response_code(403);
    echo json_encode(['erro' => 'Não autorizado']);
    exit;
}

require_once '../../db/conexao_motoristas.php';

$limite = isset($_GET['limite']) ? min((int)$_GET['limite'], 200) : 50;

$stmt = $pdo->prepare(
    "SELECT id, usuario, acao, descricao, ip, created_at
     FROM log_acoes
     ORDER BY created_at DESC
     LIMIT ?"
);
$stmt->execute([$limite]);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Formata data para exibição
foreach ($registros as &$r) {
    $r['created_at'] = date('d/m/Y H:i:s', strtotime($r['created_at']));
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($registros);
