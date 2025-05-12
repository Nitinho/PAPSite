<?php
// Iniciar a sessão administrativa
session_name('admin_session');
session_start();

// Incluir arquivo de configuração
require_once 'config.php';

// Verificar login administrativo
verificarLoginAdmin();

$mensagem = '';
$erros = [];

// Função para validar e sanitizar dados
function validarDados($dados) {
    $erros = [];
    
    // Validar nome (apenas letras e espaços)
    if (empty($dados['nome'])) {
        $erros['nome'] = "Nome é obrigatório";
    } elseif (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/u", $dados['nome'])) {
        $erros['nome'] = "Nome deve conter apenas letras e espaços";
    }
    
    // Validar email
    if (empty($dados['email'])) {
        $erros['email'] = "Email é obrigatório";
    } elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        $erros['email'] = "Formato de email inválido";
    }
    
    // Validar senha (mínimo 8 caracteres)
    if (empty($dados['senha'])) {
        $erros['senha'] = "Senha é obrigatória";
    } elseif (strlen($dados['senha']) < 8) {
        $erros['senha'] = "A senha deve ter pelo menos 8 caracteres";
    }
    
    // Validar NIF (exatamente 9 dígitos)
    if (empty($dados['nif'])) {
        $erros['nif'] = "NIF é obrigatório";
    } elseif (!preg_match("/^[0-9]{9}$/", $dados['nif'])) {
        $erros['nif'] = "NIF deve conter exatamente 9 dígitos";
    }
    
    // Validar telefone (exatamente 9 dígitos)
    if (!empty($dados['telefone']) && !preg_match("/^[0-9]{9}$/", $dados['telefone'])) {
        $erros['telefone'] = "Telefone deve conter exatamente 9 dígitos";
    }
    
    // Validar NIF da empresa (se fornecido, deve ter 9 dígitos)
    if (!empty($dados['nif_empresa']) && !preg_match("/^[0-9]{9}$/", $dados['nif_empresa'])) {
        $erros['nif_empresa'] = "NIF da empresa deve conter exatamente 9 dígitos";
    }
    
    // Validar código postal (formato português: 4 dígitos - 3 dígitos)
    if (empty($dados['codigo_postal'])) {
        $erros['codigo_postal'] = "Código postal é obrigatório";
    } elseif (!preg_match("/^\d{4}-\d{3}$/", $dados['codigo_postal'])) {
        $erros['codigo_postal'] = "Código postal deve estar no formato XXXX-XXX";
    }
    
    return $erros;
}

// Processar o registro de novo usuário
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'registrar') {
    // Dados do usuário
    $dados = [
        'nome' => trim($_POST['nome']),
        'email' => trim($_POST['email']),
        'senha' => $_POST['senha'],
        'nif' => trim($_POST['nif']),
        'nome_empresa' => !empty($_POST['nome_empresa']) ? trim($_POST['nome_empresa']) : null,
        'nif_empresa' => !empty($_POST['nif_empresa']) ? trim($_POST['nif_empresa']) : null,
        'telefone' => !empty($_POST['telefone']) ? trim($_POST['telefone']) : null,
        'rua' => trim($_POST['rua']),
        'numero' => trim($_POST['numero']),
        'cidade' => trim($_POST['cidade']),
        'codigo_postal' => trim($_POST['codigo_postal'])
    ];
    
    // Validar dados
    $erros = validarDados($dados);
    
    // Se não houver erros, prosseguir com o registro
    if (empty($erros)) {
        // Verificar se o email já existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $dados['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $erros['email'] = "Este email já está registrado";
        } else {
            // Criptografar senha
            $senha_hash = password_hash($dados['senha'], PASSWORD_DEFAULT);
            
            // Iniciar transação
            $conn->begin_transaction();
            
            try {
                // Inserir usuário
                $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, nif, nome_da_empresa, nif_da_empresa, telefone, data_registro) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("sssssss", 
                    $dados['nome'], 
                    $dados['email'], 
                    $senha_hash, 
                    $dados['nif'], 
                    $dados['nome_empresa'], 
                    $dados['nif_empresa'], 
                    $dados['telefone']
                );
                $stmt->execute();
                
                // Obter ID do usuário inserido
                $usuario_id = $conn->insert_id;
                
                // Inserir endereço
                $stmt = $conn->prepare("INSERT INTO enderecos (usuario_id, rua, numero, cidade, codigo_postal) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", 
                    $usuario_id, 
                    $dados['rua'], 
                    $dados['numero'], 
                    $dados['cidade'], 
                    $dados['codigo_postal']
                );
                $stmt->execute();
                
                // Confirmar transação
                $conn->commit();
                
                $mensagem = '<div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Cliente registrado com sucesso!
                </div>';
                
                // Limpar o formulário após o registro bem-sucedido
                $_POST = [];
            } catch (Exception $e) {
                // Reverter transação em caso de erro
                $conn->rollback();
                $mensagem = '<div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Erro ao registrar cliente: ' . $e->getMessage() . '
                </div>';
            }
        }
    } else {
        $mensagem = '<div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> Por favor, corrija os erros no formulário.
        </div>';
    }
}

