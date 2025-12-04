<?php
require_once "../db/conexao_motoristas.php";

// DEBUG OPCIONAL — pode remover depois
// header("Content-Type: text/plain; charset=utf-8");
// echo "LISTANDO DOCUMENTOS...\n\n";

if (!isset($_GET["motorista_id"])) exit("ID inválido");

$id = intval($_GET["motorista_id"]);

// Caminhos principais
$baseDocs = "../documentos/$id/";
$baseThumbs = "../documentos/$id/thumbs/";

// Consulta documentos
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

    // Caminhos completos da miniatura
    $thumbImage = $baseThumbs . $arquivo . "_thumb.jpg"; // imagens
    $thumbPdf = $baseThumbs . $arquivo . ".jpg";         // pdf

    // Caminho público para abrir
    $urlDoc = "../documentos/$id/$arquivo";

    echo "<div class='doc-item'>";

    /* ===== MINIATURA PARA IMAGENS ===== */
    if (in_array($ext, ["jpg", "jpeg", "png", "gif", "webp"])) {

        if (file_exists($thumbImage)) {
            echo "<img src='$thumbImage' class='doc-thumb'>";
        } else {
            echo "<img src='../assets/icons/file.png' class='doc-thumb'>";
        }

        echo "<a href='$urlDoc' target='_blank'>Abrir imagem</a>";
    }

    /* ===== MINIATURA PARA PDF ===== */
    elseif ($ext === "pdf") {

        if (file_exists($thumbPdf)) {
            echo "<img src='$thumbPdf' class='doc-thumb'>";
        } else {
            echo "<img src='../assets/icons/pdf.png' class='doc-thumb'>";
        }

        echo "<a href='$urlDoc' target='_blank'>Abrir PDF</a>";
    }

    /* ===== OUTROS TIPOS ===== */
    else {
        echo "<img src='../assets/icons/file.png' class='doc-thumb'>";
        echo "<a href='$urlDoc' download>Baixar arquivo</a>";
    }

    echo "</div>";
}
?>