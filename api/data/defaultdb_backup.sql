/*
 Navicat Premium Data Transfer

 Source Server         : aiven
 Source Server Type    : MySQL
 Source Server Version : 80408 (8.4.8)
 Source Host           : mysql-28528e70-mileymamani09-ede5.d.aivencloud.com:17706
 Source Schema         : defaultdb

 Target Server Type    : MySQL
 Target Server Version : 80408 (8.4.8)
 File Encoding         : 65001

 Date: 28/10/2026 17:48:09
*/
USE pasteleria_kivon;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for CLIENTE
-- ----------------------------
DROP TABLE IF EXISTS `CLIENTE`;
CREATE TABLE `CLIENTE`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(50)  NOT NULL,
  `Apellidos` varchar(100) NULL DEFAULT NULL,
  `CI` varchar(30)  NOT NULL,
  `Telefono` varchar(20)  NULL DEFAULT NULL,
  `Direccion` varchar(250)  NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8;

-- ----------------------------
-- Records of CLIENTE
-- ----------------------------
INSERT INTO `CLIENTE` VALUES (1, 'Juan', 'Carlos', '12345678', '70000000', 'Santa Cruz');
INSERT INTO `CLIENTE` VALUES (2, 'Carlos', 'Mendoza Quispe', '7654321 SC', '60598744', 'Calle Murillo #450, Zona Central');
INSERT INTO `CLIENTE` VALUES (3, 'Mariana', 'Gutiérrez Rocha', '9876542 CB', '72011223', 'Av. América, Condominio El Sol');
INSERT INTO `CLIENTE` VALUES (4, 'José Luis', 'Mamani Vargas', '4567891 OR', '68541255', 'Av. 6 de Agosto, Nro 120');
INSERT INTO `CLIENTE` VALUES (5, 'Elena', 'Torrico Suárez', '3456782 TJ', '75099881', 'Barrio San Gerónimo, Calle Los Lapachos');
INSERT INTO `CLIENTE` VALUES (6, 'Mile', 'Mam', '12345678', '70000000', 'Santa Cruz');

-- ----------------------------
-- Table structure for PEDIDO
-- ----------------------------
DROP TABLE IF EXISTS `PEDIDO`;
CREATE TABLE `PEDIDO`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `Estado` varchar(80)  NOT NULL,
  `Fecha_registro` datetime NULL DEFAULT NULL,
  `Detalle` varchar(255)  NULL DEFAULT NULL,
  `Metodo_de_pago` varchar(20)  NULL DEFAULT NULL,
  `Fecha_entrega` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_cliente` int NULL DEFAULT NULL,
  `id_personal` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `FK_PEDIDO_CLIENTE`(`id_cliente` ASC) USING BTREE,
  INDEX `FK_PEDIDO_PERSONAL`(`id_personal` ASC) USING BTREE,
  CONSTRAINT `FK_PEDIDO_CLIENTE` FOREIGN KEY (`id_cliente`) REFERENCES `CLIENTE` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_PEDIDO_PERSONAL` FOREIGN KEY (`id_personal`) REFERENCES `PERSONAL` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 12;

