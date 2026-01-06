<?php
/**
 * Procesa el pago y simula la respuesta de Mercado Pago
 */

include '../php/database.php';
include 'functions/f_pago.php';

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pago.php');
    exit;
}

$pedido_id = $_POST['pedido_id'] ?? null;
$usuario_id = $_SESSION['usuario_id'];
$raw_metodo = $_POST['metodo_pago_mp'] ?? null;

// Mapear/metodología de pago del checkout simulado a los valores permitidos por el ENUM
$metodo_pago = null;
if ($raw_metodo !== null) {
    $mp = strtolower(trim($raw_metodo));
    switch ($mp) {
        case 'tarjeta_credito':
        case 'mercado_credito':
        case 'tarjeta':
            $metodo_pago = 'tarjeta';
            break;
        case 'efectivo':
            $metodo_pago = 'efectivo';
            break;
        case 'transferencia_bancaria':
        case 'transferencia':
            $metodo_pago = 'transferencia';
            break;
        default:
            // Valor desconocido: dejar null para producir un error controlado más abajo
            $metodo_pago = null;
            break;
    }
}

if (!$pedido_id || !$metodo_pago) {
    die('Error: Datos incompletos o método de pago inválido');
}

// Simular un pequeño delay para parecer realista
sleep(1);

// Simular validación de pago (90% éxito, 10% fallo en simulación)
$es_exitoso = rand(1, 10) > 1; // 90% de probabilidad de éxito

if ($es_exitoso) {
    // Primero completamos el pedido: insertar detalles, actualizar stock y vaciar carrito
    $completado = completarPedido($pedido_id, $usuario_id);
    if (!$completado) {
        $_SESSION['pago_fallido'] = true;
        $_SESSION['razon_fallo'] = 'Ocurrió un error al procesar los detalles del pedido. Contacta al soporte.';
        header('Location: error_pago.php?pedido_id=' . $pedido_id);
        exit;
    }

    // Actualizar estado del pedido a "confirmado" y registrar método de pago
    $sql = "UPDATE pedidos SET estado = 'confirmado', metodo_pago = ? WHERE id = ? AND usuario_id = ?";
    $stmt = $conexion->prepare($sql);
    
    if (!$stmt) {
        die("Error en la consulta: " . $conexion->error);
    }
    
    $stmt->bind_param('sii', $metodo_pago, $pedido_id, $usuario_id);
    
    if (!$stmt->execute()) {
        die("Error al actualizar pedido: " . $stmt->error);
    }
    
    $stmt->close();
    
    // Redirigir a página de éxito
    $_SESSION['pago_exitoso'] = true;
    $_SESSION['pedido_confirmado'] = $pedido_id;
    // El pedido ya fue confirmado: eliminar la referencia de pedido pendiente en sesión
    if (isset($_SESSION['pedido_pendiente']) && $_SESSION['pedido_pendiente'] == $pedido_id) {
        unset($_SESSION['pedido_pendiente']);
    }
    header('Location: confirmacion_pago.php?pedido_id=' . $pedido_id);
} else {
    // Pago rechazado
    $_SESSION['pago_fallido'] = true;
    $_SESSION['razon_fallo'] = 'Fondos insuficientes. Por favor intenta con otro método de pago.';
    header('Location: error_pago.php?pedido_id=' . $pedido_id);
}

exit;
?>
