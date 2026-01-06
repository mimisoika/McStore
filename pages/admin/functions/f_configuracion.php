<?php
require_once __DIR__ . '/../../../php/database.php';

// ============================================
// FUNCIONES DE CONFIGURACIÓN
// ============================================

function obtenerConfiguracion() {
    global $conexion;
    $sql = "SELECT * FROM configuraciones WHERE id = 1 LIMIT 1";
    $resultado = mysqli_query($conexion, $sql);
    return mysqli_fetch_assoc($resultado);
}

function actualizarConfiguracion($datos) {
    global $conexion;
    // Obtener la configuración actual y fusionar solo las claves proporcionadas
    $actual = obtenerConfiguracion();

    // Si no existe la fila (inicio), crear una por seguridad
    if (!$actual) {
        $sqlInit = "INSERT INTO configuraciones (id, nombre_sitio, logo_url, color_primario, color_secundario, color_encabezado, color_texto, texto_nosotros, direccion, telefono, email, horarios, facebook, instagram, whatsapp, fecha_actualizacion) VALUES (1, 'MC Store', 'pages/img/logo-mcstore.png', '#000000', '#FFFFFF', '#000000', '#000000', '', '', '', '', '', '', '', '', NOW())";
        mysqli_query($conexion, $sqlInit);
        $actual = obtenerConfiguracion();
    }

    $nombre_sitio = isset($datos['nombre_sitio']) ? mysqli_real_escape_string($conexion, $datos['nombre_sitio']) : $actual['nombre_sitio'];
    $logo_url = isset($datos['logo_url']) ? mysqli_real_escape_string($conexion, $datos['logo_url']) : $actual['logo_url'];
    $color_primario = isset($datos['color_primario']) ? mysqli_real_escape_string($conexion, $datos['color_primario']) : $actual['color_primario'];
    $color_secundario = isset($datos['color_secundario']) ? mysqli_real_escape_string($conexion, $datos['color_secundario']) : $actual['color_secundario'];
    $color_encabezado = isset($datos['color_encabezado']) ? mysqli_real_escape_string($conexion, $datos['color_encabezado']) : $actual['color_encabezado'];
    $color_texto = isset($datos['color_texto']) ? mysqli_real_escape_string($conexion, $datos['color_texto']) : $actual['color_texto'];
    $texto_nosotros = isset($datos['texto_nosotros']) ? mysqli_real_escape_string($conexion, $datos['texto_nosotros']) : $actual['texto_nosotros'];
    $direccion = isset($datos['direccion']) ? mysqli_real_escape_string($conexion, $datos['direccion']) : $actual['direccion'];
    $telefono = isset($datos['telefono']) ? mysqli_real_escape_string($conexion, $datos['telefono']) : $actual['telefono'];
    $email = isset($datos['email']) ? mysqli_real_escape_string($conexion, $datos['email']) : $actual['email'];
    $horarios = isset($datos['horarios']) ? mysqli_real_escape_string($conexion, $datos['horarios']) : $actual['horarios'];
    $facebook = isset($datos['facebook']) ? mysqli_real_escape_string($conexion, $datos['facebook']) : $actual['facebook'];
    $instagram = isset($datos['instagram']) ? mysqli_real_escape_string($conexion, $datos['instagram']) : $actual['instagram'];
    $whatsapp = isset($datos['whatsapp']) ? mysqli_real_escape_string($conexion, $datos['whatsapp']) : $actual['whatsapp'];

    $sql = "UPDATE configuraciones SET 
            nombre_sitio = '$nombre_sitio',
            logo_url = '$logo_url',
            color_primario = '$color_primario',
            color_secundario = '$color_secundario',
            color_encabezado = '$color_encabezado',
            color_texto = '$color_texto',
            texto_nosotros = '$texto_nosotros',
            direccion = '$direccion',
            telefono = '$telefono',
            email = '$email',
            horarios = '$horarios',
            facebook = '$facebook',
            instagram = '$instagram',
            whatsapp = '$whatsapp',
            fecha_actualizacion = NOW()
            WHERE id = 1";

    return mysqli_query($conexion, $sql);
}

