<?php
// Webhook de deploy automático — chamado pelo GitHub em cada push
// Segredo compartilhado (configure o mesmo no GitHub)
define('WEBHOOK_SECRET', 'boracar_deploy_2026');

// Valida assinatura do GitHub
$payload   = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$expected  = 'sha256=' . hash_hmac('sha256', $payload, WEBHOOK_SECRET);

if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    die('Forbidden');
}

// Só processa push no branch main
$data   = json_decode($payload, true);
$branch = $data['ref'] ?? '';
if ($branch !== 'refs/heads/main') {
    echo 'Branch ignorado: ' . $branch;
    exit;
}

// Executa git pull
$output = shell_exec('cd /home1/boraca19/public_html/login/credenciais && git pull origin main 2>&1');

// Log do resultado
$log = date('Y-m-d H:i:s') . " | " . trim($output) . "\n";
file_put_contents(__DIR__ . '/webhook_log.txt', $log, FILE_APPEND);

http_response_code(200);
echo 'Deploy OK: ' . htmlspecialchars(trim($output));
