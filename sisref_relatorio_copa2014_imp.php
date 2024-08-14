<?php

set_time_limit(0);

// funcoes de uso geral
include_once( "config.php" );

// Verifica se o usuário tem a permissão para acessar este módulo
// Inicializa a sessão (session_start)
verifica_permissao("sRH");

// define um nome para o arquivo PDF
$data_hoje = date("Y-m-d");

// ler dados gravados em sessao para uso na impressao
$aDadosEncontrados        = $_SESSION['saDadosEncontradosI'];
$periodo                  = $_SESSION['sIMPPeriodo'];
$mes_ano_final            = $_SESSION['sIMPComp'];
$upag                     = $_SESSION['sIMPUpag'];
$sLotacao                 = $_SESSION['sIMPLotacao'];
$wnomelota                = $_SESSION['sIMPLotacaoDescricao'];
$caminho_modulo_utilizado = utf8_encode(str_replace('»', '>', $_SESSION['sIMPCaminho']));

// instancia bando de dados
$oDBase = new DataBase('PDO');

// arquivo
$oDBase->query("SELECT id, upag, tipo, maquina, arquivo FROM relatorios_arquivos WHERE upag = '$upag' AND tipo = 'copa2014' AND data_arquivo='$data_hoje' ORDER BY upag, arquivo DESC ");
$nRowsArq = $oDBase->num_rows();
$oArquivo = $oDBase->fetch_object();
$nId      = $oArquivo->id;

// define um nome para o arquivo PDF
$arquivo_pesquisar = 'compensacao_copa2014_nao_realizada_' . $upag . "-" . date("Ymd");
$arquivo           = $arquivo_pesquisar . ($nRowsArq == 0 ? '_1' : '_2') . '.pdf';

//Relatorio de Servidores que nao Compensaram o Recesso de Fim de Ano
$idInner = "
	<!doctype html public '-//w3c//dtd html 4.01 transitional//pt'>
	<html lang='pt-br'>
	<head>
	<title></title>
	<meta http-equiv='Content-Language' content='pt-br'>
	<meta http-equiv='Content-Type' content='text/html; charset=windows-1252'>
	<link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "print_4pdf.css' media='print'></style>
	<link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "estilo.css'></style>
	<link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "class_formpadrao.css'></style>
	<link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "estiloIE.css'></style>
	<link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "table_sorter.css'></style>
	</head>
	<body style='margin: 0px; background-color: #FFFFFF;'>";

$cabecalho = "<table width='100%' border='0' cellpadding='0' cellspacing='0' align='center'>";
$cabecalho .= "  <tr>";
$cabecalho .= "    <td colspan='7' style='height: 40px; vertical-align: middle; text-align: center;' class='tahomaSISREF_4'>";
$cabecalho .= "     SISREF - Sistema de Registro Eletrônico de Frequência";
$cabecalho .= "    </td>";
$cabecalho .= "  </tr>";
$cabecalho .= "  <tr>";
$cabecalho .= "    <td colspan='7' style='height: 35px; vertical-align: middle; text-align: center;' class='tahomaSISREF_4'>";
$cabecalho .= "      <small>" . $_SESSION['sIMPTituloFormulario1'] . "</small><br><br>";
$cabecalho .= "    </td>";
$cabecalho .= "  </tr>";
$cabecalho .= "  <tr>";
$cabecalho .= "    <td colspan='7' style='font-family:verdana; font-size:11pt; height: 20px; border: 0px solid #000000; text-align: center;'>";
$cabecalho .= "      &nbsp;";
$cabecalho .= "    </td>";
$cabecalho .= "  </tr>";
$cabecalho .= "  <tr>";
$cabecalho .= "    <td colspan='7' width='100%' align='center' class='tahomaSISREF_2'>";
$cabecalho .= "      Período:&nbsp;<input name='ano' type='text' class='alinhadoAoCentro' id='ano' value='" . $periodo . "' size=20 readonly>";
$cabecalho .= "    </td>";
$cabecalho .= "  </tr>";
$cabecalho .= "  <tr>";
$cabecalho .= "    <td colspan='7' width='100%' style='font-family: Tahoma, verdana; font-size:10pt; height: 20px; border: 1px solid #000000; text-align: center;'>";
$cabecalho .= "      Lota&ccedil;&atilde;o <font style='border: 1px solid #000000; height: 30px;'>" . $sLotacao . "- " . $wnomelota . "</font>";
$cabecalho .= "    </td>";
$cabecalho .= "  </tr>";
$cabecalho .= "  <tr>";
$cabecalho .= "    <td colspan='7' width='100%'>";
$cabecalho .= "      <table border='1' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#F1F1E2' width='100%'>";
$cabecalho .= "        <tr bgcolor='#DBDBB7'>";
$cabecalho .= "          <td colspan='6' align='center'>";
$cabecalho .= $_SESSION['sIMPTituloFormulario2'] . ".";
$cabecalho .= "          </td>";
$cabecalho .= "        </tr>";
$cabecalho .= "        <tr bgcolor='#DBDBB7'>";
$cabecalho .= "          <td width='6%'  align='center' class='tahomaSize_1_BP' color='#000000'><b>SEQ.</b></td>";
$cabecalho .= "          <td width='8%'  align='center' class='tahomaSize_1_BP' color='#000000'><b>MATR&Iacute;CULA</b></td>";
$cabecalho .= "          <td width='45%' class='tahomaSize_1_BP'><b>&nbsp; NOME</b></td>";
$cabecalho .= "          <td width='15%' align='center' class='tahomaSize_1_BP'><b>Horas devidas<br>(62014)</b></td>";
$cabecalho .= "          <td width='15%' align='center' class='tahomaSize_1_BP'><b>Horas excedentes<br>(92014)</b></td>";
$cabecalho .= "          <td width='16%' align='center' class='tahomaSize_1_BP'><b>Saldos</b></td>";
$cabecalho .= "        </tr>";

