<?php
session_start(); // Inicia a sessão

// Verifica se o usuário está logado
if (isset($_SESSION['email'])) {
    // Usuário logado, redireciona para o dashboard
    $redirectUrl = "Client/dashboard.php";
} else {
    // Usuário não logado, redireciona para o login
    $redirectUrl = "Login/login.php";
}
?>


<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Armazéns Lopes | Fornecedor Premium para Supermercados</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="js/index.js" defer></script>
    <link rel="shortcut icon" type="image/x-icon" href="img/logolopes.ico">
    <meta name="description" content="Armazéns Lopes - Fornecedor premium de produtos para supermercados em Portugal">

    <style>
/* Importação de Fontes */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

/* Configurações Globais */
:root {
    --primary-color: #FF4C4C;
    --primary-dark: #e63939;
    --primary-light: #ff7a7a;
    --white: #ffffff;
    --light-gray: #f5f5f5;
    --dark-gray: #333333;
    --transition: all 0.3s ease;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

html {
    scroll-behavior: smooth;
}

body {
    font-size: 16px;
    line-height: 1.6;
    color: var(--dark-gray);
    background-color: var(--light-gray);
}

a {
    text-decoration: none;
    color: inherit;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 2rem;
    color: var(--primary-color);
    position: relative;
    padding-bottom: 15px;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background-color: var(--primary-color);
}

.fade-in {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease, transform 0.6s ease;
}

.fade-in.visible {
    opacity: 1;
    transform: translateY(0);
}

/* Header */
header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 80px;
    padding: 0 5%;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background-color: var(--white);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    transition: var(--transition);
}

header.scrolled {
    height: 70px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

#headerimg img {
    width: 67px;
    transition: var(--transition);
}

#headerselect {
    display: flex;
    align-items: center;
}

#headerselect a {
    color: var(--primary-color);
    padding: 0 20px;
    font-weight: 600;
    font-size: 14px;
    transition: var(--transition);
    position: relative;
}

#headerselect a::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 2px;
    background-color: var(--primary-color);
    transition: var(--transition);
}

#headerselect a:hover::after {
    width: 70%;
}

#buttonheader {
    padding: 10px 20px;
    margin-left: 20px;
    font-size: 14px;
    font-weight: 600;
    color: var(--primary-color);
    background-color: transparent;
    border: 2px solid var(--primary-color);
    border-radius: 30px;
    cursor: pointer;
    transition: var(--transition);
}

#buttonheader:hover {
    background-color: var(--primary-color);
    color: var(--white);
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(255, 76, 76, 0.3);
}

/* Menu Móvel */
.mobile-menu-toggle {
    display: none;
    flex-direction: column;
    justify-content: space-between;
    width: 30px;
    height: 21px;
    cursor: pointer;
}

.mobile-menu-toggle span {
    display: block;
    width: 100%;
    height: 3px;
    background-color: var(--primary-color);
    border-radius: 3px;
    transition: var(--transition);
}

.mobile-menu {
    display: none;
    position: fixed;
    top: 80px;
    left: 0;
    width: 100%;
    background-color: var(--white);
    padding: 0;
    z-index: 999;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.5s ease;
}

.mobile-menu.active {
    max-height: 350px;
}

.mobile-menu a {
    display: block;
    padding: 15px 5%;
    color: var(--primary-color);
    font-weight: 600;
    border-bottom: 1px solid rgba(255, 76, 76, 0.1);
    transition: var(--transition);
}

.mobile-menu a:hover {
    background-color: rgba(255, 76, 76, 0.05);
    padding-left: 7%;
}

.mobile-menu a.mobile-area-cliente {
    background-color: var(--primary-color);
    color: var(--white);
    text-align: center;
    margin: 15px 5%;
    border-radius: 30px;
    border: none;
}

/* Container 1 */
#container1 {
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 600px;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    padding: 0 5%;
    margin-top: 80px;
    color: var(--white);
}

#container1txt {
    flex: 1;
    max-width: 600px;
}

#container1txt h1 {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    line-height: 1.2;
}

#container1txt p {
    font-size: 1.1rem;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.cta-button {
    display: inline-block;
    padding: 12px 30px;
    background-color: var(--white);
    color: var(--primary-color);
    font-weight: 600;
    border-radius: 30px;
    transition: var(--transition);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.cta-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

#container1img {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
}

#container1img img {
    max-width: 100%;
    height: auto;
    filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.2));
    animation: float 4s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-15px); }
}

/* Container 2 - Produtos */
#container2 {
    padding: 80px 5%;
    background-color: var(--white);
}

.produtos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

