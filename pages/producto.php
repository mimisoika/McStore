<?php
require_once 'functions/f_catalogo.php';
require_once 'functions/f_favoritos.php'; // Agregado para validar favoritos

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: catalogo.php');
    exit;
}

$id = intval($_GET['id']);
$producto = obtenerProductoPorId($id);
if (!$producto) {
    http_response_code(404);
    echo '<h1>Producto no encontrado</h1>';
    exit;
}

// session y favoritos
if (session_status() == PHP_SESSION_NONE) session_start();

// Lógica robusta para detectar favoritos (Igual que en catalogo.php)
$esFav = false;
if (isset($_SESSION['usuario_id'])) {
    $listaFavoritos = obtenerProductosFavoritos($_SESSION['usuario_id']);
    // Verificar si el ID del producto actual está en la lista de favoritos
    foreach ($listaFavoritos as $fav) {
        if ($fav['id'] == $producto['id']) {
            $esFav = true;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($producto['nombre']) ?> - MC Store</title>
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style" onload="this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></noscript>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="" referrerpolicy="no-referrer" media="all">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container py-5">
        <div class="row">
            <div class="col-md-6">
                <img src="../img_productos/<?= htmlspecialchars($producto['imagen']) ?>" class="img-fluid rounded shadow-sm" alt="<?= htmlspecialchars($producto['nombre']) ?>">
            </div>
            <div class="col-md-6">
                <h1 class="display-5"><?= htmlspecialchars($producto['nombre']) ?></h1>
                <p class="text-muted">Categoría: <span class="badge bg-secondary"><?= htmlspecialchars($producto['categoria']) ?></span></p>
                <h3 class="text-primary my-3">$<?= number_format($producto['precio'], 2) ?></h3>
                <p class="lead"><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>

                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-primary btn-lg" onclick="agregarAlCarrito(<?= $producto['id'] ?>)">
                        <i class="fas fa-cart-plus me-2"></i>Añadir al carrito
                    </button>
                    
                    <button type="button" 
                            class="btn btn-outline-danger btn-lg btn-fav" 
                            data-id="<?= $producto['id'] ?>">
                        <i id="icono-fav-<?= $producto['id'] ?>" 
                           class="<?= $esFav ? 'fa-solid text-danger' : 'fa-regular' ?> fa-heart me-2"></i> 
                        Favoritos
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="js/catalogo.js" defer></script>
    <script src="js/favoritos.js" defer></script>
</body>
</html>