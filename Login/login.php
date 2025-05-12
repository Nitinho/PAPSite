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
<html lang="pt-PT">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Armazéns Lopes</title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../img/favicon.ico" type="image/x-icon">
</head>

<body>
    <header>
        <div id="headerimg">
            <a href="../index.php"><img src="../img/logolopes.png" alt="Logo Armazéns Lopes"></a>
        </div>
        <div id="headerselect">
            <a href="../index.php">INÍCIO</a>
            <a href="../index.php#container2">PRODUTOS</a>
            <a href="../index.php#sobre">SOBRE</a>
            <a href="../index.php#container6">CONTACTOS</a>
            <button id="buttonheader"><strong>ÁREA CLIENTE</strong></button>
        </div>

        <div class="mobile-menu-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <nav class="mobile-menu">
            <a href="../index.php">INÍCIO</a>
            <a href="../index.php#container2">PRODUTOS</a>
            <a href="../index.php#sobre">SOBRE</a>
            <a href="../index.php#container6">CONTACTOS</a>
            <a href="../formulario.php">VIRAR CLIENTE</a>
            <a href="#" class="mobile-area-cliente"><strong>ÁREA CLIENTE</strong></a>
        </nav>
    </header>

    <main>
        <div class="login-container">
            <div class="login-image">
                <img src="../img/logolopes.png" alt="Armazéns Lopes">
                <div class="image-overlay">
                    <h2>Bem-vindo de volta!</h2>
                    <p>Aceda à sua conta para gerir os seus pedidos e aproveitar ofertas exclusivas.</p>
                </div>
            </div>

            <div class="login-form-container">
                <div class="login-form-wrapper">
                    <div class="login-header">

                        <h1>Acesso à Área de Cliente</h1>
                        <p>Introduza os seus dados para aceder</p>
                    </div>

                    <?php if (isset($error_message)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="login.php" class="login-form">
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" id="email" name="email" placeholder="exemplo@email.com" required>
                        </div>

                        <div class="form-group">
                            <label for="password"><i class="fas fa-lock"></i> Senha</label>
                            <div class="password-input-container">
                                <input type="password" id="password" name="password" placeholder="Digite sua senha" required>
                                <button type="button" id="toggle-password" class="toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="login-button">
                            <span>Entrar</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>

                        <div class="register-link">
                            <p>Ainda não é cliente? <a href="registrar.php">Faça o seu registo.</a></p>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-logo">

            </div>
            <div class="footer-links">
                <a href="../index.php">Início</a>
                <a href="../index.php#container2">Produtos</a>
                <a href="../index.php#sobre">Sobre</a>
                <a href="../index.php#container6">Contactos</a>
            </div>
            <div class="footer-social">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
            </div>
        </div>
        <div class="footer-bottom">
            <p><strong>© 2024 ARMAZÉNS LOPES. TODOS OS DIREITOS RESERVADOS.</strong></p>
        </div>
    </footer>

    <script>
        // Toggle password visibility
        document.getElementById('toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Mobile menu toggle
        const menuToggle = document.querySelector('.mobile-menu-toggle');
        const mobileMenu = document.querySelector('.mobile-menu');

        if (menuToggle && mobileMenu) {
            menuToggle.addEventListener('click', function() {
                menuToggle.classList.toggle('active');
                mobileMenu.classList.toggle('active');

                // Animação do ícone do menu
                const spans = menuToggle.querySelectorAll('span');
                if (menuToggle.classList.contains('active')) {
                    spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                    spans[1].style.opacity = '0';
                    spans[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
                } else {
                    spans[0].style.transform = 'none';
                    spans[1].style.opacity = '1';
                    spans[2].style.transform = 'none';
                }
            });
        }
    </script>
</body>

</html>