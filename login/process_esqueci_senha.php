<?php
session_start();
require_once '../db/conexao.php';

$email = trim($_POST['email'] ?? '');
if (!$email) {
    header('Location: esqueci_senha.php?status=erro');
    exit;
}

// Verifica se o e-mail existe
$stmt = $conn->prepare("SELECT usuario FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Por segurança, mostra "enviado" mesmo se email não existe (evita enumeração)
    header('Location: esqueci_senha.php?status=enviado');
    exit;
}

$row     = $result->fetch_assoc();
$usuario = $row['usuario'];

// Gera token seguro
$token  = bin2hex(random_bytes(32));
$expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Salva token no banco (invalida tokens antigos do mesmo usuário)
$del = $conn->prepare("UPDATE reset_tokens SET usado = 1 WHERE usuario = ? AND usado = 0");
$del->bind_param("s", $usuario);
$del->execute();

$stmt2 = $conn->prepare("INSERT INTO reset_tokens (usuario, token, expira_em) VALUES (?, ?, ?)");
$stmt2->bind_param("sss", $usuario, $token, $expira);
$stmt2->execute();

// Monta link
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host      = $_SERVER['HTTP_HOST'];
$link      = "$protocolo://$host/login/credenciais/login/redefinir_senha.php?token=$token";

// Assunto MIME-encoded (sem emoji no assunto — causa rejeição)
$assunto = '=?UTF-8?B?' . base64_encode('Redefinição de Senha - BoraCar') . '?=';

// Corpo HTML
$ano   = date('Y');
$corpo = "<!DOCTYPE html>
<html lang='pt-br'>
<head><meta charset='UTF-8'></head>
<body style='margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;'>
  <table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f4f4;padding:30px 0;'>
    <tr><td align='center'>
      <table width='560' cellpadding='0' cellspacing='0' style='background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.12);'>
        <tr>
          <td style='background:#1a1a1a;padding:24px;text-align:center;'>
            <img src='https://boracar.com.br/assets/img/logobrancasidebar.png' alt='BoraCar' style='height:38px;'>
          </td>
        </tr>
        <tr>
          <td style='padding:32px 40px;'>
            <h2 style='color:#1a1a1a;margin:0 0 12px;font-size:20px;'>Ol&aacute;, $usuario!</h2>
            <p style='color:#555;font-size:15px;line-height:1.7;margin:0 0 24px;'>
              Recebemos uma solicitação para redefinir a senha da sua conta BoraCar.<br>
              Clique no botão abaixo para criar uma nova senha.
              <strong>Este link expira em 1 hora.</strong>
            </p>
            <div style='text-align:center;margin:0 0 28px;'>
              <a href='$link'
                 style='background:#e53935;color:#fff;padding:14px 36px;border-radius:6px;
                        text-decoration:none;font-size:16px;font-weight:bold;display:inline-block;'>
                Redefinir Senha
              </a>
            </div>
            <p style='color:#999;font-size:13px;margin:0;'>
              Se você não solicitou essa alteração, ignore este e-mail. Sua senha permanece a mesma.
            </p>
          </td>
        </tr>
        <tr>
          <td style='background:#1a1a1a;padding:16px;text-align:center;'>
            <p style='color:#888;font-size:12px;margin:0;'>&copy; $ano BoraCar. Todos os direitos reservados.</p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>";

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: =?UTF-8?B?" . base64_encode("BoraCar") . "?= <contato@boracar.com.br>\r\n";
$headers .= "Reply-To: contato@boracar.com.br\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

$enviado = mail($email, $assunto, $corpo, $headers);

if ($enviado) {
    header('Location: esqueci_senha.php?status=enviado');
} else {
    // mail() falhou — log para debug
    error_log("[BoraCar] Falha ao enviar email de reset para: $email");
    header('Location: esqueci_senha.php?status=falha');
}
exit;
