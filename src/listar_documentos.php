<?php
require_once "../db/conexao_motoristas.php";
header("Content-Type: text/plain; charset=utf-8");

echo "Banco conectado: ";
echo $pdo->query("SELECT DATABASE()")->fetchColumn();
echo "\n\n";

echo "Conteúdo da tabela documentos_motoristas lida pelo PHP:\n";
$result = $pdo->query("SELECT * FROM documentos_motoristas")->fetchAll(PDO::FETCH_ASSOC);
print_r($result);

echo "\n\n========================\n";
echo "Agora testando filtro por ID\n";

if (!isset($_GET["motorista_id"])) {
    echo "Nenhum ID informado\n";
    exit;
}

$id = intval($_GET["motorista_id"]);
echo "Motorista ID recebido: $id\n\n";

$stmt = $pdo->prepare("
    SELECT *
    FROM documentos_motoristas
    WHERE motorista_id = ?
");
$stmt->execute([$id]);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

exit;