<?php
require_once __DIR__ . '/../../php/database.php';

function obtenerDetallesPedido($pedido_id) {
    global $conexion;

    $sql = "SELECT 
                nombre_producto, cantidad, precio_unitario, total
            FROM detalles_pedido
            WHERE pedido_id = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $detalles = [];
    while ($row = $result->fetch_assoc()) {
        $detalles[] = $row;
    }
    $stmt->close();

    // Si no hay resultados
    if (empty($detalles)) {
        return '<div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    No se encontraron detalles para este pedido.
                </div>';
    }

    // Calcular subtotal
    $subtotal = 0;
    foreach ($detalles as $detalle) {
        $subtotal += $detalle['total'];
    }

    // Obtener el total del pedido desde la BD
    $sql_pedido = "SELECT total FROM pedidos WHERE id = ?";
    $stmt_pedido = $conexion->prepare($sql_pedido);
    $stmt_pedido->bind_param("i", $pedido_id);
    $stmt_pedido->execute();
    $result_pedido = $stmt_pedido->get_result();
    $row_pedido = $result_pedido->fetch_assoc();
    $total_pedido = $row_pedido['total'] ?? 0;
    $stmt_pedido->close();

    // Calcular envío e IVA
    $envio = 150.00;
    $iva = $subtotal * 0.16;
    
    // Si el total es 0, significa que el carrito estaba vacío, así que no hay envío
    if ($total_pedido == 0) {
        $envio = 0;
        $iva = 0;
    }

    // Generar tabla HTML
    $html = '<div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-end">Precio Unitario</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>';

    foreach ($detalles as $detalle) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($detalle['nombre_producto']) . '</td>';
        $html .= '<td class="text-center">' . $detalle['cantidad'] . '</td>';
        $html .= '<td class="text-end">$' . number_format($detalle['precio_unitario'], 2) . '</td>';
        $html .= '<td class="text-end">$' . number_format($detalle['total'], 2) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody>
                </table>
            </div>';

    // Agregar resumen del pedido
    $html .= '<div class="mt-4">
                <h6>Resumen del Pedido</h6>
                <ul class="list-unstyled">
                    <li class="d-flex justify-content-between">
                        <span>Subtotal:</span>
                        <span>$' . number_format($subtotal, 2) . '</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span>Envío:</span>
                        <span>$' . number_format($envio, 2) . '</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span>IVA (16%):</span>
                        <span>$' . number_format($iva, 2) . '</span>
                    </li>
                </ul>
                <hr>
                <p class="d-flex justify-content-between">
                    <strong>Total del Pedido:</strong>
                    <strong class="text-success">$' . number_format($total_pedido, 2) . '</strong>
                </p>
            </div>';

    return $html;
}
?>