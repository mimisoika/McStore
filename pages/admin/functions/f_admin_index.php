<?php
require_once __DIR__ . '/../../../php/database.php';



// Obtener producto más vendido
function obtenerProductoMasVendido() {
    global $conexion;
    
    $query = "SELECT p.id, p.nombre, p.precio, p.imagen, COUNT(dp.producto_id) as cantidad_vendida
              FROM productos p
              LEFT JOIN detalles_pedido dp ON p.id = dp.producto_id
              GROUP BY p.id
              ORDER BY cantidad_vendida DESC
              LIMIT 1";
    
    $resultado = mysqli_query($conexion, $query);
    return mysqli_fetch_assoc($resultado);
}

function obtenerUsuariosActivos() {
    global $conexion;

    // Obtener total de usuarios
    $queryTotal = "SELECT COUNT(id) AS total FROM usuarios";
    $resultadoTotal = mysqli_query($conexion, $queryTotal);
    $total = mysqli_fetch_assoc($resultadoTotal)['total'];

    // Obtener solo usuarios activos
    $queryActivos = "SELECT COUNT(id) AS activos FROM usuarios WHERE usuario_estado = 'Activo'";
    $resultadoActivos = mysqli_query($conexion, $queryActivos);
    $activos = mysqli_fetch_assoc($resultadoActivos)['activos'];

    // Evitar división por cero
    if ($total == 0) {
        return 0;
    }

    // Calcular porcentaje real
    $porcentaje = round(($activos / $total) * 100, 1);

    return $porcentaje;
}



// Obtener número total de pedidos completados
function obtenerNumeroPedidos() {
    global $conexion;
    
    $query = "SELECT COUNT(id) as total_pedidos FROM pedidos WHERE estado = 'entregado'";
    $resultado = mysqli_query($conexion, $query);
    $datos = mysqli_fetch_assoc($resultado);
    
    return $datos['total_pedidos'];
}

// Obtener ventas por categoría
function obtenerVentasPorCategoria() {
    global $conexion;
    
    $query = "SELECT c.nombre, COUNT(dp.producto_id) as cantidad
              FROM categorias c
              LEFT JOIN productos p ON c.id = p.categoria_id
              LEFT JOIN detalles_pedido dp ON p.id = dp.producto_id
              GROUP BY c.id
              ORDER BY cantidad DESC
              LIMIT 5";
    
    $resultado = mysqli_query($conexion, $query);
    $datos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    
}

// Obtener alertas de productos (stock bajo, agotados)
function obtenerAlertasProductos() {
    global $conexion;
    
    // Incluir estado en la selección. Considerar productos con cantidad baja o cuyo estado indique agotado/poco_stock.
    $query = "SELECT id, nombre, cantidad, estado 
              FROM productos 
              WHERE cantidad <= 5 OR estado IN ('agotado','poco_stock')
              ORDER BY cantidad ASC
              LIMIT 50";
    
    $resultado = mysqli_query($conexion, $query);
    $datos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    
    // Si no hay alertas, devolver una vacía
    return $datos;
}

// Obtener datos de ventas del mes (por días)
function obtenerVentasMes() {
    global $conexion;
    
    $mes_actual = date('m');
    $ano_actual = date('Y');
    
    $query = "SELECT DATE(p.fecha_pedido) as fecha, COUNT(p.id) as total_ventas, COALESCE(SUM(p.total), 0) as monto_total
              FROM pedidos p
              WHERE MONTH(p.fecha_pedido) = $mes_actual 
              AND YEAR(p.fecha_pedido) = $ano_actual
              AND p.estado = 'entregado'
              GROUP BY DATE(p.fecha_pedido)
              ORDER BY fecha ASC";
    
    $resultado = mysqli_query($conexion, $query);
    $datos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    
    // Si no hay datos de ventas, generar datos de prueba
    if (empty($datos)) {
        $datos = [];
        for ($i = 1; $i <= 10; $i++) {
            $datos[] = [
                'fecha' => date('Y-m-d', strtotime("-$i days")),
                'total_ventas' => rand(1, 5),
                'monto_total' => rand(5000, 15000)
            ];
        }
        usort($datos, function($a, $b) { return strcmp($a['fecha'], $b['fecha']); });
    }
    
    return $datos;
}

// Obtener datos para gráfica de pedidos por estado
function obtenerPedidosPorEstado() {
    global $conexion;
    
    $query = "SELECT 
              estado,
              COUNT(id) as cantidad
              FROM pedidos
              GROUP BY estado";
    
    $resultado = mysqli_query($conexion, $query);
    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}

// Obtener productos favoritos más populares
function obtenerProductosFavoritosMasPopulares() {
    global $conexion;
    
    $query = "SELECT p.id, p.nombre, COUNT(pf.usuario_id) as cantidad_favoritos
              FROM productos p
              LEFT JOIN productos_favoritos pf ON p.id = pf.producto_id
              GROUP BY p.id
              ORDER BY cantidad_favoritos DESC
              LIMIT 5";
    
    $resultado = mysqli_query($conexion, $query);
    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}
?>