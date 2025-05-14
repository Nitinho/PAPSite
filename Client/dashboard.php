<?php
session_start();

// Verificar se o Utilizador está logado
if (!isset($_SESSION['email'])) {
  header("Location: ../Login/login.php");
  exit();
}

// Conexão ao base de dados
$conn = new mysqli('localhost', 'root', '', 'lopesarmazem');

// Verificar conexão
if ($conn->connect_error) {
  die("Falha na conexão: " . $conn->connect_error);
}

// Obter informações do Utilizador logado
$email = $_SESSION['email'];
$sql = "SELECT id, nome, email, nome_da_empresa, data_registro, telefone FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
  die("Erro na preparação da consulta: " . $conn->error);
}
$stmt->bind_param("s", $email);
if (!$stmt->execute()) {
  die("Erro na execução da consulta: " . $stmt->error);
}
$result = $stmt->get_result();
if ($result === false) {
  die("Erro ao obter resultado da consulta: " . $stmt->error);
}
$user = $result->fetch_assoc();
if ($user === null) {
  die("Utilizador não encontrado.");
}

$user_id = $user['id']; // Obter o ID do Utilizador
$nomeusers = $user['nome'] ?? "Utilizador";
$emailusers = $user['email'] ?? $email;
$nomeDaEmpresa = $user['nome_da_empresa'] ?? "Não informado";
$dataRegistro = $user['data_registro'] ?? null;
$telefone = $user['telefone'] ?? "Não informado";

// Formatar a data de registro
$dataFormatada = $dataRegistro ? date("d/m/Y", strtotime($dataRegistro)) : "Data não disponível";

// Função para obter o histórico de compras do Utilizador
function getHistoricoCompras($conn, $user_id)
{
  $stmt = $conn->prepare("SELECT c.id, c.data_compra, c.valor_compra, c.status, c.pontos_ganhos 
                           FROM compras c 
                           WHERE c.user_id = ? 
                           ORDER BY c.data_compra DESC");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  $compras = [];
  while ($row = $result->fetch_assoc()) {
    $compras[] = $row;
  }

  return $compras;
}

// Função para obter os itens de uma compra específica
function getItensCompra($conn, $compra_id)
{
  $stmt = $conn->prepare("SELECT ic.quantidade, ic.preco_unitario, ic.subtotal, p.nome, p.imagem 
                           FROM itens_compra ic 
                           JOIN produtos p ON ic.produto_id = p.id 
                           WHERE ic.compra_id = ?");
  $stmt->bind_param("i", $compra_id);
  $stmt->execute();
  $result = $stmt->get_result();

  $itens = [];
  while ($row = $result->fetch_assoc()) {
    $itens[] = $row;
  }

  return $itens;
}

// Função para atualizar o status de uma compra
function atualizarStatusCompra($conn, $compra_id, $novo_status)
{
  $data_campo = '';

  // Definir o campo de data com base no status
  if ($novo_status == 'recebido') {
    $data_campo = ', data_recebimento = NOW()';
  } else if ($novo_status == 'cancelado') {
    $data_campo = ', data_cancelamento = NOW()';
  }

  $query = "UPDATE compras SET status = ?" . $data_campo . " WHERE id = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("si", $novo_status, $compra_id);
  return $stmt->execute();
}


// Processar formulários de atualização
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['action'])) {
    //Definir o header como application/json antes de qualquer output
    header('Content-Type: application/json');
    switch ($_POST['action']) {
      case 'change_password':
        changePassword($conn, $email);
        break;
      case 'change_email':
        changeEmail($conn, $email);
        break;
      case 'change_name':
        changeName($conn, $email);
        break;
      case 'add_address':
        addAddress($conn, $email);
        break;
      case 'add_phone':
        addPhone($conn, $email);
        break;
      case 'add_test_purchase':
        addTestPurchase($conn, $user_id);
        break;
      case 'update_status':
        $compra_id = $_POST['compra_id'];
        $novo_status = $_POST['novo_status'];

        if (atualizarStatusCompra($conn, $compra_id, $novo_status)) {
          echo json_encode(['status' => 'success', 'message' => 'Status atualizado com sucesso!']);
        } else {
          echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar o status.']);
        }
        break;
    }
    exit(); // Importante: Terminar a execução após o processamento do AJAX
  }
}

