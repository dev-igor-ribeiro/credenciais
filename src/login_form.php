<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login BoraCar</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            const button = form.querySelector('button');

            form.addEventListener('submit', function () {
                button.disabled = true;
                button.textContent = 'Entrando...';
            });

            // Ocultar mensagens após 4 segundos
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 4000);
            });
        });
    </script>
</head>

<body>
    <div class="login-container">
        <img src="../assets/img/logobrancasidebar.png" alt="BoraCar" class="icon"
            style="max-width: 240px; margin-bottom: 15px;">
        <h2 class="title">Credenciais de Motoristas</h2>
        <p class="subtitle">Faça login para acessar o sistema</p>
        <?php if (isset($_GET['erro'])): ?>
            <div class="alert error">
                <?php echo htmlspecialchars($_GET['erro']); ?>
            </div>
        <?php elseif (isset($_GET['sucesso'])): ?>
            <div class="alert success">
                <?php echo htmlspecialchars($_GET['sucesso']); ?>
            </div>
        <?php endif; ?>
        <form action="login.php" method="post">
            <div class="form-inner">
                <input type="text" name="usuario" placeholder="Usuário" required>
                <div style="position:relative;">
                    <input type="password" name="senha" id="senhaInput" placeholder="Senha" required style="padding-right:2.8rem;">
                    <span id="toggleSenha" onclick="
                        const i = document.getElementById('senhaInput');
                        const v = i.type === 'password';
                        i.type = v ? 'text' : 'password';
                        document.getElementById('iconOlho').style.display    = v ? 'none'  : 'block';
                        document.getElementById('iconOlhoOff').style.display = v ? 'block' : 'none';
                    " style="position:absolute; right:0.75rem; top:50%; transform:translateY(-50%); cursor:pointer; display:flex; align-items:center; opacity:0.5; transition:opacity 0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.5">
                        <!-- olho aberto -->
                        <svg id="iconOlho" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                        <!-- olho fechado (riscado) -->
                        <svg id="iconOlhoOff" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </span>
                </div>
                <button type="submit">Entrar</button>
            </div>
        </form>
        <a href="#" class="forgot-password">Esqueci minha senha</a>
    </div>
</body>

</html>