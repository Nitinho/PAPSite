<?php
// Iniciar uma sessão específica para admin com configurações de segurança
session_name('admin_session');
session_start([
    'cookie_httponly' => true,     // Prevenir acesso ao cookie via JavaScript
    'cookie_secure' => true,       // Usar apenas em HTTPS
    'cookie_samesite' => 'Strict', // Prevenir CSRF
    'use_strict_mode' => true      // Rejeitar IDs de sessão não iniciados pelo servidor
]);

// Definir tempo limite de inatividade (30 minutos)
$timeout = 1800; // 30 minutos em segundos
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => true,
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true
    ]);
}
$_SESSION['last_activity'] = time();

// Regenerar ID de sessão periodicamente para prevenir fixação de sessão
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Verificar se já está logado como admin
if (isset($_SESSION['admin_logado']) && $_SESSION['admin_logado'] === true) {
    header("Location: dashboard.php");
    exit;
}

// Inicializar variáveis
$erro = '';
$tentativas = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] : 0;
$bloqueado = false;
$tempo_restante = 0;

// Verificar se o usuário está bloqueado por excesso de tentativas
if (isset($_SESSION['lockout_time'])) {
    $tempo_bloqueio = 15 * 60; // 15 minutos em segundos
    $tempo_passado = time() - $_SESSION['lockout_time'];
    
    if ($tempo_passado < $tempo_bloqueio) {
        $bloqueado = true;
        $tempo_restante = $tempo_bloqueio - $tempo_passado;
    } else {
        // Desbloquear após o tempo
        unset($_SESSION['lockout_time']);
        $_SESSION['login_attempts'] = 0;
        $tentativas = 0;
    }
}

// Processar o formulário de login
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$bloqueado) {
    // Proteção contra CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $erro = "Erro de validação do formulário. Por favor, tente novamente.";
    } else {
        // Limitar tentativas (máximo 5)
        if ($tentativas >= 5) {
            $_SESSION['lockout_time'] = time();
            $bloqueado = true;
            $tempo_restante = 15 * 60; // 15 minutos
            $erro = "Muitas tentativas de login. Conta temporariamente bloqueada.";
        } else {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $senha = $_POST['senha'];
            
            // Verificar credenciais fixas para admin (em produção, usar banco de dados e hash)
            if ($email === "admingeral@admingeral" && $senha === "admingeral@admingeral") {
                // Login bem-sucedido
                $_SESSION['login_attempts'] = 0;
                $_SESSION['admin_logado'] = true;
                $_SESSION['admin_email'] = $email;
                $_SESSION['admin_role'] = 'admin';
                $_SESSION['login_time'] = time();
                
                // Registrar login bem-sucedido (em produção, usar log adequado)
                $ip = $_SERVER['REMOTE_ADDR'];
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                error_log("Login bem-sucedido: $email, IP: $ip, User-Agent: $user_agent");
                
                // Regenerar ID de sessão após login para prevenir fixação de sessão
                session_regenerate_id(true);
                
                header("Location: dashboard.php");
                exit;
            } else {
                // Incrementar contador de tentativas
                $_SESSION['login_attempts'] = $tentativas + 1;
                $tentativas = $_SESSION['login_attempts'];
                
                // Mensagem de erro genérica (não revelar qual campo está incorreto)
                $erro = "Credenciais inválidas. Tentativa " . $tentativas . " de 5.";
                
                // Registrar tentativa falha (em produção, usar log adequado)
                $ip = $_SERVER['REMOTE_ADDR'];
                error_log("Tentativa de login falha: $email, IP: $ip, Tentativa: $tentativas");
            }
        }
    }
}