// Processar exclusão de usuário
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'apagar') {
    $usuario_id = $_POST['usuario_id'];
    $senha_admin = $_POST['senha_admin'];
    
    // Verificar senha admin (em produção, usar password_verify com hash armazenado)
    if ($senha_admin == "admingeral@admingeral") {
        try {
            // Iniciar transação
            $conn->begin_transaction();
            
            // Excluir endereços do usuário
            $stmt = $conn->prepare("DELETE FROM enderecos WHERE usuario_id = ?");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            
            // Excluir usuário
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            
            // Confirmar transação
            $conn->commit();
            
            $mensagem = '<div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Cliente excluído com sucesso!
            </div>';
        } catch (Exception $e) {
            // Reverter transação em caso de erro
            $conn->rollback();
            $mensagem = '<div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> Erro ao excluir cliente: ' . $e->getMessage() . '
            </div>';
        }
    } else {
        $mensagem = '<div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> Senha de administrador incorreta!
        </div>';
    }
}

// Buscar todos os usuários
$usuarios = [];
$query = "SELECT u.*, e.rua, e.numero, e.cidade, e.codigo_postal 
          FROM usuarios u 
          LEFT JOIN enderecos e ON u.id = e.usuario_id
          ORDER BY u.data_registro DESC";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Clientes - Painel Administrativo</title>
    <link rel="shortcut icon" type="image/x-icon" href="../img/logolopes.ico">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --accent-color: #f6c23e;
            --danger-color: #e74a3b;
            --dark-color: #5a5c69;
            --light-color: #f8f9fc;
            --border-radius: 0.35rem;
            --box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
            color: white;
            padding-top: 20px;
            box-shadow: var(--box-shadow);
        }
        
        .sidebar a {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            display: block;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
        }
        
        .sidebar a:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 3px solid var(--secondary-color);
        }
        
        .sidebar a.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 3px solid var(--secondary-color);
        }
        
        .content {
            padding: 30px;
        }
        
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #e3e6f0;
            padding: 1.25rem;
        }
        
        .form-control {
            padding: 0.8rem 1rem;
            border-radius: var(--border-radius);
            border: 1px solid #d1d3e2;
            font-size: 0.9rem;
            height: auto;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .btn-primary:hover {
            background-color: #4262c5;
            border-color: #4262c5;
        }
        
        .section-title {
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: var(--dark-color);
            font-weight: 700;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            height: 3px;
            width: 50px;
            background-color: var(--secondary-color);
        }
        
        .form-group label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: 15px;
            color: #aab0bc;
        }
        
        .input-with-icon input {
            padding-left: 40px;
        }
        
        .invalid-feedback {
            display: block;
            color: var(--danger-color);
            font-size: 0.8rem;
        }
        
        .table {
            border-radius: var(--border-radius);
            overflow: hidden;
        }
        
        .table thead th {
            background-color: var(--light-color);
            border-top: none;
            border-bottom: 1px solid #e3e6f0;
            font-weight: 700;
            color: var(--dark-color);
        }
        
        .btn-action {
            padding: 0.4rem 0.75rem;
            border-radius: var(--border-radius);
        }
        
        .is-invalid {
            border-color: var(--danger-color);
        }
        
        .alert {
            border-radius: var(--border-radius);
            border-left: 4px solid;
        }
        
        .alert-success {
            border-left-color: var(--secondary-color);
        }
        
        .alert-danger {
            border-left-color: var(--danger-color);
        }
        
        .password-toggle {
            cursor: pointer;
        }
        
        .user-card {
            transition: all 0.3s ease;
        }
        
        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <h4 class="text-center mb-4">Admin Panel</h4>
                <a href="dashboard.php"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a>
                <a href="encomendas.php"><i class="fas fa-shopping-cart mr-2"></i> Encomendas</a>
                <a href="registrar.php" class="active"><i class="fas fa-user-plus mr-2"></i> Registrar Clientes</a>
                <a href="produtosg.php"><i class="fas fa-box mr-2"></i> Gerenciar Produtos</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt mr-2"></i> Sair</a>
            </div>
            
            <!-- Conteúdo principal -->
            <div class="col-md-10 content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-user-plus mr-2"></i> Registrar Clientes</h2>
                    <span class="text-muted">Data atual: <?php echo date('d/m/Y H:i'); ?></span>
                </div>
                
                <?php echo $mensagem; ?>
                
                <div class="card">
                    <div class="card-header bg-white">
                        <h4 class="section-title mb-0">Formulário de Registro de Cliente</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="formRegistro" novalidate>
                            <input type="hidden" name="action" value="registrar">
                            
                            <h5 class="section-title">Dados Pessoais</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nome"><i class="fas fa-user mr-1"></i> Nome Completo</label>
                                        <div class="input-with-icon">
                                            <input type="text" class="form-control <?php echo isset($erros['nome']) ? 'is-invalid' : ''; ?>" 
                                                id="nome" name="nome" required 
                                                placeholder="Digite o nome completo"
                                                value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>">
                                        </div>
                                        <?php if (isset($erros['nome'])): ?>
                                            <div class="invalid-feedback"><?php echo $erros['nome']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email"><i class="fas fa-envelope mr-1"></i> Email</label>
                                        <div class="input-with-icon">
                                            <input type="email" class="form-control <?php echo isset($erros['email']) ? 'is-invalid' : ''; ?>" 
                                                id="email" name="email" required
                                                placeholder="exemplo@email.com"
                                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                        </div>
                                        <?php if (isset($erros['email'])): ?>
                                            <div class="invalid-feedback"><?php echo $erros['email']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="senha"><i class="fas fa-lock mr-1"></i> Senha</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control <?php echo isset($erros['senha']) ? 'is-invalid' : ''; ?>" 
                                                id="senha" name="senha" required
                                                placeholder="Crie uma senha segura" minlength="8">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <small class="text-muted">Mínimo de 8 caracteres</small>
                                        <?php if (isset($erros['senha'])): ?>
                                            <div class="invalid-feedback"><?php echo $erros['senha']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="nif"><i class="fas fa-id-card mr-1"></i> NIF</label>
                                        <div class="input-with-icon">
                                            <input type="text" class="form-control <?php echo isset($erros['nif']) ? 'is-invalid' : ''; ?>" 
                                                id="nif" name="nif" required
                                                placeholder="123456789" maxlength="9" pattern="[0-9]{9}"
                                                value="<?php echo isset($_POST['nif']) ? htmlspecialchars($_POST['nif']) : ''; ?>">
                                        </div>
                                        <small class="text-muted">Exatamente 9 dígitos</small>
                                        <?php if (isset($erros['nif'])): ?>
                                            <div class="invalid-feedback"><?php echo $erros['nif']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="telefone"><i class="fas fa-phone mr-1"></i> Telefone</label>
                                        <div class="input-with-icon">
                                            <input type="text" class="form-control <?php echo isset($erros['telefone']) ? 'is-invalid' : ''; ?>" 
                                                id="telefone" name="telefone"
                                                placeholder="912345678" maxlength="9" pattern="[0-9]{9}"
                                                value="<?php echo isset($_POST['telefone']) ? htmlspecialchars($_POST['telefone']) : ''; ?>">
                                        </div>
                                        <small class="text-muted">Exatamente 9 dígitos</small>
                                        <?php if (isset($erros['telefone'])): ?>
                                            <div class="invalid-feedback"><?php echo $erros['telefone']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <h5 class="section-title mt-4">Dados da Empresa <small class="text-muted"></small></h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nome_empresa"><i class="fas fa-building mr-1"></i> Nome da Empresa</label>
                                        <div class="input-with-icon">
                                            <input type="text" class="form-control" 
                                                id="nome_empresa" name="nome_empresa"
                                                placeholder="Nome da empresa "
                                                value="<?php echo isset($_POST['nome_empresa']) ? htmlspecialchars($_POST['nome_empresa']) : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nif_empresa"><i class="fas fa-briefcase mr-1"></i> NIF da Empresa</label>
                                        <div class="input-with-icon">
                                            <input type="text" class="form-control <?php echo isset($erros['nif_empresa']) ? 'is-invalid' : ''; ?>" 
                                                id="nif_empresa" name="nif_empresa"
                                                placeholder="NIF da empresa " maxlength="9" pattern="[0-9]{9}"
                                                value="<?php echo isset($_POST['nif_empresa']) ? htmlspecialchars($_POST['nif_empresa']) : ''; ?>">
                                        </div>
                                        <small class="text-muted">Se preenchido, deve ter 9 dígitos</small>
                                        <?php if (isset($erros['nif_empresa'])): ?>
                                            <div class="invalid-feedback"><?php echo $erros['nif_empresa']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <h5 class="section-title mt-4">Endereço</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="rua"><i class="fas fa-road mr-1"></i> Rua</label>
                                        <div class="input-with-icon">
                                            <input type="text" class="form-control <?php echo isset($erros['rua']) ? 'is-invalid' : ''; ?>" 
                                                id="rua" name="rua" required
                                                placeholder="Nome da rua"
                                                value="<?php echo isset($_POST['rua']) ? htmlspecialchars($_POST['rua']) : ''; ?>">
                                        </div>
                                        <?php if (isset($erros['rua'])): ?>
                                            <div class="invalid-feedback"><?php echo $erros['rua']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="numero"><i class="fas fa-home mr-1"></i> Número</label>
                                        <div class="input-with-icon">
                                            <input type="text" class="form-control <?php echo isset($erros['numero']) ? 'is-invalid' : ''; ?>" 
                                                id="numero" name="numero" required
                                                placeholder="Nº"
                                                value="<?php echo isset($_POST['numero']) ? htmlspecialchars($_POST['numero']) : ''; ?>">
                                        </div>
                                        <?php if (isset($erros['numero'])): ?>
                                            <div class="invalid-feedback"><?php echo $erros['numero']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="codigo_postal"><i class="fas fa-mail-bulk mr-1"></i> Código Postal</label>
                                        <div class="input-with-icon">
                                            <input type="text" class="form-control <?php echo isset($erros['codigo_postal']) ? 'is-invalid' : ''; ?>" 
                                                id="codigo_postal" name="codigo_postal" required
                                                placeholder="1234-567" pattern="\d{4}-\d{3}"
                                                value="<?php echo isset($_POST['codigo_postal']) ? htmlspecialchars($_POST['codigo_postal']) : ''; ?>">
                                        </div>
                                        <small class="text-muted">Formato: 1234-567</small>
                                        <?php if (isset($erros['codigo_postal'])): ?>
                                            <div class="invalid-feedback"><?php echo $erros['codigo_postal']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cidade"><i class="fas fa-city mr-1"></i> Cidade</label>
                                        <div class="input-with-icon">
                                            <input type="text" class="form-control <?php echo isset($erros['cidade']) ? 'is-invalid' : ''; ?>" 
                                                id="cidade" name="cidade" required
                                                placeholder="Nome da cidade"
                                                value="<?php echo isset($_POST['cidade']) ? htmlspecialchars($_POST['cidade']) : ''; ?>">
                                        </div>
                                        <?php if (isset($erros['cidade'])): ?>
                                            <div class="invalid-feedback"><?php echo $erros['cidade']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            

                            
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus mr-2"></i> Registrar Cliente
                                </button>
                                <button type="reset" class="btn btn-secondary btn-lg ml-2">
                                    <i class="fas fa-undo mr-2"></i> Limpar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Lista de Clientes -->
                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="section-title mb-0">Clientes Registrados</h4>
                            <div class="input-group" style="width: 300px;">
                                <input type="text" class="form-control" id="pesquisaCliente" placeholder="Pesquisar cliente...">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tabelaClientes">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag mr-1"></i> ID</th>
                                        <th><i class="fas fa-user mr-1"></i> Nome</th>
                                        <th><i class="fas fa-envelope mr-1"></i> Email</th>
                                        <th><i class="fas fa-id-card mr-1"></i> NIF</th>
                                        <th><i class="fas fa-phone mr-1"></i> Telefone</th>
                                        <th><i class="fas fa-building mr-1"></i> Empresa</th>
                                        <th><i class="fas fa-map-marker-alt mr-1"></i> Endereço</th>
                                        <th><i class="fas fa-calendar-alt mr-1"></i> Registro</th>
                                        <th><i class="fas fa-cogs mr-1"></i> Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($usuarios) > 0): ?>
                                        <?php foreach ($usuarios as $usuario): ?>
                                        <tr class="user-card">
                                            <td><?php echo $usuario['id']; ?></td>
                                            <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                            <td><?php echo htmlspecialchars($usuario['nif']); ?></td>
                                            <td><?php echo htmlspecialchars($usuario['telefone'] ?? '-'); ?></td>
                                            <td>
                                                <?php 
                                                if (!empty($usuario['nome_da_empresa'])) {
                                                    echo htmlspecialchars($usuario['nome_da_empresa']) . '<br>';
                                                    echo '<small class="text-muted">NIF: ' . htmlspecialchars($usuario['nif_da_empresa']) . '</small>';
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if (!empty($usuario['rua'])) {
                                                    echo htmlspecialchars($usuario['rua']) . ', ' . htmlspecialchars($usuario['numero']) . '<br>';
                                                    echo '<small class="text-muted">' . htmlspecialchars($usuario['cidade']) . ', ' . htmlspecialchars($usuario['codigo_postal']) . '</small>';
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($usuario['data_registro'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-info btn-sm" 
                                                            data-toggle="modal" 
                                                            data-target="#visualizarCliente" 
                                                            data-id="<?php echo $usuario['id']; ?>"
                                                            data-nome="<?php echo htmlspecialchars($usuario['nome']); ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" 
                                                            data-toggle="modal" 
                                                            data-target="#confirmarExclusao" 
                                                            data-id="<?php echo $usuario['id']; ?>"
                                                            data-nome="<?php echo htmlspecialchars($usuario['nome']); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center">Nenhum cliente registrado</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="confirmarExclusao" tabindex="-1" role="dialog" aria-labelledby="confirmarExclusaoLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmarExclusaoLabel"><i class="fas fa-exclamation-triangle mr-2"></i> Confirmar Exclusão</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o cliente <strong id="nomeUsuario"></strong>?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle mr-2"></i> Esta ação não pode ser desfeita! Todos os dados do cliente serão permanentemente removidos.
                    </div>
                    <form id="formExcluir" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <input type="hidden" name="action" value="apagar">
                        <input type="hidden" name="usuario_id" id="usuarioId">
                        <div class="form-group">
                            <label for="senha_admin"><i class="fas fa-key mr-1"></i> Digite a senha de administrador para confirmar:</label>
                            <input type="password" class="form-control" id="senha_admin" name="senha_admin" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('formExcluir').submit();">
                        <i class="fas fa-trash mr-1"></i> Confirmar Exclusão
                    </button>
                </div>
            </div>
        </div>
    </div>
    

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Mostrar/ocultar senha
            $('#togglePassword').click(function() {
                const passwordField = $('#senha');
                const passwordFieldType = passwordField.attr('type');
                
                if (passwordFieldType === 'password') {
                    passwordField.attr('type', 'text');
                    $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
            
            // Máscara para código postal
            $('#codigo_postal').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                if (value.length > 4) {
                    value = value.substring(0, 4) + '-' + value.substring(4, 7);
                }
                $(this).val(value);
            });
            
            // Máscara para NIF e telefone (apenas números)
            $('#nif, #nif_empresa, #telefone').on('input', function() {
                $(this).val($(this).val().replace(/\D/g, ''));
            });
            
            // Pesquisa na tabela de clientes
            $('#pesquisaCliente').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                $('#tabelaClientes tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
            
            // Configuração do modal de exclusão
            $('#confirmarExclusao').on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                const id = button.data('id');
                const nome = button.data('nome');
                
                const modal = $(this);
                modal.find('#nomeUsuario').text(nome);
                modal.find('#usuarioId').val(id);
            });
            
            // Validação do formulário
            $('#formRegistro').on('submit', function(event) {
                let isValid = true;
                
                // Validar NIF
                const nif = $('#nif').val();
                if (nif.length !== 9 || !/^\d{9}$/.test(nif)) {
                    $('#nif').addClass('is-invalid');
                    isValid = false;
                } else {
                    $('#nif').removeClass('is-invalid');
                }
                
                // Validar telefone (se preenchido)
                const telefone = $('#telefone').val();
                if (telefone && (telefone.length !== 9 || !/^\d{9}$/.test(telefone))) {
                    $('#telefone').addClass('is-invalid');
                    isValid = false;
                } else {
                    $('#telefone').removeClass('is-invalid');
                }
                
                // Validar NIF da empresa (se preenchido)
                const nifEmpresa = $('#nif_empresa').val();
                if (nifEmpresa && (nifEmpresa.length !== 9 || !/^\d{9}$/.test(nifEmpresa))) {
                    $('#nif_empresa').addClass('is-invalid');
                    isValid = false;
                } else {
                    $('#nif_empresa').removeClass('is-invalid');
                }
                
                // Validar código postal
                const codigoPostal = $('#codigo_postal').val();
                if (!/^\d{4}-\d{3}$/.test(codigoPostal)) {
                    $('#codigo_postal').addClass('is-invalid');
                    isValid = false;
                } else {
                    $('#codigo_postal').removeClass('is-invalid');
                }
                
                
                if (!isValid) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
