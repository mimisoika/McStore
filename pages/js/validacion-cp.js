// Validación de código postal en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const inputCP = document.getElementById('codigo_postal');
    const infoCP = document.getElementById('info_cp');
    const asentamientoInfo = document.getElementById('asentamiento_info');
    const btnGuardarDireccion = document.getElementById('btnGuardarDireccion');
    const formDireccion = document.getElementById('formDireccion');

    if (inputCP) {
        let errorDiv = null;

        // Validar formato al perder el foco
        inputCP.addEventListener('blur', function() {
            validarCodigoPostal(this.value);
        });

        // Validar en tiempo real mientras se escribe
        inputCP.addEventListener('input', function() {
            const cp = this.value.trim();
            
            // Limpiar validación anterior
            limpiarValidacion();
            
            if (cp.length === 5) {
                validarCodigoPostal(cp);
            } else if (cp.length > 0) {
                mostrarErrorCP('El código postal debe tener exactamente 5 dígitos');
            }
        });

        // Prevenir envío del formulario si el CP no es válido
        formDireccion.addEventListener('submit', function(e) {
            const cp = inputCP.value.trim();
            if (!validarFormatoCP(cp) || !validarCodigoPostal(cp, true)) {
                e.preventDefault();
                mostrarErrorCP('Por favor, ingresa un código postal válido para Baja California Sur');
                inputCP.focus();
                return false;
            }
        });

        function limpiarValidacion() {
            inputCP.classList.remove('is-valid', 'is-invalid');
            infoCP.classList.add('d-none');
            
            // Remover mensaje de error si existe
            if (errorDiv) {
                errorDiv.remove();
                errorDiv = null;
            }
        }

        function validarFormatoCP(cp) {
            return /^23\d{3}$/.test(cp);
        }

        function validarCodigoPostal(cp, esSubmit = false) {
            // Limpiar validación anterior
            limpiarValidacion();

            // Validar formato básico
            if (!validarFormatoCP(cp)) {
                if (cp.length === 5) {
                    mostrarErrorCP('Formato de CP inválido. Debe empezar con 23 y tener 5 dígitos.');
                }
                return false;
            }

            // Lista completa de CPs válidos para Baja California Sur
            const cpsValidos = [
                '23600', '23610', '23620', '23630', '23640', '23641', '23643', '23650', '23653', '23658',
                '23660', '23670', '23676', '23677', '23678', '23680', '23683', '23690', '23695', '23696',
                '23697', '23700', '23708', '23710', '23715', '23720', '23721', '23723', '23730', '23736',
                '23737', '23739', '23740', '23743', '23748', '23749', '23750', '23760', '23765', '23766',
                '23770', '23771', '23774', '23775', '23780', '23789', '23790', '23800', '23805', '23810',
                '23812', '23813', '23818', '23820', '23824', '23830', '23834', '23837', '23838', '23840',
                '23844', '23845', '23860', '23870', '23873', '23880', '23883', '23884', '23885', '23886',
                '23887', '23888', '23889', '23890', '23893', '23894', '23895', '23896', '23897', '23898'
            ];

            if (!cpsValidos.includes(cp)) {
                mostrarErrorCP('CP no perteneciente a la región de Baja California Sur (Comondú o Loreto)');
                return false;
            }

            // Si es válido, mostrar información del asentamiento
            const asentamiento = obtenerAsentamiento(cp);
            mostrarInfoCP(asentamiento);
            return true;
        }

        function obtenerAsentamiento(cp) {
            const asentamientos = {
                '23600': 'Zona Centro, Cerro Catedral, INVI Olivos Juan Domínguez Cota',
                '23610': 'Las Palmas',
                '23620': 'Cecilia Madrid, Longoria, Vargas, Renero',
                '23630': 'Los Olivos',
                '23640': 'El Paraíso, FOVISSSTE Olimpico, Olímpico',
                '23641': '4 de Marzo, Ampliación 4 de marzo, Conjunto Urbano del Norte',
                '23643': 'Valle Dorado',
                '23650': 'INVI Hacienda, Lienzo Charro',
                '23653': 'Militar',
                '23658': 'Residencial La Hacienda',
                '23660': 'Guaycura',
                '23670': 'El Crucero, Los Pinos, Pueblo Nuevo',
                '23676': 'Chato Covarrubias, FOVISSSTE Pioneros, Pioneros, Pioneros II, Revolución Mexicana',
                '23677': 'Conjunto Urbano del Sur, Constitución, La Esperanza, Salomón Sández',
                '23678': 'Plano Oriente, San Isidro Labrador',
                '23680': 'FOVISSSTE Real, Real, Valle Paraíso',
                '23683': 'Paseos de Don Pelayo',
                '23690': 'Batequitos, INFONAVIT San Martín',
                '23695': 'Roberto Esperon',
                '23696': 'El Agricultor INDECO, La Roca, Los Romeros',
                '23697': 'Brisas del Valle, Magisterial',
                '23700': 'Ciudad Insurgentes, Fernando de la Toba',
                '23708': 'Rio Mayo',
                '23710': 'Puerto Adolfo López Mateos',
                '23715': 'Villa Ignacio Zaragoza',
                '23720': 'Villa Hidalgo, Teotlán',
                '23721': 'Ramaditas, San Juan de Matancitas',
                '23723': 'Josefa Ortiz de Domínguez',
                '23730': 'Benito Juárez',
                '23736': 'Palo Bola',
                '23737': 'El Vallecito',
                '23739': 'Navojoa 1',
                '23740': 'Puerto San Carlos',
                '23743': 'El Ranchito',
                '23748': 'Puerto Magdalena',
                '23749': 'Puerto Alcatraz',
                '23750': 'Puerto Cortés',
                '23760': 'Ley Federal de Aguas Número Cinco',
                '23765': 'San Luis Gonzaga, Buenos Aires',
                '23766': 'Tepentú',
                '23770': 'Villa Morelos',
                '23771': 'El Vergel',
                '23774': 'Las Delicias',
                '23775': 'Yaquis Lote 13',
                '23780': 'Ley Federal de Aguas Número Cuatro',
                '23789': 'Santa Teresa',
                '23790': 'Ley Federal de Aguas Número Tres',
                '23800': 'San Miguel de Comondú',
                '23805': 'San Pedro',
                '23810': 'San Isidro',
                '23812': 'San Juanico, La Yaqui',
                '23813': 'Purísima Vieja, Paso Hondo',
                '23818': 'La Bocana de San Gregorio',
                '23820': 'San José de Comondú, La Purísima, El Pabellón, Carambuche',
                '23824': 'El Ojo de Agua',
                '23830': 'La Poza Grande, Francisco Villa',
                '23834': 'Las Barrancas, El Chicharrón',
                '23837': 'San Venancio',
                '23838': 'El Canelo',
                '23840': 'María Auxiliadora',
                '23844': 'Santo Domingo, Jalisco',
                '23845': 'Palo Alto',
                '23860': 'Ley Federal de Aguas Número Dos',
                '23870': 'Ley Federal de Aguas Número Uno',
                '23873': 'San Ignacio',
                '23880': 'Zona Centro Loreto',
                '23883': 'Lomas de Loreto',
                '23884': 'Nopolo',
                '23885': 'Puerto Escondido',
                '23886': 'San Javier',
                '23887': 'Ligüí',
                '23888': 'Agua Verde',
                '23889': 'Comondú',
                '23890': 'Insurgentes',
                '23893': 'Villa del Palmar',
                '23894': 'Mision de Loreto',
                '23895': 'Las Cuevas',
                '23896': 'Ensenada Blanca',
                '23897': 'Loreto Bay',
                '23898': 'Juncalito'
            };
            
            return asentamientos[cp] || 'Asentamiento no identificado';
        }

        function mostrarErrorCP(mensaje) {
            inputCP.classList.remove('is-valid');
            inputCP.classList.add('is-invalid');
            
            // Crear o actualizar mensaje de error
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                inputCP.parentNode.appendChild(errorDiv);
            }
            errorDiv.textContent = mensaje;
            
            infoCP.classList.add('d-none');
            if (btnGuardarDireccion) {
                btnGuardarDireccion.disabled = true;
            }
        }

        function mostrarInfoCP(asentamiento) {
            inputCP.classList.remove('is-invalid');
            inputCP.classList.add('is-valid');
            
            // Remover mensaje de error si existe
            if (errorDiv) {
                errorDiv.remove();
                errorDiv = null;
            }
            
            // Mostrar información del asentamiento
            asentamientoInfo.textContent = asentamiento;
            infoCP.classList.remove('d-none');
            if (btnGuardarDireccion) {
                btnGuardarDireccion.disabled = false;
            }
        }
    }
});