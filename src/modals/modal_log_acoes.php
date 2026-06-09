<div id="modalLogAcoes" class="modal">
    <div class="modal-content modal-log-content">
        <span class="close" onclick="document.getElementById('modalLogAcoes').classList.remove('show')">&times;</span>
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; flex-wrap:wrap; gap:0.5rem;">
            <h2 style="margin:0;">📋 Log de Ações</h2>
            <select id="logLimite" onchange="carregarLog()" style="padding:0.3rem 0.6rem; border-radius:6px; border:1px solid #444; background:#2a2a3e; color:#f0f0f0; font-size:0.85rem;">
                <option value="50">Últimas 50</option>
                <option value="100">Últimas 100</option>
                <option value="200">Últimas 200</option>
            </select>
        </div>
        <div id="logTabela" style="overflow-y:auto; max-height:420px;">
            <p style="color:#aaa; text-align:center;">Carregando...</p>
        </div>
    </div>
</div>

<script>
function abrirModalLog() {
    document.getElementById('modalLogAcoes').classList.add('show');
    carregarLog();
}

function carregarLog() {
    const limite = document.getElementById('logLimite').value;
    const container = document.getElementById('logTabela');
    container.innerHTML = '<p style="color:#aaa;text-align:center;">Carregando...</p>';

    fetch('src/ajax/listar_log.php?limite=' + limite)
        .then(r => r.json())
        .then(registros => {
            if (!registros.length) {
                container.innerHTML = '<p style="color:#aaa;text-align:center;">Nenhuma ação registrada ainda.</p>';
                return;
            }

            const corAcao = {
                'Cadastrou': '#7ee8a2',
                'Editou':    '#7ec8e3',
                'Excluiu':   '#ff6b6b',
                'Backup':    '#c3aee8',
                'Restaurou': '#f4c542',
            };

            const linhas = registros.map(r => {
                const cor = corAcao[r.acao] || '#ccc';
                return `<tr>
                    <td style="white-space:nowrap; color:#aaa; font-size:0.78rem;">${r.created_at}</td>
                    <td style="white-space:nowrap; font-weight:bold; color:#f0f0f0;">${r.usuario}</td>
                    <td><span style="background:${cor}22; color:${cor}; padding:2px 8px; border-radius:4px; font-size:0.8rem; font-weight:bold;">${r.acao}</span></td>
                    <td style="font-size:0.82rem; color:#ddd;">${r.descricao}</td>
                </tr>`;
            }).join('');

            container.innerHTML = `
                <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                    <thead>
                        <tr style="background:#2a2a3e; position:sticky; top:0;">
                            <th style="padding:0.5rem 0.75rem; text-align:left; color:#aaa; white-space:nowrap;">Data/Hora</th>
                            <th style="padding:0.5rem 0.75rem; text-align:left; color:#aaa;">Usuário</th>
                            <th style="padding:0.5rem 0.75rem; text-align:left; color:#aaa;">Ação</th>
                            <th style="padding:0.5rem 0.75rem; text-align:left; color:#aaa;">Detalhes</th>
                        </tr>
                    </thead>
                    <tbody>${linhas}</tbody>
                </table>`;
        })
        .catch(() => {
            container.innerHTML = '<p style="color:#ff6b6b;text-align:center;">Erro ao carregar log.</p>';
        });
}
</script>
