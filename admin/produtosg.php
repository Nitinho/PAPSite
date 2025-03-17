<?php
require_once 'config.php';
verificarLogin();

// Inicializar variáveis
$mensagem = '';
$categorias = [];
$search = '';
$categoria_filtro = '';

// Buscar todas as categorias distintas
$query_categorias = "SELECT DISTINCT categoria FROM produtos ORDER BY categoria";
$result_categorias = $conn->query($query_categorias);
while ($row = $result_categorias->fetch_assoc()) {
    if (!empty($row['categoria'])) {
        $categorias[] = $row['categoria'];
    }
}

// Processar exclusão de produto
if (isset($_POST['delete_produto']) && isset($_POST['produto_id'])) {
    $produto_id = $_POST['produto_id'];
    
    // Verificar se o produto está em alguma compra
    $check_query = "SELECT COUNT(*) as count FROM itens_compra WHERE produto_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $produto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $mensagem = '<div class="alert alert-danger">Não é possível excluir este produto pois ele está associado a compras existentes.</div>';
    } else {
        // Excluir o produto
        $delete_query = "DELETE FROM produtos WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $produto_id);
        
        if ($stmt->execute()) {
            $mensagem = '<div class="alert alert-success">Produto excluído com sucesso!</div>';
        } else {
            $mensagem = '<div class="alert alert-danger">Erro ao excluir produto: ' . $conn->error . '</div>';
        }
    }
}

// Processar atualização de produto
if (isset($_POST['update_produto'])) {
    $produto_id = $_POST['produto_id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = str_replace(',', '.', $_POST['preco']); // Converter vírgula para ponto
    $categoria = $_POST['categoria'];
    
    // Verificar se foi enviada uma nova imagem
    $imagem = $_POST['imagem_atual']; // Manter a imagem atual por padrão
    
    if (isset($_FILES['imagem']) && $_FILES['imagem']['size'] > 0) {
        $target_dir = "../img/";
        
        // Verificar se o diretório existe, se não, criar
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES["imagem"]["name"], PATHINFO_EXTENSION);
        $new_filename = "produto_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Verificar se é uma imagem válida
        $check = getimagesize($_FILES["imagem"]["tmp_name"]);
        if ($check !== false) {
            // Tentar fazer upload da imagem
            if (move_uploaded_file($_FILES["imagem"]["tmp_name"], $target_file)) {
                $imagem = "/PAPSite/img/" . $new_filename;
            } else {
                $mensagem = '<div class="alert alert-warning">Erro ao fazer upload da imagem. Os outros dados foram atualizados.</div>';
            }
        } else {
            $mensagem = '<div class="alert alert-warning">O arquivo enviado não é uma imagem válida. Os outros dados foram atualizados.</div>';
        }
    }
    
    // Atualizar o produto no banco de dados
    $update_query = "UPDATE produtos SET nome = ?, descricao = ?, preco = ?, imagem = ?, categoria = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssdssi", $nome, $descricao, $preco, $imagem, $categoria, $produto_id);
    
    if ($stmt->execute()) {
        $mensagem = '<div class="alert alert-success">Produto atualizado com sucesso!</div>';
    } else {
        $mensagem = '<div class="alert alert-danger">Erro ao atualizar produto: ' . $conn->error . '</div>';
    }
}

// Processar adição de novo produto
if (isset($_POST['add_produto'])) {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = str_replace(',', '.', $_POST['preco']); // Converter vírgula para ponto
    $categoria = $_POST['categoria'];
    
    // Processar imagem
    $imagem = "/PAPSite/img/Bolapao.png"; // Imagem padrão
    
    if (isset($_FILES['imagem']) && $_FILES['imagem']['size'] > 0) {
        $target_dir = "../img/";
        
        // Verificar se o diretório existe, se não, criar
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES["imagem"]["name"], PATHINFO_EXTENSION);
        $new_filename = "produto_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Verificar se é uma imagem válida
        $check = getimagesize($_FILES["imagem"]["tmp_name"]);
        if ($check !== false) {
            // Tentar fazer upload da imagem
            if (move_uploaded_file($_FILES["imagem"]["tmp_name"], $target_file)) {
                $imagem = "/PAPSite/img/" . $new_filename;
            } else {
                $mensagem = '<div class="alert alert-warning">Erro ao fazer upload da imagem. O produto será adicionado com a imagem padrão.</div>';
            }
        } else {
            $mensagem = '<div class="alert alert-warning">O arquivo enviado não é uma imagem válida. O produto será adicionado com a imagem padrão.</div>';
        }
    }
    
    // Inserir o novo produto
    $insert_query = "INSERT INTO produtos (nome, descricao, preco, imagem, categoria) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssdss", $nome, $descricao, $preco, $imagem, $categoria);
    
    if ($stmt->execute()) {
        $mensagem = '<div class="alert alert-success">Produto adicionado com sucesso!</div>';
    } else {
        $mensagem = '<div class="alert alert-danger">Erro ao adicionar produto: ' . $conn->error . '</div>';
    }
}

