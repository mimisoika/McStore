<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'functions/f_catalogo.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['producto_id'])) {
    $productoId = intval($_POST['producto_id']);
    $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;
    
    // Validar datos
    if ($productoId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de producto inválido'
        ]);
        exit;
    }
    
    if ($cantidad <= 0 || $cantidad > 99) {
        echo json_encode([
            'success' => false,
            'message' => 'Cantidad inválida (debe ser entre 1 y 99)'
        ]);
        exit;
    }
    
    // Verificar si el usuario está logueado
    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Debe iniciar sesión para agregar productos al carrito'
        ]);
        exit;
    }
    
    $resultado = agregarProductoAlCarrito($productoId, $cantidad);
    echo json_encode($resultado);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Datos inválidos o método no permitido'
    ]);
}
?>