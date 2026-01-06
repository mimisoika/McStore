<?php
require_once '../../../php/database.php';

// Verificar conexión
if (!$conexion) {
    echo json_encode(['success' => false, 'mensaje' => 'Error de conexión']);
    exit();
}

// Función para obtener todas las categorías
function obtenerCategorias() {
    global $conexion;
    $sql = "SELECT * FROM categorias ORDER BY nombre ASC";
    $resultado = mysqli_query($conexion, $sql);
    $categorias = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $categorias[] = $fila;
    }
    return $categorias;
}

// Función para agregar categoría
function agregarCategoria($nombre, $descripcion) {
    global $conexion;
    
    // Validar que el nombre no esté vacío
    if (empty($nombre)) {
        return false;
    }
    
    // Escapar datos
    $nombre = mysqli_real_escape_string($conexion, $nombre);
    $descripcion = mysqli_real_escape_string($conexion, $descripcion);
    
    $sql = "INSERT INTO categorias (nombre, descripcion) VALUES ('$nombre', '$descripcion')";
    return mysqli_query($conexion, $sql);
}

// Función para actualizar categoría
function actualizarCategoria($id, $nombre, $descripcion) {
    global $conexion;
    
    // Validar que el nombre no esté vacío
    if (empty($nombre)) {
        return false;
    }
    
    // Escapar datos
    $id = intval($id);
    $nombre = mysqli_real_escape_string($conexion, $nombre);
    $descripcion = mysqli_real_escape_string($conexion, $descripcion);
    
    $sql = "UPDATE categorias SET nombre='$nombre', descripcion='$descripcion' WHERE id=$id";
    return mysqli_query($conexion, $sql);
}

// Función para eliminar categoría
function eliminarCategoria($id) {
    global $conexion;
    
    $id = intval($id);
    $sql = "DELETE FROM categorias WHERE id=$id";
    return mysqli_query($conexion, $sql);
}

// Procesar peticiones AJAX
if ($_POST) {
    $accion = $_POST['accion'];
    
    if ($accion == 'obtener_categorias') {
        $categorias = obtenerCategorias();
        echo json_encode(['success' => true, 'categorias' => $categorias]);
    }
    
    elseif ($accion == 'agregar_categoria') {
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        
        $resultado = agregarCategoria($nombre, $descripcion);
        
        if ($resultado) {
            echo json_encode(['success' => true, 'mensaje' => 'Categoría agregada correctamente']);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Error al agregar categoría']);
        }
    }
    
    elseif ($accion == 'actualizar_categoria') {
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        
        $resultado = actualizarCategoria($id, $nombre, $descripcion);
        
        if ($resultado) {
            echo json_encode(['success' => true, 'mensaje' => 'Categoría actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar categoría']);
        }
    }
    
    elseif ($accion == 'eliminar_categoria') {
        $id = $_POST['id'];
        
        $resultado = eliminarCategoria($id);
        
        if ($resultado) {
            echo json_encode(['success' => true, 'mensaje' => 'Categoría eliminada correctamente']);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Error al eliminar categoría']);
        }
    }
}
?>