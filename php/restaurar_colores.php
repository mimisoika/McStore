<?php
require_once 'database.php';

// Valores por defecto según SQL_CREAR_TABLAS.sql
$defaults = [
    'color_primario' => '#0066CC',
    'color_secundario' => '#333333',
    'color_encabezado' => '#FFFFFF',
    'color_texto' => '#000000'
];

$sql = "UPDATE configuraciones SET 
    color_primario = '" . mysqli_real_escape_string($conexion, $defaults['color_primario']) . "',
    color_secundario = '" . mysqli_real_escape_string($conexion, $defaults['color_secundario']) . "',
    color_encabezado = '" . mysqli_real_escape_string($conexion, $defaults['color_encabezado']) . "',
    color_texto = '" . mysqli_real_escape_string($conexion, $defaults['color_texto']) . "',
    fecha_actualizacion = NOW()
    WHERE id = 1";

if (mysqli_query($conexion, $sql)) {
    echo "✓ Colores restaurados a los valores por defecto.<br>";
    echo "<a href=\"../index.php\">Volver al sitio</a>\n";
} else {
    echo "Error al actualizar los colores: " . mysqli_error($conexion);
}
?>