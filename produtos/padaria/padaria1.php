<?php
require_once 'db_connect.php';

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$sql = "SELECT * FROM produtos";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "
            <div class='product'>
                <h4>$row[nome]</h4>
                <img src='$row[imagem]' alt='$row[nome]'>
                <span>R$ " . number_format($row['preco'], 2, ',', '.') . "</span>
                <input type='number' class='quantity-input' value='1'>
                <button class='btn-order' data-id='$row[id]' data-price='$row[preco]'>Adicionar ao Carrinho</button>
            </div>
        ";
    }
} else {
    echo "Nenhum produto encontrado";
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja de Pães</title>
    <link rel="stylesheet" href="../styles/style.css">
    <script src="../script.js"></script>
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
                <h3>Produtos</h3>
                <ul>
                    <li><a href="padaria1.php">Pão</a></li>
                    <li><a href="padaria2.php">Baguete</a></li>
                    <li><a href="padaria3.php">Bolos</a></li>
                </ul>
            </div>
            <div class="product-container">
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<div class='product'>";
                        echo "<img src='" . $row["imagem"] . "' alt='" . $row["nome"] . "'>";
                        echo "<h4>" . $row["nome"] . "</h4>";
                        echo "<p>R$ " . number_format($row["preco"], 2, ',', '.') . "</p>";
                        echo "<div class='quantity'>";
                        echo "<button class='btn-minus'>-</button>";
                        echo "<input type='number' value='1' class='quantity-input' />";
                        echo "<button class='btn-plus'>+</button>";
                        echo "</div>";
                        echo "<button class='btn-order' data-id='" . $row["id"] . "' data-price='" . $row["preco"] . "'>Encomendar</button>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>Nenhum produto encontrado</p>";
                }
                ?>
            </div>
        </div>

        <div id="cart-container">
            <img src="../../img/carrinho-de-compras.png" alt="Carrinho" id="cart-icon">
            <div id="cart-count">0</div>
        </div>

        <div id="cart-modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Seu Carrinho</h2>
                <div id="cart-items"></div>
                <div class="cart-total">Total: R$ <span id="cart-total">0.00</span></div>
                <button id="confirm-order">Confirmar Pedido</button>
                <button id="clear-cart">Limpar Carrinho</button>
            </div>
        </div>
    </main>

    <footer>
        <p><strong>TODOS OS DIREITOS RESERVADOS NA LOPESMARKET 2024 ©</strong></p>
    </footer>

    <script src="script.js"></script>
</body>
</html>

<?php
$conn->close();
?>
