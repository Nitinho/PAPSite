<?php
session_start(); // Inicia a sessão


if (isset($_SESSION['email'])) {
    $redirectUrl = "Client/dashboard.php";
} else {
    $redirectUrl = "Login/login.php";
}
?>

<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Armazéns Lopes | Fornecedor Premium para Supermercados</title>
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="js/index.js" defer></script>
    <link rel="shortcut icon" type="image/x-icon" href="img/logolopes.ico">
    <meta name="description" content="Armazéns Lopes - Fornecedor premium de produtos para supermercados em Portugal">

</head>


<body>
    <!-- Preloader -->
    <div class="preloader">
        <div class="spinner">
            <div class="bounce1"></div>
            <div class="bounce2"></div>
            <div class="bounce3"></div>
        </div>
    </div>

    <header class="header">
        <div id="headerimg">
            <a href="index.php"><img src="img/logolopes.png" alt="Logo Armazéns Lopes"></a>
        </div>
        <div id="headerselect">
            <a href="index.php">INÍCIO</a>
            <a href="#container2">PRODUTOS</a>
            <a href="#sobre">SOBRE</a>
            <a href="#container6">CONTACTOS</a>
            <button id="buttonheader" onclick="window.location.href='<?php echo $redirectUrl; ?>'"><strong>ÁREA CLIENTE</strong></button>
        </div>

        <div class="mobile-menu-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <nav class="mobile-menu">
            <a href="index.php">INÍCIO</a>
            <a href="#container2">PRODUTOS</a>
            <a href="#sobre">SOBRE</a>
            <a href="#container6">CONTACTOS</a>
            <a href="formulario.php">VIRAR CLIENTE</a>
            <a href="<?php echo $redirectUrl; ?>" class="mobile-area-cliente"><strong>ÁREA CLIENTE</strong></a>
        </nav>
    </header>

    <main>
        <section id="container1" class="fade-in">
            <div id="container1txt">
                <h1>Qualidade Premium para o seu Negócio</h1>
                <p>Nos Armazéns Lopes, encontra tudo o que precisa para o seu supermercado. Desde produtos básicos e frescos até especialidades gourmet e internacionais.</p>
                <a href="login/registrar.php" class="cta-button">Torne-se Cliente</a>
            </div>
            <div id="container1img">
                <img src="img/carrinhop.png" alt="Carrinho de Compras">
            </div>
        </section>

        <section id="container2" class="fade-in">
            <h2 class="section-title">NOSSOS PRODUTOS</h2>
            <div class="produtos-grid">
                <div class="containerproduto product-card">
                    <div class="produtosselect">
                        <img src="img/pao.png" alt="Padaria e Pastelaria">
                        <a href="produtos/padaria/padaria1.php"><button>Padaria e Pastelaria</button></a>
                    </div>
                </div>
                <div class="containerproduto product-card">
                    <div class="produtosselect">
                        <img src="img/image.png" alt="Bebidas">
                        <a href="produtos/bebidas/bebidas.php"><button>Bebidas</button></a>
                    </div>
                </div>
                <div class="containerproduto product-card">
                    <div class="produtosselect">
                        <img src="img/gelado.png" alt="Congelados">
                        <a href="produtos/congelados/congelados.php"><button>Congelados</button></a>
                    </div>
                </div>
                <div class="containerproduto product-card">
                    <div class="produtosselect">
                        <img src="img/marceria.png" alt="Mercearia">
                        <button>Mercearia</button>
                    </div>
                </div>
                <div class="containerproduto product-card">
                    <div class="produtosselect">
                        <img src="img/laticinios.png" alt="Laticínios">
                        <a href="produtos/laticinios/laticinios.php"><button>Laticínios</button></a>
                    </div>
                </div>
                <div class="containerproduto product-card">
                    <div class="produtosselect">
                        <img src="img/fresco.png" alt="Frescos">
                        <a href="produtos/frescos/frescos.php"><button>Frescos</button></a>
                    </div>
                </div>
            </div>
        </section>

        <section id="sobre" class="fade-in">
            <div class="sobre-content">
                <h2 class="section-title">SOBRE NÓS</h2>
                <p>Os Armazéns Lopes são líderes no fornecimento de produtos de qualidade para supermercados em Portugal há mais de 30 anos. Comprometemo-nos com a excelência, oferecendo produtos frescos e de qualidade premium aos nossos clientes.</p>
                <p>A nossa missão é ser o parceiro de confiança para o seu negócio, garantindo entregas pontuais e produtos que satisfazem as necessidades dos seus clientes.</p>
            </div>
        </section>

        <section id="container5" class="fade-in">
            <h2 class="section-title">PERGUNTAS FREQUENTES</h2>
            <div class="faq-container">
                <div class="faq-item">
                    <h3 class="faq-question">O que é o Armazém Lopes?</h3>
                    <div class="faq-answer">
                        <p>O Armazém Lopes é uma empresa especializada em fornecer produtos de alta qualidade para supermercados e comércios em todo Portugal, com mais de 30 anos de experiência no mercado.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <h3 class="faq-question">Como posso tornar-me cliente?</h3>
                    <div class="faq-answer">
                        <p>Para se tornar cliente, basta preencher o formulário na secção "Virar Cliente" ou contactar-nos diretamente. A nossa equipa entrará em contacto para finalizar o processo de registo.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <h3 class="faq-question">Quais são as formas de pagamento aceites?</h3>
                    <div class="faq-answer">
                        <p>Aceitamos múltiplas formas de pagamento, incluindo cartões de crédito, débito, transferência bancária e pagamentos a prazo para clientes registados com histórico aprovado.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="locais" class="fade-in">
            <h2 class="section-title">LOCAIS DE REABASTECIMENTO</h2>
            <div class="reabastecimento">
                <div class="reabastecimento-card">
                    <div class="card-image">
                        <img src="https://mecaluxpt.cdnwm.com/video-background-image-cister.1.0.jpg?e=jpg" alt="Armazém Lisboa">
                    </div>
                    <div class="reabastecimento-info">
                        <h3>Armazém Lisboa</h3>
                        <ul>
                            <li><i class="fas fa-clock"></i> 08:00 - 18:00</li>
                            <li><i class="fas fa-map-marker-alt"></i> Rua do Comércio, 1250-096, Lisboa</li>
                            <li><i class="fas fa-phone"></i> +351 21 123 4567</li>
                        </ul>
                    </div>
                </div>

                <div class="reabastecimento-card">
                    <div class="card-image">
                        <img src="https://mecaluxpt.cdnwm.com/video-background-image-cister.1.0.jpg?e=jpg" alt="Armazém Porto">
                    </div>
                    <div class="reabastecimento-info">
                        <h3>Armazém Porto</h3>
                        <ul>
                            <li><i class="fas fa-clock"></i> 07:00 - 19:00</li>
                            <li><i class="fas fa-map-marker-alt"></i> Avenida dos Aliados, 4000-064, Porto</li>
                            <li><i class="fas fa-phone"></i> +351 22 987 6543</li>
                        </ul>
                    </div>
                </div>

                <div class="reabastecimento-card">
                    <div class="card-image">
                        <img src="https://mecaluxpt.cdnwm.com/video-background-image-cister.1.0.jpg?e=jpg" alt="Armazém Faro">
                    </div>
                    <div class="reabastecimento-info">
                        <h3>Armazém Faro</h3>
                        <ul>
                            <li><i class="fas fa-clock"></i> 09:00 - 17:00</li>
                            <li><i class="fas fa-map-marker-alt"></i> Estrada Nacional 125, 8000-123, Faro</li>
                            <li><i class="fas fa-phone"></i> +351 28 765 4321</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <section id="container6" class="fade-in">
            <h2 class="section-title">CONTACTOS</h2>
            <div class="contact-container">
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-info">
                        <h3>Email</h3>
                        <p>contacto@armazem.com</p>
                    </div>
                    <button onclick="copyToClipboard('contacto@armazem.com')">Copiar</button>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="contact-info">
                        <h3>Telefone</h3>
                        <p>+351 21 123 4567</p>
                    </div>
                    <button onclick="copyToClipboard('+351 21 123 4567')">Copiar</button>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">

            <div class="footer-links">
                <a href="index.php">Início</a>
                <a href="#container2">Produtos</a>
                <a href="#sobre">Sobre</a>
                <a href="#container6">Contactos</a>
            </div>
            <div class="footer-social">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
        <div class="footer-bottom">
            <p><strong>© 2024 ARMAZÉNS LOPES. TODOS OS DIREITOS RESERVADOS.</strong></p>
        </div>
    </footer>

    <button id="back-to-top" class="back-to-top" title="Voltar ao Topo"><i class="fas fa-arrow-up"></i></button>

    <!-- Notificação para copiar texto -->
    <div id="notification" class="notification"></div>
</body>

</html>
