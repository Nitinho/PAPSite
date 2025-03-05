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


CREATE TABLE compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_compra TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    valor_compra DECIMAL(10, 2) NOT NULL,
    pontos_ganhos INT DEFAULT 0,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);


CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10, 2) NOT NULL,
    imagem VARCHAR(255),
    categoria VARCHAR(50)
);


INSERT INTO produtos (nome, descricao, preco, imagem, categoria) VALUES
('Pão de Centeio', 'Pão tradicional', 0.50, '../../img/Bolapao.png', 'pao'),
('Pão Integral', 'Feito com farinha integral', 0.60, '../../img/Bolapao.png', 'pao'),
('Pão de Milho', 'Sabor leve de milho', 0.55, '../../img/Bolapao.png', 'pao'),
('Pão de Forma', 'Macio e perfeito para sanduíches', 0.80, '../../img/Bolapao.png', 'pao'),
('Pão Francês', 'Crocante por fora e macio por dentro', 0.40, '../../img/Bolapao.png', 'pao'),
('Pão de Queijo', 'Feito com queijo derretido', 0.70, '../../img/Bolapao.png', 'pao');

