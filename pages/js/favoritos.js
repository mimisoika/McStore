document.addEventListener("DOMContentLoaded", () => {

    // Detecta si estás dentro de /pages/ o no
    const baseURL = window.location.pathname.includes("/pages/") ? "" : "pages/";

    document.querySelectorAll(".btn-remove-fav").forEach(btn => {
        btn.addEventListener("click", function () {

            let productId = this.getAttribute("data-id");
            let card = this.closest(".col-lg-4");

            fetch(baseURL + "functions/quitar_favoritos.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "id=" + productId
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    card.remove();
                }
            });
        });
    });

    document.querySelectorAll(".btn-fav").forEach(btn => {
        btn.addEventListener("click", function () {

            let id = this.dataset.id;
            let icono = document.getElementById("icono-fav-" + id);

            fetch(baseURL + "functions/toggle_favorito.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "id=" + id
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {

                    if (data.status === "added") {
                        icono.classList.remove("fa-regular");
                        icono.classList.add("fa-solid", "text-danger");
                    } else {
                        icono.classList.remove("fa-solid", "text-danger");
                        icono.classList.add("fa-regular");
                    }
                }
            });

        });
    });
});