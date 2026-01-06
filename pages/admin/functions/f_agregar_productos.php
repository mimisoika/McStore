<?php
require_once '../../../php/database.php';

header('Content-Type: application/json');

if (!$conexion) {
    echo json_encode(['success' => false, 'mensaje' => 'Error de conexión']);
    exit();
}

// --- NUEVAS FUNCIONES PARA ACTUALIZACIÓN AUTOMÁTICA DE ESTADO ---
function actualizarEstadosStock() {
    global $conexion;
    
    try {
        // Actualizar productos con cantidad 0 a "agotado" (excepto suspendidos)
        $sql1 = "UPDATE productos SET estado = 'agotado' WHERE (cantidad = 0 OR cantidad IS NULL) AND estado != 'suspendido'";
        mysqli_query($conexion, $sql1);
        
        // Actualizar productos con cantidad entre 1 y 9 a "poco_stock"
        $sql2 = "UPDATE productos SET estado = 'poco_stock' WHERE cantidad BETWEEN 1 AND 9 AND estado != 'suspendido'";
        mysqli_query($conexion, $sql2);
        
        // Actualizar productos con cantidad >= 10 a "disponible"
        $sql3 = "UPDATE productos SET estado = 'disponible' WHERE cantidad >= 10 AND estado != 'suspendido'";
        mysqli_query($conexion, $sql3);
        
        return true;
    } catch (Exception $e) {
        error_log("Error al actualizar estados de stock: " . $e->getMessage());
        return false;
    }
}

function determinarEstadoAutomatico($cantidad) {
    $cantidad = intval($cantidad) || 0;
    if ($cantidad === 0) {
        return 'agotado';
    } else if ($cantidad < 10) {
        return 'poco_stock';
    } else {
        return 'disponible';
    }
}

// --- FUNCIONES CRUD MODIFICADAS ---
function obtenerProductos() {
    global $conexion;
    // Actualizar estados automáticamente antes de obtener
    actualizarEstadosStock();
    
    $sql = "SELECT p.*, c.nombre as categoria 
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            ORDER BY p.id DESC";
    
    $resultado = mysqli_query($conexion, $sql);
    $productos = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $productos[] = $fila;
    }
    return $productos;
}

function obtenerCategorias() {
    global $conexion;
    $sql = "SELECT id, nombre FROM categorias ORDER BY nombre";
    $resultado = mysqli_query($conexion, $sql);
    $categorias = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $categorias[] = $fila;
    }
    return $categorias;
}

// MODIFICADA para usar estado automático cuando no se fuerza manualmente
function agregarProducto($nombre, $precio, $categoria, $descripcion, $cantidad, $estado, $destacado, $imagen = '') {
    global $conexion;
    
    $cantidad_entero = intval($cantidad);
    
    // Si el estado enviado es "activo" (del frontend), calcular automáticamente
    if ($estado === 'activo' || $estado === 'disponible') {
        $estado = determinarEstadoAutomatico($cantidad_entero);
    }
    
    // Validar estado contra valores permitidos
    $estado = strtolower(trim($estado));
    $allowedEstados = ['disponible', 'suspendido', 'agotado', 'poco_stock'];
    if (!in_array($estado, $allowedEstados, true)) {
        $estado = determinarEstadoAutomatico($cantidad_entero);
    }

    // Escapar cadenas
    $nombre = mysqli_real_escape_string($conexion, $nombre);
    $descripcion = mysqli_real_escape_string($conexion, $descripcion);
    $precio = mysqli_real_escape_string($conexion, $precio);
    $estado = mysqli_real_escape_string($conexion, $estado);
    $imagen = mysqli_real_escape_string($conexion, $imagen);
    $destacado = mysqli_real_escape_string($conexion, $destacado);
    
    // Buscar ID de categoría
    $sql_cat = "SELECT id FROM categorias WHERE nombre = '" . mysqli_real_escape_string($conexion, $categoria) . "'";
    $resultado_cat = mysqli_query($conexion, $sql_cat);
    $categoria_data = mysqli_fetch_assoc($resultado_cat);
    
    if (!$categoria_data) {
        return false;
    }
    $categoria_id = $categoria_data['id'];
    
    $sql = "INSERT INTO productos (categoria_id, nombre, descripcion, precio, cantidad, estado, imagen, destacado) 
            VALUES ('$categoria_id', '$nombre', '$descripcion', '$precio', '$cantidad_entero', '$estado', '$imagen', '$destacado')";
    
    $resultado = mysqli_query($conexion, $sql);
    
    // Actualizar estados después de agregar (por si acaso)
    if ($resultado) {
        actualizarEstadosStock();
    }
    
    return $resultado;
}