// Funções para obter dados de compras
function getTotalCompras($conn, $user_id)
{
  $stmt = $conn->prepare("SELECT COUNT(*) FROM compras WHERE user_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_row();
  return $row[0] ?? 0; // Retorna o número total de compras
}

function getTotalGasto($conn, $user_id)
{
  $stmt = $conn->prepare("SELECT SUM(valor_compra) FROM compras WHERE user_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_row();
  return $row[0] ?? 0; // Retorna o total gasto ou 0 se não houver compras
}

function getTotalPontos($conn, $user_id)
{
  $stmt = $conn->prepare("SELECT SUM(pontos_ganhos) FROM compras WHERE user_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_row();
  return $row[0] ?? 0; // Retorna o total de pontos ou 0 se não houver compras
}

// Obter informações de compras
$totalCompras = getTotalCompras($conn, $user_id);
$totalGasto = getTotalGasto($conn, $user_id);
$totalPontos = getTotalPontos($conn, $user_id);

// Função para registrar a compra
function registrarCompra($conn, $user_id, $valor_compra, $pontos_ganhos)
{
  $stmt = $conn->prepare("INSERT INTO compras (user_id, valor_compra, pontos_ganhos) VALUES (?, ?, ?)");
  $stmt->bind_param("idd", $user_id, $valor_compra, $pontos_ganhos);
  $stmt->execute();
}

// Função para adicionar a compra de teste
function addTestPurchase($conn, $user_id)
{
  // Valores da compra de teste
  $valor_compra = 2500.00;
  $pontos_ganhos = floor($valor_compra / 10); // 1 ponto por cada 10 euros

  // Inserir a compra de teste no base de dados
  registrarCompra($conn, $user_id, $valor_compra, $pontos_ganhos);

  //Redireciona para o painel
  header("Location: dashboard.php");
  exit();
}

function changePassword($conn, $email)
{
  $current_password = $_POST['current_password'];
  $new_password = $_POST['new_password'];
  $confirm_password = $_POST['confirm_password'];

  if ($new_password !== $confirm_password) {
    echo json_encode(['status' => 'error', 'message' => 'As senhas não coincidem.']);
    return;
  }

  $stmt = $conn->prepare("SELECT senha FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  if (!password_verify($current_password, $user['senha'])) {
    echo json_encode(['status' => 'error', 'message' => 'Senha atual incorreta.']);
    return;
  }

  $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
  $update_stmt = $conn->prepare("UPDATE users SET senha = ? WHERE email = ?");
  $update_stmt->bind_param("ss", $hashed_password, $email);

  if ($update_stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Senha alterada com sucesso!']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao alterar a senha.']);
  }
}

function changeEmail($conn, $email)
{
  $new_email = $_POST['new_email'];
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT senha FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  if (!password_verify($password, $user['senha'])) {
    echo json_encode(['status' => 'error', 'message' => 'Senha incorreta.']);
    return;
  }

  $update_stmt = $conn->prepare("UPDATE users SET email = ? WHERE email = ?");
  $update_stmt->bind_param("ss", $new_email, $email);

  if ($update_stmt->execute()) {
    $_SESSION['email'] = $new_email;
    echo json_encode(['status' => 'success', 'message' => 'Email alterado com sucesso!']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao alterar o email.']);
  }
}

function changeName($conn, $email)
{
  $new_name = $_POST['new_name'];

  $update_stmt = $conn->prepare("UPDATE users SET nome = ? WHERE email = ?");
  $update_stmt->bind_param("ss", $new_name, $email);

  if ($update_stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Nome alterado com sucesso!']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao alterar o nome.']);
  }
}

