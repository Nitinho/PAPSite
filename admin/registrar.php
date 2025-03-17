<?php
require_once 'config.php';
verificarLogin();

$mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Dados do usuário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografar senha
    $nif = $_POST['nif'];
    $nome_empresa = $_POST['nome_empresa'] ?? null;
    $nif_empresa = $_POST['nif_empresa'] ?? null;
    $telefone = $_POST['telefone'] ?? null;
    
    // Dados do endereço
    $rua = $_POST['rua'];
    $numero = $_POST['numero'];
    $cidade = $_POST['cidade'];
    $codigo_postal = $_POST['codigo_postal'];
    
    // Iniciar transação
    $conn->begin_transaction();
    
    try {
        // Inserir usuário
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, nif, nome_da_empresa, nif_da_empresa, telefone) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $nome, $email, $senha, $nif, $nome_empresa, $nif_empresa, $telefone);
        $stmt->execute();
        
        // Obter ID do usuário inserido
        $usuario_id = $conn->insert_id;
        
        // Inserir endereço
        $stmt = $conn->prepare("INSERT INTO enderecos (usuario_id, rua, numero, cidade, codigo_postal) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $usuario_id, $rua, $numero, $cidade, $codigo_postal);
        $stmt->execute();
        
        // Confirmar transação
        $conn->commit();
        
        $mensagem = '<div class="alert alert-success">Usuário registrado com sucesso!</div>';
    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $conn->rollback();
        $mensagem = '<div class="alert alert-danger">Erro ao registrar usuário: ' . $e->getMessage() . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Pessoas - Painel Administrativo</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
        }
        .sidebar a {
            color: #adb5bd;
            padding: 10px 15px;
            display: block;
            text-decoration: none;
        }
        .sidebar a:hover {
            color: white;
            background-color: #495057;
        }
        .sidebar a.active {
            color: white;
            background-color: #007bff;
        }
        .content {
            padding: 20px;
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
                <a href="funcionarios.php"><i class="fas fa-users mr-2"></i> Adicionar Funcionários</a>
                <a href="registrar.php" class="active"><i class="fas fa-user-plus mr-2"></i> Registrar Pessoas</a>
                <a href="produtosg.php"><i class="fas fa-box mr-2"></i> Gerenciar Produtos</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt mr-2"></i> Sair</a>
            </div>
            
            <!-- Conteúdo principal -->
            <div class="col-md-10 content">
                <h2 class="mb-4">Registrar Pessoas</h2>
                
                <?php echo $mensagem; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <h4>Dados Pessoais</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nome">Nome Completo</label>
                                        <input type="text" class="form-control" id="nome" name="nome" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="senha">Senha</label>
                                        <input type="password" class="form-control" id="senha" name="senha" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="nif">NIF</label>
                                        <input type="text" class="form-control" id="nif" name="nif" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="telefone">Telefone</label>
                                        <input type="text" class="form-control" id="telefone" name="telefone">
                                    </div>
                                </div>
                            </div>
                            
                            <h4 class="mt-4">Dados da Empresa</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nome_empresa">Nome da Empresa</label>
                                        <input type="text" class="form-control" id="nome_empresa" name="nome_empresa">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nif_empresa">NIF da Empresa</label>
                                        <input type="text" class="form-control" id="nif_empresa" name="nif_empresa">
                                    </div>
                                </div>
                            </div>
                            
                            <h4 class="mt-4">Endereço</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="rua">Rua</label>
                                        <input type="text" class="form-control" id="rua" name="rua" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                    <label for="numero">Número</label>
                                        <input type="text" class="form-control" id="numero" name="numero" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cidade">Cidade</label>
                                        <input type="text" class="form-control" id="cidade" name="cidade" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="codigo_postal">Código Postal</label>
                                        <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" required>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary mt-3">Registrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
