<?php
require_once '../functions/f_login.php';

// Verificar que el usuario sea admin
if (!estaLogueado() || obtenerUsuario()['rol'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}
include('functions/f_admin_index.php');

$productoMasVendido = obtenerProductoMasVendido();
$usuariosActivos = obtenerUsuariosActivos();
$numeroPedidos = obtenerNumeroPedidos();
$ventasPorCategoria = obtenerVentasPorCategoria();
$alertasProductos = obtenerAlertasProductos();
$ventasMes = obtenerVentasMes();
$pedidosPorEstado = obtenerPedidosPorEstado();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MC Store - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="bi bi-x"></i>
                </div>
                <h3>MC Store</h3>
            </div>
            
            <nav class="sidebar-menu">
                <a href="admin_index.php" class="menu-item active">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="gestion_de_productos.php" class="menu-item">
                    <i class="bi bi-box-seam"></i>
                    <span>Productos</span>
                </a>
                <a href="gestion_pedidos.php" class="menu-item">
                    <i class="bi bi-clipboard-check"></i>
                    <span>Pedidos</span>
                </a>
                <a href="gestion_de_usuarios.php" class="menu-item">
                    <i class="bi bi-people-fill"></i>
                    <span>Usuarios</span>
                </a>
                <a href="gestion_catalogo.php" class="menu-item">
                    <i class="bi bi-tag"></i>
                    <span>Categorias</span>
                </a>
                <a href="configuracion.php" class="menu-item">
                    <i class="bi bi-gear"></i>
                    <span>Configuracion</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <a href="../../index.php" class="menu-item">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Volver a Pagina Principal</span>
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <header class="top-bar">
            </header>
            
            <!-- Content -->
            <div class="content">
                <div class="content-header">
                    <h2>Dashboard</h2>
                </div>
                
                <!-- Tarjetas de Estadísticas -->
                <div class="stats-container">
                    <!-- Producto más vendido -->
                    <div class="stat-card">
                        <div class="stat-content">
                            <h4>Producto mas vendido</h4>
                            <div class="stat-image">
                                <img id="imgProducto" src="<?php echo $productoMasVendido ? '../../img_productos/' . $productoMasVendido['imagen'] : ''; ?>" alt="Producto">
                            </div>
                            <p class="stat-value" id="productoNombre"><?php echo $productoMasVendido ? $productoMasVendido['nombre'] : 'No hay datos'; ?></p>
                            <small class="stat-label">Precio</small>
                            <p class="stat-value" id="productoPrecio"><?php echo $productoMasVendido ? '$' . number_format($productoMasVendido['precio'], 2) : '-'; ?></p>
                        </div>
                    </div>
                    
                    <!-- Margen de ganancias -->
                    <div class="stat-card">
                        <div class="stat-content">
                            <h4>Usuarios Activos</h4>
                            <p class="stat-percentage" id="usuarios Activos"><?php echo $usuariosActivos; ?>%</p>
                        </div>
                    </div>
                    
                    <!-- Número de pedidos -->
                    <div class="stat-card">
                        <div class="stat-content">
                            <h4>Numeros de pedidos</h4>
                            <p class="stat-number" id="numeroPedidos"><?php echo $numeroPedidos; ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Segunda fila de gráficos -->
                <div class="charts-row">
                    <!-- Ventas por categoría -->
                    <div class="chart-card">
                        <h4>Ventas por categoria</h4>
                        <div class="chart-wrapper">
                            <canvas id="ventasCategoriaChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Alertas de productos -->
                     <div class="alerts-card">
                        <h4>Alerta de productos</h4>

                        <!-- Contenedor con altura fija y scroll -->
                        <div class="alerts-list" id="alertasProductos" style="max-height: 220px; overflow-y: auto;">
                            <?php if (!empty($alertasProductos)): ?>
                                <?php foreach ($alertasProductos as $alerta): ?>
                                    <div class="alert-item d-flex justify-content-between align-items-center p-2 border-bottom">
                                        <p class="alert-name mb-0"><?php echo htmlspecialchars($alerta['nombre']); ?></p>

                                        <?php
                                            $estado = $alerta['estado'] ?? null;
                                            $cantidad = intval($alerta['cantidad'] ?? 0);
                                        ?>

                                        <?php if ($estado === 'agotado' || $cantidad === 0): ?>
                                            <span class="badge bg-danger">Agotado</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Stock Bajo</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-alerts">Sin alertas</p>
                            <?php endif; ?>
                        </div>
                    </div>


                </div>
                
                <!-- Ventas del mes -->
                <div class="chart-section">
                    <h4>Ventas del mes</h4>
                    <div class="chart-wrapper large">
                        <canvas id="ventasMesChart"></canvas>
                    </div>
                </div>
                
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defear></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" defear></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js" defear></script>
    
    <!-- Datos para gráficos -->
    <script>
        // Datos de ventas por categoría
        const ventasCategoriaData = <?php 
            $categorias = array_column($ventasPorCategoria, 'nombre');
            $cantidades = array_column($ventasPorCategoria, 'cantidad');
            echo json_encode(['categorias' => $categorias, 'cantidades' => $cantidades]);
        ?>;
        
        // Datos de ventas del mes
        const ventasMesData = <?php 
            $fechas = array_column($ventasMes, 'fecha');
            $montos = array_column($ventasMes, 'monto_total');
            echo json_encode(['fechas' => $fechas, 'montos' => $montos]);
        ?>;
        
        // Datos de pedidos por estado
        const pedidosPorEstadoData = <?php 
            $estados = array_column($pedidosPorEstado, 'estado');
            $cantidadesEstado = array_column($pedidosPorEstado, 'cantidad');
            echo json_encode(['estados' => $estados, 'cantidades' => $cantidadesEstado]);
        ?>;
    </script>
    
    <script src="js/admin_index.js" defear></script>
</body>
</html>