// Funcionalidad para cargar detalles del pedido con AJAX
document.addEventListener('DOMContentLoaded', function() {
    // Manejar el clic en el botón de ver detalles
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-ver-detalles')) {
            const boton = e.target.closest('.btn-ver-detalles');
            const pedidoId = boton.getAttribute('data-pedido-id');
            cargarDetallesPedido(pedidoId);
        }
    });

    // Limpiar modal cuando se cierre
    const modalDetalles = document.getElementById('modalDetallesPedido');
    if (modalDetalles) {
        modalDetalles.addEventListener('hidden.bs.modal', function() {
            document.getElementById('numeroPedido').textContent = '';
            document.getElementById('contenidoDetalles').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando detalles del pedido...</p>
                </div>
            `;
        });
    }
});

function cargarDetallesPedido(pedidoId) {
    // Actualizar el número de pedido en el modal
    document.getElementById('numeroPedido').textContent = pedidoId;

    // Mostrar spinner de carga
    const contenidoDetalles = document.getElementById('contenidoDetalles');
    contenidoDetalles.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando detalles del pedido...</p>
        </div>
    `;

    // Realizar petición AJAX
    fetch('detalles_pedido.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'pedido_id=' + encodeURIComponent(pedidoId)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        return response.text();
    })
    .then(html => {
        contenidoDetalles.innerHTML = html;
    })
    .catch(error => {
        console.error('Error:', error);
        contenidoDetalles.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Error al cargar los detalles del pedido. Por favor, intenta nuevamente.
            </div>
        `;
    });
}