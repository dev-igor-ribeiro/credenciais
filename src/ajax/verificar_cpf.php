<?php
require_once '../../db/conexao_motoristas.php';

$cpf = trim($_GET['cpf'] ?? '');
$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0; // para ignorar o próprio motorista ao editar

if ($cpf === '') {
    echo json_encode(['existe' => false]);
    exit;
}

// Remove formatação para comparar apenas dígitos
$cpfLimpo = preg_replace('/\D/', '', $cpf);

$sql = "SELECT id, nome FROM motoristas WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ?";
if ($id > 0) $sql .= " AND id != $id";

$stmt = $pdo->prepare($sql);
$stmt->execute([$cpfLimpo]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'existe' => $row ? true : false,
    'nome'   => $row ? $row['nome'] : null
]);
