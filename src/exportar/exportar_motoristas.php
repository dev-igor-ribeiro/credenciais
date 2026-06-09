<?php
require_once('../../db/conexao_motoristas.php');
require_once('../../vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

// ── Filtros recebidos via GET ──
$filtroNome   = trim($_GET['nome']    ?? '');
$filtroStatus = trim($_GET['status']  ?? '');
$filtroDataDe = trim($_GET['data_de'] ?? '');
$filtroDataAte= trim($_GET['data_ate']?? '');

// ── Monta query com filtros ──
$where  = [];
$params = [];

if ($filtroNome !== '') {
    $like = '%' . $filtroNome . '%';
    $where[]  = "(nome LIKE ? OR credencial LIKE ? OR cpf LIKE ? OR modelo LIKE ? OR placa LIKE ?)";
    $params   = array_merge($params, [$like, $like, $like, $like, $like]);
}

if ($filtroDataDe !== '') {
    $where[]  = "validade >= ?";
    $params[] = $filtroDataDe;
}

if ($filtroDataAte !== '') {
    $where[]  = "validade <= ?";
    $params[] = $filtroDataAte;
}

$sql = "SELECT *, DATEDIFF(validade, CURDATE()) AS dias_restante
        FROM motoristas";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY nome ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$motoristas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Aplica filtro de status (calculado em PHP) ──
function calcularStatus($row) {
    $statusDb = $row['status'];
    $validade = $row['validade'];
    $dias     = isset($row['dias_restante']) ? (int)$row['dias_restante'] : null;

    if ($statusDb === 'suspenso') return 'Suspenso';
    if ($statusDb === 'pendente' || empty($validade) || $validade === '0000-00-00') return 'Pendente';
    if ($dias < 0)  return 'Vencido';
    if ($dias <= 30) return 'A Vencer';
    return 'Válido';
}

if ($filtroStatus !== '' && $filtroStatus !== 'Todos') {
    $motoristas = array_filter($motoristas, fn($r) => calcularStatus($r) === $filtroStatus);
}

// ── Monta planilha ──
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Motoristas');

// Cabeçalho
$headers = ['Credencial', 'Nome', 'CNH', 'CPF', 'Modelo', 'Ano', 'Placa', 'Data Validade', 'Status', 'Dias'];
$sheet->fromArray($headers, null, 'A1');

// Estilo do cabeçalho
$headerStyle = [
    'font'    => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a1a2e']],
];
$sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

// Linhas de dados
$linha = 2;
foreach ($motoristas as $row) {
    $validade  = $row['validade'] ?? '';
    $validadeF = '';
    if (!empty($validade) && $validade !== '0000-00-00') {
        $validadeF = (new DateTime($validade))->format('d/m/Y');
    }

    $status = calcularStatus($row);
    $dias   = ($status !== 'Pendente' && $status !== 'Suspenso') ? (int)$row['dias_restante'] : '';
    $nome   = mb_convert_case(mb_strtolower($row['nome'] ?? '', 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

    $sheet->fromArray([
        $row['credencial'],
        $nome,
        $row['cnh'],
        $row['cpf'],
        $row['modelo'],
        $row['ano'],
        strtoupper($row['placa']),
        $validadeF,
        $status,
        $dias
    ], null, 'A' . $linha);

    // Cor por status
    $cor = match($status) {
        'Vencido'  => 'FFCCCC',
        'A Vencer' => 'FFF3CD',
        'Suspenso' => 'E8D5FF',
        'Pendente' => 'FFE8A0',
        default    => null,
    };
    if ($cor) {
        $sheet->getStyle("A{$linha}:J{$linha}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB($cor);
    }

    $linha++;
}

// Auto-filter e largura automática
$ultima = $linha - 1;
$sheet->setAutoFilter("A1:J{$ultima}");
foreach (range('A', 'J') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Nome do arquivo com filtros aplicados
$sufixo = $filtroStatus && $filtroStatus !== 'Todos' ? '_' . strtolower(str_replace(' ', '_', $filtroStatus)) : '';
$arquivo = 'motoristas' . $sufixo . '_' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $arquivo . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
