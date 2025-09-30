<?php
require_once '../../vendor/autoload.php';
require_once '../../db/conexao_motoristas.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    if (!isset($_FILES['arquivo']['tmp_name']) || empty($_FILES['arquivo']['tmp_name'])) {
        throw new Exception('Nenhum arquivo enviado.');
    }

    $inputFileName = $_FILES['arquivo']['tmp_name'];
    $spreadsheet = IOFactory::load($inputFileName);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();

    // Prepare PDO insert with status
    $sql = "INSERT INTO motoristas (nome, cnh, cpf, validade, modelo, ano, placa, credencial, status)
            VALUES (:nome, :cnh, :cpf, :validade, :modelo, :ano, :placa, :credencial, :status)";
    $stmt = $pdo->prepare($sql);

    $firstRow = true;
    foreach ($rows as $row) {
        if ($firstRow) {
            $firstRow = false;
            continue; // Skip header
        }
        // Map columns
        $nome = isset($row[0]) ? trim($row[0]) : '';
        $cnh = isset($row[1]) ? trim($row[1]) : '';
        $cpf = isset($row[2]) ? trim($row[2]) : '';
        $validade = isset($row[3]) ? trim($row[3]) : '';
        $modelo = isset($row[4]) ? trim($row[4]) : '';
        $ano = isset($row[5]) ? trim($row[5]) : '';
        $placa = isset($row[6]) ? trim($row[6]) : '';
        $credencial = 0;

        // Convert validade to yyyy-mm-dd, supports dd/mm/yyyy or yyyy-mm-dd
        if ($validade) {
            $timestamp = strtotime($validade);
            if ($timestamp !== false) {
                $validade_mysql = date('Y-m-d', $timestamp);
            } else {
                $validade_mysql = null;
            }
        } else {
            $validade_mysql = null;
        }

        // Calculate status
        if ($validade_mysql) {
            $hoje = new DateTime();
            $data_validade = DateTime::createFromFormat('Y-m-d', $validade_mysql);
            if ($data_validade) {
                $diff = $hoje->diff($data_validade);
                $dias_restante = (int) $diff->format('%r%a');
                if ($dias_restante < 0) {
                    $status = 'vencido';
                } elseif ($dias_restante <= 30) {
                    $status = 'a_vencer';
                } else {
                    $status = 'valido';
                }
            } else {
                $status = null;
            }
        } else {
            $status = null;
        }

        $stmt->execute([
            ':nome' => $nome,
            ':cnh' => $cnh,
            ':cpf' => $cpf,
            ':validade' => $validade_mysql,
            ':modelo' => $modelo,
            ':ano' => $ano,
            ':placa' => $placa,
            ':credencial' => $credencial,
            ':status' => $status
        ]);
    }
    echo "sucesso";
} catch (Exception $e) {
    echo "erro: " . $e->getMessage();
}