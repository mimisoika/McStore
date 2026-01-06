<?php
require_once '../functions/f_login.php';

// Verificar que el usuario sea admin
if (!estaLogueado() || obtenerUsuario()['rol'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Panel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin_style.css">
    <style>
        .main-content .content { padding: 24px; }
    </style>
</head>

<body>
    <div class="admin-container">
        <!-- Sidebar -->
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
                <a href="gestion_de_usuarios.php" class="menu-item active">
                    <i class="bi bi-people-fill"></i>
                    <span>Usuarios</span>
                </a>
                <a href="gestion_catalogo.php" class="menu-item">
                    <i class="bi bi-tag"></i>
                    <span>Categorias</span>
                </a>
                <a href="configuracion.php" class="menu-item">
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
                
            </header>

            <!-- Content -->
            <div class="content">
                <div class="content-header d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-0"><i class="bi bi-people-fill"></i> Gestión de Usuarios</h2>
                        <p class="text-muted mb-0">Sistema de gestión de usuarios</p>
                    </div>
                </div>

                <div class="container-fluid px-0">
                    <!-- Filtros -->
                    <div class="bg-white p-3 rounded mb-3 shadow-sm">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="filtroEstado" class="form-label fw-bold">Filtrar por estado:</label>
                                <select id="filtroEstado" class="form-select">
                                    <option value="todos">Todos los usuarios</option>
                                    <option value="activo">Activos</option>
                                    <option value="inactivo">Inactivos</option>
                                    <option value="suspendido">Suspendidos</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtroRol" class="form-label fw-bold">Filtrar por rol:</label>
                                <select id="filtroRol" class="form-select">
                                    <option value="todos">Todos los roles</option>
                                    <option value="admin">Administrador</option>
                                    <option value="cliente">Cliente</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="busqueda" class="form-label fw-bold">Buscar por nombre o email:</label>
                                <div class="input-group">
                                    <input type="text" id="busqueda" class="form-control" placeholder="Buscar usuario...">
                                    <button id="btnBuscar" class="btn btn-primary">
                                        <i class="bi bi-search"></i> Buscar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resultados -->
                    <div id="resultado" class="bg-white shadow rounded p-3">
                        <div class="table-responsive">
                            <table id="tUsuarios" class="table table-bordered table-hover d-none">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre Completo</th>
                                        <th>Email</th>
                                        <th>Estado</th>
                                        <th>Rol</th>
                                        <th>Fecha de Registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modales -->
                <!-- Modal para editar usuario -->
                <div class="modal fade" id="editarModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Editar Usuario</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="usuarioId">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nombre:</label>
                                    <p id="usuarioNombre" class="bg-light p-2 rounded"></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email:</label>
                                    <p id="usuarioEmail" class="bg-light p-2 rounded"></p>
                                </div>
                                <div class="mb-3">
                                    <label for="nuevoEstado" class="form-label fw-bold">Estado:</label>
                                    <select id="nuevoEstado" class="form-select">
                                        <option value="activo">Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                        <option value="suspendido">Suspendido</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="nuevoRol" class="form-label fw-bold">Rol:</label>
                                    <select id="nuevoRol" class="form-select">
                                        <option value="cliente">Cliente</option>
                                        <option value="admin">Administrador</option>
                                    </select>
                                    <div class="form-text">
                                        <small>
                                            <span class="fw-bold">Cliente:</span> Usuario normal del sistema<br>
                                            <span class="fw-bold">Administrador:</span> Acceso completo al panel de administración
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                                <button type="button" id="btnGuardarCambios" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal para ver información del usuario -->
                <div class="modal fade" id="infoModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title">Información del Usuario</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold">Datos Personales</h6>
                                        <p><span class="fw-bold">ID:</span> <span id="infoId"></span></p>
                                        <p><span class="fw-bold">Nombre:</span> <span id="infoNombre"></span></p>
                                        <p><span class="fw-bold">Email:</span> <span id="infoEmail"></span></p>
                                        <p><span class="fw-bold">Estado:</span> <span id="infoEstado"></span></p>
                                        <p><span class="fw-bold">Rol:</span> <span id="infoRol"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-bold">Información del Sistema</h6>
                                        <p><span class="fw-bold">Fecha de Registro:</span> <span id="infoFecha"></span></p>
                                        <p><span class="fw-bold">Teléfono:</span> <span id="infoTelefono">No disponible</span></p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle"></i> Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/admin_index.js"></script>
    <script src="js/administrar_usuarios.js"></script>
</body>

</html>