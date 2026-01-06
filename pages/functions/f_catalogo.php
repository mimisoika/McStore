<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/php/database.php';
require_once __DIR__ . '/../../config.php';
require_once 'f_favoritos.php';


$usuario_id = $_SESSION['usuario_id'] ?? null;

// Obtener favoritos si hay usuario logueado
$favoritosIds = $usuario_id ? obtenerIdsFavoritos($usuario_id) : [];

function obtenerProductosCatalogo($categoria = null, $minPrecio = null, $maxPrecio = null, $orden = null) {
    global $conexion;

    $sql = "SELECT p.id, p.nombre, p.descripcion, p.precio, p.imagen, 
                c.nombre as categoria  
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.estado IN ('disponible', 'poco_stock')";

    $params = [];
    $types = '';

    if ($categoria && $categoria !== 'todas') {
        $sql .= " AND c.nombre = ?";
        $types .= 's';
        $params[] = $categoria;
    }

    if ($minPrecio !== null && $minPrecio !== '') {
        $sql .= " AND p.precio >= ?";
        $types .= 'd';
        $params[] = (float)$minPrecio;
    }

    if ($maxPrecio !== null && $maxPrecio !== '') {
        $sql .= " AND p.precio <= ?";
        $types .= 'd';
        $params[] = (float)$maxPrecio;
    }

    // Ordenamiento
    switch ($orden) {
        case 'precio_asc':
            $sql .= " ORDER BY p.precio ASC";
            break;
        case 'precio_desc':
            $sql .= " ORDER BY p.precio DESC";
            break;
        case 'nombre_asc':
            $sql .= " ORDER BY p.nombre ASC";
            break;
        case 'nombre_desc':
            $sql .= " ORDER BY p.nombre DESC";
            break;
        case 'mas_reciente':
            $sql .= " ORDER BY p.fecha_creacion DESC";
            break;
        case 'menos_reciente':
            $sql .= " ORDER BY p.fecha_creacion ASC";
            break;
        default:
            $sql .= " ORDER BY p.fecha_creacion DESC";
    }

    $stmt = mysqli_prepare($conexion, $sql);
    if (!$stmt) {
        error_log('[f_catalogo] mysqli_prepare failed: ' . mysqli_error($conexion) . ' -- SQL: ' . $sql);
        return [];
    }

    if (!empty($types)) {
        // bind_param requires references
        $bind_names = [];
        $bind_names[] = $types;
        for ($i = 0; $i < count($params); $i++) {
            $bind_names[] = & $params[$i];
        }
        call_user_func_array(array($stmt, 'bind_param'), $bind_names);
    }

    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    $productos = [];
    while ($producto = mysqli_fetch_assoc($resultado)) {
        $productos[] = $producto;
    }

    mysqli_stmt_close($stmt);
    return $productos;
}


function obtenerCategorias() {
    global $conexion;
    
    $sql = "SELECT DISTINCT c.nombre 
            FROM categorias c 
            INNER JOIN productos p ON c.id = p.categoria_id 
            WHERE p.estado IN ('disponible', 'poco_stock')
            ORDER BY c.nombre";
    $resultado = mysqli_query($conexion, $sql);
    
    $categorias = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $categorias[] = $fila['nombre'];
    }
    
    return $categorias;
}

function agregarProductoAlCarrito($productoId, $cantidad = 1) {
    require_once 'f_index.php';
    return agregarAlCarrito($productoId, $cantidad);
}

/**
 * Obtener un producto por su ID
 */
