<?php
// Configurações do banco de dados
define('DB_SERVER', 'localhost');  // Endereço do servidor (localhost para ambiente local)
define('DB_USERNAME', 'root');     // Usuário do banco de dados
define('DB_PASSWORD', '');         // Senha do banco de dados
define('DB_NAME', 'lopesarmazem'); // Nome do banco de dados

// Criar a conexão com o banco de dados
function getDBConnection() {
    try {
        // Usando PDO para garantir a segurança na conexão
        $conn = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        echo "Erro ao conectar ao banco de dados: " . $e->getMessage();
        exit();
    }
}
?>