-- ----------------------------
-- Records of PEDIDO
-- ----------------------------
INSERT INTO `PEDIDO` VALUES (1, 'Entregado', NULL, 'Torta de tres leches con dedicatoria: Feliz Cumpleaños Mamá', NULL, '2026-08-26 19:12:00', 4, 2);
INSERT INTO `PEDIDO` VALUES (2, 'Listo para entrega', NULL, 'Pastel de chocolate bajo en azúcar', NULL, '2026-08-26 19:12:06', 2, 2);
INSERT INTO `PEDIDO` VALUES (3, 'Listo para entrega', NULL, 'Pie de limón y una docena de cupcakes para las 15:00', NULL, '2026-08-26 19:12:06', 3, 3);
INSERT INTO `PEDIDO` VALUES (4, 'Cancelado', NULL, 'El cliente solicitó anular el pedido de cheesecake', NULL, '2026-08-26 19:12:06', 4, 2);
INSERT INTO `PEDIDO` VALUES (5, 'Entregado', NULL, 'Pedido express de 2 tortas de tres leches', NULL, '2026-08-26 19:12:00', 5, 3);
INSERT INTO `PEDIDO` VALUES (7, 'En elaboración', NULL, 'Torta de chocolate para cumpleaños', NULL, '2026-09-04 20:22:39', 1, 1);
INSERT INTO `PEDIDO` VALUES (8, 'En elaboración', NULL, 'Torta de chocolate para cumpleaños', NULL, '2026-10-09 18:33:01', 1, 1);
INSERT INTO `PEDIDO` VALUES (9, 'En elaboración', NULL, NULL, 'Efectivo', '2026-10-14 20:45:00', 5, 4);
INSERT INTO `PEDIDO` VALUES (10, 'Pendiente', '2026-10-16 21:54:34', 'sin harina ni azucar', 'QR', '2026-10-22 17:54:00', 5, 4);
INSERT INTO `PEDIDO` VALUES (11, 'Pendiente', '2026-10-23 12:29:44', NULL, 'QR', '2026-10-10 08:29:00', 3, 2);

-- ----------------------------
-- Table structure for PERSONAL
-- ----------------------------
DROP TABLE IF EXISTS `PERSONAL`;
CREATE TABLE `PERSONAL`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(50)  NOT NULL,
  `Cargo` varchar(100) NOT NULL,
  `Telefono` varchar(50)  NULL DEFAULT NULL,
  `Salario` decimal(10, 2) NOT NULL,
  `id_usuario` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `FK_PERSONAL_USUARIO`(`id_usuario` ASC) USING BTREE,
  CONSTRAINT `FK_PERSONAL_USUARIO` FOREIGN KEY (`id_usuario`) REFERENCES `USUARIO` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 8;

-- ----------------------------
-- Records of PERSONAL
-- ----------------------------
INSERT INTO `PERSONAL` VALUES (1, 'Miley', 'Gerente General', '77255443', 6500.00, 12);
INSERT INTO `PERSONAL` VALUES (2, 'Joshe Chavez', 'Cajero Principal', '65122334', 2800.00, 13);
INSERT INTO `PERSONAL` VALUES (3, 'Lucas Mateo', 'Cajero Turno Tarde', '70144552', 2800.00, 15);
INSERT INTO `PERSONAL` VALUES (4, 'Yelim', 'Maestro Pastelero', '60211447', 4200.00, 14);
INSERT INTO `PERSONAL` VALUES (5, 'Pedro Alí', 'Repartidor', '78566331', 2500.00, NULL);
INSERT INTO `PERSONAL` VALUES (7, 'Personal Prueba', 'Vendedor', '70000000', 2500.00, NULL);

-- ----------------------------
-- Table structure for PRODUCTO
-- ----------------------------
DROP TABLE IF EXISTS `PRODUCTO`;
CREATE TABLE `PRODUCTO`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(80)  NOT NULL,
  `Precio` decimal(10, 2) NOT NULL,
  `Cantidad` int NOT NULL,
  `Ingrediente` varchar(255)  NULL DEFAULT NULL,
  `Imagen` varchar(255)  NULL DEFAULT NULL,
  `Fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_personal` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `FK_PRODUCTO_PERSONAL`(`id_personal` ASC) USING BTREE,
  CONSTRAINT `FK_PRODUCTO_PERSONAL` FOREIGN KEY (`id_personal`) REFERENCES `PERSONAL` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 13 ;

