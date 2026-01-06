// administrar_pedidos.js
$(document).ready(function() {
    // Cargar pedidos al iniciar
    cargarPedidos();
    
    // Event listeners para los filtros
    $('#filtroUsuario, #filtroEstatus, #filtroFecha').on('change keyup', function() {
        cargarPedidos();
    });
});

function cargarPedidos() {
    const filtroUsuario = $('#filtroUsuario').val();
    const filtroEstatus = $('#filtroEstatus').val();
    const filtroFecha = $('#filtroFecha').val();
    
    console.log('Cargando pedidos con filtros:', {filtroUsuario, filtroEstatus, filtroFecha});
    
    $.ajax({
        url: 'functions/f_gestion_pedidos.php',
        method: 'POST',
        data: {
            accion: 'obtener_pedidos',
            filtroUsuario: filtroUsuario,
            filtroEstatus: filtroEstatus,
            filtroFecha: filtroFecha
        },
        dataType: 'json',
        success: function(response) {
            console.log('Respuesta del servidor:', response);
            if (response.success) {
                mostrarPedidos(response.pedidos);
            } else {
                console.error('Error al cargar pedidos:', response.mensaje);
                mostrarPedidos([]);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            console.error('Response text:', xhr.responseText);
            mostrarPedidos([]);
        }
    });
}

function mostrarPedidos(pedidos) {
    const tbody = $('#tablaPedidos tbody');
    tbody.empty();
    
    if (pedidos.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No se encontraron pedidos
                </td>
            </tr>
        `);
    } else {
        pedidos.forEach(function(pedido) {
            const estatusClass = getEstatusClass(pedido.estatus);
            const fila = `
                <tr>
                    <td>${pedido.fecha_formateada}</td>
                    <td>${pedido.usuario}</td>
                    <td>${formatMetodoPago(pedido.metodo_pago)}</td>
                    <td>$${parseFloat(pedido.total).toFixed(2)}</td>
                    <td>
                        <select class="form-select form-select-sm estatus-pedido ${estatusClass}" 
                                data-pedido-id="${pedido.id}" 
                                style="width: auto; min-width: 130px;">
                            <option value="Pendiente" ${pedido.estatus === 'Pendiente' ? 'selected' : ''}>Pendiente</option>
                            <option value="Confirmado" ${pedido.estatus === 'Confirmado' ? 'selected' : ''}>Confirmado</option>
                            <option value="Preparando" ${pedido.estatus === 'Preparando' ? 'selected' : ''}>Preparando</option>
                            <option value="En camino" ${pedido.estatus === 'En camino' ? 'selected' : ''}>En camino</option>
                            <option value="Entregado" ${pedido.estatus === 'Entregado' ? 'selected' : ''}>Entregado</option>
                        </select>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="verDetallePedido(${pedido.id})" title="Ver detalles">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(fila);
        });
    }
}

function formatMetodoPago(metodo) {
    const metodos = {
        'efectivo': 'Efectivo',
        'tarjeta': 'Tarjeta',
        'transferencia': 'Transferencia'
    };
    return metodos[metodo] || metodo;
}

function getEstatusClass(estatus) {
    switch(estatus) {
        case 'Pendiente': return 'badge-pendiente';
        case 'Confirmado': return 'badge-confirmado';
        case 'Preparando': return 'badge-preparando';
        case 'En camino': return 'badge-en-camino';
        case 'Entregado': return 'badge-entregado';
        case 'Cancelado': return 'badge-secondary';
        default: return 'badge-pendiente';
    }
}

// Evento para cambiar el estatus del pedido
$(document).on('change', '.estatus-pedido', function() {
    const pedidoId = $(this).data('pedido-id');
    const nuevoEstatus = $(this).val();
    
    console.log('Actualizando pedido:', pedidoId, 'a estatus:', nuevoEstatus);
    
    // Actualizar clase visual inmediatamente
    $(this).removeClass('badge-pendiente badge-confirmado badge-preparando badge-en-camino badge-entregado badge-secondary');
    $(this).addClass(getEstatusClass(nuevoEstatus));
    
    // Actualizar en servidor
    $.ajax({
        url: 'functions/f_gestion_pedidos.php',
        method: 'POST',
        data: {
            accion: 'actualizar_estatus',
            id: pedidoId,
            nuevoEstatus: nuevoEstatus
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                console.log('Estatus actualizado correctamente');
            } else {
                console.error('Error al actualizar estatus:', response.mensaje);
                // Podrías mostrar un mensaje de error al usuario
            }
        },
        error: function(xhr, status, error) {
            console.error('Error de conexión al servidor:', error);
        }
    });
});

function verDetallePedido(pedidoId) {
    console.log('Solicitando detalles del pedido:', pedidoId);
    
    $.ajax({
        url: 'functions/f_gestion_pedidos.php',
        method: 'POST',
        data: {
            accion: 'obtener_detalle',
            id: pedidoId
        },
        dataType: 'json',
        success: function(response) {
            console.log('Detalles del pedido:', response);
            if (response.success) {
                const pedido = response.pedido;
                
                // Información básica
                $('#detalleFecha').text(pedido.fecha_pedido);
                $('#detalleUsuario').text(pedido.usuario);
                $('#detalleMetodoPago').text(formatMetodoPago(pedido.metodo_pago));
                $('#detalleTotal').text('$' + parseFloat(pedido.total).toFixed(2));
                
                // Dirección
                $('#detalleAlias').text(pedido.alias || 'No especificado');
                $('#detalleDireccion').text(pedido.direccion || 'No especificado');
                $('#detalleCiudad').text(pedido.ciudad || 'No especificado');
                $('#detalleCP').text(pedido.cp || 'No especificado');
                
                // Productos
                const tbodyProductos = $('#detalleProductos');
                tbodyProductos.empty();
                
                if (pedido.productos && pedido.productos.length > 0) {
                    pedido.productos.forEach(function(producto) {
                        const subtotal = producto.subtotal || (producto.cantidad * producto.precio);
                        tbodyProductos.append(`
                            <tr>
                                <td>${producto.nombre}</td>
                                <td class="text-center">${producto.cantidad}</td>
                                <td class="text-end">$${parseFloat(subtotal).toFixed(2)}</td>
                            </tr>
                        `);
                    });
                } else {
                    tbodyProductos.append(`
                        <tr>
                            <td colspan="3" class="text-center text-muted">No hay productos en este pedido</td>
                        </tr>
                    `);
                }
                
                $('#detallesPedidoModal').modal('show');
            } else {
                console.error('Error al obtener detalles del pedido:', response.mensaje);
                alert('Error al cargar los detalles del pedido: ' + response.mensaje);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error de conexión al servidor:', error);
            alert('Error de conexión al servidor');
        }
    });
}