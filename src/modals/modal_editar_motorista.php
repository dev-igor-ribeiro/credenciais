<div id="modalEditarMotorista" class="modal">
    <div class="modal-content">
        <span class="close" aria-label="Fechar"
            onclick="document.getElementById('modalEditarMotorista').classList.remove('show')">
            &times;
        </span>
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

            <button type="submit" class="btn-salvar">Salvar Alterações</button>
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
});
</script>