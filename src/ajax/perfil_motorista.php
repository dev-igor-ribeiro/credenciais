<?php
require_once '../../db/conexao_motoristas.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { http_response_code(400); echo json_encode(['erro' => 'ID inválido']); exit; }

$stmt = $pdo->prepare("SELECT * FROM motoristas WHERE id = ?");
$stmt->execute([$id]);
$motorista = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$motorista) { http_response_code(404); echo json_encode(['erro' => 'Não encontrado']); exit; }

// Histórico
$stmt2 = $pdo->prepare("SELECT id, observacao, criado_em FROM historico_motorista WHERE motorista_id = ? ORDER BY criado_em DESC");
$stmt2->execute([$id]);
$historico = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Documentos
$pasta = __DIR__ . '/../../uploads/motoristas/' . $id . '/';
$documentos = [];
if (is_dir($pasta)) {
    foreach (glob($pasta . '*.pdf') as $arquivo) {
        $documentos[] = basename($arquivo);
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'motorista' => $motorista,
    'historico'  => $historico,
    'documentos' => $documentos
]);
