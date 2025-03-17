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
  `usuario_id` int(11) NOT NULL,
  `data_compra` timestamp NOT NULL DEFAULT current_timestamp(),
  `valor_compra` decimal(10,2) NOT NULL,
  `pontos_ganhos` int(11) DEFAULT 0,
  `status` enum('pendente','enviado','recebido') NOT NULL DEFAULT 'pendente',
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A despejar dados para tabela lopesarmazem.compras: ~0 rows (aproximadamente)
INSERT INTO `compras` (`id`, `usuario_id`, `data_compra`, `valor_compra`, `pontos_ganhos`, `status`) VALUES
	(1, 1, '2025-03-17 00:04:23', 50.00, 5, 'recebido'),
	(2, 1, '2025-03-17 00:13:40', 6.00, 0, 'pendente');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A despejar dados para tabela lopesarmazem.enderecos: ~1 rows (aproximadamente)
INSERT INTO `enderecos` (`id`, `usuario_id`, `rua`, `numero`, `cidade`, `codigo_postal`) VALUES
	(1, 1, 'Rua Da Cavadinha', '7', 'Ourem', '2435-260');

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A despejar dados para tabela lopesarmazem.itens_compra: ~0 rows (aproximadamente)
INSERT INTO `itens_compra` (`id`, `compra_id`, `produto_id`, `quantidade`, `preco_unitario`, `subtotal`) VALUES
	(1, 1, 1, 100, 0.50, 50.00),
	(2, 2, 1, 4, 1.50, 6.00);

-- A despejar estrutura para tabela lopesarmazem.produtos
CREATE TABLE IF NOT EXISTS `produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A despejar dados para tabela lopesarmazem.produtos: ~6 rows (aproximadamente)
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
	(10, 'Baguete de Milho', 'Sabor leve de milho', 0.55, '/PAPSite/img/pao2.png', 'baguete'),
	(11, 'Baguete de Forma', 'Macio e perfeito para sanduíches', 0.80, '/PAPSite/img/pao2.png', 'baguete'),
	(12, 'Baguete Francesa', 'Crocante por fora e macio por dentro', 0.40, '/PAPSite/img/pao2.png', 'baguete'),
	(13, 'Baguete de Queijo', 'Feito com queijo derretido', 0.70, '/PAPSite/img/pao2.png', 'baguete');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A despejar dados para tabela lopesarmazem.usuarios: ~1 rows (aproximadamente)
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `nif`, `nome_da_empresa`, `nif_da_empresa`, `data_registro`, `telefone`) VALUES
	(1, 'admin@admin', 'admin@admin', '$2y$10$3.sm7dl8/5W1U4sYjEUQFOtVRigg/vQ8stxOCPASqrAVf1G9s.FZK', '111111111', 'admin@admin', '11111111', '2025-03-16 23:58:53', '912129743');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
