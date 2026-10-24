# IndieCore

Trabajo Final del 3° año de Bachillerato, Informática - UTU.

Tienda de tecnología (Tecno Parts) hecha en PHP + MySQL: catálogo, carrito,
checkout con pago simulado, historial de pedidos y panel de gestión con ABM de
productos, listado de pedidos y administración de usuarios.

---

## Requisitos

- **XAMPP** (o cualquier Apache + PHP 8 + MySQL/MariaDB).
- PHP **8.0 o superior** con las extensiones `mysqli` y `mbstring` (vienen
  activas en XAMPP).
- Un navegador moderno (Chrome, Edge, Firefox).

---

## Instalación

### 1. Copiar el proyecto

Descomprimí el ZIP dentro de la carpeta `htdocs` de XAMPP:

```
C:\xampp\htdocs\IndieCore
```

> La carpeta debe llamarse **`IndieCore`**: todas las rutas usan
> `index.php?pagina=...` relativas a esa carpeta.

### 2. Iniciar los servicios

Abrí el **XAMPP Control Panel** y pulsá **Start** en:

- **Apache**
- **MySQL**

### 3. Importar la base de datos

1. Abrí <http://localhost/phpmyadmin>.
2. Pestaña **Importar** (no hace falta crear la base antes).
3. Elegí el archivo `database/tecnoparts.sql` y pulsá **Continuar**.

El script crea la base `tecnoparts`, las tablas, 5 categorías, 44 productos y
los 3 usuarios de prueba (ver abajo).

> **Atención:** `tecnoparts.sql` empieza con `DROP DATABASE IF EXISTS tecnoparts`.
> Si ya tenías datos cargados, **los borra**. Si querés conservarlos, comentá
> esa línea antes de importar.

### 4. Revisar la conexión

`config/conexion.php` trae la configuración por defecto de XAMPP:

```php
$host = "localhost";
$user = "root";
$pass = "";          // En XAMPP por defecto no hay contraseña
$db   = "tecnoparts";
```

Si tu MySQL tiene contraseña de `root`, cambiala en `$pass`.

### 5. Abrir la tienda

<http://localhost/IndieCore/>

---

## Usuarios de prueba

Se puede entrar con el **correo** o con el **nombre de usuario**,
indistintamente.

| Rol | Nombre | Correo | Contraseña | Qué puede hacer |
|---|---|---|---|---|
| **Comprador** | `Comprador` | `comprador@tecnoparts.com` | `TiendaCompra2026` | Catálogo, carrito, checkout, historial y "Mi cuenta" |
| **Operador** | `Operador` | `operador@tecnoparts.com` | `TiendaOpera2026` | Todo lo del comprador + panel: productos y pedidos (sin acceso a usuarios) |
| **Administrador** | `Admin` | `admin@tecnoparts.com` | `TiendaAdmin2026` | Todo + gestión de usuarios (roles, suspender / reactivar cuentas) |

> Son cuentas de demostración: **cambialas antes de publicar** (Mi cuenta →
> nueva contraseña, o desde phpMyAdmin).

### Qué probar con cada uno

- **Comprador:** agregar productos al carrito, finalizar la compra y ver la
  factura. Con tarjeta, un número terminado en **dígito par** se aprueba y uno
  terminado en **impar** se rechaza (el pago es simulado).
- **Operador:** en el menú aparece **Operador** → panel con Productos y
  Pedidos. Si escribe `index.php?pagina=usuarios` a mano, recibe 403.
- **Administrador:** ve además la sección **Usuarios**, puede hacer
  administrador u operador a otra cuenta y suspenderla. Una cuenta suspendida
  ya no puede iniciar sesión (mensaje "Cuenta suspendida").

---

## Estructura del proyecto

```
IndieCore/
├── index.php              Router principal (?pagina=...)
├── config/                Conexión, sesión/permisos, subida de imágenes
├── controllers/           Lógica de cada sección
├── models/                Consultas a la base
├── views/                 HTML (views/admin/ es el panel de gestión)
├── public/css/            Hojas de estilo
├── public/js/             Carrito y validaciones del navegador
├── public/img/            Logo, iconos e imágenes de productos
├── database/              tecnoparts.sql + migración de suspensión
└── storage/logs/          Registro de errores del checkout
```

---

## Páginas principales

| URL | Descripción |
|---|---|
| `index.php?pagina=home` | Inicio |
| `index.php?pagina=catalogo` | Catálogo y búsqueda |
| `index.php?pagina=carrito` | Carrito |
| `index.php?pagina=cuenta` | Mi cuenta e historial de pedidos |
| `index.php?pagina=panel` | Panel de gestión (operador / admin) |
| `index.php?pagina=admin_producto` | ABM de productos |
| `index.php?pagina=admin_pedidos` | Todos los pedidos y sus facturas |
| `index.php?pagina=admin_usuario` | Gestión de usuarios (solo admin) |

---

## Problemas frecuentes

**"Conexión fallida"**
Apache o MySQL no están iniciados, o la contraseña de `config/conexion.php` no
coincide con la de tu MySQL.

**Importaste el SQL pero la base ya existía (de una versión anterior)**
Falta la columna `suspendida` en `usuarios`. Ejecutá una vez
`database/migracion_suspension_usuarios.sql` en phpMyAdmin.

**La página sale sin estilos**
El proyecto tiene que estar en `htdocs/IndieCore`. Si lo pusiste con otro
nombre, las rutas a `public/css/...` no coinciden.

**Error al subir fotos**
Las carpetas de destino necesitan permiso de escritura. Editá
`config/imagenes.php` si querés cambiar la ubicación o el tamaño máximo
(2 MB por defecto).
