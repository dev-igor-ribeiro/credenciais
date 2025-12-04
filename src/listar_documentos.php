<?php
require_once "../db/conexao_motoristas.php";

// Ícones em Base64 (PDF e arquivo genérico)
$pdfIconBase64 = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAABaElEQVR4nO2a0Q6CMBRFj3yoS8QKXyCk9AqEEtS3oBNEEraZFqJb5uYdE8ePhRHFqRSb/5Mzdn3vXTPT9N1vZtGMYwxxhiDAQCxAID3A71Zo4PMBVXgS5aAFoaQPQMPADvewDheN7SqXudT2cCCF3BQ0BGHYDAAA2z30UGoB+oICqDoIAPQweA3tKp2k+wDDgHcwB5xtBrYFf/0WgK1R8EXAA8Ai7T9K1z+YtMCE61OwRcE9MwAOB68nrZzCF7FxrPBp+ALoAGziSR1deFoFZpUp5SXiqxSfpKfSdwU6VCTMT1Hgi0tmbGJwIkU0XyA1BkYzaAo4J1A7kQyKtn6oOgHCAx2Xj0YbaucHIm6RTGccwDAoYx9rJ7mlxW5uqUX0AqM9u4h9iJ5mHs4lzArT7tGU2gBp8TuhG6uECtE7pMXgYCMyZ2fTNNoAcJY5aRWPSEJZDswDMAZmw0kPl8PA7QtZ5FMuv1+rzuN2xxxxhhjjDE2x34BvXWPsc/7qVwAAAABJRU5ErkJggg==";
$fileIconBase64 = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAABUElEQVR4nO2a0Q6CMBBFj3yoS8QKXyCk9AqEEtS3oBNEEraZFqJb5uYdE8ePhRHFqRSb/5Mzdn3vXTPT9N1vZtGMYwxxhiDAQCxAID3A71Zo4PMBVXgS5aAFoaQPQMPADvewDheN7SqXudT2cCCF3BQ0BGHYDAAA2z30UGoB+oICqDoIAPQweA3tKp2k+wDDgHcwB5xtBrYFf/0WgK1R8EXAA8Ai7T9K1z+YtMCE61OwRcE9MwAOB68nrZzCF7FxrPBp+ALoAGziSR1deFoFZpUp5SXiqxSfpKfSdwU6VCTMT1Hgi0tmbGJwIkU0XyA1BkYzaAo4J1A7kQyKtn6oOgHCAx2Xj0YbaucHIm6RTGccwDAoYx9rJ7mlxW5uqUX0AqM9u4h9iJ5mHs4lzArT7tGU2gBp8TuhG6uECtE7pMXgYCMyZ2fTNNoAcJY5aRWPSEJZDswDMAZmw0kPl8PA7QtZ5FMuv1+rzuN2xxxxhhjjDE2x34BvXWPsc/7qVwAAAABJRU5ErkJggg==";

// Verifica ID
if (!isset($_GET["motorista_id"])) exit("ID inválido");

$id = intval($_GET["motorista_id"]);

// Caminho correto para os arquivos
// Funciona tanto em /testes/ quanto em /credenciais/
$basePath = "../documentos/$id/";
$baseUrl  = "../documentos/$id/";

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

    // URL do arquivo
    $url = $baseUrl . $arquivo;

    // IMAGENS reais → miniatura verdadeira
    if (in_array($ext, ["jpg", "jpeg", "png", "gif", "webp"])) {
        echo "
            <div class='doc-item'>
                <img src='$url' class='doc-thumb real-thumb'>
                <a href='$url' target='_blank'>🖼 Abrir imagem</a>
            </div>
        ";
    }
    // PDF → ícone PDF
    elseif ($ext === "pdf") {
        echo "
            <div class='doc-item'>
                <img src='$pdfIconBase64' class='doc-thumb'>
                <a href='$url' target='_blank'>📄 Abrir PDF</a>
            </div>
        ";
    }
    // Qualquer outro tipo → ícone arquivo
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