function subirLogo($archivo) {
    $dirDestino = '../../pages/img/';
    $nombreArchivo = 'logo-mcstore.png';
    $rutaCompleta = $dirDestino . $nombreArchivo;
    
    // Validar que sea una imagen
    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($archivo['type'], $tiposPermitidos)) {
        return ['exito' => false, 'mensaje' => 'El archivo debe ser una imagen válida'];
    }
    
    // Validar tamaño máximo (5MB)
    if ($archivo['size'] > 5000000) {
        return ['exito' => false, 'mensaje' => 'La imagen no debe superar 5MB'];
    }
    
    if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
        return ['exito' => true, 'ruta' => 'pages/img/logo-mcstore.png'];
    } else {
        return ['exito' => false, 'mensaje' => 'Error al subir la imagen'];
    }
}

// ============================================
// FUNCIONES DEL CARRUSEL
// ============================================

function obtenerImagenesCarrusel() {
    global $conexion;
    $sql = "SELECT * FROM carrusel_imagenes WHERE activa = 1 ORDER BY orden ASC";
    $resultado = mysqli_query($conexion, $sql);
    $imagenes = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $imagenes[] = $fila;
    }
    return $imagenes;
}

function obtenerTodasImagenesCarrusel() {
    global $conexion;
    $sql = "SELECT * FROM carrusel_imagenes ORDER BY orden ASC";
    $resultado = mysqli_query($conexion, $sql);
    $imagenes = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $imagenes[] = $fila;
    }
    return $imagenes;
}

function agregarImagenCarrusel($titulo, $descripcion, $archivo) {
    global $conexion;

    /* =========================
       1. Validar subida
    ==========================*/
    if (!isset($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
        return ['exito' => false, 'mensaje' => 'Error al subir la imagen'];
    }

    if ($archivo['size'] > 10 * 1024 * 1024) {
        return ['exito' => false, 'mensaje' => 'La imagen supera los 10MB'];
    }

    /* =========================
       2. Validar imagen real
    ==========================*/
    $info = getimagesize($archivo['tmp_name']);
    if ($info === false) {
        return ['exito' => false, 'mensaje' => 'El archivo no es una imagen válida'];
    }

    $mime = $info['mime'];

    $permitidos = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp'
    ];

    if (!in_array($mime, $permitidos)) {
        return ['exito' => false, 'mensaje' => 'Formato de imagen no permitido'];
    }

    /* =========================
       3. Crear imagen desde origen
    ==========================*/
    switch ($mime) {
        case 'image/jpeg':
            $imagen = imagecreatefromjpeg($archivo['tmp_name']);
            break;
        case 'image/png':
            $imagen = imagecreatefrompng($archivo['tmp_name']);
            break;
        case 'image/gif':
            $imagen = imagecreatefromgif($archivo['tmp_name']);
            break;
        case 'image/webp':
            $imagen = imagecreatefromwebp($archivo['tmp_name']);
            break;
        default:
            return ['exito' => false, 'mensaje' => 'Tipo no soportado'];
    }

    if (!$imagen) {
        return ['exito' => false, 'mensaje' => 'No se pudo procesar la imagen'];
    }

    /* =========================
       4. Redimensionar (optimizado carrusel)
    ==========================*/
    $anchoMax = 1920;
    $ancho = imagesx($imagen);
    $alto = imagesy($imagen);

    if ($ancho > $anchoMax) {
        $nuevoAlto = intval(($anchoMax / $ancho) * $alto);
        $nueva = imagecreatetruecolor($anchoMax, $nuevoAlto);
        imagecopyresampled($nueva, $imagen, 0, 0, 0, 0, $anchoMax, $nuevoAlto, $ancho, $alto);
        imagedestroy($imagen);
        $imagen = $nueva;
    }

    /* =========================
       5. Guardar en WebP
    ==========================*/
    $nombreArchivo = 'slide_' . uniqid() . '.webp';
    $dirDestino = __DIR__ . '/../../pages/img/slider/';

    if (!is_dir($dirDestino)) {
        mkdir($dirDestino, 0755, true);
    }

    $rutaFisica = $dirDestino . $nombreArchivo;

    if (!imagewebp($imagen, $rutaFisica, 80)) {
        imagedestroy($imagen);
        return ['exito' => false, 'mensaje' => 'Error al guardar la imagen'];
    }

    imagedestroy($imagen);

    /* =========================
       6. Guardar en BD
    ==========================*/
    $titulo = mysqli_real_escape_string($conexion, $titulo);
    $descripcion = mysqli_real_escape_string($conexion, $descripcion);
    $rutaDB = 'pages/img/slider/' . $nombreArchivo;

    $resOrden = mysqli_query($conexion, "SELECT COALESCE(MAX(orden),0)+1 AS nuevo FROM carrusel_imagenes");
    $orden = mysqli_fetch_assoc($resOrden)['nuevo'];

    $sql = "INSERT INTO carrusel_imagenes (titulo, descripcion, imagen_url, orden, activa)
            VALUES ('$titulo', '$descripcion', '$rutaDB', $orden, 1)";

    if (!mysqli_query($conexion, $sql)) {
        unlink($rutaFisica);
        return ['exito' => false, 'mensaje' => 'Error al guardar en la base de datos'];
    }

    return ['exito' => true];
}


