<?php
// f_gestion_pedidos.php
header('Content-Type: application/json');

require_once '../../../php/database.php';

if (!$conexion) {
    echo json_encode(['success' => false, 'mensaje' => 'Error de conexión a la base de datos']);
    exit();
}

function obtenerPedidos($filtroUsuario = '', $filtroEstatus = '', $filtroFecha = '') {
    global $conexion;
    
    $sql = "SELECT p.id, p.fecha_pedido, 
                   CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) as usuario,
                   p.metodo_pago, p.total, p.estado 
            FROM pedidos p 
            INNER JOIN usuarios u ON p.usuario_id = u.id 
            WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($filtroUsuario)) {
        $sql .= " AND (u.nombre LIKE ? OR u.apellido_paterno LIKE ? OR u.apellido_materno LIKE ?)";
        $searchTerm = "%" . $filtroUsuario . "%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }
    
    if (!empty($filtroEstatus)) {
        // Mapear los estados de la interfaz a los de la base de datos
        $estadosMap = [
            'Pendiente' => 'pendiente',
            'Confirmado' => 'confirmado', 
            'Preparando' => 'preparando',
            'En camino' => 'en_camino',
            'Entregado' => 'entregado'
        ];
        $estatusBD = isset($estadosMap[$filtroEstatus]) ? $estadosMap[$filtroEstatus] : $filtroEstatus;
        $sql .= " AND p.estado = ?";
        $params[] = $estatusBD;
        $types .= "s";
    }
    
    if (!empty($filtroFecha)) {
        $sql .= " AND DATE(p.fecha_pedido) = ?";
        $params[] = $filtroFecha;
        $types .= "s";
    }
    
    $sql .= " ORDER BY p.fecha_pedido DESC";
    
    try {
        if (!empty($params)) {
            $stmt = mysqli_prepare($conexion, $sql);
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);
        } else {
            $resultado = mysqli_query($conexion, $sql);
        }
        
        $pedidos = [];
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                // Formatear la fecha
                $fila['fecha_formateada'] = date('Y-m-d H:i:s', strtotime($fila['fecha_pedido']));
                // Mapear el estado de la base de datos al que usa la interfaz
                $estadosMapInverso = [
                    'pendiente' => 'Pendiente',
                    'confirmado' => 'Confirmado',
                    'preparando' => 'Preparando', 
                    'en_camino' => 'En camino',
                    'entregado' => 'Entregado',
                    'cancelado' => 'Cancelado'
                ];
                $fila['estatus'] = $estadosMapInverso[$fila['estado']] ?? $fila['estado'];
                $pedidos[] = $fila;
            }
        }
        
        if (isset($stmt)) {
            mysqli_stmt_close($stmt);
        }
        
        return $pedidos;
    } catch (Exception $e) {
        error_log("Error en obtenerPedidos: " . $e->getMessage());
        return [];
    }
}

function actualizarEstatusPedido($id, $nuevoEstatus) {
    global $conexion;
    
    // Mapear el estado de la interfaz al de la base de datos
    $estadosMap = [
        'Pendiente' => 'pendiente',
        'Confirmado' => 'confirmado',
        'Preparando' => 'preparando',
        'En camino' => 'en_camino', 
        'Entregado' => 'entregado'
    ];
    $estatusBD = isset($estadosMap[$nuevoEstatus]) ? $estadosMap[$nuevoEstatus] : $nuevoEstatus;
    
    $sql = "UPDATE pedidos SET estado = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "si", $estatusBD, $id);
    $resultado = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $resultado;
}

function obtenerDetallePedido($id) {
    global $conexion;
    
    // Información básica del pedido
    $sql = "SELECT p.*, 
                   CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) as usuario,
                   d.alias, d.direccion, d.ciudad, d.codigo_postal as cp
            FROM pedidos p
            INNER JOIN usuarios u ON p.usuario_id = u.id
            LEFT JOIN direcciones d ON p.direccion_id = d.id
            WHERE p.id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $pedido = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    if (!$pedido) {
        return null;
    }
    
    // Productos del pedido desde detalles_pedido
    $sqlProductos = "SELECT nombre_producto as nombre, cantidad, precio_unitario as precio, total as subtotal
                     FROM detalles_pedido 
                     WHERE pedido_id = ?";
    $stmtProductos = mysqli_prepare($conexion, $sqlProductos);
    mysqli_stmt_bind_param($stmtProductos, "i", $id);
    mysqli_stmt_execute($stmtProductos);
    $productos = mysqli_fetch_all(mysqli_stmt_get_result($stmtProductos), MYSQLI_ASSOC);
    mysqli_stmt_close($stmtProductos);
    
    $pedido['productos'] = $productos;
    
    return $pedido;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'obtener_pedidos':
            $pedidos = obtenerPedidos(
                $_POST['filtroUsuario'] ?? '', 
                $_POST['filtroEstatus'] ?? '', 
                $_POST['filtroFecha'] ?? ''
            );
            echo json_encode(['success' => true, 'pedidos' => $pedidos]);
            break;
            
        case 'actualizar_estatus':
            $resultado = actualizarEstatusPedido($_POST['id'] ?? 0, $_POST['nuevoEstatus'] ?? '');
            echo json_encode([
                'success' => $resultado, 
                'mensaje' => $resultado ? 'Estatus actualizado' : 'Error al actualizar'
            ]);
            break;
            
        case 'obtener_detalle':
            $pedido = obtenerDetallePedido($_POST['id'] ?? 0);
            echo json_encode([
                'success' => !!$pedido, 
                'pedido' => $pedido, 
                'mensaje' => $pedido ? '' : 'Pedido no encontrado'
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
    }
    exit;
}

// Si se accede directamente al archivo sin parámetros POST
echo json_encode(['success' => false, 'mensaje' => 'Acceso no autorizado']);
?>