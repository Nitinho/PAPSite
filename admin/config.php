<?php
// Configuração da conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lopesarmazem";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Iniciar sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Função para verificar se o usuário está logado
function verificarLogin() {
    if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
        header("Location: index.php");
        exit;
    }
}
?>
