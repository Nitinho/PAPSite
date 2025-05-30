<?php
session_start();

// Verificar se o Utilizador está logado
if (!isset($_SESSION['email'])) {
  header('Content-Type: application/json');
  echo json_encode(['status' => 'error', 'message' => 'Utilizador não autenticado']);
  exit();
}

// Verificar se é uma requisição POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
  header('Content-Type: application/json');
  echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
  exit();
}

// Verificar se a ação é de cancelamento
if (!isset($_POST['action']) || $_POST['action'] != 'cancel_order' || !isset($_POST['order_id'])) {
  header('Content-Type: application/json');
  echo json_encode(['status' => 'error', 'message' => 'Parâmetros inválidos']);
  exit();
}

// Conexão ao base de dados
$conn = new mysqli('localhost', 'root', '', 'lopesarmazem');

// Verificar conexão
if ($conn->connect_error) {
  header('Content-Type: application/json');
  echo json_encode(['status' => 'error', 'message' => 'Falha na conexão com o base de dados']);
  exit();
}

// Obter o ID do Utilizador logado
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'];

// Obter o ID do pedido
$order_id = $_POST['order_id'];

// Verificar se o pedido pertence ao Utilizador e está pendente
$stmt = $conn->prepare("SELECT id, status FROM compras WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  header('Content-Type: application/json');
  echo json_encode(['status' => 'error', 'message' => 'Pedido não encontrado ou não pertence ao Utilizador']);
  exit();
}

$order = $result->fetch_assoc();

if ($order['status'] !== 'pendente') {
  header('Content-Type: application/json');
  echo json_encode(['status' => 'error', 'message' => 'Apenas pedidos pendentes podem ser cancelados']);
  exit();
}

// Atualizar o status do pedido para cancelado
$stmt = $conn->prepare("UPDATE compras SET status = 'cancelado', data_cancelamento = NOW() WHERE id = ?");
$stmt->bind_param("i", $order_id);

if ($stmt->execute()) {
  header('Content-Type: application/json');
  echo json_encode(['status' => 'success', 'message' => 'Pedido cancelado com sucesso']);
} else {
  header('Content-Type: application/json');
  echo json_encode(['status' => 'error', 'message' => 'Erro ao cancelar o pedido']);
}

$stmt->close();
$conn->close();
?>
