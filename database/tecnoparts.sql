-- 1. Usuarios / Clientes / Administradores / Operadores
-- ATENCIÓN: el DROP borra TODA la base tecnoparts (usuarios, pedidos, reseñas...)
-- antes de recrearla. Es lo que se quiere al importar este script desde cero.
-- Si tienes datos que conservar, comenta esta línea.
DROP DATABASE IF EXISTS tecnoparts;

CREATE DATABASE tecnoparts
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_spanish_ci;

USE tecnoparts;

CREATE TABLE `categorias` (
  `id_categoria` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(255) NOT NULL UNIQUE,
  `descripcion` TEXT
);

CREATE TABLE `usuarios` (
  `id_usuario` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(255) NOT NULL UNIQUE,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `contrasenia` VARCHAR(255) NOT NULL,
  `foto` VARCHAR(255),
  `rol` ENUM('cliente', 'administrador', 'operador') NOT NULL DEFAULT 'cliente',
  `fecha_registro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
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
  `precio_total` DECIMAL(10, 2) NOT NULL,
  `id_usuario` INT NOT NULL,
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`)
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
  `id_usuario` INT NOT NULL,
  `id_producto` INT NOT NULL,
  PRIMARY KEY (`id_resenia`),
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE,
  FOREIGN KEY (`id_producto`) REFERENCES `productos`(`id_producto`) ON DELETE CASCADE
);

INSERT INTO categorias (nombre, descripcion) VALUES
('Juegos',                  'Videojuegos para todas las plataformas'),
('Consolas',                'Consolas y controles'),
('Perifericos',              'Teclados, mouse y auriculares'),
('Componentes',             'Placas, memorias y hardware interno'),
('Otros dispositivos',      'Cables, fundas y repuestos varios');

-- 6. Productos de ejemplo, varios por cada categoría.
--    - La columna `foto` guarda SOLO el nombre del archivo: las vistas ya
--      anteponen la carpeta 'public/img/ImagenesProductos/'. Por eso aquí
--      va 'elden_ring_juego.png' y no la ruta completa.
--    - Los archivos existen de verdad en public/img/ImagenesProductos/.
--    - La categoría se busca por su nombre para no depender del id que
--      le asigne el AUTO_INCREMENT.
INSERT INTO productos (nombre, marca, informacion, foto, stock, precio, id_categoria) VALUES

