<div id="modalNovoMotorista">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Novo Motorista</h2>
        <form id="formNovoMotorista" method="post" onsubmit="return false;">
            <div class="form-group">
                <label for="novoCredencial">Credencial:</label>
                <input type="text" id="novoCredencial" name="credencial" inputmode="numeric" pattern="\d*"
                    maxlength="20" placeholder="Apenas números" required>
            </div>

            <div class="form-group">
                <label for="novoNome">Nome:</label>
                <input type="text" id="novoNome" name="nome" required style="text-transform:uppercase;">
            </div>

            <div class="form-group">
                <label for="novoCnh">CNH:</label>
                <input type="text" id="novoCnh" name="cnh" inputmode="numeric" pattern="\d*" maxlength="20"
                    placeholder="Apenas números" required>
            </div>

            <div class="form-group">
                <label for="novoCpf">CPF:</label>
                <input type="text" id="novoCpf" name="cpf" inputmode="numeric" maxlength="14"
                    placeholder="000.000.000-00" required>
            </div>

            <div class="form-group">
                <label for="novoModelo">Modelo:</label>
                <input type="text" id="novoModelo" name="modelo" style="text-transform:uppercase;" required>
            </div>

            <div class="form-group">
                <label for="novoAno">Ano:</label>
                <input type="text" id="novoAno" name="ano" inputmode="numeric" pattern="\d*" maxlength="4"
                    placeholder="Apenas números" required>
            </div>

            <div class="form-group">
                <label for="novoPlaca">Placa:</label>
                <input type="text" id="novoPlaca" name="placa" style="text-transform:uppercase;" required>
            </div>

            <div class="form-group">
                <label for="novoValidade">Data Validade:</label>
                <input type="date" id="novoValidade" name="validade" required>
            </div>

            <button type="submit">Salvar</button>
        </form>
        <script src="assets/js/script_novo_motorista.js"></script>
    </div>
</div>