<?php
/* ============================================================================
 * ARCHIVO DE CONEXION A LA BASE DE DATOS
 * ============================================================================ */
$host = "localhost";
$user = "root";
$pass = ""; //tecno_parts311! - Se cambia en un futuro.
$db = "tecnoparts";
// $host = "sql308.infinityfree.com";
// $user = "if0_43330076";
// $pass = "indiepass993";
// $db = "if0_43330076_tecnoparts";


$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Conexión fallida: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

?>