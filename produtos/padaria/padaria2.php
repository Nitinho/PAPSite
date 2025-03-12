<?php
session_start();
include('../db_connect.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['email'])) {
    // Redirecionar para a página de login
    header("Location: ../../login/login.php");
    exit();
}

// Verifica se o usuário está logado para definir o redirecionamento
if (isset($_SESSION['email'])) {
    // Usuário logado, redireciona para o dashboard
    $redirectUrl = "../../client/dashboard.php";
} else {
    // Usuário não logado, redireciona para o login
    $redirectUrl = "../../login/login.php";
}

// Inicializa o carrinho se não existir
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Buscar produtos do banco de dados
$categoria = "baguete"; // Ajuste conforme necessário
$sql = "SELECT * FROM produtos WHERE categoria = '$categoria'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja de Pães</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div id="headerimg">
            <img src="../../img/logolopes.png" alt="Logo">
        </div>
        <div id="headerselect">
            <a href="../../index.php">INICIO</a>
            <a href="../../index.php#container2">PRODUTOS</a>
            <a href="#">SOBRE</a>
            <a href="#">CONTATOS</a>
            <a href="formulario.html">VIRAR CLIENTE</a>
            <button id="buttonheader" onclick="window.location.href='<?php echo $redirectUrl; ?>'"><strong>ÁREA CLIENTE</strong></button>
        </div>
    </header>

    <main>
        <div class="main-container">
            <div class="sidebar">
                <h3>Categorias</h3>
                <ul>
                    <li><a href="padaria1.php" class="active">Pão</a></li>
                    <li><a href="padaria2.php">Baguete</a></li>
                    <li><a href="padaria3.php">Bolos</a></li>
                </ul>
            </div>
            
            <div class="content-wrapper">
                <div class="product-container">
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<div class='product'>";
                            echo "<div class='img-container'>";
                            echo "<img src='" . $row["imagem"] . "' alt='" . $row["nome"] . "'>";
                            echo "</div>";
                            echo "<h4>" . $row["nome"] . "</h4>";
                            echo "<p class='price'>€ " . number_format($row["preco"], 2, ',', '.') . "</p>";
                            echo "<div class='quantity'>";
                            echo "<button class='btn-minus' data-id='" . $row["id"] . "'>-</button>";
                            echo "<input type='number' value='1' min='1' class='quantity-input' id='qty-" . $row["id"] . "' />";
                            echo "<button class='btn-plus' data-id='" . $row["id"] . "'>+</button>";
                            echo "</div>";
                            echo "<button class='btn-order' data-id='" . $row["id"] . "' data-name='" . $row["nome"] . "' data-price='" . $row["preco"] . "' data-image='" . $row["imagem"] . "'>";
                            echo "<i class='fas fa-shopping-cart'></i> Adicionar</button>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p class='no-products'>Nenhum produto encontrado</p>";
                    }
                    ?>
                </div>
                
                <div class="cart-container">
                    <div class="cart-header">
                        <h3><i class="fas fa-shopping-cart"></i> Meu Carrinho</h3>
                        <button id="clear-cart"><i class="fas fa-trash"></i> Limpar</button>
                    </div>
                    <div class="cart-items">
                        <!-- Os itens do carrinho serão adicionados aqui via JavaScript -->
                        <div class="empty-cart">
                            <i class="fas fa-shopping-basket"></i>
                            <p>Seu carrinho está vazio</p>
                        </div>
                    </div>
                    <div class="cart-footer">
                        <div class="cart-total">
                            <span>Total:</span>
                            <span id="cart-total-value">€ 0,00</span>
                        </div>
                        <button id="checkout-btn" disabled>Finalizar Compra</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de confirmação -->
    <div id="confirmation-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Confirmar Pedido</h3>
            <div id="modal-cart-items"></div>
            <div class="modal-total">
                <strong>Total: </strong><span id="modal-total-value">€ 0,00</span>
            </div>
            <div class="modal-buttons">
                <button id="confirm-order">Confirmar</button>
                <button id="cancel-order">Cancelar</button>
            </div>
        </div>
    </div>

    <footer>
        <p><strong>TODOS OS DIREITOS RESERVADOS NA LOPESMARKET 2024 &copy;</strong></p>
    </footer>

    <script src="../script.js"></script>
</body>
</html>

<?php
$conn->close();
?>
