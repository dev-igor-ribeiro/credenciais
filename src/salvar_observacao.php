<?php
require_once "../db/conexao_motoristas.php";

if (!isset($_POST["motorista_id"]) || !isset($_POST["observacao"])) {
    exit("Dados inválidos");
}

$id = intval($_POST["motorista_id"]);
$obs = trim($_POST["observacao"]);

// Verifica se já existe uma observação salva
$stmt = $pdo->prepare("
    SELECT id 
    FROM documentos_motoristas 
    WHERE motorista_id = ?
      AND observacao IS NOT NULL
      AND observacao <> ''
    ORDER BY id DESC 
    LIMIT 1
");
$stmt->execute([$id]);

if ($stmt->rowCount() > 0) {
    // Atualiza a observação existente
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $upd = $pdo->prepare("UPDATE documentos_motoristas SET observacao = ? WHERE id = ?");
    $upd->execute([$obs, $row["id"]]);
} else {
    // Cria um novo registro só para observação
    $ins = $pdo->prepare("
        INSERT INTO documentos_motoristas (motorista_id, arquivo, observacao)
        VALUES (?, '', ?)
    ");
    $ins->execute([$id, $obs]);
}

echo "OK";