document.addEventListener('DOMContentLoaded', function() {
    
    document.addEventListener('click', function(evento) {
        const tarjeta = evento.target.closest('[data-producto-id]');
        if (!tarjeta) return;
        
        const idProducto = tarjeta.dataset.productoId;
        const inputCantidad = tarjeta.querySelector('.cantidad-input');
        
        if (evento.target.classList.contains('btn-sumar')) {
            inputCantidad.value = parseInt(inputCantidad.value) + 1;
            actualizarCantidad(idProducto, inputCantidad.value);
        }
        
        if (evento.target.classList.contains('btn-restar')) {
            const nuevaCantidad = Math.max(1, parseInt(inputCantidad.value) - 1);
            inputCantidad.value = nuevaCantidad;
            actualizarCantidad(idProducto, nuevaCantidad);
        }
        
        if (evento.target.classList.contains('btn-eliminar')) {
            if (confirm('¿Eliminar este producto?')) {
                eliminarProducto(idProducto);
            }
        }
    });
    
    document.addEventListener('change', function(evento) {
        if (evento.target.classList.contains('cantidad-input')) {
            const tarjeta = evento.target.closest('[data-producto-id]');
            const nuevaCantidad = Math.max(1, parseInt(evento.target.value) || 1);
            evento.target.value = nuevaCantidad;
            actualizarCantidad(tarjeta.dataset.productoId, nuevaCantidad);
        }
    });
});

function actualizarCantidad(idProducto, cantidad) {
    enviarDatos('actualizar_cantidad', { producto_id: idProducto, cantidad: cantidad });
}

function eliminarProducto(idProducto) {
    enviarDatos('eliminar_producto', { producto_id: idProducto });
}

function enviarDatos(accion, datos) {
    const formulario = new FormData();
    formulario.append('accion', accion);
    
    for (let clave in datos) {
        formulario.append(clave, datos[clave]);
    }
    
    fetch('carrito.php', {
        method: 'POST',
        body: formulario
    })
    .then(respuesta => respuesta.json())
    .then(datosRespuesta => {
        if (datosRespuesta.success) {
            location.reload();
        } else {
            alert('Error: ' + (datosRespuesta.message || 'No se pudo completar la acción'));
        }
    })
    .catch(() => alert('Error de conexión'));
}