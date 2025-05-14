<?php
// Configuração da conexão com o base de dados
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
    // Verificar se estamos em uma página administrativa
    $is_admin_page = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false || 
                      basename($_SERVER['PHP_SELF']) == 'index.php' && dirname($_SERVER['PHP_SELF']) == '/admin');
    
    if ($is_admin_page) {
        // Usar uma sessão específica para administração
        session_name('admin_session');
    }
    
    session_start();
}

// Função para verificar se o Utilizador está logado como admin
function verificarLoginAdmin() {
    if (!isset($_SESSION['admin_logado']) || $_SESSION['admin_logado'] !== true) {
        header("Location: index.php");
        exit;
    }
}

// Função para verificar se o Utilizador está logado como cliente
function verificarLogin() {
    if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
        header("Location: ../Login/login.php");
        exit;
    }
}

// Função para verificar credenciais de admin fixas
function verificarCredenciaisAdmin($email, $senha) {
    // Credenciais fixas para o admin geral
    if ($email === "admingeral@admingeral" && $senha === "admingeral@admin") {
        $_SESSION['admin_logado'] = true;
        $_SESSION['admin_email'] = $email;
        $_SESSION['admin_role'] = 'admin';
        return true;
    }
    return false;
}
?>
