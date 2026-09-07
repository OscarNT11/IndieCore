-- 1. Usuarios / Clientes / Administradores / Operadores
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
  `id_producto` INT AUTO_INCREMENT,
  `nombreP` VARCHAR(255) NOT NULL UNIQUE,
  `categoria` VARCHAR(100),
  `marca` VARCHAR(100),
  `informacion` TEXT,
  `foto` VARCHAR(255),
  `stock` INT NOT NULL DEFAULT 0,
  `precio` DECIMAL(10, 2) NOT NULL,
  PRIMARY KEY (`id_producto`)
);

-- 3. Pedidos
CREATE TABLE `pedidos` (
  `id_pedido` INT AUTO_INCREMENT,
  `fecha` DATETIME NOT NULL,
  `estado` VARCHAR(50),
  `precio_total` DECIMAL(10, 2) NOT NULL,
  `id_usuario` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_pedido`),
  FOREIGN KEY (`id_usuario`) REFERENCES `clientes`(`id_usuario`) ON DELETE CASCADE
);

-- 4. Detalle de Pedidos
CREATE TABLE `detalles_pedido` (
  `id_pedido` INT NOT NULL,
  `id_producto` INT NOT NULL,
  `cantidad` INT NOT NULL,
  PRIMARY KEY (`id_pedido`, `id_producto`),
  FOREIGN KEY (`id_pedido`) REFERENCES `pedidos`(`id_pedido`) ON DELETE CASCADE,
  FOREIGN KEY (`id_producto`) REFERENCES `productos`(`id_producto`) ON DELETE CASCADE
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