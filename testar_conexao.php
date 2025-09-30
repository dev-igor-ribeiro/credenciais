<?php
$host = 'localhost';
$user = 'boraca19_igor';
$pass = '98wp9tYoA7';
$db = 'boraca19_boracar_login'; // ou boraca19_credenciais, teste os dois

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
} else {
    echo "✅ Conexão com o banco <strong>$db</strong> foi bem-sucedida!";
}
?>