<?php
session_start();

// Verificar se o Utilizador está logado
if (!isset($_SESSION['email'])) {
  header('Content-Type: application/json');
  echo json_encode(['status' => 'error', 'message' => 'Utilizador não autenticado.']);
  exit();
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'redeem_points') {
  header('Content-Type: application/json');
  echo json_encode(['status' => 'error', 'message' => 'Requisição inválida.']);
  exit();
}

// Conexão ao base de dados
$conn = new mysqli('localhost', 'root', '', 'lopesarmazem');

// Verificar conexão
if ($conn->connect_error) {
  header('Content-Type: application/json');
  echo json_encode(['status' => 'error', 'message' => 'Falha na conexão com o base de dados.']);
  exit();
}

// Obter informações do Utilizador logado
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT id, nome FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
  header('Content-Type: application/json');
  echo json_encode(['status' => 'error', 'message' => 'Utilizador não encontrado.']);
  exit();
}

$user_id = $user['id'];
$users_nome = $user['nome'];

// Obter os pontos atuais do Utilizador
$stmt = $conn->prepare("SELECT SUM(pontos_ganhos) as total_pontos FROM compras WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalPontos = $row['total_pontos'] ?? 0;

// Obter os pontos já gastos pelo Utilizador
$stmt = $conn->prepare("SELECT SUM(pontos_utilizados) as pontos_gastos FROM resgates_pontos WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$pontosGastos = $row['pontos_gastos'] ?? 0;

// Calcular pontos disponíveis
$pontosDisponiveis = $totalPontos - $pontosGastos;

// Obter os dados do resgate
$pontos = $_POST['points'];
$recompensa = $_POST['reward'];

// Verificar se o Utilizador tem pontos suficientes
if ($pontosDisponiveis < $pontos) {
  header('Content-Type: application/json');
  echo json_encode(['status' => 'error', 'message' => 'Pontos insuficientes para este resgate.']);
  exit();
}

// Inserir o resgate no base de dados
$stmt = $conn->prepare("INSERT INTO resgates_pontos (user_id, recompensa, pontos_utilizados, data_resgate, status) VALUES (?, ?, ?, NOW(), 'pendente')");
$stmt->bind_param("isi", $user_id, $recompensa, $pontos);

if ($stmt->execute()) {
  // Resgate realizado com sucesso
  header('Content-Type: application/json');
  echo json_encode(['status' => 'success', 'message' => 'Resgate realizado com sucesso!']);
} else {
  // Erro ao realizar o resgate
  header('Content-Type: application/json');
  echo json_encode(['status' => 'error', 'message' => 'Erro ao registrar o resgate: ' . $conn->error]);
}

// Função para obter o histórico de resgates
function getRedemptionHistory($conn, $user_id) {
  $stmt = $conn->prepare("SELECT id, recompensa, pontos_utilizados, data_resgate, status FROM resgates_pontos WHERE user_id = ? ORDER BY data_resgate DESC");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  $resgates = [];
  while ($row = $result->fetch_assoc()) {
    $resgates[] = $row;
  }
  
  return $resgates;
}

$conn->close();
?>
