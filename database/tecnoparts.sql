-- 1. Usuarios / Clientes / Administradores / Operadores
CREATE DATABASE IF NOT EXISTS tecnoparts
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_spanish_ci;

USE tecnoparts;

CREATE TABLE `categorias` (
  `id_categoria` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(255) NOT NULL UNIQUE,
  `descripcion` TEXT
);

CREATE TABLE `usuarios` (
  `id_usuario` INT AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL UNIQUE,
  `telefono` VARCHAR(50) UNIQUE,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `contrasenia` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id_usuario`)
);

CREATE TABLE `clientes` (
  `id_usuario` VARCHAR(50) NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `direccion` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(50),
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `contrasenia` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id_usuario`)
);

CREATE TABLE `administradores` (
  `id_usuario` VARCHAR(50) NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(50),
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `contrasenia` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id_usuario`)
);

CREATE TABLE `operadores` (
  `id_usuario` VARCHAR(50) NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(50),
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `contrasenia` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id_usuario`)
);

-- 2. Productos
CREATE TABLE `productos` (
  `id_producto` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(255) NOT NULL UNIQUE,
  `marca` VARCHAR(100) NOT NULL,
  `informacion` TEXT,
  `foto` VARCHAR(255),
  `stock` INT NOT NULL DEFAULT 0,
  `precio` DECIMAL(10, 2) NOT NULL,
  `id_categoria` INT,
  FOREIGN KEY (`id_categoria`) REFERENCES `categorias`(`id_categoria`) 
);

-- 3. Pedidos
CREATE TABLE `pedidos` (
  `id_pedido` INT AUTO_INCREMENT PRIMARY KEY,
  `fecha` DATETIME NOT NULL,
  `estado` VARCHAR(50),
  `precio_total` DECIMAL(10, 2) NOT NULL
);

-- 4. Detalle de Pedidos
CREATE TABLE `detalles_pedido` (
  `id_detalle` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `cantidad` INT NOT NULL,
  `precio_unitario` DECIMAL(10, 2) NOT NULL,
  `id_pedido` INT NOT NULL,
  `id_producto` INT NOT NULL,
  FOREIGN KEY (`id_pedido`) REFERENCES `pedidos`(`id_pedido`),
  FOREIGN KEY (`id_producto`) REFERENCES `productos`(`id_producto`) 
);

-- 5. Reseñas
CREATE TABLE `resenias` (
  `id_resenia` INT AUTO_INCREMENT,
  `comentario` TEXT,
  `calificacion` INT NOT NULL,
  `fecha` DATETIME NOT NULL,
  `id_usuario` VARCHAR(50) NOT NULL,
  `id_producto` INT NOT NULL,
  PRIMARY KEY (`id_resenia`),
  FOREIGN KEY (`id_usuario`) REFERENCES `clientes`(`id_usuario`) ON DELETE CASCADE,
  FOREIGN KEY (`id_producto`) REFERENCES `productos`(`id_producto`) ON DELETE CASCADE
);

INSERT INTO categorias (nombre, descripcion) VALUES
('Juegos',                  'Videojuegos para todas las plataformas'),
('Consolas',                'Consolas y controles'),
('Perifericos',              'Teclados, mouse y auriculares'),
('Componentes',             'Placas, memorias y hardware interno'),
('Otros dispositivos',      'Cables, fundas y repuestos varios');




