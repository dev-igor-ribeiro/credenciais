<?php
/**
 * BoraCar — Backup automático do banco de dados
 * Salva em /backups/ com nome backup_YYYY-MM-DD_HH-MM.sql
 * Mantém apenas os últimos 15 arquivos
 *
 * Configurar cron no cPanel:
 *   0 3 * * * /usr/local/bin/php /home1/boraca19/public_html/login/credenciais/backup/backup.php
 */

$host    = 'localhost';
$db      = 'boraca19_credenciais';
$user    = 'boraca19_novo';
$pass    = '#Ribeiro123';
$charset = 'utf8';

$maxBackups = 15;

$pastaBackup = __DIR__ . '/../backups/';
if (!is_dir($pastaBackup)) {
    mkdir($pastaBackup, 0755, true);
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

$nomeArquivo = 'backup_' . date('Y-m-d_H-i') . '.sql';
$caminhoArquivo = $pastaBackup . $nomeArquivo;

$sql = "-- BoraCar Backup\n";
$sql .= "-- Data: " . date('Y-m-d H:i:s') . "\n";
$sql .= "-- Banco: $db\n\n";
$sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

// Busca todas as tabelas
$tabelas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tabelas as $tabela) {
    // Estrutura da tabela
    $createStmt = $pdo->query("SHOW CREATE TABLE `$tabela`")->fetch(PDO::FETCH_ASSOC);
    $sql .= "-- Tabela: $tabela\n";
    $sql .= "DROP TABLE IF EXISTS `$tabela`;\n";
    $sql .= $createStmt['Create Table'] . ";\n\n";

    // Dados da tabela
    $rows = $pdo->query("SELECT * FROM `$tabela`")->fetchAll(PDO::FETCH_ASSOC);
    if (count($rows) > 0) {
        $colunas = '`' . implode('`, `', array_keys($rows[0])) . '`';
        $sql .= "INSERT INTO `$tabela` ($colunas) VALUES\n";
        $valores = [];
        foreach ($rows as $row) {
            $vals = array_map(function ($v) use ($pdo) {
                return $v === null ? 'NULL' : $pdo->quote($v);
            }, array_values($row));
            $valores[] = '(' . implode(', ', $vals) . ')';
        }
        $sql .= implode(",\n", $valores) . ";\n\n";
    }
}

$sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

// Salva o arquivo
file_put_contents($caminhoArquivo, $sql);

// Remove backups antigos mantendo apenas os últimos $maxBackups
$arquivos = glob($pastaBackup . 'backup_*.sql');
if (count($arquivos) > $maxBackups) {
    usort($arquivos, fn($a, $b) => filemtime($a) - filemtime($b));
    $remover = array_slice($arquivos, 0, count($arquivos) - $maxBackups);
    foreach ($remover as $arquivo) {
        unlink($arquivo);
    }
}

echo "Backup concluído: $nomeArquivo (" . round(filesize($caminhoArquivo) / 1024, 1) . " KB)\n";