// MODIFICADA para usar estado automático cuando no se fuerza manualmente
function actualizarProducto($id, $nombre, $precio, $categoria, $descripcion, $cantidad, $estado, $destacado, $imagen = null) {
    global $conexion;
    
    $cantidad_entero = intval($cantidad);
    
    // Si el estado enviado es "activo" (del frontend), calcular automáticamente
    if ($estado === 'activo' || $estado === 'disponible') {
        $estado = determinarEstadoAutomatico($cantidad_entero);
    }
    
    // Validar estado
    $estado = strtolower(trim($estado));
    $allowedEstados = ['disponible', 'suspendido', 'agotado', 'poco_stock'];
    if (!in_array($estado, $allowedEstados, true)) {
        $estado = determinarEstadoAutomatico($cantidad_entero);
    }

    // Escapar cadenas
    $id = mysqli_real_escape_string($conexion, $id);
    $nombre = mysqli_real_escape_string($conexion, $nombre);
    $descripcion = mysqli_real_escape_string($conexion, $descripcion);
    $precio = mysqli_real_escape_string($conexion, $precio);
    $estado = mysqli_real_escape_string($conexion, $estado);
    $destacado = mysqli_real_escape_string($conexion, $destacado);
    
    // Buscar ID de categoría
    $sql_cat = "SELECT id FROM categorias WHERE nombre = '" . mysqli_real_escape_string($conexion, $categoria) . "'";
    $resultado_cat = mysqli_query($conexion, $sql_cat);
    $categoria_data = mysqli_fetch_assoc($resultado_cat);
    
    if (!$categoria_data) {
        return false;
    }
    $categoria_id = $categoria_data['id'];
    
    $set_parts = [
        "categoria_id='$categoria_id'",
        "nombre='$nombre'",
        "descripcion='$descripcion'",
        "precio='$precio'",
        "cantidad='$cantidad_entero'",
        "estado='$estado'",
        "destacado='$destacado'"
    ];
    
    if ($imagen !== null) {
        $imagen_escapada = mysqli_real_escape_string($conexion, $imagen);
        $set_parts[] = "imagen='$imagen_escapada'";
    }
    
    $sql = "UPDATE productos SET " . implode(', ', $set_parts) . " WHERE id='$id'";
    
    $resultado = mysqli_query($conexion, $sql);
    
    // Actualizar estados después de actualizar
    if ($resultado) {
        actualizarEstadosStock();
    }
    
    return $resultado;
}

function eliminarProducto($id) {
    global $conexion;
    $id = mysqli_real_escape_string($conexion, $id);
    $sql = "UPDATE productos SET estado = 'suspendido' WHERE id = '$id'";
    return mysqli_query($conexion, $sql);
}

function obtenerProducto($id) {
    global $conexion;
    $id = mysqli_real_escape_string($conexion, $id);
    $sql = "SELECT p.*, c.nombre as categoria FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.id = '$id'";
    $resultado = mysqli_query($conexion, $sql);
    return mysqli_fetch_assoc($resultado);
}

function subirImagen($archivo) {
    $carpeta = '../../../img_productos/';
    $nombre = time() . '_' . basename($archivo['name']);
    $ruta = $carpeta . $nombre;
    
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0777, true);
    }
    
    if (move_uploaded_file($archivo['tmp_name'], $ruta)) {
        return $nombre;
    }
    return false;
}

