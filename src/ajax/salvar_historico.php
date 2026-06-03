<?php
require_once '../../db/conexao_motoristas.php';

$id          = isset($_POST['motorista_id']) ? (int)$_POST['motorista_id'] : 0;
$observacao  = trim($_POST['observacao'] ?? '');

if (!$id || !$observacao) { http_response_code(400); echo json_encode(['erro' => 'Dados inválidos']); exit; }

$stmt = $pdo->prepare("INSERT INTO historico_motorista (motorista_id, observacao) VALUES (?, ?)");
$stmt->execute([$id, $observacao]);

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['sucesso' => true, 'id' => $pdo->lastInsertId(), 'criado_em' => date('d/m/Y H:i')]);
