<?php
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
    <title>Dashboard - BoraCar</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            height: auto;
            background: linear-gradient(135deg, #1c1c2b, #541212);
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #eee;
            display: block;
            padding: 0;
        }

        /* Header */
        .dash-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
            background: rgba(0,0,0,0.3);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            flex-wrap: wrap;
            gap: 0.8rem;
        }

        .dash-header h1 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #fff;
        }

        .dash-header p { color: #aaa; font-size: 0.85rem; }

        .btn-voltar {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(255,255,255,0.1);
            color: #eee;
            text-decoration: none;
            padding: 0.5rem 1.1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: background 0.2s;
        }
        .btn-voltar:hover { background: rgba(255,255,255,0.2); }

        /* Cards de totais */
        .dash-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
            padding: 1.5rem 2rem 0;
        }

        .dash-card {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 1.2rem 1rem;
            text-align: center;
        }

        .dash-card .num {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }

        .dash-card .label {
            font-size: 0.82rem;
            color: #bbb;
            margin-top: 0.3rem;
        }

        .c-total    { border-top: 3px solid #90caf9; }
        .c-valido   { border-top: 3px solid #66bb6a; }
        .c-avencer  { border-top: 3px solid #ffa726; }
        .c-vencido  { border-top: 3px solid #ef5350; }
        .c-suspenso { border-top: 3px solid #ab47bc; }
        .c-pendente { border-top: 3px solid #ffca28; }

        /* Grid de gráficos */
        .dash-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem 2rem 2rem;
        }

        .chart-box {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 12px;
            padding: 1.2rem 1.4rem;
        }

        .chart-box h3 {
            font-size: 0.95rem;
            color: #ccc;
            margin-bottom: 1rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .chart-box canvas { max-height: 280px; }

        .loading {
            text-align: center;
            color: #888;
            padding: 3rem;
            font-style: italic;
        }

        @media (max-width: 600px) {
            .dash-cards { padding: 1rem; }
            .dash-grid  { grid-template-columns: 1fr; padding: 1rem; }
            .dash-header { padding: 0.8rem 1rem; }
        }
    </style>
</head>
<body>

<div class="dash-header">
    <div>
        <h1>📊 Dashboard de Credenciais</h1>
        <p>Visão geral e evolução do sistema</p>
    </div>
    <a href="painel.php" class="btn-voltar">← Voltar ao Painel</a>
</div>

<!-- Cards de totais -->
<div class="dash-cards">
    <div class="dash-card c-total">
        <div class="num" id="dTotal">—</div>
        <div class="label">Total</div>
    </div>
    <div class="dash-card c-valido">
        <div class="num" id="dValidos">—</div>
        <div class="label">Válidos</div>
    </div>
    <div class="dash-card c-avencer">
        <div class="num" id="dAVencer">—</div>
        <div class="label">A Vencer (30d)</div>
    </div>
    <div class="dash-card c-vencido">
        <div class="num" id="dVencidos">—</div>
        <div class="label">Vencidos</div>
    </div>
    <div class="dash-card c-suspenso">
        <div class="num" id="dSuspensos">—</div>
        <div class="label">Suspensos</div>
    </div>
    <div class="dash-card c-pendente">
        <div class="num" id="dPendentes">—</div>
        <div class="label">Pendentes</div>
    </div>
</div>

<!-- Gráficos -->
<div class="dash-grid">
    <div class="chart-box">
        <h3>🥧 Distribuição atual por status</h3>
        <canvas id="chartStatus"></canvas>
    </div>
    <div class="chart-box">
        <h3>📅 Vencimentos por mês</h3>
        <canvas id="chartVencimentos"></canvas>
    </div>
    <div class="chart-box">
        <h3>➕ Cadastros por mês (últimos 12 meses)</h3>
        <canvas id="chartCadastros"></canvas>
    </div>
    <div class="chart-box">
        <h3>🚗 Modelos mais cadastrados</h3>
        <canvas id="chartModelos"></canvas>
    </div>
</div>

<script>
const MES_LABELS = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];

function mesLabel(yyyymm) {
    const [y, m] = yyyymm.split('-');
    return MES_LABELS[parseInt(m) - 1] + '/' + y.slice(2);
}

// Gera array de meses entre dois meses YYYY-MM
function gerarMeses(inicio, fim) {
    const meses = [];
    let [ay, am] = inicio.split('-').map(Number);
    const [fy, fm] = fim.split('-').map(Number);
    while (ay < fy || (ay === fy && am <= fm)) {
        meses.push(`${ay}-${String(am).padStart(2,'0')}`);
        am++;
        if (am > 12) { am = 1; ay++; }
    }
    return meses;
}

fetch('src/ajax/dados_dashboard.php')
    .then(r => r.json())
    .then(d => {
        if (d.erro) { console.error(d.erro); return; }

        const s = d.status;

        // Cards
        document.getElementById('dTotal').textContent    = d.total;
        document.getElementById('dValidos').textContent  = s.validos;
        document.getElementById('dAVencer').textContent  = s.a_vencer;
        document.getElementById('dVencidos').textContent = s.vencidos;
        document.getElementById('dSuspensos').textContent= s.suspensos;
        document.getElementById('dPendentes').textContent= s.pendentes;

        const defaults = {
            plugins: { legend: { labels: { color: '#ccc', font: { size: 12 } } } },
        };

        // ── Gráfico 1: Donut status ──
        new Chart(document.getElementById('chartStatus'), {
            type: 'doughnut',
            data: {
                labels: ['Válidos', 'A Vencer', 'Vencidos', 'Suspensos', 'Pendentes'],
                datasets: [{
                    data: [s.validos, s.a_vencer, s.vencidos, s.suspensos, s.pendentes],
                    backgroundColor: ['#66bb6a','#ffa726','#ef5350','#ab47bc','#ffca28'],
                    borderColor: 'rgba(0,0,0,0.3)',
                    borderWidth: 2,
                }]
            },
            options: {
                ...defaults,
                cutout: '60%',
                plugins: {
                    ...defaults.plugins,
                    legend: { ...defaults.plugins.legend, position: 'bottom' }
                }
            }
        });

        // ── Gráfico 2: Vencimentos por mês ──
        const hoje = new Date();
        const inicioVenc = `${hoje.getFullYear()}-${String(hoje.getMonth()-5).padStart(2,'0')}`;
        const mapaVenc = Object.fromEntries(d.vencimentos.map(v => [v.mes, parseInt(v.total)]));
        const mesesVenc = d.vencimentos.map(v => v.mes);
        const mesAtual  = `${hoje.getFullYear()}-${String(hoje.getMonth()+1).padStart(2,'0')}`;

        new Chart(document.getElementById('chartVencimentos'), {
            type: 'bar',
            data: {
                labels: mesesVenc.map(mesLabel),
                datasets: [{
                    label: 'Credenciais',
                    data: mesesVenc.map(m => mapaVenc[m] || 0),
                    backgroundColor: mesesVenc.map(m =>
                        m < mesAtual ? 'rgba(239,83,80,0.7)' :
                        m === mesAtual ? 'rgba(255,167,38,0.9)' :
                        'rgba(102,187,106,0.7)'
                    ),
                    borderRadius: 5,
                }]
            },
            options: {
                ...defaults,
                scales: {
                    x: { ticks: { color: '#aaa' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                    y: { ticks: { color: '#aaa', precision: 0 }, grid: { color: 'rgba(255,255,255,0.05)' } }
                },
                plugins: { ...defaults.plugins, legend: { display: false } }
            }
        });

        // ── Gráfico 3: Cadastros por mês ──
        if (d.cadastros.length > 0) {
            const mesesCad = d.cadastros.map(c => c.mes);
            new Chart(document.getElementById('chartCadastros'), {
                type: 'line',
                data: {
                    labels: mesesCad.map(mesLabel),
                    datasets: [{
                        label: 'Cadastros',
                        data: d.cadastros.map(c => parseInt(c.total)),
                        borderColor: '#42a5f5',
                        backgroundColor: 'rgba(66,165,245,0.15)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#42a5f5',
                        pointRadius: 4,
                    }]
                },
                options: {
                    ...defaults,
                    scales: {
                        x: { ticks: { color: '#aaa' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                        y: { ticks: { color: '#aaa', precision: 0 }, grid: { color: 'rgba(255,255,255,0.05)' } }
                    },
                    plugins: { ...defaults.plugins, legend: { display: false } }
                }
            });
        } else {
            document.getElementById('chartCadastros').closest('.chart-box').innerHTML =
                '<h3>➕ Cadastros por mês</h3><p class="loading">Dados insuficientes (coluna criado_em necessária)</p>';
        }

        // ── Gráfico 4: Modelos ──
        new Chart(document.getElementById('chartModelos'), {
            type: 'bar',
            data: {
                labels: d.modelos.map(m => m.modelo.toUpperCase()),
                datasets: [{
                    label: 'Motoristas',
                    data: d.modelos.map(m => parseInt(m.total)),
                    backgroundColor: [
                        '#42a5f5','#66bb6a','#ffa726','#ef5350',
                        '#ab47bc','#26c6da','#ffca28','#8d6e63'
                    ],
                    borderRadius: 5,
                }]
            },
            options: {
                indexAxis: 'y',
                ...defaults,
                scales: {
                    x: { ticks: { color: '#aaa', precision: 0 }, grid: { color: 'rgba(255,255,255,0.05)' } },
                    y: { ticks: { color: '#eee' }, grid: { display: false } }
                },
                plugins: { ...defaults.plugins, legend: { display: false } }
            }
        });
    })
    .catch(e => console.error('Erro dashboard:', e));
</script>
</body>
</html>
