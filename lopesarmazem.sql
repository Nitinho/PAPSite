-- --------------------------------------------------------
-- Anfitrião:                    127.0.0.1
-- Versão do servidor:           10.4.32-MariaDB - mariadb.org binary distribution
-- SO do servidor:               Win64
-- HeidiSQL Versão:              12.10.0.7000
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- A despejar estrutura da base de dados para lopesarmazem
CREATE DATABASE IF NOT EXISTS `lopesarmazem` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `lopesarmazem`;

-- A despejar estrutura para tabela lopesarmazem.compras
CREATE TABLE IF NOT EXISTS `compras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `data_compra` timestamp NOT NULL DEFAULT current_timestamp(),
  `valor_compra` decimal(10,2) NOT NULL,
  `pontos_ganhos` int(11) DEFAULT 0,
  `status` enum('pendente','enviado','recebido','cancelado') NOT NULL DEFAULT 'pendente',
  `data_cancelamento` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A despejar dados para tabela lopesarmazem.compras: ~19 rows (aproximadamente)
INSERT INTO `compras` (`id`, `user_id`, `data_compra`, `valor_compra`, `pontos_ganhos`, `status`, `data_cancelamento`) VALUES
	(1, 1, '2025-04-30 08:29:54', 9.85, 0, 'recebido', NULL),
	(2, 1, '2025-04-30 08:31:12', 4.30, 0, 'recebido', NULL),
	(3, 1, '2025-04-30 08:31:44', 4.30, 0, 'recebido', NULL),
	(4, 1, '2025-04-30 08:31:49', 8.60, 0, 'enviado', NULL),
	(5, 1, '2025-04-30 08:32:19', 12.90, 1, 'recebido', NULL),
	(6, 1, '2025-04-30 08:32:34', 12.90, 1, 'enviado', NULL),
	(7, 1, '2025-04-30 08:36:12', 52.50, 5, 'recebido', NULL),
	(8, 1, '2025-04-30 08:42:35', 52.50, 5, 'recebido', NULL),
	(9, 1, '2025-04-30 08:43:31', 34.40, 3, 'cancelado', NULL),
	(10, 1, '2025-04-30 08:44:17', 5.40, 0, 'cancelado', NULL),
	(11, 2, '2025-04-30 08:47:53', 63.40, 6, 'enviado', NULL),
	(12, 1, '2025-05-06 07:01:02', 8.95, 0, 'recebido', NULL),
	(13, 1, '2025-05-06 07:13:56', 82.10, 8, 'recebido', NULL),
	(14, 1, '2025-05-07 09:29:14', 14.65, 1, 'recebido', NULL),
	(15, 1, '2025-05-08 08:23:29', 216.31, 21, 'recebido', NULL),
	(16, 1, '2025-05-08 09:35:47', 160.00, 16, 'recebido', NULL),
	(17, 4, '2025-05-08 09:53:32', 67.20, 6, 'enviado', NULL),
	(18, 5, '2025-05-12 07:15:31', 23.65, 2, 'pendente', NULL),
	(19, 1, '2025-05-13 10:36:42', 36.35, 3, 'pendente', NULL);

-- A despejar estrutura para tabela lopesarmazem.enderecos
CREATE TABLE IF NOT EXISTS `enderecos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `rua` varchar(255) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `cidade` varchar(100) NOT NULL,
  `codigo_postal` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `enderecos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A despejar dados para tabela lopesarmazem.enderecos: ~5 rows (aproximadamente)
INSERT INTO `enderecos` (`id`, `user_id`, `rua`, `numero`, `cidade`, `codigo_postal`) VALUES
	(1, 1, 'Rua Da Cavadinha', '7', 'Ourem', '2435-260'),
	(2, 2, 'Rua de Ourem', '1', 'Ourem', '2222-213'),
	(3, 3, 'Rua Da Cavadinha', '7', 'Freixianda / Ourem', '2435-260'),
	(4, 4, 'Rua Da Cavadinha', '7', 'Ourem', '2435-260'),
	(5, 5, 'Carvalhal de baixo', '3', 'Ourem', '2435-260'),
	(6, 1, 'Rua Da Cavadinha', '7', 'Ourem', '2435-260');

-- A despejar estrutura para tabela lopesarmazem.itens_compra
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
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A despejar dados para tabela lopesarmazem.itens_compra: ~88 rows (aproximadamente)
INSERT INTO `itens_compra` (`id`, `compra_id`, `produto_id`, `quantidade`, `preco_unitario`, `subtotal`) VALUES
	(1, 1, 36, 1, 1.05, 1.05),
	(2, 1, 37, 1, 4.50, 4.50),
	(3, 1, 4, 1, 4.30, 4.30),
	(4, 2, 2, 1, 4.30, 4.30),
	(5, 3, 2, 1, 4.30, 4.30),
	(6, 4, 2, 2, 4.30, 8.60),
	(7, 5, 2, 3, 4.30, 12.90),
	(8, 6, 2, 3, 4.30, 12.90),
	(9, 7, 1, 5, 10.50, 52.50),
	(10, 8, 1, 5, 10.50, 52.50),
	(11, 9, 2, 4, 4.30, 17.20),
	(12, 9, 4, 4, 4.30, 17.20),
	(13, 10, 15, 3, 0.60, 1.80),
	(14, 10, 31, 3, 1.20, 3.60),
	(15, 11, 6, 9, 6.50, 58.50),
	(16, 11, 8, 7, 0.70, 4.90),
	(17, 12, 36, 1, 1.05, 1.05),
	(18, 12, 2, 1, 4.30, 4.30),
	(19, 12, 15, 6, 0.60, 3.60),
	(20, 13, 36, 4, 1.05, 4.20),
	(21, 13, 2, 3, 4.30, 12.90),
	(22, 13, 6, 10, 6.50, 65.00),
	(23, 14, 36, 1, 1.05, 1.05),
	(24, 14, 37, 1, 4.50, 4.50),
	(25, 14, 2, 1, 4.30, 4.30),
	(26, 14, 15, 8, 0.60, 4.80),
	(27, 15, 2, 1, 4.30, 4.30),
	(28, 15, 1, 1, 10.50, 10.50),
	(29, 15, 36, 1, 1.05, 1.05),
	(30, 15, 37, 1, 4.50, 4.50),
	(31, 15, 4, 1, 4.30, 4.30),
	(32, 15, 55, 1, 3.40, 3.40),
	(33, 15, 56, 1, 3.90, 3.90),
	(34, 15, 59, 1, 3.40, 3.40),
	(35, 15, 58, 1, 3.70, 3.70),
	(36, 15, 57, 1, 3.87, 3.87),
	(37, 15, 60, 1, 4.10, 4.10),
	(38, 15, 3, 1, 10.50, 10.50),
	(39, 15, 62, 1, 7.70, 7.70),
	(40, 15, 64, 1, 5.40, 5.40),
	(41, 15, 5, 1, 1.30, 1.30),
	(42, 15, 63, 1, 2.20, 2.20),
	(43, 15, 65, 1, 1.00, 1.00),
	(44, 15, 11, 1, 3.00, 3.00),
	(45, 15, 12, 1, 8.00, 8.00),
	(46, 15, 13, 1, 1.00, 1.00),
	(47, 15, 14, 1, 0.60, 0.60),
	(48, 15, 15, 1, 0.60, 0.60),
	(49, 15, 16, 1, 2.57, 2.57),
	(50, 15, 31, 1, 1.20, 1.20),
	(51, 15, 32, 1, 0.60, 0.60),
	(52, 15, 33, 1, 2.40, 2.40),
	(53, 15, 34, 1, 1.35, 1.35),
	(54, 15, 6, 1, 6.50, 6.50),
	(55, 15, 38, 1, 18.00, 18.00),
	(56, 15, 39, 1, 2.40, 2.40),
	(57, 15, 40, 1, 3.20, 3.20),
	(58, 15, 7, 1, 4.30, 4.30),
	(59, 15, 10, 1, 20.00, 20.00),
	(60, 15, 41, 1, 8.00, 8.00),
	(61, 15, 44, 1, 6.00, 6.00),
	(62, 15, 43, 1, 6.00, 6.00),
	(63, 15, 42, 1, 6.00, 6.00),
	(64, 15, 8, 1, 0.70, 0.70),
	(65, 15, 45, 1, 4.00, 4.00),
	(66, 15, 46, 1, 3.00, 3.00),
	(67, 15, 49, 1, 6.30, 6.30),
	(68, 15, 48, 1, 4.00, 4.00),
	(69, 15, 47, 1, 4.00, 4.00),
	(70, 15, 9, 1, 1.00, 1.00),
	(71, 15, 50, 1, 3.20, 3.20),
	(72, 15, 51, 1, 2.89, 2.89),
	(73, 15, 52, 1, 5.30, 5.30),
	(74, 15, 53, 1, 2.78, 2.78),
	(75, 15, 54, 1, 2.30, 2.30),
	(76, 16, 12, 20, 8.00, 160.00),
	(77, 17, 36, 4, 1.05, 4.20),
	(78, 17, 1, 6, 10.50, 63.00),
	(79, 18, 36, 1, 1.05, 1.05),
	(80, 18, 12, 1, 8.00, 8.00),
	(81, 18, 11, 1, 3.00, 3.00),
	(82, 18, 4, 1, 4.30, 4.30),
	(83, 18, 55, 1, 3.40, 3.40),
	(84, 18, 56, 1, 3.90, 3.90),
	(85, 19, 36, 9, 1.05, 9.45),
	(86, 19, 6, 1, 6.50, 6.50),
	(87, 19, 38, 1, 18.00, 18.00),
	(88, 19, 39, 1, 2.40, 2.40);

-- A despejar estrutura para tabela lopesarmazem.novidades
CREATE TABLE IF NOT EXISTS `novidades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `descricao` text NOT NULL,
  `data_publicacao` datetime NOT NULL,
  `data_atualizacao` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A despejar dados para tabela lopesarmazem.novidades: ~0 rows (aproximadamente)

