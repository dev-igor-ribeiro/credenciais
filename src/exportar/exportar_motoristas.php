<?php
require_once('../../db/conexao_motoristas.php');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=motoristas.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Nome', 'CNH', 'CPF', 'Data Validade', 'Modelo', 'Placa', 'Credencial', 'Status', 'Dias'], ';');

$query = "SELECT * FROM motoristas ORDER BY nome ASC";
$result = $pdo->query($query);

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $validade = $row['validade'];
    $dias = '';
    $status = 'pendente';

    if (!empty($validade) && $validade !== '0000-00-00') {
        $hoje = new DateTime(date('Y-m-d'));
        $dataValidade = new DateTime($validade);
        $dias = (int) $hoje->diff($dataValidade)->format('%r%a');

        if ($dias < 0) {
            $status = 'vencido';
        } elseif ($dias <= 30) {
            $status = 'a vencer';
        } else {
            $status = 'valido';
        }
    }

    fputcsv($output, [
        $row['nome'],
        $row['cnh'],
        $row['cpf'],
        $validade,
        $row['modelo'],
        $row['placa'],
        $row['credencial'],
        $status,
        $dias
    ], ';');
}

fclose($output);
exit;
?>