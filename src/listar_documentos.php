<?php
require_once "../db/conexao_motoristas.php";

if (!isset($_GET["motorista_id"])) exit("ID inválido");

$id = intval($_GET["motorista_id"]);

// Caminho correto onde os arquivos são salvos
$basePath = "../documentos/$id/";

$stmt = $pdo->prepare("
    SELECT arquivo
    FROM documentos_motoristas
    WHERE motorista_id = ?
      AND arquivo IS NOT NULL
      AND arquivo <> ''
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

    $url = $basePath . $arquivo;

    if (in_array($ext, ["jpg","jpeg","png","gif","webp"])) {
        // Miniatura da imagem
        echo "
            <div class='doc-item'>
                <img src='$url' class='doc-thumb'>
                <a class='doc-link' href='$url' target='_blank'>🖼 Abrir imagem</a>
            </div>
        ";
    } elseif ($ext === "pdf") {
        // Miniatura padrão para PDFs
        echo "
            <div class='doc-item'>
                <img src='../assets/icons/pdf.png' class='doc-thumb'>
                <a class='doc-link' href='$url' target='_blank'>📄 Abrir PDF</a>
            </div>
        ";
    } else {
        // Arquivo genérico
        echo "
            <div class='doc-item'>
                <img src='../assets/icons/file.png' class='doc-thumb'>
                <a class='doc-link' href='$url' download>⬇ Baixar arquivo</a>
            </div>
        ";
    }
}
?>