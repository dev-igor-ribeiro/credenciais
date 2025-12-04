// =================================================
// FUNÇÕES DE FORMATAÇÃO
// =================================================

function formatarCPF(cpf) {
    cpf = cpf.replace(/\D/g, '');
    return cpf.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, "$1.$2.$3-$4");
}

function formatarData(data) {
    const [ano, mes, dia] = data.split("-");
    return `${dia}/${mes}/${ano}`;
}

function capitalizarNome(nome) {
    return nome.toLowerCase().replace(/(?:^|\s)\S/g, l => l.toUpperCase());
}

function formatarPlaca(placa) {
    return placa.toUpperCase();
}


// =================================================
// MENSAGENS DO SISTEMA
// =================================================

function mostrarMensagem(tipo, texto, callbackConfirmar = null) {
    const modalMensagem = document.getElementById("modalMensagem");
    if (!modalMensagem) {
        alert(texto);
        if (callbackConfirmar && tipo === "warning") {
            callbackConfirmar();
        }
        return;
    }

    modalMensagem.className = "";
    modalMensagem.classList.add("show");
    modalMensagem.classList.remove("hide");
    modalMensagem.classList.add(`modal-${tipo}`);
    modalMensagem.querySelector(".mensagem-texto").textContent = texto;

    const iconeElemento = modalMensagem.querySelector(".modal-icone");
    if (iconeElemento) {
        iconeElemento.textContent =
            tipo === "success" ? "✔️" :
                tipo === "error" ? "❌" :
                    tipo === "warning" ? "⚠️" :
                        tipo === "info" ? "ℹ️" : "";
    }

    const botoesContainer = modalMensagem.querySelector(".modal-botoes");
    botoesContainer.innerHTML = "";

    const btnFechar = modalMensagem.querySelector(".fechar-mensagem");
    btnFechar.focus();

    function fecharMensagem() {
        modalMensagem.classList.add("hide");
        modalMensagem.addEventListener("animationend", function handleAnimationEnd() {
            modalMensagem.classList.remove("show", "hide");
            botoesContainer.innerHTML = "";
            modalMensagem.removeEventListener("animationend", handleAnimationEnd);
        });
    }

    btnFechar.onclick = fecharMensagem;

    if (tipo === "warning" && callbackConfirmar) {
        const btnConfirmar = document.createElement("button");
        btnConfirmar.textContent = "Confirmar";
        btnConfirmar.classList.add("btn-confirmar");

        const btnCancelar = document.createElement("button");
        btnCancelar.textContent = "Cancelar";
        btnCancelar.classList.add("btn-cancelar");

        botoesContainer.appendChild(btnConfirmar);
        botoesContainer.appendChild(btnCancelar);

        btnConfirmar.focus();

        btnConfirmar.onclick = () => {
            fecharMensagem();
            callbackConfirmar();
        };
        btnCancelar.onclick = fecharMensagem;
        btnFechar.onclick = fecharMensagem;

    } else if (tipo !== "warning") {
        setTimeout(() => {
            fecharMensagem();
        }, 3000);
    }
}


// =================================================
// MODAL DE DOCUMENTOS / OBSERVAÇÕES
// =================================================

// ABRIR MODAL
function abrirModal(id) {
    document.getElementById("motorista_id_input").value = id;
    document.getElementById("modalDocumentos").style.display = "flex";

    carregarDocumentos(id);
    carregarObservacao(id);
}

// FECHAR MODAL
function fecharModal() {
    document.getElementById("modalDocumentos").style.display = "none";
}

// CARREGAR DOCUMENTOS
function carregarDocumentos(id) {
    fetch("src/listar_documentos.php?motorista_id=" + id + "&nocache=" + Date.now(), {
        cache: "no-store"
    })
        .then(r => r.text())
        .then(html => {
            document.getElementById("listaDocumentos").innerHTML = html;
        });
}

// CARREGAR OBSERVAÇÃO
function carregarObservacao(id) {
    fetch("src/get_observacao.php?motorista_id=" + id + "&nocache=" + Date.now(), {
        cache: "no-store"
    })
        .then(r => r.text())
        .then(text => {
            document.getElementById("obsMotorista").value = text;
        });
}

// SALVAR OBSERVAÇÃO
document.getElementById("btnSalvarObs").addEventListener("click", function () {
    const id = document.getElementById("motorista_id_input").value;
    const obs = document.getElementById("obsMotorista").value;

    fetch("src/salvar_observacao.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "motorista_id=" + id + "&observacao=" + encodeURIComponent(obs)
    })
        .then(r => r.text())
        .then(() => {
            alert("Observação salva!");
            carregarObservacao(id);
        });
});

