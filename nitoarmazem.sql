-- --------------------------------------------------------
-- Anfitrião:                    127.0.0.1
-- Versão do servidor:           10.4.32-MariaDB - mariadb.org binary distribution
-- SO do servidor:               Win64
-- HeidiSQL Versão:              12.8.0.6908
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
  `usuario_id` int(11) NOT NULL,
  `data_compra` timestamp NOT NULL DEFAULT current_timestamp(),
  `valor_compra` decimal(10,2) NOT NULL,
  `pontos_ganhos` int(11) DEFAULT 0,
  `status` enum('pendente','enviado','recebido','cancelado') NOT NULL DEFAULT 'pendente',
  `data_cancelamento` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A despejar dados para tabela lopesarmazem.compras: ~7 rows (aproximadamente)
INSERT INTO `compras` (`id`, `usuario_id`, `data_compra`, `valor_compra`, `pontos_ganhos`, `status`, `data_cancelamento`) VALUES
	(1, 1, '2025-03-17 00:04:23', 50.00, 5, 'recebido', NULL),
	(2, 1, '2025-03-17 00:13:40', 6.00, 0, '', NULL),
	(3, 1, '2025-03-18 17:21:02', 15.00, 1, 'cancelado', '2025-03-25 16:44:13'),
	(4, 1, '2025-03-20 17:06:52', 222.40, 22, 'cancelado', NULL),
	(5, 1, '2025-03-25 16:45:22', 38.30, 3, 'enviado', NULL),
	(6, 1, '2025-03-25 16:45:31', 715.00, 71, 'pendente', NULL),
	(7, 2, '2025-03-25 16:56:43', 21.20, 2, 'recebido', NULL),
	(8, 1, '2025-04-01 14:16:22', 4.30, 0, 'pendente', NULL);

-- A despejar estrutura para tabela lopesarmazem.enderecos
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A despejar dados para tabela lopesarmazem.enderecos: ~0 rows (aproximadamente)
INSERT INTO `enderecos` (`id`, `usuario_id`, `rua`, `numero`, `cidade`, `codigo_postal`) VALUES
	(1, 1, 'Rua Da Cavadinha', '7', 'Ourem', '2435-260'),
	(2, 2, 'rua da escola', '21', 'Ourem', '2435-260');

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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A despejar dados para tabela lopesarmazem.itens_compra: ~17 rows (aproximadamente)
INSERT INTO `itens_compra` (`id`, `compra_id`, `produto_id`, `quantidade`, `preco_unitario`, `subtotal`) VALUES
	(1, 1, 1, 100, 0.50, 50.00),
	(2, 2, 1, 4, 1.50, 6.00),
	(3, 3, 1, 10, 1.50, 15.00),
	(4, 4, 1, 14, 1.50, 21.00),
	(5, 4, 2, 15, 0.60, 9.00),
	(6, 4, 15, 13, 4.30, 55.90),
	(7, 4, 14, 13, 10.50, 136.50),
	(8, 5, 1, 1, 1.50, 1.50),
	(9, 5, 2, 2, 0.60, 1.20),
	(10, 5, 3, 2, 0.55, 1.10),
	(11, 5, 15, 3, 4.30, 12.90),
	(12, 5, 17, 2, 4.30, 8.60),
	(13, 5, 19, 2, 6.50, 13.00),
	(14, 6, 19, 110, 6.50, 715.00),
	(15, 7, 1, 1, 1.50, 1.50),
	(16, 7, 2, 1, 0.60, 0.60),
	(17, 7, 15, 1, 4.30, 4.30),
	(18, 7, 14, 1, 10.50, 10.50),
	(19, 7, 17, 1, 4.30, 4.30),
	(20, 8, 15, 1, 4.30, 4.30);

