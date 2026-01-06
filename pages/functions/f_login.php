<?php

require_once __DIR__ . '/../../php/database.php';

// Función auxiliar para iniciar sesión de forma segura
function iniciarSesionSegura() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function registrarUsuario($datos) {
    global $conexion;
    
    $nombres = $datos['nombres'];
    $apellido_paterno = $datos['apellido_paterno'];
    $apellido_materno = $datos['apellido_materno'];
    $email = $datos['email'];
    $telefono = $datos['telefono'];
    $contraseña = $datos['contraseña'];

    $consulta = "SELECT email FROM usuarios WHERE email = '$email'";
    $resultado = mysqli_query($conexion, $consulta);

    if (mysqli_num_rows($resultado) > 0) {
        return "Este email ya existe";
    }

    $contraseña_segura = password_hash($contraseña, PASSWORD_DEFAULT);

    $insertar = "INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, email, telefono, contraseña, rol, usuario_estado) 
                 VALUES ('$nombres', '$apellido_paterno', '$apellido_materno', '$email', '$telefono', '$contraseña_segura', 'cliente', 'Activo')";

    if (mysqli_query($conexion, $insertar)) {
        return "success";
    } else {
        return "No se pudo registrar";
    }
}

function iniciarSesion($email, $contraseña) {
    global $conexion;

    $consulta = "SELECT * FROM usuarios WHERE email = '$email'";
    $resultado = mysqli_query($conexion, $consulta);

    if (mysqli_num_rows($resultado) == 1) {
        $usuario = mysqli_fetch_assoc($resultado);

        if (password_verify($contraseña, $usuario['contraseña'])) {
            iniciarSesionSegura();
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['nombres'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol']; // ← AGREGAR ESTA LÍNEA
            return "success";
        } else {
            return "Usuario o contraseña incorrectos";
        }
    } else {
        return "Usuario no encontrado";
    }
}

function cerrarSesion() {
    iniciarSesionSegura();
    session_destroy();
    return "success";
}

function estaLogueado() {
    iniciarSesionSegura();
    if (isset($_SESSION['usuario_id'])) {
        return true;
    } else {
        return false;
    }
}

function obtenerUsuario() {
    iniciarSesionSegura();
    
    if (isset($_SESSION['usuario_id']) && isset($_SESSION['email']) && isset($_SESSION['nombres'])) {
        $datos = array(
            'id' => $_SESSION['usuario_id'],
            'email' => $_SESSION['email'],
            'nombres' => $_SESSION['nombres'],
            'rol' => $_SESSION['rol'] ?? 'cliente' // ← AGREGAR ESTA LÍNEA
        );
        return $datos;
    } else {
        // Si no hay datos de sesión, retornar null
        return null;
    }
}
function emailValido($email) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    } else {
        return false;
    }
}

function contraseñaValida($contraseña) {
    if (strlen($contraseña) >= 6) {
        return true;
    } else {
        return false;
    }
} ?>