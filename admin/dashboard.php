<?php
// Iniciar a sessão administrativa
session_name('admin_session');
session_start();

// Incluir arquivo de configuração
require_once 'config.php';

// Verificar login administrativo
verificarLoginAdmin();

// Inicializar variáveis para evitar erros
$total_usuarios = 0;
$total_enderecos = 0;
$ultimo_registro = "N/A";

// Consulta para total de usuários com tratamento de erro
$sql_usuarios = "SELECT COUNT(*) as total FROM usuarios";
$result_usuarios = $conn->query($sql_usuarios);
if ($result_usuarios === false) {
    // Registrar erro
    error_log("Erro na consulta de usuários: " . $conn->error);
} else {
    $row = $result_usuarios->fetch_assoc();
    $total_usuarios = $row['total'];
}

// Consulta para total de endereços com tratamento de erro
$sql_enderecos = "SELECT COUNT(*) as total FROM enderecos";
$result_enderecos = $conn->query($sql_enderecos);
if ($result_enderecos === false) {
    // Registrar erro
    error_log("Erro na consulta de endereços: " . $conn->error);
} else {
    $row = $result_enderecos->fetch_assoc();
    $total_enderecos = $row['total'];
}

// Consulta para último registro com tratamento de erro
$sql_ultimo = "SELECT data_registro FROM usuarios ORDER BY data_registro DESC LIMIT 1";
$result_ultimo = $conn->query($sql_ultimo);
if ($result_ultimo === false) {
    // Registrar erro
    error_log("Erro na consulta do último registro: " . $conn->error);
} else {
    if ($result_ultimo->num_rows > 0) {
        $row = $result_ultimo->fetch_assoc();
        $ultimo_registro = date('d/m/Y', strtotime($row['data_registro']));
    }
}

// Consulta para últimos usuários registrados com tratamento de erro
$ultimos_usuarios = [];
$sql_ultimos = "SELECT id, nome, email, nif, data_registro FROM usuarios ORDER BY data_registro DESC LIMIT 5";
$result_ultimos = $conn->query($sql_ultimos);
if ($result_ultimos === false) {
    // Registrar erro
    error_log("Erro na consulta dos últimos usuários: " . $conn->error);
} else {
    while ($row = $result_ultimos->fetch_assoc()) {
        $ultimos_usuarios[] = $row;
    }
}

// Consulta para total de produtos
$total_produtos = 0;
$sql_produtos = "SELECT COUNT(*) as total FROM produtos";
$result_produtos = $conn->query($sql_produtos);
if ($result_produtos === false) {
    // Registrar erro
    error_log("Erro na consulta de produtos: " . $conn->error);
} else {
    $row = $result_produtos->fetch_assoc();
    $total_produtos = $row['total'];
}

// Consulta para total de encomendas
$total_encomendas = 0;
$sql_encomendas = "SELECT COUNT(*) as total FROM compras";
$result_encomendas = $conn->query($sql_encomendas);
if ($result_encomendas === false) {
    // Registrar erro
    error_log("Erro na consulta de encomendas: " . $conn->error);
} else {
    $row = $result_encomendas->fetch_assoc();
    $total_encomendas = $row['total'];
}

// Consulta para valor total de vendas
$total_vendas = 0;
$sql_vendas = "SELECT SUM(valor_compra) AS total FROM compras";
$result_vendas = $conn->query($sql_vendas);
if ($result_vendas === false) {
    // Registrar erro
    error_log("Erro na consulta de vendas: " . $conn->error);
} else {
    $row = $result_vendas->fetch_assoc();
    $total_vendas = $row['total'] ?: 0;
}

// Consulta para status das encomendas
$status_encomendas = [];
$sql_status = "SELECT status, COUNT(*) as total FROM compras GROUP BY status";
$result_status = $conn->query($sql_status);
if ($result_status === false) {
    // Registrar erro
    error_log("Erro na consulta de status: " . $conn->error);
} else {
    while ($row = $result_status->fetch_assoc()) {
        $status_encomendas[$row['status']] = $row['total'];
    }
}

// Consulta para vendas mensais (últimos 6 meses)
$labels_meses = [];
$dados_vendas = [];

// Obter o ano e mês atual
$ano_atual = date('Y');
$mes_atual = date('n');

// Consulta SQL para obter vendas mensais
$sql_vendas_mensais = "SELECT 
                        DATE_FORMAT(data_compra, '%Y-%m') AS mes,
                        SUM(valor_compra) AS total
                      FROM compras
                      WHERE data_compra >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                      GROUP BY DATE_FORMAT(data_compra, '%Y-%m')
                      ORDER BY mes";

$result_vendas_mensais = $conn->query($sql_vendas_mensais);

