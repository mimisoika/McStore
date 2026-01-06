<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'php/database.php';
require_once 'pages/admin/functions/f_configuracion.php';

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Verificación - Sistema de Configuración</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head>";
echo "<body>";
echo "<div class='container mt-5'>";
echo "<h2>🔍 Verificación del Sistema de Configuración</h2>";
echo "<hr>";

// Verificar tablas
echo "<h5>📊 Tablas de Base de Datos</h5>";

$checkTables = mysqli_query($conexion, "SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'comercializadora'");
$row = mysqli_fetch_assoc($checkTables);

// Verificar tabla configuraciones
$resultado = mysqli_query($conexion, "SELECT COUNT(*) as count FROM configuraciones");
$config_count = mysqli_fetch_assoc($resultado);

echo "<div class='alert alert-" . ($config_count['count'] > 0 ? "success" : "danger") . "'>";
echo "✓ Tabla 'configuraciones': " . ($config_count['count'] > 0 ? "ACTIVA" : "NO EXISTE");
echo "</div>";

// Verificar tabla carrusel_imagenes
$resultado2 = mysqli_query($conexion, "SELECT COUNT(*) as count FROM carrusel_imagenes");
$carousel_count = mysqli_fetch_assoc($resultado2);

echo "<div class='alert alert-" . ($carousel_count['count'] >= 0 ? "success" : "danger") . "'>";
echo "✓ Tabla 'carrusel_imagenes': " . ($carousel_count['count'] >= 0 ? "ACTIVA" : "NO EXISTE");
echo "</div>";

// Verificar datos de configuración
echo "<h5 class='mt-4'>⚙️ Datos de Configuración</h5>";
$config = obtenerConfiguracion();

if ($config) {
    echo "<div class='alert alert-success'>";
    echo "<strong>Configuración detectada:</strong><br>";
    echo "• Nombre del sitio: <code>" . htmlspecialchars($config['nombre_sitio']) . "</code><br>";
    echo "• Logo: <code>" . htmlspecialchars($config['logo_url']) . "</code><br>";
    echo "• Color primario: <span style='display:inline-block; width:30px; height:30px; background-color:" . htmlspecialchars($config['color_primario']) . "; border:1px solid #333; vertical-align:middle;'></span> <code>" . htmlspecialchars($config['color_primario']) . "</code><br>";
    echo "• Color secundario: <span style='display:inline-block; width:30px; height:30px; background-color:" . htmlspecialchars($config['color_secundario']) . "; border:1px solid #333; vertical-align:middle;'></span> <code>" . htmlspecialchars($config['color_secundario']) . "</code><br>";
    echo "• Teléfono: <code>" . htmlspecialchars($config['telefono']) . "</code><br>";
    echo "• Email: <code>" . htmlspecialchars($config['email']) . "</code><br>";
    echo "</div>";
} else {
    echo "<div class='alert alert-danger'>";
    echo "❌ No se encontró configuración. Ejecuta: php/crear_tabla_configuracion.php";
    echo "</div>";
}

// Verificar archivos
echo "<h5 class='mt-4'>📁 Archivos Creados</h5>";

$archivos = array(
    'pages/admin/functions/f_configuracion.php' => 'Funciones de configuración',
    'pages/admin/functions/f_gestion_carrusel.php' => 'Gestión del carrusel',
    'pages/admin/configuracion.php' => 'Página de configuración',
    'php/crear_tabla_configuracion.php' => 'Script de instalación',
);

foreach ($archivos as $ruta => $descripcion) {
    $existe = file_exists($ruta);
    echo "<div class='alert alert-" . ($existe ? "success" : "danger") . "'>";
    echo ($existe ? "✓" : "✗") . " <strong>$descripcion:</strong> <code>$ruta</code>";
    echo "</div>";
}

// Verificar directorios de subida
echo "<h5 class='mt-4'>📂 Directorios de Subida</h5>";

$directorios = array(
    'pages/img/' => 'Imágenes del sitio',
    'pages/img/slider/' => 'Imágenes del carrusel',
);

foreach ($directorios as $ruta => $descripcion) {
    $existe = is_dir($ruta);
    $writable = is_writable($ruta);
    $estado = $existe ? ($writable ? 'OK' : 'No escribible') : 'No existe';
    $clase = $existe && $writable ? 'success' : 'warning';
    
    echo "<div class='alert alert-$clase'>";
    echo ($existe && $writable ? "✓" : "⚠") . " <strong>$descripcion:</strong> <code>$ruta</code> - $estado";
    echo "</div>";
}

// Verificar imágenes del carrusel
echo "<h5 class='mt-4'>🖼️ Imágenes del Carrusel</h5>";

$imagenes = obtenerTodasImagenesCarrusel();
echo "<div class='alert alert-info'>";
echo "Total de imágenes: <strong>" . count($imagenes) . "</strong><br>";
if (count($imagenes) > 0) {
    echo "Imágenes activas: <strong>" . count(array_filter($imagenes, function($img) { return $img['activa']; })) . "</strong>";
} else {
    echo "⚠️ No hay imágenes en el carrusel. Ve a Configuración → Carrusel para agregar.";
}
echo "</div>";

echo "</div>";
echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>";
echo "</body>";
echo "</html>";
?>
