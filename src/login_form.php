<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
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
                <input type="password" name="senha" placeholder="Senha" required>
                <button type="submit">Entrar</button>
            </div>
        </form>
        <a href="#" class="forgot-password">Esqueci minha senha</a>
    </div>
</body>

</html>