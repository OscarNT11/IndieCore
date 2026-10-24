<?php
// config/imagenes.php
/* Validación y guardado de imágenes subidas por formulario.
   Se usa para la foto de perfil (UsuarioController) y para la foto
   de un producto (ProductoController). */

/* Carpeta pública donde se guardan las fotos de perfil y de productos. */
const IMAGEN_MAXIMO_BYTES = 2097152; // 2 MB

$GLOBALS['carpetasImagenes'] = array(
    'perfil'   => 'public/img/perfiles/',
    'producto' => 'public/img/ImagenesProductos/',
);

/* Extensiones aceptadas, indexadas por el tipo devuelto por getimagesize(). */
$GLOBALS['extensionesImagenes'] = array(
    IMAGETYPE_JPEG => 'jpg',
    IMAGETYPE_PNG  => 'png',
    IMAGETYPE_GIF  => 'gif',
    IMAGETYPE_WEBP => 'webp',
);

/* true si el array de $_FILES trae realmente un archivo para subir. */
function hayArchivoImagen($archivo) {
    return isset($archivo['error']) && $archivo['error'] !== UPLOAD_ERR_NO_FILE;
}

/* Devuelve la carpeta (ruta absoluta) asociada a un destino del mapa.
   Si el destino no existe, usa la carpeta de perfiles. */
function carpetaImagenes($destino) {
    $carpetas = $GLOBALS['carpetasImagenes'];

    $relativa = $carpetas[$destino] ?? $carpetas['perfil'];

    return __DIR__ . '/../' . $relativa;
}

/* Ruta relativa de la carpeta de fotos de producto, como se guarda en
   la BD y se usa en las vistas (public/img/ImagenesProductos/). */
function carpetaImagenProductos() {
    return $GLOBALS['carpetasImagenes']['producto'];
}

/* Valida el error de subida, el tamaño y el tipo real del archivo.
   Devuelve la extensión correcta o un array con la clave 'error'. */
function validarImagen($archivo) {

    if (!hayArchivoImagen($archivo)) {
        return array('error' => "No se recibió ninguna imagen.");
    }

    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return array('error' => "No se pudo subir la imagen.");
    }

    if ($archivo['size'] > IMAGEN_MAXIMO_BYTES) {
        return array('error' => "La imagen no puede pesar más de 2 MB.");
    }

    // El tipo real se comprueba con getimagesize(), no con la extensión
    // que manda el navegador (esa se puede falsificar).
    $infoImagen = @getimagesize($archivo['tmp_name']);

    if ($infoImagen === false) {
        return array('error' => "El archivo no es una imagen válida.");
    }

    $extensionesPermitidas = $GLOBALS['extensionesImagenes'];

    if (!isset($extensionesPermitidas[$infoImagen[2]])) {
        return array('error' => "Formato no permitido. Usa JPG, PNG, GIF o WEBP.");
    }

    return $extensionesPermitidas[$infoImagen[2]];
}

/* Guarda una imagen subida y devuelve ['ruta' => ...] o ['error' => mensaje].

   $destino: 'perfil' o 'producto' (ver $GLOBALS['carpetasImagenes']).
   $prefijo: inicio del nombre del archivo, útil para reconocerlo luego
             en la carpeta (perfil_..., producto_...). */
function guardarImagenSubida($archivo, $destino = 'perfil', $prefijo = null) {

    $extension = validarImagen($archivo);

    if (is_array($extension)) {
        return $extension;
    }

    $carpetaDestino = carpetaImagenes($destino);

    if (!is_dir($carpetaDestino)) {
        mkdir($carpetaDestino, 0755, true);
    }

    // Nombre único para no pisar otras imágenes ni exponer
    // el nombre original del archivo.
    $prefijo = $prefijo ?: $destino;
    $nombreArchivo = $prefijo . '_' . uniqid() . '.' . $extension;

    if (!move_uploaded_file($archivo['tmp_name'], $carpetaDestino . $nombreArchivo)) {
        return array('error' => "No se pudo guardar la imagen en el servidor.");
    }

    // Ruta relativa que se guarda en la BD y usa la vista.
    return array('ruta' => $GLOBALS['carpetasImagenes'][$destino] . $nombreArchivo);
}

/* Borra del disco una imagen guardada con guardarImagenSubida().
   $ruta es la ruta relativa que devolvió la función (la misma de la BD). */
function eliminarImagenGuardada($ruta) {

    $ruta = (string) $ruta;

    if ($ruta === '') {
        return false;
    }

    $permitidas = $GLOBALS['carpetasImagenes'];

    foreach ($permitidas as $carpeta) {
        if (strpos($ruta, $carpeta) === 0) {
            $absoluta = __DIR__ . '/../' . $ruta;

            return is_file($absoluta) && unlink($absoluta);
        }
    }

    return false;
}