-- A despejar estrutura para tabela lopesarmazem.produtos
CREATE TABLE IF NOT EXISTS `produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A despejar dados para tabela lopesarmazem.produtos: ~29 rows (aproximadamente)
INSERT INTO `produtos` (`id`, `nome`, `descricao`, `preco`, `imagem`, `categoria`) VALUES
	(1, 'Pão de Teste', 'Pão tradicional', 1.50, '/PAPSite/img/produto_1742170389.png', 'pao'),
	(2, 'Pão Integral', 'Feito com farinha integral', 0.60, '/PAPSite/img/Bolapao.png', 'pao'),
	(3, 'Pão de Milho', 'Sabor leve de milho', 0.55, '/PAPSite/img/Bolapao.png', 'pao'),
	(4, 'Pão de Forma', 'Macio e perfeito para sanduíches', 0.80, '/PAPSite/img/Bolapao.png', 'pao'),
	(5, 'Pão Francês', 'Crocante por fora e macio por dentro', 0.40, '/PAPSite/img/Bolapao.png', 'pao'),
	(6, 'Pão de Queijo', 'Feito com queijo derretido', 0.70, '/PAPSite/img/Bolapao.png', 'pao'),
	(7, 'Pao Teste2', 'Pao teste', 1.70, '/PAPSite/img/produto_1742170569.png', 'pao'),
	(8, 'Baguete Tradicional', 'Pão tradicional', 0.50, '/PAPSite/img/pao2.png', 'baguete'),
	(9, 'Baguete Integral', 'Feito com farinha integral', 0.60, '/PAPSite/img/pao2.png', 'baguete'),
	(12, 'Baguete Francesa', 'Crocante por fora e macio por dentro', 0.40, '/PAPSite/img/pao2.png', 'baguete'),
	(14, 'Cerveja com Álcool Mini', 'Sagres emb. 30 x 25 cl', 10.50, '/PAPSite/img/5320624-frente.png', 'bebidasalcool'),
	(15, 'Refrigerante com Gás Cola', 'Coca-Cola emb. 6 x 33 cl', 4.30, '/PAPSite/img/7748929-frente.jpeg', 'bebidassemalcool'),
	(16, 'Bacalhau Desfiado Congelado', 'Pescanova emb. 850 gr', 10.50, '/PAPSite/img/7430640-frente.jpg', 'peixecarnecongelado'),
	(17, 'Gelado Cookie Dough', 'Ben & Jerry\'s emb. 465 ml', 4.30, '/PAPSite/img/7109616-hero.jpg', 'docescongelado'),
	(18, 'Frutos Vermelhos', 'Gelcampo emb. 300 gr', 1.30, '/PAPSite/img/8172997-frente.jpg', 'outroscongelado'),
	(19, 'Coelho Kg', '', 6.50, '/PAPSite/img/coelho.png', 'talho'),
	(20, 'Salmão 5/6 Kg', '', 4.30, '/PAPSite/img/salmao.png', 'peixe'),
	(21, 'Alface Frisada Kg', '', 0.70, '/PAPSite/img/alface.png', 'legumes'),
	(22, 'Banana Kg', '', 1.00, '/PAPSite/img/banana.png', 'frutas'),
	(23, 'Bacalhau Corrente Noruega MasterChef Caixa 15Kg', 'bacalhau', 20.00, '/PAPSite/img/bacalhau.jpg', 'peixe'),
	(24, 'Peixe Espada Preto Kg', '', 9.00, '/PAPSite/img/preto.jpg', 'peixe'),
	(25, 'Dourada 400/600 G', '', 9.30, '/PAPSite/img/dourada.jpg', 'peixe'),
	(26, 'Porco Tiras de Entrecosto', '', 4.50, '/PAPSite/img/entrecosto.jpg', 'talho'),
	(27, 'Porco Bifanas KG', '', 3.50, '/PAPSite/img/bifana.jpg', 'talho'),
	(28, 'Porco Cachaço com Osso KG', '', 4.49, '/PAPSite/img/cachaso.jpg', 'talho'),
	(29, 'Cenoura 25/40 Saco 10 Kg', '', 4.50, '/PAPSite/img/cenoura.jpg', 'legumes'),
	(30, 'Batata Ganderesa Saco 15 Kg', '', 8.00, '/PAPSite/img/batata.jpg', 'legumes'),
	(31, 'Morango Caixa 3 Kg', '', 8.20, '/PAPSite/img/morango.jpg', 'frutas'),
	(33, 'Laranja Caixa 3KG', '', 4.00, '/PAPSite/img/laranja.jpg', 'frutas'),
	(34, 'Pêra Rocha Caixa 3KG', '', 4.00, '/PAPSite/img/perarocha.jpg', 'frutas');

-- A despejar estrutura para tabela lopesarmazem.support_messages
CREATE TABLE IF NOT EXISTS `support_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `sender` enum('user','admin') NOT NULL,
  `message` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  CONSTRAINT `support_messages_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A despejar dados para tabela lopesarmazem.support_messages: ~2 rows (aproximadamente)
INSERT INTO `support_messages` (`id`, `ticket_id`, `sender`, `message`, `sent_at`) VALUES
	(1, 1, 'user', 'sadasdada', '2025-03-19 13:12:31'),
	(2, 1, 'admin', 'olaaaaa', '2025-03-19 13:12:41'),
	(3, 1, 'user', 'dasdada', '2025-03-19 13:12:50');

-- A despejar estrutura para tabela lopesarmazem.support_tickets
CREATE TABLE IF NOT EXISTS `support_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `closed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A despejar dados para tabela lopesarmazem.support_tickets: ~0 rows (aproximadamente)
INSERT INTO `support_tickets` (`id`, `user_id`, `subject`, `status`, `created_at`, `closed_at`) VALUES
	(1, 1, 'teste', 'open', '2025-03-19 13:12:31', NULL);

-- A despejar estrutura para tabela lopesarmazem.usuarios
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A despejar dados para tabela lopesarmazem.usuarios: ~0 rows (aproximadamente)
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `nif`, `nome_da_empresa`, `nif_da_empresa`, `data_registro`, `telefone`) VALUES
	(1, 'admin@admin', 'admin@admin', '$2y$10$3.sm7dl8/5W1U4sYjEUQFOtVRigg/vQ8stxOCPASqrAVf1G9s.FZK', '111111111', 'admin@admin', '11111111', '2025-03-16 23:58:53', '912129743'),
	(2, 'professora', 'professora@gmail.com', '$2y$10$/VSWrbXlKPdELeSTivaHkugIs84.pLnGo3VkK/G9yEqAXbO77/.eW', '1111111111', 'professora', '1222121211211', '2025-03-25 16:55:05', '9111111111111');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
