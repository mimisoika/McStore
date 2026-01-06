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
    <title>Gestión de Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin_style.css">
    <style>
        /* pequeño ajuste para que el contenido principal tenga padding cuando se usa el layout */
        .main-content .content { padding: 24px; }
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
                <a href="gestion_de_productos.php" class="menu-item active">
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
        
        <main class="main-content">
            <header class="top-bar">
                <div class="search-container">
                    
                </div>
                
            </header>
            
            <div class="content">
                <div class="content-header d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-0"><i class="bi bi-box-seam"></i> Gestión de Productos</h2>
                        <p class="text-muted mb-0">Sistema de gestión de productos</p>
                    </div>
                    <div>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#agregarProductoModal">
                            <i class="bi bi-plus-circle"></i> Agregar Producto
                        </button>
                    </div>
                </div>

                <div class="card mb-4">

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="filtroCategoria" class="form-label">Categoría</label>
                                <select id="filtroCategoria" class="form-select">
                                    <option value="todas">Todas las categorías</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtroEstado" class="form-label">Estado</label>
                                <select id="filtroEstado" class="form-select">
                                    <option value="todos">Todos los estados</option>
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                    <option value="agotado">Agotado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtroOrdenNombre" class="form-label">Ordenar por Nombre</label>
                                <select id="filtroOrdenNombre" class="form-select">
                                    <option value="default">Por defecto</option>
                                    <option value="asc">Nombre (A-Z)</option>
                                    <option value="desc">Nombre (Z-A)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtroOrdenStock" class="form-label">Ordenar por Stock</label>
                                <select id="filtroOrdenStock" class="form-select">
                                    <option value="default">Por defecto</option>
                                    <option value="asc">Stock (Menor a Mayor)</option>
                                    <option value="desc">Stock (Mayor a Menor)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                </div>
                            <div class="col-md-5">
                                <label for="filtroBusqueda" class="form-label">Buscar</label>
                                <div class="input-group">
                                    <input type="text" id="filtroBusqueda" class="form-control" placeholder="Nombre o descripción">
                                    <button class="btn btn-primary" id="btnBuscar">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" id="productos-container">
                    <div class="col-12 text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p>Cargando productos...</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="agregarProductoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Agregar Nuevo Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formAgregarProducto" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre del Producto</label>
                                <input type="text" class="form-control" id="nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label for="stock" class="form-label">Stock / Existencias</label>
                                <input type="number" class="form-control" id="stock" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label for="precio" class="form-label">Precio</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="precio" min="0" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="categoria" class="form-label">Categoría</label>
                                <select class="form-select" id="categoria" required>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" required>
                                    <option value="activo">Activo (se actualizará automáticamente por stock)</option>
                                    <option value="inactivo">Inactivo (suspendido manualmente)</option>
                                    <option value="agotado">Agotado (manual, se sobreescribe si stock > 0)</option>
                                </select>
                                <div class="form-text text-warning">
                                    <small><i class="bi bi-info-circle"></i> El estado "Activo" se actualiza automáticamente: 0=Agotado, 1-9=Poco Stock, 10+=Disponible</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="destacado" class="form-label">Destacado</label>
                                <select class="form-select" id="destacado" required>
                                    <option value="no">No</option>
                                    <option value="si">Sí</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="imagen" class="form-label">Imagen del Producto</label>
                                <input class="form-control" type="file" id="imagen" accept="image/*">
                                <div class="form-text">Formatos aceptados: JPG, PNG, WEBP. Máx. 2MB</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Vista previa</label>
                                <div class="border p-2 text-center">
                                    <img id="previewImagen" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" 
                                            alt="Vista previa de la imagen" 
                                            class="img-fluid" 
                                            style="max-height: 150px; display: none;">
                                    <p id="sinImagen" class="text-muted mb-0">No hay imagen seleccionada</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editarProductoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Editar Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditarProducto" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="editId">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="editNombre" class="form-label">Nombre del Producto</label>
                                <input type="text" class="form-control" id="editNombre" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editStock" class="form-label">Stock / Existencias</label>
                                <input type="number" class="form-control" id="editStock" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editPrecio" class="form-label">Precio</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="editPrecio" min="0" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="editCategoria" class="form-label">Categoría</label>
                                <select class="form-select" id="editCategoria" required>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="editEstado" class="form-label">Estado</label>
                                <select class="form-select" id="editEstado" required>
                                    <option value="activo">Activo (se actualizará automáticamente por stock)</option>
                                    <option value="inactivo">Inactivo (suspendido manualmente)</option>
                                    <option value="agotado">Agotado (manual, se sobreescribe si stock > 0)</option>
                                </select>
                                <div class="form-text text-warning">
                                    <small><i class="bi bi-info-circle"></i> El estado "Activo" se actualiza automáticamente según stock</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="editDestacado" class="form-label">Destacado</label>
                                <select class="form-select" id="editDestacado" required>
                                    <option value="no">No</option>
                                    <option value="si">Sí</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="editDescripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="editDescripcion" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="editImagen" class="form-label">Cambiar Imagen</label>
                                <input class="form-control" type="file" id="editImagen" accept="image/*">
                                <div class="form-text">Dejar en blanco para mantener la imagen actual</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Imagen Actual</label>
                                <div class="border p-2 text-center">
                                    <img id="editPreviewImagen" src="" 
                                            alt="Imagen actual del producto" 
                                            class="img-fluid" 
                                            style="max-height: 150px;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defear></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" defear></script>
    <script src="js/admin_index.js" defear></script>
    <script src="js/gestion_de_productos.js" defear></script>
</body>
</html>