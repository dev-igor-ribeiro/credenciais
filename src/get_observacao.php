<?php
require_once "../db/conexao_motoristas.php";

if (!isset($_GET["motorista_id"])) {
    exit("");
}

$id = intval($_GET["motorista_id"]);

$stmt = $pdo->prepare("
    SELECT observacao 
    FROM documentos_motoristas 
    WHERE motorista_id = ?
      AND observacao IS NOT NULL
      AND observacao <> ''
    ORDER BY id DESC 
    LIMIT 1
");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

echo $row ? $row["observacao"] : "";