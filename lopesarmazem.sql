CREATE DATABASE lopesarmazem;

USE lopesarmazem;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nif VARCHAR(20) NOT NULL,
    nome_da_empresa VARCHAR(100),
    nif_da_empresa VARCHAR(20)
);