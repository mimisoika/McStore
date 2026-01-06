#!/usr/bin/env php
<?php
/**
 * SCRIPT DE VERIFICACIÓN DEL SISTEMA DE PAGOS
 * 
 * Ejecutar desde terminal:
 * php verificar_sistema_pagos.php
 * 
 * O desde navegador:
 * http://localhost/Mc-Store-Actualizacion-/verificar_sistema_pagos.php
 */

$errores = [];
$advertencias = [];
$exitosos = [];

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║     VERIFICACIÓN DEL SISTEMA DE PAGOS - MC-STORE          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// 1. VERIFICAR ARCHIVOS
// ============================================================================

echo "1. VERIFICANDO ARCHIVOS...\n";
echo str_repeat("─", 60) . "\n";

$archivos_requeridos = [
    'pages/pago.php' => 'Página de pago principal',
    'pages/mercado_pago_simulado.php' => 'Interfaz de Mercado Pago simulada',
    'pages/procesar_pago_mp.php' => 'Procesamiento de pago',
    'pages/confirmacion_pago.php' => 'Confirmación de pago',
    'pages/error_pago.php' => 'Página de error',
    'pages/functions/f_pago.php' => 'Funciones de pago',
    'php/database.php' => 'Conexión a base de datos',
    'php/actualizaciones_base_datos.sql' => 'Script SQL de actualización',
    'config_pagos.php' => 'Configuración del sistema',
    'SISTEMA_PAGOS_README.md' => 'Documentación completa',
    'GUIA_RAPIDA_PAGOS.txt' => 'Guía rápida'
];

$base_path = __DIR__;

foreach ($archivos_requeridos as $archivo => $descripcion) {
    $ruta_completa = $base_path . '/' . $archivo;
    
    if (file_exists($ruta_completa)) {
        echo "✅ $archivo\n";
        echo "   └─ $descripcion\n";
        $exitosos[] = $archivo;
    } else {
        echo "❌ $archivo\n";
        echo "   └─ $descripcion (NO ENCONTRADO)\n";
        $errores[] = "Falta archivo: $archivo";
    }
}

echo "\n";

// ============================================================================
// 2. VERIFICAR CONEXIÓN A BASE DE DATOS
// ============================================================================

echo "2. VERIFICANDO BASE DE DATOS...\n";
echo str_repeat("─", 60) . "\n";

try {
    $host = "localhost";
    $usuario = "root";
    $contrasena = "Ramirez034";
    $baseDeDatos = "comercializadora";
    
    $conexion = mysqli_connect($host, $usuario, $contrasena, $baseDeDatos);
    
    if ($conexion) {
        echo "✅ Conexión a base de datos exitosa\n";
        echo "   └─ Servidor: $host\n";
        echo "   └─ Base de datos: $baseDeDatos\n";
        
        // Verificar tabla pedidos
        $result = mysqli_query($conexion, "SHOW COLUMNS FROM pedidos");
        
        if ($result) {
            $columnas = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $columnas[] = $row['Field'];
            }
            
            echo "   └─ Tabla 'pedidos' encontrada\n";
            
            // Verificar columnas requeridas
            $columnas_requeridas = ['id', 'usuario_id', 'direccion_id', 'total', 'metodo_pago'];
            $columnas_nuevas = ['estado', 'fecha_pago', 'fecha_creacion'];
            
            foreach ($columnas_requeridas as $col) {
                if (in_array($col, $columnas)) {
                    echo "      ✅ Columna '$col'\n";
                } else {
                    echo "      ❌ Columna '$col' FALTANTE\n";
                    $errores[] = "Falta columna '$col' en tabla pedidos";
                }
            }
            
            foreach ($columnas_nuevas as $col) {
                if (in_array($col, $columnas)) {
                    echo "      ✅ Columna nueva '$col'\n";
                    $exitosos[] = "Columna '$col' creada";
                } else {
                    echo "      ⚠️  Columna nueva '$col' FALTANTE\n";
                    $advertencias[] = "Falta columna '$col'. Ejecuta actualizaciones_base_datos.sql";
                }
            }
        } else {
            $errores[] = "No se puede acceder a la tabla 'pedidos'";
        }
        
        // Verificar tabla usuarios
        $result = mysqli_query($conexion, "SELECT COUNT(*) as count FROM usuarios");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            echo "   └─ Usuarios en sistema: " . $row['count'] . "\n";
        }
        
        // Verificar tabla productos
        $result = mysqli_query($conexion, "SELECT COUNT(*) as count FROM productos");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            echo "   └─ Productos en catálogo: " . $row['count'] . "\n";
        }
        
        mysqli_close($conexion);
    } else {
        echo "❌ Error de conexión a base de datos\n";
        echo "   └─ " . mysqli_connect_error() . "\n";
        $errores[] = "No se puede conectar a la base de datos";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    $errores[] = $e->getMessage();
}

echo "\n";

// ============================================================================
// 3. VERIFICAR PERMISOS DE ARCHIVOS
// ============================================================================

echo "3. VERIFICANDO PERMISOS...\n";
echo str_repeat("─", 60) . "\n";

$archivos_verificar = [
    'pages/pago.php' => 'readable',
    'config_pagos.php' => 'readable',
    'php/database.php' => 'readable'
];

foreach ($archivos_verificar as $archivo => $permiso) {
    $ruta = $base_path . '/' . $archivo;
    
    if ($permiso === 'readable') {
        if (is_readable($ruta)) {
            echo "✅ $archivo (legible)\n";
        } else {
            echo "❌ $archivo (no legible)\n";
            $advertencias[] = "Archivo $archivo no tiene permisos de lectura";
        }
    }
}

