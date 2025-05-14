<?php
$servername = "localhost";
$username = "root";  // Normalmente é "root" para instalações locais do XAMPP
$password = "";      // Senha em branco é comum em instalações locais do XAMPP
$dbname = "lopesarmazem";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Definir o conjunto de caracteres para UTF-8
$conn->set_charset("utf8");

// Nota: Não feche a conexão aqui. Ela será fechada no arquivo que inclui db_connect.php
?>
