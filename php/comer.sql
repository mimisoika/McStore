-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: comercializadora
-- ------------------------------------------------------
-- Server version	5.7.44-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `carrito`
--

DROP TABLE IF EXISTS `carrito`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carrito` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT '1',
  `fecha_agregado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_producto` (`usuario_id`,`producto_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrito`
--

LOCK TABLES `carrito` WRITE;
/*!40000 ALTER TABLE `carrito` DISABLE KEYS */;
INSERT INTO `carrito` VALUES (1,2,1,1,'2025-11-11 17:36:24'),(2,2,3,2,'2025-11-11 17:36:24'),(3,3,5,1,'2025-11-11 17:36:24'),(8,1,10,1,'2025-11-21 00:35:43'),(9,1,9,1,'2025-11-21 00:37:48'),(10,4,8,1,'2025-11-21 04:03:14'),(11,4,7,1,'2025-11-21 04:13:18'),(12,1,7,1,'2025-11-21 05:54:21'),(13,1,6,1,'2025-11-21 06:11:24'),(14,6,1,1,'2025-11-26 15:22:49');
/*!40000 ALTER TABLE `carrito` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrusel_imagenes`
--

DROP TABLE IF EXISTS `carrusel_imagenes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carrusel_imagenes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `imagen_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` int(11) DEFAULT '0',
  `activa` tinyint(1) DEFAULT '1',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrusel_imagenes`
--

