<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;

try {
    // Cria uma planilha em memória
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'PhpSpreadsheet funcionando!');

    echo "<h2 style='color:green'>✅ PhpSpreadsheet está funcionando corretamente!</h2>";
    echo "<p>Verifiquei que a biblioteca foi carregada e uma planilha de teste foi criada em memória.</p>";

} catch (Exception $e) {
    echo "<h2 style='color:red'>❌ Erro ao testar PhpSpreadsheet</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}