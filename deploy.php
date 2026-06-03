<?php
define('SECRET', 'TROQUE_POR_UMA_SENHA_SECRETA');

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if (!hash_equals('sha256=' . hash_hmac('sha256', $payload, SECRET), $signature)) {
    http_response_code(403);
    die('Assinatura inválida.');
}

$output = shell_exec('cd ' . escapeshellarg(__DIR__) . ' && git pull 2>&1');

echo '<pre>' . htmlspecialchars($output) . '</pre>';
