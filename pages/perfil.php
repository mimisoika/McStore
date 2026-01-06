<?php 
include '../php/database.php';
include 'functions/f_perfil.php';
include 'functions/f_detalles_pedido.php';
include 'functions/f_favoritos.php';


// Iniciar sesión y verificar autenticación
iniciarSesionSegura();

// Verificar si el usuario está logueado
if (!estaLogueado()) {
    header('Location: login.php');
    exit();
}

// Obtener datos del usuario
$usuario_id = $_SESSION['usuario_id'];
$datosUsuario = obtenerDatosCompletos($usuario_id);
$pedidos = obtenerPedidosUsuario($usuario_id);
$direcciones = obtenerDireccionesUsuario($usuario_id);
$favoritos = obtenerProductosFavoritos($usuario_id);

// Manejar actualización de datos
$resultadoActualizacion = manejarActualizacionDatos($usuario_id);
if ($resultadoActualizacion['exito']) {
    $mensaje = $resultadoActualizacion['mensaje'];
    $datosUsuario = $resultadoActualizacion['datos'];
} elseif (!empty($resultadoActualizacion['mensaje'])) {
    $error = $resultadoActualizacion['mensaje'];
}

if (isset($_POST['cerrar_sesion'])) {
    cerrarSesion();
    header('Location: login.php');
    exit();
}

// Guadar una nueva direccion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_direccion'])) {
    guardarDireccion(); 
}

// Editar la direccion que se marca como principal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevoPrincipal'])) {
    $usuario_id = $_POST['usuario_id'];
    $direccion_id = $_POST['direccion_id'];
    marcarDireccionComoPrincipal($usuario_id, $direccion_id);
  
}

