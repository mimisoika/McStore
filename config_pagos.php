<?php
/**
 * CONFIGURACIÓN CENTRAL DEL SISTEMA DE PAGOS
 * 
 * Modificar estos valores para personalizar el sistema
 */

// ============================================================================
// CONFIGURACIÓN DE MERCADO PAGO
// ============================================================================

const MERCADO_PAGO_ENABLED = true;
const MERCADO_PAGO_MODO = 'simulado'; // 'simulado' o 'produccion'
const MERCADO_PAGO_ACCESS_TOKEN = 'Tu_Token_Aqui_Si_Es_Produccion';
const MERCADO_PAGO_PUBLIC_KEY = 'Tu_Public_Key_Aqui_Si_Es_Produccion';
const MERCADO_PAGO_CURRENCY = 'ARS'; // Moneda (ARS = Pesos Argentinos)

// ============================================================================
// CONFIGURACIÓN DE PAYPAL
// ============================================================================

const PAYPAL_ENABLED = false; // Deshabilitado por ahora
const PAYPAL_MODO = 'sandbox'; // 'sandbox' o 'live'
const PAYPAL_CLIENT_ID = 'Tu_Client_ID_Aqui';
const PAYPAL_CLIENT_SECRET = 'Tu_Client_Secret_Aqui';

// ============================================================================
// CONFIGURACIÓN DE PAGOS GENERAL
// ============================================================================

const PAGOS_MODULO_ACTIVO = true;

// Probabilidad de éxito en pago simulado (0-100)
const PROBABILIDAD_EXITO_PAGO = 90;

// Tiempo de espera al simular pago (segundos)
const TIEMPO_ESPERA_PAGO = 1;

// Método de pago por defecto
const METODO_PAGO_DEFECTO = 'tarjeta_credito';

// ============================================================================
// MÉTODOS DE PAGO DISPONIBLES
// ============================================================================

const METODOS_PAGO_DISPONIBLES = [
    'tarjeta_credito' => [
        'nombre' => 'Tarjeta de Crédito/Débito',
        'descripcion' => 'Visa, MasterCard, American Express',
        'icono' => 'fa-credit-card',
        'activo' => true,
        'comision' => 0.029 // 2.9%
    ],
    'mercado_credito' => [
        'nombre' => 'Mercado Crédito',
        'descripcion' => 'Hasta 12 cuotas sin interés',
        'icono' => 'fa-calculator',
        'activo' => true,
        'comision' => 0.0
    ],
    'efectivo' => [
        'nombre' => 'Pagar en Efectivo',
        'descripcion' => 'En Red Pagos y otros puntos de pago',
        'icono' => 'fa-money-bill',
        'activo' => true,
        'comision' => 0.015 // 1.5%
    ],
    'transferencia_bancaria' => [
        'nombre' => 'Transferencia Bancaria',
        'descripcion' => 'Desde tu cuenta bancaria',
        'icono' => 'fa-building',
        'activo' => true,
        'comision' => 0.0
    ],
    'paypal' => [
        'nombre' => 'PayPal',
        'descripcion' => 'Paga con tu cuenta de PayPal',
        'icono' => 'fa-paypal',
        'activo' => false, // Próxima integración
        'comision' => 0.049 // 4.9%
    ]
];

// ============================================================================
// CONFIGURACIÓN DE EMAIL
// ============================================================================

const EMAIL_CONFIRMACION_ACTIVO = false; // true cuando se implemente PHPMailer
const EMAIL_ADMIN = 'admin@mcstore.com';
const EMAIL_FROM = 'noreply@mcstore.com';
const EMAIL_FROM_NOMBRE = 'MC-Store';

// Servidor SMTP
const SMTP_HOST = 'smtp.gmail.com';
const SMTP_PORT = 587;
const SMTP_USER = 'tu-email@gmail.com';
const SMTP_PASS = 'tu-contraseña-app';

// ============================================================================
// CONFIGURACIÓN DE URLs
// ============================================================================

const URL_BASE = 'http://localhost/Mc-Store-Actualizacion-';
const URL_PAGOS = URL_BASE . '/pages/mercado_pago_simulado.php';
const URL_CONFIRMACION = URL_BASE . '/pages/confirmacion_pago.php';
const URL_ERROR = URL_BASE . '/pages/error_pago.php';
const URL_WEBHOOK_MP = URL_BASE . '/pages/webhook_mercadopago.php';
const URL_WEBHOOK_PAYPAL = URL_BASE . '/pages/webhook_paypal.php';

// ============================================================================
// CONFIGURACIÓN DE MONEDA Y FORMATO
// ============================================================================

