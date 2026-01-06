$(document).ready(function() {
    // Cargar usuarios al iniciar
    cargarUsuarios();
    
    // Event listeners
    $('#filtroEstado, #filtroRol').change(function() {
        cargarUsuarios();
    });
    
    $('#btnBuscar').click(function() {
        cargarUsuarios();
    });
    
    $('#busqueda').keypress(function(e) {
        if (e.which == 13) { // Enter key
            cargarUsuarios();
        }
    });
    
    $('#btnGuardarCambios').click(function() {
        confirmarActualizarUsuario();
    });
});

function cargarUsuarios() {
    const filtroEstado = $('#filtroEstado').val();
    const filtroRol = $('#filtroRol').val();
    const busqueda = $('#busqueda').val();
    
    // Mostrar loading
    $('#loading').show();
    $('#tUsuarios').addClass('d-none');
    
    $.ajax({
        url: 'functions/f_gestion_de_usuarios.php',
        method: 'POST',
        data: {
            accion: 'obtener_usuarios',
            filtroEstado: filtroEstado,
            filtroRol: filtroRol,
            busqueda: busqueda
        },
        dataType: 'json',
        success: function(response) {
            $('#loading').hide();
            
            if (response.success) {
                mostrarUsuarios(response.usuarios);
            } else {
                console.error('Error al cargar usuarios');
            }
        },
        error: function(xhr, status, error) {
            $('#loading').hide();
            console.error('Error AJAX:', error);
            console.error('Response:', xhr.responseText);
        }
    });
}

function mostrarUsuarios(usuarios) {
    const tbody = $('#tUsuarios tbody');
    tbody.empty();
    
    if (usuarios.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No se encontraron usuarios
                </td>
            </tr>
        `);
    } else {
        usuarios.forEach(function(usuario) {
            const estadoBadge = getEstadoBadge(usuario.estado);
            const rolBadge = getRolBadge(usuario.rol);
            const botonEstado = getBotonEstado(usuario.estado, usuario.id, usuario.nombre);
            const fila = `
                <tr>
                    <td>${usuario.id}</td>
                    <td>${usuario.nombre}</td>
                    <td>${usuario.email}</td>
                    <td>${estadoBadge}</td>
                    <td>${rolBadge}</td>
                    <td>${usuario.fecha_registro}</td>
                    <td>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-info" onclick="verDetalleUsuario(${usuario.id})" title="Ver detalles">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="editarUsuario(${usuario.id}, '${escapeHtml(usuario.nombre)}', '${escapeHtml(usuario.email)}', '${usuario.estado}', '${usuario.rol}')" title="Editar usuario">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-secondary" onclick="confirmarSuspender(${usuario.id}, '${escapeHtml(usuario.nombre)}')" title="Suspender usuario">
                                <i class="bi bi-pause-circle"></i>
                            </button>
                            ${botonEstado}
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(fila);
        });
    }
    
    $('#tUsuarios').removeClass('d-none');
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function getEstadoBadge(estado) {
    switch(estado) {
        case 'activo':
            return '<span class="badge bg-success">Activo</span>';
        case 'inactivo':
            return '<span class="badge bg-warning">Inactivo</span>';
        case 'suspendido':
            return '<span class="badge bg-danger">Suspendido</span>';
        default:
            return '<span class="badge bg-secondary">Desconocido</span>';
    }
}

function getRolBadge(rol) {
    switch(rol) {
        case 'admin':
            return '<span class="badge bg-primary">Administrador</span>';
        case 'cliente':
            return '<span class="badge bg-secondary">Cliente</span>';
        default:
            return '<span class="badge bg-light text-dark">Desconocido</span>';
    }
}

function getBotonEstado(estado, id, nombre) {
    if (estado === 'activo') {
        return `<button class="btn btn-sm btn-danger" onclick="confirmarToggleEstado(${id}, '${escapeHtml(nombre)}', 'desactivar')" title="Desactivar usuario">
                    <i class="bi bi-x-circle"></i>
                </button>`;
    } else {
        return `<button class="btn btn-sm btn-success" onclick="confirmarToggleEstado(${id}, '${escapeHtml(nombre)}', 'activar')" title="Activar usuario">
                    <i class="bi bi-check-circle"></i>
                </button>`;
    }
}

function editarUsuario(id, nombre, email, estado, rol) {
    $('#usuarioId').val(id);
    $('#usuarioNombre').text(nombre);
    $('#usuarioEmail').text(email);
    $('#nuevoEstado').val(estado);
    $('#nuevoRol').val(rol);
    
    $('#editarModal').modal('show');
}