// Verificar si se puede crear directorio logs
$logs_dir = $base_path . '/logs';
if (!is_dir($logs_dir)) {
    if (@mkdir($logs_dir, 0755)) {
        echo "✅ Directorio 'logs/' creado\n";
    } else {
        echo "⚠️  No se puede crear directorio 'logs/'\n";
        $advertencias[] = "No se puede crear directorio logs";
    }
} else {
    echo "✅ Directorio 'logs/' existe\n";
}

echo "\n";

// ============================================================================
// 4. VERIFICAR CONFIGURACIÓN PHP
// ============================================================================

echo "4. VERIFICANDO CONFIGURACIÓN PHP...\n";
echo str_repeat("─", 60) . "\n";

$php_version = phpversion();
echo "✅ Versión PHP: $php_version\n";

$extensiones_requeridas = [
    'mysqli' => 'MySQLi (base de datos)',
    'json' => 'JSON (procesamiento de datos)',
    'curl' => 'cURL (llamadas HTTP)',
    'openssl' => 'OpenSSL (seguridad)'
];

foreach ($extensiones_requeridas as $ext => $desc) {
    if (extension_loaded($ext)) {
        echo "✅ $desc ($ext)\n";
    } else {
        echo "⚠️  $desc ($ext) - NO INSTALADA\n";
        $advertencias[] = "Extensión $ext no instalada (requerida para algunas funciones)";
    }
}

// Verificar session
if (session_status() !== PHP_SESSION_DISABLED) {
    echo "✅ Sessions habilitadas\n";
} else {
    echo "❌ Sessions deshabilitadas\n";
    $errores[] = "Las sesiones están deshabilitadas";
}

echo "\n";

// ============================================================================
// 5. VERIFICAR FUNCIONES CRÍTICAS
// ============================================================================

echo "5. VERIFICANDO FUNCIONES CRÍTICAS...\n";
echo str_repeat("─", 60) . "\n";

$funciones_requeridas = [
    'mysqli_connect' => 'Conexión a BD',
    'json_encode' => 'Procesamiento JSON',
    'file_exists' => 'Manejo de archivos',
    'session_start' => 'Manejo de sesiones'
];

foreach ($funciones_requeridas as $func => $desc) {
    if (function_exists($func)) {
        echo "✅ $func ($desc)\n";
    } else {
        echo "❌ $func ($desc) - NO DISPONIBLE\n";
        $errores[] = "Función $func no disponible";
    }
}

echo "\n";

// ============================================================================
// 6. CARGAR CONFIGURACIÓN
// ============================================================================

echo "6. VERIFICANDO CONFIGURACIÓN...\n";
echo str_repeat("─", 60) . "\n";

try {
    require_once __DIR__ . '/config_pagos.php';
    echo "✅ Configuración de pagos cargada\n";
    
    if (MERCADO_PAGO_ENABLED) {
        echo "✅ Mercado Pago habilitado\n";
        echo "   └─ Modo: " . MERCADO_PAGO_MODO . "\n";
        echo "   └─ Moneda: " . MERCADO_PAGO_CURRENCY . "\n";
    }
    
    echo "✅ Métodos de pago disponibles: " . count(METODOS_PAGO_DISPONIBLES) . "\n";
    
    foreach (METODOS_PAGO_DISPONIBLES as $codigo => $metodo) {
        $estado = $metodo['activo'] ? '✅' : '❌';
        echo "   $estado " . $metodo['nombre'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error al cargar configuración: " . $e->getMessage() . "\n";
    $errores[] = "Error en config_pagos.php";
}

echo "\n";

// ============================================================================
// RESUMEN FINAL
// ============================================================================

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                      RESUMEN FINAL                         ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "✅ EXITOSOS: " . count($exitosos) . "\n";
echo "⚠️  ADVERTENCIAS: " . count($advertencias) . "\n";
echo "❌ ERRORES: " . count($errores) . "\n\n";

if (!empty($advertencias)) {
    echo "ADVERTENCIAS:\n";
    echo str_repeat("─", 60) . "\n";
    foreach ($advertencias as $i => $advertencia) {
        echo ($i + 1) . ". ⚠️  $advertencia\n";
    }
    echo "\n";
}

if (!empty($errores)) {
    echo "ERRORES (Debe corregir):\n";
    echo str_repeat("─", 60) . "\n";
    foreach ($errores as $i => $error) {
        echo ($i + 1) . ". ❌ $error\n";
    }
    echo "\n";
    
    echo "🔧 ACCIONES RECOMENDADAS:\n";
    echo "1. Ejecuta el script SQL: php/actualizaciones_base_datos.sql\n";
    echo "2. Verifica la conexión a base de datos\n";
    echo "3. Revisa los logs en el directorio logs/\n";
    echo "\n";
} else if (empty($advertencias)) {
    echo "🎉 ¡SISTEMA LISTO PARA USAR!\n";
    echo "\n";
    echo "Próximos pasos:\n";
    echo "1. Accede a: http://localhost/Mc-Store-Actualizacion-/pages/pago.php\n";
    echo "2. Completa el formulario y selecciona 'Proceder al Pago'\n";
    echo "3. Prueba el sistema con diferentes métodos de pago\n";
    echo "\n";
} else {
    echo "⚠️  REVISAR ADVERTENCIAS ANTES DE USAR EN PRODUCCIÓN\n";
    echo "\n";
}

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  Para más información, consulta: SISTEMA_PAGOS_README.md  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

?>
