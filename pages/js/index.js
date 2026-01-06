function agregarAlCarrito(productoId) {
    const informacion = new FormData();
    informacion.append('producto_id', productoId);
    
    fetch('pages/agregar_carrito.php', {
        method: 'POST',
        body: informacion
    })
}

// Inicialización mejorada del carrusel "heroCarousel"
document.addEventListener('DOMContentLoaded', function() {
    var carouselEl = document.querySelector('#heroCarousel');
    if (carouselEl && typeof bootstrap !== 'undefined') {
        new bootstrap.Carousel(carouselEl, {
            interval: 5000,
            pause: 'hover',
            touch: true,
            wrap: true
        });
    }
});

// Función para alternar favorito vía AJAX
function toggleFavorito(productoId, btn) {
    const informacion = new FormData();
    informacion.append('producto_id', productoId);

    const base = window.location.pathname.indexOf('/pages/') !== -1 ? '' : 'pages/';
    fetch(base + 'functions/toggle_favorito.php', {
        method: 'POST',
        body: informacion
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar el icono y la clase del botón
            const icon = btn.querySelector('i');
            if (data.action === 'added') {
                if (icon) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                }
                btn.classList.remove('btn-outline-danger');
                btn.classList.add('btn-danger');
                mostrarMensaje('Producto añadido a favoritos', 'success');
            } else {
                if (icon) {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                }
                btn.classList.remove('btn-danger');
                btn.classList.add('btn-outline-danger');
                mostrarMensaje('Producto removido de favoritos', 'success');
            }
        } else {
            mostrarMensaje(data.message || 'Error al actualizar favoritos', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensaje('Error de conexión', 'error');
    });
}