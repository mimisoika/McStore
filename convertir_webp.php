<?php
// ---------------------------------------------
// CONFIGURACIÓN DE RUTAS (tus carpetas reales)
// ---------------------------------------------
$carpetas = [
    "pages/img/slider",   // Carrusel
    "img_productos"       // Productos
];

// ---------------------------------------------
// FUNCIÓN PARA CONVERTIR A WEBP
// ---------------------------------------------
function convertirAWebp($ruta) {
    $extensiones = ['jpg', 'jpeg', 'png'];

    foreach (scandir($ruta) as $archivo) {

        // Ignorar archivos no válidos
        if ($archivo === "." || $archivo === "..") continue;

        $rutaOriginal = "$ruta/$archivo";
        $info = pathinfo($rutaOriginal);

        if (!isset($info['extension'])) continue;

        $extension = strtolower($info['extension']);

        // Si no es jpg/png, ignorar
        if (!in_array($extension, ['jpg', 'jpeg', 'png'])) continue;

        // Generar nombre .webp
        $rutaWebp = $ruta . "/" . $info['filename'] . ".webp";

        // Si ya existe, no convertir de nuevo
        if (file_exists($rutaWebp)) {
            echo "✔ Ya existe: $rutaWebp<br>";
            continue;
        }

        // Cargar imagen según tipo
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $imagen = imagecreatefromjpeg($rutaOriginal);
                break;
            case 'png':
                $imagen = imagecreatefrompng($rutaOriginal);
                break;
            default:
                continue 2;
        }

        if (!$imagen) {
            echo "❌ Error al abrir: $rutaOriginal<br>";
            continue;
        }

        // Crear WebP con calidad 80
        if (imagewebp($imagen, $rutaWebp, 80)) {
            echo "✅ Convertida: $rutaWebp<br>";
        } else {
            echo "❌ Error al convertir: $rutaOriginal<br>";
        }

        imagedestroy($imagen);
    }
}

// ---------------------------------------------
// EJECUTAR CONVERSIÓN EN TODAS LAS CARPETAS
// ---------------------------------------------
echo "<h2>Conversión a WebP</h2>";

foreach ($carpetas as $ruta) {
    echo "<h3>Carpeta: $ruta</h3>";
    is_dir($ruta)
        ? convertirAWebp($ruta)
        : print("❌ No existe: $ruta<br>");
}
?>
