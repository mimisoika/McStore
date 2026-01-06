<?php
// Endpoint AJAX: devuelve los cards de productos filtrados (HTML)
if (session_status() == PHP_SESSION_NONE) session_start();

require_once 'functions/f_catalogo.php';

$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : 'todas';
$minPrecio = isset($_GET['min_precio']) ? $_GET['min_precio'] : '';
$maxPrecio = isset($_GET['max_precio']) ? $_GET['max_precio'] : '';
$orden = isset($_GET['orden']) ? $_GET['orden'] : '';

// Si no vienen min/max, usar rango por defecto
$rango = obtenerRangoPrecios();
if ($minPrecio === '') $minPrecio = $rango['min'];
if ($maxPrecio === '') $maxPrecio = $rango['max'];

$productos = obtenerProductosCatalogo($categoria, $minPrecio, $maxPrecio, $orden);

// Favoritos (f_catalogo ya incluye f_favoritos.php y define obtenerIdsFavoritos)
$usuario_id = $_SESSION['usuario_id'] ?? null;
$favoritosIds = $usuario_id ? obtenerIdsFavoritos($usuario_id) : [];

if (empty($productos)) {
    echo '<div class="col-12 text-center py-5"><div class="alert alert-info">No se encontraron productos con esos filtros.</div></div>';
    exit;
}

foreach ($productos as $producto) {
    // usar la función que ya genera el HTML del card
    mostrarProducto($producto, $favoritosIds);
}
