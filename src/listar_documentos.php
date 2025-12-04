<?php
require_once "../db/conexao_motoristas.php";

// Ícones em Base64 (PDF e arquivo genérico)
$pdfIconBase64 = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAACSElEQVR4nO2bP2sUQRTGf5MNQKoIhSjgoD4ojiYidYoiikvwbAw7UutgL4CErUQqPW4DnwGEGgkECiGIQoANN5rCjInU0aiNRU2BqM777bN89s7M3uzM7u7t3Zmbs7OzDPvO2dmZ2Z2dm9nc0kymUwmk8nkmEYzWaz2azGWZj3gJ4Ai8AM4GfAPsAbwLfhxXqzH0H01agx/VUprqRZI7lXfQ+OhWEWwltexA+xJ/9E7A1oFmgEkgN7uCnd6d0HjGFH05FvonTgEiEpGyCHh6ULVmg+1AVWg9+iWC0oDnQDZwH3TPcOh8KuPEgH2gFmgauA5W8DQxjwVv06kS4GTsR3cDawJVwQ30vqZRbAJXAZ3Ik6ykMo0vIjgBmAlfEt7pC7gT7C47jJ5nwJahB6hflqUa5frwJXgwMoDpwEkobFQ9YY+qgLWgIPAI2F67q7zUeHxpqZlaXvA7nPQyGfglrSKnfD1r2gRXQu0Am0A04Gj9uKolGUw00g4oEd8jxZ/m3WNgP0suWQMA3t4Dew5G4qldgPzYMud9AY8Gz0qzW8zj3VMZ7hpB5udh4Ez0Eh2gAtgY8CxYLtkFflJHK0TAC+As8Ch9mSH38jXjzz26QNuBB8EIs7BJf4Z8Ccs5svwh7gGfAm2EVn3F3bQVHp698z48UGnTxfAx+F3gEdItkZgFNhqePql0sTLjNMM5ExMYdjJcSZy14QeCvplvGXT8brdfzwf/2iDc7Kjx7gU8C7x5KJPBJcHAH0mPpws2SgQAAAABJRU5ErkJggg==";
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