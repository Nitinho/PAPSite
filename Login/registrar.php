<?php
session_start();
include('config.php');  // A conexão com o banco de dados

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Receber dados do formulário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografar a senha
    $nif = $_POST['nif'];
    $nome_empresa = $_POST['nome_empresa'] ?? null;
    $nif_empresa = $_POST['nif_empresa'] ?? null;
    $telefone = $_POST['telefone'] ?? null;
    
    // Dados de endereço
    $rua = $_POST['rua'];
    $numero = $_POST['numero'];
    $cidade = $_POST['cidade'];
    $codigo_postal = $_POST['codigo_postal'];
    
    // Conectando ao banco de dados
    $conn = getDBConnection();
    
    try {
        // Iniciar transação
        $conn->beginTransaction();
        
        // Inserir usuário
        $sql = "INSERT INTO usuarios (nome, email, senha, nif, nome_da_empresa, nif_da_empresa, telefone) 
                VALUES (:nome, :email, :senha, :nif, :nome_empresa, :nif_empresa, :telefone)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senha);
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
    } catch (PDOException $e) {
        // Reverter transação em caso de erro
        $conn->rollBack();
        $error_message = "Erro ao registrar: " . $e->getMessage();
    }
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
                    
                    <?php if (isset($error_message)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="registrar.php" class="register-form">
                        <h3>Dados Pessoais</h3>
                        <div class="form-group">
                            <label for="nome"><i class="fas fa-user"></i> Nome Completo</label>
                            <input type="text" id="nome" name="nome" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="senha"><i class="fas fa-lock"></i> Senha</label>
                            <div class="password-input-container">
                                <input type="password" id="senha" name="senha" required>
                                <button type="button" id="toggle-password" class="toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="nif"><i class="fas fa-id-card"></i> NIF</label>
                            <input type="text" id="nif" name="nif" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="telefone"><i class="fas fa-phone"></i> Telefone</label>
                            <input type="text" id="telefone" name="telefone">
                        </div>
                        
                        <h3>Dados da Empresa (Opcional)</h3>
                        <div class="form-group">
                            <label for="nome_empresa"><i class="fas fa-building"></i> Nome da Empresa</label>
                            <input type="text" id="nome_empresa" name="nome_empresa">
                        </div>
                        
                        <div class="form-group">
                            <label for="nif_empresa"><i class="fas fa-file-invoice"></i> NIF da Empresa</label>
                            <input type="text" id="nif_empresa" name="nif_empresa">
                        </div>
                        
                        <h3>Endereço</h3>
                        <div class="form-group">
                            <label for="rua"><i class="fas fa-road"></i> Rua</label>
                            <input type="text" id="rua" name="rua" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="numero"><i class="fas fa-home"></i> Número</label>
                            <input type="text" id="numero" name="numero" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="cidade"><i class="fas fa-city"></i> Cidade</label>
                            <input type="text" id="cidade" name="cidade" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="codigo_postal"><i class="fas fa-mail-bulk"></i> Código Postal</label>
                            <input type="text" id="codigo_postal" name="codigo_postal" required>
                        </div>
                        
                        <button type="submit" class="register-button">
                            <span>Registrar</span>
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
        <!-- Conteúdo do footer igual ao da página de login -->
    </footer>

    <script>
        // Toggle password visibility
        document.getElementById('toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('senha');
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
        
        // Mobile menu toggle (mesmo código da página de login)
    </script>
</body>
</html>
