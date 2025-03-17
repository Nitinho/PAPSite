<?php
require_once 'config.php';
verificarLogin();

$mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Dados do funcionário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografar senha
    $nif = $_POST['nif'];
    $cargo = $_POST['cargo'];
    
    try {
        // Inserir funcionário (usando a mesma tabela de usuários)
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, nif, nome_da_empresa, nif_da_empresa) VALUES (?, ?, ?, ?, 'Funcionário', ?)");
        $stmt->bind_param("sssss", $nome, $email, $senha, $nif, $cargo);
        $stmt->execute();
        
        $mensagem = '<div class="alert alert-success">Funcionário adicionado com sucesso!</div>';
    } catch (Exception $e) {
        $mensagem = '<div class="alert alert-danger">Erro ao adicionar funcionário: ' . $e->getMessage() . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Funcionários - Painel Administrativo</title>
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
                <a href="funcionarios.php" class="active"><i class="fas fa-users mr-2"></i> Adicionar Funcionários</a>
                <a href="registrar.php"><i class="fas fa-user-plus mr-2"></i> Registrar Pessoas</a>
                <a href="produtosg.php"><i class="fas fa-box mr-2"></i> Gerenciar Produtos</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt mr-2"></i> Sair</a>
            </div>
            
            <!-- Conteúdo principal -->
            <div class="col-md-10 content">
                <h2 class="mb-4">Adicionar Funcionários</h2>
                
                <?php echo $mensagem; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h4>Novo Funcionário</h4>
                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                    <div class="form-group">
                                        <label for="nome">Nome Completo</label>
                                        <input type="text" class="form-control" id="nome" name="nome" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="senha">Senha</label>
                                        <input type="password" class="form-control" id="senha" name="senha" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="nif">NIF</label>
                                        <input type="text" class="form-control" id="nif" name="nif" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="cargo">Cargo</label>
                                        <select class="form-control" id="cargo" name="cargo" required>
                                            <option value="">Selecione um cargo</option>
                                            <option value="Vendedor">Vendedor</option>
                                            <option value="Gerente">Gerente</option>
                                            <option value="Atendente">Atendente</option>
                                            <option value="Estoquista">Estoquista</option>
                                            <option value="Administrador">Administrador</option>
                                        </select>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Adicionar Funcionário</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h4>Funcionários Cadastrados</h4>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Email</th>
                                            <th>Cargo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT nome, email, nif_da_empresa as cargo FROM usuarios WHERE nome_da_empresa = 'Funcionário' ORDER BY nome";
                                        $result = $conn->query($sql);
                                        
                                        if ($result->num_rows > 0) {
                                            while($row = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . $row["nome"] . "</td>";
                                                echo "<td>" . $row["email"] . "</td>";
                                                echo "<td>" . $row["cargo"] . "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='3' class='text-center'>Nenhum funcionário cadastrado</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body