// --- Procesar peticiones AJAX ---
if ($_POST) {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion == 'obtener_productos') {
        // Actualizar estados automáticamente antes de filtrar
        actualizarEstadosStock();
        
        $filtroCategoria = isset($_POST['filtroCategoria']) ? trim($_POST['filtroCategoria']) : '';
        $filtroEstado = isset($_POST['filtroEstado']) ? trim($_POST['filtroEstado']) : '';
        $busqueda = isset($_POST['busqueda']) ? trim($_POST['busqueda']) : '';

        // Mapeo de estados del frontend a la DB
        $estadoMap = [
            'activo' => 'disponible',
            'inactivo' => 'suspendido',
            'disponible' => 'disponible',
            'suspendido' => 'suspendido',
            'agotado' => 'agotado',
            'poco_stock' => 'poco_stock'
        ];

        if ($filtroEstado !== '' && $filtroEstado !== 'todos') {
            $filtroEstado = $estadoMap[$filtroEstado] ?? $filtroEstado;
        } else {
            $filtroEstado = '';
        }

        $productos = obtenerProductos();

        // Aplicar filtros
        if ($filtroCategoria !== '' && $filtroCategoria !== 'todas') {
            $productos = array_filter($productos, function($p) use ($filtroCategoria) {
                return isset($p['categoria']) && mb_strtolower($p['categoria']) === mb_strtolower($filtroCategoria);
            });
        }
        if ($filtroEstado !== '') {
            $productos = array_filter($productos, function($p) use ($filtroEstado) {
                return isset($p['estado']) && $p['estado'] === $filtroEstado;
            });
        }
        if ($busqueda !== '') {
            $q = mb_strtolower($busqueda);
            $productos = array_filter($productos, function($p) use ($q) {
                $nombre = isset($p['nombre']) ? mb_strtolower($p['nombre']) : '';
                $descripcion = isset($p['descripcion']) ? mb_strtolower($p['descripcion']) : '';
                return mb_strpos($nombre, $q) !== false || mb_strpos($descripcion, $q) !== false;
            });
        }

        $productos = array_values($productos);
        echo json_encode(['success' => true, 'productos' => $productos]);

    } elseif ($accion == 'obtener_categorias') {
        $categorias = obtenerCategorias();
        echo json_encode(['success' => true, 'categorias' => $categorias]);

    } elseif ($accion == 'agregar_producto') {
        $nombre = $_POST['nombre'] ?? '';
        $precio = $_POST['precio'] ?? 0;
        $categoria = $_POST['categoria'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $cantidad = $_POST['stock'] ?? 0;
        $estado = $_POST['estado'] ?? 'activo';
        $destacado = $_POST['destacado'] ?? 'no';
        
        $imagen = '';
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $imagen = subirImagen($_FILES['imagen']);
        }
        
        $resultado = agregarProducto($nombre, $precio, $categoria, $descripcion, $cantidad, $estado, $destacado, $imagen);
        
        if ($resultado) {
            echo json_encode(['success' => true, 'mensaje' => 'Producto agregado correctamente']);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Error al agregar producto: ' . mysqli_error($conexion)]);
        }

    } elseif ($accion == 'actualizar_producto') {
        $id = $_POST['id'] ?? 0;
        $nombre = $_POST['nombre'] ?? '';
        $precio = $_POST['precio'] ?? 0;
        $categoria = $_POST['categoria'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $cantidad = $_POST['stock'] ?? 0;
        $estado = $_POST['estado'] ?? 'activo';
        $destacado = $_POST['destacado'] ?? 'no';
        
        $imagen = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $imagen = subirImagen($_FILES['imagen']);
        }
        
        $resultado = actualizarProducto($id, $nombre, $precio, $categoria, $descripcion, $cantidad, $estado, $destacado, $imagen);
        
        if ($resultado) {
            echo json_encode(['success' => true, 'mensaje' => 'Producto actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar producto: ' . mysqli_error($conexion)]);
        }
            
    } elseif ($accion == 'eliminar_producto') {
        $id = $_POST['id'] ?? 0;
        $resultado = eliminarProducto($id);
        
        if ($resultado) {
            echo json_encode(['success' => true, 'mensaje' => 'Producto suspendido correctamente']);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Error al suspender producto']);
        }

    } elseif ($accion == 'obtener_producto') {
        $id = $_POST['id'] ?? 0;
        $producto = obtenerProducto($id);
        
        if ($producto) {
            echo json_encode(['success' => true, 'producto' => $producto]);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Producto no encontrado']);
        }
    }
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Petición inválida']);
}
?>