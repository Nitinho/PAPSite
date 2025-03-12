<?php
header('Content-Type: application/json');
session_start();
include('db_connect.php');


// Verificar se o usuário está logado
if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não está logado']);
    exit();
}

// Obter o ID do usuário logado
$email = $_SESSION['email'];
$query = "SELECT id FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
    exit();
}

$usuario = $result->fetch_assoc();
$usuario_id = $usuario['id'];

// Receber os dados do pedido
$input = file_get_contents('php://input');
$orderData = json_decode($input, true);

if (!$orderData || !isset($orderData['items']) || !isset($orderData['total'])) {
    echo json_encode(['success' => false, 'message' => 'Dados do pedido inválidos']);
    exit();
}

// Calcular pontos (exemplo: 1 ponto para cada R$ 10)
$pontos_ganhos = floor($orderData['total'] / 10);

// Iniciar transação
$conn->begin_transaction();

try {
    // Inserir na tabela compras
    $query = "INSERT INTO compras (usuario_id, valor_compra, pontos_ganhos) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("idi", $usuario_id, $orderData['total'], $pontos_ganhos);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao registrar a compra: " . $stmt->error);
    }
    
    $compra_id = $conn->insert_id;
    
    // Inserir os itens individuais do pedido
    foreach ($orderData['items'] as $item) {
        $produto_id = $item['id'];
        $quantidade = $item['quantity'];
        $preco_unitario = $item['price'];
        $subtotal = $preco_unitario * $quantidade;
        
        $query = "INSERT INTO itens_compra (compra_id, produto_id, quantidade, preco_unitario, subtotal) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiddd", $compra_id, $produto_id, $quantidade, $preco_unitario, $subtotal);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao registrar item do pedido: " . $stmt->error);
        }
    }
    
    // Confirmar a transação
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Pedido registrado com sucesso',
        'order_id' => $compra_id
    ]);
    
} catch (Exception $e) {
    // Reverter em caso de erro
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
