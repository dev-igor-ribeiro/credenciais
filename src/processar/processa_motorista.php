<?php
include '../../db/conexao_motoristas.php'; // Use PDO connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $cnh = $_POST['cnh'] ?? '';
    $cpf = $_POST['cpf'] ?? '';
    $validade = $_POST['validade'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $ano = $_POST['ano'] ?? '';
    $placa = $_POST['placa'] ?? '';
    $credencial = $_POST['credencial'] ?? '';

    if (empty($nome) || empty($cnh) || empty($cpf) || empty($validade) || empty($modelo) || empty($placa)) {
        echo "erro: Campos obrigatórios não preenchidos";
        exit();
    }

    // Convert validade to yyyy-mm-dd
    $validade_formatada = '';
    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $validade)) {
        // dd/mm/yyyy
        $parts = explode('/', $validade);
        $validade_formatada = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $validade)) {
        // yyyy-mm-dd
        $validade_formatada = $validade;
    } else {
        echo "erro: Formato de validade inválido";
        exit();
    }

    // Calculate dias_restante
    try {
        $hoje = new DateTime();
        $dt_validade = new DateTime($validade_formatada);
        $diff = $hoje->diff($dt_validade);
        $dias_restante = (int) $diff->format('%r%a');
    } catch (Exception $e) {
        echo "erro: Erro ao calcular validade";
        exit();
    }

    // Determine status
    if ($dias_restante < 0) {
        $status = "vencido";
    } elseif ($dias_restante <= 30) {
        $status = "a_vencer";
    } else {
        $status = "valido";
    }

    // Insert using PDO
    try {
        $query = "INSERT INTO boraca19_credenciais (nome, cnh, cpf, validade, modelo, ano, placa, credencial)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $nome,
            $cnh,
            $cpf,
            $validade_formatada,
            $modelo,
            $ano,
            $placa,
            $credencial
        ]);
        echo "sucesso";
    } catch (Exception $e) {
        echo "erro: " . $e->getMessage();
    }
    exit();
} else {
    echo "erro: Requisição inválida";
    exit();
}
?>