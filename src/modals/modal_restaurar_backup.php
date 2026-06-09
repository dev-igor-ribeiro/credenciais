<div id="modalRestaurarBackup" class="modal">
    <div class="modal-content" style="max-width:560px;">
        <span class="close" onclick="document.getElementById('modalRestaurarBackup').classList.remove('show')">&times;</span>
        <h2>🗄️ Restaurar Backup</h2>
        <p style="color:#ffb3b3; font-size:0.88rem; margin-bottom:1rem;">
            ⚠️ <strong>Atenção:</strong> restaurar um backup substitui <u>todos os dados atuais</u> do banco. Esta ação não pode ser desfeita.
        </p>
        <div id="listaBackups" style="max-height:320px; overflow-y:auto;">
            <p style="color:#aaa; text-align:center;">Carregando...</p>
        </div>
    </div>
</div>

<script>
function abrirModalRestaurar() {
    document.getElementById('modalRestaurarBackup').classList.add('show');
    carregarListaBackups();
}

function carregarListaBackups() {
    const lista = document.getElementById('listaBackups');
    lista.innerHTML = '<p style="color:#aaa;text-align:center;">Carregando...</p>';

    fetch('src/ajax/listar_backups.php')
        .then(r => r.json())
        .then(backups => {
            if (!backups.length) {
                lista.innerHTML = '<p style="color:#aaa;text-align:center;">Nenhum backup encontrado.</p>';
                return;
            }

            lista.innerHTML = backups.map((b, i) => `
                <div class="backup-item ${i === 0 ? 'backup-item-recente' : ''}">
                    <div class="backup-info">
                        <span class="backup-nome">${b.nome}</span>
                        <span class="backup-meta">${b.data} &bull; ${b.tamanho}</span>
                    </div>
                    <button class="btn-restaurar-item" onclick="confirmarRestauracao('${b.nome}', '${b.data}')">
                        Restaurar
                    </button>
                </div>
            `).join('');
        })
        .catch(() => {
            lista.innerHTML = '<p style="color:#ff6b6b;text-align:center;">Erro ao carregar backups.</p>';
        });
}

function confirmarRestauracao(arquivo, data) {
    document.getElementById('modalRestaurarBackup').classList.remove('show');

    mostrarMensagem('warning',
        `Tem certeza que deseja restaurar o backup de ${data}? Todos os dados atuais serão substituídos.`,
        function() {
            executarRestauracao(arquivo);
        }
    );
}

function executarRestauracao(arquivo) {
    mostrarMensagem('info', '⏳ Restaurando banco de dados, aguarde...');

    const formData = new FormData();
    formData.append('arquivo', arquivo);

    fetch('src/ajax/restaurar_backup.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.sucesso) {
                mostrarMensagem('success', '✅ ' + data.mensagem);
                atualizarTabela();
            } else {
                mostrarMensagem('error', 'Erro: ' + (data.erro || 'Falha ao restaurar.'));
            }
        })
        .catch(() => mostrarMensagem('error', 'Erro ao conectar com o servidor.'));
}
</script>