.containerproduto {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    border-radius: 15px;
    box-shadow: 0 10px 20px rgba(255, 76, 76, 0.1);
    overflow: hidden;
    transition: var(--transition);
    height: 250px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.containerproduto:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(255, 76, 76, 0.2);
}

.produtosselect {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    padding: 20px;
}

.produtosselect img {
    width: 120px;
    height: 120px;
    object-fit: contain;
    margin-bottom: 20px;
    transition: var(--transition);
}

.containerproduto:hover .produtosselect img {
    transform: scale(1.1);
}

.produtosselect button {
    padding: 10px 20px;
    background-color: var(--white);
    color: var(--primary-color);
    font-weight: 600;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    transition: var(--transition);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.produtosselect button:hover {
    background-color: rgba(255, 255, 255, 0.9);
    transform: scale(1.05);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
}

/* Sobre Nós */
#sobre {
    padding: 80px 5%;
    background-color: var(--light-gray);
}

.sobre-content {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

.sobre-content p {
    margin-bottom: 1.5rem;
    font-size: 1.1rem;
    line-height: 1.7;
}

/* Perguntas Frequentes */
#container5 {
    padding: 80px 5%;
    background-color: var(--white);
}

.faq-container {
    max-width: 800px;
    margin: 0 auto;
}

.faq-item {
    background-color: var(--white);
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    margin-bottom: 20px;
    overflow: hidden;
    transition: var(--transition);
    border-left: 4px solid var(--primary-color);
}

.faq-item:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.faq-question {
    padding: 20px;
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--primary-color);
    cursor: pointer;
    position: relative;
    transition: var(--transition);
}

.faq-question::after {
    content: '+';
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1.5rem;
    transition: var(--transition);
}

.faq-item.active .faq-question::after {
    content: '-';
}

.faq-answer {
    padding: 0 20px;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease, padding 0.4s ease;
}

.faq-item.active .faq-answer {
    max-height: 500px;
    padding: 0 20px 20px;
}

/* Locais de Reabastecimento */
#locais {
    padding: 80px 5%;
    background-color: var(--light-gray);
}

.reabastecimento {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

.reabastecimento-card {
    width: 350px;
    background-color: var(--white);
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
    transition: var(--transition);
}

.reabastecimento-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.card-image {
    height: 200px;
    overflow: hidden;
}

.card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.reabastecimento-card:hover .card-image img {
    transform: scale(1.1);
}

.reabastecimento-info {
    padding: 20px;
}

.reabastecimento-info h3 {
    font-size: 1.3rem;
    color: var(--primary-color);
    margin-bottom: 15px;
    font-weight: 600;
}

.reabastecimento-info ul {
    list-style: none;
}

.reabastecimento-info li {
    margin-bottom: 10px;
    display: flex;
    align-items: center;
}

.reabastecimento-info i {
    color: var(--primary-color);
    margin-right: 10px;
    font-size: 1rem;
}

/* Contactos */
#container6 {
    padding: 80px 5%;
    background-color: var(--white);
}

.contact-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 30px;
    max-width: 800px;
    margin: 0 auto;
}

.contact-item {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    border-radius: 15px;
    padding: 25px;
    width: 100%;
    box-shadow: 0 10px 20px rgba(255, 76, 76, 0.1);
    transition: var(--transition);
}

.contact-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(255, 76, 76, 0.2);
}

.contact-icon {
    width: 60px;
    height: 60px;
    background-color: var(--white);
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-right: 20px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.contact-icon i {
    font-size: 1.5rem;
    color: var(--primary-color);
}

.contact-info {
    flex: 1;
}

.contact-info h3 {
    font-size: 1.2rem;
    color: var(--white);
    margin-bottom: 5px;
    font-weight: 600;
}

.contact-info p {
    color: var(--white);
    font-size: 1.1rem;
}

.contact-item button {
    padding: 10px 20px;
    background-color: transparent;
    color: var(--white);
    border: 2px solid var(--white);
    border-radius: 30px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
}

.contact-item button:hover {
    background-color: var(--white);
    color: var(--primary-color);
}

/* Footer */
footer {
    background-color: var(--dark-gray);
    color: var(--white);
    padding: 50px 5% 20px;
}

.footer-content {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.footer-logo img {
    filter: brightness(0) invert(1);
}

.footer-links {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.footer-links a {
    color: var(--white);
    transition: var(--transition);
}

.footer-links a:hover {
    color: var(--primary-light);
}

.footer-social {
    display: flex;
    gap: 15px;
}

.footer-social a {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 40px;
    height: 40px;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    transition: var(--transition);
}

.footer-social a:hover {
    background-color: var(--primary-color);
    transform: translateY(-3px);
}

.footer-social i {
    font-size: 1.2rem;
    color: var(--white);
}

.footer-bottom {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

/* Botão Voltar ao Topo */
#back-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background-color: var(--primary-color);
    color: var(--white);
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    opacity: 0;
    visibility: hidden;
    transition: var(--transition);
    z-index: 999;
}

#back-to-top.visible {
    opacity: 1;
    visibility: visible;
}

#back-to-top:hover {
    background-color: var(--primary-dark);
    transform: translateY(-5px);
}

/* Responsividade */
@media (max-width: 1024px) {
    .section-title {
        font-size: 2.2rem;
    }
    
    #container1txt h1 {
        font-size: 2.5rem;
    }
}

