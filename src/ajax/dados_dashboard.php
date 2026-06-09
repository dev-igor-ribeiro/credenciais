<?php
ob_start();
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../../db/conexao_motoristas.php';

    // ── 1. Distribuição atual de status ──
    $stmt = $pdo->query("
        SELECT
            SUM(CASE WHEN status='suspenso' THEN 1 ELSE 0 END) AS suspensos,
            SUM(CASE WHEN status='pendente' OR validade IS NULL OR validade='0000-00-00' THEN 1 ELSE 0 END) AS pendentes,
            SUM(CASE WHEN status NOT IN ('suspenso','pendente') AND validade IS NOT NULL AND validade!='0000-00-00' AND DATEDIFF(validade, CURDATE()) < 0 THEN 1 ELSE 0 END) AS vencidos,
            SUM(CASE WHEN status NOT IN ('suspenso','pendente') AND validade IS NOT NULL AND validade!='0000-00-00' AND DATEDIFF(validade, CURDATE()) BETWEEN 0 AND 30 THEN 1 ELSE 0 END) AS a_vencer,
            SUM(CASE WHEN status NOT IN ('suspenso','pendente') AND validade IS NOT NULL AND validade!='0000-00-00' AND DATEDIFF(validade, CURDATE()) > 30 THEN 1 ELSE 0 END) AS validos
        FROM motoristas
    ");
    $status = $stmt->fetch(PDO::FETCH_ASSOC);

    // ── 2. Vencimentos por mês (próximos 12 meses + últimos 6) ──
    $stmt2 = $pdo->query("
        SELECT DATE_FORMAT(validade, '%Y-%m') AS mes, COUNT(*) AS total
        FROM motoristas
        WHERE validade IS NOT NULL AND validade != '0000-00-00'
          AND validade BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                           AND DATE_ADD(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY mes
        ORDER BY mes ASC
    ");
    $vencimentos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // ── 3. Cadastros por mês (últimos 12 meses) ──
    $stmt3 = $pdo->query("
        SELECT DATE_FORMAT(criado_em, '%Y-%m') AS mes, COUNT(*) AS total
        FROM motoristas
        WHERE criado_em >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY mes
        ORDER BY mes ASC
    ");
    $cadastros = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    // ── 4. Top 5 modelos de veículos ──
    $stmt4 = $pdo->query("
        SELECT modelo, COUNT(*) AS total
        FROM motoristas
        WHERE modelo IS NOT NULL AND modelo != ''
        GROUP BY modelo
        ORDER BY total DESC
        LIMIT 8
    ");
    $modelos = $stmt4->fetchAll(PDO::FETCH_ASSOC);

    // ── 5. Totais gerais ──
    $stmt5 = $pdo->query("SELECT COUNT(*) AS total FROM motoristas");
    $totalGeral = $stmt5->fetchColumn();

    ob_end_clean();
    echo json_encode([
        'status'      => $status,
        'vencimentos' => $vencimentos,
        'cadastros'   => $cadastros,
        'modelos'     => $modelos,
        'total'       => (int)$totalGeral,
    ]);
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['erro' => $e->getMessage()]);
}
