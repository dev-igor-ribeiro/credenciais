<?php
require_once "../db/conexao_motoristas.php";

if (!isset($_GET["motorista_id"])) exit("ID inválido");
$id = intval($_GET["motorista_id"]);

/* =======================================================
   DETECTA AUTOMATICAMENTE SE ESTÁ EM /testes/ OU /credenciais/
   ======================================================= */
$baseUrl = "";  // caminho para URL pública
$basePath = ""; // caminho interno no servidor

if (strpos($_SERVER['REQUEST_URI'], '/testes/') !== false) {
    // Ambiente de testes
    $baseUrl  = "../testes/documentos/$id/";
    $basePath = "../testes/documentos/$id/";
} else {
    // Ambiente de produção (credenciais)
    $baseUrl  = "../documentos/$id/";
    $basePath = "../documentos/$id/";
}

// Caminhos das miniaturas
$thumbUrl  = $baseUrl . "thumbs/";
$thumbPath = $basePath . "thumbs/";

/* =======================================================
   BUSCA DOCUMENTOS NO BANCO
   ======================================================= */
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

    $arquivo = $d['arquivo'];
    $ext = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));

    // Arquivo real
    $urlDoc = $baseUrl . $arquivo;

    // Miniaturas geradas
    $thumbImagePath = $thumbPath . $arquivo . "_thumb.jpg"; // imagens
    $thumbPdfPath   = $thumbPath . $arquivo . ".jpg";       // pdf

    // Miniaturas URL
    $thumbImageUrl = $thumbUrl . $arquivo . "_thumb.jpg";
    $thumbPdfUrl   = $thumbUrl . $arquivo . ".jpg";

    echo "<div class='doc-item'>";

    /* MINIATURA REAL PARA IMAGENS */
    if (in_array($ext, ["jpg","jpeg","png","gif","webp"])) {

        if (file_exists($thumbImagePath)) {
            echo "<img src='$thumbImageUrl' class='doc-thumb'>";
        } else {
            echo "<img src='../assets/icons/file.png' class='doc-thumb'>";
        }

        echo "<a href='$urlDoc' target='_blank'>Abrir imagem</a>";
    }

    /* MINIATURA REAL PARA PDF */
    elseif ($ext === "pdf") {

        if (file_exists($thumbPdfPath)) {
            echo "<img src='$thumbPdfUrl' class='doc-thumb'>";
        } else {
            echo "<img src='../assets/icons/pdf.png' class='doc-thumb'>";
        }

        echo "<a href='$urlDoc' target='_blank'>Abrir PDF</a>";
    }

    /* OUTROS ARQUIVOS */
    else {

        echo "<img src='../assets/icons/file.png' class='doc-thumb'>";
        echo "<a href='$urlDoc' download>Baixar arquivo</a>";
    }

    echo "</div>";
}
?>