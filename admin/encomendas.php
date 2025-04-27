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
    if ($stmt === false) {
        $mensagem = '<div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i>Erro na preparação da consulta: ' . $conn->error . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
    } else {
        $stmt->bind_param("si", $status, $compra_id);
        
        if($stmt->execute()) {
            $mensagem = '<div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>Status da encomenda atualizado com sucesso!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
        } else {
            $mensagem = '<div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>Erro ao atualizar status: ' . $stmt->error . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
        }
    }
}

// Definir o número de itens por página
$itens_por_pagina = 10;

// Determinar a página atual
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $itens_por_pagina;

// Filtros
$filtro_status = isset($_GET['filtro_status']) ? $_GET['filtro_status'] : '';
$filtro_data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$filtro_data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';
$filtro_cliente = isset($_GET['cliente']) ? $_GET['cliente'] : '';

// Construir a consulta SQL com filtros
$where_clauses = [];
$params = [];
$types = '';

if (!empty($filtro_status)) {
    $where_clauses[] = "c.status = ?";
    $params[] = $filtro_status;
    $types .= "s";
}

if (!empty($filtro_data_inicio)) {
    $where_clauses[] = "c.data_compra >= ?";
    $params[] = $filtro_data_inicio . ' 00:00:00';
    $types .= "s";
}

if (!empty($filtro_data_fim)) {
    $where_clauses[] = "c.data_compra <= ?";
    $params[] = $filtro_data_fim . ' 23:59:59';
    $types .= "s";
}

