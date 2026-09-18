<?php
$host = "localhost";
$user = "root";
$pass = ""; //tecno_parts311!
$db = "tecnoparts";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Conexión fallida: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

?>