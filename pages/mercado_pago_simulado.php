<?php
/**
 * Página de simulación de pago en Mercado Pago
 * Esta página simula el flujo de redirección a Mercado Pago
 */

session_start();

// Verificar si llegan los datos del pedido
if (!isset($_POST['pedido_id']) || !isset($_SESSION['usuario_id'])) {
    header('Location: pago.php');
    exit;
}

    $pedido_id = $_POST['pedido_id'];
    $usuario_id = $_SESSION['usuario_id'];
    $total = $_POST['total'] ?? 0;

// Simular datos del checkout de Mercado Pago
$checkout_data = [
    'pedido_id' => $pedido_id,
    'usuario_id' => $usuario_id,
    'total' => $total,
    'timestamp' => time(),
    'preference_id' => 'MP-' . uniqid() // Simulamos un ID de preferencia
];

// Guardar datos en sesión para validar después
$_SESSION['mp_checkout'] = $checkout_data;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Procesando Pago - Mercado Pago</title>
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
        }
        .payment-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }
        .mercado-pago-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .mercado-pago-logo {
            font-size: 2.5rem;
            color: #3483FA;
            margin-bottom: 10px;
        }
        .payment-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .payment-info h5 {
            margin-bottom: 15px;
            font-weight: 600;
        }
        .payment-detail {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .payment-detail:last-child {
            border-bottom: none;
        }
        .payment-detail .label {
            color: #6c757d;
            font-size: 0.95rem;
        }
        .payment-detail .value {
            font-weight: 600;
            color: #212529;
        }
        .total-amount {
            padding: 15px 0;
            border-top: 2px solid #3483FA;
            border-bottom: 2px solid #3483FA;
            margin: 20px 0;
            text-align: center;
        }
        .total-amount .amount {
            font-size: 2rem;
            font-weight: bold;
            color: #3483FA;
        }
        .payment-methods {
            margin: 30px 0;
        }
        .payment-methods h5 {
            margin-bottom: 15px;
            font-weight: 600;
        }
        .method-option {
            display: flex;
            align-items: center;
            padding: 12px;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .method-option:hover {
            border-color: #3483FA;
            background: #f0f7ff;
        }
        .method-option input[type="radio"] {
            margin-right: 12px;
            cursor: pointer;
        }
        .method-option.selected {
            border-color: #3483FA;
            background: #f0f7ff;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        .action-buttons button {
            flex: 1;
        }
        .loading-spinner {
            text-align: center;
            display: none;
            padding: 20px;
        }
        .loading-spinner i {
            font-size: 2rem;
            color: #3483FA;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #3483FA;
            padding: 12px;
            border-radius: 4px;
            font-size: 0.9rem;
            color: #1565c0;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="mercado-pago-header">
            <div class="mercado-pago-logo">
                <i class="fas fa-credit-card"></i>
            </div>
            <h3 style="color: #3483FA;">Mercado Pago</h3>
            <p class="text-muted">Checkout seguro</p>
        </div>

        <form id="paymentForm" method="POST" action="procesar_pago_mp.php">
            <input type="hidden" name="pedido_id" value="<?php echo htmlspecialchars($pedido_id); ?>">
            <input type="hidden" name="usuario_id" value="<?php echo htmlspecialchars($usuario_id); ?>">
            <input type="hidden" name="total" value="<?php echo htmlspecialchars($total); ?>">

            <div class="payment-info">
                <h5><i class="fas fa-receipt me-2"></i>Resumen del Pedido</h5>
                <div class="payment-detail">
                    <span class="label">Número de Pedido:</span>
                    <span class="value">#<?php echo htmlspecialchars($pedido_id); ?></span>
                </div>
                <div class="payment-detail">
                    <span class="label">Estado:</span>
                    <span class="value"><span class="badge bg-warning">Pendiente</span></span>
                </div>
                <div class="total-amount">
                    <div class="label" style="margin-bottom: 5px;">Total a pagar</div>
                    <div class="amount">$<?php echo number_format($total, 2); ?></div>
                </div>
            </div>

            <div class="payment-methods">
                <h5><i class="fas fa-money-check me-2"></i>Método de Pago</h5>
                
                <label class="method-option">
                    <input type="radio" name="metodo_pago_mp" value="tarjeta" required onchange="updateMethodSelection(this)">
                    <div style="width: 100%;">
                        <strong>Tarjeta de Crédito/Débito</strong>
                        <small class="text-muted d-block">Visa, MasterCard, American Express</small>
                    </div>
                </label>

                <label class="method-option">
                    <input type="radio" name="metodo_pago_mp" value="tarjeta" required onchange="updateMethodSelection(this)">
                    <div style="width: 100%;">
                        <strong>Mercado Crédito</strong>
                        <small class="text-muted d-block">Hasta 12 cuotas sin interés</small>
                    </div>
                </label>

                <label class="method-option">
                    <input type="radio" name="metodo_pago_mp" value="efectivo" required onchange="updateMethodSelection(this)">
                    <div style="width: 100%;">
                        <strong>Pagar en Efectivo</strong>
                        <small class="text-muted d-block">En Red Pagos y otros puntos de pago</small>
                    </div>
                </label>

                <label class="method-option">
                    <input type="radio" name="metodo_pago_mp" value="transferencia" required onchange="updateMethodSelection(this)">
                    <div style="width: 100%;">
                        <strong>Transferencia Bancaria</strong>
                        <small class="text-muted d-block">Desde tu cuenta bancaria</small>
                    </div>
                </label>
            </div>

            <div class="info-box">
                <i class="fas fa-shield-alt me-2"></i>
                <strong>Seguridad:</strong> Tus datos están protegidos con encriptación SSL de 256 bits
            </div>

            <div class="action-buttons">
                <a href="pago.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Atrás
                </a>
                <button type="submit" class="btn btn-primary" id="payBtn" style="background-color: #3483FA; border-color: #3483FA;">
                    <i class="fas fa-lock me-2"></i>Confirmar Pago
                </button>
            </div>

            <div class="loading-spinner" id="loadingSpinner">
                <i class="fas fa-spinner"></i>
                <p class="mt-2">Procesando pago...</p>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateMethodSelection(element) {
            document.querySelectorAll('.method-option').forEach(option => {
                option.classList.remove('selected');
            });
            element.closest('.method-option').classList.add('selected');
        }

        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const selectedMethod = document.querySelector('input[name="metodo_pago_mp"]:checked');
            if (!selectedMethod) {
                e.preventDefault();
                alert('Por favor selecciona un método de pago');
                return false;
            }
            
            // Mostrar spinner de carga
            document.getElementById('loadingSpinner').style.display = 'block';
            document.getElementById('payBtn').disabled = true;
        });
    </script>
</body>
</html>
