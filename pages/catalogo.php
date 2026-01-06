<?php
// Iniciar sesión para acceder a los favoritos del usuario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'functions/f_catalogo.php';
require_once 'functions/f_favoritos.php';

// --- LÓGICA DE FAVORITOS ---
$favoritosIds = [];
if (isset($_SESSION['usuario_id'])) {
    $listaFavoritos = obtenerProductosFavoritos($_SESSION['usuario_id']);
    if (!empty($listaFavoritos)) {
        // Extraemos solo los IDs en un array simple [1, 5, 8...]
        $favoritosIds = array_column($listaFavoritos, 'id');
    }
}

// --- FILTROS ---
$categoriaSeleccionada = isset($_GET['categoria']) ? $_GET['categoria'] : 'todas';
$minPrecio = isset($_GET['min_precio']) && $_GET['min_precio'] !== '' 
    ? floatval($_GET['min_precio']) 
    : '';

$maxPrecio = isset($_GET['max_precio']) && $_GET['max_precio'] !== '' 
    ? floatval($_GET['max_precio']) 
    : '';

$orden = isset($_GET['orden']) ? $_GET['orden'] : '';

$categorias = obtenerCategorias();
$rangoPrecios = obtenerRangoPrecios();

// Defaults
if ($minPrecio === '') $minPrecio = $rangoPrecios['min'];
if ($maxPrecio === '') $maxPrecio = $rangoPrecios['max'];

$productos = obtenerProductosCatalogo($categoriaSeleccionada, $minPrecio, $maxPrecio, $orden);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Repostería</title>
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style" onload="this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></noscript>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="" referrerpolicy="no-referrer" media="all">
</head>
<body class="bg-light pt-4">
    
    <?php include 'header.php'; ?>
    <div class="container">
        <div class="bg-primary text-white text-center py-4 mb-4 rounded">
            <h1 class="display-4">Catálogo de Repostería</h1>
            <p class="lead mb-0">Los mejores productos artesanales</p>
        </div>
        
        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <strong>Filtros</strong>
                    </div>
                    <div class="card-body">
                        <form id="filterForm" method="get">
                            <div class="mb-3">
                                <label for="categoria" class="form-label fw-bold">Categoría</label>
                                <select name="categoria" id="categoria" class="form-select">
                                    <option value="todas" <?= $categoriaSeleccionada == 'todas' ? 'selected' : '' ?>>Todas las categorías</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?= htmlspecialchars($categoria) ?>" <?= $categoriaSeleccionada == $categoria ? 'selected' : '' ?>>
                                            <?= ucfirst(htmlspecialchars($categoria)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Rango de Precio</label>
                                <div class="mb-3">
                                    <label class="form-label small">Precio Mínimo: $<span id="minPrecioVal"><?= number_format($minPrecio, 2) ?></span></label>
                                    <input type="range" id="minPrecioRange" name="min_precio"
                                        min="1" max="<?= $rangoPrecios['max'] ?>" value="<?= htmlspecialchars($minPrecio) ?>"
                                        step="0.01" class="form-range">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Precio Máximo: $<span id="maxPrecioVal"><?= number_format($maxPrecio, 2) ?></span></label>
                                    <input type="range" id="maxPrecioRange" name="max_precio"
                                        min="1" max="<?= $rangoPrecios['max'] ?>" value="<?= htmlspecialchars($maxPrecio) ?>"
                                        step="0.01" class="form-range">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="orden" class="form-label fw-bold">Ordenar por</label>
                                <select name="orden" id="orden" class="form-select">
                                    <option value="mas_reciente" <?= $orden == 'mas_reciente' ? 'selected' : '' ?>>Más reciente</option>
                                    <option value="menos_reciente" <?= $orden == 'menos_reciente' ? 'selected' : '' ?>>Menos reciente</option>
                                    <option value="precio_asc" <?= $orden == 'precio_asc' ? 'selected' : '' ?>>Precio: menor a mayor</option>
                                    <option value="precio_desc" <?= $orden == 'precio_desc' ? 'selected' : '' ?>>Precio: mayor a menor</option>
                                    <option value="nombre_asc" <?= $orden == 'nombre_asc' ? 'selected' : '' ?>>Nombre A-Z</option>
                                    <option value="nombre_desc" <?= $orden == 'nombre_desc' ? 'selected' : '' ?>>Nombre Z-A</option>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <a href="catalogo.php" class="btn btn-outline-secondary">Limpiar filtros</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div id="productosGrid" class="row">
            <?php if (empty($productos)): ?>
                <div class="col-12 text-center py-5">
                    <div class="alert alert-info">No se encontraron productos en esta categoría.</div>
                </div>
            <?php else: ?>
                <?php foreach ($productos as $producto): 
                    // 1. RUTA IMAGEN (Correcta para carpeta pages/: ../img_productos/)
                    $rutaImagen = '../img_productos/' . htmlspecialchars($producto['imagen']);
                    
                    // Validación simple por si no existe la imagen
                    if (empty($producto['imagen'])) {
                         $rutaImagen = '../img_productos/producto-default.jpg';
                    }

                    // 2. ESTADO FAVORITO
                    $esFavorito = in_array($producto['id'], $favoritosIds);
                    // Clases para FontAwesome: fa-solid (relleno) + text-danger (rojo) SI es favorito
                    // Si no, fa-regular (solo borde)
                    $claseIcono = $esFavorito ? 'fa-solid text-danger' : 'fa-regular';
                ?>
                    <div class="col-lg-4 col-md-6 mb-4">
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
                                <h5 class="card-title"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                                <p class="card-text text-muted flex-grow-1">
                                    <?php echo htmlspecialchars(substr($producto['descripcion'], 0, 80) . '...'); ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="h5 text-primary mb-0">$<?php echo number_format($producto['precio'], 2); ?></span>
                                    <a href="producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye me-1"></i> Ver
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
                </div> 
            </div> 
        </div> 
    </div>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="js/catalogo.js" defer></script>
    <script src="js/favoritos.js" defer></script>
</body>
</html>