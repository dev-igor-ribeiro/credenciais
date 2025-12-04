<?php
require_once "../db/conexao_motoristas.php";

if (!isset($_GET["motorista_id"])) exit("ID inválido");

$id = intval($_GET["motorista_id"]);

// Caminho correto para documentos (funciona no /testes/)
$basePath = "../documentos/$id/";

$stmt = $pdo->prepare("
    SELECT arquivo
    FROM documentos_motoristas
    WHERE motorista_id = ?
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

    // URL do arquivo real
    $url = "../documentos/$id/$arquivo";

    echo "<div class='doc-item'>";

    if (in_array($ext, ["jpg", "jpeg", "png", "gif", "webp"])) {

        // Miniatura real
        echo "<img src='$url' class='doc-thumb'>";
        echo "<a href='$url' target='_blank'>🖼 Abrir imagem</a>";

    } elseif ($ext === "pdf") {

        // Ícone fixo de PDF
        echo "<img src='../assets/icons/pdf.png' class='doc-thumb'>";
        echo "<a href='$url' target='_blank'>📄 Abrir PDF</a>";

    } else {

        // Qualquer outro arquivo
        echo "<img src='../assets/icons/file.png' class='doc-thumb'>";
        echo "<a href='$url' download>⬇ Baixar arquivo</a>";

    }

    echo "</div>";
}