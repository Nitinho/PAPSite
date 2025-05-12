<?php
session_start();
include('config.php');  // A conexão com o banco de dados

$errors = [];

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
    $termos = isset($_POST['termos']);

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
    } elseif (!preg_match('/^\d{9}$/', $nif)) {
        $errors['nif'] = "O NIF deve ter exatamente 9 dígitos";
    }

    if (!empty($nif_empresa) && !preg_match('/^\d{9}$/', $nif_empresa)) {
        $errors['nif_empresa'] = "O NIF da empresa deve ter exatamente 9 dígitos";
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

    if (!$termos) {
        $errors['termos'] = "Você deve aceitar os termos e condições";
    }

    // Se não houver erros, prosseguir com o registro
    if (empty($errors)) {
        $conn = getDBConnection();

        try {
            // Verificar se o email já existe
            $stmt = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                $errors['email'] = "Este email já está em uso";
            } else {
                // Iniciar transação
                $conn->beginTransaction();

                // Hash da senha
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

                // Inserir usuário
                $sql = "INSERT INTO usuarios (nome, email, senha, nif, nome_da_empresa, nif_da_empresa, telefone)
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

                // Obter o ID do usuário recém-inserido
                $usuario_id = $conn->lastInsertId();

                // Inserir endereço
                $sql = "INSERT INTO enderecos (usuario_id, rua, numero, cidade, codigo_postal) 
                        VALUES (:usuario_id, :rua, :numero, :cidade, :codigo_postal)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':usuario_id', $usuario_id);
                $stmt->bindParam(':rua', $rua);
                $stmt->bindParam(':numero', $numero);
                $stmt->bindParam(':cidade', $cidade);
                $stmt->bindParam(':codigo_postal', $codigo_postal);
                $stmt->execute();

                // Confirmar transação
                $conn->commit();

                // Redirecionar para a página de login com mensagem de sucesso
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
        /* ... (mantém os estilos do seu arquivo original) ... */
    </style>
</head>
<body>
    <!-- ... (mantém o header e navegação) ... -->

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
                        
                        <div class="terms-checkbox <?php echo hasError('termos'); ?>">
                            <input type="checkbox" id="termos" name="termos" <?php echo isset($_POST['termos']) ? 'checked' : ''; ?>>
                            <label for="termos">
                                Li e concordo com os <a href="#" target="_blank">Termos de Uso</a> e <a href="#" target="_blank">Política de Privacidade</a>
                            </label>
                            <?php if (isset($errors['termos'])): ?>
                                <span class="error-feedback"><?php echo $errors['termos']; ?></span>
                            <?php endif; ?>
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

    <!-- ... (footer permanece igual) ... -->

    <script>
        // Toggle password visibility
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

        // Verificador de força da senha
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

        // Limitar NIF e NIF da empresa a 9 dígitos
        function limitarNifInput(input) {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 9);
            });
        }

        const nifInput = document.getElementById('nif');
        const nifEmpresaInput = document.getElementById('nif_empresa');
        if (nifInput) limitarNifInput(nifInput);
        if (nifEmpresaInput) limitarNifInput(nifEmpresaInput);

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
