<!-- Modal Alterar Senha -->
<div id="modalAlterarSenha" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:1100; align-items:center; justify-content:center;">
    <div style="background:#1e1e2e; border-radius:10px; padding:2rem; width:100%; max-width:420px; position:relative; box-shadow:0 8px 32px rgba(0,0,0,0.5);">

        <!-- Fechar -->
        <button onclick="fecharModalSenha()" style="position:absolute; top:12px; right:14px; background:none; border:none; font-size:1.4rem; color:#aaa; cursor:pointer; line-height:1;"
            onmouseover="this.style.color='#e53935'" onmouseout="this.style.color='#aaa'">&#10005;</button>

        <h3 style="color:#f0f0f0; margin:0 0 1.4rem; font-size:1.1rem;">🔒 Alterar Senha</h3>

        <div id="avisoAlterarSenha" style="display:none; padding:9px 12px; border-radius:6px; font-size:0.87rem; margin-bottom:1rem;"></div>

        <form id="formAlterarSenha" onsubmit="enviarAlterarSenha(event)">
            <div style="margin-bottom:1rem; position:relative;">
                <label style="color:#ccc; font-size:0.85rem; display:block; margin-bottom:4px;">Senha atual</label>
                <input type="password" id="senhaAtual" name="senha_atual" placeholder="Digite sua senha atual"
                    style="width:100%; padding:9px 2.8rem 9px 10px; border-radius:6px; border:1px solid #444; background:#12121e; color:#eee; font-size:0.9rem; box-sizing:border-box;">
                <span onclick="toggleModalSenha('senhaAtual')" class="toggle-modal-senha">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </span>
            </div>
            <div style="margin-bottom:1rem; position:relative;">
                <label style="color:#ccc; font-size:0.85rem; display:block; margin-bottom:4px;">Nova senha</label>
                <input type="password" id="novaSenhaModal" name="nova_senha" placeholder="Mínimo 6 caracteres"
                    style="width:100%; padding:9px 2.8rem 9px 10px; border-radius:6px; border:1px solid #444; background:#12121e; color:#eee; font-size:0.9rem; box-sizing:border-box;">
                <span onclick="toggleModalSenha('novaSenhaModal')" class="toggle-modal-senha">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </span>
            </div>
            <div style="margin-bottom:1.4rem; position:relative;">
                <label style="color:#ccc; font-size:0.85rem; display:block; margin-bottom:4px;">Confirmar nova senha</label>
                <input type="password" id="confirmarSenhaModal" name="confirmar_senha" placeholder="Repita a nova senha"
                    style="width:100%; padding:9px 2.8rem 9px 10px; border-radius:6px; border:1px solid #444; background:#12121e; color:#eee; font-size:0.9rem; box-sizing:border-box;">
                <span onclick="toggleModalSenha('confirmarSenhaModal')" class="toggle-modal-senha">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </span>
            </div>
            <button type="submit" style="width:100%; padding:10px; background:#e53935; color:#fff; border:none; border-radius:6px; font-size:0.95rem; font-weight:600; cursor:pointer;">
                Salvar nova senha
            </button>
        </form>
    </div>
</div>

<style>
.toggle-modal-senha {
    position: absolute;
    right: 10px;
    bottom: 9px;
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.2s;
}
.toggle-modal-senha:hover { opacity: 1; }
</style>

<script>
function abrirModalSenha() {
    document.getElementById('modalAlterarSenha').style.display = 'flex';
    document.getElementById('avisoAlterarSenha').style.display = 'none';
    document.getElementById('formAlterarSenha').reset();
}

function fecharModalSenha() {
    document.getElementById('modalAlterarSenha').style.display = 'none';
}

function toggleModalSenha(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

function enviarAlterarSenha(e) {
    e.preventDefault();
    const atual    = document.getElementById('senhaAtual').value.trim();
    const nova     = document.getElementById('novaSenhaModal').value.trim();
    const confirmar = document.getElementById('confirmarSenhaModal').value.trim();
    const aviso    = document.getElementById('avisoAlterarSenha');

    if (!atual || !nova || !confirmar) {
        mostrarAvisoSenha('error', 'Preencha todos os campos.');
        return;
    }
    if (nova.length < 6) {
        mostrarAvisoSenha('error', 'A nova senha deve ter no mínimo 6 caracteres.');
        return;
    }
    if (nova !== confirmar) {
        mostrarAvisoSenha('error', 'As senhas não coincidem.');
        return;
    }

    const form = new FormData();
    form.append('senha_atual', atual);
    form.append('nova_senha', nova);

    fetch('src/ajax/alterar_senha.php', { method: 'POST', body: form })
        .then(r => r.json())
        .then(d => {
            if (d.ok) {
                mostrarAvisoSenha('success', '✅ Senha alterada com sucesso!');
                document.getElementById('formAlterarSenha').reset();
                setTimeout(fecharModalSenha, 2000);
            } else {
                mostrarAvisoSenha('error', '❌ ' + (d.erro || 'Erro desconhecido.'));
            }
        })
        .catch(() => mostrarAvisoSenha('error', '❌ Erro de comunicação.'));
}

function mostrarAvisoSenha(tipo, msg) {
    const el = document.getElementById('avisoAlterarSenha');
    el.textContent = msg;
    el.style.display = 'block';
    el.style.background = tipo === 'success' ? '#1b3a1b' : '#3b1a1a';
    el.style.border = '1px solid ' + (tipo === 'success' ? '#2e7d32' : '#c62828');
    el.style.color  = tipo === 'success' ? '#81c784' : '#ef9a9a';
}

// Fechar ao clicar fora
document.getElementById('modalAlterarSenha').addEventListener('click', function(e) {
    if (e.target === this) fecharModalSenha();
});
</script>
