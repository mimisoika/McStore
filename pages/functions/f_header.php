<?php 
require_once 'f_login.php';
require_once __DIR__ . '/../../php/database.php';
require_once 'f_helpers.php';

function generarMenuUsuario() {
    $logueado = estaLogueado();
    $datosUsuario = obtenerUsuario();
    
    // Determinar si estamos en la raíz o en la carpeta pages
    $currentDir = dirname($_SERVER['SCRIPT_NAME']);
    $isInPages = strpos($currentDir, 'pages') !== false || basename($currentDir) === 'pages';
    $prefix = $isInPages ? '' : 'pages/';
    
    if ($logueado && $datosUsuario !== null) {
        $nombre = $datosUsuario['nombres'] ?? 'Usuario';
        $rol = $datosUsuario['rol'] ?? 'cliente';
        
        $menu = '<ul class="dropdown-menu dropdown-menu-end">' .
               '<li><h6 class="dropdown-header"><i class="bi bi-person-badge me-2"></i>Hola, ' . htmlspecialchars($nombre) . '</h6></li>' .
               '<li><hr class="dropdown-divider"></li>' .
               '<li><a class="dropdown-item" href="' . $prefix . 'perfil.php"><i class="bi bi-person-gear me-2"></i>Mi Perfil</a></li>' .
               '<li><a class="dropdown-item" href="' . $prefix . 'perfil.php#pedidos"><i class="bi bi-bag me-2"></i>Mis Pedidos</a></li>';
        
        if ($rol === 'admin') {
            $menu .= '<li><hr class="dropdown-divider"></li>' .
                    '<li><a class="dropdown-item text-primary" href="' . $prefix . 'admin/admin_index.php"><i class="bi bi-gear-fill me-2"></i>Panel de Administrador</a></li>';
        }
        
        $menu .= '<li><hr class="dropdown-divider"></li>' .
                '<li><a class="dropdown-item text-danger" href="' . ($isInPages ? '../php/authentification.php?accion=logout' : 'php/authentification.php?accion=logout') . '"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>' .
                '</ul>';
        
        return $menu;
    } else {
        return '<ul class="dropdown-menu dropdown-menu-end">' .
               '<li><h6 class="dropdown-header"><i class="bi bi-person-badge me-2"></i>Mi Cuenta</h6></li>' .
               '<li><a class="dropdown-item" href="' . $prefix . 'login.php"><i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión</a></li>' .
               '<li><a class="dropdown-item" href="' . $prefix . 'login.php#registro"><i class="bi bi-person-plus me-2"></i>Registrarse</a></li>' .
               '</ul>';
    }
}
?>