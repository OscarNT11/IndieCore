<?php
$c = include ("conexion.php");
if ($c) { 
    function tarjetaid($conn, $id) {
    $consulta = "SELECT nombreP, precio, foto FROM productos WHERE id_producto=$id;";
    $result = mysqli_query($conn,$consulta);


if ($result && $row = mysqli_fetch_array($result)) {
    
        $nombrep = $row["nombreP"];
        $precio = $row["precio"];
        $foto = $row["foto"];
       
       
        
         echo '<article class="carta-producto">
                    <a href="paginaProducto.html" class="carta-producto-info">
                        <img src="../img/'. $foto .'" alt="Producto ...">
                        <h3>'. $nombrep .'</h3>
                        <p>US$'. $precio .'</p>
                    </a>
                    <button class="btn-agregar-carrito">
                        Agregar al Carrito
                        <svg>
                            <use href="../img/iconos.svg#icono-carrito"></use>
                        </svg>
                    </button>   
            </article>';
}
}
}


  ?>