<?php
require_once "../db/conexao_motoristas.php";

if (!isset($_POST["motorista_id"])) {
    exit("ID inválido");
}

$id = intval($_POST["motorista_id"]);

if (!isset($_FILES["arquivo"]) || $_FILES["arquivo"]["error"] !== 0) {
    exit("Erro ao enviar o arquivo");
}

$nomeArquivo = basename($_FILES["arquivo"]["name"]);
$nomeArquivo = preg_replace("/[^a-zA-Z0-9._-]/", "_", $nomeArquivo);

// NOVO DIRETÓRIO CORRETO
$diretorio = "../documentos/$id/";

// Cria pasta se não existir
if (!is_dir($diretorio)) {
    mkdir($diretorio, 0777, true);
}

$caminho = $diretorio . $nomeArquivo;

// Move arquivo
if (move_uploaded_file($_FILES["arquivo"]["tmp_name"], $caminho)) {

    // Salva no banco
    $stmt = $pdo->prepare("INSERT INTO documentos_motoristas (motorista_id, arquivo) VALUES (?, ?)");
    $stmt->execute([$id, $nomeArquivo]);

    echo "OK";
} else {
    echo "Erro ao mover o arquivo";
}