if (!empty($filtro_cliente)) {
    $where_clauses[] = "u.nome LIKE ?";
    $params[] = "%$filtro_cliente%";
    $types .= "s";
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Verificar se é para exibir detalhes de uma compra específica
$mostrar_detalhes = false;
if(isset($_GET['compra_id'])) {
    $compra_id = $_GET['compra_id'];
    $mostrar_detalhes = true;
    
    // Verificar se o ID é válido
    if (!is_numeric($compra_id)) {
        die("ID de encomenda inválido");
    }
    
    // Buscar detalhes da compra
    $query_compra = "SELECT c.*, u.nome as nome_cliente, u.email, u.telefone, u.nif 
                    FROM compras c 
                    JOIN usuarios u ON c.usuario_id = u.id 
                    WHERE c.id = ?";
    $stmt = $conn->prepare($query_compra);
    if ($stmt === false) {
        die("Erro na preparação da consulta: " . $conn->error . "<br>Query: " . $query_compra);
    }
    
    $stmt->bind_param("i", $compra_id);
    if (!$stmt->execute()) {
        die("Erro na execução da consulta: " . $stmt->error);
    }
    
    $result_compra = $stmt->get_result();
    if ($result_compra->num_rows === 0) {
        die("Encomenda não encontrada");
    }
    
    $compra = $result_compra->fetch_assoc();
    
    // Buscar itens da compra
    $query_itens = "SELECT i.*, p.nome as nome_produto, p.imagem 
                   FROM itens_compra i 
                   JOIN produtos p ON i.produto_id = p.id 
                   WHERE i.compra_id = ?";
    $stmt = $conn->prepare($query_itens);
    if ($stmt === false) {
        die("Erro na preparação da consulta de itens: " . $conn->error);
    }
    
    $stmt->bind_param("i", $compra_id);
    if (!$stmt->execute()) {
        die("Erro na execução da consulta de itens: " . $stmt->error);
    }
    
    $result_itens = $stmt->get_result();
    $itens = $result_itens->fetch_all(MYSQLI_ASSOC);
    
    // Buscar endereço de entrega
    $query_endereco = "SELECT e.* 
                      FROM enderecos e 
                      JOIN compras c ON e.id = c.endereco_id 
                      WHERE c.id = ?";
    $stmt = $conn->prepare($query_endereco);
    if ($stmt === false) {
        // Apenas registrar o erro, não interromper a execução
        error_log("Erro na preparação da consulta de endereço: " . $conn->error);
        $endereco = null;
    } else {
        $stmt->bind_param("i", $compra_id);
        if (!$stmt->execute()) {
            error_log("Erro na execução da consulta de endereço: " . $stmt->error);
            $endereco = null;
        } else {
            $result_endereco = $stmt->get_result();
            $endereco = $result_endereco->fetch_assoc();
        }
    }
} else {
    // Contar o total de registros para paginação
    $count_query = "SELECT COUNT(*) as total FROM compras c JOIN usuarios u ON c.usuario_id = u.id $where_sql";
    
    if (!empty($params)) {
        $stmt = $conn->prepare($count_query);
        if ($stmt === false) {
            die("Erro na preparação da consulta de contagem: " . $conn->error);
        }
        
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
            die("Erro na execução da consulta de contagem: " . $stmt->error);
        }
        
        $result_count = $stmt->get_result();
        $total_registros = $result_count->fetch_assoc()['total'];
    } else {
        $result_count = $conn->query($count_query);
        if ($result_count === false) {
            die("Erro na consulta de contagem: " . $conn->error);
        }
        
        $total_registros = $result_count->fetch_assoc()['total'];
    }
    
    $total_paginas = ceil($total_registros / $itens_por_pagina);
    
    // Buscar todas as compras com informações do cliente
    $query = "SELECT c.*, u.nome as nome_cliente 
             FROM compras c 
             JOIN usuarios u ON c.usuario_id = u.id 
             $where_sql
             ORDER BY c.data_compra DESC 
             LIMIT ?, ?";
    
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Erro na preparação da consulta principal: " . $conn->error);
    }
    
    if (!empty($params)) {
        $params[] = $offset;
        $params[] = $itens_por_pagina;
        $types .= "ii";
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param("ii", $offset, $itens_por_pagina);
    }
    
    if (!$stmt->execute()) {
        die("Erro na execução da consulta principal: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    // Buscar estatísticas
    $query_stats = "SELECT 
                    COUNT(*) as total_encomendas,
                    SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pendentes,
                    SUM(CASE WHEN status = 'enviado' THEN 1 ELSE 0 END) as enviados,
                    SUM(CASE WHEN status = 'recebido' THEN 1 ELSE 0 END) as recebidos,
                    SUM(valor_compra) as valor_total
                    FROM compras";
    $result_stats = $conn->query($query_stats);
    if ($result_stats === false) {
        // Se falhar, inicializar com valores padrão
        $stats = [
            'total_encomendas' => 0,
            'pendentes' => 0,
            'enviados' => 0,
            'recebidos' => 0,
            'valor_total' => 0
        ];
    } else {
        $stats = $result_stats->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encomendas - Painel Administrativo</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <link rel="shortcut icon" type="image/x-icon" href="../img/logolopes.ico">

    <style>
        :root {
            --primary: #4e73df;
            --secondary: #858796;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --light: #f8f9fc;
            --dark: #5a5c69;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 
                'Helvetica Neue', Arial, sans-serif;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--primary) 10%, #224abe 100%);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            z-index: 1;
        }
        
        .sidebar a {
            color: rgba(255, 255, 255, 0.8);
            padding: 1rem;
            font-weight: 700;
            font-size: 0.85rem;
            display: block;
            text-decoration: none;
        }
        
        .sidebar a:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar a.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .sidebar a i {
            margin-right: 0.5rem;
            width: 1.25rem;
            text-align: center;
        }
        
        .content {
            padding: 20px;
        }
        
        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            font-weight: 700;
            color: var(--dark);
        }
        
        .stat-card {
            border-left: 0.25rem solid;
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.primary {
            border-left-color: var(--primary);
        }
        
        .stat-card.success {
            border-left-color: var(--success);
        }
        
        .stat-card.info {
            border-left-color: var(--info);
        }
        
        .stat-card.warning {
            border-left-color: var(--warning);
        }
        
        .stat-card.danger {
            border-left-color: var(--danger);
        }
        
        .product-img {
            max-width: 80px;
            max-height: 80px;
            object-fit: contain;
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline:before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e3e6f0;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }
        
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        
        .timeline-dot {
            position: absolute;
            left: -30px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.7rem;
        }
        
        .timeline-content {
            background: white;
            border-radius: 0.35rem;
            padding: 1rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }
        
        .badge-status {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        .pagination {
            margin-bottom: 0;
        }
        
        .filter-card {
            border-radius: 0.35rem;
            margin-bottom: 1.5rem;
        }
        
        .filter-card .card-body {
            padding: 1rem;
        }
        
        .btn-filter {
            border-radius: 0.25rem;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .table th {
            font-weight: 700;
            color: var(--dark);
        }
        
        .status-circle {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .status-pendente {
            background-color: var(--warning);
        }
        
        .status-enviado {
            background-color: var(--primary);
        }
        
        .status-recebido {
            background-color: var(--success);
        }
        
        .status-cancelado {
            background-color: var(--danger);
        }
        
        .dropdown-menu {
            min-width: 8rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <h4 class="text-center text-white mb-4 py-3">Admin Panel</h4>
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="encomendas.php" class="active"><i class="fas fa-shopping-cart"></i> Encomendas</a>
                <a href="registrar.php"><i class="fas fa-user-plus"></i> Registrar Pessoas</a>
                <a href="produtosg.php"><i class="fas fa-box"></i> Gerenciar Produtos</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
            </div>
            
            <!-- Conteúdo principal -->
            <div class="col-md-10 content">
                <?php if(isset($mensagem)) echo $mensagem; ?>
                
                <?php if($mostrar_detalhes): ?>
                    <!-- Detalhes da Encomenda -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-info-circle me-2"></i>Detalhes da Encomenda #<?php echo $compra['id']; ?></h2>
                        <a href="encomendas.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i> Voltar para Lista
                        </a>
                    </div>
                    
                    <!-- Status Cards -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body p-0">
                                    <div class="d-flex justify-content-between align-items-center p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <span class="badge bg-<?php 
                                                switch($compra['status']) {
                                                    case 'pendente': echo 'warning'; break;
                                                    case 'enviado': echo 'primary'; break;
                                                    case 'recebido': echo 'success'; break;
                                                    case 'cancelado': echo 'danger'; break;
                                                    default: echo 'secondary';
                                                }
                                                ?> p-2">
                                                    <i class="fas fa-<?php 
                                                    switch($compra['status']) {
                                                        case 'pendente': echo 'clock'; break;
                                                        case 'enviado': echo 'truck'; break;
                                                        case 'recebido': echo 'check-circle'; break;
                                                        case 'cancelado': echo 'times-circle'; break;
                                                        default: echo 'question-circle';
                                                    }
                                                    ?> me-1"></i>
                                                    <?php echo ucfirst($compra['status']); ?>
                                                </span>
                                            </div>
                                            <h5 class="mb-0">Status atual da encomenda</h5>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#statusModal">
                                                <i class="fas fa-edit me-1"></i> Atualizar Status
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Informações da Encomenda -->
                            <div class="card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Informações da Encomenda</h5>
                                    <span class="badge bg-primary">ID: <?php echo $compra['id']; ?></span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Data da Compra:</strong><br>
                                            <?php echo date('d/m/Y H:i', strtotime($compra['data_compra'])); ?></p>
                                            
                                            <p><strong>Método de Pagamento:</strong><br>
                                            <?php echo !empty($compra['metodo_pagamento']) ? $compra['metodo_pagamento'] : 'Não especificado'; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Valor Total:</strong><br>
                                            <span class="text-primary fw-bold">€<?php echo number_format($compra['valor_compra'], 2, ',', '.'); ?></span></p>
                                            
                                            <p><strong>Número de Itens:</strong><br>
                                            <?php echo count($itens); ?> produto(s)</p>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <h6 class="fw-bold">Linha do Tempo</h6>
                                    <div class="timeline mt-3">
                                        <div class="timeline-item">
                                            <div class="timeline-dot">
                                                <i class="fas fa-shopping-cart"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <h6 class="mb-1">Pedido Realizado</h6>
                                                <p class="text-muted mb-0"><?php echo date('d/m/Y H:i', strtotime($compra['data_compra'])); ?></p>
                                            </div>
                                        </div>
                                        
                                        <?php if($compra['status'] == 'enviado' || $compra['status'] == 'recebido'): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-dot bg-primary">
                                                <i class="fas fa-truck"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <h6 class="mb-1">Pedido Enviado</h6>
                                                <p class="text-muted mb-0">Enviado para entrega</p>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if($compra['status'] == 'recebido'): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-dot bg-success">
                                                <i class="fas fa-check"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <h6 class="mb-1">Pedido Entregue</h6>
                                                <p class="text-muted mb-0">Entrega confirmada pelo cliente</p>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Itens da Encomenda -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-box-open me-2"></i>Itens da Encomenda</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Produto</th>
                                                    <th>Imagem</th>
                                                    <th class="text-end">Preço</th>
                                                    <th class="text-center">Qtd</th>
                                                    <th class="text-end">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($itens as $item): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold"><?php echo $item['nome_produto']; ?></div>
                                                        <small class="text-muted">ID: <?php echo $item['produto_id']; ?></small>
                                                    </td>
                                                    <td>
                                                        <?php if(!empty($item['imagem'])): ?>
                                                            <img src="<?php echo $item['imagem']; ?>" alt="<?php echo $item['nome_produto']; ?>" class="product-img">
                                                        <?php else: ?>
                                                            <div class="text-center text-muted">
                                                                <i class="fas fa-image fa-2x"></i>
                                                                <small>Sem imagem</small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end">€<?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                                                    <td class="text-center"><?php echo $item['quantidade']; ?></td>
                                                    <td class="text-end fw-bold">€<?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <th colspan="4" class="text-end">Total:</th>
                                                    <th class="text-end">€<?php echo number_format($compra['valor_compra'], 2, ',', '.'); ?></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- Informações do Cliente -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informações do Cliente</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="me-3">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                <i class="fas fa-user fa-lg"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?php echo $compra['nome_cliente']; ?></h6>
                                            <small class="text-muted">Cliente ID: <?php echo $compra['usuario_id']; ?></small>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <p class="mb-2">
                                        <i class="fas fa-envelope text-primary me-2"></i>
                                        <a href="mailto:<?php echo $compra['email']; ?>"><?php echo $compra['email']; ?></a>
                                    </p>
                                    
                                    <p class="mb-2">
                                        <i class="fas fa-phone text-primary me-2"></i>
                                        <a href="tel:<?php echo $compra['telefone']; ?>"><?php echo $compra['telefone']; ?></a>
                                    </p>
                                    
                                    <p class="mb-0">
                                        <i class="fas fa-id-card text-primary me-2"></i>
                                        NIF: <?php echo $compra['nif']; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Endereço de Entrega -->
                            <?php if(isset($endereco) && $endereco): ?>
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Endereço de Entrega</h5>
                                </div>
                                <div class="card-body">
                                    <address>
                                        <strong><?php echo $compra['nome_cliente']; ?></strong><br>
                                        <?php echo $endereco['rua']; ?>, <?php echo $endereco['numero']; ?><br>
                                        <?php if(!empty($endereco['complemento'])): ?>
                                            <?php echo $endereco['complemento']; ?><br>
                                        <?php endif; ?>
                                        <?php echo $endereco['cidade']; ?>, <?php echo $endereco['estado']; ?><br>
                                        <?php echo $endereco['cep']; ?><br>
                                        <?php echo $endereco['pais']; ?>
                                    </address>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Ações -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Ações</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="generate_invoice.php?compra_id=<?php echo $compra['id']; ?>" class="btn btn-primary" target="_blank">
                                            <i class="fas fa-file-invoice me-2"></i>Gerar Fatura
                                        </a>
                                        <a href="mailto:<?php echo $compra['email']; ?>" class="btn btn-outline-primary">
                                            <i class="fas fa-envelope me-2"></i>Contactar Cliente
                                        </a>
                                        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                            <i class="fas fa-times-circle me-2"></i>Cancelar Encomenda
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal de Atualização de Status -->
                    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="statusModalLabel">Atualizar Status da Encomenda</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="post" action="encomendas.php?compra_id=<?php echo $compra['id']; ?>">
                                    <div class="modal-body">
                                        <input type="hidden" name="compra_id" value="<?php echo $compra['id']; ?>">
                                        
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Selecione o novo status:</label>
                                            <select name="status" id="status" class="form-select">
                                                <option value="pendente" <?php if($compra['status'] == 'pendente') echo 'selected'; ?>>Pendente</option>
                                                <option value="enviado" <?php if($compra['status'] == 'enviado') echo 'selected'; ?>>Enviado</option>
                                                <option value="recebido" <?php if($compra['status'] == 'recebido') echo 'selected'; ?>>Recebido</option>
                                                <option value="cancelado" <?php if($compra['status'] == 'cancelado') echo 'selected'; ?>>Cancelado</option>
                                            </select>
                                        </div>
                                        
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            A alteração do status será registrada e não poderá ser desfeita.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" name="update_status" class="btn btn-primary">Atualizar Status</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal de Cancelamento -->
                    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="cancelModalLabel">Cancelar Encomenda</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="post" action="encomendas.php?compra_id=<?php echo $compra['id']; ?>">
                                    <div class="modal-body">
                                        <input type="hidden" name="compra_id" value="<?php echo $compra['id']; ?>">
                                        <input type="hidden" name="status" value="cancelado">
                                        
                                        <div class="alert alert-danger">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Atenção!</strong> Você está prestes a cancelar a encomenda #<?php echo $compra['id']; ?>.
                                            Esta ação não poderá ser desfeita.
                                        </div>
                                        
                                        <p>Tem certeza que deseja cancelar esta encomenda?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Não, Voltar</button>
                                        <button type="submit" name="update_status" class="btn btn-danger">Sim, Cancelar Encomenda</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                
                <?php else: ?>
                    <!-- Lista de Encomendas -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-shopping-cart me-2"></i>Gestão de Encomendas</h2>
                        <div>
                            <button class="btn btn-outline-success" id="exportBtn">
                                <i class="fas fa-file-excel me-1"></i> Exportar
                            </button>
                        </div>
                    </div>
                    
                    <!-- Cards de Estatísticas -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card primary h-100">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total de Encomendas
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_encomendas']; ?></div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card warning h-100">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Pendentes
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['pendentes']; ?></div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card success h-100">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Entregues
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['recebidos']; ?></div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card info h-100">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Valor Total
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">€<?php echo number_format($stats['valor_total'], 2, ',', '.'); ?></div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <i class="fas fa-euro-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filtros -->
                    <div class="card filter-card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros</h5>
                        </div>
                        <div class="card-body">
                            <form method="get" action="encomendas.php" class="row g-3">
                                <div class="col-md-3">
                                    <label for="filtro_status" class="form-label">Status</label>
                                    <select name="filtro_status" id="filtro_status" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="pendente" <?php if($filtro_status == 'pendente') echo 'selected'; ?>>Pendente</option>
                                        <option value="enviado" <?php if($filtro_status == 'enviado') echo 'selected'; ?>>Enviado</option>
                                        <option value="recebido" <?php if($filtro_status == 'recebido') echo 'selected'; ?>>Recebido</option>
                                        <option value="cancelado" <?php if($filtro_status == 'cancelado') echo 'selected'; ?>>Cancelado</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="data_inicio" class="form-label">Data Inicial</label>
                                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo $filtro_data_inicio; ?>">
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="data_fim" class="form-label">Data Final</label>
                                    <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo $filtro_data_fim; ?>">
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="cliente" class="form-label">Cliente</label>
                                    <input type="text" class="form-control" id="cliente" name="cliente" placeholder="Nome do cliente" value="<?php echo $filtro_cliente; ?>">
                                </div>
                                
                                <div class="col-12 text-end">
                                    <a href="encomendas.php" class="btn btn-secondary me-2">Limpar Filtros</a>
                                    <button type="submit" class="btn btn-primary btn-filter">
                                        <i class="fas fa-search me-1"></i> Filtrar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Tabela de Encomendas -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Encomendas</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="encomendasTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Data</th>
                                            <th class="text-end">Valor</th>
                                            <th>Status</th>
                                            <th class="text-center">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if($result->num_rows > 0) {
                                            while($row = $result->fetch_assoc()) {
                                                // Definir classe do badge de acordo com o status
                                                $badge_class = '';
                                                $status_icon = '';
                                                
                                                switch($row['status']) {
                                                    case 'pendente':
                                                        $badge_class = 'bg-warning';
                                                        $status_icon = 'clock';
                                                        break;
                                                    case 'enviado':
                                                        $badge_class = 'bg-primary';
                                                        $status_icon = 'truck';
                                                        break;
                                                    case 'recebido':
                                                        $badge_class = 'bg-success';
                                                        $status_icon = 'check-circle';
                                                        break;
                                                    case 'cancelado':
                                                        $badge_class = 'bg-danger';
                                                        $status_icon = 'times-circle';
                                                        break;
                                                    default:
                                                        $badge_class = 'bg-secondary';
                                                        $status_icon = 'question-circle';
                                                }
                                                ?>
                                                <tr>
                                                    <td><?php echo $row['id']; ?></td>
                                                    <td><?php echo $row['nome_cliente']; ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($row['data_compra'])); ?></td>
                                                    <td class="text-end">€<?php echo number_format($row['valor_compra'], 2, ',', '.'); ?></td>
                                                    <td>
                                                        <span class="badge <?php echo $badge_class; ?>">
                                                            <i class="fas fa-<?php echo $status_icon; ?> me-1"></i>
                                                            <?php echo ucfirst($row['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group">
                                                            <a href="encomendas.php?compra_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="generate_invoice.php?compra_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" target="_blank">
                                                                <i class="fas fa-file-invoice"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-warning dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li>
                                                                    <form method="post" action="encomendas.php">
                                                                        <input type="hidden" name="compra_id" value="<?php echo $row['id']; ?>">
                                                                        <input type="hidden" name="status" value="pendente">
                                                                        <button type="submit" name="update_status" class="dropdown-item">
                                                                            <i class="fas fa-clock text-warning me-2"></i>Pendente
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                                <li>
                                                                    <form method="post" action="encomendas.php">
                                                                        <input type="hidden" name="compra_id" value="<?php echo $row['id']; ?>">
                                                                        <input type="hidden" name="status" value="enviado">
                                                                        <button type="submit" name="update_status" class="dropdown-item">
                                                                            <i class="fas fa-truck text-primary me-2"></i>Enviado
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                                <li>
                                                                    <form method="post" action="encomendas.php">
                                                                        <input type="hidden" name="compra_id" value="<?php echo $row['id']; ?>">
                                                                        <input type="hidden" name="status" value="recebido">
                                                                        <button type="submit" name="update_status" class="dropdown-item">
                                                                            <i class="fas fa-check-circle text-success me-2"></i>Recebido
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <form method="post" action="encomendas.php">
                                                                        <input type="hidden" name="compra_id" value="<?php echo $row['id']; ?>">
                                                                        <input type="hidden" name="status" value="cancelado">
                                                                        <button type="submit" name="update_status" class="dropdown-item text-danger">
                                                                            <i class="fas fa-times-circle me-2"></i>Cancelar
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            </ul>
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
                            
                            <!-- Paginação -->
                            <?php if($result->num_rows > 0): ?>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <p class="mb-0">Exibindo <?php echo min($itens_por_pagina, $result->num_rows); ?> de <?php echo $total_registros; ?> registros</p>
                                </div>
                                <nav aria-label="Navegação de página">
                                    <ul class="pagination">
                                        <?php if($pagina_atual > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="encomendas.php?pagina=1<?php 
                                                echo (!empty($filtro_status)) ? "&filtro_status=$filtro_status" : "";
                                                echo (!empty($filtro_data_inicio)) ? "&data_inicio=$filtro_data_inicio" : "";
                                                echo (!empty($filtro_data_fim)) ? "&data_fim=$filtro_data_fim" : "";
                                                echo (!empty($filtro_cliente)) ? "&cliente=$filtro_cliente" : "";
                                            ?>" aria-label="Primeira">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="encomendas.php?pagina=<?php echo $pagina_atual - 1; ?><?php 
                                                echo (!empty($filtro_status)) ? "&filtro_status=$filtro_status" : "";
                                                echo (!empty($filtro_data_inicio)) ? "&data_inicio=$filtro_data_inicio" : "";
                                                echo (!empty($filtro_data_fim)) ? "&data_fim=$filtro_data_fim" : "";
                                                echo (!empty($filtro_cliente)) ? "&cliente=$filtro_cliente" : "";
                                            ?>" aria-label="Anterior">
                                                <span aria-hidden="true">&lt;</span>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php
                                        // Determinar quais páginas mostrar
                                        $inicio_paginas = max(1, $pagina_atual - 2);
                                        $fim_paginas = min($total_paginas, $pagina_atual + 2);
                                        
                                        // Garantir que sempre mostramos 5 páginas se possível
                                        if ($fim_paginas - $inicio_paginas + 1 < 5) {
                                            if ($inicio_paginas == 1) {
                                                $fim_paginas = min($total_paginas, $inicio_paginas + 4);
                                            } elseif ($fim_paginas == $total_paginas) {
                                                $inicio_paginas = max(1, $fim_paginas - 4);
                                            }
                                        }
                                        
                                        for ($i = $inicio_paginas; $i <= $fim_paginas; $i++):
                                        ?>
                                        <li class="page-item <?php echo ($i == $pagina_atual) ? 'active' : ''; ?>">
                                            <a class="page-link" href="encomendas.php?pagina=<?php echo $i; ?><?php 
                                                echo (!empty($filtro_status)) ? "&filtro_status=$filtro_status" : "";
                                                echo (!empty($filtro_data_inicio)) ? "&data_inicio=$filtro_data_inicio" : "";
                                                echo (!empty($filtro_data_fim)) ? "&data_fim=$filtro_data_fim" : "";
                                                echo (!empty($filtro_cliente)) ? "&cliente=$filtro_cliente" : "";
                                            ?>"><?php echo $i; ?></a>
                                        </li>
                                        <?php endfor; ?>
                                        
                                        <?php if($pagina_atual < $total_paginas): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="encomendas.php?pagina=<?php echo $pagina_atual + 1; ?><?php 
                                                echo (!empty($filtro_status)) ? "&filtro_status=$filtro_status" : "";
                                                echo (!empty($filtro_data_inicio)) ? "&data_inicio=$filtro_data_inicio" : "";
                                                echo (!empty($filtro_data_fim)) ? "&data_fim=$filtro_data_fim" : "";
                                                echo (!empty($filtro_cliente)) ? "&cliente=$filtro_cliente" : "";
                                            ?>" aria-label="Próxima">
                                                <span aria-hidden="true">&gt;</span>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="encomendas.php?pagina=<?php echo $total_paginas; ?><?php 
                                                echo (!empty($filtro_status)) ? "&filtro_status=$filtro_status" : "";
                                                echo (!empty($filtro_data_inicio)) ? "&data_inicio=$filtro_data_inicio" : "";
                                                echo (!empty($filtro_data_fim)) ? "&data_fim=$filtro_data_fim" : "";
                                                echo (!empty($filtro_cliente)) ? "&cliente=$filtro_cliente" : "";
                                            ?>" aria-label="Última">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- SheetJS (Excel Export) -->
    <script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>
    
    <script>
        // Inicializar DataTable para pesquisa e ordenação
        $(document).ready(function() {
            // Inicializar DataTable apenas se houver dados
            if ($('#encomendasTable tbody tr').length > 1 || $('#encomendasTable tbody tr td').length > 1) {
                var table = $('#encomendasTable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-PT.json'
                    },
                    paging: false,
                    searching: true,
                    ordering: true,
                    info: false
                });
            }
            
            // Exportar para Excel
            $('#exportBtn').on('click', function() {
                // Criar uma tabela temporária para exportação
                var $exportTable = $('#encomendasTable').clone();
                
                // Remover a coluna de ações
                $exportTable.find('thead th:last-child, tbody td:last-child').remove();
                
                // Converter a tabela em uma matriz de dados
                var data = [];
                var headers = [];
                
                $exportTable.find('thead th').each(function() {
                    headers.push($(this).text().trim());
                });
                
                data.push(headers);
                
                $exportTable.find('tbody tr').each(function() {
                    var rowData = [];
                    $(this).find('td').each(function() {
                        rowData.push($(this).text().trim());
                    });
                    data.push(rowData);
                });
                
                // Criar uma planilha Excel
                var ws = XLSX.utils.aoa_to_sheet(data);
                var wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "Encomendas");
                
                // Gerar o arquivo e fazer o download
                XLSX.writeFile(wb, "Encomendas_" + new Date().toISOString().split('T')[0] + ".xlsx");
            });
        });
    </script>
</body>
</html>
