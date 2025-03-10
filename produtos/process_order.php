<?php
session_start();

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não está logado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data)) {
    echo json_encode(['success' => false, 'message' => 'Nenhum dado recebido']);
    exit;
}

// Conexão com o banco de dados
$conn = new mysqli('localhost', 'root', '', 'seu_banco_de_dados');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Falha na conexão com o banco de dados']);
    exit;
}

// Inserir o pedido
$userEmail = $_SESSION['email'];
$orderDate = date('Y-m-d H:i:s');

$stmt = $conn->prepare("INSERT INTO orders (user_email, order_date) VALUES (?, ?)");
$stmt->bind_param("ss", $userEmail, $orderDate);

if ($stmt->execute()) {
    $orderId = $stmt->insert_id;

    // Inserir os itens do pedido
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, quantity) VALUES (?, ?, ?)");
    
    foreach ($data as $item) {
        $stmt->bind_param("isi", $orderId, $item['name'], $item['quantity']);
        $stmt->execute();
    }

    echo json_encode(['success' => true, 'message' => 'Pedido salvo com sucesso']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar o pedido']);
}

$conn->close();
?>