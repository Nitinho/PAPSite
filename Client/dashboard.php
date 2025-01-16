<?php
// Iniciar a sessão
session_start();

// Verificar se o usuário está logado (supondo que você tenha salvo o e-mail na sessão após o login)
if (!isset($_SESSION['email'])) {
    // Se o usuário não estiver logado, redireciona para a página de login
    header("Location: ../Login/login.php");
    exit(); // Garantir que o script seja interrompido após o redirecionamento
}


if (isset($_SESSION['email'])) {
    // Usuário logado, redireciona para o dashboard
    $redirectUrl = "dashboard.php";
} else {
    // Usuário não logado, redireciona para o login
    $redirectUrl = "../login/login.php";
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Cliente</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <header>
        <div id="headerimg">
            <img src="img/logolopes.png" alt="Logo">
        </div>
        <div id="headerselect">
            <a href="../index.php">INICIO</a>
            <a href="../index.php#container2">PRODUTOS</a>
            <a href="#">SOBRE</a>
            <a href="#">CONTATOS</a>
            <a href="../formulario.php">VIRAR CLIENTE</a>
            <button id="buttonheader" onclick="window.location.href='<?php echo $redirectUrl; ?>'"><strong>ÁREA CLIENTE</strong></button>
        </div>
    </header>

    <main>
        <div class="main-container">
            <div class="form-container">
                <h2><strong>Bem-vindo, [Nome do Cliente]</strong></h2>
                <p>Aqui está a sua área do cliente.</p>
                <a href="../Login/logout.php" class="form-submit-btn">Sair</a>
            </div>
        </div>
    </main>

    <footer>
        <p><strong>TODOS OS DIREITOS RESERVADOS NA LOPESMARKET 2024 ©</strong></p>
    </footer>
</body>
</html>
