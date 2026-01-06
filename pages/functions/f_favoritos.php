<?php
require_once __DIR__ . '/../../php/database.php';
require_once 'f_login.php';

function obtenerProductosFavoritos($usuario_id) {
    global $conexion;
    
    $consulta = "SELECT p.* FROM productos p 
                 INNER JOIN productos_favoritos pf ON p.id = pf.producto_id 
                 WHERE pf.usuario_id = ? 
                 ORDER BY pf.fecha_agregado DESC";
    
    $stmt = mysqli_prepare($conexion, $consulta);
    mysqli_stmt_bind_param($stmt, "i", $usuario_id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    $favoritos = [];
    while ($producto = mysqli_fetch_assoc($resultado)) {
        $favoritos[] = $producto;
    }
    return $favoritos;
}
function obtenerIdsFavoritos($usuario_id) {
    global $conexion;

    $sql = "SELECT producto_id FROM productos_favoritos WHERE usuario_id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $usuario_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $ids = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ids[] = $row['producto_id'];
    }
    return $ids;
}


function agregarFavorito($usuario_id, $producto_id) {
    global $conexion;

    $sql = "INSERT IGNORE INTO productos_favoritos (usuario_id, producto_id)
            VALUES (?, ?)";
    
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $producto_id);
    return mysqli_stmt_execute($stmt);
}

function quitarFavorito($usuario_id, $producto_id) {
    global $conexion;

    $sql = "DELETE FROM productos_favoritos 
            WHERE usuario_id = ? AND producto_id = ?";
    
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $producto_id);
    return mysqli_stmt_execute($stmt);
}

?>