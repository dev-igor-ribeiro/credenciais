# BoraCar – Sistema de Credenciais de Motoristas

Sistema completo de gerenciamento de credenciais de motoristas da BoraCar.  
Deploy automático via **cPanel Git Integration** → HostGator.

---

## 🚀 Funcionalidades

### 🔐 Autenticação
- Login com usuário e senha (hash `password_hash`)
- Ver/ocultar senha na tela de login (ícone SVG)
- **Esqueci minha senha** — envia link de redefinição por e-mail
- **Redefinir senha** — página com token seguro (expira em 1 hora)
- **Alterar senha** — modal dentro do painel para trocar senha com validação da atual
- Confirmação antes de sair (logout via modal customizado)

### 👤 Gestão de Motoristas
- Cadastro, edição e exclusão de motoristas
- Exclusão em lote (checkboxes)
- Perfil detalhado do motorista (modal com todos os dados + data de cadastro)
- Validação real de CPF (algoritmo de dígitos verificadores)
- Verificação de CPF duplicado em tempo real (novo e edição)
- Importação via Excel (`.xlsx`)
- Exportação para Excel

### 🔍 Busca e Filtros
- Busca multi-campo: nome, credencial, CPF, modelo, placa
- Filtro por status (Válido, A Vencer, Vencido, Suspenso, Pendente)
- Filtro por intervalo de data de validade
- Destaque amarelo nos termos buscados
- Botão ✕ para limpar busca dentro do campo
- ESC limpa todos os filtros de uma vez
- Contador de resultados filtrados
- Mensagem quando tabela está vazia

### 📊 Dashboard
- Cards de totais: Total, Válidos, A Vencer (30 dias), Vencidos, Suspensos, Pendentes
- Banner de alertas de vencimento com pills clicáveis (filtra a tabela ao clicar)
- Alertas coloridos: vermelho (vencido), laranja (≤15 dias), amarelo (≤30 dias)

### 📋 Tabela
- Ordenação por qualquer coluna (clique no cabeçalho)
- Setas visuais indicando coluna e direção da ordenação
- Tooltips nos ícones de ação (Editar, Excluir, Ver perfil, Gerar PDF)
- In-memory caching — busca, filtro e ordenação sem nova requisição ao servidor

### 📄 Credenciais PDF
- Geração de credencial individual em PDF (DomPDF)
- Layout padronizado com dados do motorista

### 💾 Backup e Restauração
- Backup manual do banco de dados (arquivo `.sql`)
- Listagem de backups com data, tamanho e destaque do mais recente
- Restauração de backup com confirmação via modal

### 📝 Log de Ações
- Registro automático de: Cadastrou, Editou, Excluiu, Backup, Restaurou
- Modal com tabela colorida por tipo de ação
- Filtro por quantidade de registros (50 / 100 / 200)

### 📱 Responsividade
- Layout adaptado para tablet (`max-width: 900px`)
- Layout adaptado para mobile (`max-width: 600px`)
- Tela de login responsiva com viewport correto

---

## 🛠️ Tecnologias

| Camada | Tecnologia |
|--------|-----------|
| Backend | PHP 8.1 |
| Banco de dados | MySQL / MariaDB (PDO + mysqli) |
| Frontend | HTML5, CSS3, JavaScript (Vanilla) |
| PDF | DomPDF |
| Excel | PhpSpreadsheet |
| Email | PHPMailer + SMTP Mailgrid |
| Servidor | HostGator (Apache) |
| Deploy | cPanel Git Integration |

---

## 🗄️ Bancos de Dados

| Banco | Uso |
|-------|-----|
| `boraca19_credenciais` | motoristas, historico_motorista, log_acoes |
| `boraca19_boracar_login` | usuarios, reset_tokens |

---

## 📂 Estrutura do Projeto

```
/
├── painel.php                  # Painel principal
├── login/                      # Autenticação
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   ├── esqueci_senha.php       # Formulário recuperar senha
│   ├── process_esqueci_senha.php
│   ├── redefinir_senha.php     # Página com token
│   └── process_redefinir_senha.php
├── src/
│   ├── login_form.php
│   ├── modals/                 # Modais: novo, editar, perfil, credencial, backup, log, senha
│   ├── ajax/                   # Handlers AJAX (busca, backup, log, alterar senha...)
│   ├── processar/              # Cadastro, edição, exclusão
│   ├── exportar/
│   └── helpers/
│       └── log.php             # registrarLog()
├── assets/
│   ├── css/
│   │   ├── painel.css
│   │   ├── login.css
│   │   ├── reset_senha.css     # Páginas de recuperação de senha
│   │   ├── modal_motorista.css
│   │   └── modal_perfil.css
│   └── js/
│       ├── script.js           # Lógica principal, busca, filtros, ordenação
│       └── script_novo_motorista.js
├── db/
│   ├── conexao.php             # boraca19_boracar_login (mysqli)
│   └── conexao_motoristas.php  # boraca19_credenciais (PDO)
├── sql/
│   ├── log_acoes.sql           # CREATE TABLE log_acoes
│   └── reset_senha.sql         # ALTER TABLE usuarios + CREATE TABLE reset_tokens
├── backups/                    # Arquivos .sql de backup
└── vendor/                     # Dependências (no git — HostGator sem Composer)
    ├── phpmailer/
    ├── phpoffice/
    └── dompdf/
```

---

## ⚙️ Desenvolvimento Local (XAMPP)

Junction point para sincronizar XAMPP com o repositório:

```cmd
mklink /J C:\xampp\htdocs\credenciais C:\Users\igors\Documents\GitHub\credenciais
```

Acesse: `http://localhost/credenciais`

---

## 🗃️ Migrações SQL necessárias em produção

Execute no banco `boraca19_credenciais`:
```sql
ALTER TABLE motoristas ADD COLUMN criado_em DATETIME DEFAULT CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS log_acoes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(100),
  acao VARCHAR(50),
  descricao TEXT,
  ip VARCHAR(45),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Execute no banco `boraca19_boracar_login`:
```sql
ALTER TABLE usuarios ADD COLUMN email VARCHAR(150) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS reset_tokens (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(100) NOT NULL,
  token VARCHAR(64) NOT NULL,
  expira_em DATETIME NOT NULL,
  usado TINYINT(1) DEFAULT 0,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

UPDATE usuarios SET email = 'seu@email.com' WHERE usuario = 'igor';
```
