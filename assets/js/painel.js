
// Novo painel.js com script para abrir/fechar modal
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("modalNovoMotorista");
    const btnAbrir = document.getElementById("btnNovoMotorista");
    const btnFechar = document.getElementById("fecharModal");

    if (btnAbrir && modal) {
        btnAbrir.addEventListener("click", function () {
            modal.style.display = "flex";

            // Preencher última credencial do localStorage
            const ultimaSalva = localStorage.getItem("ultimaCredencial");
            const infoEl = document.getElementById("ultima-credencial-novo");
            const inputCredencial = document.getElementById("novoCredencial");
            if (ultimaSalva) {
                if (infoEl) infoEl.textContent = "Última credencial gerada: " + ultimaSalva;
                if (inputCredencial && !inputCredencial.value) {
                    inputCredencial.value = parseInt(ultimaSalva, 10) + 1;
                }
            } else {
                if (infoEl) infoEl.textContent = "Última credencial gerada: -";
            }
        });
    }

    if (btnFechar && modal) {
        btnFechar.addEventListener("click", function () {
            modal.style.display = "none";
        });
    }

    // Fechar ao clicar fora
    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
});
