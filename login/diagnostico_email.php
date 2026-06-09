<?php
// REMOVER APÓS O DIAGNÓSTICO
require_once '../db/conexao.php';

echo "<style>body{font-family:monospace;background:#111;color:#eee;padding:20px;} .ok{color:#4CAF50;} .err{color:#e53935;} h3{color:#aaa;}</style>";
echo "<h2>🔍 Diagnóstico BoraCar</h2>";

// 1. Tabela reset_tokens existe?
echo "<h3>1. Tabela reset_tokens</h3>";
$r = $conn->query("SHOW TABLES LIKE 'reset_tokens'");
if ($r && $r->num_rows > 0) {
    echo "<span class='ok'>✅ Existe</span><br>";
} else {
    echo "<span class='err'>❌ NÃO EXISTE — rode o SQL abaixo!</span><br>";
    echo "<pre style='background:#222;padding:10px;color:#f9c74f;'>
CREATE TABLE IF NOT EXISTS reset_tokens (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario VARCHAR(100) NOT NULL,
  token VARCHAR(64) NOT NULL,
  expira_em DATETIME NOT NULL,
  usado TINYINT(1) DEFAULT 0,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
</pre>";
}

// 2. Coluna email em usuarios?
echo "<h3>2. Coluna email em usuarios</h3>";
$r2 = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'email'");
if ($r2 && $r2->num_rows > 0) {
    echo "<span class='ok'>✅ Existe</span><br>";
    $sel = $conn->query("SELECT usuario, email FROM usuarios");
    while ($row = $sel->fetch_assoc()) {
        $emailMask = $row['email'] ? substr($row['email'], 0, 4) . '***' : 'NULL';
        echo "Usuário: <b>{$row['usuario']}</b> | Email: <b>$emailMask</b><br>";
    }
} else {
    echo "<span class='err'>❌ NÃO EXISTE — rode: ALTER TABLE usuarios ADD COLUMN email VARCHAR(150) DEFAULT NULL;</span><br>";
}

// 3. Teste de mail()
echo "<h3>3. Teste de envio de e-mail</h3>";
$emailTeste = 'igorsribeiro13.ir@gmail.com';
$assunto    = '=?UTF-8?B?' . base64_encode('Teste BoraCar - Diagnostico') . '?=';
$corpo      = "<p>Este é um email de teste do sistema BoraCar. Se chegou, o mail() está funcionando.</p>";
$headers    = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nFrom: BoraCar <contato@boracar.com.br>\r\n";

$ok = mail($emailTeste, $assunto, $corpo, $headers);
if ($ok) {
    echo "<span class='ok'>✅ mail() retornou TRUE — verifique caixa de entrada e spam de $emailTeste</span><br>";
} else {
    echo "<span class='err'>❌ mail() retornou FALSE — servidor não aceitou o envio</span><br>";
}

// 4. Tokens recentes
echo "<h3>4. Últimos tokens gerados</h3>";
$r3 = $conn->query("SELECT usuario, LEFT(token,12) as token_inicio, expira_em, usado, criado_em FROM reset_tokens ORDER BY id DESC LIMIT 5");
if ($r3 && $r3->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse;color:#eee;'><tr><th>Usuário</th><th>Token (início)</th><th>Expira</th><th>Usado</th><th>Criado</th></tr>";
    while ($row = $r3->fetch_assoc()) {
        echo "<tr><td>{$row['usuario']}</td><td>{$row['token_inicio']}...</td><td>{$row['expira_em']}</td><td>{$row['usado']}</td><td>{$row['criado_em']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<span class='err'>Nenhum token encontrado (tabela vazia ou não existe)</span><br>";
}

echo "<br><hr><small style='color:#666;'>Remova este arquivo após o diagnóstico.</small>";
