document.getElementById('confirmar_contrasena').addEventListener('input', function() {
            const password = document.getElementById('contrasena_registrada').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });