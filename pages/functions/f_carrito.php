<?php
require_once __DIR__ . '/../../php/database.php';

function manejarAccionesCarrito() {
    global $conexion;
  
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return false;
    }
    
    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
        exit;
    }
    
    $accion = $_POST['accion'] ?? '';
    $usuario_id = $_SESSION['usuario_id']; 
    
    if ($accion === 'actualizar_cantidad') {
        $producto_id = (int)$_POST['producto_id'];
        $cantidad_solicitada = (int)$_POST['cantidad'];
        
        // VERIFICAR STOCK DISPONIBLE ANTES DE ACTUALIZAR
        $sql_stock = "SELECT cantidad, estado FROM productos WHERE id = ?";
        $stmt_stock = $conexion->prepare($sql_stock);
        $stmt_stock->bind_param("i", $producto_id);
        $stmt_stock->execute();
        $result_stock = $stmt_stock->get_result();
        
        if ($result_stock->num_rows > 0) {
            $producto = $result_stock->fetch_assoc();
            $stock_disponible = (int)$producto['cantidad'];
            $estado = $producto['estado'];
            
            // Verificar que el producto esté disponible
            if ($estado === 'suspendido' || $estado === 'agotado') {
                echo json_encode(['success' => false, 'message' => 'Este producto no está disponible actualmente']);
                exit;
            }
            
            // AJUSTAR LA CANTIDAD SI SUPERA EL STOCK DISPONIBLE
            $cantidad_final = $cantidad_solicitada;
            
            if ($cantidad_solicitada > $stock_disponible) {
                $cantidad_final = $stock_disponible;
            }
            
            // Verificar que la cantidad sea al menos 1
            if ($cantidad_final < 1) {
                // Si no hay stock, eliminar del carrito
                $sql_eliminar = "DELETE FROM carrito WHERE usuario_id = ? AND producto_id = ?";
                $stmt_eliminar = $conexion->prepare($sql_eliminar);
                $stmt_eliminar->bind_param("ii", $usuario_id, $producto_id);
                $stmt_eliminar->execute();
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Producto eliminado del carrito por falta de stock',
                    'stock_disponible' => 0,
                    'cantidad_ajustada' => 0,
                    'eliminado' => true
                ]);
                exit;
            }
            
            // Si la cantidad fue ajustada, informar al usuario
            $mensaje = '';
            $ajustado = false;
            if ($cantidad_final !== $cantidad_solicitada) {
                $mensaje = 'Cantidad ajustada al stock disponible: ' . $cantidad_final;
                $ajustado = true;
            }
            
            // Actualizar la cantidad en el carrito
            $sql = "UPDATE carrito SET cantidad = ? WHERE usuario_id = ? AND producto_id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("iii", $cantidad_final, $usuario_id, $producto_id);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true, 
                    'stock_disponible' => $stock_disponible,
                    'cantidad_ajustada' => $cantidad_final,
                    'mensaje' => $mensaje,
                    'ajustado' => $ajustado
                ]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar la cantidad']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
            exit;
        }
    }
    if ($accion === 'eliminar_producto') {
        $producto_id = (int)$_POST['producto_id'];
        
        $sql = "DELETE FROM carrito WHERE usuario_id = ? AND producto_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $usuario_id, $producto_id);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
        exit;
    }
    
    if ($accion === 'verificar_stock') {
        // Nueva acción para verificar stock antes de permitir agregar al carrito
        $producto_id = (int)$_POST['producto_id'];
        $cantidad_deseada = (int)($_POST['cantidad'] ?? 1);
        
        $sql = "SELECT cantidad, estado, nombre FROM productos WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $producto_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $producto = $result->fetch_assoc();
            $stock_disponible = (int)$producto['cantidad'];
            $estado = $producto['estado'];
            
            if ($estado === 'suspendido' || $estado === 'agotado') {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Producto no disponible: ' . $producto['nombre'],
                    'disponible' => false
                ]);
                exit;
            }
            
            if ($cantidad_deseada > $stock_disponible) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Stock insuficiente. Disponible: ' . $stock_disponible,
                    'disponible' => false,
                    'stock_disponible' => $stock_disponible
                ]);
                exit;
            }
            
            echo json_encode([
                'success' => true, 
                'disponible' => true,
                'stock_disponible' => $stock_disponible
            ]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
            exit;
        }
    }
    
    return false;
}

