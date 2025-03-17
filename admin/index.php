<?php
require_once 'config.php';

// Verificar se já está logado
if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header("Location: dashboard.php");
    exit;
}

$erro = '';

// Processar o formulário de login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    
    // Verificar credenciais (admin@admin / admin@admin)
    if ($email === "admin@admin" && $senha === "admin@admin") {
        $_SESSION['logado'] = true;
        $_SESSION['email'] = $email;
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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-form {
            width: 100%;
            max-width: 400px;
            padding: 15px;
            margin: auto;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="login-form">
        <h2 class="text-center mb-4">Painel Administrativo</h2>
        
        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger"><?php echo $erro; ?></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </form>
    </div>
</body>
</html>
