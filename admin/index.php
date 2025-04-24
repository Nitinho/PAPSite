<?php
// Iniciar uma sessão específica para admin
session_name('admin_session');
session_start();

// Verificar se já está logado como admin
if (isset($_SESSION['admin_logado']) && $_SESSION['admin_logado'] === true) {
    header("Location: dashboard.php");
    exit;
}

$erro = '';

// Processar o formulário de login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    
    // Verificar credenciais fixas para admin
    if ($email === "admingeral@admingeral" && $senha === "admingeral@admingeral") {
        $_SESSION['admin_logado'] = true;
        $_SESSION['admin_email'] = $email;
        $_SESSION['admin_role'] = 'admin';
        header("Location: dashboard.php");
        exit;
    } else {
        $erro = "Email ou senha incorretos!";
    }
}
?>


<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Administrativo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 30px;
            margin: auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .logo {
            max-width: 150px;
            height: auto;
        }
        
        .login-title {
            color: #4a4a4a;
            font-weight: 600;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .input-group {
            position: relative;
        }
        
        .form-control {
            border-radius: 5px;
            padding: 12px 40px 12px 15px;
            height: auto;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.2rem rgba(118, 75, 162, 0.25);
        }
        
        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            z-index: 10;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 5px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            color: white;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background: linear-gradient(135deg, #5a71d5 0%, #6a4292 100%);
        }
        
        .alert {
            border-radius: 5px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }
        
        .forgot-password a {
            color: #764ba2;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
            color: #667eea;
        }
        
        label {
            font-weight: 500;
            color: #555;
            margin-bottom: 8px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <img src="../img/logolopes.png" alt="Logo" class="logo">
        </div>
        <h3 class="login-title">Painel Administrativo</h3>
        
        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo $erro; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-group">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Seu email" required>
                    <span class="input-icon">
                        <i class="fas fa-envelope"></i>
                    </span>
                </div>
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="senha" name="senha" placeholder="Sua senha" required>
                    <span class="input-icon">
                        <i class="fas fa-lock"></i>
                    </span>
                </div>
            </div>
            <button type="submit" class="btn btn-block btn-login">
                <i class="fas fa-sign-in-alt mr-2"></i>Entrar
            </button>
        </form>
        
        <div class="forgot-password">
            <a href="#">Esqueceu sua senha?</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
