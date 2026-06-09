<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    http_response_code(403);
    echo json_encode(['erro' => 'Não autorizado']);
    exit;
}

$pastaBackup = __DIR__ . '/../../backups/';
$arquivos = glob($pastaBackup . 'backup_*.sql');

if (!$arquivos) {
    echo json_encode([]);
    exit;
}

// Ordena do mais recente para o mais antigo
usort($arquivos, fn($a, $b) => filemtime($b) - filemtime($a));

$lista = array_map(function($arquivo) {
    $nome = basename($arquivo);
    $tamanho = filesize($arquivo);
    $data = filemtime($arquivo);

    // Formata tamanho
    if ($tamanho >= 1048576) {
        $tamanhoStr = round($tamanho / 1048576, 1) . ' MB';
    } else {
        $tamanhoStr = round($tamanho / 1024, 1) . ' KB';
    }

    return [
        'nome'      => $nome,
        'tamanho'   => $tamanhoStr,
        'data'      => date('d/m/Y H:i', $data),
        'timestamp' => $data,
    ];
}, $arquivos);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($lista);
