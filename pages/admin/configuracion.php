<?php
require_once '../functions/f_login.php';
require_once 'functions/f_configuracion.php';


// Verificar que el usuario sea admin
if (!estaLogueado() || obtenerUsuario()['rol'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

$config = obtenerConfiguracion();
$mensaje = '';
$tipo_mensaje = '';

// Procesar actualizaciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['actualizar_config'])) {
        // Actualizar configuración general
        $datos = $_POST;
        
        // Manejar upload de logo
        if (!empty($_FILES['logo']['name'])) {
            $resultado_logo = subirLogo($_FILES['logo']);
            if ($resultado_logo['exito']) {
                $datos['logo_url'] = $resultado_logo['ruta'];
            } else {
                $mensaje = $resultado_logo['mensaje'];
                $tipo_mensaje = 'danger';
            }
        }
        
        if (!$mensaje && actualizarConfiguracion($datos)) {
            $mensaje = '✓ Configuración actualizada exitosamente';
            $tipo_mensaje = 'success';
            $config = obtenerConfiguracion();
        } elseif (!$mensaje) {
            $mensaje = 'Error al actualizar la configuración';
            $tipo_mensaje = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Sitio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin_style.css">
    <style>
        .main-content .content { padding: 24px; }
        .tab-content { margin-top: 20px; }
        .form-section { 
            background: #f8f9fa; 
            padding: 20px; 
            border-radius: 8px; 
            margin-bottom: 20px;
        }
        .color-preview {
            width: 100%;
            height: 80px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            margin-top: 10px;
        }
        .logo-preview {
            max-width: 150px;
            height: auto;
            margin-top: 10px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
       
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="bi bi-x"></i>
                </div>
                <h3>MC Store</h3>
            </div>
            
            <nav class="sidebar-menu">
                <a href="admin_index.php" class="menu-item">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="gestion_de_productos.php" class="menu-item">
                    <i class="bi bi-box-seam"></i>
                    <span>Productos</span>
                </a>
                <a href="gestion_pedidos.php" class="menu-item">
                    <i class="bi bi-clipboard-check"></i>
                    <span>Pedidos</span>
                </a>
                <a href="gestion_de_usuarios.php" class="menu-item">
                    <i class="bi bi-people-fill"></i>
                    <span>Usuarios</span>
                </a>
                <a href="gestion_catalogo.php" class="menu-item">
                    <i class="bi bi-tag"></i>
                    <span>Categorias</span>
                </a>
                <a href="configuracion.php" class="menu-item active">
                    <i class="bi bi-gear"></i>
                    <span>Configuracion</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <a href="../../index.php" class="menu-item">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Volver a Pagina Principal</span>
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <header class="top-bar">
                <div class="search-container">

                </div>
            </header>
            
            <div class="content">
                <?php if ($mensaje): ?>
                    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                        <?php echo $mensaje; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Nav Tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                            <i class="bi bi-gear"></i> General
                        </button>
                    </li>
                    <!-- Colores tab removed -->
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contacto-tab" data-bs-toggle="tab" data-bs-target="#contacto" type="button" role="tab">
                            <i class="bi bi-telephone"></i> Contacto
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="carrusel-tab" data-bs-toggle="tab" data-bs-target="#carrusel" type="button" role="tab">
                            <i class="bi bi-images"></i> Carrusel
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- TAB 1: GENERAL -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-section">
                                <h5 class="mb-4"><i class="bi bi-info-circle"></i> Información General</h5>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="nombre_sitio" class="form-label fw-bold">Nombre del Sitio</label>
                                        <input type="text" class="form-control form-control-lg" id="nombre_sitio" name="nombre_sitio" value="<?php echo htmlspecialchars($config['nombre_sitio']); ?>" required>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-12">
                                        <label for="texto_nosotros" class="form-label fw-bold">Texto Sección "Nosotros"</label>
                                        <textarea class="form-control" id="texto_nosotros" name="texto_nosotros" rows="6"><?php echo htmlspecialchars($config['texto_nosotros']); ?></textarea>
                                    </div>
                                </div>

                                <button type="submit" name="actualizar_config" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Colores panel rem¿oved -->

                    <!-- TAB 3: CONTACTO -->
                    <div class="tab-pane fade" id="contacto" role="tabpanel">
                        <form method="POST">
                            <div class="form-section">
                                <h5 class="mb-4"><i class="bi bi-telephone"></i> Información de Contacto</h5>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="direccion" class="form-label fw-bold">Dirección</label>
                                        <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo htmlspecialchars($config['direccion']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="telefono" class="form-label fw-bold">Teléfono</label>
                                        <input type="tel" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($config['telefono']); ?>">
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="email" class="form-label fw-bold">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($config['email']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="whatsapp" class="form-label fw-bold">WhatsApp</label>
                                        <input type="tel" class="form-control" id="whatsapp" name="whatsapp" value="<?php echo htmlspecialchars($config['whatsapp']); ?>">
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="facebook" class="form-label fw-bold">Facebook</label>
                                        <input type="url" class="form-control" id="facebook" name="facebook" value="<?php echo htmlspecialchars($config['facebook']); ?>" placeholder="https://facebook.com/...">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="instagram" class="form-label fw-bold">Instagram</label>
                                        <input type="url" class="form-control" id="instagram" name="instagram" value="<?php echo htmlspecialchars($config['instagram']); ?>" placeholder="https://instagram.com/...">
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-12">
                                        <label for="horarios" class="form-label fw-bold">Horarios de Atención</label>
                                        <textarea class="form-control" id="horarios" name="horarios" rows="3" placeholder="Ej: Lun - Vie: 9:00 AM - 6:00 PM&#10;Sáb: 9:00 AM - 4:00 PM"><?php echo htmlspecialchars($config['horarios']); ?></textarea>
                                        <small class="text-muted">Separar líneas con Enter</small>
                                    </div>
                                </div>

                                <button type="submit" name="actualizar_config" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle"></i> Guardar Contacto
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 4: CARRUSEL -->
                    <div class="tab-pane fade" id="carrusel" role="tabpanel">
                        <?php include 'functions/f_gestion_carrusel.php'; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defear></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" defear></script>

    <script src="js/admin_index.js" defear></script>
    <script src="js/gestion_catalogo.js" defear></script>
</body>
</html>