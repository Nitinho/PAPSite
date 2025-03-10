<?php
require_once 'db_connect.php'; // Arquivo de conexão com o banco

// Verificar se o usuário está logado
if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não está logado']);
    exit;
}

// Receber dados do carrinho
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data)) {
    echo json_encode(['success' => false, 'message' => 'Nenhum dado recebido']);
    exit;
}

// Conectar ao banco de dados
$conn = new mysqli('localhost', 'root', '', 'lopesarmazem');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Falha na conexão com o banco de dados']);
    exit;
}

// Inserir o pedido
$userEmail = $_SESSION['email'];
$orderDate = date('Y-m-d H:i:s');

$stmt = $conn->prepare("INSERT INTO compras (usuario_id, data_compra, valor_compra) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $userId, $orderDate, $totalPrice);

// Obter o ID do usuário
$stmtGetUser = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmtGetUser->bind_param("s", $userEmail);
$stmtGetUser->execute();
$result = $stmtGetUser->get_result();
$row = $result->fetch_assoc();
$userId = $row['id'];

// Calcular o preço total
$totalPrice = 0;
foreach ($data as $item) {
    $totalPrice += $item['quantity'] * $item['price'];
}

$stmt->execute();
$orderId = $stmt->insert_id;

// Inserir os itens do pedido
$stmt = $conn->prepare("INSERT INTO itens_compra (compra_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
foreach ($data as $item) {
    $stmt->bind_param("iiid", $orderId, $item['id'], $item['quantity'], $item['price']);
    $stmt->execute();
}

echo json_encode(['success' => true, 'message' => 'Pedido salvo com sucesso']);

$conn->close();
?>
