<?php
require_once 'config.php';

// Iniciar a sessão administrativa
session_name('admin_session');
session_start();

// Destruir todas as variáveis da sessão administrativa
$_SESSION = array();

// Se deseja destruir a sessão completamente, apague também o cookie da sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir a sessão administrativa
session_destroy();

// Redirecionar para a página de login administrativa
header("Location: index.php");
exit;
?>
