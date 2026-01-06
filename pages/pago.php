<?php 
include '../php/database.php';
include 'functions/f_perfil.php';
include 'functions/f_carrito.php';
include 'functions/f_pago.php';


session_start();

// Verificar si el usuario no está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Usar el ID del usuario logueado
$usuario_id = $_SESSION['usuario_id'];

manejarAccionesCarrito();

// Obtener datos del usuario
$usuario_id = $_SESSION['usuario_id'];
$direcciones = obtenerDireccionesUsuario($usuario_id);
   

// Procesar creación de pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevoPedido'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $direccion_id = isset($_POST['direccion_id']) ? trim($_POST['direccion_id']) : null;
    $total = isset($_POST['total']) ? trim($_POST['total']) : 0;
    $metodo_pago = isset($_POST['metodo_pago']) ? trim($_POST['metodo_pago']) : '';

    // Si hay direcciones registradas en la cuenta, obligamos a seleccionar una
    if (!empty($direcciones) && empty($direccion_id)) {
        $error = 'Debes seleccionar una dirección antes de continuar.';
    } else {
        // Crear el pedido (estado: pendiente)
        $pedido_id = crearPedido($usuario_id, $direccion_id, $total, $metodo_pago);
    
        // Redirigir a la página de pago de Mercado Pago
        ?>
        <form id="redirectForm" method="POST" action="mercado_pago_simulado.php" style="display:none;">
            <input type="hidden" name="pedido_id" value="<?php echo htmlspecialchars($pedido_id); ?>">
            <input type="hidden" name="total" value="<?php echo htmlspecialchars($total); ?>">
        </form>
        <script>
            document.getElementById('redirectForm').submit();
        </script>
        <?php
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Carrito de Compras</title>
      <!-- Bootstrap CSS -->
    <link rel="preload" 
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
          as="style" 
          onload="this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    </noscript>

    <!-- Font Awesome (optimizado con display=swap) -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
          integrity=""
          referrerpolicy="no-referrer"
          media="all">

</head>
<body>
    <?php include 'header.php';  ?>

    <div class="container py-4">
        <?php if (isset($error) && !empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form action="pago.php" method="POST">
            <input type="hidden" name="usuario_id" value="<?php echo $_SESSION['usuario_id']; ?>">
            
            <div class="row">

                <!-- lado izquierdo -->
                <div class="col-md-8 ">
                    <h2>Completa los Datos de Tu Pedido</h2>

                    <!-- Muestra de productos-->
                    <h3 class="mb-4" >Productos</h3>
                    <?php          
                        $usuario_id = $_SESSION['usuario_id'];
                        $sql = "SELECT p.id, p.nombre, p.precio, c.cantidad
                                FROM carrito c
                                JOIN productos p ON c.producto_id = p.id
                                WHERE c.usuario_id = ?";
                        $stmt = $conexion->prepare($sql);
                        $stmt->bind_param("i", $usuario_id);
                        $stmt->execute();
                        $resultado = $stmt->get_result();

                        echo '<div class="row">';
                        
                        while ($row = $resultado->fetch_assoc()) {
                                $subtotal = $row['precio'] * $row['cantidad']; 
                                 
                            echo '    
                            <div class="col-12 mb-3">
                                <div class="card w-100 p-3">
                                    <div class="card-body">
                                        <h5 class="card-title">' . htmlspecialchars($row['nombre']) . '</h5>
                                        <div class="card-text d-flex align-items-center gap-2">
                                            <p class="card-text">Precio unitario: $' . number_format($row['precio'], 2) . '</p>
                                            <p class="card-text mb-0">Cantidad: <span class="fw-bold">' . intval($row['cantidad']) . '</span></p>
                                            <p class="card-text mb-0 text-end ms-auto fw-bold">Subtotal: $' . number_format($subtotal, 2) . '</p>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                        }
                        echo '</div>';
                    ?>

                    <!-- Muestra de direcciones disponiles -->
                    <h3 class="mb-4" >Direccion</h3>
                    
                    
                    <?php if (empty($direcciones)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-map-marker-alt fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No tienes direcciones registradas</h5>
                            <p class="text-muted">Agrega tu primera dirección para realizar pedidos</p>
                            <button class="btn btn-primary" href="perfil.php">
                                <i class="fas fa-plus me-2"></i>Agregar Dirección
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($direcciones as $direccion): ?>
                                <div class="col-md-12 mb-3">
                                    <div class="card border">
                                        <label class="card-body d-flex gap-3 align-items-start form-check-label" for="direccion_<?php echo $direccion['id']; ?>">
                                            <input 
                                                class="form-check-input mt-1" 
                                                type="radio" 
                                                name="direccion_id" 
                                                id="direccion_<?php echo $direccion['id']; ?>" 
                                                value="<?php echo $direccion['id']; ?>" 
                                                required>
                                            <div class="w-100">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title mb-0"><?php echo htmlspecialchars($direccion['alias']); ?></h6>
                                                </div>
                                                <p class="card-text text-muted mb-1">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    <?php echo htmlspecialchars($direccion['direccion']); ?>
                                                </p>
                                                <p class="card-text text-muted mb-0">
                                                    <i class="fas fa-city me-1"></i>
                                                    <?php echo htmlspecialchars($direccion['ciudad'] . ', ' . $direccion['estado']); ?>
                                                </p>
                                                <p class="card-text text-muted">
                                                    <i class="fas fa-mail-bulk me-1"></i>
                                                    <?php echo htmlspecialchars($direccion['codigo_postal']); ?>
                                                </p>
                                                <?php if (!empty($direccion['es_principal'])): ?>
                                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Principal</span>
                                                <?php endif; ?>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <!-- Muestra los metodos de pago -->
                    <h3 class="mb-4" >Metodo de Pago</h3>
                    <div class="list-group">
                        <!-- Estos botones deberan cambiar el metodo_pago en la tabla de pedidos-->

                        <!-- Efectivo -->
                        <label class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input class="form-check-input me-2" type="radio" name="metodo_pago" value="efectivo" required>
                            <span class="fw-semibold">Efectivo</span>
                        </div>
                        </label>

                        <!-- Tarjeta -->
                        <label class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input class="form-check-input me-2" type="radio" name="metodo_pago" value="tarjeta" required>
                            <span class="fw-semibold">Mercado Pago</span>
                        </div>
                        </label>

                        <!-- Transferencia -->
                        <label class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input class="form-check-input me-2" type="radio" name="metodo_pago" value="transferencia" required>
                            <span class="fw-semibold">Transferencia</span>
                        </div>
                        </label>

                    </div>


                </div>

                <!-- lado derecho de recibo de compra -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">RESUMEN DEL PEDIDO</h6>
                        </div>
                        <div class="card-body" id="resumen-pedido">
                            <?php
                            if (verificarSesionUsuario()) {
                                $totales = calcularTotales($conexion, $_SESSION['usuario_id']);
                                echo '<input type="hidden" name="total" value="' . htmlspecialchars($totales['total']) . '">';

                                echo generarHTMLResumenPedido($totales);
                            } else {
                                echo '<p class="text-muted">Inicia sesión para ver el resumen</p>';
                            }
                            ?>
                            <button type="submit" name="nuevoPedido" class="btn btn-warning w-100 mt-3">Proceder al Pago</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php include 'footer.php'; ?>
    <!-- uso de boostrap para pagina responsiva-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>

</body>
</html>