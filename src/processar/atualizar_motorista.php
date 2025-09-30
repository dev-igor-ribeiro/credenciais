<?php
require_once '../../db/conexao_motoristas.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitizar entradas usando null coalescing operator
        $id = $_POST['id'] ?? null;
        $nome = $_POST['nome'] ?? '';
        $cnh = $_POST['cnh'] ?? '';
        $cpf = $_POST['cpf'] ?? '';
        $validade = $_POST['validade'] ?? '';
        $modelo = $_POST['modelo'] ?? '';
        $ano = $_POST['ano'] ?? '';
        $placa = $_POST['placa'] ?? '';
        $credencial = $_POST['credencial'] ?? '';

        // Converter validade para formato yyyy-mm-dd
        $validade_formatada = null;
        if ($validade) {
            // Detecta formato dd/mm/yyyy ou yyyy-mm-dd
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $validade)) {
                $dt = DateTime::createFromFormat('d/m/Y', $validade);
            } else {
                $dt = new DateTime($validade);
            }
            $validade_formatada = $dt ? $dt->format('Y-m-d') : null;
        }

        // Determina status automaticamente: vencido, a_vencer, valido (sem acento)
        $status = 'valido';
        if ($validade_formatada) {
            $hoje = new DateTime();
            $validade_dt = new DateTime($validade_formatada);
            $interval = $hoje->diff($validade_dt);
            $dias_restante = (int) $interval->format('%r%a'); // signed

            if ($dias_restante < 0) {
                $status = 'vencido';
            } elseif ($dias_restante <= 30) {
                $status = 'a_vencer';
            } else {
                $status = 'valido';
            }
        }

        // Atualiza usando PDO
        $sql = "UPDATE motoristas 
                SET nome = :nome, cnh = :cnh, cpf = :cpf, validade = :validade, modelo = :modelo, ano = :ano, placa = :placa, credencial = :credencial, status = :status
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':cnh', $cnh);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->bindParam(':validade', $validade_formatada);
        $stmt->bindParam(':modelo', $modelo);
        $stmt->bindParam(':ano', $ano);
        $stmt->bindParam(':placa', $placa);
        $stmt->bindParam(':credencial', $credencial);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "sucesso";
        } else {
            echo "erro: falha ao atualizar";
        }
    } catch (Exception $e) {
        echo "erro: " . $e->getMessage();
    }
} else {
    echo "metodo_invalido";
}
?>