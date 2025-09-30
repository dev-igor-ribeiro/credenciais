// Funções de formatação
function formatarCPF(cpf) {
    cpf = cpf.replace(/\D/g, ''); // Remove tudo que não for número
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
        if (tipo === "success") {
            iconeElemento.textContent = "✔️";
        } else if (tipo === "error") {
            iconeElemento.textContent = "❌";
        } else if (tipo === "warning") {
            iconeElemento.textContent = "⚠️";
        } else if (tipo === "info") {
            iconeElemento.textContent = "ℹ️";
        } else {
            iconeElemento.textContent = "";
        }
    }

    const botoesContainer = modalMensagem.querySelector(".modal-botoes");
    botoesContainer.innerHTML = ""; // Limpa os botões existentes

    const btnFechar = modalMensagem.querySelector(".fechar-mensagem");
    btnFechar.focus();

    function fecharModal() {
        modalMensagem.classList.add("hide");
        modalMensagem.addEventListener("animationend", function handleAnimationEnd() {
            modalMensagem.classList.remove("show", "hide");
            botoesContainer.innerHTML = "";
            modalMensagem.removeEventListener("animationend", handleAnimationEnd);
        });
    }

    btnFechar.onclick = function () {
        fecharModal();
    };

    if (tipo === "warning" && callbackConfirmar) {
        const btnConfirmar = document.createElement("button");
        btnConfirmar.textContent = "Confirmar";
        btnConfirmar.classList.add("btn-confirmar");
        botoesContainer.appendChild(btnConfirmar);

        const btnCancelar = document.createElement("button");
        btnCancelar.textContent = "Cancelar";
        btnCancelar.classList.add("btn-cancelar");
        botoesContainer.appendChild(btnCancelar);

        btnConfirmar.focus();

        function limparEventos() {
            btnConfirmar.removeEventListener("click", onConfirmar);
            btnCancelar.removeEventListener("click", onCancelar);
            btnFechar.removeEventListener("click", onCancelar);
        }

        function onConfirmar() {
            limparEventos();
            fecharModal();
            callbackConfirmar();
        }

        function onCancelar() {
            limparEventos();
            fecharModal();
        }

        btnConfirmar.addEventListener("click", onConfirmar);
        btnCancelar.addEventListener("click", onCancelar);
        btnFechar.addEventListener("click", onCancelar);

    } else {
        // Para mensagens do tipo success e error, manter o modal aberto até usuário fechar
        if (tipo !== "warning") {
            setTimeout(() => {
                fecharModal();
            }, 3000);
        }
    }
}

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

                // Filtrar pelo nome
                if (filtroNome && !motorista.nome.toLowerCase().includes(filtroNome)) {
                    return; // Pula este motorista
                }

                // Filtrar pelo status
                if (filtroStatus !== "Todos" && statusLabel !== filtroStatus) {
                    return; // Pula este motorista
                }

                total++;
                if (statusLabel === "Válido") validos++;
                else if (statusLabel === "A Vencer") aVencer++;
                else if (statusLabel === "Vencido") vencidos++;

                const row = `<tr>
    <td><input type="checkbox" class="select-motorista" value="${motorista.id}"></td>
    <td>${motorista.credencial}</td>
    <td>${capitalizarNome(motorista.nome)}</td>
    <td>${motorista.cnh}</td>
    <td class="cpf">${formatarCPF(motorista.cpf)}</td>
    <td>${motorista.modelo}</td>
    <td class="ano ${motorista.ano_vermelho ? 'ano-vermelho' : ''}">${motorista.ano}</td>
    <td class="placa">${formatarPlaca(motorista.placa)}</td>
    <td>${motorista.validade}</td>
    <td class="status"><span class="status-badge ${statusClass}">${statusLabel}</span></td>
    <td class="dias">${dias}</td>
    <td class="action-icons">
        <img src="assets/icons/edit.svg" alt="Editar" class="icon-action edit-icon">
        <img src="assets/icons/trash-2.svg" alt="Excluir" class="icon-action delete-icon">
    </td>
</tr>`;

                tabela.insertAdjacentHTML("beforeend", row);

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
                        fetch('src/processar/excluir_motorista.php?id=' + encodeURIComponent(motorista.id), { cache: 'no-store' })
                            .then(response => response.text())
                            .then(data => {
                                if (data.toLowerCase().includes('sucesso')) {
                                    mostrarMensagem("success", "Motorista excluído com sucesso!");
                                    atualizarTabela();
                                } else {
                                    mostrarMensagem("error", "Erro ao excluir: " + data);
                                }
                            })
                            .catch(error => {
                                console.error("Erro ao excluir:", error);
                                mostrarMensagem("error", "Erro ao excluir motorista.");
                            });
                    }
                    mostrarMensagem("warning", `Tem certeza que deseja excluir ${motorista.nome}? Clique em Confirmar para confirmar ou Cancelar para cancelar.`, confirmarExclusao);
                });
            });

            totalEl.textContent = total;
            validosEl.textContent = validos;
            aVencerEl.textContent = aVencer;
            vencidosEl.textContent = vencidos;
        })
        .catch(error => console.error("Erro ao carregar motoristas:", error));
}

