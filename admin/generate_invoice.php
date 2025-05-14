<?php
// Iniciar a sessão administrativa
session_name('admin_session');
session_start();

// Incluir arquivo de configuração
require_once 'config.php';

// Verificar login administrativo
verificarLoginAdmin();

// Incluir a biblioteca TCPDF
require_once('../Client/tcpdf/tcpdf.php');

// Verificar se o ID da compra foi fornecido
if (!isset($_GET['compra_id']) || !is_numeric($_GET['compra_id'])) {
    die("ID de compra inválido");
}

$compra_id = $_GET['compra_id'];

// Obter detalhes da compra
$query_compra = "SELECT c.*, u.nome as nome_cliente, u.email, u.telefone, u.nif 
                FROM compras c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.id = ?";
$stmt = $conn->prepare($query_compra);
if ($stmt === false) {
    die("Erro na preparação da consulta: " . $conn->error);
}

$stmt->bind_param("i", $compra_id);
if (!$stmt->execute()) {
    die("Erro na execução da consulta: " . $stmt->error);
}

$result_compra = $stmt->get_result();
if ($result_compra->num_rows === 0) {
    die("Encomenda não encontrada");
}

$compra = $result_compra->fetch_assoc();

// Buscar itens da compra
$query_itens = "SELECT i.*, p.nome as nome_produto, p.imagem 
               FROM itens_compra i 
               JOIN produtos p ON i.produto_id = p.id 
               WHERE i.compra_id = ?";
$stmt = $conn->prepare($query_itens);
if ($stmt === false) {
    die("Erro na preparação da consulta de itens: " . $conn->error);
}

$stmt->bind_param("i", $compra_id);
if (!$stmt->execute()) {
    die("Erro na execução da consulta de itens: " . $stmt->error);
}

$result_itens = $stmt->get_result();
$itens = $result_itens->fetch_all(MYSQLI_ASSOC);

// Buscar endereço de entrega
$query_endereco = "SELECT e.* 
                  FROM enderecos e 
                  JOIN compras c ON e.id = c.endereco_id 
                  WHERE c.id = ?";
$stmt = $conn->prepare($query_endereco);
if ($stmt === false) {
    // Apenas registrar o erro, não interromper a execução
    $endereco = null;
} else {
    $stmt->bind_param("i", $compra_id);
    if (!$stmt->execute()) {
        $endereco = null;
    } else {
        $result_endereco = $stmt->get_result();
        $endereco = $result_endereco->fetch_assoc();
    }
}

// Criar nova instância do TCPDF
class MYPDF extends TCPDF {
    // Cabeçalho da página
    public function Header() {
        // Logo
        $image_file = '../img/logolopes.png';
        $this->Image($image_file, 10, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Definir fonte
        $this->SetFont('helvetica', 'B', 20);
        // Título
        $this->Cell(0, 15, 'Armazéns Lopes - Fatura Admnistrativa', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Rodapé da página
    public function Footer() {
        // Posição a 15 mm do final
        $this->SetY(-15);
        // Definir fonte
        $this->SetFont('helvetica', 'I', 8);
        // Número da página
        $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Criar novo documento PDF
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Definir informações do documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Armazéns Lopes');
$pdf->SetTitle('Fatura #' . $compra_id);
$pdf->SetSubject('Fatura de Compra');
$pdf->SetKeywords('Fatura, Armazéns Lopes, Compra');

// Definir informações de cabeçalho padrão
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// Definir fontes de cabeçalho e rodapé
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// Definir fonte padrão monospaced
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Definir margens
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Definir quebras de página automáticas
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Definir fator de escala de imagem
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Adicionar uma página
$pdf->AddPage();

// Definir fonte
$pdf->SetFont('helvetica', '', 10);

// Data atual formatada
$data_atual = date('d/m/Y');

// Conteúdo da fatura
$html = '
<h1 style="text-align:center;">FATURA</h1>
<table border="0" cellspacing="0" cellpadding="5">
    <tr>
        <td width="50%"><strong>Armazéns Lopes</strong><br />
            Rua Principal, 123<br />
            1000-000 Lisboa<br />
            Portugal<br />
            Tel: +351 210 000 000<br />
            Email: info@armazenslopes.pt<br />
            NIF: 123456789
        </td>
        <td width="50%">
            <strong>Fatura para:</strong><br />
            Nome: '.$compra['nome_cliente'].'<br />
            Email: '.$compra['email'].'<br />
            Telefone: '.$compra['telefone'].'<br />
            NIF: '.$compra['nif'].'<br />';

if ($endereco) {
    $html .= '
            <strong>Endereço de Entrega:</strong><br />
            '.$endereco['rua'].', '.$endereco['numero'].'<br />
            '.$endereco['cidade'].', '.$endereco['codigo_postal'].'<br />
            '.$endereco['pais'];
}

$html .= '
        </td>
    </tr>
</table>
<br /><br />
<table border="0" cellspacing="0" cellpadding="5">
    <tr>
        <td width="33%"><strong>Fatura Nº:</strong> #'.$compra_id.'</td>
        <td width="33%"><strong>Data:</strong> '.date('d/m/Y', strtotime($compra['data_compra'])).'</td>
        <td width="33%"><strong>Status:</strong> '.ucfirst($compra['status']).'</td>
    </tr>
</table>
<br /><br />
<table border="1" cellspacing="0" cellpadding="5">
    <tr style="background-color:#f0f0f0;">
        <th width="10%" align="center"><strong>Qtd</strong></th>
        <th width="50%" align="left"><strong>Produto</strong></th>
        <th width="20%" align="right"><strong>Preço Unit.</strong></th>
        <th width="20%" align="right"><strong>Subtotal</strong></th>
    </tr>';

$total = 0;
foreach ($itens as $item) {
    $subtotal = $item['quantidade'] * $item['preco_unitario'];
    $total += $subtotal;
    $html .= '
    <tr>
        <td align="center">'.$item['quantidade'].'</td>
        <td>'.$item['nome_produto'].'</td>
        <td align="right">€ '.number_format($item['preco_unitario'], 2, ',', '.').'</td>
        <td align="right">€ '.number_format($subtotal, 2, ',', '.').'</td>
    </tr>';
}

$html .= '
    <tr>
        <td colspan="3" align="right"><strong>Total:</strong></td>
        <td align="right"><strong>€ '.number_format($total, 2, ',', '.').'</strong></td>
    </tr>
</table>
<br /><br />
<p><strong>Notas:</strong></p>

<p>Esta fatura é apenas uma cópia da original e destina-se exclusivamente a fins logísticos, nomeadamente para a montagem da encomenda.</p> 
<p>Documento gerado automaticamente em '.$data_atual.'.</p>

<br /><br />
<table border="0" cellspacing="0" cellpadding="5">
    <tr>
        <td width="70%"></td>
        <td width="30%" style="border-top: 1px solid #000000; text-align: center;">
            <br />Assinatura Autorizada
        </td>
    </tr>
</table>
';

// Escrever o HTML no PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Fechar e gerar o PDF
$pdf->Output('Fatura_'.$compra_id.'_'.date('Y-m-d').'.pdf', 'I');
