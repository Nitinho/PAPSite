<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['email'])) {
  header("Location: ../Login/login.php");
  exit();
}

// Incluir a biblioteca TCPDF
require_once('tcpdf/tcpdf.php');

// Conexão ao banco de dados
$conn = new mysqli('localhost', 'root', '', 'lopesarmazem');

// Verificar conexão
if ($conn->connect_error) {
  die("Falha na conexão: " . $conn->connect_error);
}

// Verificar se o ID da compra foi fornecido
if (!isset($_GET['order_id'])) {
  die("ID da compra não fornecido.");
}

$order_id = intval($_GET['order_id']);

// Obter informações da compra
$stmt = $conn->prepare("SELECT c.*, u.nome, u.email, u.telefone, u.nome_da_empresa 
                        FROM compras c 
                        JOIN usuarios u ON c.usuario_id = u.id 
                        WHERE c.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  die("Compra não encontrada.");
}

$order = $result->fetch_assoc();

// Obter itens da compra
$stmt = $conn->prepare("SELECT ic.quantidade, ic.preco_unitario, ic.subtotal, p.nome 
                        FROM itens_compra ic 
                        JOIN produtos p ON ic.produto_id = p.id 
                        WHERE ic.compra_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();

$items = [];
while ($item = $items_result->fetch_assoc()) {
  $items[] = $item;
}

// Criar nova instância de TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Definir informações do documento
$pdf->SetCreator('Empresa Lopes');
$pdf->SetAuthor('Empresa Lopes');
$pdf->SetTitle('Fatura #' . $order_id);
$pdf->SetSubject('Fatura');
$pdf->SetKeywords('Fatura, Empresa Lopes, Compra');

// Remover cabeçalho e rodapé padrão
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Adicionar página
$pdf->AddPage();

// Definir fonte
$pdf->SetFont('helvetica', '', 10);

// Informações da empresa
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'EMPRESA LOPES', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'NIF: 123456789', 0, 1, 'L');
$pdf->Cell(0, 5, 'Rua Principal, 123', 0, 1, 'L');
$pdf->Cell(0, 5, '1000-100 Lisboa, Portugal', 0, 1, 'L');
$pdf->Cell(0, 5, 'Tel: +351 210 123 456', 0, 1, 'L');
$pdf->Cell(0, 5, 'Email: info@empresalopes.pt', 0, 1, 'L');

// Adicionar espaço
$pdf->Ln(10);

// Informações da fatura
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'FATURA', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'Fatura Nº: ' . $order_id, 0, 1, 'R');
$pdf->Cell(0, 5, 'Data: ' . date('d/m/Y', strtotime($order['data_compra'])), 0, 1, 'R');
$pdf->Cell(0, 5, 'Método de Pagamento: Cartão de Crédito', 0, 1, 'R');

// Adicionar espaço
$pdf->Ln(5);

// Informações do cliente
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, 'CLIENTE:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'Nome: ' . $order['nome'], 0, 1, 'L');
$pdf->Cell(0, 5, 'Empresa: ' . $order['nome_da_empresa'], 0, 1, 'L');
$pdf->Cell(0, 5, 'Email: ' . $order['email'], 0, 1, 'L');
$pdf->Cell(0, 5, 'Telefone: ' . $order['telefone'], 0, 1, 'L');

// Adicionar espaço
$pdf->Ln(10);

// Tabela de itens
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(80, 7, 'Produto', 1, 0, 'L', true);
$pdf->Cell(30, 7, 'Quantidade', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Preço Unit.', 1, 0, 'R', true);
$pdf->Cell(40, 7, 'Subtotal', 1, 1, 'R', true);

$pdf->SetFont('helvetica', '', 10);
foreach ($items as $item) {
  $pdf->Cell(80, 7, $item['nome'], 1, 0, 'L');
  $pdf->Cell(30, 7, $item['quantidade'], 1, 0, 'C');
  $pdf->Cell(40, 7, '€' . number_format($item['preco_unitario'], 2, ',', '.'), 1, 0, 'R');
  $pdf->Cell(40, 7, '€' . number_format($item['subtotal'], 2, ',', '.'), 1, 1, 'R');
}

// Total
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(150, 7, 'Total:', 1, 0, 'R', true);
$pdf->Cell(40, 7, '€' . number_format($order['valor_compra'], 2, ',', '.'), 1, 1, 'R', true);

// Adicionar espaço
$pdf->Ln(10);

// Informações adicionais
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, 'Fatura processada por sistema informático', 0, 1, 'L');
$pdf->Cell(0, 5, 'Documento emitido de acordo com o Decreto-Lei n.º 28/2019', 0, 1, 'L');

// Adicionar QR Code (obrigatório a partir de 2022)
$qrcode_data = 'A:Empresa Lopes|B:123456789|C:FT ' . $order_id . '|D:' . date('Y-m-d', strtotime($order['data_compra'])) . '|E:' . number_format($order['valor_compra'], 2, '.', '') . '|F:' . $order['nome'] . '|G:' . $order['email'];
$pdf->write2DBarcode($qrcode_data, 'QRCODE,L', 150, 230, 40, 40, ['border' => false], 'N');

// Adicionar assinatura digital (obrigatório a partir de 2025)
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 5, 'Este documento contém uma assinatura digital qualificada', 0, 1, 'L');
$pdf->Cell(0, 5, 'Assinado digitalmente por Empresa Lopes', 0, 1, 'L');
$pdf->Cell(0, 5, 'Data: ' . date('d/m/Y H:i:s'), 0, 1, 'L');

// Adicionar ATCUD (opcional, mas recomendado)
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(0, 5, 'ATCUD: ' . date('Y') . '/' . $order_id, 0, 1, 'L');

// Gerar o PDF e forçar o download
$pdf->Output('Fatura_' . $order_id . '.pdf', 'D');

// Fechar conexão com o banco de dados
$stmt->close();
$conn->close();
?>
