<?php
/**
 * Registra uma ação no log do sistema.
 *
 * @param PDO    $pdo       Conexão PDO já aberta
 * @param string $acao      Ex: 'Cadastrou', 'Editou', 'Excluiu', 'Backup', 'Restaurou'
 * @param string $descricao Ex: 'Motorista Ana Costa (credencial 472)'
 */
function registrarLog(PDO $pdo, string $acao, string $descricao): void {
    try {
        session_start_if_not_started();
        $usuario = $_SESSION['usuario'] ?? 'sistema';
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        $stmt = $pdo->prepare(
            "INSERT INTO log_acoes (usuario, acao, descricao, ip) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$usuario, $acao, $descricao, $ip]);
    } catch (Throwable $e) {
        // Log não deve quebrar a operação principal
        error_log('Erro ao registrar log: ' . $e->getMessage());
    }
}

function session_start_if_not_started(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
