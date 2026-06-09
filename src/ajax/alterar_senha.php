<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['ok' => false, 'erro' => 'Sessão expirada.']);
    exit;
}

require_once '../../db/conexao.php';

$senhaAtual = trim($_POST['senha_atual'] ?? '');
$novaSenha  = trim($_POST['nova_senha']  ?? '');
$usuario    = $_SESSION['usuario'];

if (!$senhaAtual || !$novaSenha) {
    echo json_encode(['ok' => false, 'erro' => 'Campos obrigatórios.']);
    exit;
}

if (strlen($novaSenha) < 6) {
    echo json_encode(['ok' => false, 'erro' => 'Mínimo de 6 caracteres.']);
    exit;
}

// Busca hash atual
$stmt = $conn->prepare("SELECT senha FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['ok' => false, 'erro' => 'Usuário não encontrado.']);
    exit;
}

$row = $result->fetch_assoc();

if (!password_verify($senhaAtual, $row['senha'])) {
    echo json_encode(['ok' => false, 'erro' => 'Senha atual incorreta.']);
    exit;
}

// Atualiza
$hash = password_hash($novaSenha, PASSWORD_DEFAULT);
$upd  = $conn->prepare("UPDATE usuarios SET senha = ? WHERE usuario = ?");
$upd->bind_param("ss", $hash, $usuario);
$upd->execute();

echo json_encode(['ok' => true]);
