<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - BoraCar</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="../assets/css/reset_senha.css">
</head>
<body>
    <div class="login-container reset-container">
        <img src="../assets/img/logobrancasidebar.png" alt="BoraCar" class="icon" style="max-width:180px; margin-bottom:15px;">
        <h2 class="title">Recuperar Senha</h2>
        <p class="subtitle">Digite o e-mail cadastrado para receber o link de redefinição.</p>

        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] === 'enviado'): ?>
                <div class="alert-reset success-reset">
                    ✅ Link enviado! Verifique seu e-mail.
                </div>
            <?php elseif ($_GET['status'] === 'erro'): ?>
                <div class="alert-reset error-reset">
                    ❌ E-mail não encontrado no sistema.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form action="process_esqueci_senha.php" method="POST">
            <div class="form-inner">
                <input type="email" name="email" placeholder="Digite seu e-mail" required autofocus>
                <button type="submit">Enviar link de redefinição</button>
            </div>
        </form>
        <a href="index.php" class="forgot-password">← Voltar para Login</a>
    </div>
</body>
</html>
