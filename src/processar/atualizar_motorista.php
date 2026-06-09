<?php
require_once '../../db/conexao_motoristas.php';
require_once '../helpers/log.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo "metodo_invalido"; exit; }

try {
    $id            = (int)($_POST['id'] ?? 0);
    $nome          = trim($_POST['nome']      ?? '');
    $cnh           = trim($_POST['cnh']       ?? '');
    $cpf           = trim($_POST['cpf']       ?? '');
    $validade      = trim($_POST['validade']  ?? '');
    $modelo        = trim($_POST['modelo']    ?? '');
    $ano           = trim($_POST['ano']       ?? '');
    $placa         = trim($_POST['placa']     ?? '');
    $credencial    = trim($_POST['credencial']?? '');
    $status_manual = trim($_POST['status']    ?? 'automatico');

    // Converte validade para yyyy-mm-dd
    $validade_formatada = null;
    if ($validade) {
        $dt = preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $validade)
            ? DateTime::createFromFormat('d/m/Y', $validade)
            : new DateTime($validade);
        $validade_formatada = $dt ? $dt->format('Y-m-d') : null;
    }

    // Calcula status
    if ($status_manual === 'suspenso' || $status_manual === 'pendente') {
        $status = $status_manual;
    } else {
        $status = 'valido';
        if ($validade_formatada) {
            $dias = (int)(new DateTime())->diff(new DateTime($validade_formatada))->format('%r%a');
            if ($dias < 0)       $status = 'vencido';
            elseif ($dias <= 30) $status = 'a_vencer';
        }
    }

    // ── Busca valores atuais para comparação ──
    $antes = $pdo->prepare("SELECT nome, cnh, cpf, validade, modelo, ano, placa, credencial, status FROM motoristas WHERE id = ?");
    $antes->execute([$id]);
    $atual = $antes->fetch(PDO::FETCH_ASSOC);

    // ── Atualiza ──
    $sql = "UPDATE motoristas
            SET nome=:nome, cnh=:cnh, cpf=:cpf, validade=:validade,
                modelo=:modelo, ano=:ano, placa=:placa,
                credencial=:credencial, status=:status
            WHERE id=:id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome'       => $nome,
        ':cnh'        => $cnh,
        ':cpf'        => $cpf,
        ':validade'   => $validade_formatada,
        ':modelo'     => $modelo,
        ':ano'        => $ano,
        ':placa'      => $placa,
        ':credencial' => $credencial,
        ':status'     => $status,
        ':id'         => $id,
    ]);

    // ── Grava histórico de edições (campo a campo) ──
    if ($atual) {
        $mapa = [
            'nome'       => [$atual['nome'],                 $nome],
            'cnh'        => [$atual['cnh'],                  $cnh],
            'cpf'        => [$atual['cpf'],                  $cpf],
            'validade'   => [$atual['validade'],              $validade_formatada],
            'modelo'     => [$atual['modelo'],               $modelo],
            'ano'        => [$atual['ano'],                  $ano],
            'placa'      => [$atual['placa'],                $placa],
            'credencial' => [$atual['credencial'],           $credencial],
            'status'     => [$atual['status'],               $status],
        ];

        // Nomes legíveis para os campos
        $labels = [
            'nome' => 'Nome', 'cnh' => 'CNH', 'cpf' => 'CPF',
            'validade' => 'Validade', 'modelo' => 'Modelo', 'ano' => 'Ano',
            'placa' => 'Placa', 'credencial' => 'Credencial', 'status' => 'Status',
        ];

        session_start_if_not_started();
        $usuario = $_SESSION['usuario'] ?? 'sistema';

        $ins = $pdo->prepare(
            "INSERT INTO historico_edicoes (motorista_id, usuario, campo, valor_anterior, valor_novo)
             VALUES (?, ?, ?, ?, ?)"
        );

        foreach ($mapa as $campo => [$vAntes, $vDepois]) {
            $vAntes  = (string)($vAntes  ?? '');
            $vDepois = (string)($vDepois ?? '');
            if ($vAntes !== $vDepois) {
                $ins->execute([$id, $usuario, $labels[$campo], $vAntes, $vDepois]);
            }
        }
    }

    registrarLog($pdo, 'Editou', "Motorista: $nome | Credencial: $credencial | Status: $status | ID: $id");
    echo "sucesso";

} catch (Exception $e) {
    echo "erro: " . $e->getMessage();
}
