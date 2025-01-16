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
    <title>Bem-Vindo aos Armazens Lopes     </title>
    <link rel="stylesheet" href="styles/style.css">
    <script src="js/index.js"></script>
</head>

<body>
    <header>

        <div id="headerimg">
            <a href="index.php"><img src="img/logolopes.png" alt="Logo" ></a>
        </div>
        <div id="headerselect">
            <a href="index.php">INICIO</a>
            <a href="#container2">PRODUTOS</a>
            <a href="">SOBRE</a>
            <a href="#container6">CONTATOS</a>
            <a href="formulario.php">VIRAR CLIENTE</a>
            <button id="buttonheader" onclick="window.location.href='<?php echo $redirectUrl; ?>'"><strong>ÁREA CLIENTE</strong></button>

        </div>
    </header>

    <main>
        <div id="container1" class="fade-in">
            <div id="container1txt">
                <h1>Armazém Lopes, onde a qualidade é <br>
                    prioridade e encontrar o que precia é
                    <br>simples.
                </h1>
                <p>Nos Armazéns Lopes, encontra tudo o que precisa para o seu supermercado. <br>
                    Desde os básicos como arroz e frutas frescas, até especiarias exóticas e <br>
                    produtos de charcutaria</p>
            </div>
            <div id="container1img">
                <img src="img/carrinhop.png" alt="">
            </div>
        </div>
        <div id="container2" class="fade-in">
            <h1>CONHEÇA OS NOSSOS PRODUTOS</h1>
            <div id="produtos1">
                <div class="containerproduto" class="fade-in">
                    <div class="produtosselect">
                        <img src="img/pao.png" alt="">
                        <a href="produtos/padaria/padaria1.php"><button>Padaria e <br>Pastaleria</button></a>
                        
                    </div>
                </div>
                <div class="containerproduto" class="fade-in">
                    <div class="produtosselect">
                        <img src="img/image.png" alt="">
                        <button>Bebidas</button>
                    </div>
                </div>
                <div class="containerproduto" class="fade-in">
                    <div class="produtosselect">
                        <img src="img/gelado.png" alt="">
                        <button>Congelados</button>
                    </div>
                </div>
                <div class="containerproduto" class="fade-in">
                    <div class="produtosselect">
                        <img src="img/pao.png" alt="">
                        <button>Merceria</button>
                    </div>
                </div>
            </div>
            <div id="produtos2" class="fade-in">
                <div class="containerproduto" class="fade-in">
                    <div class="produtosselect">
                        <img src="img/pao.png" alt="">
                        <button>Laticínios</button>
                    </div>
                </div>
                <div class="containerproduto" class="fade-in">
                    <div class="produtosselect">
                        <img src="img/pao.png" alt="">
                        <button>Frescos</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="container3" class="fade-in">
            <div id="imgcontaner3">
                <img id="imgcontainer3" src="img/polvo.jpg" alt="Polvo" onclick="openNewPage()">
            </div>
            <div id="txtcontainer3">
                <h1>ALGUMAS RECEITAS NOSSAS</h1>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Eaque alias consectetur itaque odio at nihil
                    vero vel nobis quod tempora.</p>
            </div>
        </div>
        <div id="container5" class="fade-in">
            <h1>PERGUNTAS FREQUENTES</h1>
            <div class="faq-item">
                <h2 class="faq-question">O que é o Armazém Lopes?</h2>
                <div class="faq-answer">
                    <p>O Armazém Lopes é uma loja especializada em fornecer produtos de alta qualidade para
                        supermercados e comércios.</p>
                </div>
            </div>
            <div class="faq-item">
                <h2 class="faq-question">Como posso tornar-me cliente?</h2>
                <div class="faq-answer">
                    <p>Para se tornar cliente, basta seguir as instruções na seção "Como Virar Cliente" ou clicar aqui
                        <a href="formulario.html">Virar cliente</a>.</p>
                </div>
            </div>
            <div class="faq-item">
                <h2 class="faq-question">Quais são as formas de pagamento aceites?</h2>
                <div class="faq-answer">
                    <p>Aceitamos cartões de crédito, débito e pagamentos por transferência bancária.</p>
                </div>
            </div>
        </div>

        <div id="container5">
            <h1>Locais de Reabastecimentos</h1>
            <div class="reabastecimento">
                <div class="reabastecimento-card">
                    <img src="https://mecaluxpt.cdnwm.com/video-background-image-cister.1.0.jpg?e=jpg"
                        alt="Armazém Lisboa">
                    <div class="reabastecimento-info">
                        <h2>Armazém Lisboa</h2>
                        <p>Horário: 08:00 - 18:00</p>
                        <p>Coordenadas GPS: 38.7169° N, 9.1399° W</p>
                        <p>Morada: Rua do Comércio, 1250-096, Lisboa</p>
                        <p>Telefone: +351 21 123 4567</p>
                    </div>
                </div>

                <div class="reabastecimento-card">
                    <img src="https://mecaluxpt.cdnwm.com/video-background-image-cister.1.0.jpg?e=jpg"
                        alt="Armazém Porto">
                    <div class="reabastecimento-info">
                        <h2>Armazém Porto</h2>
                        <p>Horário: 07:00 - 19:00</p>
                        <p>Coordenadas GPS: 41.1496° N, 8.6110° W</p>
                        <p>Morada: Avenida dos Aliados, 4000-064, Porto</p>
                        <p>Telefone: +351 22 987 6543</p>
                    </div>
                </div>

                <div class="reabastecimento-card">
                    <img src="https://mecaluxpt.cdnwm.com/video-background-image-cister.1.0.jpg?e=jpg"
                        alt="Armazém Faro">
                    <div class="reabastecimento-info">
                        <h2>Armazém Faro</h2>
                        <p>Horário: 09:00 - 17:00</p>
                        <p>Coordenadas GPS: 37.0179° N, 7.9308° W</p>
                        <p>Morada: Estrada Nacional 125, 8000-123, Faro</p>
                        <p>Telefone: +351 28 765 4321</p>
                    </div>
                </div>


            </div>
        </div>

        <div id="container6">
            <h1>Contactos</h1>
            <div class="contact-item">
                <img src="https://cdn-icons-png.flaticon.com/512/732/732200.png" alt="Email Icon">
                <div class="contact-info">
                    <h2>Email</h2>
                    <p>contacto@lopesmarket.com</p>
                </div>
                <button onclick="copyToClipboard('contacto@lopesmarket.com')">Copiar</button>
            </div>
            <div class="contact-item">
                <img src="https://cdn-icons-png.flaticon.com/512/724/724664.png" alt="Telefone Icon">
                <div class="contact-info">
                    <h2>Telefone</h2>
                    <p>+351 21 123 4567</p>
                </div>
                <button onclick="copyToClipboard('+351 21 123 4567')">Copiar</button>
            </div>
        </div>
        

    </main>

    <footer>
        <p><strong>TODOS OS DIREITOS RESERVADOS NA LOPESMARKET 2024 ©</strong></p>
    </footer>

</body>

</html>