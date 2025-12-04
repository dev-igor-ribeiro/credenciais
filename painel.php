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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        integrity="sha512-dyZ/J+6oX1kX2Is0kB5tscJq1hfwmiy2Bu5QY3xGO7t3/M5cO9YosYukw1VPR4tOHEEWoei6u5KxCW2o4rBPyQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="assets/js/script.js" defer></script>
    <script src="assets/js/script_novo_motorista.js" defer></script>
    <script src="assets/js/painel.js" defer></script>
    <script>
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
        <a href="login/logout.php" class="logout-btn">
            <span>⎋</span> Sair
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
        </section>
        <section class="filtros">
            <input type="text" id="filtroNome" placeholder="Buscar por nome...">
            <select id="filtroStatus">
                <option value="Todos">Todos</option>
                <option value="Válido">Válido</option>
                <option value="A Vencer">A Vencer</option>
                <option value="Vencido">Vencido</option>
            </select>
            <button id="btnExcluirSelecionados" class="btn btn-danger">Excluir Selecionados</button>
            <button id="btnExportar"
                onclick="window.location.href='src/exportar/exportar_motoristas.php'">Exportar</button>
            <button id="btnImportar" class="btn btn-secondary">Importar</button>
            <input type="file" id="inputImportar" accept=".xlsx,.xls" style="display:none;">
            <button id="btnNovoMotorista" onclick="document.getElementById('modalNovoMotorista').classList.add('show')">
                Novo Motorista
            </button>
            <button id="btnNovaCredencial" onclick="document.getElementById('modalCredencial').classList.add('show')">
                Nova Credencial
            </button>
        </section>
        <section class="tabela-motoristas">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th class="col-select"><input type="checkbox" id="select-all"></th>
                            <th class="col-credencial">Credencial</th>
                            <th class="col-nome">Nome</th>
                            <th class="col-cnh">CNH</th>
                            <th class="col-cpf">CPF</th>
                            <th class="col-modelo">Modelo</th>
                            <th class="col-ano">Ano</th>
                            <th class="col-placa">Placa</th>
                            <th class="col-validade">Data Validade</th>
                            <th class="col-status">Status</th>
                            <th class="col-dias">Dias</th>
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

    <!-- MODAL BORA CAR – DOCUMENTOS DO MOTORISTA -->
    <div id="modalDocumentos" class="modal-overlay-bc" style="display:none;">
        <div class="modal-content-bc animate-modal">

            <div class="modal-header-bc">
                <h2 id="nomeMotoristaTitulo">Documentos do Motorista</h2>
                <button class="close-bc" onclick="fecharModal()">×</button>
            </div>

            <div class="modal-body-bc">

                <div class="upload-box-bc">
                    <label><b>Adicionar arquivos</b></label>
                    <form id="formUploadDocs" enctype="multipart/form-data">
                        <input type="hidden" name="motorista_id" id="motorista_id_input">

                        <input type="file" name="arquivo" class="input-file-bc" required>

                        <button type="submit" class="btn-bc-green">
                            Enviar Arquivo
                        </button>
                    </form>
                </div>

                <hr class="divisor-bc">

                <h3 class="title-section">📎 Arquivos enviados</h3>
                <div id="listaDocumentos" class="lista-docs-bc">Carregando...</div>

                <hr class="divisor-bc">

                <h3 class="title-section">📝 Observações</h3>
                <textarea id="obsMotorista" rows="4" class="textarea-bc" placeholder="Digite observações..."></textarea>

                <button id="btnSalvarObs" class="btn-bc-blue">Salvar Observação</button>
            </div>

        </div>
    </div>

</body>

</html>