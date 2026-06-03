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
    $statusDb  = $row['status'];
    $validade  = $row['validade'];
    $dias      = '';
    $validadeFormatada = '';

    if (!empty($validade) && $validade !== '0000-00-00') {
        $hoje         = new DateTime(date('Y-m-d'));
        $dataValidade = new DateTime($validade);
        $dias         = (int) $hoje->diff($dataValidade)->format('%r%a');
        $validadeFormatada = $dataValidade->format('d/m/Y');
    }

    // Respeita suspenso e pendente do banco; demais calculados pela validade
    if ($statusDb === 'suspenso') {
        $status = 'Suspenso';
    } elseif ($statusDb === 'pendente' || empty($validade) || $validade === '0000-00-00') {
        $status = 'Pendente';
        $dias   = '';
    } elseif ($dias < 0) {
        $status = 'Vencido';
    } elseif ($dias <= 30) {
        $status = 'A Vencer';
    } else {
        $status = 'Válido';
    }

    // Normaliza nome: primeira letra de cada palavra maiúscula
    $nome = mb_convert_case(mb_strtolower($row['nome'], 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

    $sheet->fromArray([
        $nome,
        $row['cnh'],
        $row['cpf'],
        $validadeFormatada,
        $row['modelo'],
        $row['placa'],
        $row['credencial'],
        $status,
        $dias
    ], null, 'A' . $linha);

    $linha++;
}

$ultimaColuna = 'I';
$ultimaLinha  = $linha - 1;

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
