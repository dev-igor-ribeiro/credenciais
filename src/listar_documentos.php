<?php
require_once "../db/conexao_motoristas.php";

// Ícone PDF (base64 melhorado e visível)
$pdfIconBase64 = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAQAAAAAYLlVAAAArElEQVR4Ae3YMQrCMBBA0adpBCOwESOwEWuwESOwAQusQSOsQSPcNH352W0kn8K2Tf+IuwHGAE3g7WpX8mo6UCj2QcW6vEX5oTvcJt0vMAbF1rKcmE93xNS+kknUGu3yx0yBdnsJ9sbwE30v4NqvMe2AaxbdzHxQJ8JK31oJ7QhgTA5PzGMKw8RrEP31oJ3wtsFG3f7ZLy3fCBVQ61aGqS8uKQAAAABJRU5ErkJggg==";

// Ícone arquivo genérico
$fileIconBase64 = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAQAAAAAYLlVAAAArElEQVR4Ae3YMQrCMBBA0adpBCOwESOwEWuwESOwAQusQSOsQSPcNH352W0kn8K2Tf+IuwHGAE3g7WpX8mo6UCj2QcW6vEX5oTvcJt0vMAbF1rKcmE93xNS+kknUGu3yx0yBdnsJ9sbwE30v4NqvMe2AaxbdzHxQJ8JK31oJ7QhgTA5PzGMKw8RrEP31oJ3wtsFG3f7ZLy3fCBVQ61aGqS8uKQAAAABJRU5ErkJggg==";

// -------------------------------
// VALIDAR ID
// -------------------------------
if (!isset($_GET["motorista_id"])) {
    exit("<p>ID inválido.</p>");
}

$id = intval($_GET["motorista_id"]);

// -------------------------------
// CAMINHO DOS ARQUIVOS
// -------------------------------
$basePath = "../documentos/$id/";
$baseUrl  = "../documentos/$id/";

// -------------------------------
// BUSCAR DOCUMENTOS NO BANCO
// -------------------------------
$stmt = $pdo->prepare("
    SELECT arquivo
    FROM documentos_motoristas
    WHERE motorista_id = ?
      AND LENGTH(TRIM(arquivo)) > 0
    ORDER BY id DESC
");
$stmt->execute([$id]);
$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// -------------------------------
// SE NÃO TIVER DOCUMENTOS
// -------------------------------
if (!$docs) {
    echo "<p>Nenhum documento enviado.</p>";
    exit;
}

// -------------------------------
// LISTAR DOCUMENTOS
// -------------------------------
foreach ($docs as $d) {

    $arquivo = $d["arquivo"];
    $ext = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));
    $url = $baseUrl . $arquivo;

    // SE FOR IMAGEM → MINIATURA REAL
    if (in_array($ext, ["jpg", "jpeg", "png", "gif", "bmp", "webp"])) {
        echo "
            <div class='doc-item'>
                <img src='$url' class='doc-thumb real-thumb'>
                <a href='$url' target='_blank'>🖼 Abrir imagem</a>
            </div>
        ";
    }

    // SE FOR PDF → ÍCONE PDF
    elseif ($ext === "pdf") {
        echo "
            <div class='doc-item'>
                <img src='$pdfIconBase64' class='doc-thumb'>
                <a href='$url' target='_blank'>📄 Abrir PDF</a>
            </div>
        ";
    }

    // QUALQUER OUTRO TIPO
    else {
        echo "
            <div class='doc-item'>
                <img src='$fileIconBase64' class='doc-thumb'>
                <a href='$url' download>⬇ Baixar arquivo</a>
            </div>
        ";
    }
}
?>