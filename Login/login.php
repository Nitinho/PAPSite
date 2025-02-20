<?php
session_start();
include('config.php');  // A conexão com o banco de dados

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Conectando ao banco de dados
    $conn = getDBConnection();
    
    // Verifique se o usuário existe no banco de dados
    $sql = "SELECT * FROM usuarios WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Comparando a senha informada com o hash da senha no banco de dados
        if (password_verify($password, $user['senha'])) {
            // Se a senha for válida, inicia a sessão do usuário
            $_SESSION['email'] = $user['email']; // Salva o e-mail na sessão
            header("Location: http://localhost/papsite/client/dashboard.php"); // Redireciona para o dashboard
            exit();
        } else {
            // Se a senha estiver incorreta
            $error_message = "Senha incorreta!";
        }
    } else {
        // Se o e-mail não for encontrado no banco de dados
        $error_message = "Usuário não encontrado!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <header>
        <div id="headerimg">
            <img src="../img/logolopes.png" alt="Logo">
        </div>
        <div id="headerselect">
            <a href="../index.php">INICIO</a>
            <a href="../index.php#container2">PRODUTOS</a>
            <a href="..">SOBRE</a>
            <a href="#">CONTATOS</a>
            <a href="../formulario.php">VIRAR CLIENTE</a>
            <button id="buttonheader"><strong>ÁREA CLIENTE</strong></button>
        </div>
    </header>

    <main>
        <div class="main-container">
            <div class="form-container">
                <form method="POST" action="login.php">
                    <h2><strong>LOGIN</strong></h2>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Senha</label>
                        <input type="password" id="password" name="password" placeholder="Digite sua senha" required>
                    </div>
                    <button type="submit" class="form-submit-btn">Entrar</button>
                    <?php if (isset($error_message)): ?>
                        <p style="color: red;"><?php echo $error_message; ?></p>
                    <?php endif; ?>
                    <div class="register-link">
                        <p>Gostava de ser cliente? <a href="formulario.html">Clique aqui</a></p>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <p><strong>TODOS OS DIREITOS RESERVADOS NA LOPESMARKET 2024 ©</strong></p>
    </footer>
</body>
</html>
