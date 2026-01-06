$(document).ready(function() {
    // Inicializar gráficas
    crearGraficaVentasCategoria();
    crearGraficaVentasMes();
    
    // Activar enlace del menú
    $('.menu-item[data-section]').on('click', function(e) {
        e.preventDefault();
        $('.menu-item').removeClass('active');
        $(this).addClass('active');

        const href = $(this).attr('href');
        $('.content').load(href, function(response, status) {
            if (status === 'error') {
                console.error('Error cargando', href);
            } else {
                console.log('Sección cargada:', href);
            }
        });
    });
    
    // Búsqueda
    $('.search-btn').on('click', function() {
        const searchTerm = $('.search-input').val();
        if (searchTerm) {
            console.log('Buscar:', searchTerm);
        }
    });
    
    $('.search-input').on('keypress', function(e) {
        if (e.which === 13) {
            $('.search-btn').click();
        }
    });
});

// Gráfica de Ventas por Categoría (Pastel)
function crearGraficaVentasCategoria() {
    const ctx = document.getElementById('ventasCategoriaChart');
    if (!ctx || !ventasCategoriaData) return;
    
    const colores = ['#FF8B66', '#FFB366', '#FFCC66', '#66B2FF', '#9966FF'];
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ventasCategoriaData.categorias,
            datasets: [{
                data: ventasCategoriaData.cantidades,
                backgroundColor: colores.slice(0, ventasCategoriaData.categorias.length),
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 12,
                            family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                        },
                        color: '#333',
                        padding: 10,
                        usePointStyle: true
                    }
                }
            }
        }
    });
}

// Gráfica de Ventas del Mes (Línea/Área)
function crearGraficaVentasMes() {
    const ctx = document.getElementById('ventasMesChart');
    if (!ctx || !ventasMesData) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ventasMesData.fechas,
            datasets: [{
                label: 'Ventas del mes',
                data: ventasMesData.montos,
                borderColor: '#FF8B66',
                backgroundColor: 'rgba(255, 139, 102, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#FF8B66',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        font: {
                            size: 12
                        },
                        color: '#333',
                        padding: 15
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#666',
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    ticks: {
                        color: '#666',
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Mover modales fuera de contenedores con overflow para evitar que queden "recortadas"
$(function(){
    // Mover modales ya presentes dentro de .content al body
    $('.content').find('.modal').each(function(){
        $(this).appendTo('body');
    });

    // Observar cambios en .content para mover modales añadidos dinámicamente
    const contentEl = document.querySelector('.content');
    if (contentEl && window.MutationObserver) {
        const observer = new MutationObserver(mutations => {
            mutations.forEach(m => {
                m.addedNodes.forEach(node => {
                    if (node.nodeType === 1) {
                        $(node).find('.modal').each(function(){
                            $(this).appendTo('body');
                        });
                        if ($(node).hasClass('modal')) {
                            $(node).appendTo('body');
                        }
                    }
                });
            });
        });
        observer.observe(contentEl, { childList: true, subtree: true });
    }
});