-- ----------------------------
-- Records of PRODUCTO
-- ----------------------------
INSERT INTO `PRODUCTO` VALUES (1, 'Torta de Tres Leches Tradicional', 120.00, 15, 'Leche evaporada, crema de leche, leche condensada, harina, huevos', 'uploads/products/d803e97bd12a5a5fb177eb9975a04779.jpg', '2026-08-26 19:12:06', 1);
INSERT INTO `PRODUCTO` VALUES (2, 'Torta de Oreo', 122.00, 5, 'Oreo, leche, azucar', 'uploads/products/066f9a1cd142e81952a85154f155ed9f.jpg', '2026-06-12 00:00:00', 1);
INSERT INTO `PRODUCTO` VALUES (3, 'Pie de Limón Mediano', 65.00, 20, 'Zumo de limón, base de galleta, merengue italiano', NULL, '2026-08-26 19:12:06', 1);
INSERT INTO `PRODUCTO` VALUES (4, 'Cheesecake de Frutilla', 140.00, 8, 'Queso crema, mermelada de frutilla, base de galleta dulce', 'uploads/products/5fad73e6f3df48bcbaba5723e09baaaa.jpg', '2026-08-26 19:12:06', 1);
INSERT INTO `PRODUCTO` VALUES (5, 'Cupcake de Vainilla (Docena)', 45.00, 25, 'Harina, azúcar, esencia de vainilla, chispas de colores', NULL, '2026-08-26 19:12:06', 1);
INSERT INTO `PRODUCTO` VALUES (6, 'Torta de Tres Leches Tradicional', 120.00, 15, 'Leche evaporada, crema de leche, leche condensada, harina, huevos', NULL, '2026-08-26 19:13:34', 1);
INSERT INTO `PRODUCTO` VALUES (7, 'Pastel de Chocolate Supremo', 150.00, 10, 'Chocolate amargo 70%, harina, mantequilla, esencia de vainilla', 'uploads/products/684b8f49889e6b002a0235891d9ea484.jpg', '2026-08-26 19:13:34', 1);
INSERT INTO `PRODUCTO` VALUES (8, 'Pie de Limón Mediano', 65.00, 20, 'Zumo de limón, base de galleta, merengue italiano', NULL, '2026-08-26 19:13:34', 1);
INSERT INTO `PRODUCTO` VALUES (9, 'Cheesecake de Frutilla', 140.00, 8, 'Queso crema, mermelada de frutilla, base de galleta dulce', 'uploads/products/bd80b9a7c889be96b434344d09d7e63c.jpg', '2026-08-26 19:13:34', 1);
INSERT INTO `PRODUCTO` VALUES (10, 'Cupcake de Vainilla (Docena)', 45.00, 25, 'Harina, azúcar, esencia de vainilla, chispas de colores', NULL, '2026-08-26 19:13:34', 1);
INSERT INTO `PRODUCTO` VALUES (11, 'Torta de leche', 6.00, 3, 'leche,huevo', NULL, '2026-10-14 19:07:29', 4);
INSERT INTO `PRODUCTO` VALUES (12, 'Torta de Coco', 100.00, 7, 'Harina, huevo, coco y vainilla', 'uploads/products/6bea8944f7dc5146c470f6f99a5588cc.jpg', '2026-10-22 18:54:33', NULL);

-- ----------------------------
-- Table structure for PRODUCTO_PEDIDO
-- ----------------------------
DROP TABLE IF EXISTS `PRODUCTO_PEDIDO`;
CREATE TABLE `PRODUCTO_PEDIDO`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_producto` int NULL DEFAULT NULL,
  `id_pedido` int NULL DEFAULT NULL,
  `Cantidad` int NOT NULL,
  `Precio_unitario` decimal(10, 2) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `FK_PRODUCTO_PEDIDO_PRODUCTO`(`id_producto` ASC) USING BTREE,
  INDEX `FK_PRODUCTO_PEDIDO_PEDIDO`(`id_pedido` ASC) USING BTREE,
  CONSTRAINT `FK_PRODUCTO_PEDIDO_PEDIDO` FOREIGN KEY (`id_pedido`) REFERENCES `PEDIDO` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_PRODUCTO_PEDIDO_PRODUCTO` FOREIGN KEY (`id_producto`) REFERENCES `PRODUCTO` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 15;

