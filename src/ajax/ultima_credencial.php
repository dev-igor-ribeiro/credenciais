<?php
require_once '../../db/conexao_motoristas.php';

$stmt = $pdo->query("SELECT MAX(CAST(credencial AS UNSIGNED)) AS ultima FROM motoristas WHERE credencial REGEXP '^[0-9]+$'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ultima' => $row['ultima'] ?? 0]);
