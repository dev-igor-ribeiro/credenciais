<?php
require_once('../../db/conexao_motoristas.php');
require_once('../../vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$headers = ['Nome', 'CNH', 'CPF', 'Data Validade', 'Modelo', 'Placa', 'Credencial', 'Status', 'Dias'];
$sheet->fromArray($headers, null, 'A1');

$query = "SELECT * FROM motoristas ORDER BY nome ASC";
$result = $pdo->query($query);

$linha = 2;

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

    $sheet->fromArray([
        $row['nome'],
        $row['cnh'],
        $row['cpf'],
        $validade,
        $row['modelo'],
        $row['placa'],
        $row['credencial'],
        $status,
        $dias
    ], null, 'A' . $linha);

    $linha++;
}

$ultimaColuna = 'I';
$ultimaLinha = $linha - 1;

$sheet->setAutoFilter("A1:{$ultimaColuna}{$ultimaLinha}");

foreach (range('A', $ultimaColuna) as $coluna) {
    $sheet->getColumnDimension($coluna)->setAutoSize(true);
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="motoristas.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>