function obtenerProductoPorId($id) {
    global $conexion;
    
    // Validar que el ID sea numérico
    $id = intval($id);
    if ($id <= 0) {
        return null;
    }
    
    // Consulta SQL corregida - incluye WHERE con el ID y condición de estado
    $sql = "SELECT p.id, p.nombre, p.descripcion, p.precio, p.imagen, p.cantidad as stock, p.estado, 
                   c.nombre as categoria  
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.id = ? AND p.estado IN ('disponible', 'poco_stock')";
    
    $stmt = mysqli_prepare($conexion, $sql);
    if (!$stmt) {
        error_log("Error preparando consulta: " . mysqli_error($conexion));
        return null;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    if (!$resultado) {
        error_log("Error obteniendo resultado: " . mysqli_error($conexion));
        mysqli_stmt_close($stmt);
        return null;
    }
    
    $producto = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);
    
    // Si no hay stock (cantidad = 0), marcar como no disponible para compra
    if ($producto && isset($producto['stock']) && $producto['stock'] <= 0) {
        $producto['disponible_para_compra'] = false;
    } elseif ($producto) {
        $producto['disponible_para_compra'] = true;
    }
    
    return $producto;
}
function mostrarProducto($producto, $favoritosIds = []) {

    // Imagen normal
    $imagenOriginal = !empty($producto['imagen'])
        ? BASE_URL . 'img_productos/' . $producto['imagen']
        : BASE_URL . 'img_productos/producto-default.jpg';

    // Imagen WebP
    $imagenWebP = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $imagenOriginal);

    // Ruta física para file_exists (NO URL)
    $rutaFisicaWebP = $_SERVER['DOCUMENT_ROOT'] . parse_url($imagenWebP, PHP_URL_PATH);

    if (!file_exists($rutaFisicaWebP)) {
        $imagenWebP = null;
    }

    // Favoritos
    $esFavorito = in_array($producto['id'], $favoritosIds);
    $icono = $esFavorito ? 'fa-solid fa-heart text-danger' : 'fa-regular fa-heart';

    echo '
    <div class="col-md-3 mb-3">
        <div class="card h-100 shadow-sm border-0 position-relative">

            <a href="' . BASE_URL . 'producto.php?id=' . $producto['id'] . '" class="text-decoration-none text-dark">

                <picture>
                    ' . ($imagenWebP ? '<source srcset="' . $imagenWebP . '" type="image/webp">' : '') . '

                    <img src="' . $imagenOriginal . '"
                        loading="lazy"
                        decoding="async"
                        width="300" height="200"
                        class="card-img-top"
                        style="height: 200px; object-fit: cover;"
                        alt="' . htmlspecialchars($producto['nombre']) . '">
                </picture>

            </a>

            <div class="card-body">
                <a href="' . BASE_URL . 'producto.php?id=' . $producto['id'] . '" class="text-decoration-none text-dark">
                    <h5 class="card-title">' . htmlspecialchars($producto['nombre']) . '</h5>
                </a>
                <p class="text-muted card-text">' . htmlspecialchars($producto['descripcion']) . '</p>
            </div>

            <div class="card-footer bg-white border-0">
                <div class="d-flex align-items-center gap-2">

                    <div class="text-primary fw-bold fs-5 mb-0 me-2">
                        $' . number_format($producto['precio'], 2) . '
                    </div>

                    <button class="btn btn-outline-primary" onclick="agregarAlCarrito(' . $producto['id'] . ')">
                        <i class="bi bi-cart-plus"></i>
                    </button>

                    <button class="btn btn-outline-primary btn-fav" data-id="' . $producto['id'] . '">
                        <i class="' . $icono . '" id="icono-fav-' . $producto['id'] . '"></i>
                    </button>

                </div>
            </div>

        </div>
    </div>
    ';
}


/**
 * Obtiene el rango de precios (min, max) de los productos disponibles
 */
function obtenerRangoPrecios() {
    global $conexion;
    $sql = "SELECT MIN(precio) as min_precio, MAX(precio) as max_precio FROM productos WHERE estado IN ('disponible', 'poco_stock')"; 
    $res = mysqli_query($conexion, $sql);
    if ($fila = mysqli_fetch_assoc($res)) {
        return [
            'min' => (float)$fila['min_precio'],
            'max' => (float)$fila['max_precio']
        ];
    }
    return ['min' => 0, 'max' => 0];
}
?>