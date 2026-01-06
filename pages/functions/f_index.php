<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/php/database.php';
require_once __DIR__ . '/../../config.php';
require_once 'f_catalogo.php';
require_once 'f_favoritos.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;

// Función auxiliar para renderizar la tarjeta CORRECTAMENTE en el INDEX
function renderizarTarjetaInicio($producto, $favoritosIds) {
    // CORRECCIÓN: Verificar primero si hay imagen para evitar el error de "Passing null"
    $nombreImagen = !empty($producto['imagen']) ? $producto['imagen'] : 'producto-default.jpg';
    
    // Construir ruta (para index.php es 'img_productos/')
    $rutaImagen = 'img_productos/' . htmlspecialchars($nombreImagen);

    // Validación extra: si el archivo no existe físicamente, poner default
    // (Solo verificamos si no es ya la default para ahorrar recursos)
    if ($nombreImagen !== 'producto-default.jpg' && !file_exists(__DIR__ . '/../../img_productos/' . $nombreImagen)) {
        $rutaImagen = 'img_productos/producto-default.jpg';
    }

    // Lógica para el botón de favoritos
    $esFavorito = in_array($producto['id'], $favoritosIds);
    $claseIcono = $esFavorito ? 'fa-solid text-danger' : 'fa-regular';

    ?>
    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="position-relative">
                <img src="<?php echo $rutaImagen; ?>" 
                     class="card-img-top" 
                     style="height: 250px; object-fit: cover;" 
                     alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                
                <button type="button" 
                        class="btn btn-light rounded-circle position-absolute top-0 end-0 m-2 shadow-sm btn-fav" 
                        data-id="<?php echo $producto['id']; ?>"
                        title="Añadir a favoritos"
                        style="z-index: 10;">
                    <i id="icono-fav-<?php echo $producto['id']; ?>" class="<?php echo $claseIcono; ?> fa-heart"></i>
                </button>
            </div>
            <div class="card-body d-flex flex-column">
                <h5 class="card-title text-truncate"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                <p class="card-text text-muted text-truncate"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-primary">$<?php echo number_format($producto['precio'], 2); ?></span>
                    <a href="pages/producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye me-1"></i> Ver
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php
}

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
        mysqli_stmt_bind_param($stmt, "si", $categoria, $limite);
    } else {
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
    $favoritosIds = $usuario_id ? obtenerIdsFavoritos($usuario_id) : [];
    
    if (empty($productos)) {
        echo '<div class="col-12 text-center">';
        echo '<p class="text-muted">No hay productos destacados disponibles en este momento.</p>';
        echo '</div>';
        return;
    }
    
    foreach ($productos as $producto) {
        renderizarTarjetaInicio($producto, $favoritosIds);
    }
}

function mostrarProductosAleatorios(){
    global $usuario_id;
    $categorias = obtenerCategorias();
    $favoritosIds = $usuario_id ? obtenerIdsFavoritos($usuario_id) : [];

    if (!empty($categorias)) {
        foreach ($categorias as $categoria) {
            $productosAleatorios = obtenerProductosAleatoriosPorCategoria($categoria, 4);
            echo '<div class="row mb-4">';
            echo '  <div class="col-12 mb-3">';
            echo '    <h3 class="h4 border-bottom pb-2">' . htmlspecialchars($categoria) . '</h3>';
            echo '  </div>';

            if (empty($productosAleatorios)) {
                echo '<div class="col-12 text-muted">No hay productos disponibles en esta categoría.</div>';
            } else {
                echo '<div class="row">'; 
                foreach ($productosAleatorios as $producto) {
                    renderizarTarjetaInicio($producto, $favoritosIds);
                }
                echo '</div>';
            }
            echo '</div>'; 
        }
    } else {
        echo '<div class="row"><div class="col-12 text-center text-muted">No hay categorías con productos disponibles.</div></div>';
    }    
}
?>