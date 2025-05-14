<?php
session_start();
include('config.php');  // A conexão com o base de dados

$errors = [];

// Função para validação do NIF português
function validateNIF($nif) {
    $nif = trim($nif);
    if (!is_numeric($nif) || strlen($nif) != 9) {
        return false;
    }
    $nif_split = str_split($nif);
    $nif_primeiros_digito = array(1, 2, 3, 5, 6, 7, 8, 9);
    if (!in_array((int)$nif_split[0], $nif_primeiros_digito)) {
        return false;
    }
    $check_digit = 0;
    for ($i = 0; $i < 8; $i++) {
        $check_digit += $nif_split[$i] * (9 - $i);
    }
    $check_digit = 11 - ($check_digit % 11);
    if ($check_digit >= 10) {
        $check_digit = 0;
    }
    return ($check_digit == $nif_split[8]);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Receber dados do formulário
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    $nif = trim($_POST['nif'] ?? '');
    $nome_empresa = trim($_POST['nome_empresa'] ?? '');
    $nif_empresa = trim($_POST['nif_empresa'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');

    // Dados de endereço
    $rua = trim($_POST['rua'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $codigo_postal = trim($_POST['codigo_postal'] ?? '');

    // Validações
    if (empty($nome)) {
        $errors['nome'] = "Nome é obrigatório";
    }

    if (empty($email)) {
        $errors['email'] = "Email é obrigatório";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Email inválido";
    }

    if (empty($senha)) {
        $errors['senha'] = "Senha é obrigatória";
    } elseif (strlen($senha) < 6) {
        $errors['senha'] = "A senha deve ter pelo menos 6 caracteres";
    }

    if ($senha !== $confirmar_senha) {
        $errors['confirmar_senha'] = "As senhas não coincidem";
    }

    if (empty($nif)) {
        $errors['nif'] = "NIF é obrigatório";
    } elseif (!validateNIF($nif)) {
        $errors['nif'] = "NIF inválido (deve ter 9 dígitos e dígito de controlo válido)";
    }

    if (!empty($nif_empresa)) {
        if (!preg_match('/^\d{9}$/', $nif_empresa)) {
            $errors['nif_empresa'] = "NIF da empresa deve conter exatamente 9 dígitos numéricos";
        } elseif (!validateNIF($nif_empresa)) {
            $errors['nif_empresa'] = "NIF da empresa inválido (deve ter 9 dígitos e dígito de controlo válido)";
        }
    }

    if (empty($rua)) {
        $errors['rua'] = "Rua é obrigatória";
    }

    if (empty($numero)) {
        $errors['numero'] = "Número é obrigatório";
    }

    if (empty($cidade)) {
        $errors['cidade'] = "Cidade é obrigatória";
    }

    if (empty($codigo_postal)) {
        $errors['codigo_postal'] = "Código Postal é obrigatório";
    }

    // Se não houver erros, prosseguir com o registro
    if (empty($errors)) {
        $conn = getDBConnection();

        try {
            // Verificar se o email já existe
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                $errors['email'] = "Este email já está em uso";
            } else {
                $conn->beginTransaction();

                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

                $sql = "INSERT INTO users (nome, email, senha, nif, nome_da_empresa, nif_da_empresa, telefone)
                        VALUES (:nome, :email, :senha, :nif, :nome_empresa, :nif_empresa, :telefone)";

                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':senha', $senha_hash);
                $stmt->bindParam(':nif', $nif);
                $stmt->bindParam(':nome_empresa', $nome_empresa);
                $stmt->bindParam(':nif_empresa', $nif_empresa);
                $stmt->bindParam(':telefone', $telefone);
                $stmt->execute();

                $user_id = $conn->lastInsertId();

                $sql = "INSERT INTO enderecos (user_id, rua, numero, cidade, codigo_postal)
                        VALUES (:user_id, :rua, :numero, :cidade, :codigo_postal)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':rua', $rua);
                $stmt->bindParam(':numero', $numero);
                $stmt->bindParam(':cidade', $cidade);
                $stmt->bindParam(':codigo_postal', $codigo_postal);
                $stmt->execute();

                $conn->commit();

                $_SESSION['registro_sucesso'] = "Registro realizado com sucesso! Faça login para continuar.";
                header("Location: login.php");
                exit();
            }
        } catch (PDOException $e) {
            $conn->rollBack();
            $error_message = "Erro ao registrar: " . $e->getMessage();
        }
    }
}

// Função para manter os valores preenchidos em caso de erro
function getValue($field) {
    global $_POST;
    return $_POST[$field] ?? '';
}

// Função para verificar se um campo tem erro
function hasError($field) {
    global $errors;
    return isset($errors[$field]) ? 'error' : '';
}
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | Armazéns Lopes</title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" type="image/x-icon" href="../img/logolopes.ico">
    <style>
        .form-group input.error {
            border-color: var(--primary-color);
            background-color: rgba(255, 76, 76, 0.05);
        }
        .error-feedback {
            color: var(--primary-color);
            font-size: 0.8rem;
            margin-top: 5px;
            display: block;
        }
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--medium-gray);
        }
        .form-section:last-child {
            border-bottom: none;
        }
        .form-section-title {
            display: flex;
            align-items: center;
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 1.2rem;
            font-weight: 600;
        }
        .form-section-title i {
            margin-right: 10px;
            background-color: var(--primary-color);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 0;
        }
        .form-row .form-group {
            flex: 1;
        }
        .password-strength {
            margin-top: 8px;
            font-size: 0.8rem;
        }
        .strength-meter {
            height: 4px;
            background-color: #ddd;
            margin-top: 5px;
            border-radius: 2px;
            overflow: hidden;
        }
        .strength-meter-fill {
            height: 100%;
            width: 0;
            background-color: #ddd;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        .strength-meter-fill.weak {
            width: 33%;
            background-color: #ff4d4d;
        }
        .strength-meter-fill.medium {
            width: 66%;
            background-color: #ffa64d;
        }
        .strength-meter-fill.strong {
            width: 100%;
            background-color: #2ecc71;
        }
        .strength-text {
            font-size: 0.8rem;
            margin-top: 5px;
        }
    </style>
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
        <div class="register-container">
            <div class="register-form-container">
                <div class="register-form-wrapper">
                    <div class="register-header">
                        <h1>Criar Conta</h1>
                        <p>Preencha os dados abaixo para se tornar cliente</p>
                    </div>
                    <?php if (!empty($errors) && !isset($errors['specific'])): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            Por favor, corrija os erros no formulário para continuar.
                        </div>
                    <?php endif; ?>
                    <?php if (isset($error_message)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="registrar.php" class="register-form" novalidate>
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fas fa-user"></i>
                                <span>Dados Pessoais</span>
                            </div>
                            <div class="form-group">
                                <label for="nome">Nome Completo</label>
                                <input type="text" id="nome" name="nome" value="<?php echo getValue('nome'); ?>" class="<?php echo hasError('nome'); ?>" required>
                                <?php if (isset($errors['nome'])): ?>
                                    <span class="error-feedback"><?php echo $errors['nome']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" value="<?php echo getValue('email'); ?>" class="<?php echo hasError('email'); ?>" autocomplete="username" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <span class="error-feedback"><?php echo $errors['email']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label for="telefone">Telefone</label>
                                    <input type="tel" id="telefone" name="telefone" value="<?php echo getValue('telefone'); ?>" class="<?php echo hasError('telefone'); ?>">
                                    <?php if (isset($errors['telefone'])): ?>
                                        <span class="error-feedback"><?php echo $errors['telefone']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="senha">Senha</label>
                                    <div class="password-input-container">
                                        <input type="password" id="senha" name="senha" class="<?php echo hasError('senha'); ?>" autocomplete="new-password" required>
                                        <button type="button" class="toggle-password" data-target="senha">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <?php if (isset($errors['senha'])): ?>
                                        <span class="error-feedback"><?php echo $errors['senha']; ?></span>
                                    <?php else: ?>
                                        <div class="password-strength">
                                            <div class="strength-meter">
                                                <div class="strength-meter-fill"></div>
                                            </div>
                                            <div class="strength-text"></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label for="confirmar_senha">Confirmar Senha</label>
                                    <div class="password-input-container">
                                        <input type="password" id="confirmar_senha" name="confirmar_senha" class="<?php echo hasError('confirmar_senha'); ?>" autocomplete="new-password" required>
                                        <button type="button" class="toggle-password" data-target="confirmar_senha">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <?php if (isset($errors['confirmar_senha'])): ?>
                                        <span class="error-feedback"><?php echo $errors['confirmar_senha']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="nif">NIF (Número de Identificação Fiscal)</label>
                                <input type="text" id="nif" name="nif" maxlength="9" pattern="\d{9}" value="<?php echo getValue('nif'); ?>" class="<?php echo hasError('nif'); ?>" required>
                                <?php if (isset($errors['nif'])): ?>
                                    <span class="error-feedback"><?php echo $errors['nif']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fas fa-building"></i>
                                <span>Dados da Empresa</span>
                            </div>
                            <div class="form-group">
                                <label for="nome_empresa">Nome da Empresa</label>
                                <input type="text" id="nome_empresa" name="nome_empresa" value="<?php echo getValue('nome_empresa'); ?>" class="<?php echo hasError('nome_empresa'); ?>">
                                <?php if (isset($errors['nome_empresa'])): ?>
                                    <span class="error-feedback"><?php echo $errors['nome_empresa']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="nif_empresa">NIF da Empresa</label>
                                <input type="text" id="nif_empresa" name="nif_empresa" maxlength="9" pattern="\d{9}" value="<?php echo getValue('nif_empresa'); ?>" class="<?php echo hasError('nif_empresa'); ?>">
                                <?php if (isset($errors['nif_empresa'])): ?>
                                    <span class="error-feedback"><?php echo $errors['nif_empresa']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Endereço</span>
                            </div>
                            <div class="form-group">
                                <label for="rua">Rua</label>
                                <input type="text" id="rua" name="rua" value="<?php echo getValue('rua'); ?>" class="<?php echo hasError('rua'); ?>" required>
                                <?php if (isset($errors['rua'])): ?>
                                    <span class="error-feedback"><?php echo $errors['rua']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="numero">Número</label>
                                    <input type="text" id="numero" name="numero" value="<?php echo getValue('numero'); ?>" class="<?php echo hasError('numero'); ?>" required>
                                    <?php if (isset($errors['numero'])): ?>
                                        <span class="error-feedback"><?php echo $errors['numero']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label for="cidade">Cidade</label>
                                    <input type="text" id="cidade" name="cidade" value="<?php echo getValue('cidade'); ?>" class="<?php echo hasError('cidade'); ?>" required>
                                    <?php if (isset($errors['cidade'])): ?>
                                        <span class="error-feedback"><?php echo $errors['cidade']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="codigo_postal">Código Postal</label>
                                <input type="text" id="codigo_postal" name="codigo_postal" value="<?php echo getValue('codigo_postal'); ?>" class="<?php echo hasError('codigo_postal'); ?>" placeholder="0000-000" required>
                                <?php if (isset($errors['codigo_postal'])): ?>
                                    <span class="error-feedback"><?php echo $errors['codigo_postal']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <button type="submit" class="register-button">
                            <span>Criar Conta</span>
                            <i class="fas fa-user-plus"></i>
                        </button>
                        <div class="login-link">
                            <p>Já é cliente? <a href="login.php">Faça login</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <!-- Logo no footer se necessário -->
            </div>
            <div class="footer-links">
                <a href="../index.php">Início</a>
                <a href="../index.php#container2">Produtos</a>
                <a href="../index.php#sobre">Sobre</a>
                <a href="../index.php#container6">Contactos</a>
            </div>
            <div class="footer-social">
                <a href="https://www.facebook.com/escolabasica.secundariaourem/?locale=pt_PT"><i class="fab fa-facebook"></i></a>
                <a href="https://www.instagram.com/aeourem/"><i class="fab fa-instagram"></i></a>

                
            </div>
        </div>
        <div class="footer-bottom">
            <p><strong>© 2025 ARMAZÉNS LOPES. TODOS OS DIREITOS RESERVADOS.</strong></p>
        </div>
    </footer>

    <script>
       
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
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
        });

        // Força da senha
        const passwordInput = document.getElementById('senha');
        const strengthMeter = document.querySelector('.strength-meter-fill');
        const strengthText = document.querySelector('.strength-text');
        if (passwordInput && strengthMeter && strengthText) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                let message = '';
                if (password.length > 0) {
                    if (password.length >= 8) strength += 1;
                    if (password.match(/[a-z]+/)) strength += 1;
                    if (password.match(/[A-Z]+/)) strength += 1;
                    if (password.match(/[0-9]+/)) strength += 1;
                    if (password.match(/[^a-zA-Z0-9]+/)) strength += 1;
                    strengthMeter.classList.remove('weak', 'medium', 'strong');
                    if (strength <= 2) {
                        strengthMeter.classList.add('weak');
                        message = 'Fraca';
                    } else if (strength <= 4) {
                        strengthMeter.classList.add('medium');
                        message = 'Média';
                    } else {
                        strengthMeter.classList.add('strong');
                        message = 'Forte';
                    }
                    strengthText.textContent = message;
                } else {
                    strengthMeter.style.width = '0';
                    strengthMeter.className = 'strength-meter-fill';
                    strengthText.textContent = '';
                }
            });
        }

        // Formatação do código postal
        const codigoPostalInput = document.getElementById('codigo_postal');
        if (codigoPostalInput) {
            codigoPostalInput.addEventListener('input', function(e) {
                let value = this.value.replace(/\D/g, '');
                if (value.length > 7) {
                    value = value.substring(0, 7);
                }
                if (value.length > 4) {
                    this.value = value.substring(0, 4) + '-' + value.substring(4);
                } else {
                    this.value = value;
                }
            });
        }

        // Formatação do telefone
        const telefoneInput = document.getElementById('telefone');
        if (telefoneInput) {
            telefoneInput.addEventListener('input', function(e) {
                let value = this.value.replace(/\D/g, '');
                if (value.length > 9) {
                    value = value.substring(0, 9);
                }
                if (value.length > 6) {
                    this.value = value.substring(0, 3) + ' ' + value.substring(3, 6) + ' ' + value.substring(6);
                } else if (value.length > 3) {
                    this.value = value.substring(0, 3) + ' ' + value.substring(3);
                } else {
                    this.value = value;
                }
            });
        }

        // Limitar NIF e NIF da empresa a 9 dígitos numéricos
        function limitNifInput(input) {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0,9);
            });
        }
        const nifInput = document.getElementById('nif');
        const nifEmpresaInput = document.getElementById('nif_empresa');
        if (nifInput) limitNifInput(nifInput);
        if (nifEmpresaInput) limitNifInput(nifEmpresaInput);

        // Mobile menu toggle
        const menuToggle = document.querySelector('.mobile-menu-toggle');
        const mobileMenu = document.querySelector('.mobile-menu');
        if (menuToggle && mobileMenu) {
            menuToggle.addEventListener('click', function() {
                menuToggle.classList.toggle('active');
                mobileMenu.classList.toggle('active');
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
