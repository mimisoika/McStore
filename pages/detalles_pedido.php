<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'functions/f_detalles_pedido.php';
include '../php/database.php';


// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo '<div class="alert alert-danger">No autorizado</div>';
    exit();
}

// Verificar que la petición sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo '<div class="alert alert-danger">Método no permitido</div>';
    exit();
}

// Verificar que se haya enviado el ID del pedido
if (!isset($_POST['pedido_id']) || empty($_POST['pedido_id'])) {
    http_response_code(400);
    echo '<div class="alert alert-danger">ID de pedido no especificado</div>';
    exit();
}

// Validar y sanitizar el ID del pedido
$pedido_id = filter_var($_POST['pedido_id'], FILTER_VALIDATE_INT);
if ($pedido_id === false || $pedido_id <= 0) {
    http_response_code(400);
    echo '<div class="alert alert-danger">ID de pedido inválido</div>';
    exit();
}

// Verificar que el pedido pertenezca al usuario actual
global $conexion;
$usuario_id = $_SESSION['usuario_id'];

$sql_verificar = "SELECT id FROM pedidos WHERE id = ? AND usuario_id = ?";
$stmt_verificar = $conexion->prepare($sql_verificar);
$stmt_verificar->bind_param("ii", $pedido_id, $usuario_id);
$stmt_verificar->execute();
$resultado_verificar = $stmt_verificar->get_result();

if ($resultado_verificar->num_rows === 0) {
    http_response_code(403);
    echo '<div class="alert alert-danger">No tienes permiso para ver este pedido</div>';
    exit();
}

// Obtener y mostrar los detalles del pedido
$detalles_html = obtenerDetallesPedido($pedido_id);
echo $detalles_html;
?>