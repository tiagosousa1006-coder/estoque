<?php
$host = getenv('DB_HOST') ?: 'localhost';
$db   = getenv('DB_NAME') ?: 'estoque';
$user = getenv('DB_USER') ?: 'tiagosousa';
$pass = getenv('DB_PASS') ?: 'Colipo0020!';

if ($user === '' || $pass === '') {
    die("Erro: credenciais de banco não configuradas. Defina DB_USER e DB_PASS.");
}

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro: " . $conn->connect_error);
}
?>