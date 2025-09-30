<?php
session_start();
include('../db/conexao.php');

$usuario = $_POST['usuario'];
$senha = $_POST['senha'];

$sql = "SELECT * FROM usuarios WHERE usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    if (password_verify($senha, $row['senha'])) {
        $_SESSION['usuario'] = $usuario;
        header("Location: ../painel.php");
        exit();
    }
}

$_SESSION['erro_login'] = 'Usuário ou senha incorretos!';
header("Location: ../login/index.php");
exit();
?>