function obtenerImagenCarrusel($id) {
    global $conexion;
    $id = intval($id);
    $sql = "SELECT * FROM carrusel_imagenes WHERE id = $id LIMIT 1";
    $resultado = mysqli_query($conexion, $sql);
    return mysqli_fetch_assoc($resultado);
}

function actualizarImagenCarrusel($id, $titulo, $descripcion) {
    global $conexion;
    $id = intval($id);
    $titulo = mysqli_real_escape_string($conexion, $titulo);
    $descripcion = mysqli_real_escape_string($conexion, $descripcion);
    
    $sql = "UPDATE carrusel_imagenes SET titulo = '$titulo', descripcion = '$descripcion' WHERE id = $id";
    return mysqli_query($conexion, $sql);
}

function eliminarImagenCarrusel($id) {
    global $conexion;
    $id = intval($id);
    
    // Obtener ruta de imagen
    $sql = "SELECT imagen_url FROM carrusel_imagenes WHERE id = $id LIMIT 1";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    
    if ($fila) {
        $ruta = '../../' . $fila['imagen_url'];
        
        // Eliminar de la BD
        $sqlDelete = "DELETE FROM carrusel_imagenes WHERE id = $id";
        if (mysqli_query($conexion, $sqlDelete)) {
            // Intentar eliminar archivo
            if (file_exists($ruta)) {
                unlink($ruta);
            }
            return true;
        }
    }
    return false;
}

function reordenarCarrusel($orden) {
    global $conexion;
    
    foreach ($orden as $posicion => $id) {
        $id = intval($id);
        $posicion = intval($posicion);
        $sql = "UPDATE carrusel_imagenes SET orden = $posicion WHERE id = $id";
        if (!mysqli_query($conexion, $sql)) {
            return false;
        }
    }
    return true;
}

function activarDesactivarImagenCarrusel($id, $activa) {
    global $conexion;
    $id = intval($id);
    $activa = $activa ? 1 : 0;
    
    $sql = "UPDATE carrusel_imagenes SET activa = $activa WHERE id = $id";
    return mysqli_query($conexion, $sql);
}

// ============================================
// FUNCIÓN PARA GENERAR CSS DINÁMICO
// ============================================

function generarCssDinamico() {
    $config = obtenerConfiguracion();
    // Valores por defecto (coinciden con SQL_CREAR_TABLAS.sql)
    $color_primario = isset($config['color_primario']) && $config['color_primario'] ? $config['color_primario'] : '#0066CC';
    $color_secundario = isset($config['color_secundario']) && $config['color_secundario'] ? $config['color_secundario'] : '#333333';
    $color_encabezado = isset($config['color_encabezado']) && $config['color_encabezado'] ? $config['color_encabezado'] : '#FFFFFF';
    $color_texto = isset($config['color_texto']) && $config['color_texto'] ? $config['color_texto'] : '#000000';

    $css = "
    :root {
        --color-primario: {$color_primario};
        --color-secundario: {$color_secundario};
        --color-encabezado: {$color_encabezado};
        --color-texto: {$color_texto};
    }

    .navbar {
        background-color: var(--color-encabezado) !important;
        color: var(--color-texto) !important;
    }

    .navbar-brand {
        color: var(--color-primario) !important;
    }

    .nav-link {
        color: var(--color-texto) !important;
    }

    .btn-primary {
        background-color: var(--color-primario) !important;
        border-color: var(--color-primario) !important;
    }

    .btn-primary:hover {
        background-color: var(--color-secundario) !important;
    }

    h1, h2, h3, h4, h5, h6 {
        color: var(--color-secundario) !important;
    }

    body {
        color: var(--color-texto) !important;
    }

    .text-primary {
        color: var(--color-primario) !important;
    }
    ";

    return $css;
}
?>