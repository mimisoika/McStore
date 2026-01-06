document.addEventListener("DOMContentLoaded", () => {

    // Detecta si estás dentro de /pages/ o en la raíz
    // Si la URL contiene "/pages/", baseURL es vacía (estamos dentro).
    // Si NO contiene "/pages/", estamos en raíz, baseURL es "pages/".
    const baseURL = window.location.pathname.includes("/pages/") ? "" : "pages/";

    // 1. Quitar favoritos (usado en perfil)
    document.querySelectorAll(".btn-remove-fav").forEach(btn => {
        btn.addEventListener("click", function () {
            let productId = this.getAttribute("data-id");
            // Ajustamos el selector del card por si cambia la estructura HTML
            let card = this.closest(".col-lg-4") || this.closest(".card").parentElement;

            fetch(baseURL + "functions/quitar_favoritos.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "id=" + productId
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Animación simple de desvanecimiento
                    card.style.transition = "opacity 0.5s";
                    card.style.opacity = "0";
                    setTimeout(() => card.remove(), 500);
                }
            })
            .catch(err => console.error("Error al quitar favorito:", err));
        });
    });

    // 2. Toggle favoritos (usado en index y catálogo)
    document.querySelectorAll(".btn-fav").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault(); // Prevenir comportamiento default del botón
            
            let id = this.dataset.id;
            let icono = document.getElementById("icono-fav-" + id);

            if(!icono) return; // Seguridad

            fetch(baseURL + "functions/toggle_favorito.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "id=" + id
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (data.status === "added") {
                        // Cambiar a corazón relleno y rojo
                        icono.classList.remove("fa-regular");
                        icono.classList.add("fa-solid", "text-danger");
                        // Efecto visual opcional (latido)
                        icono.style.transform = "scale(1.2)";
                        setTimeout(() => icono.style.transform = "scale(1)", 200);
                    } else {
                        // Cambiar a corazón vacío
                        icono.classList.remove("fa-solid", "text-danger");
                        icono.classList.add("fa-regular");
                    }
                } else if (data.message === 'Debe iniciar sesión') {
                    // Opcional: Redirigir a login si el backend lo indica
                    window.location.href = baseURL + "login.php";
                }
            })
            .catch(err => console.error("Error al togglear favorito:", err));
        });
    });
});