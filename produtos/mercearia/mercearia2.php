<?php
session_start();
include('../db_connect.php');

// Verificar se o Utilizador está logado
if (!isset($_SESSION['email'])) {
    // Redirecionar para a página de login
    header("Location: ../../login/login.php");
    exit();
}

// Verifica se o Utilizador está logado para definir o redirecionamento
if (isset($_SESSION['email'])) {
    // Utilizador logado, redireciona para o dashboard
    $redirectUrl = "../../client/dashboard.php";
} else {
    // Utilizador não logado, redireciona para o login
    $redirectUrl = "../../login/login.php";
}

// Inicializa o carrinho se não existir
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Buscar produtos do base de dados
$categoria = "arroz"; // Ajuste conforme necessário
$sql = "SELECT * FROM produtos WHERE categoria = '$categoria'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mercearia</title>
    <meta name="description" content="Produtos de padaria e pastelaria de alta qualidade dos Armazéns Lopes">
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../../img/favicon.ico" type="image/x-icon">
</head>

<body>
    <header>
        <div id="headerimg">
            <a href="../../index.php"><img src="../../img/logolopes.png" alt="Logo Armazéns Lopes"></a>
        </div>
        <div id="headerselect">
            <a href="../../index.php">INÍCIO</a>
            <a href="../../index.php#container2">PRODUTOS</a>
            <a href="../../index.php#sobre">SOBRE</a>
            <a href="../../index.php#container6">CONTACTOS</a>
            <button id="buttonheader" onclick="window.location.href='<?php echo $redirectUrl; ?>'"><strong>ÁREA CLIENTE</strong></button>
        </div>

        <div class="mobile-menu-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <nav class="mobile-menu">
            <a href="../../index.php">INÍCIO</a>
            <a href="../../index.php#container2">PRODUTOS</a>
            <a href="../../index.php#sobre">SOBRE</a>
            <a href="../../index.php#container6">CONTACTOS</a>
            <a href="<?php echo $redirectUrl; ?>" class="mobile-area-cliente"><strong>ÁREA CLIENTE</strong></a>
        </nav>
    </header>

    <div class="page-banner">
        <div class="banner-content">
            <h1>Mercearia</h1>
            <p>Arroz e massa</p>
        </div>
    </div>

    <main>
        <div class="main-container">
            <div class="sidebar">
                <h3>Categorias</h3>
                <ul>
                    <li>
                        <a href="mercearia.php">
                            <i class="fas fa-oil-can" aria-hidden="true"></i>
                            Azeites, Óleos
                        </a>
                    </li>
                    <li>
                        <a href="mercearia2.php" class="active">
                            <i class="fas fa-seedling" aria-hidden="true"></i>
                            Arroz e massa
                        </a>
                    </li>
                </ul>



                <div style="margin-top: 30px;"></div>

                <h3>Outras Categorias</h3>
                <ul>
                    <li><a href="../padaria/padaria1.php"><i class="fas fa-bread-slice"></i>Padaria</a></li>
                    <li><a href="../bebidas/bebidas.php"><i class="fas fa-wine-bottle"></i>Bebidas</a></li>
                    <li><a href="../congelados/congelados.php"><i class="fas fa-snowflake"></i>Congelados</a></li>
                    <li><a href="../mercearia/mercearia.php"><i class="fas fa-shopping-basket"></i>Mercearia</a></li>
                    <li><a href="../laticinios/laticinios.php"><i class="fas fa-cheese"></i>Laticínios</a></li>
                    <li><a href="../frescos/frescos.php"><i class="fas fa-carrot"></i>Frescos</a></li>

                </ul>

            </div>




            <div class="content-wrapper">
                <div class="product-filters">
                    <div class="filter-group">
                        <label for="sort-by">Ordenar por:</label>
                        <select id="sort-by">
                            <option value="name-asc">Nome (A-Z)</option>
                            <option value="name-desc">Nome (Z-A)</option>
                            <option value="price-asc">Preço (Menor-Maior)</option>
                            <option value="price-desc">Preço (Maior-Menor)</option>
                        </select>
                    </div>
                    <div class="search-products">
                        <input type="text" id="search-input" placeholder="Pesquisar produtos...">
                        <button id="search-btn"><i class="fas fa-search"></i></button>
                    </div>
                </div>

                <div class="product-container">
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<div class='product' data-id='" . $row["id"] . "' data-name='" . $row["nome"] . "' data-price='" . $row["preco"] . "'>";

                            echo "<div class='img-container'>";
                            echo "<img src='" . $row["imagem"] . "' alt='" . $row["nome"] . "'>";
                            echo "</div>";
                            echo "<div class='product-info'>";
                            echo "<h4>" . $row["nome"] . "</h4>";
                            echo "<p class='price'>€ " . number_format($row["preco"], 2, ',', '.') . "</p>";
                            echo "<div class='product-rating'>";
                            echo "<i class='fas fa-star'></i>";
                            echo "<i class='fas fa-star'></i>";
                            echo "<i class='fas fa-star'></i>";
                            echo "<i class='fas fa-star'></i>";
                            echo "<i class='far fa-star'></i>";
                            echo "<span>(4.0)</span>";
                            echo "</div>";
                            echo "</div>";
                            echo "<div class='quantity'>";
                            echo "<button class='btn-minus' data-id='" . $row["id"] . "'><i class='fas fa-minus'></i></button>";
                            echo "<input type='number' value='1' min='1' class='quantity-input' id='qty-" . $row["id"] . "' />";
                            echo "<button class='btn-plus' data-id='" . $row["id"] . "'><i class='fas fa-plus'></i></button>";
                            echo "</div>";
                            echo "<button class='btn-order' data-id='" . $row["id"] . "' data-name='" . $row["nome"] . "' data-price='" . $row["preco"] . "' data-image='" . $row["imagem"] . "'>";
                            echo "<i class='fas fa-shopping-cart'></i> Adicionar ao Carrinho</button>";
                            echo "</div>";
                        }
                    } else {
                        echo "<div class='no-products'>";
                        echo "<i class='fas fa-exclamation-circle'></i>";
                        echo "<p>Nenhum produto encontrado</p>";
                        echo "<p>Tente outra categoria ou volte mais tarde</p>";
                        echo "</div>";
                    }
                    ?>
                </div>

                <div class="cart-container">
                    <div class="cart-header">
                        <h3><i class="fas fa-shopping-cart"></i> Meu Carrinho</h3>
                        <button id="clear-cart"><i class="fas fa-trash"></i> Limpar</button>
                    </div>
                    <div class="cart-items">
                        <div class="empty-cart">
                            <i class="fas fa-shopping-basket"></i>
                            <p>Seu carrinho está vazio</p>
                            <small>Adicione produtos para continuar</small>
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

    <!-- Modal de sucesso -->
    <div id="success-modal" class="modal">
        <div class="modal-content success-content">
            <span class="close">&times;</span>
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3>Pedido Realizado com Sucesso!</h3>
            <p>O seu pedido foi registado e será processado em breve.</p>
            <button id="continue-shopping">Continuar Comprando</button>
        </div>
    </div>

    <footer>
        <div class="footer-content">

            <div class="footer-links">
                <a href="../../index.php">Início</a>
                <a href="../../index.php#container2">Produtos</a>
                <a href="../../index.php#sobre">Sobre</a>
                <a href="../../index.php#container6">Contactos</a>
            </div>
            <div class="footer-social">
                <a href="https://www.facebook.com/escolabasica.secundariaourem/?locale=pt_PT"><i class="fab fa-facebook"></i></a>
                <a href="https://www.instagram.com/aeourem/"><i class="fab fa-instagram"></i></a>

                
            </div>
        </div>
        <div class="footer-bottom">
            <p><strong>© 2024 ARMAZÉNS LOPES. TODOS OS DIREITOS RESERVADOS.</strong></p>
        </div>
    </footer>

    <button id="back-to-top" title="Voltar ao Topo"><i class="fas fa-arrow-up"></i></button>

    <script src="../script.js"></script>
</body>

</html>

<?php
$conn->close();
?>