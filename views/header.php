<!DOCTYPE html>
<html lang="es">
<head>
        <title>Tecno Parts - Tecnología para todos</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair:ital,opsz,wght@0,5..1200,300..900;1,5..1200,300..900&display=swap" rel="stylesheet">
        <link rel="icon" href="public/img/logoTecnoParts.png">
    
        <link rel="stylesheet" href="public/css/generales.css">
        <link rel="stylesheet" href="public/css/css-listado.css">
        <link rel="stylesheet" href="public/css/responsiveDesign.css">
        <link rel="stylesheet" href="public/css/paginaInicio.css?v=<?php echo filemtime(__DIR__ . '/../public/css/paginaInicio.css'); ?>">

</head>

    <body>
        <header class="header">

            <div class="header-nav" id="nav-superior">

                <!-- LOGO A ENLACE -->
                <a href="index.php" class="header-logo">
                <img src="public/img/logoTecnoParts.png" alt="Logo Tecno Parts">
                </a>

                <!-- FORMULARIO PARA BUSCAR DETERMINADO PRODUCTO / SESION -->
                <form class="header-busqueda">
                    <input type="text" placeholder="Buscar...">
                    <!-- BOTON SUBMIT QUE BUSQUE LO SOLICITADO -->
                    <button type="submit" aria-label="lupa">
                        <svg><use href="public/img/iconos.svg#icono-lupa"></use></svg>
                    </button>

                </form>

                <!-- BOTON / ENLACE QUE ABRA UNA VENTANA MODAL DE FORMULARIO  -->
                <button class="btn-ingresar">
                    <svg><use href="public/img/iconos.svg#icono-persona"></use></svg>
                    <p>Iniciar Sesion</p>
                </button>

                <!-- BOTON / ENLACE QUE ABRE UNA VENTANA MODAL
                  (O DIRIGE A OTRA PARTE) DE LOS PRODUCTOS A COMPRAR -->
                <a href="index.php?pagina=carrito" class="btn-carrito">
                    <svg><use href="public/img/iconos.svg#icono-carrito"></use></svg>
                    <span id="contador-carrito">0</span>
                </a>

            </div>

                <!-- AQUÍ VA EL VERDEDADERO NAV, ENLACES DE ANCLAS -->
            <nav class="header-nav" id="nav-inferior">
                <ul class="nav-list">
                    <li class="nav-item"><a href="">Catalogo</a></li>
                    <li class="nav-item"><a href="paginainicio.php#lo-nuevo">Nuevo</a></li>
                    <li class="nav-item"><a href="paginainicio.php#destacados">Destacado</a></li>
                    <li class="nav-item"><a href="paginainicio.php#redes">Contactanos</a></li>
                </ul>
            </nav>


        </header>

        <main class="contenido-principal">