// UPLOAD DOCUMENTO
document.getElementById("formUploadDocs").addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch("src/upload_documento.php", {
        method: "POST",
        body: formData
    })
        .then(r => r.text())
        .then(() => {
            const id = document.getElementById("motorista_id_input").value;
            carregarDocumentos(id);
            alert("Arquivo enviado!");
        });
});


// =================================================
// CARREGAR / ATUALIZAR TABELA DE MOTORISTAS
// =================================================

function atualizarTabela() {
    fetch("src/ajax/carregar_motoristas.php?ts=" + Date.now(), { cache: "no-store" })
        .then(response => response.json())
        .then(data => {

            const tabela = document.getElementById("tabelaCorpo");
            const totalEl = document.getElementById("totalMotoristas");
            const validosEl = document.getElementById("validos");
            const aVencerEl = document.getElementById("aVencer");
            const vencidosEl = document.getElementById("vencidos");

            const filtroNomeInput = document.getElementById("filtroNome");
            const filtroStatusSelect = document.getElementById("filtroStatus");

            let filtroNome = filtroNomeInput ? filtroNomeInput.value.trim().toLowerCase() : "";
            let filtroStatus = filtroStatusSelect ? filtroStatusSelect.value : "Todos";

            let total = 0, validos = 0, aVencer = 0, vencidos = 0;
            tabela.innerHTML = "";

            data.forEach(motorista => {

                const dias = parseInt(motorista.dias_restante);

                let statusLabel = "";
                let statusClass = "";

                if (dias > 30) {
                    statusLabel = "Válido";
                    statusClass = "status-valido";
                } else if (dias > 0) {
                    statusLabel = "A Vencer";
                    statusClass = "status-a-vencer";
                } else {
                    statusLabel = "Vencido";
                    statusClass = "status-vencido";
                }

                // FILTROS
                if (filtroNome && !motorista.nome.toLowerCase().includes(filtroNome)) return;
                if (filtroStatus !== "Todos" && statusLabel !== filtroStatus) return;

                total++;
                if (statusLabel === "Válido") validos++;
                else if (statusLabel === "A Vencer") aVencer++;
                else if (statusLabel === "Vencido") vencidos++;

                // LINHA
                const row = `
<tr>
    <td><input type="checkbox" class="select-motorista" value="${motorista.id}"></td>
    <td>${motorista.credencial}</td>
    <td>
        <a href="#" class="abrirModalMotorista" data-id="${motorista.id}">
            ${capitalizarNome(motorista.nome)}
        </a>
    </td>
    <td>${motorista.cnh}</td>
    <td class="cpf">${formatarCPF(motorista.cpf)}</td>
    <td>${motorista.modelo}</td>
    <td class="ano ${motorista.ano_vermelho ? "ano-vermelho" : ""}">${motorista.ano}</td>
    <td class="placa">${formatarPlaca(motorista.placa)}</td>
    <td>${motorista.validade}</td>
    <td class="status"><span class="status-badge ${statusClass}">${statusLabel}</span></td>
    <td class="dias">${dias}</td>
    <td class="action-icons">
        <img src="assets/icons/edit.svg" alt="Editar" class="icon-action edit-icon">
        <img src="assets/icons/trash-2.svg" alt="Excluir" class="icon-action delete-icon">
    </td>
</tr>
`;

                tabela.insertAdjacentHTML("beforeend", row);

                // CLIQUE NO NOME = ABRIR MODAL DOCUMENTOS
                const linkNome = tabela.lastElementChild.querySelector(".abrirModalMotorista");
                if (linkNome) {
                    linkNome.addEventListener("click", function (e) {
                        e.preventDefault();
                        abrirModal(this.getAttribute("data-id"));
                    });
                }

                // EDITAR / EXCLUIR (mantido igual)
                const ultimaLinha = tabela.lastElementChild;
                const btnEditar = ultimaLinha.querySelector(".edit-icon");
                const btnExcluir = ultimaLinha.querySelector(".delete-icon");

                btnEditar.addEventListener("click", () => {
                    const modal = document.getElementById("modalEditarMotorista");
                    modal.classList.add("show");
                    document.getElementById("editarId").value = motorista.id;
                    document.getElementById("editarNome").value = motorista.nome;
                    document.getElementById("editarCnh").value = motorista.cnh;
                    document.getElementById("editarCpf").value = motorista.cpf;
                    document.getElementById("editarValidade").value = motorista.validade;
                    document.getElementById("editarModelo").value = motorista.modelo;
                    document.getElementById("editarAno").value = motorista.ano;
                    document.getElementById("editarPlaca").value = motorista.placa;
                    document.getElementById("editarCredencial").value = motorista.credencial;
                });

                btnExcluir.addEventListener("click", () => {
                    function confirmarExclusao() {
                        fetch("src/processar/excluir_motorista.php?id=" + encodeURIComponent(motorista.id), { cache: "no-store" })
                            .then(r => r.text())
                            .then(data => {
                                if (data.toLowerCase().includes("sucesso")) {
                                    mostrarMensagem("success", "Motorista excluído com sucesso!");
                                    atualizarTabela();
                                } else {
                                    mostrarMensagem("error", "Erro ao excluir: " + data);
                                }
                            })
                            .catch(() => {
                                mostrarMensagem("error", "Erro ao excluir motorista.");
                            });
                    }
                    mostrarMensagem("warning", `Tem certeza que deseja excluir ${motorista.nome}?`, confirmarExclusao);
                });

            });

            totalEl.textContent = total;
            validosEl.textContent = validos;
            aVencerEl.textContent = aVencer;
            vencidosEl.textContent = vencidos;
        })
        .catch(error => console.error("Erro ao carregar motoristas:", error));
}


