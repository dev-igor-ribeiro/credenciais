<?php
// Carrega a biblioteca DomPDF
require_once __DIR__ . '/dompdf/autoload.inc.php';
use Dompdf\Dompdf;

// Habilita exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Captura os dados enviados via POST e formata o nome em maiúsculas
$nome = strtoupper($_POST['nome']);
$numero = $_POST['numero'];
$cpf = $_POST['cpf'];
$cnh = $_POST['cnh'];

// Converte as imagens (logo e assinatura) para base64
$logoData = base64_encode(file_get_contents(__DIR__ . '/img/logo.jpeg'));
$assinaturaData = base64_encode(file_get_contents(__DIR__ . '/img/assinatura.png'));

// Cria uma nova instância do DomPDF
$dompdf = new Dompdf();

// Monta o conteúdo HTML do PDF com as variáveis preenchidas
$html = '
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; font-size: 13px; margin: 0; padding: 10px; background: #ffffff; }
    .container { border: 1px solid #000; padding: 40px; max-width: 820px; margin: auto; }
    /* .topo { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; }
    .topo img { height: 100px; }
    .topo-texto { font-size: 12px; line-height: 1.4; flex: 1; margin-left: 20px; }
    .topo-texto b { display: inline; } */
    h2 { text-align: center; margin: 30px 0 20px 0; font-size: 18px; text-decoration: underline; }
    .conteudo { text-align: justify; line-height: 1.7; font-size: 14px; }
    .data { margin-top: 30px; font-size: 14px; }
    .assinatura { position: relative; text-align: center; margin-top: 100px; }
    .assinatura img { position: absolute; top: -70px; left: 50%; transform: translateX(-50%); height: 110px; opacity: 0.95; }
    .assinatura-nome { font-weight: bold; margin-top: 5px; margin-bottom: 2px; }
  </style>
</head>
<body>
  <div class="container">
    <div style="border: 1px solid #000; padding: 20px 10px; margin-bottom: 40px;">
    <table width="100%" style="margin-bottom: 20px;">
      <tr style="vertical-align: middle;">
        <td width="25%" align="center" valign="middle" style="padding-top: 10px;">
          <img src="data:image/jpeg;base64,' . $logoData . '" style="height: 95px;">
        </td>
        <td width="75%" style="font-size: 13px; line-height: 1.5; vertical-align: middle; padding-top: 10px;">
          <b>Razão S:</b> BORA CAR TRANSPORTE E SERVIÇOS LTDA<br>
          <b>Endereço:</b> Rua Afonso Arinos N° 153 – Centro<br>
          <b>Cidade:</b> Alfenas - MG - CEP: 37.130-017<br>
          <b>CNPJ:</b> 34.783.315/0001-74 / Email: <u>CONTATO@BORACAR.COM.BR</u><br>
          <b>Fone:</b> (35) 98835-7503
        </td>
      </tr>
    </table>
    </div>
    <h2>Credencial de Motorista Parceiro Nº ' . $numero . '</h2>
    <div class="conteudo">
      A empresa Bora Car Transporte e Serviços LTDA, com sede na Rua Afonso Arinos 153, Centro, na cidade de Alfenas, estado de Minas Gerais, inscrita no CNPJ: 34.783.315/0001-74, inscrição municipal nº 217006, alvará de funcionamento nº575/2023, credenciada junto à superintendência de trânsito da prefeitura Municipal de Alfenas sob o nº' . $numero . '/2025 credencia o senhor(a) <b><u>' . $nome . '</u></b>, CNH <b><u>' . $cnh . '</u></b>, CPF <b><u>' . $cpf . '</u></b>, como motorista parceiro junto ao aplicativo de mobilidade urbana BORACAR, atestando que o motorista se encontra na ativa e tanto o motorista quanto o veículo utilizado para a prestação de serviço estão cumprindo os requisitos de cadastramento previsto na Lei Federal 13.640/2018 art. 8º da Lei Municipal 5.193 de 23 de março de 2023.
    </div>
    <div class="data">Data de emissão: ' . date("d/m/Y") . '</div>
    <div class="assinatura">
      <img src="data:image/png;base64,' . $assinaturaData . '" />
      <div style="height: 1px; background-color: #000; width: 60%; margin: 10px auto 10px auto;"></div>
      <div class="assinatura-nome">Renato Graciano Serafim</div>
      <div>Diretor Geral</div>
    </div>
  </div>
</body>
</html>
';

// Carrega o HTML no DomPDF
$dompdf->loadHtml($html);

// Define o tamanho do papel
$dompdf->setPaper('A4');

// Renderiza o PDF
$dompdf->render();

// Define o nome do arquivo de saída
$arquivo = "{$numero} - {$nome}.pdf";

// Exibe o PDF no navegador (sem forçar download)
$dompdf->stream($arquivo, ["Attachment" => false]);