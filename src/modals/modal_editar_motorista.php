<div id="modalEditarMotorista" class="modal">
    <div class="modal-content">
        <span onclick="document.getElementById('modalEditarMotorista').classList.remove('show')" style="position:absolute; top:12px; right:18px; font-size:1.5rem; font-weight:bold; color:#aaa; cursor:pointer; line-height:1;" onmouseover="this.style.color='#e63946'" onmouseout="this.style.color='#aaa'">&times;</span>
        <h2>Editar Motorista</h2>
        <form id="formEditarMotorista">
            <input type="hidden" id="editarId" name="id">

            <div class="form-group">
                <label for="editarCredencial">Credencial:</label>
                <input type="text" id="editarCredencial" name="credencial" inputmode="numeric" pattern="\d*"
                    maxlength="20" placeholder="Apenas números" required>
            </div>

            <div class="form-group">
                <label for="editarNome">Nome:</label>
                <input type="text" id="editarNome" name="nome" style="text-transform:uppercase;" required>
            </div>

            <div class="form-group">
                <label for="editarCnh">CNH:</label>
                <input type="text" id="editarCnh" name="cnh" inputmode="numeric" pattern="\d*" maxlength="20"
                    placeholder="Apenas números" required>
            </div>

            <div class="form-group">
                <label for="editarCpf">CPF:</label>
                <input type="text" id="editarCpf" name="cpf" inputmode="numeric" maxlength="14"
                    placeholder="000.000.000-00" required>
                <small id="aviso-cpf-editar" style="display:none; font-size:0.82rem; margin-top:3px;"></small>
            </div>

            <div class="form-group">
                <label for="editarModelo">Modelo:</label>
                <input type="text" id="editarModelo" name="modelo" style="text-transform:uppercase;" required>
            </div>

            <div class="form-group">
                <label for="editarAno">Ano:</label>
                <input type="text" id="editarAno" name="ano" inputmode="numeric" pattern="\d*" maxlength="4"
                    placeholder="Apenas números" required>
            </div>

            <div class="form-group">
                <label for="editarPlaca">Placa:</label>
                <input type="text" id="editarPlaca" name="placa" style="text-transform:uppercase;" required>
            </div>

            <div class="form-group">
                <label for="editarValidade">Data Validade:</label>
                <input type="date" id="editarValidade" name="validade" value="" required>
            </div>

            <div class="form-group">
                <label for="editarStatus">Status:</label>
                <select id="editarStatus" name="status">
                    <option value="automatico">Automático (pela validade)</option>
                    <option value="suspenso">Suspenso</option>
                    <option value="pendente">Pendente</option>
                </select>
            </div>

            <div style="margin-top: 1rem;">
                <button type="submit" class="btn-salvar" style="width: 100%; font-size: 1rem; padding: 0.8rem;">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>
</div>
<script src="assets/js/script_novo_motorista.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof onlyDigitsListener === 'function') {
        onlyDigitsListener(document.getElementById('editarCredencial'));
        onlyDigitsListener(document.getElementById('editarCnh'));
        onlyDigitsListener(document.getElementById('editarAno'));
    }
    if (typeof uppercaseListener === 'function') {
        uppercaseListener(document.getElementById('editarNome'));
        uppercaseListener(document.getElementById('editarModelo'));
        uppercaseListener(document.getElementById('editarPlaca'));
    }
    if (typeof maskCPFListener === 'function') {
        maskCPFListener(document.getElementById('editarCpf'));
    }

    // Validação CPF duplicado no editar
    const editarCpfInput = document.getElementById('editarCpf');
    const avisoCpfEditar = document.getElementById('aviso-cpf-editar');

    editarCpfInput.addEventListener('blur', function () {
        const cpf = this.value.replace(/\D/g, '');
        const id  = document.getElementById('editarId').value;
        if (cpf.length < 11) {
            avisoCpfEditar.style.display = 'none';
            window._cpfDuplicadoEditar = false;
            this.style.borderColor = '';
            return;
        }

        // Valida matematicamente
        if (typeof _validarCPF === 'function' && !_validarCPF(cpf)) {
            avisoCpfEditar.textContent = '⚠️ CPF inválido.';
            avisoCpfEditar.style.display = 'block';
            avisoCpfEditar.style.color = '#ff6b6b';
            editarCpfInput.style.borderColor = 'red';
            window._cpfDuplicadoEditar = true;
            return;
        }

        fetch('src/ajax/verificar_cpf.php?cpf=' + encodeURIComponent(cpf) + '&id=' + encodeURIComponent(id))
            .then(r => r.json())
            .then(data => {
                if (data.existe) {
                    avisoCpfEditar.textContent = '⚠️ CPF já cadastrado: ' + data.nome;
                    avisoCpfEditar.style.display = 'block';
                    avisoCpfEditar.style.color = '#ff6b6b';
                    editarCpfInput.style.borderColor = 'red';
                    window._cpfDuplicadoEditar = true;
                } else {
                    avisoCpfEditar.textContent = '✔️ CPF disponível';
                    avisoCpfEditar.style.display = 'block';
                    avisoCpfEditar.style.color = '#7ee8a2';
                    editarCpfInput.style.borderColor = 'green';
                    window._cpfDuplicadoEditar = false;
                }
            });
    });
});
</script>