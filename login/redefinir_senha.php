<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - BoraCar</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="../assets/css/reset_senha.css">
</head>
<body>
<?php
require_once '../db/conexao.php';

$token = trim($_GET['token'] ?? '');

// Valida token
$stmt = $conn->prepare(
    "SELECT usuario FROM reset_tokens
     WHERE token = ? AND expira_em > NOW() AND usado = 0"
);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$valido = $result->num_rows === 1;
?>
    <div class="login-container reset-container">
        <img src="../assets/img/logobrancasidebar.png" alt="BoraCar" class="icon" style="max-width:180px; margin-bottom:15px;">
        <h2 class="title">Nova Senha</h2>

        <?php if (!$valido): ?>
            <div class="alert-reset error-reset">
                ❌ Link inválido ou expirado. Solicite um novo link.
            </div>
            <a href="esqueci_senha.php" class="forgot-password">← Solicitar novo link</a>

        <?php elseif (isset($_GET['sucesso'])): ?>
            <div class="alert-reset success-reset">
                ✅ Senha alterada com sucesso!
            </div>
            <a href="index.php" class="forgot-password">← Fazer login</a>

        <?php else: ?>
            <p class="subtitle">Digite e confirme sua nova senha.</p>
            <?php if (isset($_GET['erro'])): ?>
                <div class="alert-reset error-reset">❌ <?= htmlspecialchars($_GET['erro']) ?></div>
            <?php endif; ?>
            <form action="process_redefinir_senha.php" method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="form-inner">
                    <div style="position:relative;">
                        <input type="password" name="senha" id="novaSenha" placeholder="Nova senha" required minlength="6" style="padding-right:2.8rem;">
                        <span onclick="toggleVer('novaSenha', this)" class="toggle-senha">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </span>
                    </div>
                    <div style="position:relative;">
                        <input type="password" name="confirmar" id="confirmarSenha" placeholder="Confirmar nova senha" required minlength="6" style="padding-right:2.8rem;">
                        <span onclick="toggleVer('confirmarSenha', this)" class="toggle-senha">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </span>
                    </div>
                    <button type="submit">Salvar nova senha</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
    function toggleVer(id, btn) {
        const input = document.getElementById(id);
        input.type = input.type === 'password' ? 'text' : 'password';
    }
    </script>
</body>
</html>
