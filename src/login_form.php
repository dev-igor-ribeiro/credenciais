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
                    <input type="password" name="senha" id="senhaInput" placeholder="Senha" required style="padding-right:2.5rem;">
                    <span id="toggleSenha" onclick="
                        const i = document.getElementById('senhaInput');
                        const v = i.type === 'password';
                        i.type = v ? 'text' : 'password';
                        this.textContent = v ? '🙈' : '👁️';
                    " style="position:absolute; right:0.75rem; top:50%; transform:translateY(-50%); cursor:pointer; font-size:1.1rem; user-select:none;">👁️</span>
                </div>
                <button type="submit">Entrar</button>
            </div>
        </form>
        <a href="#" class="forgot-password">Esqueci minha senha</a>
    </div>
</body>

</html>