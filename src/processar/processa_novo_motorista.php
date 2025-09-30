<?php
require_once '../../db/conexao_motoristas.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $cnh = $_POST['cnh'];
    $cpf = $_POST['cpf'];
    $validade_input = $_POST['validade'];
    $modelo = $_POST['modelo'];
    $ano = $_POST['ano'] ?? '';
    $placa = $_POST['placa'];
    $credencial = $_POST['credencial'];

    // Convert validade to yyyy-mm-dd format
    if (strpos($validade_input, '/') !== false) {
        $date_parts = explode('/', $validade_input);
        if (count($date_parts) === 3) {
            // dd/mm/yyyy to yyyy-mm-dd
            $validade = sprintf('%04d-%02d-%02d', $date_parts[2], $date_parts[1], $date_parts[0]);
        } else {
            echo "erro: formato de data inválido";
            exit();
        }
    } else {
        // Assume already yyyy-mm-dd
        $validade = $validade_input;
    }

    $validade_timestamp = strtotime($validade);
    $hoje_timestamp = strtotime(date('Y-m-d'));
    $dias_restante = ceil(($validade_timestamp - $hoje_timestamp) / (60 * 60 * 24));

    // Calcular status apenas com base na validade
    if ($validade_timestamp < $hoje_timestamp) {
        $status = 'vencido';
    } elseif (($validade_timestamp - $hoje_timestamp) / (60 * 60 * 24) <= 30) {
        $status = 'a_vencer';
    } else {
        $status = 'valido';
    }

    $sql = "INSERT INTO motoristas (nome, cnh, cpf, validade, modelo, ano, placa, credencial, status)
            VALUES (:nome, :cnh, :cpf, :validade, :modelo, :ano, :placa, :credencial, :status)";

    $stmt = $pdo->prepare($sql);
    $params = [
        ':nome' => $nome,
        ':cnh' => $cnh,
        ':cpf' => $cpf,
        ':validade' => $validade,
        ':modelo' => $modelo,
        ':ano' => $ano,
        ':placa' => $placa,
        ':credencial' => $credencial,
        ':status' => $status
    ];

    if ($stmt->execute($params)) {
        echo "sucesso";
    } else {
        $errorInfo = $stmt->errorInfo();
        echo "erro: " . $errorInfo[2];
    }
} else {
    echo "Requisição inválida.";
}
?>