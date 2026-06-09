<?php
require_once '../../db/conexao_motoristas.php';
require_once '../helpers/log.php';

try {
    // Aceita o parâmetro 'id' via GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        file_put_contents(__DIR__ . "/debug_excluir.log", "ID recebido: " . $id . PHP_EOL, FILE_APPEND);
        // Prepare statement com PDO
        $stmt = $pdo->prepare("DELETE FROM motoristas WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            registrarLog($pdo, 'Excluiu', "Motorista ID: $id");
            echo "sucesso";
        } else {
            echo "erro: motorista não encontrado";
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $dados = json_decode(file_get_contents("php://input"), true);
        $ids = $dados['ids'] ?? [];
        if (empty($ids)) {
            http_response_code(400);
            echo "Nenhum ID enviado.";
            exit;
        }
        $placeholders = rtrim(str_repeat('?,', count($ids)), ',');
        $sql = "DELETE FROM motoristas WHERE id IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($ids);
        registrarLog($pdo, 'Excluiu', "Exclusão em massa: " . count($ids) . " motorista(s) | IDs: " . implode(', ', $ids));
        echo "sucesso";
    } else {
        http_response_code(400);
        echo "ID inválido";
    }
} catch (Exception $e) {
    echo "erro: " . $e->getMessage();
}
?>