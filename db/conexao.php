<?php
$host = 'localhost';
$db = 'boraca19_boracar_login';
$user = 'boraca19_novo';
$pass = '#Ribeiro123';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
?>