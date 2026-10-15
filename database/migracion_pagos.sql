-- =============================================================
-- MIGRACIÓN: pagos, envío en los pedidos y método de pago
-- Ejecutar sobre una base `tecnoparts` que YA existe y tiene datos
-- (por ejemplo en phpMyAdmin o con:
--   mysql -u root -h 127.0.0.1 tecnoparts < database/migracion_pagos.sql ).
--
-- IMPORTANTE: cada paso es independiente. Si uno falla (porque la
-- columna o la tabla ya existen), MySQL corta el script y los pasos
-- siguientes NO se ejecutan. Por eso conviene revisar la salida.
-- Si se importa database/tecnoparts.sql desde cero, todo esto ya viene
-- hecho y NO hace falta correr esta migración.
-- =============================================================

USE tecnoparts;

-- 0. La tabla `pagos` debe existir. Si tu base es vieja puede faltar
--    (era el caso de este proyecto), y sin ella no se puede registrar
--    ningún pago. Se crea solo si no está.
CREATE TABLE IF NOT EXISTS `pagos` (
  `id_pago` INT AUTO_INCREMENT PRIMARY KEY,
  `id_pedido` INT NOT NULL,
  `fecha_pago` DATETIME NOT NULL,
  `monto` DECIMAL(10, 2) NOT NULL,
  `metodo_pago` ENUM('tarjeta_credito', 'tarjeta_debito', 'transferencia', 'efectivo') NOT NULL,
  `ultimos4` CHAR(4) NULL,
  `resultado` ENUM('aprobado', 'rechazado') NOT NULL,
  CONSTRAINT `fk_pago_pedido`
    FOREIGN KEY (`id_pedido`) REFERENCES `pedidos`(`id_pedido`)
);

-- 0b. Las cinco columnas de envío del pedido. Son NOT NULL y se llenan
--     en el checkout; en pedidos viejos quedan con texto vacío.
ALTER TABLE `pedidos`
    ADD COLUMN `envio_nombre` VARCHAR(255) NOT NULL DEFAULT '' AFTER `id_usuario`,
    ADD COLUMN `envio_direccion` VARCHAR(255) NOT NULL DEFAULT '' AFTER `envio_nombre`,
    ADD COLUMN `envio_ciudad` VARCHAR(100) NOT NULL DEFAULT '' AFTER `envio_direccion`,
    ADD COLUMN `envio_departamento` VARCHAR(100) NOT NULL DEFAULT '' AFTER `envio_ciudad`,
    ADD COLUMN `envio_telefono` VARCHAR(20) NOT NULL DEFAULT '' AFTER `envio_departamento`;

-- 1. Cada pago registra CÓMO se pagó. Antes solo se guardaban los
--    últimos 4 dígitos de la tarjeta, así que no se distinguía una
--    transferencia de un pago en efectivo.
ALTER TABLE `pagos`
    ADD COLUMN `metodo_pago`
        ENUM('tarjeta_credito', 'tarjeta_debito', 'transferencia', 'efectivo')
        NOT NULL DEFAULT 'tarjeta_credito'
        AFTER `monto`;

-- 2. ultimos4 solo tiene sentido cuando se paga con tarjeta: en una
--    transferencia o en efectivo no hay número de tarjeta que guardar.
ALTER TABLE `pagos`
    MODIFY COLUMN `ultimos4` CHAR(4) NULL;

-- 3. Estados de pedido más completos para el seguimiento del envío.
ALTER TABLE `pedidos`
    MODIFY COLUMN `estado`
        ENUM('pendiente', 'pagado', 'rechazado', 'enviado', 'entregado', 'cancelado')
        NOT NULL DEFAULT 'pendiente';

-- 4. El detalle guarda el NOMBRE del producto tal como estaba al comprar.
--    Si después se renombra o se borra un producto del catálogo, el pedido
--    ya emitido sigue mostrando lo que el cliente compró ese día.
--    Es NULL en los pedidos viejos: la vista cae al nombre del JOIN.
ALTER TABLE `detalles_pedido`
    ADD COLUMN `nombre_producto` VARCHAR(255) NULL
        AFTER `id_producto`;
