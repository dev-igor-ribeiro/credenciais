<?php
$id = isset($_POST['motorista_id']) ? (int)$_POST['motorista_id'] : 0;
if (!$id) { http_response_code(400); echo json_encode(['erro' => 'ID inválido']); exit; }

if (!isset($_FILES['documento']) || $_FILES['documento']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400); echo json_encode(['erro' => 'Erro no upload']); exit;
}

$arquivo = $_FILES['documento'];
$ext     = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
if ($ext !== 'pdf') { http_response_code(400); echo json_encode(['erro' => 'Apenas PDF permitido']); exit; }
if ($arquivo['size'] > 8 * 1024 * 1024) { http_response_code(400); echo json_encode(['erro' => 'Arquivo maior que 8MB']); exit; }

$pasta = __DIR__ . '/../../uploads/motoristas/' . $id . '/';
if (!is_dir($pasta)) mkdir($pasta, 0755, true);

$nomeArquivo = basename($arquivo['name']);
$destino     = $pasta . $nomeArquivo;

if (move_uploaded_file($arquivo['tmp_name'], $destino)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['sucesso' => true, 'nome' => $nomeArquivo]);
} else {
    http_response_code(500); echo json_encode(['erro' => 'Falha ao salvar arquivo']);
}