// =================================================
// INICIALIZAÇÃO
// =================================================

document.addEventListener("DOMContentLoaded", function () {

    atualizarTabela();

    const filtroNomeInput = document.getElementById("filtroNome");
    if (filtroNomeInput) {
        filtroNomeInput.addEventListener("input", atualizarTabela);
    }

    const filtroStatusSelect = document.getElementById("filtroStatus");
    if (filtroStatusSelect) {
        filtroStatusSelect.addEventListener("change", atualizarTabela);
    }

    // FORM EDITAR MOTORISTA
    const formEditar = document.getElementById("formEditarMotorista");
    if (formEditar) {
        formEditar.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(formEditar);

            fetch("src/processar/atualizar_motorista.php", {
                method: "POST",
                body: formData
            })
                .then(r => r.text())
                .then(data => {
                    if (data.trim() === "sucesso") {
                        mostrarMensagem("success", "Motorista atualizado com sucesso!");
                        document.getElementById("modalEditarMotorista").classList.remove("show");
                        atualizarTabela();
                    } else {
                        mostrarMensagem("error", "Erro ao atualizar motorista.");
                    }
                });
        });
    }

    // FORM NOVO MOTORISTA
    const formNovo = document.getElementById("formNovoMotorista");
    if (formNovo) {
        formNovo.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(formNovo);

            fetch("src/processar/processa_novo_motorista.php", {
                method: "POST",
                body: formData
            })
                .then(r => r.text())
                .then(data => {
                    if (data.toLowerCase().includes("sucesso")) {
                        mostrarMensagem("success", "Motorista cadastrado!");
                        document.getElementById("modalNovoMotorista").classList.remove("show");
                        formNovo.reset();
                        atualizarTabela();
                    } else {
                        mostrarMensagem("error", "Erro ao cadastrar motorista.");
                    }
                });
        });
    }

    // IMPORTAR EXCEL
    const btnImportar = document.getElementById("btnImportar");
    const inputImportar = document.getElementById("inputImportar");

    if (btnImportar && inputImportar) {
        btnImportar.addEventListener("click", () => inputImportar.click());

        inputImportar.addEventListener("change", () => {
            if (inputImportar.files.length === 0) return;

            const arquivo = inputImportar.files[0];
            const formData = new FormData();
            formData.append("arquivo", arquivo);

            fetch("src/processar/importar_motoristas.php", {
                method: "POST",
                body: formData
            })
                .then(r => r.text())
                .then(data => {
                    if (data.toLowerCase().includes("sucesso")) {
                        mostrarMensagem("success", "Importação concluída!");
                        atualizarTabela();
                    } else {
                        mostrarMensagem("error", "Erro ao importar arquivo.");
                    }
                })
                .finally(() => {
                    inputImportar.value = "";
                });
        });
    }

    // EXCLUSÃO EM MASSA
    const btnExcluirSelecionados = document.getElementById("btnExcluirSelecionados");
    if (btnExcluirSelecionados) {
        btnExcluirSelecionados.addEventListener("click", () => {

            const ids = Array.from(document.querySelectorAll(".select-motorista:checked"))
                .map(cb => cb.value);

            if (ids.length === 0) {
                mostrarMensagem("info", "Nenhum motorista selecionado.");
                return;
            }

            function confirmarExclusao() {
                fetch("src/processar/excluir_motorista.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ ids })
                })
                    .then(r => r.text())
                    .then(data => {
                        if (data.toLowerCase().includes("sucesso")) {
                            mostrarMensagem("success", "Exclusão concluída!");
                            atualizarTabela();
                        } else {
                            mostrarMensagem("error", "Erro ao excluir motoristas.");
                        }
                    });
            }

            mostrarMensagem("warning", "Deseja excluir os motoristas selecionados?", confirmarExclusao);
        });
    }

    // SELECIONAR TODOS
    const selectAll = document.getElementById("select-all");
    if (selectAll) {
        selectAll.addEventListener("change", function () {
            const checkboxes = document.querySelectorAll(".select-motorista");
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }

});
