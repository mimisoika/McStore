<?php
/**
 * Página de error en el pago
 */

include '../php/database.php';

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$pedido_id = $_GET['pedido_id'] ?? null;
$usuario_id = $_SESSION['usuario_id'];

// Obtener datos del pedido
$sql = "SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('ii', $pedido_id, $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$pedido = $resultado->fetch_assoc();
$stmt->close();

$razon_fallo = $_SESSION['razon_fallo'] ?? 'El pago no pudo ser procesado. Por favor intenta de nuevo.';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error en el Pago</title>
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
        .error-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        .error-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .error-icon {
            width: 100px;
            height: 100px;
            background: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            color: white;
            animation: shake 0.5s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        .error-header h2 {
            color: #ef4444;
            margin-bottom: 10px;
        }
        .error-header p {
            color: #6c757d;
            font-size: 1.1rem;
        }
        .error-details {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
        }
        .error-details h5 {
            color: #dc2626;
            margin-bottom: 10px;
        }
        .error-details p {
            color: #7f1d1d;
            margin: 0;
        }
        .suggestions {
            background: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .suggestions h5 {
            color: #1e40af;
            margin-bottom: 15px;
        }
        .suggestions ul {
            margin: 0;
            padding-left: 20px;
        }
        .suggestions li {
            color: #1e3a8a;
            margin-bottom: 8px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        .action-buttons a, .action-buttons button {
            flex: 1;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-header">
            <div class="error-icon">
                <i class="fas fa-times"></i>
            </div>
            <h2>Pago No Procesado</h2>
            <p>No pudimos procesar tu pago en este momento</p>
        </div>

        <div class="error-details">
            <h5><i class="fas fa-exclamation-circle me-2"></i>Razón del Error</h5>
            <p><?php echo htmlspecialchars($razon_fallo); ?></p>
        </div>

        <?php if ($pedido): ?>
            <div class="alert alert-info">
                <strong>Pedido #<?php echo htmlspecialchars($pedido_id); ?></strong>
                <br>
                <small>Total: $<?php echo number_format($pedido['total'], 2); ?></small>
            </div>
        <?php endif; ?>

        <div class="suggestions">
            <h5><i class="fas fa-lightbulb me-2"></i>Sugerencias</h5>
            <ul>
                <li>Verifica que tengas fondos disponibles</li>
                <li>Intenta con otro método de pago</li>
                <li>Contacta a tu banco si el problema persiste</li>
                <li>Asegúrate de que tus datos sean correctos</li>
            </ul>
        </div>

        <div class="action-buttons">
            <a href="pago.php" class="btn btn-primary">
                <i class="fas fa-redo me-2"></i>Intentar de Nuevo
            </a>
            <a href="catalogo.php" class="btn btn-secondary">
                <i class="fas fa-home me-2"></i>Volver al Inicio
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defear></script>
    <?php
    // Limpiar variables de sesión
    unset($_SESSION['pago_fallido']);
    unset($_SESSION['razon_fallo']);
    ?>
</body>
</html>
