<?php
require_once 'config.php';

// Destruir todas as variáveis de sessão
$_SESSION = array();

// Se deseja destruir a sessão completamente, apague também o cookie da sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir a sessão
session_destroy();

// Redirecionar para a página de login
header("Location: index.php");
exit;
?>
