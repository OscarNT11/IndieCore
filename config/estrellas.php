<?php
/* config/estrellas.php
   Funciones para dibujar la calificación con estrellas. Se usan en la ficha
   del producto y en las tarjetas del catálogo / inicio. */

/* Estrellas de una calificación de 1 a 5 (admite decimales).
   '$valor' es el promedio del producto; si es 0 no se pinta ninguna.
   Devuelve HTML ya escapado, listo para imprimir. */
function estrellasHtml($valor) {

    // Se redondea ACÁ (no en SQL): así la estrella media se calcula con el
    // valor real. Ej.: 4.25 se muestra como 4.5 estrellas.
    $valor = round(max(0, (float) $valor), 1);

    $html = '<span class="estrellas" role="img" aria-label="'
        . escapar(number_format($valor, 1, '.', '') . ' de 5 estrellas') . '">';

    // Una estrella por cada punto entero y una a medias cuando el promedio
    // tiene cualquier decimal (4.5 o 4.3 -> 4 llenas + 1 a medias). Si se
    // esperara a 4.5 exacto, un 4.3 se veria como 4 estrellas peladas.
    $llenas = (int) floor($valor);
    $media = ($valor - $llenas) > 0.05 && $llenas < 5;

    for ($i = 1; $i <= 5; $i++) {

        if ($i <= $llenas) {
            $clase = 'estrellas__item estrellas__item--llena';
            $simbolo = '★';
        } elseif ($i === $llenas + 1 && $media) {
            $clase = 'estrellas__item estrellas__item--media';
            $simbolo = '★';
        } else {
            $clase = 'estrellas__item estrellas__item--vacia';
            $simbolo = '☆';
        }

        $html .= '<span class="' . $clase . '">' . $simbolo . '</span>';
    }

    return $html . '</span>';
}

/* Texto corto del promedio: "4.5 (12)" o "Sin reseñas". */
function resumenEstrellas($promedio, $total) {

    $total = (int) $total;
    $promedio = round(max(0, (float) $promedio), 1);

    if ($total === 0) {
        return 'Sin reseñas';
    }

    return number_format($promedio, 1, '.', '') . ' (' . $total . ')';
}
?>
