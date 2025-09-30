<?php
require_once '../../db/conexao_motoristas.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $cnh = $_POST['cnh'];
    $cpf = $_POST['cpf'];
    $validade = $_POST['validade'];
    $modelo = $_POST['modelo'];
    $placa = $_POST['placa'];
    $credencial = $_POST['credencial'];

    $validade_timestamp = strtotime($validade);
    $hoje_timestamp = time();
    $dias_restante = ceil(($validade_timestamp - $hoje_timestamp) / (60 * 60 * 24));

    $sql = "INSERT INTO motoristas (nome, cnh, cpf, validade, modelo, placa, credencial, status, dias_restante)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $nome, $cnh, $cpf, $validade, $modelo, $placa, $credencial, $status, $dias_restante);

    if ($stmt->execute()) {
        header("Location: ../../painel.php?sucesso=1");
        exit();
    } else {
        echo "Erro ao inserir: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Requisição inválida.";
}
?><?php
require_once '../../db/conexao_motoristas.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $cnh = $_POST['cnh'];
    $cpf = $_POST['cpf'];
    $validade = $_POST['validade'];
    $modelo = $_POST['modelo'];
    $ano = $_POST['ano'] ?? '';
    $placa = $_POST['placa'];
    $credencial = $_POST['credencial'];

    // Calcula dias restantes
    $validade_timestamp = strtotime($validade);
    $hoje_timestamp = time();
    $dias_restante = ceil(($validade_timestamp - $hoje_timestamp) / (60 * 60 * 24));

    // Define status automaticamente
    if ($dias_restante < 0) {
        $status = 'vencido';
    } elseif ($dias_restante <= 30) {
        $status = 'a_vencer';
    } else {
        $status = 'valido';
    }

    $sql = "INSERT INTO motoristas (nome, cnh, cpf, validade, modelo, ano, placa, credencial, status, dias_restante)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $nome, $cnh, $cpf, $validade, $modelo, $ano, $placa, $credencial, $status, $dias_restante);

    if ($stmt->execute()) {
        header("Location: ../../painel.php?sucesso=1");
        exit();
    } else {
        echo "Erro ao inserir: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Requisição inválida.";
}