<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../../db/conexao_motoristas.php';

    $sql = "SELECT id, credencial, nome, cnh, cpf, modelo, ano, placa,
                   DATE_FORMAT(validade, '%d/%m/%Y') AS validade,
                   status,
                   DATEDIFF(validade, CURDATE()) AS dias_restante,
                   criado_em
            FROM motoristas
            ORDER BY nome ASC";

    $stmt = $pdo->query($sql);
    $motoristas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $anoAtual = date("Y");
    foreach ($motoristas as &$motorista) {
        $motorista['ano_vermelho'] = (!empty($motorista['ano']) && ($anoAtual - (int)$motorista['ano']) >= 10);
        $motorista['cpf_class']   = 'cpf';
    }

    ob_end_clean();
    echo json_encode($motoristas);

} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['erro' => $e->getMessage()]);
}
