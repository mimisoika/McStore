$(document).ready(function() {
    const BACKEND_URL = 'functions/f_agregar_productos.php'; 
    let productosCache = []; 

    // Event listeners para filtros, búsqueda y ordenamiento
    $('#filtroCategoria, #filtroEstado, #filtroOrdenNombre, #filtroOrdenStock').change(aplicarFiltrosYOrdenamiento);
    $('#btnBuscar').click(aplicarFiltrosYOrdenamiento);
    $('#filtroBusqueda').keypress(function(e) {
        if (e.which === 13) {
            e.preventDefault(); 
            aplicarFiltrosYOrdenamiento();
        }
    });

    // Iniciar la carga inicial de datos y categorías
    cargarCategorias();
    cargarProductosInicial(); 

    // Preview de imagen (Agregar y Editar)
    $('#imagen').change(function() {
        previewImagen(this, '#previewImagen', '#sinImagen');
    });
    $('#editImagen').change(function() {
        previewImagen(this, '#editPreviewImagen', null);
    });
    
    // Formularios
    $('#formAgregarProducto').submit(function(e) {
        e.preventDefault();
        agregarProducto();
    });
    
    $('#formEditarProducto').submit(function(e) {
        e.preventDefault();
        actualizarProducto();
    });
    
    // Resetear formulario al cerrar modal de agregar
    $('#agregarProductoModal').on('hidden.bs.modal', function () {
        $('#formAgregarProducto')[0].reset();
        $('#previewImagen').hide();
        $('#sinImagen').show();
    });
    
    // Limpiar input file al abrir modal de editar
    $('#editarProductoModal').on('show.bs.modal', function () {
        $('#editImagen').val(''); 
    });

    /**
     * Muestra la previsualización de una imagen seleccionada.
     */
    function previewImagen(input, imgSelector, noImgSelector) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $(imgSelector).attr('src', e.target.result).show();
                if (noImgSelector) {
                    $(noImgSelector).hide();
                }
            };
            reader.readAsDataURL(input.files[0]);
        } else if (noImgSelector) {
            $(imgSelector).hide();
            $(noImgSelector).show();
        }
    }

    // --- GESTIÓN DE CATEGORÍAS Y DATOS ---

    /**
     * Carga las categorías de la DB para llenar los selectores.
     */
    function cargarCategorias() {
        $.ajax({
            url: BACKEND_URL,
            method: 'POST',
            data: { accion: 'obtener_categorias' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const categorias = response.categorias;
                    let optionsFiltro = '<option value="todas">Todas las categorías</option>';
                    let optionsModal = '<option value="">Seleccionar categoría</option>';
                    
                    categorias.forEach(cat => {
                        optionsFiltro += `<option value="${cat.nombre}">${cat.nombre}</option>`;
                        optionsModal += `<option value="${cat.nombre}">${cat.nombre}</option>`; 
                    });
                    
                    $('#filtroCategoria').html(optionsFiltro);
                    $('#categoria').html(optionsModal);
                    $('#editCategoria').html(optionsModal);
                }
            }
        });
    }

    /**
     * Obtiene TODOS los productos del servidor (solo al inicio).
     */
    function cargarProductosInicial() {
        const container = $('#productos-container');
        container.html(`
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p>Cargando productos...</p>
            </div>
        `);

        $.ajax({
            url: BACKEND_URL,
            method: 'POST',
            data: { accion: 'obtener_productos', filtroCategoria: 'todas', filtroEstado: 'todos', busqueda: '' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    productosCache = response.productos;
                    aplicarFiltrosYOrdenamiento(); 
                } else {
                    console.error('Error al cargar productos:', response.mensaje);
                    container.html('<div class="col-12 text-center py-5 text-danger">Error al cargar productos iniciales.</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error de conexión:', status, error, xhr.responseText);
                container.html('<div class="col-12 text-center py-5 text-danger">Error de conexión con el servidor.</div>');
            }
        });
    }

    /**
     * Aplica todos los filtros y ordenamientos a los productos almacenados en caché.
     */
    function aplicarFiltrosYOrdenamiento() {
        let productosFiltrados = [...productosCache]; 
        
        const filtroCategoria = $('#filtroCategoria').val();
        const filtroEstado = $('#filtroEstado').val();
        const busqueda = $('#filtroBusqueda').val().toLowerCase().trim();
        const ordenNombre = $('#filtroOrdenNombre').val();
        const ordenStock = $('#filtroOrdenStock').val();

        // 1. FILTRADO (Local)
        
        if (filtroCategoria !== 'todas') {
            productosFiltrados = productosFiltrados.filter(p => 
                p.categoria && p.categoria.toLowerCase() === filtroCategoria.toLowerCase()
            );
        }

        if (filtroEstado !== 'todos') {
            let estadoDB;
            // Mapear estados del frontend a la DB
            if (filtroEstado === 'activo') estadoDB = 'disponible';
            else if (filtroEstado === 'inactivo') estadoDB = 'suspendido';
            else estadoDB = filtroEstado; // 'agotado' o 'poco_stock'
            
            productosFiltrados = productosFiltrados.filter(p => 
                p.estado === estadoDB
            );
        }

        if (busqueda !== '') {
            productosFiltrados = productosFiltrados.filter(p => {
                const nombre = p.nombre ? p.nombre.toLowerCase() : '';
                const descripcion = p.descripcion ? p.descripcion.toLowerCase() : '';
                return nombre.includes(busqueda) || descripcion.includes(busqueda);
            });
        }
        
        // 2. ORDENAMIENTO (Local)
        productosFiltrados.forEach(p => {
            p.cantidad = parseInt(p.cantidad) || 0; 
            p.nombre = p.nombre || '';
        });

        if (ordenStock !== 'default') {
            productosFiltrados.sort((a, b) => {
                const stockA = a.cantidad;
                const stockB = b.cantidad;

                if (ordenStock === 'asc') {
                    return stockA - stockB; 
                } else {
                    return stockB - stockA; 
                }
            });
        } 
        else if (ordenNombre !== 'default') {
            productosFiltrados.sort((a, b) => {
                const nombreA = a.nombre.toLowerCase();
                const nombreB = b.nombre.toLowerCase();
                
                if (ordenNombre === 'asc') {
                    if (nombreA < nombreB) return -1;
                    if (nombreA > nombreB) return 1;
                    return 0;
                } else { 
                    if (nombreA > nombreB) return -1;
                    if (nombreA < nombreB) return 1;
                    return 0;
                }
            });
        }
        
        mostrarProductos(productosFiltrados);
    }

    /**
     * Renderiza los productos en el contenedor.
     */
    function mostrarProductos(productos) {
        const container = $('#productos-container');
        
        if (productos.length === 0) {
            container.html(`
                <div class="col-12 text-center py-5">
                    <i class="bi bi-box-seam display-1 text-muted"></i>
                    <h3 class="text-muted">No se encontraron productos</h3>
                    <p class="text-muted">Intenta ajustar los filtros o la búsqueda.</p>
                </div>
            `);
            return;
        }
        
        let html = '';
        productos.forEach(producto => {
            const estadoBadge = getEstadoBadge(producto.estado);
            const stockBadge = getStockBadge(producto.cantidad);
            const destacadoIcon = producto.destacado == 1 ? '<i class="bi bi-star-fill text-warning me-2" title="Producto Destacado"></i>' : '';
            const imagenSrc = producto.imagen ? `../../img_productos/${producto.imagen}` : 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';
            
            html += `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 product-card">
                        <div class="position-relative">
                            <img src="${imagenSrc}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="${producto.nombre}" onerror="this.onerror=null;this.src='data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';">
                            <div class="position-absolute top-0 start-0 m-2">${stockBadge}</div>
                            <div class="position-absolute top-0 end-0 m-2">${estadoBadge}</div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">${destacadoIcon}${producto.nombre}</h5>
                            <p class="card-text text-muted small">${producto.descripcion || 'Sin descripción'}</p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="h5 text-primary mb-0">$${parseFloat(producto.precio).toFixed(2)}</span>
                                    <span class="badge bg-info">Stock: ${producto.cantidad || 0}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">${producto.categoria || 'N/A'}</small>
                                </div>
                                <div class="btn-group w-100" role="group">
                                    <button class="btn btn-outline-primary btn-sm" onclick="window.editarProducto(${producto.id})">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="window.eliminarProducto(${producto.id}, '${producto.nombre}')">
                                        <i class="bi bi-dash-circle"></i> Suspender
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.html(html);
    }

    /**
     * Devuelve el HTML del badge de estado según el estado de DB.
     */
    function getEstadoBadge(estado) {
        const badges = {
            'disponible': '<span class="badge bg-success">Disponible</span>',
            'suspendido': '<span class="badge bg-secondary">Suspendido</span>',
            'agotado': '<span class="badge bg-danger">Agotado</span>',
            'poco_stock': '<span class="badge bg-warning text-dark">Poco Stock</span>'
        };
        return badges[estado] || '<span class="badge bg-info">Nuevo</span>';
    }

    /**
     * Devuelve el HTML del badge de stock según la cantidad.
     */
    function getStockBadge(stock) {
        stock = parseInt(stock) || 0;
        if (stock === 0) {
            return '<span class="badge bg-danger">Agotado</span>';
        } else if (stock < 10) {
            return '<span class="badge bg-warning text-dark">Poco Stock</span>';
        } else {
            return '<span class="badge bg-success">Disponible</span>';
        }
    }

    /**
     * Función para determinar estado automático basado en stock.
     */
    function determinarEstadoAutomatico(stock) {
        stock = parseInt(stock) || 0;
        if (stock === 0) {
            return 'agotado';
        } else if (stock < 10) {
            return 'poco_stock';
        } else {
            return 'disponible';
        }
    }

    function agregarProducto() {
        const formData = new FormData();
        formData.append('accion', 'agregar_producto');
        formData.append('nombre', $('#nombre').val());
        formData.append('precio', $('#precio').val());
        formData.append('categoria', $('#categoria').val());
        
        // Calcular estado automáticamente basado en stock
        const cantidad = $('#stock').val() || 0;
        const estadoAutomatico = determinarEstadoAutomatico(cantidad);
        formData.append('estado', estadoAutomatico);
        
        formData.append('descripcion', $('#descripcion').val());
        formData.append('stock', cantidad);
        formData.append('destacado', $('#destacado').val());
        
        const imagen = $('#imagen')[0].files[0];
        if (imagen) {
            formData.append('imagen', imagen);
        }
        
        $.ajax({
            url: BACKEND_URL,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#agregarProductoModal').modal('hide');
                    cargarProductosInicial();
                    alert('Producto agregado exitosamente. El estado se actualizó automáticamente según el stock.');
                } else {
                    alert('Error: ' + (response.mensaje || 'No se pudo agregar el producto'));
                }
            },
            error: function(xhr) {
                alert('Error de conexión o error del servidor.');
            }
        });
    }

    window.editarProducto = function(id) {
        $('#editImagen').val(''); 
        $('#editPreviewImagen').attr('src', '').hide();

        $.ajax({
            url: BACKEND_URL,
            method: 'POST',
            data: {
                accion: 'obtener_producto',
                id: id
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.producto) {
                    const p = response.producto;
                    $('#editId').val(p.id);
                    $('#editNombre').val(p.nombre);
                    $('#editPrecio').val(p.precio);
                    $('#editCategoria').val(p.categoria);
                    
                    // Mostrar estado actual del producto
                    let estadoMostrar = p.estado;
                    if (estadoMostrar === 'disponible') estadoMostrar = 'activo';
                    else if (estadoMostrar === 'suspendido') estadoMostrar = 'inactivo';
                    $('#editEstado').val(estadoMostrar);
                    
                    $('#editDescripcion').val(p.descripcion);
                    $('#editStock').val(p.cantidad);
                    $('#editDestacado').val(p.destacado);
                    
                    if (p.imagen) {
                        $('#editPreviewImagen').attr('src', `../../img_productos/${p.imagen}`).show();
                    } else {
                        $('#editPreviewImagen').hide();
                    }
                    
                    $('#editarProductoModal').modal('show');
                } else {
                    alert('Error: ' + (response.mensaje || 'Producto no encontrado'));
                }
            }
        });
    }

    function actualizarProducto() {
        const formData = new FormData();
        formData.append('accion', 'actualizar_producto');
        formData.append('id', $('#editId').val());
        formData.append('nombre', $('#editNombre').val());
        formData.append('precio', $('#editPrecio').val());
        formData.append('categoria', $('#editCategoria').val());
        
        // Calcular estado automáticamente basado en stock (solo si está activo)
        const cantidad = $('#editStock').val() || 0;
        const estadoSeleccionado = $('#editEstado').val();
        let estadoFinal = estadoSeleccionado;
        
        if (estadoSeleccionado === 'activo' || estadoSeleccionado === 'disponible') {
            estadoFinal = determinarEstadoAutomatico(cantidad);
        } else if (estadoSeleccionado === 'inactivo') {
            estadoFinal = 'suspendido';
        }
        
        formData.append('estado', estadoFinal);
        formData.append('descripcion', $('#editDescripcion').val());
        formData.append('stock', cantidad);
        formData.append('destacado', $('#editDestacado').val());

        const imagen = $('#editImagen')[0].files[0];
        if (imagen) {
            formData.append('imagen', imagen);
        }
        
        $.ajax({
            url: BACKEND_URL,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#editarProductoModal').modal('hide');
                    cargarProductosInicial();
                    alert('Producto actualizado exitosamente. El estado se actualizó automáticamente según el stock.');
                } else {
                    alert('Error: ' + (response.mensaje || 'No se pudo actualizar el producto'));
                }
            },
            error: function(xhr) {
                alert('Error de conexión o error del servidor.');
            }
        });
    }

    /**
     * Función utilizada para cambiar el estado de un producto a 'suspendido' (Inactivo).
     */
    window.eliminarProducto = function(id, nombre) {
        if (confirm(`¿Estás seguro de que deseas suspender el producto "${nombre}"? (Cambiará su estado a INACTIVO y no se actualizará automáticamente por stock)`)) {
            $.ajax({
                url: BACKEND_URL,
                method: 'POST',
                data: {
                    accion: 'eliminar_producto', 
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        cargarProductosInicial();
                        alert('Producto suspendido exitosamente.');
                    } else {
                        alert('Error: ' + (response.mensaje || 'No se pudo suspender el producto'));
                    }
                },
                error: function(xhr) {
                    alert('Error de conexión o error del servidor al suspender.');
                }
            });
        }
    }
});