// Nueva función para confirmar antes de actualizar
function confirmarActualizarUsuario() {
    const id = $('#usuarioId').val();
    const nuevoEstado = $('#nuevoEstado').val();
    const nuevoRol = $('#nuevoRol').val();
    const nombre = $('#usuarioNombre').text();
    
    const mensaje = `¿Estás seguro de que quieres actualizar los datos del usuario "${nombre}"?\n\nNuevo estado: ${nuevoEstado}\nNuevo rol: ${nuevoRol}`;
    
    if (confirm(mensaje)) {
        actualizarUsuario();
    }
}

function actualizarUsuario() {
    const id = $('#usuarioId').val();
    const nuevoEstado = $('#nuevoEstado').val();
    const nuevoRol = $('#nuevoRol').val();
    
    $.ajax({
        url: 'functions/f_gestion_de_usuarios.php',
        method: 'POST',
        data: {
            accion: 'actualizar_usuario',
            id: id,
            nuevoEstado: nuevoEstado,
            nuevoRol: nuevoRol
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#editarModal').modal('hide');
                cargarUsuarios(); // Recargar la tabla
                // Mostrar mensaje de éxito
                mostrarMensaje('success', 'Usuario actualizado correctamente');
            } else {
                console.error(response.mensaje || 'Error al actualizar usuario');
                mostrarMensaje('error', response.mensaje || 'Error al actualizar usuario');
            }
        },
        error: function() {
            console.error('Error de conexión al servidor');
            mostrarMensaje('error', 'Error de conexión al servidor');
        }
    });
}

// Función para mostrar mensajes bonitos
function mostrarMensaje(tipo, mensaje) {
    // Remover mensajes anteriores
    $('.alert-message').remove();
    
    const icon = tipo === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
    const bgClass = tipo === 'success' ? 'alert-success' : 'alert-danger';
    
    const alertHtml = `
        <div class="alert ${bgClass} alert-dismissible fade show alert-message position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999; min-width: 300px;" role="alert">
            <i class="bi ${icon} me-2"></i>
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    $('body').append(alertHtml);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        $('.alert-message').alert('close');
    }, 5000);
}

function verDetalleUsuario(id) {
    $.ajax({
        url: 'functions/f_gestion_de_usuarios.php',
        method: 'POST',
        data: {
            accion: 'obtener_detalle',
            id: id
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const usuario = response.usuario;
                $('#infoId').text(usuario.id);
                $('#infoNombre').text(usuario.nombre_completo);
                $('#infoEmail').text(usuario.email);
                $('#infoEstado').html(getEstadoBadge(usuario.estado));
                $('#infoRol').html(getRolBadge(usuario.rol));
                $('#infoFecha').text(usuario.fecha_registro);
                $('#infoTelefono').text(usuario.telefono || 'No disponible');
                
                $('#infoModal').modal('show');
            } else {
                console.error('Error al obtener detalles del usuario');
                mostrarMensaje('error', 'Error al obtener detalles del usuario');
            }
        },
        error: function() {
            console.error('Error de conexión al servidor');
            mostrarMensaje('error', 'Error de conexión al servidor');
        }
    });
}

function confirmarSuspender(id, nombre) {
    if (confirm(`¿Estás seguro de que quieres suspender al usuario "${nombre}"?`)) {
        suspenderUsuario(id);
    }
}

function suspenderUsuario(id) {
    $.ajax({
        url: 'functions/f_gestion_de_usuarios.php',
        method: 'POST',
        data: {
            accion: 'suspender_usuario',
            id: id
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                cargarUsuarios(); // Recargar la lista
                mostrarMensaje('success', 'Usuario suspendido correctamente');
            } else {
                console.error(response.mensaje || 'Error al suspender usuario');
                mostrarMensaje('error', response.mensaje || 'Error al suspender usuario');
            }
        },
        error: function() {
            console.error('Error de conexión al servidor');
            mostrarMensaje('error', 'Error de conexión al servidor');
        }
    });
}

function confirmarToggleEstado(id, nombre, accion) {
    const mensaje = accion === 'activar' 
        ? `¿Estás seguro de que quieres activar al usuario "${nombre}"?`
        : `¿Estás seguro de que quieres desactivar al usuario "${nombre}"?`;
    
    if (confirm(mensaje)) {
        toggleEstadoUsuario(id);
    }
}

function toggleEstadoUsuario(id) {
    $.ajax({
        url: 'functions/f_gestion_de_usuarios.php',
        method: 'POST',
        data: {
            accion: 'toggle_estado_usuario',
            id: id
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                cargarUsuarios(); // Recargar la tabla
                const mensaje = response.mensaje || 'Estado del usuario actualizado correctamente';
                mostrarMensaje('success', mensaje);
            } else {
                console.error(response.mensaje || 'Error al cambiar estado del usuario');
                mostrarMensaje('error', response.mensaje || 'Error al cambiar estado del usuario');
            }
        },
        error: function() {
            console.error('Error de conexión al servidor');
            mostrarMensaje('error', 'Error de conexión al servidor');
        }
    });
}