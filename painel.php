<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Credenciais - BoraCar</title>
    <link rel="stylesheet" href="assets/css/painel.css">
    <link rel="stylesheet" href="assets/css/modal_motorista.css">
    <link rel="stylesheet" href="assets/css/modal_perfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        integrity="sha512-dyZ/J+6oX1kX2Is0kB5tscJq1hfwmiy2Bu5QY3xGO7t3/M5cO9YosYukw1VPR4tOHEEWoei6u5KxCW2o4rBPyQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="assets/js/script.js" defer></script>
    <script src="assets/js/script_novo_motorista.js" defer></script>
    <script src="assets/js/painel.js" defer></script>
    <script>
    function fazerBackup() {
        const btn = document.getElementById('btnBackup');
        btn.disabled = true;
        btn.textContent = 'Aguarde...';
        fetch('src/ajax/executar_backup.php')
            .then(r => r.json())
            .then(data => {
                if (data.sucesso) {
                    mostrarMensagem('success', '✅ ' + data.mensagem);
                } else {
                    mostrarMensagem('error', 'Erro: ' + (data.erro || 'Falha no backup'));
                }
            })
            .catch(() => mostrarMensagem('error', 'Erro ao executar backup.'))
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Backup';
            });
    }

    function toggleFerramentas(e) {
        e.stopPropagation();
        document.getElementById('ferramMenu').classList.toggle('aberto');
    }
    function fecharFerramentas() {
        document.getElementById('ferramMenu').classList.remove('aberto');
    }
    document.addEventListener('click', function(e) {
        if (!document.getElementById('ferramWrap').contains(e.target)) {
            fecharFerramentas();
        }
    });

    function exportarFiltrado() {
        const nome    = document.getElementById('filtroNome')?.value.trim() || '';
        const status  = document.getElementById('filtroStatus')?.value || '';
        const dataDe  = document.getElementById('filtroDataDe')?.value || '';
        const dataAte = document.getElementById('filtroDataAte')?.value || '';

        const params = new URLSearchParams();
        if (nome)    params.set('nome',    nome);
        if (status && status !== 'Todos') params.set('status', status);
        if (dataDe)  params.set('data_de',  dataDe);
        if (dataAte) params.set('data_ate', dataAte);

        window.location.href = 'src/exportar/exportar_motoristas.php?' + params.toString();
    }

    function confirmarSaida() {
        mostrarMensagem('warning', 'Deseja realmente sair do sistema?', function() {
            window.location.href = 'login/logout.php';
        });
    }

    function confirmarExclusao(id) {
        if (confirm("Tem certeza que deseja excluir este motorista?")) {
            window.location.href = `src/processar/excluir_motorista.php?id=${id}`;
        }
    }
    </script>
</head>

