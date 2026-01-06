<?php
/**
 * Página de confirmación de pago exitoso
 */

include '../php/database.php';

session_start();

if (!isset($_SESSION['usuario_id']) || !isset($_GET['pedido_id'])) {
    header('Location: catalogo.php');
    exit;
}

$pedido_id = $_GET['pedido_id'];
$usuario_id = $_SESSION['usuario_id'];

// Obtener datos del pedido
$sql = "SELECT p.*, u.nombre, u.email FROM pedidos p
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.id = ? AND p.usuario_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('ii', $pedido_id, $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$pedido = $resultado->fetch_assoc();
$stmt->close();

if (!$pedido) {
    header('Location: catalogo.php');
    exit;
}

// Obtener detalles del pedido
$sql = "SELECT * FROM detalles_pedido WHERE pedido_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $pedido_id);
$stmt->execute();
$resultado_detalles = $stmt->get_result();
$detalles = [];
while ($row = $resultado_detalles->fetch_assoc()) {
    $detalles[] = $row;
}
$stmt->close();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pago Confirmado</title>
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
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .confirmation-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        .success-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .success-icon {
            width: 100px;
            height: 100px;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            color: white;
            animation: scaleIn 0.5s ease-out;
        }
        @keyframes scaleIn {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }
        .success-header h2 {
            color: #10b981;
            margin-bottom: 10px;
        }
        .success-header p {
            color: #6c757d;
            font-size: 1.1rem;
        }
        .order-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 30px 0;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .order-detail {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 0.95rem;
        }
        .order-detail.total {
            border-top: 2px solid #dee2e6;
            padding-top: 15px;
            margin-top: 15px;
            font-weight: 600;
            font-size: 1.1rem;
            color: #10b981;
        }
        .info-section {
            margin: 25px 0;
        }
        .info-section h5 {
            font-weight: 600;
            margin-bottom: 15px;
            color: #212529;
        }
        .info-text {
            display: flex;
            align-items: center;
            padding: 10px 0;
            color: #495057;
        }
        .info-text i {
            width: 20px;
            margin-right: 10px;
            color: #10b981;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        .action-buttons a {
            flex: 1;
            text-align: center;
        }
        .badge-status {
            display: inline-block;
            background: #d1f2eb;
            color: #0f766e;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            margin-top: 10px;
        }
        .download-receipt {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .download-receipt:hover {
            border-color: #10b981;
            background: #f0fdf4;
        }
        .download-receipt i {
            color: #10b981;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="success-header">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h2>¡Pago Confirmado!</h2>
            <p>Tu pedido ha sido procesado exitosamente</p>
            <div class="badge-status">
                <i class="fas fa-check-circle me-1"></i>Estado: Pagado
            </div>
        </div>

        <div class="order-summary">
            <h5 style="margin-bottom: 20px;"><i class="fas fa-receipt me-2"></i>Resumen del Pedido</h5>
            
            <div class="order-detail">
                <span>Número de Pedido:</span>
                <strong>#<?php echo htmlspecialchars($pedido_id); ?></strong>
            </div>

            <div class="order-detail">
                <span>Fecha del Pedido:</span>
                <strong><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></strong>
            </div>

            <div class="order-detail">
                <span>Método de Pago:</span>
                <strong><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $pedido['metodo_pago']))); ?></strong>
            </div>

            <h6 style="margin-top: 20px; margin-bottom: 15px;">Productos:</h6>
            <?php foreach ($detalles as $detalle): ?>
                <div class="order-item">
                    <div>
                        <strong><?php echo htmlspecialchars($detalle['nombre_producto']); ?></strong>
                        <small class="text-muted d-block">Cantidad: <?php echo intval($detalle['cantidad']); ?></small>
                    </div>
                    <span>$<?php echo number_format($detalle['total'], 2); ?></span>
                </div>
            <?php endforeach; ?>

            <div class="order-detail total">
                <span>Total Pagado:</span>
                <span>$<?php echo number_format($pedido['total'], 2); ?></span>
            </div>
        </div>

        <div class="info-section">
            <h5><i class="fas fa-shipping-fast me-2"></i>Información de Envío</h5>
            <div class="info-text">
                <i class="fas fa-check"></i>
                <span>Tu pedido será procesado y enviado en las próximas 24 horas</span>
            </div>
            <div class="info-text">
                <i class="fas fa-bell"></i>
                <span>Recibirás un email en <strong><?php echo htmlspecialchars($pedido['email']); ?></strong></span>
            </div>
            <div class="info-text">
                <i class="fas fa-mobile-alt"></i>
                <span>Seguimiento disponible en tu perfil</span>
            </div>
        </div>

        <div class="download-receipt" onclick="window.print()">
            <i class="fas fa-print me-2"></i>
            <strong>Descargar Recibo</strong>
        </div>

        <div class="action-buttons">
            <a href="perfil.php" class="btn btn-primary">
                <i class="fas fa-user me-2"></i>Mi Perfil
            </a>
            <a href="catalogo.php" class="btn btn-secondary">
                <i class="fas fa-shopping-bag me-2"></i>Seguir Comprando
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defear></script>
</body>
</html>
