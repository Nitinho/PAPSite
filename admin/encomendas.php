<?php
// Iniciar a sessão administrativa
session_name('admin_session');
session_start();

// Incluir arquivo de configuração
require_once 'config.php';

// Verificar login administrativo
verificarLoginAdmin();

// Processar atualização de status
if(isset($_POST['update_status']) && isset($_POST['compra_id']) && isset($_POST['status'])) {
    $compra_id = $_POST['compra_id'];
    $status = $_POST['status'];
    
    $update_query = "UPDATE compras SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $status, $compra_id);
    
    if($stmt->execute()) {
        $mensagem = '<div class="alert alert-success">Status da encomenda atualizado com sucesso!</div>';
    } else {
        $mensagem = '<div class="alert alert-danger">Erro ao atualizar status: ' . $conn->error . '</div>';
    }
}

// Verificar se é para exibir detalhes de uma compra específica
$mostrar_detalhes = false;
if(isset($_GET['compra_id'])) {
    $compra_id = $_GET['compra_id'];
    $mostrar_detalhes = true;
    
    // Buscar detalhes da compra
    $query_compra = "SELECT c.*, u.nome as nome_cliente, u.email, u.telefone 
                    FROM compras c 
                    JOIN usuarios u ON c.usuario_id = u.id 
                    WHERE c.id = ?";
    $stmt = $conn->prepare($query_compra);
    $stmt->bind_param("i", $compra_id);
    $stmt->execute();
    $result_compra = $stmt->get_result();
    $compra = $result_compra->fetch_assoc();
    
    // Buscar itens da compra
    $query_itens = "SELECT i.*, p.nome as nome_produto, p.imagem 
                   FROM itens_compra i 
                   JOIN produtos p ON i.produto_id = p.id 
                   WHERE i.compra_id = ?";
    $stmt = $conn->prepare($query_itens);
    $stmt->bind_param("i", $compra_id);
    $stmt->execute();
    $result_itens = $stmt->get_result();
    $itens = $result_itens->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encomendas - Painel Administrativo</title>
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
        .product-img {
            max-width: 80px;
            max-height: 80px;
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
                <a href="encomendas.php" class="active"><i class="fas fa-shopping-cart mr-2"></i> Encomendas</a>
                <a href="registrar.php"><i class="fas fa-user-plus mr-2"></i> Registrar Pessoas</a>
                <a href="produtosg.php"><i class="fas fa-box mr-2"></i> Gerenciar Produtos</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt mr-2"></i> Sair</a>
            </div>
            
            <!-- Conteúdo principal -->
            <div class="col-md-10 content">
                <?php if(isset($mensagem)) echo $mensagem; ?>
                
                <?php if($mostrar_detalhes): ?>
                    <!-- Detalhes da Encomenda -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Detalhes da Encomenda #<?php echo $compra['id']; ?></h2>
                        <a href="encomendas.php" class="btn btn-primary"><i class="fas fa-arrow-left mr-2"></i> Voltar</a>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Informações da Encomenda</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th>ID da Encomenda:</th>
                                            <td><?php echo $compra['id']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Data da Compra:</th>
                                            <td><?php echo date('d/m/Y H:i', strtotime($compra['data_compra'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Valor Total:</th>
                                            <td>€<?php echo number_format($compra['valor_compra'], 2, ',', '.'); ?></td>
                                        </tr>
                                        <tr>
                                        </tr>
                                        <tr>
                                            <th>Status Atual:</th>
                                            <td>
                                                <?php 
                                                $badge_class = '';
                                                switch($compra['status']) {
                                                    case 'pendente':
                                                        $badge_class = 'badge-warning';
                                                        break;
                                                    case 'enviado':
                                                        $badge_class = 'badge-primary';
                                                        break;
                                                    case 'recebido':
                                                        $badge_class = 'badge-success';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($compra['status']); ?></span>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <form method="post" action="encomendas.php?compra_id=<?php echo $compra['id']; ?>">
                                        <input type="hidden" name="compra_id" value="<?php echo $compra['id']; ?>">
                                        <div class="form-group">
                                            <label for="status"><strong>Atualizar Status:</strong></label>
                                            <select name="status" id="status" class="form-control">
                                                <option value="pendente" <?php if($compra['status'] == 'pendente') echo 'selected'; ?>>Pendente</option>
                                                <option value="enviado" <?php if($compra['status'] == 'enviado') echo 'selected'; ?>>Enviado</option>
                                                <option value="recebido" <?php if($compra['status'] == 'recebido') echo 'selected'; ?>>Recebido</option>
                                            </select>
                                        </div>
                                        <button type="submit" name="update_status" class="btn btn-success">Atualizar Status</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">Informações do Cliente</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th>Nome:</th>
                                            <td><?php echo $compra['nome_cliente']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td><?php echo $compra['email']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Telefone:</th>
                                            <td><?php echo $compra['telefone']; ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Itens da Encomenda</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Imagem</th>
                                        <th>Preço Unitário</th>
                                        <th>Quantidade</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($itens as $item): ?>
                                    <tr>
                                        <td><?php echo $item['nome_produto']; ?></td>
                                        <td>
                                            <?php if($item['imagem']): ?>
                                                <img src="<?php echo $item['imagem']; ?>" alt="<?php echo $item['nome_produto']; ?>" class="product-img">
                                            <?php else: ?>
                                                <span class="text-muted">Sem imagem</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>€<?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                                        <td><?php echo $item['quantidade']; ?></td>
                                        <td>€<?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-right">Total:</th>
                                        <th>€<?php echo number_format($compra['valor_compra'], 2, ',', '.'); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                
                <?php else: ?>
                    <!-- Lista de Encomendas -->
                    <h2 class="mb-4">Gerenciar Encomendas</h2>
                    
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Encomendas</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Data</th>
                                        <th>Valor</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Buscar todas as compras com informações do cliente
                                    $query = "SELECT c.*, u.nome as nome_cliente 
                                             FROM compras c 
                                             JOIN usuarios u ON c.usuario_id = u.id 
                                             ORDER BY c.data_compra DESC";
                                    $result = $conn->query($query);
                                    
                                    if($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            // Definir classe do badge de acordo com o status
                                            $badge_class = '';
                                            switch($row['status']) {
                                                case 'pendente':
                                                    $badge_class = 'badge-warning';
                                                    break;
                                                case 'enviado':
                                                    $badge_class = 'badge-primary';
                                                    break;
                                                case 'recebido':
                                                    $badge_class = 'badge-success';
                                                    break;
                                            }
                                            ?>
                                            <tr>
                                                <td><?php echo $row['id']; ?></td>
                                                <td><?php echo $row['nome_cliente']; ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($row['data_compra'])); ?></td>
                                                <td>€<?php echo number_format($row['valor_compra'], 2, ',', '.'); ?></td>
                                                <td><span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                                <td>
                                                    <a href="encomendas.php?compra_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> Ver Detalhes
                                                    </a>
                                                    <div class="dropdown d-inline-block">
                                                        <button class="btn btn-sm btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fas fa-edit"></i> Status
                                                        </button>
                                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                            <form method="post" action="encomendas.php">
                                                                <input type="hidden" name="compra_id" value="<?php echo $row['id']; ?>">
                                                                <input type="hidden" name="status" value="pendente">
                                                                <button type="submit" name="update_status" class="dropdown-item">Pendente</button>
                                                            </form>
                                                            <form method="post" action="encomendas.php">
                                                                <input type="hidden" name="compra_id" value="<?php echo $row['id']; ?>">
                                                                <input type="hidden" name="status" value="enviado">
                                                                <button type="submit" name="update_status" class="dropdown-item">Enviado</button>
                                                            </form>
                                                            <form method="post" action="encomendas.php">
                                                                <input type="hidden" name="compra_id" value="<?php echo $row['id']; ?>">
                                                                <input type="hidden" name="status" value="recebido">
                                                                <button type="submit" name="update_status" class="dropdown-item">Recebido</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="6" class="text-center">Nenhuma encomenda encontrada</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