<body>
    <header class="top-header">
        <h1>Gerenciamento de Motoristas</h1>
        <p>Sistema de controle de credenciais</p>
        <a href="#" class="logout-btn" title="Sair" onclick="confirmarSaida(); return false;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="white" width="22" height="22">
                <path d="M288 32c0-17.7-14.3-32-32-32s-32 14.3-32 32V256c0 17.7 14.3 32 32 32s32-14.3 32-32V32zM143.5 120.6c13.6-11.3 15.4-31.5 4.1-45.1s-31.5-15.4-45.1-4.1C49.7 115.4 16 181.8 16 256c0 132.5 107.5 240 240 240s240-107.5 240-240c0-74.2-33.8-140.6-86.6-184.6c-13.6-11.3-33.8-9.4-45.1 4.1s-9.4 33.8 4.1 45.1C407.4 160.5 432 205.8 432 256c0 97.2-78.8 176-176 176S80 353.2 80 256c0-50.2 24.6-95.5 63.5-135.4z"/>
            </svg>
        </a>
    </header>
    <main class="dashboard">
        <section class="cards">
            <div class="card total">
                <h2 id="totalMotoristas">0</h2>
                <p>Total</p>
            </div>
            <div class="card validos">
                <h2 id="validos">0</h2>
                <p>Válidos</p>
            </div>
            <div class="card a-vencer">
                <h2 id="aVencer">0</h2>
                <p>A Vencer (30 dias)</p>
            </div>
            <div class="card vencidos">
                <h2 id="vencidos">0</h2>
                <p>Vencidos</p>
            </div>
            <div class="card suspensos">
                <h2 id="suspensos">0</h2>
                <p>Suspensos</p>
            </div>
            <div class="card pendentes">
                <h2 id="pendentes">0</h2>
                <p>Pendentes</p>
            </div>
        </section>
        <div id="alertaVencimentos"></div>
        <section class="filtros">
            <div class="filtros-busca">
                <div class="busca-wrap">
                    <input type="text" id="filtroNome" placeholder="Buscar por nome, credencial, CPF, modelo ou placa...">
                    <span id="btnLimparBusca" title="Limpar busca">✕</span>
                </div>
                <select id="filtroStatus">
                    <option value="Todos">Todos</option>
                    <option value="Válido">Válido</option>
                    <option value="A Vencer">A Vencer</option>
                    <option value="Vencido">Vencido</option>
                    <option value="Suspenso">Suspenso</option>
                    <option value="Pendente">Pendente</option>
                </select>
            </div>
            <div class="filtros-data">
                <label>Validade de:</label>
                <input type="date" id="filtroDataDe">
                <label>até:</label>
                <input type="date" id="filtroDataAte">
                <button id="btnLimparDatas" title="Limpar datas">✕ Limpar datas</button>
            </div>
            <div class="filtros-acoes">
                <button id="btnExcluirSelecionados">Excluir Selecionados</button>
                <button id="btnExportar" onclick="exportarFiltrado()">Exportar</button>
                <button id="btnImportar">Importar</button>
                <input type="file" id="inputImportar" accept=".xlsx,.xls" style="display:none;">
                <button id="btnNovoMotorista" onclick="document.getElementById('modalNovoMotorista').classList.add('show')">+ Novo Motorista</button>

                <!-- Dropdown Ferramentas -->
                <div class="ferramentas-wrap" id="ferramWrap">
                    <button class="btn-ferramentas" id="btnFerramentas" onclick="toggleFerramentas(event)">
                        ⚙️ Ferramentas ▾
                    </button>
                    <div class="ferramentas-menu" id="ferramMenu">
                        <button onclick="fazerBackup(); fecharFerramentas()">💾 Backup</button>
                        <button onclick="abrirModalRestaurar(); fecharFerramentas()">🔄 Restaurar</button>
                        <button onclick="abrirModalLog(); fecharFerramentas()">📋 Log de Ações</button>
                        <button onclick="abrirModalSenha(); fecharFerramentas()">🔒 Alterar Senha</button>
                    </div>
                </div>
            </div>
        </section>
        <section class="tabela-motoristas">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th class="col-select"><input type="checkbox" id="select-all"></th>
                            <th class="col-credencial sortable" data-sort="credencial">Credencial</th>
                            <th class="col-nome sortable" data-sort="nome">Nome</th>
                            <th class="col-cnh sortable" data-sort="cnh">CNH</th>
                            <th class="col-cpf sortable" data-sort="cpf">CPF</th>
                            <th class="col-modelo sortable" data-sort="modelo">Modelo</th>
                            <th class="col-ano sortable" data-sort="ano">Ano</th>
                            <th class="col-placa sortable" data-sort="placa">Placa</th>
                            <th class="col-validade sortable" data-sort="validade">Data Validade</th>
                            <th class="col-status sortable" data-sort="status">Status</th>
                            <th class="col-dias sortable" data-sort="dias">Dias</th>
                            <th class="col-acoes">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaCorpo">
                        <!-- Os dados serão preenchidos dinamicamente via script.js -->
                        <!-- Cada linha deve conter ícones de ação com classes e tooltips -->
                        <!-- Exemplo de célula de ações: -->
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <?php include('src/modals/modal_alterar_senha.php'); ?>
    <?php include('src/modals/modal_restaurar_backup.php'); ?>
    <?php include('src/modals/modal_log_acoes.php'); ?>
    <?php include('src/modals/modal_perfil_motorista.php'); ?>
    <?php include('src/modals/modal_novo_motorista.php'); ?>
    <?php include('src/modals/modal_editar_motorista.php'); ?>
    <?php include('src/modals/modal_credencial.php'); ?>
    <div id="modalMensagem" class="modal">
        <div class="modal-content">
            <span class="close fechar-mensagem">&times;</span>
            <div class="modal-icone"></div>
            <p class="mensagem-texto"></p>
            <div class="modal-botoes"></div>
        </div>
    </div>
    <?php if (isset($_GET['sucesso'])): ?>
    <script>
    mostrarMensagem("success", "Motorista cadastrado com sucesso!");
    </script>
    <?php endif; ?>
    <?php if (isset($_GET['erro'])): ?>
    <script>
    mostrarMensagem("error", "Erro ao cadastrar motorista.");
    </script>
    <?php endif; ?>
</body>

</html>