<?php
ob_start();
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../../db/conexao_motoristas.php';

    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { echo json_encode([]); exit; }

    $stmt = $pdo->prepare(
        "SELECT campo, valor_anterior, valor_novo, usuario, editado_em
         FROM historico_edicoes
         WHERE motorista_id = ?
         ORDER BY editado_em DESC
         LIMIT 200"
    );
    $stmt->execute([$id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_end_clean();
    echo json_encode($rows);
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['erro' => $e->getMessage()]);
}