if ($result_vendas_mensais === false) {
    // Registrar erro
    error_log("Erro na consulta de vendas mensais: " . $conn->error);
    
    // Criar dados fictícios para não quebrar o gráfico
    for ($i = 5; $i >= 0; $i--) {
        $month = $mes_atual - $i;
        $year = $ano_atual;
        
        if ($month <= 0) {
            $month += 12;
            $year--;
        }
        
        $date = new DateTime("$year-$month-01");
        $labels_meses[] = $date->format('M Y');
        $dados_vendas[] = 0;
    }
} else {
    // Se não houver resultados, criar array de meses vazios
    if ($result_vendas_mensais->num_rows == 0) {
        for ($i = 5; $i >= 0; $i--) {
            $month = $mes_atual - $i;
            $year = $ano_atual;
            
            if ($month <= 0) {
                $month += 12;
                $year--;
            }
            
            $date = new DateTime("$year-$month-01");
            $labels_meses[] = $date->format('M Y');
            $dados_vendas[] = 0;
        }
    } else {
        // Criar um array associativo com todos os últimos 6 meses
        $vendas_por_mes = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = $mes_atual - $i;
            $year = $ano_atual;
            
            if ($month <= 0) {
                $month += 12;
                $year--;
            }
            
            $month_padded = str_pad($month, 2, '0', STR_PAD_LEFT);
            $month_key = "$year-$month_padded";
            
            $date = new DateTime("$year-$month-01");
            $labels_meses[] = $date->format('M Y');
            $vendas_por_mes[$month_key] = 0;
        }
        
        // Preencher com os valores reais
        while ($row = $result_vendas_mensais->fetch_assoc()) {
            if (isset($vendas_por_mes[$row['mes']])) {
                $vendas_por_mes[$row['mes']] = (float)$row['total'];
            }
        }
        
        // Converter para o formato do gráfico
        foreach ($labels_meses as $index => $label) {
            $month_key = array_keys($vendas_por_mes)[$index];
            $dados_vendas[] = $vendas_por_mes[$month_key];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Painel Administrativo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        .card-dashboard {
            border-left: 4px solid var(--primary);
            transition: transform 0.2s;
        }
        
        .card-dashboard:hover {
            transform: translateY(-5px);
        }
        
        .card-dashboard.primary {
            border-left-color: var(--primary);
        }
        
        .card-dashboard.success {
            border-left-color: var(--success);
        }
        
        .card-dashboard.info {
            border-left-color: var(--info);
        }
        
        .card-dashboard.warning {
            border-left-color: var(--warning);
        }
        
        .content {
            padding: 20px;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 1rem;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .table thead th {
            background-color: #f8f9fc;
            color: var(--dark);
            font-weight: 700;
            border-top: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <h4 class="text-center text-white mb-4 py-3">Admin Panel</h4>
                <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="encomendas.php"><i class="fas fa-shopping-cart"></i> Encomendas</a>
                <a href="registrar.php"><i class="fas fa-user-plus"></i> Registrar Pessoas</a>
                <a href="produtosg.php"><i class="fas fa-box"></i> Gerenciar Produtos</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
            </div>
            
            <!-- Conteúdo principal -->
            <div class="col-md-10 content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard</h2>
                    <div>
                        <span class="text-muted">Hoje: <?php echo date('d/m/Y'); ?></span>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="card card-dashboard primary">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <h5 class="card-title text-primary">Utilizadores</h5>
                                        <h3><?php echo $total_usuarios; ?></h3>
                                    </div>
                                    <div class="col-4 text-end">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card card-dashboard success">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <h5 class="card-title text-success">Endereços</h5>
                                        <h3><?php echo $total_enderecos; ?></h3>
                                    </div>
                                    <div class="col-4 text-end">
                                        <i class="fas fa-map-marker-alt fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card card-dashboard info">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <h5 class="card-title text-info">Produtos</h5>
                                        <h3><?php echo $total_produtos; ?></h3>
                                    </div>
                                    <div class="col-4 text-end">
                                        <i class="fas fa-box fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card card-dashboard warning">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <h5 class="card-title text-warning">Encomendas</h5>
                                        <h3><?php echo $total_encomendas; ?></h3>
                                    </div>
                                    <div class="col-4 text-end">
                                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-area me-2"></i>Vendas Mensais</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="vendasChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-pie me-2"></i>Status das Encomendas</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="statusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-user me-2"></i>Últimos Usuários Registrados</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
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
                                    if (count($ultimos_usuarios) > 0) {
                                        foreach ($ultimos_usuarios as $usuario) {
                                            echo "<tr>";
                                            echo "<td>" . $usuario["id"] . "</td>";
                                            echo "<td>" . $usuario["nome"] . "</td>";
                                            echo "<td>" . $usuario["email"] . "</td>";
                                            echo "<td>" . $usuario["nif"] . "</td>";
                                            echo "<td>" . date('d/m/Y H:i', strtotime($usuario["data_registro"])) . "</td>";
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
                    <div class="card-footer">
                        <a href="registrar.php" class="btn btn-primary">Ver Todos os Usuários</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dados para o gráfico de vendas (reais do banco de dados)
        const meses = <?php echo json_encode($labels_meses); ?>;
        const vendas = <?php echo json_encode($dados_vendas); ?>;
        
        // Configuração do gráfico de vendas
        const ctxVendas = document.getElementById('vendasChart').getContext('2d');
        const vendasChart = new Chart(ctxVendas, {
            type: 'line',
            data: {
                labels: meses,
                datasets: [{
                    label: 'Vendas (€)',
                    data: vendas,
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '€' + value;
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Vendas: €' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
        
        // Dados para o gráfico de status
        const status = [
            <?php
                $statuses = ['pendente', 'enviado', 'recebido', 'cancelado'];
                foreach ($statuses as $status) {
                    echo isset($status_encomendas[$status]) ? $status_encomendas[$status] : 0;
                    echo ", ";
                }
            ?>
        ];
        
        // Configuração do gráfico de status
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Pendente', 'Enviado', 'Recebido', 'Cancelado'],
                datasets: [{
                    data: status,
                    backgroundColor: [
                        '#f6c23e', // warning
                        '#4e73df', // primary
                        '#1cc88a', // success
                        '#e74a3b'  // danger
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '70%'
            }
        });
    </script>
</body>
</html>