// Processar pesquisa e filtro
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
    $categoria_filtro = $_GET['categoria'];
}

// Construir a consulta SQL com base nos filtros
$sql = "SELECT * FROM produtos WHERE 1=1";

if (!empty($search)) {
    $search_term = "%$search%";
    $sql .= " AND (nome LIKE ? OR descricao LIKE ?)";
}

if (!empty($categoria_filtro)) {
    $sql .= " AND categoria = ?";
}

$sql .= " ORDER BY categoria, nome";

// Preparar e executar a consulta
$stmt = $conn->prepare($sql);

if (!empty($search) && !empty($categoria_filtro)) {
    $search_term = "%$search%";
    $stmt->bind_param("sss", $search_term, $search_term, $categoria_filtro);
} elseif (!empty($search)) {
    $search_term = "%$search%";
    $stmt->bind_param("ss", $search_term, $search_term);
} elseif (!empty($categoria_filtro)) {
    $stmt->bind_param("s", $categoria_filtro);
}

$stmt->execute();
$result = $stmt->get_result();
$produtos = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Produtos - Painel Administrativo</title>
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
            width: 80px;
            height: 80px;
            object-fit: cover;
        }
        .categoria-badge {
            font-size: 0.8rem;
        }
        .modal-img-preview {
            max-width: 100%;
            max-height: 200px;
            display: block;
            margin: 10px auto;
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
                <a href="registrar.php"><i class="fas fa-user-plus mr-2"></i> Registrar Pessoas</a>
                <a href="produtosg.php" class="active"><i class="fas fa-box mr-2"></i> Gerenciar Produtos</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt mr-2"></i> Sair</a>
            </div>
            
            <!-- Conteúdo principal -->
            <div class="col-md-10 content">
                <h2 class="mb-4">Gerenciar Produtos</h2>
                
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
                                            <option value="<?php echo $cat; ?>" <?php if ($categoria_filtro === $cat) echo 'selected'; ?>>
                                                <?php echo ucfirst($cat); ?>
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
                
                <!-- Lista de produtos -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Produtos</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($produtos) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Imagem</th>
                                            <th>Nome</th>
                                            <th>Descrição</th>
                                            <th>Preço</th>
                                            <th>Categoria</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($produtos as $produto): ?>
                                            <tr>
                                                <td><?php echo $produto['id']; ?></td>
                                                <td>
                                                    <img src="<?php echo $produto['imagem']; ?>" alt="<?php echo $produto['nome']; ?>" class="product-img">
                                                </td>
                                                <td><?php echo $produto['nome']; ?></td>
                                                <td><?php echo substr($produto['descricao'], 0, 50) . (strlen($produto['descricao']) > 50 ? '...' : ''); ?></td>
                                                <td>€<?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                                                <td>
                                                    <span class="badge badge-info categoria-badge">
                                                        <?php echo ucfirst($produto['categoria']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary" 
                                                            data-toggle="modal" 
                                                            data-target="#editProdutoModal" 
                                                            data-id="<?php echo $produto['id']; ?>"
                                                            data-nome="<?php echo $produto['nome']; ?>"
                                                            data-descricao="<?php echo $produto['descricao']; ?>"
                                                            data-preco="<?php echo $produto['preco']; ?>"
                                                            data-categoria="<?php echo $produto['categoria']; ?>"
                                                            data-imagem="<?php echo $produto['imagem']; ?>">
                                                        <i class="fas fa-edit"></i> Editar
                                                    </button>
                                                    
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            data-toggle="modal" 
                                                            data-target="#deleteProdutoModal"
                                                            data-id="<?php echo $produto['id']; ?>"
                                                            data-nome="<?php echo $produto['nome']; ?>">
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
                    <h5 class="modal-title" id="addProdutoModalLabel">Adicionar Novo Produto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="produtosg.php" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nome">Nome do Produto</label>
                                    <input type="text" class="form-control" id="nome" name="nome" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="preco">Preço (€)</label>
                                    <input type="text" class="form-control" id="preco" name="preco" required placeholder="0,00">
                                </div>
                                
                                <div class="form-group">
                                    <label for="categoria">Categoria</label>
                                    <select class="form-control" id="categoria" name="categoria" required>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?php echo $cat; ?>"><?php echo ucfirst($cat); ?></option>
                                        <?php endforeach; ?>
                                        <option value="nova">Nova Categoria...</option>
                                        </select>
                                </div>
                                
                                <div class="form-group" id="nova-categoria-group" style="display: none;">
                                    <label for="nova_categoria">Nova Categoria</label>
                                    <input type="text" class="form-control" id="nova_categoria" name="nova_categoria">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="descricao">Descrição</label>
                                    <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="imagem">Imagem do Produto</label>
                                    <input type="file" class="form-control-file" id="imagem" name="imagem" accept="image/*">
                                    <small class="form-text text-muted">Selecione uma imagem para o produto. Se nenhuma imagem for selecionada, será usada uma imagem padrão.</small>
                                    <div id="preview-container" class="mt-2" style="display: none;">
                                        <img id="preview-image" class="modal-img-preview" src="" alt="Preview">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" name="add_produto" class="btn btn-success">Adicionar Produto</button>
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
                    <h5 class="modal-title" id="editProdutoModalLabel">Editar Produto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="produtosg.php" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="edit_produto_id" name="produto_id">
                        <input type="hidden" id="edit_imagem_atual" name="imagem_atual">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_nome">Nome do Produto</label>
                                    <input type="text" class="form-control" id="edit_nome" name="nome" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_preco">Preço (€)</label>
                                    <input type="text" class="form-control" id="edit_preco" name="preco" required placeholder="0,00">
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_categoria">Categoria</label>
                                    <select class="form-control" id="edit_categoria" name="categoria" required>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?php echo $cat; ?>"><?php echo ucfirst($cat); ?></option>
                                        <?php endforeach; ?>
                                        <option value="nova">Nova Categoria...</option>
                                    </select>
                                </div>
                                
                                <div class="form-group" id="edit-nova-categoria-group" style="display: none;">
                                    <label for="edit_nova_categoria">Nova Categoria</label>
                                    <input type="text" class="form-control" id="edit_nova_categoria" name="nova_categoria">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_descricao">Descrição</label>
                                    <textarea class="form-control" id="edit_descricao" name="descricao" rows="3"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_imagem">Imagem do Produto</label>
                                    <input type="file" class="form-control-file" id="edit_imagem" name="imagem" accept="image/*">
                                    <small class="form-text text-muted">Selecione uma nova imagem apenas se desejar substituir a atual.</small>
                                    <div id="edit-preview-container" class="mt-2">
                                        <img id="edit-preview-image" class="modal-img-preview" src="" alt="Preview">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" name="update_produto" class="btn btn-primary">Salvar Alterações</button>
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
                    <h5 class="modal-title" id="deleteProdutoModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o produto <strong id="delete-produto-nome"></strong>?</p>
                    <p class="text-danger">Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <form method="post" action="produtosg.php">
                        <input type="hidden" id="delete_produto_id" name="produto_id">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" name="delete_produto" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Preview da imagem ao adicionar produto
        document.getElementById('imagem').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('preview-image').src = event.target.result;
                    document.getElementById('preview-container').style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Preview da imagem ao editar produto
        document.getElementById('edit_imagem').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('edit-preview-image').src = event.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Mostrar/esconder campo de nova categoria
        document.getElementById('categoria').addEventListener('change', function() {
            const novaCategoria = document.getElementById('nova-categoria-group');
            if (this.value === 'nova') {
                novaCategoria.style.display = 'block';
                document.getElementById('nova_categoria').setAttribute('required', 'required');
            } else {
                novaCategoria.style.display = 'none';
                document.getElementById('nova_categoria').removeAttribute('required');
            }
        });
        
        document.getElementById('edit_categoria').addEventListener('change', function() {
            const novaCategoria = document.getElementById('edit-nova-categoria-group');
            if (this.value === 'nova') {
                novaCategoria.style.display = 'block';
                document.getElementById('edit_nova_categoria').setAttribute('required', 'required');
            } else {
                novaCategoria.style.display = 'none';
                document.getElementById('edit_nova_categoria').removeAttribute('required');
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
            
            // Verificar se a categoria existe no select
            const categoriaExiste = Array.from(modal.find('#edit_categoria option')).some(option => option.value === categoria);
            if (!categoriaExiste && categoria) {
                // Adicionar a categoria ao select
                const option = new Option(categoria.charAt(0).toUpperCase() + categoria.slice(1), categoria);
                modal.find('#edit_categoria').append(option);
                modal.find('#edit_categoria').val(categoria);
            }
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
        }
        
        document.getElementById('preco').addEventListener('input', function() {
            validarPreco(this);
        });
        
        document.getElementById('edit_preco').addEventListener('input', function() {
            validarPreco(this);
        });
    </script>
</body>
</html>