-- ----------------------------
-- Records of PRODUCTO_PEDIDO
-- ----------------------------
INSERT INTO `PRODUCTO_PEDIDO` VALUES (2, 2, 2, 1, 150.00);
INSERT INTO `PRODUCTO_PEDIDO` VALUES (3, 3, 3, 1, 65.00);
INSERT INTO `PRODUCTO_PEDIDO` VALUES (4, 5, 3, 1, 45.00);
INSERT INTO `PRODUCTO_PEDIDO` VALUES (8, 1, 1, 1, 120.00);
INSERT INTO `PRODUCTO_PEDIDO` VALUES (9, 1, 5, 3, 120.00);
INSERT INTO `PRODUCTO_PEDIDO` VALUES (11, 4, 10, 2, 140.00);
INSERT INTO `PRODUCTO_PEDIDO` VALUES (12, 10, 10, 4, 45.00);
INSERT INTO `PRODUCTO_PEDIDO` VALUES (13, 6, 9, 3, 120.00);
INSERT INTO `PRODUCTO_PEDIDO` VALUES (14, 9, 11, 4, 140.00);

-- ----------------------------
-- Table structure for USUARIO
-- ----------------------------
DROP TABLE IF EXISTS `USUARIO`;
CREATE TABLE `USUARIO`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `Username` varchar(50)  NOT NULL,
  `Permiso` varchar(100)  NOT NULL,
  `Password_hash` varchar(255) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 17 ;

-- ----------------------------
-- Records of USUARIO
-- ----------------------------
INSERT INTO `USUARIO` VALUES (12, 'Miley', 'Administrador', '$2y$10$9.xp77xumffjiga2ZTsWHeGOTjNfSwlGTesE/WtO0Dm4jIF7Yu006');
INSERT INTO `USUARIO` VALUES (13, 'Joshe', 'Ventas', '$2y$10$D6ssamkHXWeCG26BF1xnZOgAypkf0y8XLwWlo3RJyFSPXOoaV3Yba');
INSERT INTO `USUARIO` VALUES (14, 'Yelim', 'Produccion', '$2y$10$x1F6JOGjh4LdA7Z7PGXeK.vbAMuMBgoZkJC5zH.f0Wrq2LM3b3sai');
INSERT INTO `USUARIO` VALUES (15, 'usuario_prueba2', 'Administrador', '$2y$10$3tGBsjsiFvOqDQ3/AhE7Uuqkd5Jdfm8ALB5fhKUT/ocl0OZjQe1ke');

-- ----------------------------
-- Table structure for VENTA
-- ----------------------------
DROP TABLE IF EXISTS `VENTA`;
CREATE TABLE `VENTA`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `Fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Total` decimal(10, 2) NOT NULL,
  `Metodo_de_pago` varchar(255)  NOT NULL,
  `id_personal` int NULL DEFAULT NULL,
  `id_pedido` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `FK_VENTA_PEDIDO`(`id_pedido` ASC) USING BTREE,
  INDEX `FK_VENTA_PERSONAL`(`id_personal` ASC) USING BTREE,
  CONSTRAINT `FK_VENTA_PEDIDO` FOREIGN KEY (`id_pedido`) REFERENCES `PEDIDO` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_VENTA_PERSONAL` FOREIGN KEY (`id_personal`) REFERENCES `PERSONAL` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 8;

-- ----------------------------
-- Records of VENTA
-- ----------------------------
INSERT INTO `VENTA` VALUES (1, '2026-08-26 19:12:06', 120.00, 'Efectivo', 1, 1);
INSERT INTO `VENTA` VALUES (2, '2026-08-26 19:12:06', 150.00, 'Transferencia QR', 2, 2);
INSERT INTO `VENTA` VALUES (3, '2026-08-26 19:12:06', 110.00, 'Tarjeta de Débito', 3, 3);
INSERT INTO `VENTA` VALUES (4, '2026-08-26 19:12:06', 140.00, 'Efectivo', 4, NULL);
INSERT INTO `VENTA` VALUES (5, '2026-08-26 19:12:06', 240.00, 'Transferencia QR', 5, 5);
INSERT INTO `VENTA` VALUES (7, '2026-10-22 21:45:20', 360.00, 'Tarjeta de credito', 4, 9);

SET FOREIGN_KEY_CHECKS = 1;
