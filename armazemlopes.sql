-- Criação da base de dados
CREATE DATABASE IF NOT EXISTS `lopesarmazem` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `lopesarmazem`;

-- Estrutura para tabela usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nif` varchar(20) NOT NULL,
  `nome_da_empresa` varchar(100) DEFAULT NULL,
  `nif_da_empresa` varchar(20) DEFAULT NULL,
  `data_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `telefone` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estrutura para tabela produtos
CREATE TABLE IF NOT EXISTS `produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estrutura para tabela compras
CREATE TABLE IF NOT EXISTS `compras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `data_compra` timestamp NOT NULL DEFAULT current_timestamp(),
  `valor_compra` decimal(10,2) NOT NULL,
  `pontos_ganhos` int(11) DEFAULT 0,
  `status` enum('pendente','enviado','recebido','cancelado') NOT NULL DEFAULT 'pendente',
  `data_cancelamento` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estrutura para tabela enderecos
CREATE TABLE IF NOT EXISTS `enderecos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `rua` varchar(255) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `cidade` varchar(100) NOT NULL,
  `codigo_postal` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `enderecos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estrutura para tabela itens_compra
CREATE TABLE IF NOT EXISTS `itens_compra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `compra_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `compra_id` (`compra_id`),
  KEY `produto_id` (`produto_id`),
  CONSTRAINT `itens_compra_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`),
  CONSTRAINT `itens_compra_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados para tabela produtos
INSERT INTO `produtos` (`id`, `nome`, `descricao`, `preco`, `imagem`, `categoria`) VALUES
	(1, 'Cerveja com Álcool Mini', 'Sagres emb. 30 x 25 cl', 10.50, '/armazemlopes/img/5320624-frente.png', 'bebidasalcool'),
	(2, 'Refrigerante com Gás Cola', 'Coca-Cola emb. 6 x 33 cl', 4.30, '/armazemlopes/img/7748929-frente.jpeg', 'bebidassemalcool'),
	(3, 'Bacalhau Desfiado Congelado', 'Pescanova emb. 850 gr', 10.50, '/armazemlopes/img/7430640-frente.jpg', 'peixecarnecongelado'),
	(4, 'Gelado Cookie Dough', 'Ben & Jerry\'s emb. 465 ml', 4.30, '/armazemlopes/img/7109616-hero.jpg', 'docescongelado'),
	(5, 'Frutos Vermelhos', 'Gelcampo emb. 300 gr', 1.30, '/armazemlopes/img/8172997-frente.jpg', 'outroscongelado'),
	(6, 'Coelho Kg', '', 6.50, '/armazemlopes/img/coelho.png', 'talho'),
	(7, 'Salmão 5/6 Kg', '', 4.30, '/armazemlopes/img/salmao.png', 'peixe'),
	(8, 'Alface Frisada Kg', '', 0.70, '/armazemlopes/img/alface.png', 'legumes'),
	(9, 'Banana Kg', '', 1.00, '/armazemlopes/img/banana.png', 'frutas'),
	(10, 'bacalhau', 'bacalhau', 20.00, '/armazemlopes/img/bacalhau.jpg', 'peixe'),
	(11, 'Óleo Alimentar Fula 1 Lt', 'Óleo Alimentar Fula 1 Lt', 3.00, '/armazemlopes/img/oleofula1l.jpg', 'azeite'),
	(12, 'Azeite Virgem Gallo 2 Lt', 'Azeite Virgem Gallo 2 Lt', 8.00, '/armazemlopes/img/azeitevirgemgalo2lt.jpg', 'azeite'),
	(13, 'Arroz Vaporizado Cigala 1 Kg', 'Arroz Vaporizado Cigala 1 Kg', 1.00, '/armazemlopes/img/21cbef2a71539abc7d8b5047640f7f96.jpg', 'arroz'),
	(14, 'Massa Cotovelos Milaneza 500 G', 'Massa Cotovelos Grossos Milaneza 500 G', 0.60, '/armazemlopes/img/massamilanesa.jpg', 'arroz'),
	(15, 'Leite Mimosa Meio Gordo 1 Lt', 'Leite Uht Mimosa Meio Gordo 1 Lt', 0.60, '/armazemlopes/img/leitegordo.jpg', 'leite'),
	(16, 'IOGURTE ACTIVIA NATURAL 4X115G', 'IOGURTE 000% ACTIVIA NATURAL 4X115G', 2.57, '/armazemlopes/img/activica.jpg', 'iogurte'),
	(31, 'Creme Vegetal Culinária Vaqueiro 1 Kg', 'Creme Vegetal Culinária Vaqueiro 1 Kg', 1.20, '/armazemlopes/img/menteiga.jpg', 'manteiga'),
	(32, 'Natas Uht para Bater Mimosa 1 Lt', 'Natas Uht para Bater Mimosa 1 Lt', 0.60, '/armazemlopes/img/natas.jpg', 'natas'),
	(33, 'Queijo j Brie Le Rustique 200G', 'Queijo j Brie Le Rustique 200G', 2.40, '/armazemlopes/img/queijo.png', 'queijo'),
	(34, 'Ovos Nostrum M 1 Duzia', 'Ovos Nostrum M 1 Duzia', 1.35, '/armazemlopes/img/ovos.jpg', 'ovos'),
	(36, 'Pão Forma Bimbo com Codea 430 G', 'Pão Forma Bimbo com Codea 430 G', 1.05, '/armazemlopes/img/pao.jpg', 'pao'),
	(37, 'Pão de Ló Alvorada 600 G', 'Pão de Ló Alvorada 600 G', 4.50, '/armazemlopes/img/pastelaria.jpg', 'pastelaria');
