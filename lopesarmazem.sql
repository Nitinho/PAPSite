CREATE DATABASE lopesarmazem;

USE lopesarmazem;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nif VARCHAR(20) NOT NULL,
    nome_da_empresa VARCHAR(100),
    nif_da_empresa VARCHAR(20),
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE enderecos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    rua VARCHAR(255) NOT NULL,
    numero VARCHAR(20) NOT NULL,
    complemento VARCHAR(100),
    cidade VARCHAR(100) NOT NULL,
    codigo_postal VARCHAR(20) NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);


-- Cria a tabela "produtos"
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY, -- ID único para cada produto
    nome VARCHAR(100) NOT NULL,        -- Nome do produto
    preco DECIMAL(10, 2) NOT NULL,     -- Preço do produto (com 2 casas decimais)
    imagem VARCHAR(255) NOT NULL      -- Caminho da imagem do produto
);


INSERT INTO produtos (nome, preco, imagem) VALUES
('Pão', 0.50, '../../img/Bolapao.png'),
('Pão de Centeio', 1.00, '../../img/Bolapao.png'),
('Pão Integral', 1.20, '../../img/Bolapao.png'),
('Pão de Água', 0.80, '../../img/Bolapao.png');