function calcularTotales($conexion, $usuario_id) {
    $sql = "SELECT SUM(p.precio * c.cantidad) as subtotal 
            FROM carrito c 
            JOIN productos p ON c.producto_id = p.id 
            WHERE c.usuario_id = ? AND p.estado NOT IN ('suspendido', 'agotado')";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $row = $resultado->fetch_assoc();
    
    $subtotal = $row['subtotal'] ?? 0;
    $envio = 150.00; 
    $iva = $subtotal * 0.16; 
    $total = $subtotal + $envio + $iva;
    
    return [
        'subtotal' => $subtotal,
        'envio' => $envio,
        'iva' => $iva,
        'total' => $total
    ];
}

function obtenerProductosCarrito($conexion, $usuario_id) {
    $sql = "SELECT p.id, p.nombre, p.precio, p.imagen, p.cantidad as stock, p.estado, c.cantidad
            FROM carrito c
            JOIN productos p ON c.producto_id = p.id
            WHERE c.usuario_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $productos = [];
    while ($row = $resultado->fetch_assoc()) {
        $productos[] = $row;
    }
    
    return $productos;
}

function generarHTMLProductosCarrito($productos) {
    if (empty($productos)) {
        return '<div class="alert alert-info">Tu carrito está vacío</div>';
    }
    $html = '<div class="row">';
    
    foreach ($productos as $producto) {
        $subtotal = $producto['precio'] * $producto['cantidad'];
        $stock_disponible = (int)$producto['stock'];
        $estado = $producto['estado'];
        
        // Determinar clase CSS según disponibilidad
        $clase_disponibilidad = '';
        $mensaje_disponibilidad = '';
        
        if ($estado === 'suspendido' || $estado === 'agotado') {
            $clase_disponibilidad = 'border-danger';
            $mensaje_disponibilidad = '<div class="alert alert-danger alert-sm mb-2">Producto no disponible</div>';
        } elseif ($producto['cantidad'] > $stock_disponible) {
            $clase_disponibilidad = 'border-warning';
            $mensaje_disponibilidad = '<div class="alert alert-warning alert-sm mb-2">Cantidad excede stock disponible (' . $stock_disponible . ')</div>';
        } elseif ($stock_disponible < 11 && $stock_disponible > 0) {
            $clase_disponibilidad = 'border-info';
            $mensaje_disponibilidad = '<div class="alert alert-info alert-sm mb-2">Últimas unidades (' . $stock_disponible . ' disponibles)</div>';
        }
        
        $max_cantidad = $stock_disponible;
        $disabled = ($estado === 'suspendido' || $estado === 'agotado') ? 'disabled' : '';
        
        $html .= '
        <div class="col-md-6 mb-3" data-producto-id="' . $producto['id'] . '">
            <div class="card ' . $clase_disponibilidad . '">
                ' . $mensaje_disponibilidad . '
                <div class="row g-0">
                    <div class="col-4">
                        <img src="../img_productos/' . $producto['imagen'] . '" class="img-fluid" alt="' . $producto['nombre'] . '">
                    </div>
                    <div class="col-8">
                        <div class="card-body">
                            <h6>' . $producto['nombre'] . '</h6>
                            <p>Precio: $' . number_format($producto['precio'], 2) . '</p>
                            <p>Subtotal: $' . number_format($subtotal, 2) . '</p>
                            <p class="text-muted small">Stock disponible: ' . $stock_disponible . '</p>
                            
                            <div class="d-flex align-items-center mb-2">
                                <button class="btn btn-sm btn-outline-secondary btn-restar" ' . $disabled . '>-</button>
                                <input type="number" class="form-control form-control-sm cantidad-input mx-2" 
                                       style="width: 60px;" 
                                       value="' . $producto['cantidad'] . '" 
                                       min="1" 
                                       max="' . $max_cantidad . '"
                                       data-stock-max="' . $max_cantidad . '"
                                       ' . $disabled . '>
                                <button class="btn btn-sm btn-outline-secondary btn-sumar" ' . $disabled . '>+</button>
                            </div>
                            
                            <button class="btn btn-sm btn-danger btn-eliminar">Eliminar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }
    
    $html .= '</div>';
    return $html;
}

function generarHTMLResumenPedido($totales) {
    return '
    <ul class="list-unstyled">
        <li class="d-flex justify-content-between">Subtotal: <span>$' . number_format($totales['subtotal'], 2) . '</span></li>
        <li class="d-flex justify-content-between">Envío: <span>$' . number_format($totales['envio'], 2) . '</span></li>
        <li class="d-flex justify-content-between">IVA (16%): <span>$' . number_format($totales['iva'], 2) . '</span></li>
    </ul>
    <hr>
    <p class="d-flex justify-content-between"><strong>Total: <span>$' . number_format($totales['total'], 2) . '</span></strong></p>';
}

function verificarSesionUsuario() {
    return true;
}
?>