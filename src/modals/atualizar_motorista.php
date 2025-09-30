<?php
require_once '../../db/conexao_motoristas.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $cnh = $_POST['cnh'];
    $cpf = $_POST['cpf'];
    $validade = DateTime::createFromFormat('d/m/Y', $_POST['validade'])->format('Y-m-d');
    $modelo = $_POST['modelo'];
    $placa = $_POST['placa'];
    $credencial = $_POST['credencial'];

    $stmt = $conn->prepare("UPDATE motoristas 
                            SET nome=?, cnh=?, cpf=?, validade=?, modelo=?, placa=?, credencial=?
                            WHERE id=?");
    $stmt->bind_param("sssssssi", $nome, $cnh, $cpf, $validade, $modelo, $placa, $credencial, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Motorista atualizado com sucesso!'); window.location.href='../../painel.php';</script>";
    } else {
        echo "Erro: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>