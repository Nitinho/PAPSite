<?php
$conn = new mysqli('localhost', 'root', '', 'lopesarmazem');

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
