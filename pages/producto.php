<?php
require_once 'functions/f_catalogo.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: catalogo.php');
    exit;
}

$id = intval($_GET['id']);
$producto = obtenerProductoPorId($id);
if (!$producto) {
    // producto no encontrado
    http_response_code(404);
    echo '<h1>Producto no encontrado</h1>';
    exit;
}

// session y favoritos
if (session_status() == PHP_SESSION_NONE) session_start();
$favoritos = isset($_SESSION['favoritos']) ? $_SESSION['favoritos'] : [];
$isFav = in_array($producto['id'], $favoritos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($producto['nombre']) ?> - MC Store</title>
        <!-- Bootstrap CSS -->
    <link rel="preload" 
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
          as="style" 
          onload="this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    </noscript>

    <!-- Font Awesome (optimizado con display=swap) -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
          integrity=""
          referrerpolicy="no-referrer"
          media="all">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container py-5">
        <div class="row">
            <div class="col-md-6">
                <img src="../img_productos/<?= htmlspecialchars($producto['imagen']) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($producto['nombre']) ?>">
            </div>
            <div class="col-md-6">
                <h1><?= htmlspecialchars($producto['nombre']) ?></h1>
                <p class="text-muted">Categoría: <?= htmlspecialchars($producto['categoria']) ?></p>
                <h3 class="text-primary">$<?= number_format($producto['precio'], 2) ?></h3>
                <p><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>

                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-primary" onclick="agregarAlCarrito(<?= $producto['id'] ?>)">Añadir al carrito</button>
                    <button class="btn <?= $isFav ? 'btn-danger' : 'btn-outline-danger' ?>" onclick="toggleFavorito(<?= $producto['id'] ?>, this)">
                        <i class="<?= $isFav ? 'fas' : 'far' ?> fa-heart"></i> Favoritos
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="js/catalogo.js" defer></script>
</body>
</html>