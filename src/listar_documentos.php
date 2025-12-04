<?php
require_once "../db/conexao_motoristas.php";

if (!isset($_GET["motorista_id"])) {
    exit("ID inválido");
}

$id = intval($_GET["motorista_id"]);

$stmt = $pdo->prepare("SELECT * FROM documentos_motoristas WHERE motorista_id = ? ORDER BY id DESC");
$stmt->execute([$id]);
$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$docs) {
    echo "<p>Nenhum documento enviado.</p>";
    exit;
}

foreach ($docs as $d) {
    $arquivo = htmlspecialchars($d["arquivo"]);
    echo "
        <p>
            <a href='../backup/documentos/$id/$arquivo' target='_blank'>
                📄 $arquivo
            </a>
        </p>
    ";
}