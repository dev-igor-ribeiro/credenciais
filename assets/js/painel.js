
// Novo painel.js com script para abrir/fechar modal
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("modalNovoMotorista");
    const btnAbrir = document.getElementById("btnNovoMotorista");
    const btnFechar = document.getElementById("fecharModal");

    if (btnAbrir && modal) {
        btnAbrir.addEventListener("click", function () {
            modal.style.display = "flex";
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
