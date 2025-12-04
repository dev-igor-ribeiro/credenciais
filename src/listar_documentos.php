<?php
require_once "../db/conexao_motoristas.php";

if (!isset($_GET["motorista_id"])) exit("ID inválido");

$id = intval($_GET["motorista_id"]);

$stmt = $pdo->prepare("
    SELECT arquivo
    FROM documentos_motoristas
    WHERE motorista_id = ?
      AND arquivo IS NOT NULL
      AND LENGTH(TRIM(arquivo)) > 0
    ORDER BY id DESC
");
$stmt->execute([$id]);
$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$docs) {
    echo "<p>Nenhum documento enviado.</p>";
    exit;
}

foreach ($docs as $d) {
    $arquivo = $d["arquivo"];
    $ext = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));
    $url = "../documentos/$id/$arquivo";

    if (in_array($ext, ["png","jpg","jpeg","gif","webp"])) {
        echo "
        <div class='doc-item'>
            <img src='$url' class='doc-thumb'>
            <a href='$url' target='_blank'>🖼 Abrir imagem</a>
        </div>";
    } elseif ($ext === "pdf") {
        echo "
        <div class='doc-item'>
            <img src='../assets/icons/pdf.png' class='doc-thumb'>
            <a href='$url' target='_blank'>📄 Abrir PDF</a>
        </div>";
    } else {
        echo "
        <div class='doc-item'>
            <img src='../assets/icons/file.png' class='doc-thumb'>
            <a href='$url' download>⬇ Baixar arquivo</a>
        </div>";
    }
}