-- ---- Juegos ----
('Elden Ring', 'FromSoftware', 'RPG de acción de mundo abierto, edición estándar.', 'elden_ring_juego.png', 18, 49.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Juegos')),
('Super Mario Odyssey', 'Nintendo', 'Plataformas 3D para Nintendo Switch.', 'super_mario_odessey_juego.png', 30, 54.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Juegos')),
('Metroid Dread', 'Nintendo', 'Acción y exploración 2D para Nintendo Switch.', 'metroid_dread_juego.png', 24, 49.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Juegos')),
('Halo: Combat Evolved', 'Microsoft', 'Shooter de ciencia ficción, clásico de Xbox.', 'halo_camping_evolved_juego.png', 20, 39.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Juegos')),
('DOOM Eternal', 'Bethesda', 'Shooter en primera persona de ritmo frenético.', 'doom_eternal_juego.png', 26, 39.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Juegos')),
('God of War: Ragnarök', 'Sony', 'Aventura de acción para PlayStation 4 y 5.', 'god_of_war_ragnarok_juego.png', 28, 59.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Juegos')),

-- ---- Consolas ----
('Xbox Series X', 'Microsoft', 'Consola de sobremesa 4K, 1 TB SSD.', 'xboxs_series_x.png', 10, 479.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Consolas')),
('Nintendo Switch 2', 'Nintendo', 'Consola híbrida de nueva generación.', 'nintendo_switch_2.png', 14, 399.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Consolas')),
('Nintendo Switch Blanca', 'Nintendo', 'Consola híbrida en edición blanca.', 'nintendo_switch_blanca.png', 20, 299.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Consolas')),
('PlayStation Classic Mini', 'Sony', 'Consola retro con juegos clásicos preinstalados.', 'playstation_mini_classic.png', 9, 99.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Consolas')),
('Mando Xbox', 'Microsoft', 'Mando inalámbrico para Xbox y PC.', 'mando_xbox.png', 45, 59.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Consolas')),
('Mando Wolverine', 'Razer', 'Mando gamer con botones adicionales programables.', 'mando_wolverine.png', 16, 89.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Consolas')),

-- ---- Periféricos ----
('Teclado Redragon', 'Redragon', 'Teclado mecánico gamer retroiluminado.', 'teclado_redragon.png', 35, 69.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Perifericos')),
('Teclado Logitech Negro', 'Logitech', 'Teclado en español de perfil bajo.', 'teclado_logitech_negro.png', 40, 44.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Perifericos')),
('Mouse Razer', 'Razer', 'Mouse gamer de alta precisión.', 'mause_razer.png', 50, 59.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Perifericos')),
('Mouse Logitech X Plus', 'Logitech', 'Mouse inalámbrico ergonómico.', 'mause_logietech_x_plus.png', 55, 34.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Perifericos')),
('Mouse Logitech con Cable', 'Logitech', 'Mouse óptico USB de uso diario.', 'mause_logitech_cable.png', 60, 19.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Perifericos')),
('Auriculares PlayStation', 'Sony', 'Auriculares con sonido 3D para PlayStation.', 'auriculares_playstation.png', 32, 99.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Perifericos')),
('Auriculares JBL Blancos', 'JBL', 'Auriculares inalámbricos over-ear.', 'auriculares_jbl_blancos.png', 40, 79.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Perifericos')),
('Monitor Samsung Odyssey Curvo', 'Samsung', 'Monitor curvo QHD de alta tasa de refresco.', 'monitor_samsung_odyssey_curvo.png', 15, 299.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Perifericos')),
('Micrófono Logitech', 'Logitech', 'Micrófono USB para streaming y podcast.', 'microfono_logitech.png', 28, 89.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Perifericos')),
('Cámara Web Logitech', 'Logitech', 'Cámara web Full HD para videollamadas.', 'camara_web_logitech.png', 30, 64.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Perifericos')),

-- ---- Componentes ----
('Procesador AMD Ryzen 9 7900X', 'AMD', 'Procesador de 12 núcleos y 24 hilos, socket AM5.', 'procesador_amd_ryzen_9_7900x.png', 12, 429.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Componentes')),
('Procesador AMD Ryzen 5 7600X', 'AMD', 'Procesador de 6 núcleos y 12 hilos, socket AM5.', 'procesador_amd_ryzen_5_7600x.png', 20, 229.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Componentes')),
('Tarjeta Gráfica RTX 5070 Gigabyte', 'Gigabyte', 'GPU de nueva generación para gaming en 1440p.', 'rtx_5070_gigabyte.png', 8, 649.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Componentes')),
('Tarjeta Gráfica RTX 5060 Shadow X2', 'NVIDIA', 'GPU compacta para 1080p.', 'rtx_5060_shadowx2.png', 10, 379.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Componentes')),
('Motherboard ASUS Negra', 'ASUS', 'Placa madre ATX para procesadores modernos.', 'motherboard_asus_negra.png', 18, 189.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Componentes')),
('Motherboard ASRock B850', 'ASRock', 'Placa madre con chipset B850 y PCIe 5.0.', 'motherboard_asrock_b850.png', 15, 219.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Componentes')),
('Memoria RAM T-Force DDR5 16 GB', 'TeamGroup', 'Kit DDR5 de 16 GB de alto rendimiento.', 'memorias_ram_t-force_ddr5_16g.png', 40, 89.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Componentes')),
('Memoria RAM XPG DDR4 16 GB', 'ADATA', 'Kit DDR4 de 16 GB a 3200 MHz.', 'memorias_ram_xpg_ddr4_16g.png', 45, 59.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Componentes')),
('Gabinete Cougar Negro', 'Cougar', 'Gabinete ATX con panel lateral de cristal.', 'gabinete_cougar_negro.png', 22, 89.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Componentes')),
('Gabinete Antec Negro', 'Antec', 'Gabinete con buen flujo de aire y gestión de cables.', 'gabinete_antec_negro.png', 20, 79.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Componentes')),
('Fuente Corsair', 'Corsair', 'Fuente de alimentación certificada 80 Plus.', 'fuente_crosair.png', 25, 109.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Componentes')),
('Fuente ASUS Blanca', 'ASUS', 'Fuente modular de alta eficiencia.', 'fuente_asus_blanca.png', 18, 129.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Componentes')),
('Cooler Cougar', 'Cougar', 'Disipador para CPU con ventilador silencioso.', 'cooler_cougar.png', 30, 49.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Componentes')),
('Cooler 3 Ventiladores Cougar', 'Cougar', 'Refrigeración líquida AIO de 360 mm.', 'cooler_3ventiladores_cougar.png', 14, 129.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Componentes')),

-- ---- Otros dispositivos ----
('Cámara de Seguridad Redmi', 'Xiaomi', 'Cámara de vigilancia WiFi con visión nocturna.', 'camara_de_seguridad_redmi.png', 35, 44.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Otros dispositivos')),
('Cámara 4K EMEET', 'EMEET', 'Webcam 4K con micrófono integrado.', 'camara_4k_emeet.png', 22, 119.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Otros dispositivos')),
('Parlante JBL Portátil', 'JBL', 'Parlante Bluetooth resistente al agua.', 'parlantes_jbl_portatil.png', 40, 69.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Otros dispositivos')),
('Google Chromecast', 'Google', 'Dispositivo para transmitir contenido a la TV.', 'google chromecast.png', 50, 39.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Otros dispositivos')),
('Silla Gamer Cougar', 'Cougar', 'Silla gaming reclinable con soporte lumbar.', 'silla_gamer_cougar.png', 16, 219.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Otros dispositivos')),
('Silla Gamer Corsair', 'Corsair', 'Silla gaming ergonómica de cuero sintético.', 'silla_gamer_crosair.png', 14, 239.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Otros dispositivos')),
('Volante y Pedales Logitech', 'Logitech', 'Set de volante y pedales para simulación de conducción.', 'volante_pedales_logitech.png', 12, 299.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Otros dispositivos')),
('Mousepad Redragon', 'Redragon', 'Alfombrilla gamer de gran tamaño.', 'mausepad_redragon.png', 60, 24.99, (SELECT id_categoria FROM categorias WHERE nombre = 'Otros dispositivos'));




