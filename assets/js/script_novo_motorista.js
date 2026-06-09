function _validarCPF(cpf) {
    cpf = cpf.replace(/\D/g, '');
    if (cpf.length !== 11) return false;
    if (/^(\d)\1{10}$/.test(cpf)) return false; // todos iguais: 00000000000

    let soma = 0;
    for (let i = 0; i < 9; i++) soma += parseInt(cpf[i]) * (10 - i);
    let r = (soma * 10) % 11;
    if (r === 10 || r === 11) r = 0;
    if (r !== parseInt(cpf[9])) return false;

    soma = 0;
    for (let i = 0; i < 10; i++) soma += parseInt(cpf[i]) * (11 - i);
    r = (soma * 10) % 11;
    if (r === 10 || r === 11) r = 0;
    return r === parseInt(cpf[10]);
}

document.addEventListener("DOMContentLoaded", function () {
    const openModalBtn = document.getElementById("btnNovoMotorista");
    const modal = document.getElementById("modalNovoMotorista");
    const closeModalBtn = document.querySelector(".close-modal");

    openModalBtn.addEventListener("click", () => {
        modal.style.display = "flex";

        // Busca a última credencial numérica cadastrada no banco
        fetch("src/ajax/ultima_credencial.php")
            .then(r => r.json())
            .then(data => {
                const ultima = parseInt(data.ultima) || 0;
                const proxima = ultima + 1;
                const label = document.getElementById('ultima-credencial-novo');
                const input = document.getElementById('novoCredencial');
                if (label) label.textContent = 'Última credencial cadastrada: ' + ultima;
                if (input && !input.value) input.value = proxima;
            });
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

    // Validação de credencial duplicada
    const credencialInput = document.getElementById('novoCredencial');
    const avisoCredencial = document.getElementById('ultima-credencial-novo');
    window._credencialDuplicada = false;

    credencialInput.addEventListener('blur', function () {
        const valor = this.value.trim();
        if (!valor) return;

        fetch('src/ajax/verificar_credencial.php?credencial=' + encodeURIComponent(valor))
            .then(r => r.json())
            .then(data => {
                if (data.existe) {
                    window._credencialDuplicada = true;
                    credencialInput.style.borderColor = '#e74c3c';
                    avisoCredencial.style.color = '#e74c3c';
                    const nomeFormatado = data.nome.toLowerCase().replace(/(?:^|\s)\S/g, l => l.toUpperCase());
                    avisoCredencial.textContent = '⚠️ Credencial ' + valor + ' já pertence a: ' + nomeFormatado;
                } else {
                    window._credencialDuplicada = false;
                    credencialInput.style.borderColor = '#2ecc40';
                    avisoCredencial.style.color = '#2ecc40';
                    avisoCredencial.textContent = '✔ Credencial disponível';
                }
            });
    });

    credencialInput.addEventListener('input', function () {
        window._credencialDuplicada = false;
        credencialInput.style.borderColor = '';
        avisoCredencial.style.color = '#666';
        avisoCredencial.textContent = '';
    });

    // Validação de CPF duplicado + CPF real
    const cpfInput = document.getElementById('novoCpf');
    const avisoCpf = document.getElementById('aviso-cpf-novo');
    window._cpfDuplicado = false;

    cpfInput.addEventListener('blur', function () {
        const valor = this.value.replace(/\D/g, '');
        if (valor.length < 11) return;

        // Valida matematicamente o CPF
        if (!_validarCPF(valor)) {
            window._cpfDuplicado = true; // bloqueia o submit
            cpfInput.style.borderColor = '#e74c3c';
            avisoCpf.style.color = '#e74c3c';
            avisoCpf.textContent = '⚠️ CPF inválido.';
            return;
        }

        fetch('src/ajax/verificar_cpf.php?cpf=' + encodeURIComponent(valor))
            .then(r => r.json())
            .then(data => {
                if (data.existe) {
                    window._cpfDuplicado = true;
                    cpfInput.style.borderColor = '#e74c3c';
                    avisoCpf.style.color = '#e74c3c';
                    const nome = data.nome.toLowerCase().replace(/(?:^|\s)\S/g, l => l.toUpperCase());
                    avisoCpf.textContent = '⚠️ CPF já cadastrado para: ' + nome;
                } else {
                    window._cpfDuplicado = false;
                    cpfInput.style.borderColor = '#2ecc40';
                    avisoCpf.style.color = '#2ecc40';
                    avisoCpf.textContent = '✔ CPF válido e disponível';
                }
            });
    });

    cpfInput.addEventListener('input', function () {
        window._cpfDuplicado = false;
        cpfInput.style.borderColor = '';
        avisoCpf.textContent = '';
    });

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
