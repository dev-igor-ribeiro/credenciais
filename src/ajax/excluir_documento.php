<?php
$id   = isset($_POST['motorista_id']) ? (int)$_POST['motorista_id'] : 0;
$nome = isset($_POST['nome']) ? basename($_POST['nome']) : '';

if (!$id || !$nome) { http_response_code(400); echo json_encode(['erro' => 'Dados inválidos']); exit; }

$arquivo = __DIR__ . '/../../uploads/motoristas/' . $id . '/' . $nome;
if (file_exists($arquivo) && unlink($arquivo)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['sucesso' => true]);
} else {
    http_response_code(404); echo json_encode(['erro' => 'Arquivo não encontrado']);
}
