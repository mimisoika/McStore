<?php
require_once '../../../php/database.php';

if (!$conexion) {
    echo json_encode(['success' => false, 'mensaje' => 'Error de conexión']);
    exit();
}

function obtenerUsuarios($filtroEstado = 'todos', $filtroRol = 'todos', $busqueda = '') {
    global $conexion;
    
    $sql = "SELECT id, nombre, apellido_paterno, apellido_materno, email, usuario_estado as estado, rol, fecha_creacion as fecha_registro FROM usuarios WHERE 1=1";
    $params = [];
    $types = "";
    
    if ($filtroEstado !== 'todos') {
        $sql .= " AND usuario_estado = ?";
        $params[] = ucfirst($filtroEstado);
        $types .= "s";
    }
    
    if ($filtroRol !== 'todos') {
        $sql .= " AND rol = ?";
        $params[] = $filtroRol;
        $types .= "s";
    }
    
    if (!empty($busqueda)) {
        $sql .= " AND (CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) LIKE ? OR email LIKE ?)";
        $searchTerm = "%" . $busqueda . "%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }
    
    $sql .= " ORDER BY fecha_creacion DESC";
    $stmt = mysqli_prepare($conexion, $sql);
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    $usuarios = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $fila['nombre'] = $fila['nombre'] . ' ' . $fila['apellido_paterno'] . ' ' . $fila['apellido_materno'];
        $fila['estado'] = strtolower($fila['estado']);
        $fila['fecha_registro'] = date('Y-m-d', strtotime($fila['fecha_registro']));
        unset($fila['apellido_paterno'], $fila['apellido_materno']);
        $usuarios[] = $fila;
    }
    
    mysqli_stmt_close($stmt);
    return $usuarios;
}

function actualizarUsuario($id, $nuevoEstado, $nuevoRol) {
    global $conexion;
    $sql = "UPDATE usuarios SET usuario_estado = ?, rol = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", ucfirst($nuevoEstado), $nuevoRol, $id);
    $resultado = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $resultado;
}

function toggleEstadoUsuario($id) {
    global $conexion;
    
    // Verificar si es admin
    $sqlCheck = "SELECT rol, usuario_estado FROM usuarios WHERE id = ?";
    $stmtCheck = mysqli_prepare($conexion, $sqlCheck);
    mysqli_stmt_bind_param($stmtCheck, "i", $id);
    mysqli_stmt_execute($stmtCheck);
    $usuario = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCheck));
    mysqli_stmt_close($stmtCheck);
    
    if ($usuario && $usuario['rol'] === 'admin') return false;
    
    // Cambiar estado (Activo/Inactivo)
    $nuevoEstado = ($usuario['usuario_estado'] === 'Activo') ? 'Inactivo' : 'Activo';
    
    $sql = "UPDATE usuarios SET usuario_estado = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "si", $nuevoEstado, $id);
    $resultado = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $resultado;
}

function suspenderUsuario($id) {
    global $conexion;
    $sqlCheck = "SELECT rol FROM usuarios WHERE id = ?";
    $stmtCheck = mysqli_prepare($conexion, $sqlCheck);
    mysqli_stmt_bind_param($stmtCheck, "i", $id);
    mysqli_stmt_execute($stmtCheck);
    $usuario = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCheck));
    mysqli_stmt_close($stmtCheck);
    
    if ($usuario && $usuario['rol'] === 'admin') return false;
    
    $sql = "UPDATE usuarios SET usuario_estado = 'Suspendido' WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    $resultado = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $resultado;
}

function obtenerDetalleUsuario($id) {
    global $conexion;
    $sql = "SELECT id, nombre, apellido_paterno, apellido_materno, email, usuario_estado as estado, telefono, rol, fecha_creacion as fecha_registro FROM usuarios WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $usuario = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    if ($usuario) {
        $usuario['nombre_completo'] = $usuario['nombre'] . ' ' . $usuario['apellido_paterno'] . ' ' . $usuario['apellido_materno'];
        $usuario['estado'] = strtolower($usuario['estado']);
        $usuario['fecha_registro'] = date('Y-m-d H:i:s', strtotime($usuario['fecha_registro']));
    }
    
    return $usuario;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $accion = $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'obtener_usuarios':
            $usuarios = obtenerUsuarios($_POST['filtroEstado'] ?? 'todos', $_POST['filtroRol'] ?? 'todos', $_POST['busqueda'] ?? '');
            echo json_encode(['success' => true, 'usuarios' => $usuarios]);
            break;
            
        case 'actualizar_usuario':
            $resultado = actualizarUsuario($_POST['id'] ?? 0, $_POST['nuevoEstado'] ?? '', $_POST['nuevoRol'] ?? '');
            echo json_encode(['success' => $resultado, 'mensaje' => $resultado ? 'Usuario actualizado' : 'Error al actualizar']);
            break;
            
        case 'toggle_estado_usuario':
            $resultado = toggleEstadoUsuario($_POST['id'] ?? 0);
            echo json_encode(['success' => $resultado, 'mensaje' => $resultado ? 'Estado del usuario actualizado' : 'Error al cambiar estado']);
            break;
            
        case 'suspender_usuario':
            $resultado = suspenderUsuario($_POST['id'] ?? 0);
            echo json_encode(['success' => $resultado, 'mensaje' => $resultado ? 'Usuario suspendido' : 'Error al suspender']);
            break;
            
        case 'obtener_detalle':
            $usuario = obtenerDetalleUsuario($_POST['id'] ?? 0);
            echo json_encode(['success' => !!$usuario, 'usuario' => $usuario, 'mensaje' => $usuario ? '' : 'Usuario no encontrado']);
            break;
            
        default:
            echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
    }
    exit;
}
?>