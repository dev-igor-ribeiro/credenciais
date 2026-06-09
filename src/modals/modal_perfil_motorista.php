<div id="modalPerfil" class="modal">
    <div class="modal-content modal-perfil-content">
        <span onclick="fecharModalPerfil()" style="position:absolute; top:12px; right:18px; font-size:1.5rem; font-weight:bold; color:#aaa; cursor:pointer; line-height:1;" onmouseover="this.style.color='#e63946'" onmouseout="this.style.color='#aaa'">&times;</span>
        <h2 id="perfilNome">Perfil do Motorista</h2>

        <div class="perfil-tabs">
            <button class="perfil-tab ativo" onclick="trocarAba('info', this)">Informações</button>
            <button class="perfil-tab" onclick="trocarAba('docs', this)">Documentos</button>
            <button class="perfil-tab" onclick="trocarAba('hist', this)">Histórico</button>
            <button class="perfil-tab" onclick="trocarAba('edicoes', this)">Edições</button>
        </div>

        <!-- ABA INFORMAÇÕES -->
        <div id="abaInfo" class="perfil-aba ativo">
            <table class="perfil-tabela">
                <tr><td>Credencial</td><td id="pCredencial"></td></tr>
                <tr><td>CPF</td><td id="pCpf"></td></tr>
                <tr><td>CNH</td><td id="pCnh"></td></tr>
                <tr><td>Modelo</td><td id="pModelo"></td></tr>
                <tr><td>Ano</td><td id="pAno"></td></tr>
                <tr><td>Placa</td><td id="pPlaca"></td></tr>
                <tr><td>Validade</td><td id="pValidade"></td></tr>
                <tr><td>Status</td><td id="pStatus"></td></tr>
                <tr><td>Cadastrado em</td><td id="pCriadoEm"></td></tr>
            </table>
        </div>

        <!-- ABA DOCUMENTOS -->
        <div id="abaDocs" class="perfil-aba">
            <div class="upload-area">
                <label for="inputDocumento" class="btn-upload">+ Adicionar PDF</label>
                <input type="file" id="inputDocumento" accept=".pdf" style="display:none;" onchange="uploadDocumento(this)">
                <span id="uploadStatus"></span>
            </div>
            <ul id="listaDocumentos" class="lista-docs"></ul>
        </div>

        <!-- ABA EDIÇÕES -->
        <div id="abaEdicoes" class="perfil-aba">
            <div id="listaEdicoes" style="max-height:360px; overflow-y:auto;">
                <p style="color:#aaa; text-align:center; padding:1.5rem; font-style:italic;">Carregando...</p>
            </div>
        </div>

        <!-- ABA HISTÓRICO -->
        <div id="abaHist" class="perfil-aba">
            <div class="historico-form">
                <textarea id="novaObservacao" placeholder="Nova observação..." rows="3"></textarea>
                <button class="btn-salvar-obs" onclick="salvarObservacao()">Salvar</button>
            </div>
            <ul id="listaHistorico" class="lista-historico"></ul>
        </div>
    </div>
</div>

<script>
let _perfilMotoristaId = null;

