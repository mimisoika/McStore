const elementosTooltip = document.querySelectorAll('[data-bs-toggle="tooltip"]');
const listaTooltips = [...elementosTooltip].map(elemento => new bootstrap.Tooltip(elemento));

const pestañas = document.querySelectorAll('#tablas_perfil .nav-link');
pestañas.forEach(pestaña => {
    pestaña.addEventListener('click', function() {
        pestañas.forEach(p => {
            p.classList.remove('active', 'bg-white', 'text-primary');
            p.classList.add('text-white');
        });
        
        this.classList.add('active', 'bg-white', 'text-primary');
        this.classList.remove('text-white');
    });
});

const botonCerrarSesion = document.querySelector('button[name="cerrar_sesion"]');
botonCerrarSesion.addEventListener('click', function(evento) {
    if (!confirm('¿Estás seguro de que deseas cerrar sesión?')) {
        evento.preventDefault();
    }
});


const btnEditar = document.getElementById('btnEditar');
const btnGuardar = document.getElementById('btnGuardar');
const btnCancelar = document.getElementById('btnCancelar');
const camposEditables = ['inputNombre', 'inputApellidoPaterno', 'inputApellidoMaterno', 'inputTelefono'];
let valoresOriginales = {};

if (btnEditar) {
    btnEditar.addEventListener('click', function() {
        // Guardar valores originales
        camposEditables.forEach(id => {
            const campo = document.getElementById(id);
            valoresOriginales[id] = campo.value;
            campo.removeAttribute('readonly');
            campo.classList.add('border-primary');
        });
        
        // Mostrar/ocultar botones
        btnEditar.classList.add('d-none');
        btnGuardar.classList.remove('d-none');
        btnCancelar.classList.remove('d-none');
    });
}

if (btnCancelar) {
    btnCancelar.addEventListener('click', function() {
        camposEditables.forEach(id => {
            const campo = document.getElementById(id);
            campo.value = valoresOriginales[id];
            campo.setAttribute('readonly', true);
            campo.classList.remove('border-primary');
        });
        
        btnEditar.classList.remove('d-none');
        btnGuardar.classList.add('d-none');
        btnCancelar.classList.add('d-none');
    });
}

// Manejar cancelación de pedidos
document.addEventListener('DOMContentLoaded', function() {
    const botonesCancelar = document.querySelectorAll('.btn-cancelar-pedido');
    
    botonesCancelar.forEach(boton => {
        boton.addEventListener('click', function(e) {
            e.preventDefault();
            
            const pedidoId = this.getAttribute('data-pedido-id');
            
            // Confirmar cancelación
            if (confirm('¿Estás seguro de que deseas cancelar el pedido #' + pedidoId + '?')) {
                // Crear formulario para enviar la solicitud
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'perfil.php';
                
                const inputPedidoId = document.createElement('input');
                inputPedidoId.type = 'hidden';
                inputPedidoId.name = 'pedido_id';
                inputPedidoId.value = pedidoId;
                
                const inputAccion = document.createElement('input');
                inputAccion.type = 'hidden';
                inputAccion.name = 'cancelar_pedido';
                inputAccion.value = '1';
                
                form.appendChild(inputPedidoId);
                form.appendChild(inputAccion);
                document.body.appendChild(form);
                
                form.submit();
            }
        });
    });
});