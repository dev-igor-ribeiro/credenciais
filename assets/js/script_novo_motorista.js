
document.addEventListener("DOMContentLoaded", function () {
    const openModalBtn = document.getElementById("btnNovoMotorista");
    const modal = document.getElementById("modalNovoMotorista");
    const closeModalBtn = document.querySelector(".close-modal");

    openModalBtn.addEventListener("click", () => {
        modal.style.display = "flex";
    });

    closeModalBtn.addEventListener("click", () => {
        modal.style.display = "none";
    });

    window.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
        }
    });

    // Listeners for novo motorista modal fields
    function uppercaseListener(el) {
        if (!el) return;
        el.addEventListener('input', function () {
            const start = this.selectionStart;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(start, start);
        });
        el.addEventListener('paste', function (e) {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text') || '';
            this.value = text.toUpperCase();
        });
    }

    function onlyDigitsListener(el) {
        if (!el) return;
        el.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
        });
        el.addEventListener('paste', function (e) {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text');
            this.value = text.replace(/\D/g, '');
        });
    }

    function maskCPFListener(el) {
        if (!el) return;
        el.addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '');
            if (v.length > 11) v = v.slice(0, 11);
            let out = '';
            if (v.length > 0) out += v.substring(0, Math.min(3, v.length));
            if (v.length > 3) out += '.' + v.substring(3, Math.min(6, v.length));
            if (v.length > 6) out += '.' + v.substring(6, Math.min(9, v.length));
            if (v.length > 9) out += '-' + v.substring(9, 11);
            this.value = out;
        });
        el.addEventListener('paste', function (e) {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text');
            let v = text.replace(/\D/g, '').slice(0, 11);
            let out = '';
            if (v.length > 0) out += v.substring(0, Math.min(3, v.length));
            if (v.length > 3) out += '.' + v.substring(3, Math.min(6, v.length));
            if (v.length > 6) out += '.' + v.substring(6, Math.min(9, v.length));
            if (v.length > 9) out += '-' + v.substring(9, 11);
            this.value = out;
        });
    }

    const nomeNovo = document.getElementById('novoNome');
    const cnhNovo = document.getElementById('novoCnh');
    const cpfNovo = document.getElementById('novoCpf');
    const credNovo = document.getElementById('novoCredencial');
    const anoNovo = document.getElementById('novoAno');

    uppercaseListener(nomeNovo);
    onlyDigitsListener(cnhNovo);
    onlyDigitsListener(credNovo);
    onlyDigitsListener(anoNovo);
    maskCPFListener(cpfNovo);
});
