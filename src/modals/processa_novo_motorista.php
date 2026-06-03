<?php
require_once '../../db/conexao_motoristas.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Requisição inválida.";
    exit();
}

$nome      = $_POST['nome'];
$cnh       = $_POST['cnh'];
$cpf       = $_POST['cpf'];
$modelo    = $_POST['modelo'];
$ano       = $_POST['ano'] ?? '';
$placa     = $_POST['placa'];
$credencial = $_POST['credencial'];
$validade  = !empty($_POST['validade']) ? $_POST['validade'] : null;
$status    = 'pendente';

$sql = "INSERT INTO motoristas (nome, cnh, cpf, validade, modelo, ano, placa, credencial, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssss", $nome, $cnh, $cpf, $validade, $modelo, $ano, $placa, $credencial, $status);

if ($stmt->execute()) {
    header("Location: ../../painel.php?sucesso=1");
    exit();
} else {
    echo "Erro ao inserir: " . $conn->error;
}

$stmt->close();
$conn->close();
