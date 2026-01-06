<?php
require_once '../../../php/database.php';

require_once 'f_productos.php';

header('Content-Type: application/json');

try {
    // Actualizar estados automáticamente antes de obtener los productos
    actualizarEstadosStock();
    
    $filtros = [
        'categoria' => $_GET['categoria'] ?? '',
        'estado' => $_GET['estado'] ?? '',
        'busqueda' => $_GET['busqueda'] ?? ''
    ];
    
    $productos = obtenerProductosAdmin($filtros);
    
    echo json_encode([
        'success' => true,
        'products' => $productos
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>