LOCK TABLES `carrusel_imagenes` WRITE;
/*!40000 ALTER TABLE `carrusel_imagenes` DISABLE KEYS */;
INSERT INTO `carrusel_imagenes` VALUES (1,'MC','Compra mas','pages/img/slider/slide_1763337249_4702.jpg',1,1,'2025-11-16 22:54:09'),(2,'Prueba carrucel','','pages/img/slider/slide_1763789369_2114.jpg',2,1,'2025-11-22 05:29:30');
/*!40000 ALTER TABLE `carrusel_imagenes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (1,'Electrónicos','Dispositivos electrónicos y gadgets'),(2,'Hogar','Artículos para el hogar'),(3,'Oficina','Productos de oficina y papelería');
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuraciones`
--

DROP TABLE IF EXISTS `configuraciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuraciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_sitio` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'MC Store',
  `logo_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pages/img/logo-mcstore.png',
  `color_primario` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#0066CC',
  `color_secundario` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#333333',
  `color_encabezado` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#FFFFFF',
  `color_texto` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#000000',
  `texto_nosotros` longtext COLLATE utf8mb4_unicode_ci,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `horarios` text COLLATE utf8mb4_unicode_ci,
  `facebook` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuraciones`
--

LOCK TABLES `configuraciones` WRITE;
/*!40000 ALTER TABLE `configuraciones` DISABLE KEYS */;
INSERT INTO `configuraciones` VALUES (1,'MC Store','pages/img/logo-mcstore.png','#0066CC','#333333','#FFFFFF','#000000','Somos comercializadora MC, una tienda que se especializa en la venta de productos para panaderia y reposteria','Laguna','6131006787','kominomisenpai098@gmail.com','','','','162632163127','2025-11-24 15:17:43');
/*!40000 ALTER TABLE `configuraciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_pedido`
--

DROP TABLE IF EXISTS `detalles_pedido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalles_pedido` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `nombre_producto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_id` (`pedido_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `detalles_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalles_pedido_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_pedido`
--

LOCK TABLES `detalles_pedido` WRITE;
/*!40000 ALTER TABLE `detalles_pedido` DISABLE KEYS */;
INSERT INTO `detalles_pedido` VALUES (1,1,1,'Smartphone X',1,8999.99,8999.99),(2,1,3,'Juego de sábanas',2,899.00,1798.00),(3,2,5,'Laptop Elite',1,18999.00,18999.00);
/*!40000 ALTER TABLE `detalles_pedido` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `direcciones`
--

DROP TABLE IF EXISTS `direcciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `direcciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `alias` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre para identificar la dirección (ej: Casa, Trabajo)',
  `direccion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ciudad` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_postal` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pais` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'México',
  `instrucciones_entrega` text COLLATE utf8mb4_unicode_ci COMMENT 'Instrucciones especiales para el repartidor',
  `es_principal` tinyint(1) DEFAULT '0' COMMENT 'Indica si es la dirección principal del usuario',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `direcciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `direcciones`
--

LOCK TABLES `direcciones` WRITE;
/*!40000 ALTER TABLE `direcciones` DISABLE KEYS */;
INSERT INTO `direcciones` VALUES (1,1,'Casa','Av. Insurgentes 123, Col. Condesa','Ciudad de México','06140','CDMX','México',NULL,0,'2025-11-11 17:36:24'),(2,2,'Oficina','Paseo de la Reforma 505, Piso 12','Ciudad de México','06500','CDMX','México',NULL,1,'2025-11-11 17:36:24'),(3,3,'Casa','Calzada Guadalupe 456, Col. Lindavista','Ciudad de México','07300','CDMX','México',NULL,1,'2025-11-11 17:36:24'),(4,1,'Negocio','ninguna','Constitucion','23670','B.C.S.','México','',1,'2025-11-20 19:30:22'),(5,5,'Casa','Colonia infonavit int.24','Insurgentes','23700','Baja california sur','México','Esta blanca la casa y tiene piso',0,'2025-11-22 06:23:57');
/*!40000 ALTER TABLE `direcciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `direccion_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','confirmado','preparando','en_camino','entregado','cancelado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `metodo_pago` enum('efectivo','tarjeta','transferencia') COLLATE utf8mb4_unicode_ci DEFAULT 'efectivo',
  `fecha_pedido` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `direccion_id` (`direccion_id`),
  CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`direccion_id`) REFERENCES `direcciones` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedidos`
--

LOCK TABLES `pedidos` WRITE;
/*!40000 ALTER TABLE `pedidos` DISABLE KEYS */;
INSERT INTO `pedidos` VALUES (1,1,1,10797.99,'entregado','tarjeta','2025-11-11 17:36:24'),(2,3,3,18999.00,'preparando','transferencia','2025-11-11 17:36:24');
/*!40000 ALTER TABLE `pedidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria_id` int(11) DEFAULT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `precio` decimal(10,2) NOT NULL,
  `cantidad` int(11) DEFAULT '0' COMMENT 'Cantidad en stock',
  `estado` enum('disponible','agotado','suspendido','poco_stock') COLLATE utf8mb4_unicode_ci DEFAULT 'disponible',
  `imagen` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destacado` enum('si','no') COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,1,'Smartphone X','Teléfono inteligente última generación',8999.99,50,'disponible',NULL,'si','2025-11-11 17:36:24'),(2,1,'Tablet Pro','Tablet de 10 pulgadas con stylus',4599.50,30,'disponible','1763761878_all-matepad-11-5-s.jpg','si','2025-11-11 17:36:24'),(3,2,'Juego de sábanas','Juego de sábanas de algodón king size',899.00,0,'disponible','1763761835_images (16).jfif','si','2025-11-11 17:36:24'),(4,3,'Paquete de hojas','Resma de 500 hojas tamaño carta',120.00,0,'disponible','1763761814_83143.jfif','si','2025-11-11 17:36:24'),(5,1,'Laptop Elite','Laptop profesional i7 16GB RAM',18999.00,0,'poco_stock','1763761761_images (17).jfif','si','2025-11-11 17:36:24'),(6,1,'Auriculares Inalámbricos','Auriculares Bluetooth con cancelación de ruido',1499.00,0,'disponible','1763761717_51vPAar1+JL._AC_UF1000,1000_QL80_.jpg','si','2025-11-13 21:17:43'),(7,1,'Smartwatch Fit','Reloj inteligente resistente al agua',2299.00,0,'disponible','1763761693_515ftw-100214954-4.jpg','si','2025-11-13 21:17:43'),(8,2,'Cobija Suave','Cobija térmica de microfibra',699.00,0,'disponible','1763761642_images (16).jfif','si','2025-11-13 21:17:43'),(9,2,'Cojín Decorativo','Cojín cuadrado con diseño moderno',299.00,0,'disponible','1763761594_71WjC-YikCL._AC_UF894,1000_QL80_.jpg','si','2025-11-13 21:17:43'),(10,1,'Cuaderno Profesional DEL CARTERL','Cuaderno tamaño carta con espiral',85.00,0,'disponible','1763761557_51vPAar1+JL._AC_UF1000,1000_QL80_.jpg','si','2025-11-13 21:17:43'),(11,3,'Cuaderno rojo','',22.00,0,'disponible','1763761475_images (16).png','no','2025-11-19 18:44:58'),(12,2,'Rascador para gatos','evita que tus gatos rasgen tus muebles',1023.00,0,'disponible','1763791522_rascadorposte4_300x.webp','no','2025-11-22 06:05:26');
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos_favoritos`
--

DROP TABLE IF EXISTS `productos_favoritos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos_favoritos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `fecha_agregado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_producto_fav` (`usuario_id`,`producto_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `productos_favoritos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `productos_favoritos_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos_favoritos`
--

LOCK TABLES `productos_favoritos` WRITE;
/*!40000 ALTER TABLE `productos_favoritos` DISABLE KEYS */;
INSERT INTO `productos_favoritos` VALUES (3,2,2,'2025-11-11 17:36:24'),(4,2,3,'2025-11-11 17:36:24'),(5,3,1,'2025-11-11 17:36:24'),(6,3,5,'2025-11-11 17:36:24'),(13,1,8,'2025-11-21 06:05:07'),(15,1,3,'2025-11-21 06:15:57'),(18,1,7,'2025-11-21 06:59:34'),(19,1,6,'2025-11-21 20:21:23'),(20,1,9,'2025-11-21 21:27:39');
/*!40000 ALTER TABLE `productos_favoritos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido_paterno` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido_materno` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contraseña` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usuario_estado` enum('Suspendido','Inactivo','Activo') COLLATE utf8mb4_unicode_ci DEFAULT 'Activo',
  `rol` enum('admin','cliente') COLLATE utf8mb4_unicode_ci DEFAULT 'cliente',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Juana','Pérez','Gómez','juan@comercializadora.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','5512345678','Activo','admin','2025-11-11 17:36:24'),(2,'María','López','Hernández','maria@comercializadora.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','5523456789','Activo','cliente','2025-11-11 17:36:24'),(3,'Carlos','García','Martínez','carlos@comercializadora.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','5534567890','Activo','cliente','2025-11-11 17:36:24'),(4,'miguel','g','z','mm@gmail.com','$2y$10$FshuXEAfg1eOQGXoiW/d9.h0kWmVNtr7h6t6ZeuIJZcNUPqCNpCKe','555-1234','Activo','cliente','2025-11-21 03:55:57'),(5,'Carlos Armando','Montelongo Orozco','Montelongo Orozco','carlangaspro04@gmail.com','$2y$10$hAvOOgayan6mHnYhXjkIZ.YfqSnOdtuiPZF6AoolAu4fd9ht.wqCm','6131407470','Activo','cliente','2025-11-22 05:51:08'),(6,'Sasha','m','Hernández','kominomisenpai098@gmail.com','$2y$10$R3O1bIhi6gVmcmvQx0GoGuvLnn2XOcljyVf92pIME9ZZOvdGDPdgi','6131006787','Activo','admin','2025-11-24 15:48:35');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-26  8:35:38
