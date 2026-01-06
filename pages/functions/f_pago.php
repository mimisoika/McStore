<?php
     
require_once __DIR__ . '/../../php/database.php';

/**
 * Crea el pedido con estado inicial "pendiente"
 * Retorna el ID del pedido creado
 */
function crearPedido($usuario_id, $direccion_id, $total, $metodo_pago){
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    global $conexion;

    // Obtener datos del formulario (si se llaman por POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario_id = $_SESSION['usuario_id'];
        $direccion_id = isset($_POST['direccion_id']) ? trim($_POST['direccion_id']) : null;
        $total = isset($_POST['total']) ? trim($_POST['total']) : 0;
        $metodo_pago = isset($_POST['metodo_pago']) ? trim($_POST['metodo_pago']) : '';
    }

    // Insertar pedido con estado "pendiente"
    // La tabla `pedidos` definida por el usuario usa las columnas:
    // (usuario_id, direccion_id, total, metodo_pago, estado, fecha_pedido)
    $estado = "pendiente";

    // Si no hay direccion seleccionada (NULL o string vacío), insertamos NULL
    // para evitar pasar una cadena vacía que rompe la FK.
    if (empty($direccion_id)) {
        $sql = "INSERT INTO pedidos (usuario_id, direccion_id, total, metodo_pago, estado)
                VALUES (?, NULL, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            die("Error en la consulta: " . $conexion->error);
        }

        // tipos: usuario_id (i), total (d), metodo_pago (s), estado (s)
        $stmt->bind_param('idss', $usuario_id, $total, $metodo_pago, $estado);
    } else {
        // asegurarnos de que la dirección es un entero
        $direccion_id = (int)$direccion_id;

        $sql = "INSERT INTO pedidos (usuario_id, direccion_id, total, metodo_pago, estado)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            die("Error en la consulta: " . $conexion->error);
        }

        // tipos: usuario_id (i), direccion_id (i), total (d), metodo_pago (s), estado (s)
        $stmt->bind_param('iidss', $usuario_id, $direccion_id, $total, $metodo_pago, $estado);
    }

    if (!$stmt->execute()) {
        die("Error al crear el pedido: " . $stmt->error);
    }

    $pedido_id = $stmt->insert_id;
    $stmt->close();

    // Obtener productos del carrito del usuario
    $sql = "SELECT c.producto_id, p.nombre, p.precio, c.cantidad
            FROM carrito c
            JOIN productos p ON c.producto_id = p.id
            WHERE c.usuario_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('i', $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $sql_insert_detalle = "INSERT INTO detalles_pedido (pedido_id, producto_id, nombre_producto, cantidad, precio_unitario, total)
                           VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_detalle = $conexion->prepare($sql_insert_detalle);

    while ($row = $result->fetch_assoc()) {
        $producto_id = $row['producto_id'];
        $nombre_producto = $row['nombre'];
        $cantidad = $row['cantidad'];
        $precio_unitario = $row['precio'];
        $total_detalle = $precio_unitario * $cantidad;

        // Insertar detalle del pedido
        $stmt_detalle->bind_param('iisidd', $pedido_id, $producto_id, $nombre_producto, $cantidad, $precio_unitario, $total_detalle);
        if (!$stmt_detalle->execute()) {
            die("Error al guardar detalle del pedido: " . $stmt_detalle->error);
        }

        // Descontar del campo 'cantidad' (que es tu stock real en productos)
        $sql_update_stock = "UPDATE productos SET cantidad = cantidad - ? WHERE id = ? AND cantidad >= ?";
        $stmt_update = $conexion->prepare($sql_update_stock);
        $stmt_update->bind_param('iii', $cantidad, $producto_id, $cantidad);
        if (!$stmt_update->execute()) {
            die("Error al actualizar el stock: " . $stmt_update->error);
        }
        $stmt_update->close();
    }

    $stmt_detalle->close();

    // Vaciar el carrito del usuario
    $sql = "DELETE FROM carrito WHERE usuario_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('i', $usuario_id);
    $stmt->execute();
    $stmt->close();

    return $pedido_id;
}

/**
 * Función antigua mantenida por compatibilidad
 */
function enviarPedido($usuario_id, $direccion_id, $total, $metodo_pago){
    crearPedido($usuario_id, $direccion_id, $total, $metodo_pago);
    //Redirigir a página de confirmación
    header("Location: perfil.php");
    exit();
}        
?>