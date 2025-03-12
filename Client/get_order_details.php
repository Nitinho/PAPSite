<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['email'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit();
}

// Verificar se o ID do pedido foi fornecido
if (!isset($_GET['order_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID do pedido não fornecido']);
    exit();
}

$order_id = intval($_GET['order_id']);

// Conexão ao banco de dados
$conn = new mysqli('localhost', 'root', '', 'lopesarmazem');

// Verificar conexão
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Falha na conexão com o banco de dados']);
    exit();
}

// Obter informações do usuário logado
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Usuário não encontrado']);
    exit();
}

$usuario_id = $user['id'];

// Verificar se o pedido pertence ao usuário
$stmt = $conn->prepare("SELECT * FROM compras WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $order_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Pedido não encontrado ou não pertence ao usuário']);
    exit();
}

// Obter os itens do pedido
$stmt = $conn->prepare("SELECT ic.quantidade, ic.preco_unitario, ic.subtotal, p.nome, p.imagem 
                       FROM itens_compra ic 
                       JOIN produtos p ON ic.produto_id = p.id 
                       WHERE ic.compra_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

// Fechar a conexão
$stmt->close();
$conn->close();

// Retornar os dados como JSON
header('Content-Type: application/json');
echo json_encode([
    'order' => $order,
    'items' => $items
]);
exit();
?>
