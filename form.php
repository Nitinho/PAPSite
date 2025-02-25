<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informações do Dono e da Empresa</title>
    <link rel="stylesheet" href="styles/form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <header>
        <div id="headerimg">
            <a href="index.php"><img src="img/logolopes.png" alt="Logo"></a>
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
        <div class="container">
            <div class="form-header">
                <i class="fas fa-building"></i>
                <h1>Informações da Empresa</h1>
            </div>
            <form id="infoForm">
                <div class="form-group">
                    <label for="nomeEmpresa"><i class="fas fa-signature"></i> Nome da Empresa:</label>
                    <input type="text" id="nomeEmpresa" name="nomeEmpresa" placeholder="Digite o nome da empresa" required>
                </div>
                <div class="form-group">
                    <label for="nomeDono"><i class="fas fa-user"></i> Nome do Dono:</label>
                    <input type="text" id="nomeDono" name="nomeDono" placeholder="Digite o nome do dono" required>
                </div>
                <div class="form-group">
                    <label for="niff"><i class="fas fa-id-card"></i> NIFF:</label>
                    <input type="text" id="niff" name="niff" placeholder="Digite o NIFF" required>
                </div>
                <div class="form-group">
                    <label for="endereco"><i class="fas fa-map-marker-alt"></i> Endereço:</label>
                    <input type="text" id="endereco" name="endereco" placeholder="Digite o endereço" required>
                </div>
                <div class="form-group">
                    <label for="telefone"><i class="fas fa-phone"></i> Telefone:</label>
                    <input type="tel" id="telefone" name="telefone" placeholder="Digite o telefone">
                </div>
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email:</label>
                    <input type="email" id="email" name="email" placeholder="Digite o email">
                </div>
                <button type="submit"><i class="fas fa-paper-plane"></i> Enviar</button>
            </form>
        </div>
    </main>

    <footer>
        <p><strong>TODOS OS DIREITOS RESERVADOS NA LOPESMARKET 2024 ©</strong></p>
    </footer>

    <script src="script.js"></script>
</body>
</html>