-- A despejar estrutura para tabela lopesarmazem.produtos
CREATE TABLE IF NOT EXISTS `produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A despejar dados para tabela lopesarmazem.produtos: ~50 rows (aproximadamente)
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
	(37, 'Pão de Ló Alvorada 600 G', 'Pão de Ló Alvorada 600 G', 4.50, '/armazemlopes/img/pastelaria.jpg', 'pastelaria'),
	(38, 'Bovino Picanha Novillo Real Kg', 'Bovino Picanha Novillo Real', 18.00, '/armazemlopes/img/picanha.jpg', 'talho'),
	(39, 'Porco Bifanas Kg', 'Porco Bifanas KG', 2.40, '/armazemlopes/img/bifana.jpg', 'talho'),
	(40, 'Porco Tiras de Entrecosto Kg', 'Porco Tiras de Entrecosto Kg', 3.20, '/armazemlopes/img/entrecosto.jpg', 'talho'),
	(41, 'Polvo Fresco Pequeno Kg', 'Polvo Fresco Pequeno Kg', 8.00, '/armazemlopes/img/polvo.jpg', 'peixe'),
	(42, 'Robalo +800 G', 'Robalo +800 G', 6.00, '/armazemlopes/img/robalo.jpg', 'peixe'),
	(43, 'Dourada +800 G', 'Dourada +800 G', 6.00, '/armazemlopes/img/dourada.jpg', 'peixe'),
	(44, 'Pescada Fresca Kg', 'Pescada Fresca Kg', 6.00, '/armazemlopes/img/pescadafresca.jpg', 'peixe'),
	(45, 'Batata Vermelha Saco 20 Kg', 'Batata Vermelha Saco 20 Kg', 4.00, '/armazemlopes/img/batata.jpg', 'legumes'),
	(46, 'Tomate Kg', 'Tomate Kg', 3.00, '/armazemlopes/img/tomate.png', 'legumes'),
	(47, 'Cenoura saco 10 Kg', 'Cenoura saco 10 Kg', 4.00, '/armazemlopes/img/cenoura.jpg', 'legumes'),
	(48, 'Pimento Vermelho Kg', 'Pimento Vermelho Kg', 4.00, '/armazemlopes/img/pimentovermelho.jpg', 'legumes'),
	(49, 'Cebola Saco 15 Kg', 'Cebola Saco 15 Kg', 6.30, '/armazemlopes/img/cebola.png', 'legumes'),
	(50, 'Lima Kg', 'Lima', 3.20, '/armazemlopes/img/lima.jpg', 'frutas'),
	(51, 'Limão Kg', 'Limão Kg', 2.89, '/armazemlopes/img/limao.jpg', 'frutas'),
	(52, 'Morangos Caixa Kg', 'Morangos Caixa Kg', 5.30, '/armazemlopes/img/morango.jpg', 'frutas'),
	(53, 'Laranja Kg', 'Laranja Kg', 2.78, '/armazemlopes/img/laranja2.png', 'frutas'),
	(54, 'Maçã Kanzi Kg', 'Maçã Kanzi', 2.30, '/armazemlopes/img/maa.jpg', 'frutas'),
	(55, 'Gelado Mini Maxibon Nata 6', 'Gelado Mini Maxibon Nata 6', 3.40, '/armazemlopes/img/GeladoMiniMaxibonNata6.png', 'docescongelado'),
	(56, 'Gelado Oreo Milka Stick Sandwich 4', 'Gelado Oreo Milka Stick Sandwich 4', 3.90, '/armazemlopes/img/GeladoOreoMilkaStickSandwich4.png', 'docescongelado'),
	(57, 'Gelado Oreo Bombon 3', 'Gelado Oreo Bombon 3', 3.87, '/armazemlopes/img/GeladoOreoBombon3.png', 'docescongelado'),
	(58, 'Gelado Toblerone Mini 6', 'Gelado Toblerone Mini 6', 3.70, '/armazemlopes/img/GeladoTobleroneMini6.png', 'docescongelado'),
	(59, 'Gelado Milka Bombon 3', 'Gelado Milka Bombon 3', 3.40, '/armazemlopes/img/geladomilkabombon.png', 'docescongelado'),
	(60, 'Gelado Twix Cong 6', 'Gelado Twix Cong 6', 4.10, '/armazemlopes/img/GeladoTwixCong6.jpg', 'docescongelado'),
	(61, 'Frango Coxa Qualiko 2,5 Kg', 'Frango Coxa Qualiko 2,5 Kg', 3.40, '/armazemlopes/img/FrangoCoxaQualikoIQFCong25Kg.jpg', 'peixecarnecongelado'),
	(62, 'Polvo Marrocos MasterChef 4 a 6 Kg', 'Polvo Marrocos MasterChef 4 a 6 Kg', 7.70, '/armazemlopes/img/PolvoMarrocosMasterChef4a6Kg.png', 'peixecarnecongelado'),
	(63, 'Batata em Palitos MasterChef 2,5 Kg', 'Batata em Palitos MasterChef 2,5 Kg', 2.20, '/armazemlopes/img/BatataemPalitosM.jpg', 'outroscongelado'),
	(64, 'Preparado Kebab Frango 1Kg', 'Preparado Kebab Frango 1Kg', 5.40, '/armazemlopes/img/kebab.png', 'peixecarnecongelado'),
	(65, 'Gelo Picado Gelito Saco 2 Kg', 'Gelo Picado Gelito Saco 2 Kg', 1.00, '/armazemlopes/img/GeloPicadoGelitoSaco2Kg.jpg', 'outroscongelado');

-- A despejar estrutura para tabela lopesarmazem.users
CREATE TABLE IF NOT EXISTS `users` (
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A despejar dados para tabela lopesarmazem.users: ~5 rows (aproximadamente)
INSERT INTO `users` (`id`, `nome`, `email`, `senha`, `nif`, `nome_da_empresa`, `nif_da_empresa`, `data_registro`, `telefone`) VALUES
	(1, 'Tomas', 'loestomas272@gmail.com', '$2y$10$bp8zA1pDUEXCPePkbJJuWumBJouykcgUlvUC6t33WrRwqxYSCXtoW', '290953545', 'Armazem Lopes', '540902438', '2025-04-30 08:29:26', '912129743'),
	(2, 'Gustavo Reis', 'gustavo@gmail.com', '$2y$10$PtvNrytsVfQ8WGd4DZP6YuX906CzKvlKe.3AwGLax/ZXC7dJnt.xG', '229098592', 'Jardins', '579446930', '2025-04-30 08:47:31', '131 313 131'),
	(3, 'Nicolas', 'Nicolas@gmail.com', '$2y$10$1w4HiIJxDM5jev14EwdNouiJUe67v0R5Z9lVGUfkDZ2JxUPZXlceq', '271393963', 'dasdasdas', '999999999', '2025-04-30 09:28:34', '912129743'),
	(4, 'Anna', 'anna@gmai.com', '$2y$10$db6yZAa6y8CYUj0IBVCFa.RvWg9SuewDvAyQLLHYNiczx1XouzDjq', '246787244', 'Bonecos', '506209555', '2025-05-08 09:51:54', '988 888 111'),
	(5, 'Catarina Silva', 'catarinasilva@gmail.com', '$2y$10$FntpgHdZb1BBY2KvY1l.3OcW2xZXqRGgHpssXB/fKa4sS/4uzBQju', '292710062', 'AmaSilva', '518246574', '2025-05-12 07:12:39', '922 122 122');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
