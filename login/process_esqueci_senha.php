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
    header('Location: esqueci_senha.php?status=erro');
    exit;
}

$row = $result->fetch_assoc();
$usuario = $row['usuario'];

// Gera token seguro
$token = bin2hex(random_bytes(32));
$expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Salva token no banco
$stmt2 = $conn->prepare("INSERT INTO reset_tokens (usuario, token, expira_em) VALUES (?, ?, ?)");
$stmt2->bind_param("sss", $usuario, $token, $expira);
$stmt2->execute();

// Monta link
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$link = "$protocolo://$host/login/credenciais/login/redefinir_senha.php?token=$token";

// Monta e-mail HTML
$assunto = "🔐 Redefinição de Senha - BoraCar";
$corpo = "
<!DOCTYPE html>
<html lang='pt-br'>
<head><meta charset='UTF-8'></head>
<body style='margin:0; padding:0; background:#f4f4f4; font-family:Arial,sans-serif;'>
  <table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f4f4; padding:30px 0;'>
    <tr><td align='center'>
      <table width='600' cellpadding='0' cellspacing='0' style='background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1);'>
        <!-- Header -->
        <tr>
          <td style='background:#1a1a1a; padding:25px; text-align:center;'>
            <img src='https://boracar.com.br/assets/img/logobrancasidebar.png' alt='BoraCar' style='height:40px;'>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td style='padding:35px 40px;'>
            <h2 style='color:#1a1a1a; margin:0 0 15px;'>Olá, $usuario!</h2>
            <p style='color:#555; font-size:15px; line-height:1.6;'>
              Você solicitou a redefinição de sua senha. Clique no botão abaixo para criar uma nova senha.
              <strong>Este link é válido por 1 hora.</strong>
            </p>
            <div style='text-align:center; margin:30px 0;'>
              <a href='$link' style='background:#e53935; color:#fff; padding:14px 32px; border-radius:6px; text-decoration:none; font-size:16px; font-weight:bold; display:inline-block;'>
                🔑 Redefinir Senha
              </a>
            </div>
            <p style='color:#999; font-size:13px;'>
              Se você não solicitou essa alteração, por favor, ignore este e-mail.
            </p>
          </td>
        </tr>
        <!-- Footer -->
        <tr>
          <td style='background:#1a1a1a; padding:18px; text-align:center;'>
            <p style='color:#888; font-size:12px; margin:0;'>© " . date('Y') . " BoraCar. Todos os direitos reservados.</p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>";

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: BoraCar <contato@boracar.com.br>\r\n";
$headers .= "Reply-To: contato@boracar.com.br\r\n";

mail($email, $assunto, $corpo, $headers);

header('Location: esqueci_senha.php?status=enviado');
exit;
