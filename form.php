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
        </div>
    </header>

    <main>
        <div class="container">
            <div class="form-header">
                
                <h1>Formulário de Cliente</h1>
            </div>
            <form id="infoForm">
                <h2>Dados do Proprietário</h2>
                <div class="form-group">
                    <label for="nomeDono"><i class="fas fa-user"></i> Nome do Proprietário:</label>
                    <input type="text" id="nomeDono" name="nomeDono" placeholder="Digite o nome do proprietário" required>
                </div>
                <div class="form-group">
                    <label for="nifDono"><i class="fas fa-id-card"></i> NIF do Proprietário:</label>
                    <input type="text" id="nifDono" name="nifDono" placeholder="Digite o NIF do proprietário" required>
                </div>
                <div class="form-group">
                    <label for="telefoneDono"><i class="fas fa-phone"></i> Telefone do Proprietário:</label>
                    <input type="tel" id="telefoneDono" name="telefoneDono" placeholder="Digite o telefone do proprietário">
                </div>

                <h2>Dados da Empresa</h2>
                <div class="form-group">
                    <label for="nomeEmpresa"><i class="fas fa-signature"></i> Nome da Empresa:</label>
                    <input type="text" id="nomeEmpresa" name="nomeEmpresa" placeholder="Digite o nome da empresa" required>
                </div>
                <div class="form-group">
                    <label for="nifEmpresa"><i class="fas fa-building"></i> NIF da Empresa:</label>
                    <input type="text" id="nifEmpresa" name="nifEmpresa" placeholder="Digite o NIF da empresa" required>
                </div>
                <div class="form-group">
                    <label for="rua"><i class="fas fa-road"></i> Rua:</label>
                    <input type="text" id="rua" name="rua" placeholder="Digite o nome da rua" required>
                </div>
                <div class="form-group">
                    <label for="codigoPostal"><i class="fas fa-mail-bulk"></i> Código Postal:</label>
                    <input type="text" id="codigoPostal" name="codigoPostal" placeholder="Digite o código postal" required>
                </div>
                <div class="form-group">
                    <label for="cidade"><i class="fas fa-city"></i> Cidade:</label>
                    <input type="text" id="cidade" name="cidade" placeholder="Digite a cidade" required>
                </div>
                <div class="form-group">
                    <label for="emailEmpresa"><i class="fas fa-envelope"></i> Email da Empresa:</label>
                    <input type="email" id="emailEmpresa" name="emailEmpresa" placeholder="Digite o email da empresa">
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
