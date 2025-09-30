<!-- Modal para formulário de credencial -->
<div class="modal" id="modalCredencial">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('modalCredencial').classList.remove('show')">&times;</span>
        <h2>Nova Credencial</h2>
        <form action="src/pdf/gerar_pdf.php" method="POST" target="_blank" id="formCredencial">
            <div class="form-group">
                <label for="numero">Número Credencial</label>
                <input type="text" id="numero" name="numero" inputmode="numeric" pattern="\d*" maxlength="20"
                    placeholder="Apenas números" required>
                <small id="ultima-credencial" style="display:block;margin-top:5px;color:#666;">
                    Última credencial gerada: -
                </small>
            </div>
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" style="text-transform: uppercase;" required>
            </div>
            <div class="form-group">
                <label for="cpf">CPF</label>
                <input type="text" id="cpf" name="cpf" inputmode="numeric" maxlength="14" placeholder="000.000.000-00"
                    required>
            </div>
            <div class="form-group">
                <label for="cnh">Número CNH</label>
                <input type="text" id="cnh" name="cnh" inputmode="numeric" pattern="\d*" maxlength="20"
                    placeholder="Apenas números" required>
            </div>
            <button type="submit" class="btn-salvar">Gerar PDF</button>
        </form>
    </div>
</div>

<div class="modal" id="modalErro">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('modalErro').classList.remove('show')">&times;</span>
        <h2>Erro de Validação</h2>
        <p>Por favor verifique os campos: Número Credencial, CNH (apenas números) e CPF (11 dígitos).</p>
        <button onclick="document.getElementById('modalErro').classList.remove('show')">Fechar</button>
    </div>
</div>

<script>
// Input helpers for modalCredencial: ensure numeric-only for numero and cnh, and CPF mask
(function() {
    function onlyDigits(el) {
        el.addEventListener('input', function(e) {
            const cursor = this.selectionStart;
            const cleaned = this.value.replace(/\D/g, '');
            this.value = cleaned;
            // try to keep cursor at end
            this.setSelectionRange(this.value.length, this.value.length);
        });
    }

    function maskCPF(el) {
        el.addEventListener('input', function(e) {
            let v = this.value.replace(/\D/g, '');
            if (v.length > 11) v = v.slice(0, 11);
            // build mask 000.000.000-00
            let out = '';
            if (v.length > 0) out += v.substring(0, Math.min(3, v.length));
            if (v.length > 3) out += '.' + v.substring(3, Math.min(6, v.length));
            if (v.length > 6) out += '.' + v.substring(6, Math.min(9, v.length));
            if (v.length > 9) out += '-' + v.substring(9, 11);
            this.value = out;
            // keep caret at end
            this.setSelectionRange(this.value.length, this.value.length);
        });

        // also allow paste normalization
        el.addEventListener('paste', function(e) {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text');
            const digits = text.replace(/\D/g, '').slice(0, 11);
            // format then set
            let out = '';
            if (digits.length > 0) out += digits.substring(0, Math.min(3, digits.length));
            if (digits.length > 3) out += '.' + digits.substring(3, Math.min(6, digits.length));
            if (digits.length > 6) out += '.' + digits.substring(6, Math.min(9, digits.length));
            if (digits.length > 9) out += '-' + digits.substring(9, 11);
            this.value = out;
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const numero = document.getElementById('numero');
        const cnh = document.getElementById('cnh');
        const cpf = document.getElementById('cpf');
        const nome = document.getElementById('nome');

        if (numero) onlyDigits(numero);
        if (cnh) onlyDigits(cnh);
        if (cpf) maskCPF(cpf);
        if (nome) {
            nome.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        }

        // Load last credential from localStorage if available
        const ultimaSalva = localStorage.getItem("ultimaCredencial");
        if (ultimaSalva) {
            const historico = document.getElementById('ultima-credencial');
            if (historico) {
                historico.textContent = "Última credencial gerada: " + ultimaSalva;
            }
            if (numero) {
                numero.value = parseInt(ultimaSalva, 10) + 1;
            }
        }

        // Prevent non-numeric form submission for numeric fields as extra safety
        const form = document.getElementById('formCredencial');
        if (form) {
            form.addEventListener('submit', function(e) {
                // final validation
                const n = numero ? numero.value.replace(/\D/g, '') : '';
                const c = cnh ? cnh.value.replace(/\D/g, '') : '';
                const cp = cpf ? cpf.value.replace(/\D/g, '') : '';
                if (!n || !c || cp.length !== 11) {
                    e.preventDefault();
                    document.getElementById('modalErro').classList.add('show');
                    return false;
                }

                // Após envio bem-sucedido, limpar o formulário e incrementar o número da credencial
                setTimeout(() => {
                    const ultimoNumero = parseInt(numero.value.replace(/\D/g, ''), 10) || 0;
                    localStorage.setItem("ultimaCredencial", ultimoNumero);
                    form.reset();
                    numero.value = ultimoNumero + 1;
                    const historico = document.getElementById('ultima-credencial');
                    if (historico) {
                        historico.textContent = "Última credencial gerada: " + ultimoNumero;
                    }
                    if (typeof mostrarMensagem === 'function') {
                        mostrarMensagem("success",
                            "✅ PDF gerado com sucesso! Confira na nova guia.");
                    }
                }, 1000);

                return true;
            });
        }
    });
})();
</script>