<?php
require_once('../../db/conexao.php');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=motoristas.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Nome', 'CNH', 'CPF', 'Data Validade', 'Modelo', 'Placa', 'Credencial', 'Status', 'Dias']);

$query = "SELECT * FROM boraca19_credenciais";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['nome'],
        $row['cnh'],
        $row['cpf'],
        $row['validade'],
        $row['modelo'],
        $row['placa'],
        $row['credencial'],
        $row['status'],
        $row['dias']
    ]);
}

fclose($output);
exit;
?>