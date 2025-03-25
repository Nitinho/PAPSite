<?php

// Iniciar a sessão administrativa
session_name('admin_session');
session_start();

// Incluir arquivo de configuração
require_once 'config.php';

// Verificar login administrativo
verificarLoginAdmin();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Painel Administrativo</title>
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
        .card-dashboard {
            border-left: 4px solid #007bff;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <h4 class="text-center mb-4">Admin Panel</h4>
                <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a>
                <a href="encomendas.php"><i class="fas fa-shopping-cart mr-2"></i> Encomendas</a>
                <a href="registrar.php"><i class="fas fa-user-plus mr-2"></i> Registrar Pessoas</a>
                <a href="produtosg.php"><i class="fas fa-box mr-2"></i> Gerenciar Produtos</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt mr-2"></i> Sair</a>
            </div>
            
            <!-- Conteúdo principal -->
            <div class="col-md-10 content">
                <h2 class="mb-4">Dashboard</h2>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="card card-dashboard">
                            <div class="card-body">
                                <h5 class="card-title">Usuários</h5>
                                <p class="card-text">Total de usuários registrados</p>
                                <h3>
                                    <?php
                                    $sql = "SELECT COUNT(*) as total FROM usuarios";
                                    $result = $conn->query($sql);
                                    $row = $result->fetch_assoc();
                                    echo $row['total'];
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card card-dashboard">
                            <div class="card-body">
                                <h5 class="card-title">Endereços</h5>
                                <p class="card-text">Total de endereços cadastrados</p>
                                <h3>
                                    <?php
                                    $sql = "SELECT COUNT(*) as total FROM enderecos";
                                    $result = $conn->query($sql);
                                    $row = $result->fetch_assoc();
                                    echo $row['total'];
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card card-dashboard">
                            <div class="card-body">
                                <h5 class="card-title">Último Registro</h5>
                                <p class="card-text">Data do último usuário registrado</p>
                                <h3>
                                    <?php
                                    $sql = "SELECT data_registro FROM usuarios ORDER BY data_registro DESC LIMIT 1";
                                    $result = $conn->query($sql);
                                    if ($result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                        echo date('d/m/Y', strtotime($row['data_registro']));
                                    } else {
                                        echo "N/A";
                                    }
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Últimos Usuários Registrados</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>NIF</th>
                                    <th>Data de Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT id, nome, email, nif, data_registro FROM usuarios ORDER BY data_registro DESC LIMIT 5";
                                $result = $conn->query($sql);
                                
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $row["id"] . "</td>";
                                        echo "<td>" . $row["nome"] . "</td>";
                                        echo "<td>" . $row["email"] . "</td>";
                                        echo "<td>" . $row["nif"] . "</td>";
                                        echo "<td>" . date('d/m/Y H:i', strtotime($row["data_registro"])) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center'>Nenhum usuário registrado</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
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