// Manejar cancelación de pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_pedido'])) {
    $pedido_id = $_POST['pedido_id'];
    $resultado = cancelarPedido($pedido_id, $usuario_id);
    
    if ($resultado['exito']) {
        header('Location: perfil.php?mensaje=' . urlencode($resultado['mensaje']));
    } else {
        header('Location: perfil.php?error=' . urlencode($resultado['mensaje']));
    }
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mi Perfil - MC Store</title>
      <!-- Bootstrap CSS -->
    <link rel="preload" 
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
          as="style" 
          onload="this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    </noscript>

    <!-- Font Awesome (optimizado con display=swap) -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
          integrity=""
          referrerpolicy="no-referrer"
          media="all">
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>

    <?php if (isset($mensaje)): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="fas fa-user fs-1 text-white"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h2 class="mb-1">
                                    <?php echo htmlspecialchars($datosUsuario['nombre'] . ' ' . $datosUsuario['apellido_paterno']); ?>
                                </h2>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($datosUsuario['email']); ?>
                                </p>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-calendar me-2"></i>Miembro desde <?php echo date('M Y', strtotime($datosUsuario['fecha_creacion'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <!-- Sidebar de navegación -->
            <div class="col-lg-3 mb-4">
                <div class="card bg-primary text-white">
                    <div class="card-body p-4">
                        <h5 class="mb-4 text-center">
                            <i class="fas fa-user-cog me-2"></i>Mi Perfil
                        </h5>
                        <ul class="nav nav-pills flex-column" id="tablas_perfil">
                            <li class="nav-item mb-2">
                                <a class="nav-link active bg-white text-primary" data-bs-toggle="pill" href="#datos">
                                    <i class="fas fa-user me-2"></i>Datos Personales
                                </a>
                            </li>
                            <li class="nav-item mb-2">
                                <a class="nav-link text-white" data-bs-toggle="pill" href="#direcciones">
                                    <i class="fas fa-map-marker-alt me-2"></i>Direcciones
                                </a>
                            </li>
                            <li class="nav-item mb-2">
                                <a class="nav-link text-white" data-bs-toggle="pill" href="#pedidos">
                                    <i class="fas fa-shopping-bag me-2"></i>Mis Pedidos
                                </a>
                            </li>
                            <li class="nav-item mb-2">
                                <a class="nav-link text-white" data-bs-toggle="pill" href="#favoritos">
                                    <i class="fas fa-heart me-2"></i>Mis Favoritos
                                </a>
                            </li>
                        </ul>
                        <hr class="my-4">
                        <form method="POST" class="d-grid">
                            <button class="btn btn-outline-light" type="submit" name="cerrar_sesion">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="col-lg-9">
                <div class="tab-content">
                    <!-- Datos Personales -->
                    <div class="tab-pane fade show active" id="datos">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-user me-2"></i>Datos Personales
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <form method="POST" id="formDatos">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Nombre</label>
                                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($datosUsuario['nombre']); ?>" readonly id="inputNombre">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Apellido Paterno</label>
                                            <input type="text" name="apellido_paterno" class="form-control" value="<?php echo htmlspecialchars($datosUsuario['apellido_paterno']); ?>" readonly id="inputApellidoPaterno">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Apellido Materno</label>
                                            <input type="text" name="apellido_materno" class="form-control" value="<?php echo htmlspecialchars($datosUsuario['apellido_materno']); ?>" readonly id="inputApellidoMaterno">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Email</label>
                                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($datosUsuario['email']); ?>" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Teléfono</label>
                                            <input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($datosUsuario['telefono']); ?>" readonly id="inputTelefono">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Fecha de Registro</label>
                                            <input type="text" class="form-control" value="<?php echo date('d/m/Y', strtotime($datosUsuario['fecha_creacion'])); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <button type="button" class="btn btn-primary" id="btnEditar">
                                            <i class="fas fa-edit me-2"></i>Editar Información
                                        </button>
                                        <button type="submit" name="actualizar_datos" class="btn btn-success d-none" id="btnGuardar">
                                            <i class="fas fa-save me-2"></i>Guardar Cambios
                                        </button>
                                        <button type="button" class="btn btn-secondary d-none" id="btnCancelar">
                                            <i class="fas fa-times me-2"></i>Cancelar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Direcciones -->
                    <div class="tab-pane fade" id="direcciones">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-map-marker-alt me-2"></i>Mis Direcciones
                                </h5>                           
                                <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                                    <i class="fas fa-plus me-1"></i>Agregar
                                </button>
                            </div>
                            <div class="card-body p-4">
                                <?php if (empty($direcciones)): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-map-marker-alt fs-1 text-muted mb-3"></i>
                                        <h5 class="text-muted">No tienes direcciones registradas</h5>
                                        <p class="text-muted">Agrega tu primera dirección para realizar pedidos</p>
                                    </div>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($direcciones as $direccion): ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="card border">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <h6 class="card-title mb-0"><?php echo htmlspecialchars($direccion['alias']); ?></h6>
                                                            <form action="perfil.php" method="POST" class="d-inline">
                                                                <!--Estos datos proporcionan la informacion necesaria para poder hacer la consulta -->
                                                                <input type="hidden" name="usuario_id" value="<?php echo $_SESSION['usuario_id']; ?>">
                                                                <input type="hidden" name="direccion_id" value="<?php echo $direccion['id']; ?>">
                                                                <button type="submit" name="nuevoPrincipal" class="btn btn-light btn-sm">
                                                                    <i class="fas fa-check me-1"></i>Principal
                                                                </button>
                                                            </form>
                                                        </div>
                                                        <p class="card-text text-muted mb-1">
                                                            <i class="fas fa-map-marker-alt me-1"></i>
                                                            <?php echo htmlspecialchars($direccion['direccion']); ?>
                                                        </p>
                                                        <p class="card-text text-muted mb-0">
                                                            <i class="fas fa-city me-1"></i>
                                                            <?php echo htmlspecialchars($direccion['ciudad'] . ', ' . $direccion['estado']); ?>
                                                        </p>
                                                        <p class="card-text text-muted">
                                                            <i class="fas fa-mail-bulk me-1"></i>
                                                            <?php echo htmlspecialchars($direccion['codigo_postal']); ?>
                                                        </p>
                                                        <?php if ($direccion['es_principal']): ?>
                                                            <span class="badge bg-success">Principal</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Pedidos -->
                    <div class="tab-pane fade" id="pedidos">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-shopping-bag me-2"></i>Historial de Pedidos
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <?php if (empty($pedidos)): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-shopping-bag fs-1 text-muted mb-3"></i>
                                        <h5 class="text-muted">No tienes pedidos realizados</h5>
                                        <p class="text-muted">Explora nuestros productos y realiza tu primer pedido</p>
                                        <a href="../index.php" class="btn btn-primary">
                                            <i class="fas fa-shopping-cart me-2"></i>Ir a Comprar
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Pedido #</th>
                                                    <th>Fecha</th>
                                                    <th>Total</th>
                                                    <th>Estado</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pedidos as $pedido): ?>
                                                    <tr>
                                                        <td class="fw-bold">#<?php echo $pedido['id']; ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?></td>
                                                        <td class="fw-bold text-success">$<?php echo number_format($pedido['total'], 2); ?></td>
                                                        <td>
                                                            <?php echo generarBadgeEstado($pedido['estado']); ?>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-light btn-sm btn-ver-detalles" 
                                                                    data-pedido-id="<?php echo $pedido['id']; ?>"
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#modalDetallesPedido">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <?php if ($pedido['estado'] == 'pendiente'): ?>
                                                                <button class="btn btn-sm btn-outline-danger ms-1 btn-cancelar-pedido" 
                                                                        data-pedido-id="<?php echo $pedido['id']; ?>"
                                                                        data-bs-toggle="tooltip" 
                                                                        title="Cancelar Pedido">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Productos Favoritos -->
                    <div class="tab-pane fade" id="favoritos">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-heart me-2"></i>Mis Productos Favoritos
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <?php if (empty($favoritos)): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-heart fs-1 text-muted mb-3"></i>
                                        <h5 class="text-muted">No tienes productos favoritos</h5>
                                        <p class="text-muted">Agrega productos a tu lista de favoritos para verlos aquí</p>
                                        <a href="catalogo.php" class="btn btn-primary">
                                            <i class="fas fa-heart me-2"></i>Explorar Productos
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="row g-4">
                                        <?php foreach ($favoritos as $producto): 
                                            // Imagen segura
                                            $imagen = !empty($producto['imagen']) 
                                                ? '../img_productos/' . htmlspecialchars($producto['imagen']) 
                                                : '../img_productos/producto-default.jpg';?>
                                            
                                            
                                            <div class="col-lg-4 col-md-6">
                                                <div class="card h-100 shadow-sm">
                                                    <div class="position-relative overflow-hidden" style="height: 200px;">
                                                        <img src="<?php echo $imagen; ?>" 
                                                        class="card-img-top" 
                                                        style="height: 200px; object-fit: cover;"
                                                        alt="<?php echo htmlspecialchars($producto['nombre']); ?>">

                                                             
                                                        <!--Este boton debe de eliminar de favoritos si se presiona -->
                                                        <div class="position-absolute top-0 end-0 p-2">
                                                            <button class="btn btn-sm btn-danger btn-remove-fav" 
                                                                    data-id="<?php echo $producto['id']; ?>" 
                                                                    title="Quitar de favoritos">
                                                                <i class="fas fa-heart"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="card-body d-flex flex-column">
                                                        <h5 class="card-title"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                                                        <p class="card-text text-muted flex-grow-1"><?php echo htmlspecialchars(substr($producto['descripcion'], 0, 100) . '...'); ?></p>
                                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                                            <span class="h6 text-primary mb-0">$<?php echo number_format($producto['precio'], 2); ?></span>
                                                            <a href="producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-eye me-1"></i>Ver
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Modal para agregar dirección -->
<!-- Modal para agregar dirección -->
<div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="perfil.php" method="POST" id="formDireccion">
        <div class="modal-header">
          <h5 class="modal-title" id="modalAgregarLabel">Nueva dirección</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <input type="text" class="form-control" name="alias" placeholder="Alias (Ej: Casa, Trabajo)" required>
            </div>
            <div class="col-md-6">
              <input type="text" class="form-control" name="ciudad" placeholder="Ciudad" required>
            </div>
            <div class="col-12">
              <textarea class="form-control" name="direccion" placeholder="Dirección completa" rows="2" required></textarea>
            </div>
            <div class="col-md-6">
              <input type="text" class="form-control" name="codigo_postal" id="codigo_postal" 
                     placeholder="Código Postal (Ej: 23600)" required 
                     pattern="23\d{3}" maxlength="5"
                     title="El código postal debe empezar con 23 y tener 5 dígitos">
              <div class="form-text">Debe empezar con 23 y tener 5 dígitos</div>
            </div>
            <div class="col-md-6">
              <input type="text" class="form-control" name="estado" placeholder="Estado" required>
            </div>
            <div class="col-12">
              <div id="info_cp" class="alert alert-info d-none">
                <small><strong>Asentamiento:</strong> <span id="asentamiento_info"></span></small>
              </div>
            </div>
            <div class="col-12">
              <textarea class="form-control" name="instrucciones_entrega" placeholder="Instrucciones para entrega" rows="2"></textarea>
            </div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="es_principal" id="es_principal" value="1">
                <label class="form-check-label" for="es_principal">Establecer como dirección principal</label>
              </div>
            </div>
            <input type="hidden" name="usuario_id" value="<?php echo $_SESSION['usuario_id']; ?>">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary btn-Nueva-Direccion" name="guardar_direccion" id="btnGuardarDireccion">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal de Detalles del Pedido -->
<div class="modal fade" id="modalDetallesPedido" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetallesLabel">Detalles del Pedido #<span id="numeroPedido"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="contenidoDetalles">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando detalles del pedido...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="js/perfil.js" defear></script>
    <script src="js/favoritos.js" defear></script>
    <script src="js/validacion-cp.js" defear></script>
    <script src="js/detalles-pedido.js" defear></script>
</body>
</html>