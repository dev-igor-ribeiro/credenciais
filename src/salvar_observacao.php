<?php
require_once "../db/conexao_motoristas.php";

if (!isset($_POST["motorista_id"]) || !isset($_POST["observacao"])) {
    exit("Dados inválidos");
}

$id = intval($_POST["motorista_id"]);
$obs = trim($_POST["observacao"]);

// Se já existe registro, atualiza
$stmt = $pdo->prepare("SELECT id FROM documentos_motoristas WHERE motorista_id = ? LIMIT 1");
$stmt->execute([$id]);

if ($stmt->rowCount() > 0) {
    $upd = $pdo->prepare("
        UPDATE documentos_motoristas 
        SET observacao = ? 
        WHERE motorista_id = ?
    ");
    $upd->execute([$obs, $id]);
} else {
    // Se não existe, cria um novo registro apenas com a observação
    $ins = $pdo->prepare("
        INSERT INTO documentos_motoristas (motorista_id, arquivo, observacao)
        VALUES (?, '', ?)
    ");
    $ins->execute([$id, $obs]);
}

echo "OK";