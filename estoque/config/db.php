<?php
$host = "localhost";
$db   = "estoque";
$user = "estoque_user";
$pass = "@Colipo0020";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro: " . $conn->connect_error);
}
?>