function addAddress($conn, $email)
{
  $rua = $_POST['rua'];
  $numero = $_POST['numero'];
  $cidade = $_POST['cidade'];
  $codigo_postal = $_POST['codigo_postal'];

  // Obter o ID do Utilizador
  $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();
  $user_id = $user['id'];

  $insert_stmt = $conn->prepare("INSERT INTO enderecos (user_id, rua, numero, cidade, codigo_postal) VALUES (?, ?, ?, ?, ?)");
  $insert_stmt->bind_param("issss", $user_id, $rua, $numero, $cidade, $codigo_postal);

  if ($insert_stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Endereço adicionado com sucesso!']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao adicionar o endereço.']);
  }
}

function addPhone($conn, $email)
{
  $phone = $_POST['phone'];

  $update_stmt = $conn->prepare("UPDATE users SET telefone = ? WHERE email = ?");
  $update_stmt->bind_param("ss", $phone, $email);

  if ($update_stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Telefone adicionado com sucesso!']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao adicionar o telefone.']);
  }
}

// Obter o histórico de compras do Utilizador
$historicoCompras = getHistoricoCompras($conn, $user_id);



// Fechar a conexão com o base de dados
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-PT">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Área do Cliente | Armazéns Lopes</title>
  <link rel="stylesheet" href="style/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="shortcut icon" type="image/x-icon" href="../img/logolopes.ico">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
      <a href="../Login/logout.php">SAIR</a>
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
      <a href="../Login/logout.php">SAIR</a>
    </nav>
  </header>

  <main>
    <div class="dashboard-container">
      <div class="dashboard-sidebar">
        <div class="user-profile">
          <div class="profile-avatar">
            <i class="fas fa-user"></i>
          </div>
          <h3><?php echo htmlspecialchars($nomeusers); ?></h3>
          <p><?php echo htmlspecialchars($emailusers); ?></p>
        </div>

        <nav class="sidebar-nav">
          <ul>
            <li class="active"><a href="#overview"><i class="fas fa-home"></i> Visão Geral</a></li>
            <li><a href="#purchases"><i class="fas fa-shopping-cart"></i> Compras</a></li>
            <li><a href="#settings"><i class="fas fa-cog"></i> Configurações</a></li>
          </ul>
        </nav>

        <div class="sidebar-footer">
          <p>Membro desde: <?php echo htmlspecialchars($dataFormatada); ?></p>
        </div>
      </div>

      <div class="dashboard-content">
        <section id="overview" class="dashboard-section active">
          <div class="section-header">
            <h2><i class="fas fa-chart-line"></i> Visão Geral</h2>
            <p>Bem-vindo de volta, <?php echo htmlspecialchars($nomeusers); ?>!</p>
          </div>

          <div class="stats-cards">
            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-shopping-bag"></i>
              </div>
              <div class="stat-info">
                <h3>Total de Compras</h3>
                <p class="stat-number"><?php echo htmlspecialchars($totalCompras); ?></p>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-euro-sign"></i>
              </div>
              <div class="stat-info">
                <h3>Total Gasto</h3>
                <p class="stat-number">€ <?php echo htmlspecialchars(number_format($totalGasto, 2, ',', '.')); ?></p>
              </div>
            </div>


          </div>

          <div class="user-info-card">
            <h3>Informações da Conta</h3>
            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">Nome:</span>
                <span class="info-value"><?php echo htmlspecialchars($nomeusers); ?></span>
              </div>
              <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value"><?php echo htmlspecialchars($emailusers); ?></span>
              </div>
              <div class="info-item">
                <span class="info-label">Empresa:</span>
                <span class="info-value"><?php echo htmlspecialchars($nomeDaEmpresa); ?></span>
              </div>
              <div class="info-item">
                <span class="info-label">Telefone:</span>
                <span class="info-value"><?php echo htmlspecialchars($telefone); ?></span>
              </div>
            </div>
            <div class="recent-purchases">
              <div class="section-header-small">
                <h3>Compras Recentes</h3>
              </div>

              <div class="purchases-list">
                <?php if (empty($historicoCompras)): ?>
                  <div class="empty-state">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Você ainda não realizou nenhuma compra.</p>
                    <a href="../index.php#container2" class="btn-primary">Ir às Compras</a>
                  </div>
                <?php else: ?>
                  <?php
                  $recentPurchases = array_slice($historicoCompras, 0, 3); // Mostrar apenas as 3 mais recentes
                  foreach ($recentPurchases as $compra):
                  ?>
                    <div class="purchase-item">
                      <div class="purchase-icon">
                        <i class="fas fa-shopping-bag"></i>
                      </div>
                      <div class="purchase-details">
                        <h4>Compra #<?php echo htmlspecialchars($compra['id']); ?></h4>
                        <p class="purchase-date"><?php echo date('d/m/Y', strtotime($compra['data_compra'])); ?></p>
                        <p class="purchase-amount">€ <?php echo htmlspecialchars(number_format($compra['valor_compra'], 2, ',', '.')); ?></p>
                      </div>
                      <?php
                      $statusClass = '';
                      $statusText = '';

                      switch ($compra['status']) {
                        case 'pendente':
                          $statusClass = 'status-pending';
                          $statusText = 'Pendente';
                          break;
                        case 'enviado':
                          $statusClass = 'status-processing';
                          $statusText = 'Enviado';
                          break;
                        case 'recebido':
                          $statusClass = 'status-delivered';
                          $statusText = 'Recebido';
                          break;
                      }
                      ?>
                      <div class="purchase-status <?php echo $statusClass; ?>">
                        <?php echo $statusText; ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
        </section>

        <section id="purchases" class="dashboard-section">
          <div class="section-header">
            <h2><i class="fas fa-shopping-cart"></i> Histórico de Compras</h2>
            <p>Visualize todas as suas compras e acompanhe o status dos seus pedidos.</p>
          </div>

          <div class="purchases-filters">
            <div class="search-box">
              <input type="text" id="search-purchases" placeholder="Pesquisar compras...">
              <button><i class="fas fa-search"></i></button>
            </div>

            <div class="filter-box">
              <select id="filter-status">
                <option value="all">Todos os Status</option>
                <option value="pendente">Pendente</option>
                <option value="enviado">Enviado</option>
                <option value="recebido">Recebido</option>
                <option value="cancelado">Cancelado</option>
              </select>

            </div>
          </div>

          <div class="purchases-table-container">
            <?php if (empty($historicoCompras)): ?>
              <div class="empty-state">
                <i class="fas fa-shopping-cart"></i>
                <p>Você ainda não realizou nenhuma compra.</p>
                <a href="../index.php#container2" class="btn-primary">Ir às Compras</a>
              </div>
            <?php else: ?>
              <table class="purchases-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Ações</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($historicoCompras as $compra): ?>
                    <tr data-id="<?php echo $compra['id']; ?>" data-status="<?php echo $compra['status']; ?>">
                      <td>#<?php echo htmlspecialchars($compra['id']); ?></td>
                      <td><?php echo date('d/m/Y', strtotime($compra['data_compra'])); ?></td>
                      <td>€ <?php echo htmlspecialchars(number_format($compra['valor_compra'], 2, ',', '.')); ?></td>
                      <td>
                        <?php
                        $statusClass = '';
                        $statusText = '';
                        switch ($compra['status']) {
                          case 'pendente':
                            $statusClass = 'status-pending';
                            $statusText = 'Pendente';
                            break;
                          case 'enviado':
                            $statusClass = 'status-processing';
                            $statusText = 'Enviado';
                            break;
                          case 'recebido':
                            $statusClass = 'status-delivered';
                            $statusText = 'Recebido';
                            break;
                          case 'cancelado':
                            $statusClass = 'status-canceled';
                            $statusText = 'Cancelado';
                            break;
                        }
                        ?>
                        <span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                      </td>
                      <td>
                        <button class="btn-icon" onclick="showOrderDetails(<?php echo $compra['id']; ?>)">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-icon" onclick="generateInvoice(<?php echo $compra['id']; ?>)">
                          <i class="fas fa-file-invoice"></i>
                        </button>
                        <?php if ($compra['status'] == 'pendente'): ?>
                          <button class="btn-icon btn-cancel" onclick="window.cancelOrder(<?php echo $compra['id']; ?>)">
                            <i class="fas fa-times-circle"></i>
                          </button>

                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>

              </table>
            <?php endif; ?>
          </div>
        </section>


        <section id="settings" class="dashboard-section">
          <div class="section-header">
            <h2><i class="fas fa-cog"></i> Configurações da Conta</h2>
            <p>Atualize suas informações pessoais e preferências.</p>
          </div>

          <div class="settings-grid">
            <div class="settings-card" onclick="showModal('password')">
              <div class="settings-icon">
                <i class="fas fa-lock"></i>
              </div>
              <div class="settings-info">
                <h3>Alterar Senha</h3>
                <p>Atualize a sua senha para manter sua conta segura</p>
              </div>
              <div class="settings-action">
                <i class="fas fa-chevron-right"></i>
              </div>
            </div>

            <div class="settings-card" onclick="showModal('email')">
              <div class="settings-icon">
                <i class="fas fa-envelope"></i>
              </div>
              <div class="settings-info">
                <h3>Alterar Email</h3>
                <p>Atualize o seu endereço de email</p>
              </div>
              <div class="settings-action">
                <i class="fas fa-chevron-right"></i>
              </div>
            </div>

            <div class="settings-card" onclick="showModal('name')">
              <div class="settings-icon">
                <i class="fas fa-user"></i>
              </div>
              <div class="settings-info">
                <h3>Alterar Nome</h3>
                <p>Atualize o seu nome de Utilizador</p>
              </div>
              <div class="settings-action">
                <i class="fas fa-chevron-right"></i>
              </div>
            </div>

            <div class="settings-card" onclick="showModal('address')">
              <div class="settings-icon">
                <i class="fas fa-map-marker-alt"></i>
              </div>
              <div class="settings-info">
                <h3>Alterar Morada</h3>
                <p>Atualizeo seu endereço de entrega</p>
              </div>
              <div class="settings-action">
                <i class="fas fa-chevron-right"></i>
              </div>
            </div>

            <div class="settings-card" onclick="showModal('phone')">
              <div class="settings-icon">
                <i class="fas fa-phone"></i>
              </div>
              <div class="settings-info">
                <h3>Alterar Telefone</h3>
                <p>Atualize o seu número de telefone</p>
              </div>
              <div class="settings-action">
                <i class="fas fa-chevron-right"></i>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>

    <!-- Modal para configurações -->
    <div id="modal" class="modal">
      <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modal-title"></h2>
        <form id="modal-form">
          <div id="modal-fields"></div>
          <button type="submit" class="btn-primary">Salvar Alterações</button>
        </form>
      </div>
    </div>


    <!-- Modal para detalhes do pedido -->
    <div id="order-details-modal" class="modal">
      <div class="modal-content">
        <span class="close" onclick="closeOrderDetailsModal()">&times;</span>
        <h2>Detalhes do Pedido</h2>
        <div id="order-details-content"></div>
      </div>
    </div>
  </main>
  <footer>
    <div class="footer-content">
      <div class="footer-links"> <a href="../index.php">Início</a> <a href="../index.php#container2">Produtos</a> <a href="../index.php#sobre">Sobre</a> <a href="../index.php#container6">Contactos</a> </div>
      <div class="footer-social"> <a href="https://www.facebook.com/escolabasica.secundariaourem/?locale=pt_PT"><i class="fab fa-facebook"></i></a> <a href="https://www.instagram.com/aeourem/"><i class="fab fa-instagram"></i></a>
  </div>
    </div>
    <div class="footer-bottom">
      <p><strong>© 2024 ARMAZÉNS LOPES. TODOS OS DIREITOS RESERVADOS.</strong></p>
    </div>
  </footer>
  <script src="js/script.js"></script>
  <script>
        window.cancelOrder = function(orderId) {
      if (confirm('Tem certeza que deseja cancelar este pedido?')) {
        // Mostrar indicador de carregamento
        showNotification('Processando cancelamento...', 'info');
        
        fetch('cancel_order.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `order_id=${orderId}&action=cancel_order`
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            showNotification(data.message, 'success');
            setTimeout(() => {
              location.reload();
            }, 1500);
          } else {
            showNotification('Erro: ' + data.message, 'error');
          }
        })
        .catch(error => {
          console.error('Erro:', error);
          showNotification('Erro ao processar o cancelamento', 'error');
        });
      }
    };
  </script>
</body>

</html>