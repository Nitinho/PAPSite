<?php
// Iniciar a sessão administrativa
session_name('admin_session');
session_start();

// Incluir arquivo de configuração
require_once 'config.php';

// Verificar login administrativo
verificarLoginAdmin();

// Inicializar variáveis
$mensagem = '';
$categorias = [];
$search = '';
$categoria_filtro = '';
$produtos = [];

// Classe para gerenciar produtos
class ProdutoManager {
    private $conn;
    private $upload_dir;
    private $default_image;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->upload_dir = "../img/";
        $this->default_image = "/PAPSite/img/Bolapao.png";
        
        // Garantir que o diretório de upload existe
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
    }
    
    // Obter todas as categorias distintas
    public function getCategorias() {
        $categorias = [];
        $query = "SELECT DISTINCT categoria FROM produtos WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria";
        $result = $this->conn->query($query);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $categorias[] = $row['categoria'];
            }
        }
        
        return $categorias;
    }
    
    // Gerar nome único para arquivo
    private function gerarNomeUnico($nome_original) {
        $nome_base = pathinfo($nome_original, PATHINFO_FILENAME);
        $nome_base = preg_replace('/[^a-zA-Z0-9_-]/', '', $nome_base); // Sanitizar nome
        $extensao = pathinfo($nome_original, PATHINFO_EXTENSION);
        $nome_arquivo = $nome_base;
        $contador = 1;
        
        while (true) {
            $query = "SELECT COUNT(*) as count FROM produtos WHERE imagem LIKE ?";
            $stmt = $this->conn->prepare($query);
            $nome_busca = "%{$nome_arquivo}.{$extensao}";
            $stmt->bind_param("s", $nome_busca);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] == 0) {
                break;
            }
            
            $nome_arquivo = $nome_base . '_' . $contador;
            $contador++;
        }
        
        return $nome_arquivo . '.' . $extensao;
    }
    
    // Processar upload de imagem
    public function processarImagem($file, $imagem_atual = null) {
        // Se não houver arquivo ou ocorrer erro, manter imagem atual ou usar padrão
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return $imagem_atual ?: $this->default_image;
        }
        
        // Verificar se é uma imagem válida
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            throw new Exception("O arquivo enviado não é uma imagem válida.");
        }
        
        // Verificar tamanho do arquivo (max 5MB)
        if ($file["size"] > 5000000) {
            throw new Exception("O arquivo é muito grande. Tamanho máximo: 5MB.");
        }
        
        // Verificar extensão
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extensao = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        if (!in_array($extensao, $allowed_types)) {
            throw new Exception("Apenas arquivos JPG, JPEG, PNG, GIF e WEBP são permitidos.");
        }
        
        // Gerar nome único e mover arquivo
        $nome_arquivo = $this->gerarNomeUnico($file["name"]);
        $target_file = $this->upload_dir . $nome_arquivo;
        
        if (!move_uploaded_file($file["tmp_name"], $target_file)) {
            throw new Exception("Erro ao fazer upload da imagem.");
        }
        
        return "/PAPSite/img/" . $nome_arquivo;
    }
    
    // Adicionar produto
    public function adicionarProduto($dados, $imagem) {
        try {
            // Verificar se categoria é nova
            if ($dados['categoria'] === 'nova' && !empty($dados['nova_categoria'])) {
                $dados['categoria'] = trim($dados['nova_categoria']);
            }
    
            // Processar imagem
            $imagem_path = $this->processarImagem($imagem);
    
            // Inserir produto (corrigido: sem data_criacao)
            $insert_query = "INSERT INTO produtos (nome, descricao, preco, imagem, categoria) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($insert_query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            $stmt->bind_param(
                "ssdss", 
                $dados['nome'], 
                $dados['descricao'], 
                $dados['preco'], 
                $imagem_path, 
                $dados['categoria']
            );
    
            if (!$stmt->execute()) {
                throw new Exception("Erro ao adicionar produto: " . $this->conn->error);
            }
    
            return [
                'success' => true,
                'message' => 'Produto adicionado com sucesso!',
                'produto_id' => $this->conn->insert_id
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    
    // Atualizar produto
    public function atualizarProduto($id, $dados, $imagem, $imagem_atual) {
        try {
            // Verificar se categoria é nova
            if ($dados['categoria'] === 'nova' && !empty($dados['nova_categoria'])) {
                $dados['categoria'] = trim($dados['nova_categoria']);
            }
            
            // Processar imagem se fornecida
            $imagem_path = $imagem_atual;
            if (isset($imagem) && $imagem['size'] > 0) {
                $imagem_path = $this->processarImagem($imagem, $imagem_atual);
            }
            
            // Atualizar produto
            $update_query = "UPDATE produtos SET 
                             nome = ?, 
                             descricao = ?, 
                             preco = ?, 
                             imagem = ?, 
                             categoria = ?,
                             data_atualizacao = NOW()
                             WHERE id = ?";
            $stmt = $this->conn->prepare($update_query);
            $stmt->bind_param("ssdssi", 
                $dados['nome'], 
                $dados['descricao'], 
                $dados['preco'], 
                $imagem_path, 
                $dados['categoria'], 
                $id
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao atualizar produto: " . $this->conn->error);
            }
            
            return [
                'success' => true,
                'message' => 'Produto atualizado com sucesso!'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    // Excluir produto
    public function excluirProduto($id) {
        try {
            // Verificar se o produto está em alguma compra
            $check_query = "SELECT COUNT(*) as count FROM itens_compra WHERE produto_id = ?";
            $stmt = $this->conn->prepare($check_query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                throw new Exception("Não é possível excluir este produto pois ele está associado a compras existentes.");
            }
            
            // Obter imagem atual para possível exclusão do arquivo
            $img_query = "SELECT imagem FROM produtos WHERE id = ?";
            $stmt = $this->conn->prepare($img_query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $produto = $result->fetch_assoc();
            
            // Excluir o produto
            $delete_query = "DELETE FROM produtos WHERE id = ?";
            $stmt = $this->conn->prepare($delete_query);
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao excluir produto: " . $this->conn->error);
            }
            
            // Tentar excluir a imagem se não for a padrão
            if ($produto && $produto['imagem'] !== $this->default_image) {
                $imagem_path = str_replace("/PAPSite/img/", $this->upload_dir, $produto['imagem']);
                if (file_exists($imagem_path)) {
                    @unlink($imagem_path);
                }
            }
            
            return [
                'success' => true,
                'message' => 'Produto excluído com sucesso!'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    // Buscar produtos com filtros
    public function buscarProdutos($search = '', $categoria = '') {
        $produtos = [];
        $sql = "SELECT p.*, 
                (SELECT COUNT(*) FROM itens_compra ic WHERE ic.produto_id = p.id) as vendas_count 
                FROM produtos p WHERE 1=1";
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $sql .= " AND (p.nome LIKE ? OR p.descricao LIKE ?)";
            $search_term = "%$search%";
            $params[] = $search_term;
            $params[] = $search_term;
            $types .= "ss";
        }
        
        if (!empty($categoria)) {
            $sql .= " AND p.categoria = ?";
            $params[] = $categoria;
            $types .= "s";
        }
        
        $sql .= " ORDER BY p.categoria, p.nome";
        
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $produtos[] = $row;
        }
        
        return $produtos;
    }
}

// Instanciar gerenciador de produtos
$produtoManager = new ProdutoManager($conn);

// Buscar todas as categorias
$categorias = $produtoManager->getCategorias();

// Processar exclusão de produto
if (isset($_POST['delete_produto']) && isset($_POST['produto_id'])) {
    $produto_id = intval($_POST['produto_id']);
    $resultado = $produtoManager->excluirProduto($produto_id);
    
    if ($resultado['success']) {
        $mensagem = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' . $resultado['message'] . '</div>';
    } else {
        $mensagem = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> ' . $resultado['message'] . '</div>';
    }
}

// Processar atualização de produto
if (isset($_POST['update_produto'])) {
    $produto_id = intval($_POST['produto_id']);
    $dados = [
        'nome' => trim($_POST['nome']),
        'descricao' => trim($_POST['descricao']),
        'preco' => str_replace(',', '.', $_POST['preco']),
        'categoria' => $_POST['categoria'],
        'nova_categoria' => isset($_POST['nova_categoria']) ? trim($_POST['nova_categoria']) : ''
    ];
    
    $resultado = $produtoManager->atualizarProduto(
        $produto_id, 
        $dados, 
        isset($_FILES['imagem']) ? $_FILES['imagem'] : null, 
        $_POST['imagem_atual']
    );
    
    if ($resultado['success']) {
        $mensagem = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' . $resultado['message'] . '</div>';
        // Atualizar lista de categorias após possível adição de nova categoria
        $categorias = $produtoManager->getCategorias();
    } else {
        $mensagem = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> ' . $resultado['message'] . '</div>';
    }
}

// Processar adição de novo produto
if (isset($_POST['add_produto'])) {
    $dados = [
        'nome' => trim($_POST['nome']),
        'descricao' => trim($_POST['descricao']),
        'preco' => str_replace(',', '.', $_POST['preco']),
        'categoria' => $_POST['categoria'],
        'nova_categoria' => isset($_POST['nova_categoria']) ? trim($_POST['nova_categoria']) : ''
    ];
    
    $resultado = $produtoManager->adicionarProduto(
        $dados, 
        isset($_FILES['imagem']) ? $_FILES['imagem'] : null
    );
    
    if ($resultado['success']) {
        $mensagem = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' . $resultado['message'] . '</div>';
        // Atualizar lista de categorias após possível adição de nova categoria
        $categorias = $produtoManager->getCategorias();
    } else {
        $mensagem = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> ' . $resultado['message'] . '</div>';
    }
}

// Processar pesquisa e filtro
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
    $categoria_filtro = trim($_GET['categoria']);
}

// Buscar produtos com filtros
$produtos = $produtoManager->buscarProdutos($search, $categoria_filtro);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Produtos - Painel Administrativo</title>
    <link rel="shortcut icon" type="image/x-icon" href="../img/logolopes.ico">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --dark-color: #5a5c69;
            --light-color: #f8f9fc;
            --border-radius: 0.35rem;
            --box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        body {
            background-color: var(--light-color);
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
        select.form-control {
    padding: 0.5rem 1rem 0.5rem 0.5rem;
    line-height: normal;
    height: auto !important;
    font-kerning: normal;
}

/* Para garantir que o texto não seja cortado nos dropdowns */
.dropdown-item, 
option {
    padding: 0.25rem 1rem;
    line-height: 1.5;
}

/* Ajuste adicional para o botão de filtro ao lado do select */
.btn-secondary {
    height: 100%;
    display: flex;
    align-items: center;
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
        
        .product-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            transition: transform 0.3s ease;
        }
        
        .product-img:hover {
            transform: scale(1.1);
        }
        
        .categoria-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.6rem;
            border-radius: 50px;
        }
        
        .modal-img-preview {
            max-width: 100%;
            max-height: 200px;
            display: block;
            margin: 10px auto;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .btn-action {
            padding: 0.4rem 0.75rem;
            border-radius: var(--border-radius);
            margin-right: 5px;
        }
        
        .form-control {
            border-radius: var(--border-radius);
            padding: 0.6rem 1rem;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
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
        
        .alert-warning {
            border-left-color: var(--warning-color);
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
            background-color: var(--primary-color);
        }
        
        .table th {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-color) !important;
            color: white !important;
            border: none !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #4262c5 !important;
            color: white !important;
            border: none !important;
        }
        
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .custom-file-label {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .price-tag {
            background-color: var(--secondary-color);
            color: white;
            padding: 0.3rem 0.6rem;
            border-radius: 50px;
            font-weight: bold;
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
                <a href="registrar.php"><i class="fas fa-user-plus mr-2"></i> Registrar Clientes</a>
                <a href="produtosg.php" class="active"><i class="fas fa-box mr-2"></i> Gerenciar Produtos</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt mr-2"></i> Sair</a>
            </div>
            
            <!-- Conteúdo principal -->
            <div class="col-md-10 content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title mb-0"><i class="fas fa-box mr-2"></i> Gerenciar Produtos</h2>
                    <span class="text-muted">Data atual: <?php echo date('d/m/Y H:i'); ?></span>
                </div>
                
                <?php echo $mensagem; ?>
                
                <!-- Filtros e pesquisa -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <form method="get" action="produtosg.php" class="form-inline">
                                    <div class="input-group w-100">
                                        <input type="text" class="form-control" placeholder="Pesquisar produtos..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-4">
                                <form method="get" action="produtosg.php" class="form-inline">
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                    <select name="categoria" class="form-control mr-2">
                                        <option value="">Todas as categorias</option>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php if ($categoria_filtro === $cat) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars(ucfirst($cat)); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-secondary">Filtrar</button>
                                </form>
                            </div>
                            <div class="col-md-2 text-right">
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addProdutoModal">
                                    <i class="fas fa-plus"></i> Novo Produto
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Estatísticas rápidas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Total de Produtos</h6>
                                        <h2 class="mb-0"><?php echo count($produtos); ?></h2>
                                    </div>
                                    <i class="fas fa-box fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Categorias</h6>
                                        <h2 class="mb-0"><?php echo count($categorias); ?></h2>
                                    </div>
                                    <i class="fas fa-tags fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Preço Médio</h6>
                                        <h2 class="mb-0">
                                            <?php 
                                            $total = 0;
                                            foreach ($produtos as $p) {
                                                $total += $p['preco'];
                                            }
                                            echo count($produtos) > 0 ? '€' . number_format($total / count($produtos), 2, ',', '.') : '€0,00';
                                            ?>
                                        </h2>
                                    </div>
                                    <i class="fas fa-euro-sign fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Mais Vendido</h6>
                                        <?php
                                        $mais_vendido = null;
                                        $max_vendas = 0;
                                        foreach ($produtos as $p) {
                                            if (isset($p['vendas_count']) && $p['vendas_count'] > $max_vendas) {
                                                $mais_vendido = $p;
                                                $max_vendas = $p['vendas_count'];
                                            }
                                        }
                                        ?>
                                        <h5 class="mb-0">
                                            <?php echo $mais_vendido ? htmlspecialchars(substr($mais_vendido['nome'], 0, 15)) . (strlen($mais_vendido['nome']) > 15 ? '...' : '') : 'N/A'; ?>
                                        </h5>
                                    </div>
                                    <i class="fas fa-crown fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de produtos -->
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Lista de Produtos</h5>
                            <div>
                                <span class="badge badge-primary"><?php echo count($produtos); ?> produtos encontrados</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (count($produtos) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped" id="produtosTable">
                                    <thead>
                                        <tr>
                                            <th width="5%">ID</th>
                                            <th width="10%">Imagem</th>
                                            <th width="20%">Nome</th>
                                            <th width="25%">Descrição</th>
                                            <th width="10%">Preço</th>
                                            <th width="10%">Categoria</th>
                                            <th width="20%">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($produtos as $produto): ?>
                                            <tr class="product-card">
                                                <td><?php echo $produto['id']; ?></td>
                                                <td>
                                                    <img src="<?php echo htmlspecialchars($produto['imagem']); ?>" 
                                                         alt="<?php echo htmlspecialchars($produto['nome']); ?>" 
                                                         class="product-img" 
                                                         data-toggle="tooltip" 
                                                         title="<?php echo htmlspecialchars($produto['nome']); ?>">
                                                </td>
                                                <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                                <td><?php echo htmlspecialchars(substr($produto['descricao'], 0, 50) . (strlen($produto['descricao']) > 50 ? '...' : '')); ?></td>
                                                <td><span class="price-tag">€<?php echo number_format($produto['preco'], 2, ',', '.'); ?></span></td>
                                                <td>
                                                    <span class="badge badge-info categoria-badge">
                                                        <?php echo htmlspecialchars(ucfirst($produto['categoria'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary btn-action" 
                                                            data-toggle="modal" 
                                                            data-target="#editProdutoModal" 
                                                            data-id="<?php echo $produto['id']; ?>"
                                                            data-nome="<?php echo htmlspecialchars($produto['nome']); ?>"
                                                            data-descricao="<?php echo htmlspecialchars($produto['descricao']); ?>"
                                                            data-preco="<?php echo $produto['preco']; ?>"
                                                            data-categoria="<?php echo htmlspecialchars($produto['categoria']); ?>"
                                                            data-imagem="<?php echo htmlspecialchars($produto['imagem']); ?>">
                                                        <i class="fas fa-edit"></i> Editar
                                                    </button>
                                                    
                                                    <button type="button" class="btn btn-sm btn-danger btn-action" 
                                                            data-toggle="modal" 
                                                            data-target="#deleteProdutoModal"
                                                            data-id="<?php echo $produto['id']; ?>"
                                                            data-nome="<?php echo htmlspecialchars($produto['nome']); ?>">
                                                        <i class="fas fa-trash"></i> Excluir
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                Nenhum produto encontrado. <?php if (!empty($search) || !empty($categoria_filtro)): ?>Tente ajustar os filtros de pesquisa.<?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Produto -->
    <div class="modal fade" id="addProdutoModal" tabindex="-1" role="dialog" aria-labelledby="addProdutoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addProdutoModalLabel"><i class="fas fa-plus-circle mr-2"></i> Adicionar Novo Produto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="produtosg.php" enctype="multipart/form-data" id="formAddProduto">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nome"><i class="fas fa-tag mr-1"></i> Nome do Produto</label>
                                    <input type="text" class="form-control" id="nome" name="nome" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="preco"><i class="fas fa-euro-sign mr-1"></i> Preço (€)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">€</span>
                                        </div>
                                        <input type="text" class="form-control" id="preco" name="preco" required placeholder="0,00">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="categoria"><i class="fas fa-folder mr-1"></i> Categoria</label>
                                    <select class="form-control" id="categoria" name="categoria" required>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars(ucfirst($cat)); ?></option>
                                        <?php endforeach; ?>
                                        <option value="nova">Nova Categoria...</option>
                                    </select>
                                </div>
                                
                                <div class="form-group" id="nova-categoria-group" style="display: none;">
                                    <label for="nova_categoria"><i class="fas fa-plus-circle mr-1"></i> Nova Categoria</label>
                                    <input type="text" class="form-control" id="nova_categoria" name="nova_categoria">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="descricao"><i class="fas fa-align-left mr-1"></i> Descrição</label>
                                    <textarea class="form-control" id="descricao" name="descricao" rows="4" required></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="imagem"><i class="fas fa-image mr-1"></i> Imagem do Produto</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="imagem" name="imagem" accept="image/*">
                                        <label class="custom-file-label" for="imagem">Escolher arquivo</label>
                                    </div>
                                    <small class="form-text text-muted">Formatos aceitos: JPG, JPEG, PNG, GIF, WEBP. Tamanho máximo: 5MB.</small>
                                    <div id="preview-container" class="mt-2" style="display: none;">
                                        <img id="preview-image" class="modal-img-preview" src="" alt="Preview">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancelar</button>
                        <button type="submit" name="add_produto" class="btn btn-success"><i class="fas fa-plus mr-1"></i> Adicionar Produto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Produto -->
    <div class="modal fade" id="editProdutoModal" tabindex="-1" role="dialog" aria-labelledby="editProdutoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editProdutoModalLabel"><i class="fas fa-edit mr-2"></i> Editar Produto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="produtosg.php" enctype="multipart/form-data" id="formEditProduto">
                    <div class="modal-body">
                        <input type="hidden" id="edit_produto_id" name="produto_id">
                        <input type="hidden" id="edit_imagem_atual" name="imagem_atual">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_nome"><i class="fas fa-tag mr-1"></i> Nome do Produto</label>
                                    <input type="text" class="form-control" id="edit_nome" name="nome" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_preco"><i class="fas fa-euro-sign mr-1"></i> Preço (€)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">€</span>
                                        </div>
                                        <input type="text" class="form-control" id="edit_preco" name="preco" required placeholder="0,00">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_categoria"><i class="fas fa-folder mr-1"></i> Categoria</label>
                                    <select class="form-control" id="edit_categoria" name="categoria" required>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars(ucfirst($cat)); ?></option>
                                        <?php endforeach; ?>
                                        <option value="nova">Nova Categoria...</option>
                                    </select>
                                </div>
                                
                                <div class="form-group" id="edit-nova-categoria-group" style="display: none;">
                                    <label for="edit_nova_categoria"><i class="fas fa-plus-circle mr-1"></i> Nova Categoria</label>
                                    <input type="text" class="form-control" id="edit_nova_categoria" name="nova_categoria">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_descricao"><i class="fas fa-align-left mr-1"></i> Descrição</label>
                                    <textarea class="form-control" id="edit_descricao" name="descricao" rows="4" required></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_imagem"><i class="fas fa-image mr-1"></i> Imagem do Produto</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="edit_imagem" name="imagem" accept="image/*">
                                        <label class="custom-file-label" for="edit_imagem">Escolher arquivo</label>
                                    </div>
                                    <small class="form-text text-muted">Selecione uma nova imagem apenas se desejar substituir a atual.</small>
                                    <div id="edit-preview-container" class="mt-2">
                                        <img id="edit-preview-image" class="modal-img-preview" src="" alt="Preview">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancelar</button>
                        <button type="submit" name="update_produto" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Excluir Produto -->
    <div class="modal fade" id="deleteProdutoModal" tabindex="-1" role="dialog" aria-labelledby="deleteProdutoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteProdutoModalLabel"><i class="fas fa-trash-alt mr-2"></i> Confirmar Exclusão</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
                        <h5>Tem certeza que deseja excluir o produto <strong id="delete-produto-nome"></strong>?</h5>
                        <p class="text-danger">Esta ação não pode ser desfeita.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <form method="post" action="produtosg.php">
                        <input type="hidden" id="delete_produto_id" name="produto_id">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancelar</button>
                        <button type="submit" name="delete_produto" class="btn btn-danger"><i class="fas fa-trash mr-1"></i> Confirmar Exclusão</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Inicializar DataTables
            $('#produtosTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-PT.json"
                },
                "pageLength": 10,
                "responsive": true,
                "columnDefs": [
                    { "orderable": false, "targets": [1, 6] }
                ]
            });
            
            // Inicializar tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Preview da imagem ao adicionar produto
            $('#imagem').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        $('#preview-image').attr('src', event.target.result);
                        $('#preview-container').show();
                    }
                    reader.readAsDataURL(file);
                    
                    // Atualizar label com nome do arquivo
                    $(this).next('.custom-file-label').html(file.name);
                }
            });
            
            // Preview da imagem ao editar produto
            $('#edit_imagem').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        $('#edit-preview-image').attr('src', event.target.result);
                    }
                    reader.readAsDataURL(file);
                    
                    // Atualizar label com nome do arquivo
                    $(this).next('.custom-file-label').html(file.name);
                }
            });
            
            // Mostrar/esconder campo de nova categoria
            $('#categoria').on('change', function() {
                const novaCategoria = $('#nova-categoria-group');
                if ($(this).val() === 'nova') {
                    novaCategoria.slideDown();
                    $('#nova_categoria').attr('required', 'required');
                } else {
                    novaCategoria.slideUp();
                    $('#nova_categoria').removeAttr('required');
                }
            });
            
            $('#edit_categoria').on('change', function() {
                const novaCategoria = $('#edit-nova-categoria-group');
                if ($(this).val() === 'nova') {
                    novaCategoria.slideDown();
                    $('#edit_nova_categoria').attr('required', 'required');
                } else {
                    novaCategoria.slideUp();
                    $('#edit_nova_categoria').removeAttr('required');
                }
            });
            
            // Preencher modal de edição
            $('#editProdutoModal').on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                const id = button.data('id');
                const nome = button.data('nome');
                const descricao = button.data('descricao');
                const preco = button.data('preco');
                const categoria = button.data('categoria');
                const imagem = button.data('imagem');
                
                const modal = $(this);
                modal.find('#edit_produto_id').val(id);
                modal.find('#edit_nome').val(nome);
                modal.find('#edit_descricao').val(descricao);
                modal.find('#edit_preco').val(preco.toString().replace('.', ','));
                modal.find('#edit_categoria').val(categoria);
                modal.find('#edit_imagem_atual').val(imagem);
                modal.find('#edit-preview-image').attr('src', imagem);
                
                // Resetar o input de arquivo
                modal.find('#edit_imagem').val('');
                modal.find('#edit_imagem').next('.custom-file-label').html('Escolher arquivo');
                
                // Verificar se a categoria existe no select
                const categoriaExiste = Array.from(modal.find('#edit_categoria option')).some(option => option.value === categoria);
                if (!categoriaExiste && categoria) {
                    // Adicionar a categoria ao select
                    const option = new Option(categoria.charAt(0).toUpperCase() + categoria.slice(1), categoria);
                    modal.find('#edit_categoria').append(option);
                    modal.find('#edit_categoria').val(categoria);
                }
                
                // Esconder o campo de nova categoria
                modal.find('#edit-nova-categoria-group').hide();
                modal.find('#edit_nova_categoria').removeAttr('required');
            });
            
            // Preencher modal de exclusão
            $('#deleteProdutoModal').on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                const id = button.data('id');
                const nome = button.data('nome');
                
                const modal = $(this);
                modal.find('#delete_produto_id').val(id);
                modal.find('#delete-produto-nome').text(nome);
            });
            
            // Validar preço (aceitar apenas números e vírgula)
            function validarPreco(input) {
                input.value = input.value.replace(/[^0-9,]/g, '');
                // Garantir que há apenas uma vírgula
                const parts = input.value.split(',');
                if (parts.length > 2) {
                    input.value = parts[0] + ',' + parts.slice(1).join('');
                }
                // Limitar a 2 casas decimais
                if (parts.length === 2 && parts[1].length > 2) {
                    input.value = parts[0] + ',' + parts[1].substring(0, 2);
                }
            }
            
            $('#preco').on('input', function() {
                validarPreco(this);
            });
            
            $('#edit_preco').on('input', function() {
                validarPreco(this);
            });
            
            // Validação de formulários
            $('#formAddProduto').on('submit', function(e) {
                const nome = $('#nome').val().trim();
                const preco = $('#preco').val().trim();
                const descricao = $('#descricao').val().trim();
                const categoria = $('#categoria').val();
                
                if (nome === '') {
                    e.preventDefault();
                    alert('Por favor, informe o nome do produto.');
                    return false;
                }
                
                if (preco === '') {
                    e.preventDefault();
                    alert('Por favor, informe o preço do produto.');
                    return false;
                }
                
                if (descricao === '') {
                    e.preventDefault();
                    alert('Por favor, informe a descrição do produto.');
                    return false;
                }
                
                if (categoria === 'nova' && $('#nova_categoria').val().trim() === '') {
                    e.preventDefault();
                    alert('Por favor, informe o nome da nova categoria.');
                    return false;
                }
                
                return true;
            });
            
            $('#formEditProduto').on('submit', function(e) {
                const nome = $('#edit_nome').val().trim();
                const preco = $('#edit_preco').val().trim();
                const descricao = $('#edit_descricao').val().trim();
                const categoria = $('#edit_categoria').val();
                
                if (nome === '') {
                    e.preventDefault();
                    alert('Por favor, informe o nome do produto.');
                    return false;
                }
                
                if (preco === '') {
                    e.preventDefault();
                    alert('Por favor, informe o preço do produto.');
                    return false;
                }
                
                if (descricao === '') {
                    e.preventDefault();
                    alert('Por favor, informe a descrição do produto.');
                    return false;
                }
                
                if (categoria === 'nova' && $('#edit_nova_categoria').val().trim() === '') {
                    e.preventDefault();
                    alert('Por favor, informe o nome da nova categoria.');
                    return false;
                }
                
                return true;
            });
            
            // Animação para alertas
            $('.alert').fadeIn('slow').delay(5000).fadeOut('slow');
        });
    </script>
</body>
</html>