const MONEDA_SIMBOLO = '$';
const MONEDA_DECIMALES = 2;
const MONEDA_SEPARADOR_DECIMAL = '.';
const MONEDA_SEPARADOR_MILES = ',';

// ============================================================================
// CONFIGURACIÓN DE SEGURIDAD
// ============================================================================

// Validar certificado SSL en producción
const VALIDAR_SSL = false; // Cambiar a true en producción

// Tiempo máximo de sesión de pago (minutos)
const TIEMPO_SESION_PAGO = 15;

// Intentos máximos de pago
const INTENTOS_MAXIMOS_PAGO = 3;

// ============================================================================
// CONFIGURACIÓN DE LOGS
// ============================================================================

const LOGS_ACTIVOS = true;
const LOGS_DIRECTORIO = __DIR__ . '/../logs/';
const LOG_PAGOS = LOGS_DIRECTORIO . 'pagos.log';
const LOG_ERRORES = LOGS_DIRECTORIO . 'errores.log';

// ============================================================================
// CONFIGURACIÓN DE BASE DE DATOS
// ============================================================================

// Se obtienen del archivo database.php
// Definir aquí si es necesario override

const DB_INTENTOS_CONEXION = 3;
const DB_TIMEOUT = 10;

// ============================================================================
// ESTADOS DE PEDIDO VÁLIDOS
// ============================================================================

const ESTADOS_PEDIDO = [
    'pendiente' => 'Pendiente de Pago',
    'pagado' => 'Pagado - Procesando',
    'enviado' => 'Enviado',
    'entregado' => 'Entregado',
    'cancelado' => 'Cancelado',
    'reembolsado' => 'Reembolsado',
    'fallido' => 'Pago Fallido'
];

// ============================================================================
// FUNCIONES DE AYUDA
// ============================================================================

/**
 * Obtener método de pago disponible
 */
function obtenerMetodoPago($codigo) {
    return METODOS_PAGO_DISPONIBLES[$codigo] ?? null;
}

/**
 * Verificar si método de pago está activo
 */
function esMetodoPagoActivo($codigo) {
    $metodo = obtenerMetodoPago($codigo);
    return $metodo ? $metodo['activo'] : false;
}

/**
 * Formatar moneda
 */
function formatearMoneda($cantidad) {
    return MONEDA_SIMBOLO . number_format(
        $cantidad,
        MONEDA_DECIMALES,
        MONEDA_SEPARADOR_DECIMAL,
        MONEDA_SEPARADOR_MILES
    );
}

/**
 * Calcular comisión de método de pago
 */
function calcularComision($cantidad, $codigo_metodo) {
    $metodo = obtenerMetodoPago($codigo_metodo);
    if (!$metodo) return 0;
    
    return $cantidad * $metodo['comision'];
}

/**
 * Calcular total con comisión
 */
function calcularTotalConComision($cantidad, $codigo_metodo) {
    $comision = calcularComision($cantidad, $codigo_metodo);
    return $cantidad + $comision;
}

/**
 * Registrar log de pago
 */
function registrarLogPago($pedido_id, $accion, $detalles = []) {
    if (!LOGS_ACTIVOS) return;
    
    $mensaje = "[" . date('Y-m-d H:i:s') . "] Pedido #$pedido_id - $accion\n";
    if (!empty($detalles)) {
        $mensaje .= json_encode($detalles) . "\n";
    }
    $mensaje .= "---\n";
    
    // Crear directorio si no existe
    if (!is_dir(LOGS_DIRECTORIO)) {
        mkdir(LOGS_DIRECTORIO, 0755, true);
    }
    
    file_put_contents(LOG_PAGOS, $mensaje, FILE_APPEND);
}

/**
 * Registrar error
 */
function registrarErrorPago($pedido_id, $error, $detalles = []) {
    if (!LOGS_ACTIVOS) return;
    
    $mensaje = "[" . date('Y-m-d H:i:s') . "] ERROR - Pedido #$pedido_id\n";
    $mensaje .= "Mensaje: " . $error . "\n";
    if (!empty($detalles)) {
        $mensaje .= "Detalles: " . json_encode($detalles) . "\n";
    }
    $mensaje .= "---\n";
    
    if (!is_dir(LOGS_DIRECTORIO)) {
        mkdir(LOGS_DIRECTORIO, 0755, true);
    }
    
    file_put_contents(LOG_ERRORES, $mensaje, FILE_APPEND);
}

/**
 * Obtener estado de pedido en español
 */
function obtenerEstadoPedido($estado) {
    return ESTADOS_PEDIDO[$estado] ?? $estado;
}

/**
 * Verificar si pagos están habilitados
 */
function estaModuloPagosActivo() {
    return PAGOS_MODULO_ACTIVO;
}

?>
