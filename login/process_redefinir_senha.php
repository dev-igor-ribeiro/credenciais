<?php
session_start();
require_once '../db/conexao.php'; // boraca19_boracar_login via mysqli $conn

$token   = trim($_POST['token']   ?? '');
$senha   = trim($_POST['senha']   ?? '');
$confirmar = trim($_POST['confirmar'] ?? '');

$redirect = "redefinir_senha.php?token=" . urlencode($token);

if (!$token || !$senha || !$confirmar) {
    header("Location: $redirect&erro=" . urlencode("Preencha todos os campos."));
    exit;
}

if (strlen($senha) < 6) {
    header("Location: $redirect&erro=" . urlencode("A senha deve ter no mínimo 6 caracteres."));
    exit;
}

if ($senha !== $confirmar) {
    header("Location: $redirect&erro=" . urlencode("As senhas não coincidem."));
    exit;
}

// Valida token
$stmt = $conn->prepare(
    "SELECT usuario FROM reset_tokens WHERE token = ? AND expira_em > NOW() AND usado = 0"
);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: redefinir_senha.php?token=invalido");
    exit;
}

$row     = $result->fetch_assoc();
$usuario = $row['usuario'];

// Atualiza senha
$hash = password_hash($senha, PASSWORD_DEFAULT);
$upd  = $conn->prepare("UPDATE usuarios SET senha = ? WHERE usuario = ?");
$upd->bind_param("ss", $hash, $usuario);
$upd->execute();

// Marca token como usado
$used = $conn->prepare("UPDATE reset_tokens SET usado = 1 WHERE token = ?");
$used->bind_param("s", $token);
$used->execute();

header("Location: redefinir_senha.php?token=" . urlencode($token) . "&sucesso=1");
exit;
