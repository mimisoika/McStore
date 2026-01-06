$(document).ready(function() {
    cargarCategorias();
    
    // Event listeners
    $('#formAgregarCategoria').submit(function(e) {
        e.preventDefault();
        agregarCategoria();
    });
    
    $('#formEditarCategoria').submit(function(e) {
        e.preventDefault();
        actualizarCategoria();
    });
});

function cargarCategorias() {
    $.ajax({
        url: 'functions/f_gestion_categorias.php',
        method: 'POST',
        data: { accion: 'obtener_categorias' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                mostrarCategorias(response.categorias);
            } else {
                console.error('Error al cargar categorías');
            }
        },
        error: function() {
            console.error('Error de conexión');
        }
    });
}

function mostrarCategorias(categorias) {
    const tbody = $('#tablaCategorias tbody');
    tbody.empty();
    
    if (categorias.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="4" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No hay categorías disponibles
                </td>
            </tr>
        `);
    } else {
        categorias.forEach(function(categoria) {
            const fila = `
                <tr>
                    <td>${categoria.id}</td>
                    <td>${categoria.nombre}</td>
                    <td>${categoria.descripcion || 'Sin descripción'}</td>
                    <td>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-warning" onclick="editarCategoria(${categoria.id}, '${categoria.nombre}', '${categoria.descripcion || ''}')" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="confirmarEliminar(${categoria.id}, '${categoria.nombre}')" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(fila);
        });
    }
}

function agregarCategoria() {
    const nombre = $('#nombre').val();
    const descripcion = $('#descripcion').val();
    
    $.ajax({
        url: 'functions/f_gestion_categorias.php',
        method: 'POST',
        data: {
            accion: 'agregar_categoria',
            nombre: nombre,
            descripcion: descripcion
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#agregarCategoriaModal').modal('hide');
                $('#formAgregarCategoria')[0].reset();
                cargarCategorias();
                alert('Categoría agregada exitosamente');
            } else {
                alert('Error: ' + (response.mensaje || 'No se pudo agregar la categoría'));
            }
        },
        error: function() {
            alert('Error de conexión');
        }
    });
}

function editarCategoria(id, nombre, descripcion) {
    $('#categoriaId').val(id);
    $('#editNombre').val(nombre);
    $('#editDescripcion').val(descripcion);
    
    $('#editarCategoriaModal').modal('show');
}

function actualizarCategoria() {
    const id = $('#categoriaId').val();
    const nombre = $('#editNombre').val();
    const descripcion = $('#editDescripcion').val();
    
    $.ajax({
        url: 'functions/f_gestion_categorias.php',
        method: 'POST',
        data: {
            accion: 'actualizar_categoria',
            id: id,
            nombre: nombre,
            descripcion: descripcion
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#editarCategoriaModal').modal('hide');
                cargarCategorias();
                alert('Categoría actualizada exitosamente');
            } else {
                alert('Error: ' + (response.mensaje || 'No se pudo actualizar la categoría'));
            }
        },
        error: function() {
            alert('Error de conexión');
        }
    });
}

function confirmarEliminar(id, nombre) {
    if (confirm(`¿Estás seguro de que deseas eliminar la categoría "${nombre}"?`)) {
        eliminarCategoria(id);
    }
}

function eliminarCategoria(id) {
    $.ajax({
        url: 'functions/f_gestion_categorias.php',
        method: 'POST',
        data: {
            accion: 'eliminar_categoria',
            id: id
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                cargarCategorias();
                alert('Categoría eliminada exitosamente');
            } else {
                alert('Error: ' + (response.mensaje || 'No se pudo eliminar la categoría'));
            }
        },
        error: function() {
            alert('Error de conexión');
        }
    });
}