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

// Destaca o termo buscado dentro de um texto
function _highlight(texto, termo) {
    if (!termo || !texto) return texto;
    const regex = new RegExp('(' + termo.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
    return texto.replace(regex, '<mark class="busca-highlight">$1</mark>');
}

// Cache dos dados e estado de ordenação
let _motoristasCache = [];
let _sortCol = null;
let _sortDir = 1; // 1 = asc, -1 = desc

function _calcularStatus(motorista) {
    const dias = parseInt(motorista.dias_restante);
    const statusDb = motorista.status;
    if (statusDb === "suspenso") return { label: "Suspenso", cls: "status-suspenso" };
    if (statusDb === "pendente" || isNaN(dias)) return { label: "Pendente", cls: "status-pendente" };
    if (dias > 30) return { label: "Válido", cls: "status-valido" };
    if (dias > 0) return { label: "A Vencer", cls: "status-a-vencer" };
    return { label: "Vencido", cls: "status-vencido" };
}

function _ordenarDados(dados) {
    if (!_sortCol) return dados;
    return [...dados].sort((a, b) => {
        let va, vb;
        if (_sortCol === 'status') {
            va = _calcularStatus(a).label;
            vb = _calcularStatus(b).label;
        } else if (_sortCol === 'dias') {
            va = parseInt(a.dias_restante);
            vb = parseInt(b.dias_restante);
            if (isNaN(va)) va = -9999;
            if (isNaN(vb)) vb = -9999;
            return (va - vb) * _sortDir;
        } else if (_sortCol === 'credencial' || _sortCol === 'ano') {
            va = parseInt(a[_sortCol]) || 0;
            vb = parseInt(b[_sortCol]) || 0;
            return (va - vb) * _sortDir;
        } else if (_sortCol === 'validade') {
            // formato dd/mm/yyyy → yyyy-mm-dd para comparação
            const toISO = v => {
                if (!v || !v.includes('/')) return '';
                const [d, m, y] = v.split('/');
                return `${y}-${m}-${d}`;
            };
            va = toISO(a.validade);
            vb = toISO(b.validade);
        } else {
            va = (a[_sortCol] || '').toString().toLowerCase();
            vb = (b[_sortCol] || '').toString().toLowerCase();
        }
        if (va < vb) return -1 * _sortDir;
        if (va > vb) return 1 * _sortDir;
        return 0;
    });
}

function _atualizarSetinhas() {
    document.querySelectorAll('th.sortable').forEach(th => {
        th.classList.remove('sort-asc', 'sort-desc');
        if (th.dataset.sort === _sortCol) {
            th.classList.add(_sortDir === 1 ? 'sort-asc' : 'sort-desc');
        }
    });
}

function renderTabela(data) {
            const tabela = document.getElementById("tabelaCorpo");
            const totalEl = document.getElementById("totalMotoristas");
            const validosEl = document.getElementById("validos");
            const aVencerEl = document.getElementById("aVencer");
            const vencidosEl = document.getElementById("vencidos");
            const filtroNomeInput = document.getElementById("filtroNome");
            const filtroStatusSelect = document.getElementById("filtroStatus");

            let filtroNome = filtroNomeInput ? filtroNomeInput.value.trim().toLowerCase() : "";
            let filtroStatus = filtroStatusSelect ? filtroStatusSelect.value : "Todos";
            const filtroDataDe  = document.getElementById('filtroDataDe')?.value  || '';
            const filtroDataAte = document.getElementById('filtroDataAte')?.value || '';

            let total = 0, validos = 0, aVencer = 0, vencidos = 0, suspensos = 0, pendentes = 0;
            tabela.innerHTML = "";

            const dadosOrdenados = _ordenarDados(data);

            dadosOrdenados.forEach(motorista => {
                const dias = parseInt(motorista.dias_restante);
                const { label: statusLabel, cls: statusClass } = _calcularStatus(motorista);

                // Filtrar por nome, credencial, CPF, modelo ou placa
                if (filtroNome) {
                    const cpfLimpo = (motorista.cpf || '').replace(/\D/g, '');
                    const buscaLimpa = filtroNome.replace(/\D/g, '');
                    const campos = [
                        (motorista.nome     || '').toLowerCase(),
                        (motorista.credencial|| '').toLowerCase(),
                        (motorista.cpf      || '').toLowerCase(),
                        cpfLimpo,
                        (motorista.modelo   || '').toLowerCase(),
                        (motorista.placa    || '').toLowerCase(),
                    ];
                    const bate = campos.some(c => c.includes(filtroNome)) ||
                                 (buscaLimpa.length >= 3 && cpfLimpo.includes(buscaLimpa));
                    if (!bate) return;
                }

                // Filtrar pelo status
                if (filtroStatus !== "Todos" && statusLabel !== filtroStatus) {
                    return;
                }

                // Filtrar por intervalo de validade
                if (filtroDataDe || filtroDataAte) {
                    // Motoristas sem validade (pendentes) são ignorados no filtro de data
                    if (!motorista.validade || motorista.validade === 'NULL' || motorista.validade === '') {
                        return;
                    }
                    // Converte dd/mm/yyyy → yyyy-mm-dd para comparação
                    let validadeISO = motorista.validade;
                    if (motorista.validade.includes('/')) {
                        const [d, m, y] = motorista.validade.split('/');
                        validadeISO = `${y}-${m}-${d}`;
                    }
                    if (filtroDataDe && validadeISO < filtroDataDe) return;
                    if (filtroDataAte && validadeISO > filtroDataAte) return;
                }

                total++;
                if (statusLabel === "Válido") validos++;
                else if (statusLabel === "A Vencer") aVencer++;
                else if (statusLabel === "Vencido") vencidos++;
                else if (statusLabel === "Suspenso") suspensos++;
                else if (statusLabel === "Pendente") pendentes++;

                const h = t => filtroNome ? _highlight(t, filtroNome) : t;
                const row = `<tr>
    <td><input type="checkbox" class="select-motorista" value="${motorista.id}"></td>
    <td>${h(motorista.credencial)}</td>
    <td class="nome-clicavel" onclick="abrirModalPerfil(${motorista.id})" title="Ver perfil">${h(capitalizarNome(motorista.nome))}</td>
    <td>${motorista.cnh}</td>
    <td class="cpf">${h(formatarCPF(motorista.cpf))}</td>
    <td>${h(motorista.modelo)}</td>
    <td class="ano ${motorista.ano_vermelho ? 'ano-vermelho' : ''}">${motorista.ano}</td>
    <td class="placa">${h(formatarPlaca(motorista.placa))}</td>
    <td>${motorista.validade}</td>
    <td class="status"><span class="status-badge ${statusClass}">${statusLabel}</span></td>
    <td class="dias">${dias}</td>
    <td class="action-icons">
        <span class="tooltip-wrap" data-tooltip="Editar"><img src="assets/icons/edit.svg" alt="Editar" class="icon-action edit-icon"></span>
        <span class="tooltip-wrap" data-tooltip="Excluir"><img src="assets/icons/trash-2.svg" alt="Excluir" class="icon-action delete-icon"></span>
    </td>
</tr>`;

                tabela.insertAdjacentHTML("beforeend", row);

                const ultimaLinha = tabela.lastElementChild;
                const btnEditar = ultimaLinha.querySelector(".edit-icon");
                const btnExcluir = ultimaLinha.querySelector(".delete-icon");

                btnEditar.addEventListener("click", () => {
                    const modal = document.getElementById("modalEditarMotorista");
                    modal.classList.add("show");

                    // Limpa estado de validação CPF ao abrir
                    window._cpfDuplicadoEditar = false;
                    const avisoCpfEditar = document.getElementById('aviso-cpf-editar');
                    if (avisoCpfEditar) avisoCpfEditar.style.display = 'none';
                    const editarCpfInput = document.getElementById('editarCpf');
                    if (editarCpfInput) editarCpfInput.style.borderColor = '';

                    document.getElementById("editarId").value = motorista.id;
                    document.getElementById("editarNome").value = motorista.nome || '';
                    document.getElementById("editarCnh").value = motorista.cnh || '';
                    document.getElementById("editarCpf").value = motorista.cpf || '';
                    if (motorista.validade && motorista.validade.includes("/")) {
                        const [dia, mes, ano] = motorista.validade.split("/");
                        document.getElementById("editarValidade").value = `${ano}-${mes}-${dia}`;
                    } else {
                        document.getElementById("editarValidade").value = '';
                    }
                    document.getElementById("editarModelo").value = motorista.modelo || '';
                    document.getElementById("editarAno").value = motorista.ano || '';
                    document.getElementById("editarPlaca").value = motorista.placa || '';
                    document.getElementById("editarCredencial").value = motorista.credencial || '';
                    const statusSelect = document.getElementById("editarStatus");
                    if (statusSelect) {
                        const s = motorista.status;
                        statusSelect.value = (s === "suspenso" || s === "pendente") ? s : "automatico";
                    }
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

            // Mensagem tabela vazia
            if (total === 0) {
                tabela.innerHTML = `<tr><td colspan="12" style="text-align:center; padding:2rem; color:#aaa; font-style:italic;">
                    Nenhum motorista encontrado para os filtros aplicados.
                </td></tr>`;
            }

            // Contador de resultados
            let contadorEl = document.getElementById('contadorResultados');
            if (!contadorEl) {
                contadorEl = document.createElement('p');
                contadorEl.id = 'contadorResultados';
                contadorEl.style.cssText = 'margin:0.3rem 0 0.5rem; font-size:0.82rem; color:#bbb; text-align:right;';
                document.querySelector('.table-wrapper').insertAdjacentElement('beforebegin', contadorEl);
            }
            const totalGeral = _motoristasCache.length;
            contadorEl.textContent = total === totalGeral
                ? `${total} motorista${total !== 1 ? 's' : ''} cadastrado${total !== 1 ? 's' : ''}`
                : `Exibindo ${total} de ${totalGeral} motoristas`;

            totalEl.textContent = total;
            validosEl.textContent = validos;
            aVencerEl.textContent = aVencer;
            vencidosEl.textContent = vencidos;
            const suspensosEl = document.getElementById("suspensos");
            const pendentesEl = document.getElementById("pendentes");
            if (suspensosEl) suspensosEl.textContent = suspensos;
            if (pendentesEl) pendentesEl.textContent = pendentes;

            _atualizarSetinhas();
}

function atualizarAlertas(data) {
    const el = document.getElementById('alertaVencimentos');
    if (!el) return;

    let vencidos = 0, aVencer15 = 0, aVencer30 = 0;

    data.forEach(m => {
        const { label } = _calcularStatus(m);
        const dias = parseInt(m.dias_restante);
        if (label === 'Vencido') vencidos++;
        else if (label === 'A Vencer') {
            aVencer30++;
            if (dias <= 15) aVencer15++;
        }
    });

    const alertas = [];
    if (vencidos > 0)
        alertas.push(`<span class="alerta-item alerta-vermelho">🔴 <strong>${vencidos}</strong> ${vencidos > 1 ? 'credenciais vencidas' : 'credencial vencida'}</span>`);
    if (aVencer15 > 0)
        alertas.push(`<span class="alerta-item alerta-laranja">🟠 <strong>${aVencer15}</strong> vence${aVencer15 > 1 ? 'm' : ''} em até 15 dias</span>`);
    else if (aVencer30 > 0)
        alertas.push(`<span class="alerta-item alerta-amarelo">🟡 <strong>${aVencer30}</strong> vence${aVencer30 > 1 ? 'm' : ''} em até 30 dias</span>`);

    if (alertas.length > 0) {
        el.innerHTML = `<div class="alerta-banner">${alertas.join('')}</div>`;
    } else {
        el.innerHTML = `<div class="alerta-banner alerta-ok">✅ <strong>Todas as credenciais estão em dia!</strong></div>`;
    }
}

function atualizarTabela() {
    fetch("src/ajax/carregar_motoristas.php?ts=" + Date.now(), { cache: "no-store" })
        .then(response => response.json())
        .then(data => {
            _motoristasCache = data;
            atualizarAlertas(data);
            renderTabela(_motoristasCache);
        })
        .catch(error => console.error("Erro ao carregar motoristas:", error));
}

document.addEventListener("DOMContentLoaded", function () {
    atualizarTabela();

    // Ordenação por clique nos cabeçalhos
    document.querySelectorAll('th.sortable').forEach(th => {
        th.addEventListener('click', () => {
            const col = th.dataset.sort;
            if (_sortCol === col) {
                _sortDir *= -1;
            } else {
                _sortCol = col;
                _sortDir = 1;
            }
            _atualizarSetinhas();
            renderTabela(_motoristasCache);
        });
    });

    // Cards clicáveis para filtrar por status
    const mapaCards = {
        'totalMotoristas': 'Todos',
        'validos':         'Válido',
        'aVencer':         'A Vencer',
        'vencidos':        'Vencido',
        'suspensos':       'Suspenso',
        'pendentes':       'Pendente'
    };

    Object.entries(mapaCards).forEach(([cardId, statusValor]) => {
        const card = document.getElementById(cardId)?.closest('.card');
        if (!card) return;
        card.style.cursor = 'pointer';
        card.addEventListener('click', () => {
            const select = document.getElementById('filtroStatus');
            if (select) {
                select.value = statusValor;
                select.dispatchEvent(new Event('change'));
            }
            // Destaca o card ativo
            document.querySelectorAll('.card').forEach(c => c.classList.remove('card-ativo'));
            card.classList.add('card-ativo');
        });
    });

    const filtroNomeInput = document.getElementById("filtroNome");
    if (filtroNomeInput) {
        filtroNomeInput.addEventListener("input", () => {
            renderTabela(_motoristasCache);
        });
        filtroNomeInput.addEventListener("keydown", (e) => {
            if (e.key === "Escape") {
                filtroNomeInput.value = "";
                document.getElementById("filtroStatus").value = "Todos";
                const de = document.getElementById('filtroDataDe');
                const ate = document.getElementById('filtroDataAte');
                if (de) de.value = '';
                if (ate) ate.value = '';
                document.querySelectorAll('.card').forEach(c => c.classList.remove('card-ativo'));
                renderTabela(_motoristasCache);
                filtroNomeInput.blur();
            }
        });
    }

    const filtroStatusSelect = document.getElementById("filtroStatus");
    if (filtroStatusSelect) {
        filtroStatusSelect.addEventListener("change", () => {
            renderTabela(_motoristasCache);
        });
    }

    // Filtro por data de validade
    document.getElementById('filtroDataDe')?.addEventListener('change', () => renderTabela(_motoristasCache));
    document.getElementById('filtroDataAte')?.addEventListener('change', () => renderTabela(_motoristasCache));

    document.getElementById('btnLimparDatas')?.addEventListener('click', () => {
        document.getElementById('filtroDataDe').value = '';
        document.getElementById('filtroDataAte').value = '';
        renderTabela(_motoristasCache);
    });

    // Submissão do formulário de edição
    const formEditar = document.getElementById("formEditarMotorista");
    if (formEditar) {
        formEditar.addEventListener("submit", function (e) {
            e.preventDefault();

            if (window._cpfDuplicadoEditar === true) {
                mostrarMensagem('error', 'Este CPF já está cadastrado para outro motorista. Verifique os dados.');
                return;
            }

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

            // Bloqueia se credencial já cadastrada
            if (window._credencialDuplicada === true) {
                mostrarMensagem('error', 'Esta credencial já está cadastrada. Escolha outro número.');
                return;
            }
            // Bloqueia se CPF já cadastrado
            if (window._cpfDuplicado === true) {
                mostrarMensagem('error', 'Este CPF já está cadastrado. Verifique os dados.');
                return;
            }

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