@media (max-width: 768px) {
    /* Header */
    #headerselect {
        display: none;
    }
    
    .mobile-menu-toggle {
        display: flex;
    }
    
    /* Container 1 */
    #container1 {
        flex-direction: column;
        padding: 50px 5%;
        text-align: center;
    }
    
    #container1txt {
        margin-bottom: 40px;
    }
    
    #container1txt h1 {
        font-size: 2rem;
    }
    
    #container1img img {
        max-width: 80%;
    }
    
    /* Produtos */
    .produtos-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
    
    /* Reabastecimento */
    .reabastecimento-card {
        width: 100%;
        max-width: 400px;
    }
    
    /* Footer */
    .footer-content {
        flex-direction: column;
        gap: 30px;
        text-align: center;
    }
    
    .footer-links {
        justify-content: center;
    }
    
    .footer-social {
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .section-title {
        font-size: 1.8rem;
    }
    
    #container1txt h1 {
        font-size: 1.8rem;
    }
    
    .produtos-grid {
        grid-template-columns: 1fr;
    }
    
    .contact-item {
        flex-direction: column;
        text-align: center;
    }
    
    .contact-icon {
        margin: 0 0 15px 0;
    }
    
    .contact-info {
        margin-bottom: 15px;
    }
}
.mobile-menu {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.mobile-menu.active {
    max-height: 350px; /* Ou altura suficiente para seu menu */
}

    </style>

</head>

<body>
    <header>
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
            <a href="../index.php">INÍCIO</a>
            <a href="../index.php#container2">PRODUTOS</a>
            <a href="../index.php#sobre">SOBRE</a>
            <a href="../index.php#container6">CONTACTOS</a>
            <a href="../formulario.php">VIRAR CLIENTE</a>
            <a href="#" class="mobile-area-cliente"><strong>ÁREA CLIENTE</strong></a>
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
                <div class="containerproduto">
                    <div class="produtosselect">
                        <img src="img/pao.png" alt="Padaria e Pastelaria">
                        <a href="produtos/padaria/padaria1.php"><button>Padaria e Pastelaria</button></a>
                    </div>
                </div>
                <div class="containerproduto">
                    <div class="produtosselect">
                        <img src="img/image.png" alt="Bebidas">
                        <a href="produtos/bebidas/bebidas.php"><button>Bebidas</button></a>
                    </div>
                </div>

                <div class="containerproduto">
                    <div class="produtosselect">
                        <img src="img/gelado.png" alt="Congelados">
                        <a href="produtos/congelados/congelados.php"><button>Congelados</button></a>
                    </div>
                </div>
                <div class="containerproduto">
                    <div class="produtosselect">
                        <img src="img/marceria.png" alt="Mercearia">
                        <button>Mercearia</button>
                    </div>
                </div>
                <div class="containerproduto">
                    <div class="produtosselect">
                        <img src="img/laticinios.png" alt="Laticínios">
                        <a href="produtos/laticinios/laticinios.php"><button>Laticínios</button></a>

                    </div>
                </div>
                <div class="containerproduto">
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
                        <p>contacto@lopesmarket.com</p>
                    </div>
                    <button onclick="copyToClipboard('contacto@lopesmarket.com')">Copiar</button>
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
            <div class="footer-logo">
            </div>
            <div class="footer-links">
                <a href="index.php">Início</a>
                <a href="#container2">Produtos</a>
                <a href="#sobre">Sobre</a>
                <a href="#container6">Contactos</a>
            </div>
            <div class="footer-social">

            </div>
        </div>
        <div class="footer-bottom">
            <p><strong>© 2024 ARMAZÉNS LOPES. TODOS OS DIREITOS RESERVADOS.</strong></p>
        </div>
    </footer>

    <button id="back-to-top" title="Voltar ao Topo"><i class="fas fa-arrow-up"></i></button>


</body>

</html>