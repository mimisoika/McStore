<?php
require_once __DIR__ . '/../../php/database.php';
require_once 'f_login.php';

/**
 * Obtiene todos los datos del usuario incluyendo dirección principal
 */
function obtenerDatosCompletos($usuario_id) {
    global $conexion;
    
    $consulta = "SELECT u.*, d.alias, d.direccion, d.ciudad, d.codigo_postal, d.estado 
                 FROM usuarios u 
                 LEFT JOIN direcciones d ON u.id = d.usuario_id AND d.es_principal = 1 
                 WHERE u.id = ?";
    
    $stmt = mysqli_prepare($conexion, $consulta);
    mysqli_stmt_bind_param($stmt, "i", $usuario_id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    if ($usuario = mysqli_fetch_assoc($resultado)) {
        return $usuario;
    }
    return null;
}

/**
 * Obtiene el historial de pedidos del usuario
 */
function obtenerPedidosUsuario($usuario_id) {
    global $conexion;
    
    $consulta = "SELECT p.id, p.fecha_pedido, p.total, p.estado 
                 FROM pedidos p 
                 WHERE p.usuario_id = ? 
                 ORDER BY p.fecha_pedido DESC";
    
    $stmt = mysqli_prepare($conexion, $consulta);
    mysqli_stmt_bind_param($stmt, "i", $usuario_id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    $pedidos = [];
    while ($pedido = mysqli_fetch_assoc($resultado)) {
        $pedidos[] = $pedido;
    }
    return $pedidos;
}

/**
 * Obtiene todas las direcciones del usuario
 */
function obtenerDireccionesUsuario($usuario_id) {
    global $conexion;
    
    $consulta = "SELECT * FROM direcciones WHERE usuario_id = ? ORDER BY es_principal DESC, fecha_creacion DESC";
    
    $stmt = mysqli_prepare($conexion, $consulta);
    mysqli_stmt_bind_param($stmt, "i", $usuario_id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    $direcciones = [];
    while ($direccion = mysqli_fetch_assoc($resultado)) {
        $direcciones[] = $direccion;
    }
    return $direcciones;
}

function actualizarDatosPersonales($usuario_id, $datos) {
    global $conexion;
    
    $consulta = "UPDATE usuarios SET 
                 nombre = ?, 
                 apellido_paterno = ?, 
                 apellido_materno = ?, 
                 telefono = ? 
                 WHERE id = ?";
    
    $stmt = mysqli_prepare($conexion, $consulta);
    mysqli_stmt_bind_param($stmt, "ssssi", 
        $datos['nombre'], 
        $datos['apellido_paterno'], 
        $datos['apellido_materno'], 
        $datos['telefono'], 
        $usuario_id
    );
    
    return mysqli_stmt_execute($stmt);
}

function manejarActualizacionDatos($usuario_id) {
    $resultado = ['exito' => false, 'mensaje' => '', 'datos' => null];
    
    if (isset($_POST['actualizar_datos'])) {
        $datos = [
            'nombre' => $_POST['nombre'],
            'apellido_paterno' => $_POST['apellido_paterno'],
            'apellido_materno' => $_POST['apellido_materno'],
            'telefono' => $_POST['telefono']
        ];
        
        if (actualizarDatosPersonales($usuario_id, $datos)) {
            $resultado['exito'] = true;
            $resultado['mensaje'] = "Datos actualizados correctamente";
            // Recargar datos
            $resultado['datos'] = obtenerDatosCompletos($usuario_id);
        } else {
            $resultado['mensaje'] = "Error al actualizar los datos";
        }
    }
    
    return $resultado;
}

function generarBadgeEstado($estado) {
    $badges = [
        'pendiente' => 'bg-warning text-dark',
        'procesando' => 'bg-info',
        'enviado' => 'bg-primary',
        'entregado' => 'bg-success',
        'cancelado' => 'bg-danger'
    ];
    
    $clase = $badges[$estado] ?? 'bg-secondary';
    return "<span class='badge {$clase}'>" . ucfirst($estado) . "</span>";
}

function guardarDireccion(){
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    global $conexion;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_direccion'])) {
        $usuario_id = $_SESSION['usuario_id'];
        $alias = trim($_POST['alias']);
        $direccion = trim($_POST['direccion']);
        $ciudad = trim($_POST['ciudad']);
        $codigo_postal = trim($_POST['codigo_postal']);
        $estado = trim($_POST['estado']);
        $pais = !empty($_POST['pais']) ? trim($_POST['pais']) : 'México';
        $instrucciones = trim($_POST['instrucciones_entrega']);
        $es_principal = isset($_POST['es_principal']) ? 1 : 0;

        // Validar código postal
        $validacion_cp = validarCodigoPostalBCS($codigo_postal);
        if (!$validacion_cp['valido']) {
            header("Location: perfil.php?error=" . urlencode($validacion_cp['mensaje']));
            exit();
        }

        if ($es_principal) {
            $sql_reset = "UPDATE direcciones SET es_principal = 0 WHERE usuario_id = ?";
            $stmt_reset = $conexion->prepare($sql_reset);
            $stmt_reset->bind_param('i', $usuario_id);
            $stmt_reset->execute();
        }

        $sql = "INSERT INTO direcciones (
            usuario_id, alias, direccion, ciudad, codigo_postal, estado, pais, instrucciones_entrega, es_principal
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('isssssssi', $usuario_id, $alias, $direccion, $ciudad, $codigo_postal, $estado, $pais, $instrucciones, $es_principal);

        if ($stmt->execute()) {
            header("Location: perfil.php?mensaje=Dirección+guardada+correctamente");
            exit();
        } else {
            header("Location: perfil.php?error=Error+al+guardar+la+dirección");
            exit();
        }
    }
}

/**
 * Valida el código postal para Baja California Sur (Comondú y Loreto)
 */
function validarCodigoPostalBCS($codigo_postal) {
    $resultado = ['valido' => false, 'mensaje' => '', 'asentamiento' => ''];
    
    // Validar formato: debe empezar con 23 y tener 5 dígitos
    if (!preg_match('/^23\d{3}$/', $codigo_postal)) {
        $resultado['mensaje'] = 'Formato de CP inválido. Debe empezar con 23 y tener 5 dígitos.';
        return $resultado;
    }
    
    // Definir rangos válidos para Comondú y Loreto basados en los PDFs
    // NOTA: 23790 aparece en ambos arrays, lo mantenemos solo una vez
    $rangos_comondu = [
        '23600', '23610', '23620', '23630', '23640', '23641', '23643', '23650', '23653', '23658', '23660',
        '23670', '23676', '23677', '23678', '23680', '23683', '23690', '23695', '23696', '23697',
        '23700', '23708', '23710', '23715', '23720', '23721', '23723', '23730', '23736', '23737', '23739',
        '23740', '23743', '23748', '23749', '23750', '23760', '23765', '23766', '23770', '23771', '23774',
        '23775', '23780', '23789', '23800', '23805', '23810', '23812', '23813', '23818', '23820',
        '23824', '23830', '23834', '23837', '23838', '23840', '23844', '23845', '23860', '23870', '23873'
    ];
    
    $rangos_loreto = [
        '23790', '23880', '23883', '23884', '23885', '23886', '23887', '23888', '23889', '23890',
        '23893', '23894', '23895', '23896', '23897', '23898'
    ];
    
    // Combinar todos los códigos postales válidos (eliminando duplicados)
    $cps_validos = array_unique(array_merge($rangos_comondu, $rangos_loreto));
    
    // DEBUG: Verificar si el CP está en la lista
    if (!in_array($codigo_postal, $cps_validos)) {
        $resultado['mensaje'] = 'CP no perteneciente a la región de Baja California Sur (Comondú o Loreto)';
        return $resultado;
    }
    
    // Obtener información del asentamiento
    $asentamiento = obtenerAsentamientoPorCP($codigo_postal);
    
    $resultado['valido'] = true;
    $resultado['mensaje'] = 'CP válido';
    $resultado['asentamiento'] = $asentamiento;
    
    return $resultado;
}

/**
 * Obtiene el nombre del asentamiento basado en el código postal
 */
function obtenerAsentamientoPorCP($codigo_postal) {
    $asentamientos = [
        // Comondú - Ciudad Constitución
        '23600' => 'Zona Centro, Cerro Catedral, INVI Olivos Juan Domínguez Cota',
        '23610' => 'Las Palmas',
        '23620' => 'Cecilia Madrid, Longoria, Vargas, Renero',
        '23630' => 'Los Olivos',
        '23640' => 'El Paraíso, FOVISSSTE Olimpico, Olímpico',
        '23641' => '4 de Marzo, Ampliación 4 de marzo, Conjunto Urbano del Norte',
        '23643' => 'Valle Dorado',
        '23650' => 'INVI Hacienda, Lienzo Charro',
        '23653' => 'Militar',
        '23658' => 'Residencial La Hacienda',
        '23660' => 'Guaycura',
        '23670' => 'El Crucero, Los Pinos, Pueblo Nuevo',
        '23676' => 'Chato Covarrubias, FOVISSSTE Pioneros, Pioneros, Pioneros II, Revolución Mexicana',
        '23677' => 'Conjunto Urbano del Sur, Constitución, La Esperanza, Salomón Sández',
        '23678' => 'Plano Oriente, San Isidro Labrador',
        '23680' => 'FOVISSSTE Real, Real, Valle Paraíso',
        '23683' => 'Paseos de Don Pelayo',
        '23690' => 'Batequitos, INFONAVIT San Martín',
        '23695' => 'Roberto Esperon',
        '23696' => 'El Agricultor INDECO, La Roca, Los Romeros',
        '23697' => 'Brisas del Valle, Magisterial',
        
        // Comondú - Otras localidades
        '23700' => 'Ciudad Insurgentes, Fernando de la Toba',
        '23708' => 'Rio Mayo',
        '23710' => 'Puerto Adolfo López Mateos',
        '23715' => 'Villa Ignacio Zaragoza',
        '23720' => 'Villa Hidalgo, Teotlán',
        '23721' => 'Ramaditas, San Juan de Matancitas',
        '23723' => 'Josefa Ortiz de Domínguez',
        '23730' => 'Benito Juárez',
        '23736' => 'Palo Bola',
        '23737' => 'El Vallecito',
        '23739' => 'Navojoa 1',
        '23740' => 'Puerto San Carlos',
        '23743' => 'El Ranchito',
        '23748' => 'Puerto Magdalena',
        '23749' => 'Puerto Alcatraz',
        '23750' => 'Puerto Cortés',
        '23760' => 'Ley Federal de Aguas Número Cinco',
        '23765' => 'San Luis Gonzaga, Buenos Aires',
        '23766' => 'Tepentú',
        '23770' => 'Villa Morelos',
        '23771' => 'El Vergel',
        '23774' => 'Las Delicias',
        '23775' => 'Yaquis Lote 13',
        '23780' => 'Ley Federal de Aguas Número Cuatro',
        '23789' => 'Santa Teresa',
        '23790' => 'Ley Federal de Aguas Número Tres',
        '23800' => 'San Miguel de Comondú',
        '23805' => 'San Pedro',
        '23810' => 'San Isidro',
        '23812' => 'San Juanico, La Yaqui',
        '23813' => 'Purísima Vieja, Paso Hondo',
        '23818' => 'La Bocana de San Gregorio',
        '23820' => 'San José de Comondú, La Purísima, El Pabellón, Carambuche',
        '23824' => 'El Ojo de Agua',
        '23830' => 'La Poza Grande, Francisco Villa',
        '23834' => 'Las Barrancas, El Chicharrón',
        '23837' => 'San Venancio',
        '23838' => 'El Canelo',
        '23840' => 'María Auxiliadora',
        '23844' => 'Santo Domingo, Jalisco',
        '23845' => 'Palo Alto',
        '23860' => 'Ley Federal de Aguas Número Dos',
        '23870' => 'Ley Federal de Aguas Número Uno',
        '23873' => 'San Ignacio',
        
        // Loreto
        '23880' => 'Zona Centro Loreto',
        '23883' => 'Lomas de Loreto',
        '23884' => 'Nopolo',
        '23885' => 'Puerto Escondido',
        '23886' => 'San Javier',
        '23887' => 'Ligüí',
        '23888' => 'Agua Verde',
        '23889' => 'Comondú',
        '23890' => 'Insurgentes',
        '23893' => 'Villa del Palmar',
        '23894' => 'Mision de Loreto',
        '23895' => 'Las Cuevas',
        '23896' => 'Ensenada Blanca',
        '23897' => 'Loreto Bay',
        '23898' => 'Juncalito'
    ];
    
    return $asentamientos[$codigo_postal] ?? 'Asentamiento no identificado';
}

function marcarDireccionComoPrincipal($usuario_id, $direccion_id) {
    global $conexion;

    // Primero se desactiva la dirección marcada como principal
    $sql1 = "UPDATE direcciones SET es_principal = 0 WHERE usuario_id = ?";
    $stmt1 = $conexion->prepare($sql1);
    $stmt1->bind_param("i", $usuario_id);
    $stmt1->execute();

    // Luego se activa la nueva principal
    $sql2 = "UPDATE direcciones SET es_principal = 1 WHERE id = ? AND usuario_id = ?";
    $stmt2 = $conexion->prepare($sql2);
    $stmt2->bind_param("ii", $direccion_id, $usuario_id);
    
    if ($stmt2->execute()) {
        header("Location: perfil.php?mensaje=Dirección+principal+actualizada");
        exit();
    } else {
        header("Location: perfil.php?error=Error+al+actualizar+la+dirección+principal");
        exit();
    }
}

/**
 * Cancela un pedido si está en estado pendiente
 */
function cancelarPedido($pedido_id, $usuario_id) {
    global $conexion;
    
    $resultado = ['exito' => false, 'mensaje' => ''];
    
    // Verificar que el pedido existe y pertenece al usuario
    $consulta_verificar = "SELECT estado FROM pedidos WHERE id = ? AND usuario_id = ?";
    $stmt_verificar = mysqli_prepare($conexion, $consulta_verificar);
    mysqli_stmt_bind_param($stmt_verificar, "ii", $pedido_id, $usuario_id);
    mysqli_stmt_execute($stmt_verificar);
    $resultado_verificar = mysqli_stmt_get_result($stmt_verificar);
    
    if ($pedido = mysqli_fetch_assoc($resultado_verificar)) {
        // Verificar que el pedido esté pendiente
        if ($pedido['estado'] == 'pendiente') {
            // Actualizar el estado a cancelado
            $consulta_actualizar = "UPDATE pedidos SET estado = 'cancelado' WHERE id = ?";
            $stmt_actualizar = mysqli_prepare($conexion, $consulta_actualizar);
            mysqli_stmt_bind_param($stmt_actualizar, "i", $pedido_id);
            
            if (mysqli_stmt_execute($stmt_actualizar)) {
                $resultado['exito'] = true;
                $resultado['mensaje'] = "Pedido #$pedido_id cancelado correctamente";
            } else {
                $resultado['mensaje'] = "Error al cancelar el pedido";
            }
        } else {
            $resultado['mensaje'] = "No se puede cancelar un pedido que ya ha sido procesado";
        }
    } else {
        $resultado['mensaje'] = "Pedido no encontrado";
    }
    
    return $resultado;
}
?>