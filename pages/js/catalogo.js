function agregarAlCarrito(productoId) {
    const informacion = new FormData();
    informacion.append('producto_id', productoId);
    informacion.append('cantidad', 1);
    
    // endpoint base: si la página actual está en /pages/ usamos ruta relativa, si está en raíz usamos pages/
    const base = window.location.pathname.indexOf('/pages/') !== -1 ? '' : 'pages/';
    fetch(base + 'agregar_carrito_catalogo.php', {
        method: 'POST',
        body: informacion
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar mensaje de éxito
            mostrarMensaje('Producto agregado al carrito exitosamente', 'success');
        } else {
            // Mostrar mensaje de error
            mostrarMensaje(data.message || 'Error al agregar producto', 'error');
            
            // Si no está logueado, redirigir al login
            if (data.message && data.message.includes('iniciar sesión')) {
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensaje('Error de conexión', 'error');
    });
}

function mostrarMensaje(mensaje, tipo) {
    // Crear elemento de alerta
    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    alerta.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    
    alerta.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Agregar al body
    document.body.appendChild(alerta);
    
    // Remover automáticamente después de 5 segundos
    setTimeout(() => {
        if (alerta.parentNode) {
            alerta.remove();
        }
    }, 5000);
}

// Función para alternar favorito vía AJAX
function toggleFavorito(productoId, btn) {
    const informacion = new FormData();
    informacion.append('producto_id', productoId);

    const base = window.location.pathname.indexOf('/pages/') !== -1 ? '' : 'pages/';
    fetch(base + 'toggle_favorito.php', {
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



// Debounce helper para AJAX
function debounce(fn, delay) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
}

// Manejo del slider de precio en la página de catálogo
document.addEventListener('DOMContentLoaded', function() {
    const minRange = document.getElementById('minPrecioRange');
    const maxRange = document.getElementById('maxPrecioRange');
    const minVal = document.getElementById('minPrecioVal');
    const maxVal = document.getElementById('maxPrecioVal');
    const minInput = document.getElementById('minPrecioInput');
    const maxInput = document.getElementById('maxPrecioInput');
    const categoriaSelect = document.getElementById('categoria');
    const ordenSelect = document.getElementById('orden');
    const filterForm = document.getElementById('filterForm');

    if (!minRange || !maxRange || !minVal || !maxVal || !filterForm) return;

    function formatMoney(v) {
        return parseFloat(v).toFixed(2);
    }

    // Función para actualizar valores visuales
    function updateDisplayValues() {
        let min = parseFloat(minRange.value);
        let max = parseFloat(maxRange.value);
        
        if (min > max) {
            if (this === minRange) {
                maxRange.value = min;
                max = min;
            } else {
                minRange.value = max;
                min = max;
            }
        }
        minVal.textContent = formatMoney(min);
        maxVal.textContent = formatMoney(max);
        
        // Sincronizar inputs numéricos
        if (minInput) minInput.value = min;
        if (maxInput) maxInput.value = max;
    }

    // Función para sincronizar sliders desde inputs numéricos
    function syncSlidersFromInputs() {
        if (minInput && parseFloat(minInput.value)) {
            minRange.value = minInput.value;
        }
        if (maxInput && parseFloat(maxInput.value)) {
            maxRange.value = maxInput.value;
        }
        updateDisplayValues.call(minRange);
    }

    // Función para hacer AJAX con los filtros
    async function fetchProductos(pushUrl = true) {
        const categoria = categoriaSelect?.value || 'todas';
        const minPrecio = minRange.value;
        const maxPrecio = maxRange.value;
        const orden = ordenSelect?.value || '';

        const params = new URLSearchParams({
            categoria: categoria,
            min_precio: minPrecio,
            max_precio: maxPrecio,
            orden: orden
        });

        try {
            const response = await fetch('get_productos.php?' + params.toString());
            const html = await response.text();
            const productosGrid = document.getElementById('productosGrid');
            if (productosGrid) {
                productosGrid.innerHTML = html;
            }

            // Actualizar URL sin recargar
            if (pushUrl) {
                const newUrl = '?' + params.toString();
                history.replaceState(null, '', newUrl);
            }
        } catch (error) {
            console.error('Error fetching products:', error);
        }
    }

    const debouncedFetch = debounce(fetchProductos, 250);

    // Event listeners para sliders
    minRange.addEventListener('input', () => {
        updateDisplayValues.call(minRange);
        debouncedFetch();
    });

    maxRange.addEventListener('input', () => {
        updateDisplayValues.call(maxRange);
        debouncedFetch();
    });

    // Event listeners para inputs numéricos
    if (minInput) {
        minInput.addEventListener('change', () => {
            syncSlidersFromInputs();
            debouncedFetch();
        });
    }
    if (maxInput) {
        maxInput.addEventListener('change', () => {
            syncSlidersFromInputs();
            debouncedFetch();
        });
    }

    // Event listeners para category y order (sin debounce)
    if (categoriaSelect) {
        categoriaSelect.addEventListener('change', () => {
            fetchProductos(true);
        });
    }
    if (ordenSelect) {
        ordenSelect.addEventListener('change', () => {
            fetchProductos(true);
        });
    }

    // Prevenir que el formulario se envíe
    filterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        fetchProductos(true);
    });

    // Inicializar valores
    updateDisplayValues.call(minRange);
});