<?php
require_once '../../db/conexao_motoristas.php';

$credencial = trim($_GET['credencial'] ?? '');
if ($credencial === '') {
    echo json_encode(['existe' => false]);
    exit;
}

$stmt = $pdo->prepare("SELECT nome FROM motoristas WHERE credencial = ?");
$stmt->execute([$credencial]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'existe' => $row ? true : false,
    'nome'   => $row ? $row['nome'] : null
]);
