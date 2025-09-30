<?php
require_once '../../db/conexao_motoristas.php';

$sql = "SELECT credencial, nome, cnh, cpf, modelo, ano, placa, DATE_FORMAT(validade, '%d/%m/%Y') AS validade, status, DATEDIFF(validade, CURDATE()) AS dias_restante, id FROM motoristas ORDER BY nome ASC";

$stmt = $pdo->query($sql);
$motoristas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$anoAtual = date("Y");

// Marcar veículos com 10 anos ou mais
foreach ($motoristas as &$motorista) {
    if (!empty($motorista['ano']) && ($anoAtual - (int) $motorista['ano']) >= 10) {
        $motorista['ano_vermelho'] = true;
    } else {
        $motorista['ano_vermelho'] = false;
    }
    $motorista['cpf_class'] = 'cpf';
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($motoristas);
?>