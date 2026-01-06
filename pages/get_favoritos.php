<?php
session_start();
include '../php/database.php';
include 'functions/f_perfil.php';

header('Content-Type: application/json');

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

try {
    $usuario_id = $_SESSION['usuario_id'];
    $favoritos = obtenerProductosFavoritos($usuario_id);
    
    echo json_encode([
        'success' => true,
        'favoritos' => $favoritos
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
