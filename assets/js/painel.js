// ==========================================
// painel.js — CONTROLA APENAS O MODAL "NOVO MOTORISTA"
// ==========================================

document.addEventListener("DOMContentLoaded", function () {

    const modalNovo = document.getElementById("modalNovoMotorista");
    const btnAbrirNovo = document.getElementById("btnNovoMotorista");
    const btnFecharNovo = document.getElementById("fecharModal");

    // Abrir modal "Novo Motorista"
    if (btnAbrirNovo && modalNovo) {
        btnAbrirNovo.addEventListener("click", function () {
            modalNovo.style.display = "flex";
        });
    }

    // Fechar modal
    if (btnFecharNovo && modalNovo) {
        btnFecharNovo.addEventListener("click", function () {
            modalNovo.style.display = "none";
        });
    }

    // Fechar clicando fora
    window.addEventListener("click", function (event) {
        if (event.target === modalNovo) {
            modalNovo.style.display = "none";
        }
    });

});