function abrirModalPerfil(id) {
    _perfilMotoristaId = id;
    document.getElementById('modalPerfil').classList.add('show');
    trocarAba('info', document.querySelector('.perfil-tab'));

    fetch('src/ajax/perfil_motorista.php?id=' + id)
        .then(r => r.json())
        .then(data => {
            const m = data.motorista;
            const nome = m.nome.toLowerCase().replace(/(?:^|\s)\S/g, l => l.toUpperCase());
            document.getElementById('perfilNome').textContent = nome;
            document.getElementById('pCredencial').textContent = m.credencial || '-';
            document.getElementById('pCpf').textContent = m.cpf || '-';
            document.getElementById('pCnh').textContent = m.cnh || '-';
            document.getElementById('pModelo').textContent = m.modelo || '-';
            document.getElementById('pAno').textContent = m.ano || '-';
            document.getElementById('pPlaca').textContent = m.placa || '-';
            document.getElementById('pValidade').textContent = m.validade ? formatarDataPerfil(m.validade) : '-';
            document.getElementById('pStatus').textContent = m.status || '-';
            document.getElementById('pCriadoEm').textContent = m.criado_em
                ? new Date(m.criado_em).toLocaleString('pt-BR', {day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'})
                : '-';

            // Documentos
            const listaDocs = document.getElementById('listaDocumentos');
            listaDocs.innerHTML = '';
            if (data.documentos.length === 0) {
                listaDocs.innerHTML = '<li class="vazio">Nenhum documento anexado.</li>';
            } else {
                data.documentos.forEach(doc => adicionarItemDoc(doc));
            }

            // Histórico
            const listaHist = document.getElementById('listaHistorico');
            listaHist.innerHTML = '';
            if (data.historico.length === 0) {
                listaHist.innerHTML = '<li class="vazio">Nenhuma observação registrada.</li>';
            } else {
                data.historico.forEach(h => adicionarItemHist(h.criado_em, h.observacao));
            }
        });
}

function fecharModalPerfil() {
    document.getElementById('modalPerfil').classList.remove('show');
    _perfilMotoristaId = null;
}

function trocarAba(aba, btn) {
    document.querySelectorAll('.perfil-tab').forEach(b => b.classList.remove('ativo'));
    document.querySelectorAll('.perfil-aba').forEach(a => a.classList.remove('ativo'));
    const mapa = { info: 'abaInfo', docs: 'abaDocs', hist: 'abaHist', edicoes: 'abaEdicoes' };
    document.getElementById(mapa[aba]).classList.add('ativo');
    btn.classList.add('ativo');
    if (aba === 'edicoes') carregarEdicoes(_perfilMotoristaId);
}

function carregarEdicoes(id) {
    const wrap = document.getElementById('listaEdicoes');
    wrap.innerHTML = '<p style="color:#aaa;text-align:center;padding:1.5rem;font-style:italic;">Carregando...</p>';

    fetch('src/ajax/listar_edicoes.php?id=' + id)
        .then(r => r.json())
        .then(rows => {
            if (!rows.length) {
                wrap.innerHTML = '<p style="color:#aaa;text-align:center;padding:1.5rem;font-style:italic;">Nenhuma edição registrada.</p>';
                return;
            }

            // Agrupa por data/hora (edições feitas no mesmo momento)
            const grupos = [];
            let grupoAtual = null;
            rows.forEach(r => {
                const chave = r.editado_em + '|' + r.usuario;
                if (!grupoAtual || grupoAtual.chave !== chave) {
                    grupoAtual = { chave, editado_em: r.editado_em, usuario: r.usuario, campos: [] };
                    grupos.push(grupoAtual);
                }
                grupoAtual.campos.push(r);
            });

            wrap.innerHTML = grupos.map(g => {
                const dt = new Date(g.editado_em);
                const data = dt.toLocaleDateString('pt-BR') + ' ' + dt.toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'});
                const linhas = g.campos.map(c => `
                    <div class="edicao-campo">
                        <span class="edicao-label">${c.campo}</span>
                        <span class="edicao-antes">${c.valor_anterior || '—'}</span>
                        <span class="edicao-seta">→</span>
                        <span class="edicao-depois">${c.valor_novo || '—'}</span>
                    </div>`).join('');
                return `
                    <div class="edicao-grupo">
                        <div class="edicao-header">
                            <span class="edicao-data">🕐 ${data}</span>
                            <span class="edicao-usuario">por ${g.usuario}</span>
                        </div>
                        ${linhas}
                    </div>`;
            }).join('');
        })
        .catch(() => {
            wrap.innerHTML = '<p style="color:#e53935;text-align:center;padding:1rem;">Erro ao carregar edições.</p>';
        });
}

function formatarDataPerfil(data) {
    const [ano, mes, dia] = data.split('-');
    return `${dia}/${mes}/${ano}`;
}

function adicionarItemDoc(nome) {
    const lista = document.getElementById('listaDocumentos');
    const vazio = lista.querySelector('.vazio');
    if (vazio) vazio.remove();

    const li = document.createElement('li');
    li.className = 'doc-item';
    li.innerHTML = `
        <span class="doc-nome">📄 ${nome}</span>
        <div class="doc-acoes">
            <a href="uploads/motoristas/${_perfilMotoristaId}/${encodeURIComponent(nome)}" target="_blank" class="btn-ver">Visualizar</a>
            <button class="btn-excluir-doc" onclick="excluirDocumento('${nome}', this)">Excluir</button>
        </div>`;
    lista.appendChild(li);
}

function adicionarItemHist(data, texto) {
    const lista = document.getElementById('listaHistorico');
    const vazio = lista.querySelector('.vazio');
    if (vazio) vazio.remove();

    // Formata data se vier como yyyy-mm-dd hh:mm:ss
    let dataFormatada = data;
    if (data.includes('-') && data.includes(':')) {
        const d = new Date(data);
        dataFormatada = d.toLocaleDateString('pt-BR') + ' ' + d.toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'});
    }

    const li = document.createElement('li');
    li.className = 'hist-item';
    li.innerHTML = `<span class="hist-data">${dataFormatada}</span><p class="hist-texto">${texto}</p>`;
    lista.prepend(li);
}

function uploadDocumento(input) {
    const arquivo = input.files[0];
    if (!arquivo) return;
    const status = document.getElementById('uploadStatus');
    status.textContent = 'Enviando...';

    const form = new FormData();
    form.append('motorista_id', _perfilMotoristaId);
    form.append('documento', arquivo);

    fetch('src/ajax/upload_documento.php', { method: 'POST', body: form })
        .then(r => r.json())
        .then(data => {
            if (data.sucesso) {
                adicionarItemDoc(data.nome);
                status.textContent = '✔ Enviado';
            } else {
                status.textContent = '✖ ' + (data.erro || 'Erro');
            }
            input.value = '';
            setTimeout(() => status.textContent = '', 3000);
        });
}

function excluirDocumento(nome, btn) {
    mostrarMensagem('warning', 'Deseja excluir o arquivo "' + nome + '"?', function() {
        const form = new FormData();
        form.append('motorista_id', _perfilMotoristaId);
        form.append('nome', nome);

        fetch('src/ajax/excluir_documento.php', { method: 'POST', body: form })
            .then(r => r.json())
            .then(data => {
                if (data.sucesso) {
                    btn.closest('li').remove();
                    const lista = document.getElementById('listaDocumentos');
                    if (!lista.querySelector('li:not(.vazio)')) {
                        lista.innerHTML = '<li class="vazio">Nenhum documento anexado.</li>';
                    }
                    mostrarMensagem('success', 'Documento excluído com sucesso!');
                }
            });
    });
}

function salvarObservacao() {
    const textarea = document.getElementById('novaObservacao');
    const texto = textarea.value.trim();
    if (!texto) return;

    const form = new FormData();
    form.append('motorista_id', _perfilMotoristaId);
    form.append('observacao', texto);

    fetch('src/ajax/salvar_historico.php', { method: 'POST', body: form })
        .then(r => r.json())
        .then(data => {
            if (data.sucesso) {
                adicionarItemHist(data.criado_em, texto);
                textarea.value = '';
            }
        });
}
</script>
