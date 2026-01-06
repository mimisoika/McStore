<?php
$host = "92.112.184.134";          // IP del VPS Hostinger
$usuario = "mc_user";              // Usuario MySQL del VPS
$contrasena = "McStore2026!";       // Contraseña MySQL
$baseDeDatos = "comercializadora";  // Base de datos

$conexion = mysqli_connect($host, $usuario, $contrasena, $baseDeDatos);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8mb4");
?>