document.addEventListener("DOMContentLoaded", function () {
    atualizarTabela();

    const filtroNomeInput = document.getElementById("filtroNome");
    if (filtroNomeInput) {
        filtroNomeInput.addEventListener("input", () => {
            atualizarTabela();
        });
    }

    const filtroStatusSelect = document.getElementById("filtroStatus");
    if (filtroStatusSelect) {
        filtroStatusSelect.addEventListener("change", () => {
            atualizarTabela();
        });
    }

    // Submissão do formulário de edição
    const formEditar = document.getElementById("formEditarMotorista");
    if (formEditar) {
        formEditar.addEventListener("submit", function (e) {
            e.preventDefault();

            const formData = new FormData(formEditar);

            fetch("src/processar/atualizar_motorista.php", {
                method: "POST",
                body: formData
            })
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === "sucesso") {
                        mostrarMensagem("success", "Motorista atualizado com sucesso!");
                        document.getElementById("modalEditarMotorista").classList.remove("show");
                        const modalMensagem = document.getElementById("modalMensagem");
                        if (modalMensagem) {
                            // deixa o modal de mensagem fechar sozinho
                        }
                        atualizarTabela();
                    } else {
                        mostrarMensagem("error", "Erro ao atualizar motorista.");
                    }
                })
                .catch(error => {
                    console.error("Erro ao atualizar motorista:", error);
                    mostrarMensagem("error", "Erro ao atualizar motorista.");
                });
        });
    }

    // Submissão do formulário de novo motorista (AJAX)
    const formNovo = document.getElementById("formNovoMotorista");
    if (formNovo) {
        formNovo.removeAttribute('action');
        formNovo.addEventListener("submit", function (e) {
            e.preventDefault();
            e.stopPropagation();
            const formData = new FormData(formNovo);
            const actionUrl = formNovo.getAttribute("action") || "src/processar/processa_novo_motorista.php";
            fetch(actionUrl, {
                method: "POST",
                body: formData
            })
                .then(response => response.text())
                .then(data => {
                    if (data.trim().toLowerCase().includes("sucesso")) {
                        mostrarMensagem("success", "Motorista cadastrado com sucesso!");
                        const modalNovo = document.getElementById("modalNovoMotorista");
                        if (modalNovo) modalNovo.classList.remove("show");
                        formNovo.reset();
                        atualizarTabela();
                    } else {
                        mostrarMensagem("error", "Erro ao cadastrar motorista: " + data);
                    }
                })
                .catch(error => {
                    console.error("Erro ao cadastrar motorista:", error);
                    mostrarMensagem("error", "Erro ao cadastrar motorista.");
                });
        });
    }

    // Importar Excel
    const btnImportar = document.getElementById("btnImportar");
    const inputImportar = document.getElementById("inputImportar");

    if (btnImportar && inputImportar) {
        btnImportar.addEventListener("click", () => {
            inputImportar.click();
        });

        inputImportar.addEventListener("change", () => {
            if (inputImportar.files.length === 0) return;

            const arquivo = inputImportar.files[0];
            const formData = new FormData();
            formData.append("arquivo", arquivo);

            fetch("src/processar/importar_motoristas.php", {
                method: "POST",
                body: formData
            })
                .then(response => response.text())
                .then(data => {
                    if (data.toLowerCase().includes("sucesso")) {
                        mostrarMensagem("success", "Arquivo importado com sucesso!");
                        atualizarTabela();
                    } else {
                        mostrarMensagem("error", "Erro ao importar arquivo.");
                    }
                })
                .catch(error => {
                    console.error("Erro ao importar arquivo:", error);
                    mostrarMensagem("error", "Erro ao importar arquivo.");
                })
                .finally(() => {
                    inputImportar.value = ""; // limpa o input para permitir reimportar o mesmo arquivo se quiser
                });
        });
    }

    // Exclusão em massa
    const btnExcluirSelecionados = document.getElementById("btnExcluirSelecionados");
    if (btnExcluirSelecionados) {
        btnExcluirSelecionados.addEventListener("click", () => {
            const ids = Array.from(document.querySelectorAll(".select-motorista:checked")).map(cb => cb.value);
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
                    .then(response => response.text())
                    .then(data => {
                        if (data.toLowerCase().includes("sucesso")) {
                            mostrarMensagem("success", "Motoristas excluídos com sucesso!");
                            atualizarTabela();
                        } else {
                            mostrarMensagem("error", "Erro ao excluir motoristas.");
                        }
                    })
                    .catch(error => {
                        console.error("Erro ao excluir motoristas:", error);
                        mostrarMensagem("error", "Erro ao excluir motoristas.");
                    });
            }
            mostrarMensagem("warning", "Deseja realmente excluir os motoristas selecionados?", confirmarExclusao);
        });
    }

    // Selecionar/Deselecionar todos
    const selectAll = document.getElementById("select-all");
    if (selectAll) {
        selectAll.addEventListener("change", function () {
            const checkboxes = document.querySelectorAll(".select-motorista");
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }
});