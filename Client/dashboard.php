<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['email'])) {
  header("Location: ../Login/login.php");
  exit();
}

// Conexão ao banco de dados
$conn = new mysqli('localhost', 'root', '', 'lopesarmazem');

// Verificar conexão
if ($conn->connect_error) {
  die("Falha na conexão: " . $conn->connect_error);
}

// Obter informações do usuário logado
$email = $_SESSION['email'];
$sql = "SELECT id, nome, email, nome_da_empresa, data_registro, telefone FROM usuarios WHERE email = ?";
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
  die("Usuário não encontrado.");
}

$usuario_id = $user['id']; // Obter o ID do usuário
$nomeUsuario = $user['nome'] ?? "Usuário";
$emailUsuario = $user['email'] ?? $email;
$nomeDaEmpresa = $user['nome_da_empresa'] ?? "Não informado";
$dataRegistro = $user['data_registro'] ?? null;
$telefone = $user['telefone'] ?? "Não informado";

// Formatar a data de registro
$dataFormatada = $dataRegistro ? date("d/m/Y", strtotime($dataRegistro)) : "Data não disponível";

// Função para obter o histórico de compras do usuário
function getHistoricoCompras($conn, $usuario_id)
{
  $stmt = $conn->prepare("SELECT c.id, c.data_compra, c.valor_compra, c.status, c.pontos_ganhos 
                           FROM compras c 
                           WHERE c.usuario_id = ? 
                           ORDER BY c.data_compra DESC");
  $stmt->bind_param("i", $usuario_id);
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
  $stmt = $conn->prepare("UPDATE compras SET status = ? WHERE id = ?");
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
        addTestPurchase($conn, $usuario_id);
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
function getTotalCompras($conn, $usuario_id)
{
  $stmt = $conn->prepare("SELECT COUNT(*) FROM compras WHERE usuario_id = ?");
  $stmt->bind_param("i", $usuario_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_row();
  return $row[0] ?? 0; // Retorna o número total de compras
}

function getTotalGasto($conn, $usuario_id)
{
  $stmt = $conn->prepare("SELECT SUM(valor_compra) FROM compras WHERE usuario_id = ?");
  $stmt->bind_param("i", $usuario_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_row();
  return $row[0] ?? 0; // Retorna o total gasto ou 0 se não houver compras
}

function getTotalPontos($conn, $usuario_id)
{
  $stmt = $conn->prepare("SELECT SUM(pontos_ganhos) FROM compras WHERE usuario_id = ?");
  $stmt->bind_param("i", $usuario_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_row();
  return $row[0] ?? 0; // Retorna o total de pontos ou 0 se não houver compras
}

// Obter informações de compras
$totalCompras = getTotalCompras($conn, $usuario_id);
$totalGasto = getTotalGasto($conn, $usuario_id);
$totalPontos = getTotalPontos($conn, $usuario_id);

// Função para registrar a compra
function registrarCompra($conn, $usuario_id, $valor_compra, $pontos_ganhos)
{
  $stmt = $conn->prepare("INSERT INTO compras (usuario_id, valor_compra, pontos_ganhos) VALUES (?, ?, ?)");
  $stmt->bind_param("idd", $usuario_id, $valor_compra, $pontos_ganhos);
  $stmt->execute();
}

// Função para adicionar a compra de teste
function addTestPurchase($conn, $usuario_id)
{
  // Valores da compra de teste
  $valor_compra = 2500.00;
  $pontos_ganhos = floor($valor_compra / 10); // 1 ponto por cada 10 euros

  // Inserir a compra de teste no banco de dados
  registrarCompra($conn, $usuario_id, $valor_compra, $pontos_ganhos);

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

  $stmt = $conn->prepare("SELECT senha FROM usuarios WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  if (!password_verify($current_password, $user['senha'])) {
    echo json_encode(['status' => 'error', 'message' => 'Senha atual incorreta.']);
    return;
  }

  $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
  $update_stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
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

  $stmt = $conn->prepare("SELECT senha FROM usuarios WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  if (!password_verify($password, $user['senha'])) {
    echo json_encode(['status' => 'error', 'message' => 'Senha incorreta.']);
    return;
  }

  $update_stmt = $conn->prepare("UPDATE usuarios SET email = ? WHERE email = ?");
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

  $update_stmt = $conn->prepare("UPDATE usuarios SET nome = ? WHERE email = ?");
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

  // Obter o ID do usuário
  $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();
  $usuario_id = $user['id'];

  $insert_stmt = $conn->prepare("INSERT INTO enderecos (usuario_id, rua, numero, cidade, codigo_postal) VALUES (?, ?, ?, ?, ?)");
  $insert_stmt->bind_param("issss", $usuario_id, $rua, $numero, $cidade, $codigo_postal);

  if ($insert_stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Endereço adicionado com sucesso!']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao adicionar o endereço.']);
  }
}

function addPhone($conn, $email)
{
  $phone = $_POST['phone'];

  $update_stmt = $conn->prepare("UPDATE usuarios SET telefone = ? WHERE email = ?");
  $update_stmt->bind_param("ss", $phone, $email);

  if ($update_stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Telefone adicionado com sucesso!']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao adicionar o telefone.']);
  }
}

// Obter o histórico de compras do usuário
$historicoCompras = getHistoricoCompras($conn, $usuario_id);

// Fechar a conexão com o banco de dados
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Completo</title>
  <link rel="stylesheet" href="style/style.css">
</head>

<body>
  <header>
    <div id="headerimg">
      <a href="index.php"><img src="../img/logolopes.png" alt="Logo"></a>
    </div>
    <div id="headerselect">
      <a href="../index.php">INICIO</a>
      <a href="../index.php#container2">PRODUTOS</a>
      <a href="#">SOBRE</a>
      <a href="#">CONTATOS</a>
      <a href="../Login/logout.php">Sair</a>
    </div>
  </header>
  <main>
    <div class="dashboard">
      <div class="profile-section">
        <div class="profile-header">
          <div class="profile-avatar">
            <svg viewBox="0 0 24 24" class="avatar-icon">
              <path d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z" />
            </svg>
          </div>
          <div class="profile-info">
            <h2><?php echo htmlspecialchars($nomeUsuario); ?></h2>
            <p><?php echo htmlspecialchars($emailUsuario); ?></p>
            <p class="member-since">Nome da empresa: <?php echo htmlspecialchars($nomeDaEmpresa); ?></p>
            <p class="member-since">Membro desde: <?php echo htmlspecialchars($dataFormatada); ?></p>
            <p class="member-since">Telefone: <?php echo htmlspecialchars($telefone); ?></p>
          </div>
        </div>
      </div>

      <div class="dashboard-grid">
        <div class="stats-section">
          <!-- Exibir os dados no HTML -->
          <div class="stat-card">
            <h3>Total de Compras</h3>
            <p class="stat-number"><?php echo htmlspecialchars($totalCompras); ?></p>
          </div>
          <div class="stat-card">
            <h3>Total Gasto</h3>
            <p class="stat-number">€ <?php echo htmlspecialchars(number_format($totalGasto, 2, ',', '.')); ?></p>
          </div>
          <div class="stat-card">
            <h3>Total de Pontos</h3>
            <p class="stat-number"><?php echo htmlspecialchars($totalPontos); ?></p>
          </div>
        </div>

        <div class="section-title">
          <h2>Configurações</h2>
        </div>
        <div class="settings-grid">
          <div class="card" onclick="showModal('password')">
            <svg class="icon" viewBox="0 0 24 24">
              <path d="M12,17A2,2 0 0,0 14,15C14,13.89 13.1,13 12,13A2,2 0 0,0 10,15A2,2 0 0,0 12,17M18,8A2,2 0 0,1 20,10V20A2,2 0 0,1 18,22H6A2,2 0 0,1 4,20V10C4,8.89 4.9,8 6,8H7V6A5,5 0 0,1 12,1A5,5 0 0,1 17,6V8H18M12,3A3,3 0 0,0 9,6V8H15V6A3,3 0 0,0 12,3Z" />
            </svg>
            <h3>Mudar Senha</h3>
          </div>

          <div class="card" onclick="showModal('email')">
            <svg class="icon" viewBox="0 0 24 24">
              <path d="M20,8L12,13L4,8V6L12,11L20,6M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z" />
            </svg>
            <h3>Alterar Email</h3>
          </div>

          <div class="card" onclick="showModal('name')">
            <svg class="icon" viewBox="0 0 24 24">
              <path d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z" />
            </svg>
            <h3>Alterar Nome</h3>
          </div>

          <div class="card" onclick="showModal('address')">
            <svg class="icon" viewBox="0 0 24 24">
              <path d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z" />
            </svg>
            <h3>Adicionar Morada</h3>
          </div>

          <div class="card" onclick="showModal('phone')">
            <svg class="icon" viewBox="0 0 24 24">
              <path d="M6.62,10.79C8.06,13.62 10.38,15.94 13.21,17.38L15.41,15.18C15.69,14.9 16.08,14.82 16.43,14.93C17.55,15.3 18.75,15.5 20,15.5A1,1 0 0,1 21,16.5V20A1,1 0 0,1 20,21A17,17 0 0,1 3,4A1,1 0 0,1 4,3H7.5A1,1 0 0,1 8.5,4C8.5,5.25 8.7,6.45 9.07,7.57C9.18,7.92 9.1,8.31 8.82,8.59L6.62,10.79Z" />
            </svg>
            <h3>Telefone</h3>
          </div>
        </div>

        <div class="section-title">
          <h2>Histórico de Compras</h2>
        </div>
        <div class="purchases-section">
          <?php if (empty($historicoCompras)): ?>
            <div class="empty-purchases">
              <p>Você ainda não realizou nenhuma compra.</p>
            </div>
          <?php else: ?>
            <?php foreach ($historicoCompras as $compra): ?>
              <div class="purchase-card">
                <div class="purchase-icon">🛍️</div>
                <div class="purchase-details">
                  <h4>Compra #<?php echo htmlspecialchars($compra['id']); ?></h4>
                  <p>€<?php echo htmlspecialchars(number_format($compra['valor_compra'], 2, ',', '.')); ?> - <?php echo date('d/m/Y', strtotime($compra['data_compra'])); ?></p>
                  <p>Pontos ganhos: <?php echo htmlspecialchars($compra['pontos_ganhos']); ?></p>
                  <button class="view-details-btn" onclick="showOrderDetails(<?php echo $compra['id']; ?>)">Ver Detalhes</button>
                </div>
                <?php
                $statusClass = '';
                $statusText = '';

                switch ($compra['status']) {
                  case 'pendente':
                    $statusClass = 'pending';
                    $statusText = 'Pendente';
                    break;
                  case 'enviado':
                    $statusClass = 'processing';
                    $statusText = 'Enviado';
                    break;
                  case 'recebido':
                    $statusClass = 'delivered';
                    $statusText = 'Recebido';
                    break;
                }
                ?>
                <div class="purchase-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Modal para configurações -->
    <div id="modal" class="modal">
      <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modal-title"></h2>
        <form id="modal-form">
          <div id="modal-fields"></div>
          <button type="submit">Salvar</button>
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
    <!-- Adicione o conteúdo do footer aqui -->
  </footer>

  <script>
    function showModal(type) {
      const modal = document.getElementById('modal');
      const modalTitle = document.getElementById('modal-title');
      const modalFields = document.getElementById('modal-fields');
      const modalForm = document.getElementById('modal-form');

      // Clear previous fields
      modalFields.innerHTML = '';

      // Configure modal based on type
      switch (type) {
        case 'password':
          modalTitle.textContent = 'Mudar Senha';
          modalFields.innerHTML = `
                    <input type="password" name="current_password" placeholder="Senha atual" required>
                    <input type="password" name="new_password" placeholder="Nova senha" required>
                    <input type="password" name="confirm_password" placeholder="Confirmar nova senha" required>
                    <input type="hidden" name="action" value="change_password">
                `;
          break;
        case 'email':
          modalTitle.textContent = 'Alterar Email';
          modalFields.innerHTML = `
                    <input type="email" name="new_email" placeholder="Novo email" required>
                    <input type="password" name="password" placeholder="Confirmar senha" required>
                    <input type="hidden" name="action" value="change_email">
                `;
          break;
        case 'name':
          modalTitle.textContent = 'Alterar Nome';
          modalFields.innerHTML = `
                    <input type="text" name="new_name" placeholder="Novo nome" required>
                    <input type="hidden" name="action" value="change_name">
                `;
          break;
        case 'address':
          modalTitle.textContent = 'Adicionar Morada';
          modalFields.innerHTML = `
                    <input type="text" name="rua" placeholder="Rua" required>
                    <input type="text" name="numero" placeholder="Número" required>
                    <input type="text" name="cidade" placeholder="Cidade" required>
                    <input type="text" name="codigo_postal" placeholder="Código Postal" required>
                    <input type="hidden" name="action" value="add_address">
                `;
          break;
        case 'phone':
          modalTitle.textContent = 'Telefone';
          modalFields.innerHTML = `
                    <input type="tel" name="phone" placeholder="Novo número de telefone" required>
                    <input type="hidden" name="action" value="add_phone">
                `;
          break;
      }

      modal.style.display = 'block';

      modalForm.onsubmit = (e) => {
        e.preventDefault();
        const formData = new FormData(modalForm);
        fetch(window.location.href, {
            method: 'POST',
            body: formData
          })
          .then(response => {
            if (!response.ok) {
              throw new Error('Erro na resposta do servidor');
            }
            return response.json();
          })
          .then(data => {
            if (data.status === 'success') {
              alert(data.message);
              closeModal();
              location.reload();
            } else {
              throw new Error(data.message);
            }
          })
          .catch(error => {
            console.error('Erro:', error);
            alert('Erro: ' + error.message);
          });
      };
    }

    function closeModal() {
      const modal = document.getElementById('modal');
      modal.style.display = 'none';
    }

    // Função para mostrar os detalhes do pedido
    function showOrderDetails(orderId) {
  fetch(`get_order_details.php?order_id=${orderId}`)
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        alert(data.error);
        return;
      }

      const modalContent = document.getElementById('order-details-content');
      modalContent.style.maxHeight = '400px';
      modalContent.style.overflowY = 'auto';

      // Construir o HTML para os itens do pedido
      let html = '<div class="order-items">';

      data.items.forEach(item => {
        html += `
          <div class="order-item">
            <img src="${item.imagem}" alt="${item.nome}">
            <div class="order-item-details">
              <h4>${item.nome}</h4>
              <p>Quantidade: ${item.quantidade}</p>
              <p>Preço unitário: €${parseFloat(item.preco_unitario).toFixed(2)}</p>
              <p>Subtotal: €${parseFloat(item.subtotal).toFixed(2)}</p>
            </div>
          </div>
        `;
      });

      html += '</div>';

      // Adicionar o resumo do pedido
      html += `
        <div class="order-summary">
          <p>Total: €${parseFloat(data.order.valor_compra).toFixed(2)}</p>
          <p>Status: ${data.order.status}</p>
          <p>Data: ${new Date(data.order.data_compra).toLocaleDateString()}</p>
          <button class="invoice-btn" onclick="generateInvoice(${orderId})">Fatura</button>
        </div>
      `;

      modalContent.innerHTML = html;

      // Mostrar o modal
      document.getElementById('order-details-modal').style.display = 'block';
    })
    .catch(error => {
      console.error('Erro ao buscar detalhes do pedido:', error);
      alert('Ocorreu um erro ao buscar os detalhes do pedido.');
    });
}

