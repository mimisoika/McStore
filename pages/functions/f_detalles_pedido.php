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

    // Calcular total
    $total_pedido = 0;
    foreach ($detalles as $detalle) {
        $total_pedido += $detalle['total'];
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
                <tfoot class="table-secondary">
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Total del Pedido:</td>
                        <td class="text-end fw-bold text-success">$' . number_format($total_pedido, 2) . '</td>
                    </tr>
                </tfoot>
            </table>
        </div>';

    return $html;
}
?>