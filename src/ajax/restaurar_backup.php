<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    http_response_code(403);
    echo json_encode(['erro' => 'Não autorizado']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$arquivo = trim($_POST['arquivo'] ?? '');

// Segurança: valida nome do arquivo (apenas backup_*.sql, sem barras)
if (!preg_match('/^backup_[\d_\-]+\.sql$/', $arquivo)) {
    echo json_encode(['erro' => 'Arquivo inválido.']);
    exit;
}

$caminho = __DIR__ . '/../../backups/' . $arquivo;

if (!file_exists($caminho)) {
    echo json_encode(['erro' => 'Arquivo não encontrado.']);
    exit;
}

require_once '../../db/conexao_motoristas.php';

$sql = file_get_contents($caminho);

if ($sql === false) {
    echo json_encode(['erro' => 'Não foi possível ler o arquivo de backup.']);
    exit;
}

try {
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

    // Executa o SQL completo (múltiplos statements)
    $pdo->exec($sql);

    echo json_encode([
        'sucesso'  => true,
        'mensagem' => "Banco restaurado com sucesso a partir de: $arquivo"
    ]);
} catch (PDOException $e) {
    echo json_encode(['erro' => 'Erro ao restaurar: ' . $e->getMessage()]);
}