$sRodape = "      </table>";
$sRodape .= "    </td>";
$sRodape .= "  </tr>";
$sRodape .= "  <tr>";
$sRodape .= "    <td>";
$sRodape .= "      <p><font size='1'></font></p>";
$sRodape .= "    </td>";
$sRodape .= "  </tr>";
$sRodape .= "</table>";

$tam = count($aDadosEncontrados);

if ($tam > 0)
{
    $linhas_por_pagina = 42;
    $nPaginas          = ($tam > $linhas_por_pagina ? number_format(($tam / $linhas_por_pagina + 1), 0, ',', '.') : 1);
    $nPagina           = 1;

    for ($x = 0; $x < $tam; $x++)
    {
        if ($x == 0 || ($x % $linhas_por_pagina) == 0)
        {
            if ($x > 0)
            {
                $idInner .= $sRodape . '<pagebreak />';
            }
            $idInner .= $cabecalho;
            $nPagina++;
        }
        $idInner .= "<tr height='18'>";
        $idInner .= "<td align='center'>" . ($x + 1) . "</td>";
        $idInner .= "<td align='center'>" . tratarHTML($aDadosEncontrados[$x][0]) . "</td>";
        $idInner .= "<td align='left' nowrap>" . tratarHTML($aDadosEncontrados[$x][1]) . "</td>";
        $idInner .= "<td align='center'>" . tratarHTML($aDadosEncontrados[$x][2]) . "</td>";
        $idInner .= "<td align='center'>" . tratarHTML($aDadosEncontrados[$x][3]) . "</td>";
        $idInner .= "<td align='center'>" . tratarHTML($aDadosEncontrados[$x][4]) . "</td>";
        $idInner .= "</tr>";
    }
}
else
{
    $idInner .= $cabecalho;
}
$idInner .= $sRodape . "</body></html>";

$idInner = utf8_encode($idInner);

//print $idInner;
//die();
// salva a(s) paginas em arquivo
if ($nRowsArq == 0)
{
    //$oDBase->query( "INSERT relatorios_arquivos SET upag='$upag', tipo='copa2014', maquina='http://".$_SERVER['SERVER_ADDR']."/sisref/relatorios/', arquivo='$arquivo', data_arquivo='$data_hoje' " );
}
else
{
    //$oDBase->query( "UPDATE relatorios_arquivos SET maquina='http://".$_SERVER['SERVER_ADDR']."/sisref/relatorios/', arquivo='$arquivo' WHERE id = '$nId' " );
}

// memoria
$nMemoria = "80M";

// gera o arquivo em PDF
include_once( "gera_pdf.php" );

/*
  include_once("dompdf/dompdf_config.inc.php");

  $dompdf = new DOMPDF();
  $dompdf->load_html($idInner);
  $dompdf->set_paper('A4', 'portrait');
  $dompdf->render();
  $dompdf->stream("$arquivo");
 */
exit();