function generateInvoice(orderId) {
  window.location.href = `generate_invoice.php?order_id=${orderId}`;
}

    // Função para fechar o modal de detalhes do pedido
    function closeOrderDetailsModal() {
      document.getElementById('order-details-modal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = (event) => {
      const modal = document.getElementById('modal');
      const orderDetailsModal = document.getElementById('order-details-modal');
      if (event.target === modal) {
        closeModal();
      }
      if (event.target === orderDetailsModal) {
        closeOrderDetailsModal();
      }
    }

    // Animate stats on page load
    document.addEventListener('DOMContentLoaded', () => {
      const statNumbers = document.querySelectorAll('.stat-number');
      statNumbers.forEach(stat => {
        const finalValue = stat.textContent;
        stat.textContent = '0';
        setTimeout(() => {
          animateNumber(stat, finalValue);
        }, 500);
      });
    });

    function animateNumber(element, finalValue) {
      const value = parseFloat(finalValue.replace(/[^0-9.-]+/g, ""));
      const prefix = finalValue.replace(/[0-9.-]+/g, "");
      let currentValue = 0;
      const duration = 1000;
      const steps = 20;
      const increment = value / steps;

      const interval = setInterval(() => {
        currentValue += increment;
        if (currentValue >= value) {
          element.textContent = finalValue;
          clearInterval(interval);
        } else {
          element.textContent = prefix + Math.floor(currentValue);
        }
      }, duration / steps);
    }
  </script>
</body>

</html>