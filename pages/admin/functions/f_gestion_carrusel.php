<?php
require_once 'f_configuracion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        // Agregar imagen al carrusel
        if ($action === 'agregar' && isset($_FILES['imagen'])) {
            $titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
            $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
            
            $resultado = agregarImagenCarrusel($titulo, $descripcion, $_FILES['imagen']);
            if ($resultado['exito']) {
                header('Location: configuracion.php');
                exit();
            }
        }
        
        // Actualizar imagen del carrusel
        if ($action === 'actualizar') {
            $id = isset($_POST['id']) ? $_POST['id'] : '';
            $titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
            $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
            
            if (actualizarImagenCarrusel($id, $titulo, $descripcion)) {
                header('Location: configuracion.php');
                exit();
            }
        }
        
        // Eliminar imagen del carrusel
        if ($action === 'eliminar') {
            $id = isset($_POST['id']) ? $_POST['id'] : '';
            if (eliminarImagenCarrusel($id)) {
                header('Location: configuracion.php');
                exit();
            }
        }
        
        // Reordenar carrusel
        if ($action === 'reordenar') {
            $orden = isset($_POST['orden']) ? json_decode($_POST['orden'], true) : [];
            if (reordenarCarrusel($orden)) {
                header('Content-Type: application/json');
                echo json_encode(array('exito' => true));
                exit();
            }
        }
        
        // Activar/Desactivar
        if ($action === 'toggle') {
            $id = isset($_POST['id']) ? $_POST['id'] : '';
            // Leer explícitamente el valor enviado ('0' o '1') y convertir a entero
            $activa = isset($_POST['activa']) ? intval($_POST['activa']) : 0;
            $activa = $activa ? 1 : 0;
            if (activarDesactivarImagenCarrusel($id, $activa)) {
                header('Location: configuracion.php');
                exit();
            }
        }
    }
}

$imagenes = obtenerTodasImagenesCarrusel();
?>

<div class="form-section">
    <h5 class="mb-4"><i class="bi bi-images"></i> Gestión de Imágenes del Carrusel</h5>
    
    <!-- Formulario para agregar imagen -->
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-plus-circle"></i> Agregar Nueva Imagen
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="agregar">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="titulo" class="form-label">Título de la Imagen</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required placeholder="Ej: Oferta de Verano">
                    </div>
                    <div class="col-md-6">
                        <label for="imagen" class="form-label">Archivo de Imagen</label>
                        <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*" required>
                        <small class="text-muted">Formatos: JPG, PNG, GIF, WebP (máx. 10MB)</small>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="2" placeholder="Descripción de la imagen para el carrusel"></textarea>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-cloud-upload"></i> Subir Imagen
                </button>
            </form>
        </div>
    </div>

    <!-- Lista de imágenes -->
    <h6 class="mb-3 mt-4">Imágenes Actuales (<?php echo count($imagenes); ?>)</h6>
    
    <?php if (empty($imagenes)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No hay imágenes en el carrusel. ¡Agrega una para comenzar!
        </div>
    <?php else: ?>
        <div id="sortable-carrusel" class="row g-3 mb-4">
            <?php foreach ($imagenes as $imagen): ?>
                <div class="col-lg-6 col-xl-4 imagen-item" data-id="<?php echo $imagen['id']; ?>">
                    <div class="card h-100 <?php echo $imagen['activa'] ? '' : 'opacity-50'; ?>">
                        <div class="position-relative">
                            <img src="../../<?php echo htmlspecialchars($imagen['imagen_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($imagen['titulo']); ?>" style="height: 200px; object-fit: cover;">
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-<?php echo $imagen['activa'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $imagen['activa'] ? 'Activa' : 'Inactiva'; ?>
                                </span>
                                <span class="badge bg-info">Orden: <?php echo $imagen['orden']; ?></span>
                            </div>
                            <div class="position-absolute top-0 end-0 m-2">
                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $imagen['id']; ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($imagen['titulo']); ?></h6>
                            <p class="card-text small text-muted"><?php echo htmlspecialchars(substr($imagen['descripcion'], 0, 50)) . (strlen($imagen['descripcion']) > 50 ? '...' : ''); ?></p>
                        </div>
                        <div class="card-footer bg-white">
                            <form method="POST" class="d-inline" onsubmit="return confirm('¿Deseas eliminar esta imagen?');">
                                <input type="hidden" name="action" value="eliminar">
                                <input type="hidden" name="id" value="<?php echo $imagen['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>
                            </form>
                            
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?php echo $imagen['id']; ?>">
                                <input type="hidden" name="activa" value="<?php echo $imagen['activa'] ? '0' : '1'; ?>">
                                <button type="submit" class="btn btn-sm btn-<?php echo $imagen['activa'] ? 'outline-secondary' : 'outline-success'; ?>">
                                    <i class="bi bi-<?php echo $imagen['activa'] ? 'eye-slash' : 'eye'; ?>"></i> 
                                    <?php echo $imagen['activa'] ? 'Desactivar' : 'Activar'; ?>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Modal para editar imagen -->
                    <div class="modal fade" id="editModal<?php echo $imagen['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Editar Imagen</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="actualizar">
                                        <input type="hidden" name="id" value="<?php echo $imagen['id']; ?>">
                                        
                                        <div class="mb-3">
                                            <label for="titulo<?php echo $imagen['id']; ?>" class="form-label">Título</label>
                                            <input type="text" class="form-control" id="titulo<?php echo $imagen['id']; ?>" name="titulo" value="<?php echo htmlspecialchars($imagen['titulo']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="descripcion<?php echo $imagen['id']; ?>" class="form-label">Descripción</label>
                                            <textarea class="form-control" id="descripcion<?php echo $imagen['id']; ?>" name="descripcion" rows="3"><?php echo htmlspecialchars($imagen['descripcion']); ?></textarea>
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
                </div>
            <?php endforeach; ?>
        </div>

        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle"></i> <strong>Nota:</strong> Puedes arrastrar las imágenes para reordenarlas en el carrusel. Solo se mostrarán las imágenes marcadas como "Activas".
        </div>
    <?php endif; ?>
</div>

<script>
// Preview de logo (se usa en la pestaña General)
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.querySelector('.logo-preview');
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>