// Gerar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Painel Administrativo - Área de Login">
    <link rel="shortcut icon" type="image/x-icon" href="../img/logolopes.ico">

    <meta name="robots" content="noindex, nofollow">
    <title>Login - Painel Administrativo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #764ba2;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Nunito', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 35px;
            margin: auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }
        
        .login-container:hover {
            transform: translateY(-5px);
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo {
            max-width: 150px;
            height: auto;
            transition: transform 0.3s ease;
        }
        
        .logo:hover {
            transform: scale(1.05);
        }
        
        .login-title {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 25px;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }
        
        .login-title:after {
            content: '';
            position: absolute;
            width: 50px;
            height: 3px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .input-group {
            position: relative;
        }
        
        .form-control {
            border-radius: 5px;
            padding: 15px 40px 15px 15px;
            height: auto;
            border: 1px solid #ddd;
            transition: all 0.3s;
            font-size: 16px;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(118, 75, 162, 0.25);
        }
        
        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            z-index: 10;
            transition: color 0.3s;
        }
        
        .form-control:focus + .input-icon {
            color: var(--secondary-color);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 5px;
            padding: 15px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            color: white;
            font-size: 16px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background: linear-gradient(135deg, #5a71d5 0%, #6a4292 100%);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert {
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 25px;
            border-left: 4px solid;
        }
        
        .alert-danger {
            border-left-color: var(--danger-color);
            background-color: rgba(231, 74, 59, 0.1);
        }
        
        .alert-warning {
            border-left-color: var(--warning-color);
            background-color: rgba(246, 194, 62, 0.1);
        }
        
        label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
            display: block;
            font-size: 14px;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            cursor: pointer;
            z-index: 10;
            transition: color 0.3s;
        }
        
        .password-toggle:hover {
            color: var(--secondary-color);
        }
        
        .lockout-message {
            text-align: center;
            margin-top: 15px;
            color: var(--danger-color);
            font-size: 14px;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 25px;
            color: #888;
            font-size: 13px;
        }
        
        /* Melhorias para responsividade */
        @media (max-width: 576px) {
            .login-container {
                padding: 25px 20px;
            }
            
            .form-control {
                padding: 12px 40px 12px 12px;
                font-size: 14px;
            }
            
            .btn-login {
                padding: 12px;
                font-size: 14px;
            }
        }
        
        /* Correção para o dropdown de categorias */
        select.form-control {
            padding: 0.5rem 1rem;
            line-height: normal;
            height: auto !important;
            font-kerning: normal;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <img src="../img/logolopes.png" alt="Logo" class="logo">
        </div>
        <h3 class="login-title">Painel Administrativo</h3>
        
        <?php if ($bloqueado): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>Conta temporariamente bloqueada</strong>
                <p class="mb-0">Muitas tentativas de login incorretas. Tente novamente em 
                    <span id="countdown"><?php echo floor($tempo_restante / 60) . ':' . str_pad($tempo_restante % 60, 2, '0', STR_PAD_LEFT); ?></span>.
                </p>
            </div>
        <?php elseif (!empty($erro)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo $erro; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="loginForm" <?php if ($bloqueado) echo 'style="display:none;"'; ?>>
            <!-- Token CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope mr-1"></i> Email</label>
                <div class="input-group">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Seu email" required autocomplete="username">
                    <span class="input-icon">
                        <i class="fas fa-envelope"></i>
                    </span>
                </div>
            </div>
            <div class="form-group">
                <label for="senha"><i class="fas fa-lock mr-1"></i> Senha</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="senha" name="senha" placeholder="Sua senha" required autocomplete="current-password">
                    <span class="password-toggle" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>
            <button type="submit" class="btn btn-block btn-login">
                <i class="fas fa-sign-in-alt mr-2"></i>Entrar
            </button>
        </form>
        
        <?php if ($bloqueado): ?>
            <div class="lockout-message">
                <p>Por razões de segurança, sua conta foi temporariamente bloqueada devido a múltiplas tentativas de login malsucedidas.</p>
            </div>
        <?php endif; ?>
        
        <div class="footer-text">
            <p>© <?php echo date('Y'); ?> Painel Administrativo. Todos os direitos reservados.</p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Mostrar/ocultar senha
        document.getElementById('togglePassword').addEventListener('click', function() {
            const senhaInput = document.getElementById('senha');
            const icon = this.querySelector('i');
            
            if (senhaInput.type === 'password') {
                senhaInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                senhaInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Contador regressivo para desbloqueio
        <?php if ($bloqueado): ?>
        let tempoRestante = <?php echo $tempo_restante; ?>;
        const countdownElement = document.getElementById('countdown');
        
        const countdownTimer = setInterval(function() {
            tempoRestante--;
            
            if (tempoRestante <= 0) {
                clearInterval(countdownTimer);
                window.location.reload();
            } else {
                const minutos = Math.floor(tempoRestante / 60);
                const segundos = tempoRestante % 60;
                countdownElement.textContent = minutos + ':' + (segundos < 10 ? '0' : '') + segundos;
            }
        }, 1000);
        <?php endif; ?>
        
        // Prevenir reenvio de formulário ao atualizar a página
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
        
        // Animação suave para alertas
        $(document).ready(function() {
            $('.alert').hide().fadeIn(500);
            
            // Adicionar validação de formulário
            $('#loginForm').on('submit', function(e) {
                const email = $('#email').val().trim();
                const senha = $('#senha').val();
                
                if (email === '') {
                    e.preventDefault();
                    $('#email').addClass('is-invalid');
                    return false;
                }
                
                if (senha === '') {
                    e.preventDefault();
                    $('#senha').addClass('is-invalid');
                    return false;
                }
                
                return true;
            });
            
            // Remover classe is-invalid ao digitar
            $('.form-control').on('input', function() {
                $(this).removeClass('is-invalid');
            });
        });
    </script>
</body>
</html>
