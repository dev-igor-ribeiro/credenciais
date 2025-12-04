<?php
require_once "../db/conexao_motoristas.php";

if (!isset($_POST["motorista_id"])) exit("ID inválido");

$id = intval($_POST["motorista_id"]);

if (!isset($_FILES["arquivo"]) || $_FILES["arquivo"]["error"] !== 0) {
    exit("Erro ao enviar o arquivo");
}

$nomeArquivo = basename($_FILES["arquivo"]["name"]);
$nomeArquivo = preg_replace("/[^a-zA-Z0-9._-]/", "_", $nomeArquivo);

$diretorio = "../documentos/$id/";
$thumbsDir = "../documentos/$id/thumbs/";

if (!is_dir($diretorio)) mkdir($diretorio, 0777, true);
if (!is_dir($thumbsDir)) mkdir($thumbsDir, 0777, true);

$caminho = $diretorio . $nomeArquivo;

if (move_uploaded_file($_FILES["arquivo"]["tmp_name"], $caminho)) {

    /* ==============================
       MINIATURA PARA IMAGENS
       ============================== */
    $ext = strtolower(pathinfo($caminho, PATHINFO_EXTENSION));

    if (in_array($ext, ["jpg", "jpeg", "png", "gif", "webp"])) {
        
        $thumbPath = $thumbsDir . $nomeArquivo . "_thumb.jpg";

        $img = new Imagick($caminho);
        $img->thumbnailImage(200, 200, true);
        $img->writeImage($thumbPath);
        $img->clear();
        $img->destroy();

    }

    /* ==============================
       MINIATURA PARA PDFs
       ============================== */
    if ($ext === "pdf") {

        $thumbPath = $thumbsDir . $nomeArquivo . ".jpg";

        $img = new Imagick();
        $img->setResolution(120, 120);
        $img->readImage($caminho."[0]"); // Primeira página
        $img->setImageFormat("jpg");
        $img->thumbnailImage(200, 200, true);
        $img->writeImage($thumbPath);
        $img->clear();
        $img->destroy();

    }

    // Salva no banco
    $stmt = $pdo->prepare("
        INSERT INTO documentos_motoristas (motorista_id, arquivo)
        VALUES (?, ?)
    ");
    $stmt->execute([$id, $nomeArquivo]);

    echo "OK";

} else {
    echo "Erro ao mover o arquivo";
}