<?php
require_once '../pages/functions/f_login.php';

// Manejar logout por GET
if (isset($_GET['accion']) && $_GET['accion'] == 'logout') {
    $resultado = cerrarSesion();
    
    if ($resultado == 'success') {
        echo "<script>
                alert('Sesión cerrada correctamente');
                window.location.href='../index.php';
              </script>";
    } else {
        echo "<script>
                alert('Error al cerrar sesión');
                window.history.back();
              </script>";
    }
    exit();
}

if (isset($_POST['action'])) {
    
    if ($_POST['action'] == 'register') {
        $resultado = registrarUsuario($_POST);
        
        if ($resultado == 'success') {
            echo "<script>
                    alert('¡Te registraste correctamente!');
                    window.location.href='../pages/login.php';
                  </script>";
        } else {
            echo "<script>
                    alert('$resultado');
                    window.history.back();
                  </script>";
        }
    }
    
    if ($_POST['action'] == 'login') {
        $email = $_POST['email'];
        $contraseña = $_POST['contraseña'];
        
        $resultado = iniciarSesion($email, $contraseña);
        
        if ($resultado == 'success') {
            echo "<script>
                    alert('¡Bienvenido!');
                    window.location.href='../index.php';
                  </script>";
        } else {
            echo "<script>
                    alert('$resultado');
                    window.history.back();
                  </script>";
        }
    }
}
?>