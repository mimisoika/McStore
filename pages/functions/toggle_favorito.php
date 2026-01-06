<?php
session_start();
require_once 'f_favoritos.php';
require_once 'f_login.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$producto_id = $_POST['id'] ?? null;

if (!$producto_id) {
    echo json_encode(['success' => false, 'error' => 'ID no recibido']);
    exit;
}

// Verificar si ya es favorito
$favs = obtenerIdsFavoritos($usuario_id);

if (in_array($producto_id, $favs)) {
    quitarFavorito($usuario_id, $producto_id);
    echo json_encode(['success' => true, 'status' => 'removed']);
} else {
    agregarFavorito($usuario_id, $producto_id);
    echo json_encode(['success' => true, 'status' => 'added']);
}