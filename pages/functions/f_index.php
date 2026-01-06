<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/php/database.php';
require_once __DIR__ . '/../../config.php';
require_once 'f_catalogo.php';
require_once 'f_favoritos.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;

// Obtener favoritos si hay usuario logueado
$favoritosIds = $usuario_id ? obtenerIdsFavoritos($usuario_id) : [];


function obtenerProductosDestacados($limite = 10) {
    global $conexion;
    
    $sql = "SELECT id, nombre, descripcion, precio, imagen 
            FROM productos 
            WHERE estado IN ('disponible', 'poco_stock') AND destacado = 'si'
            ORDER BY fecha_creacion DESC 
            LIMIT ?";
    
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $limite);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    $productos = [];
    while ($producto = mysqli_fetch_assoc($resultado)) {
        $productos[] = $producto;
    }
    
    mysqli_stmt_close($stmt);
    return $productos;
}

function agregarAlCarrito($productoId, $cantidad = 1) {
    global $conexion;
    
    if (!isset($_SESSION['usuario_id'])) {
        return array('success' => false, 'message' => 'Debe iniciar sesión');
    }
    
    $usuarioId = $_SESSION['usuario_id'];
    
    $sql = "SELECT cantidad FROM carrito WHERE producto_id = ? AND usuario_id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $productoId, $usuarioId);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($resultado) > 0) {
        $fila = mysqli_fetch_assoc($resultado);
        $nuevaCantidad = $fila['cantidad'] + $cantidad;
        $sql = "UPDATE carrito SET cantidad = ? WHERE producto_id = ? AND usuario_id = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "iii", $nuevaCantidad, $productoId, $usuarioId);
        $exito = mysqli_stmt_execute($stmt);
        $mensaje = $exito ? 'Cantidad actualizada' : 'Error al actualizar';
    } else {
        $sql = "INSERT INTO carrito (producto_id, usuario_id, cantidad) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "iii", $productoId, $usuarioId, $cantidad);
        $exito = mysqli_stmt_execute($stmt);
        $mensaje = $exito ? 'Producto agregado al carrito' : 'Error al agregar producto';
    }
    
    mysqli_stmt_close($stmt);
    return array('success' => $exito, 'message' => $mensaje);
}
/**
 * Obtener productos aleatorios por categoría
 * @param string|null $categoria Nombre de la categoría (o null/'todas' para cualquier categoría)
 * @param int $limite Cantidad de productos a retornar
 * @return array
 */
function obtenerProductosAleatoriosPorCategoria($categoria = null, $limite = 4) {
    global $conexion;

    $sql = "SELECT p.id, p.nombre, p.descripcion, p.precio, p.imagen, c.nombre as categoria
        FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        WHERE p.estado IN ('disponible', 'poco_stock')";

    if ($categoria && $categoria !== 'todas') {
        $sql .= " AND c.nombre = ?";
    }

    $sql .= " ORDER BY RAND() LIMIT ?";

    $stmt = mysqli_prepare($conexion, $sql);
    if (!$stmt) {
        error_log("[f_catalogo] mysqli_prepare failed: " . mysqli_error($conexion) . " -- SQL: " . $sql);
        return [];
    }

    if ($categoria && $categoria !== 'todas') {
        // Bind category (string) and limit (int)
        mysqli_stmt_bind_param($stmt, "si", $categoria, $limite);
    } else {
        // Only limit
        mysqli_stmt_bind_param($stmt, "i", $limite);
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

function mostrarProductosDestacados() {

    global $usuario_id;
    $productos = obtenerProductosDestacados(10);
    
    if (empty($productos)) {
        echo '<div class="col-12 text-center">';
        echo '<p class="text-muted">No hay productos destacados disponibles en este momento.</p>';
        echo '</div>';
        return;
    }
    
    foreach ($productos as $producto) {
        $favoritosIds = obtenerIdsFavoritos($usuario_id);
        mostrarProducto($producto, $favoritosIds);
    }
}

function mostrarProductosAleatorios(){

    global $usuario_id;
    // Obtener todas las categorías y mostrar 4 productos aleatorios por cada una
            $categorias = obtenerCategorias();
            if (!empty($categorias)) {
                // favoritos desde sesión
                $favoritos = isset($_SESSION['favoritos']) ? $_SESSION['favoritos'] : [];
                foreach ($categorias as $categoria) {
                    $productosAleatorios = obtenerProductosAleatoriosPorCategoria($categoria, 4);
                    echo '<div class="row mb-4">';
                    echo '  <div class="col-12 mb-3">';
                    echo '    <h3 class="h4">' . htmlspecialchars($categoria) . '</h3>';
                    echo '  </div>';

                    if (empty($productosAleatorios)) {
                        echo '<div class="col-12 text-muted">No hay productos disponibles en esta categoría.</div>';
                    } else {
                        foreach ($productosAleatorios as $producto) {
                            $favoritosIds = obtenerIdsFavoritos($usuario_id);
                            mostrarProducto($producto, $favoritosIds);
                        }
                    }

                    echo '</div>'; // .row
                }
            } else {
                echo '<div class="row"><div class="col-12 text-center text-muted">No hay categorías con productos disponibles.</div></div>';
            }    
}
?>