<?php

// funcoes de uso geral
include_once( "config.php" );

set_time_limit(0);

// Verifica se o usuário tem a permissão para acessar este módulo
// Inicializa a sessão (session_start)
verifica_permissao('sRH ou sTabServidor');

// ler dados gravados em sessao para uso na impressao
$pagina_de_origem         = $_SESSION['sIMPPaginaOrigem'];
$year                     = $_SESSION['sIMPYear'];
$comp                     = $_SESSION['sIMPComp'];
$upag                     = $_SESSION['sIMPUpag'];
$sLotacao                 = $_SESSION['sIMPLotacao'];
$wnomelota                = $_SESSION['sIMPLotacaoDescricao'];
$caminho_modulo_utilizado = utf8_encode(str_replace('»', '>', $_SESSION['sIMPCaminho']));

// data de hoje para o arquivo
$data_hoje = date("Y-m-d");

// instancia banco de dados
$oDBase = new DataBase('PDO');

// arquivo
$oDBase->query("SELECT id, compet, upag, tipo, maquina, arquivo FROM relatorios_arquivos WHERE upag = '$upag' AND tipo = 'siapecad' AND data_arquivo='$data_hoje' AND compet='$year$comp' ORDER BY upag, arquivo DESC ");
$nRowsArq = $oDBase->num_rows();
$oArquivo = $oDBase->fetch_object();
$nId      = $oArquivo->id;

// define um nome para o arquivo PDF
$arquivo = 'comando_siapecad_' . $upag . "-" . $year . $comp . "-" . date("Ymd") . ($nRowsArq == 0 ? '_1' : '_2') . '.pdf';


// Início do HTML
$idInner = "
	<!doctype html public '-//w3c//dtd html 4.01 transitional//pt'>
	<html lang='pt-br'>
	<head>
	<title></title>
	<meta http-equiv='Content-Language' content='pt-br'>
	<meta http-equiv='Content-Type' content='text/html; charset=windows-1252'>
	<link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "print3.css' media='print'></style>
	<link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "estilo.css'></style>
	<link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "class_formpadrao.css'></style>
	<link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "estiloIE.css'></style>
	<link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "table_sorter.css'></style>
	</head>
	<body style='margin: 0px; background-color: #FFFFFF;'>";

$titulo_topo = "
	<table border='0' cellpadding='0' cellspacing='0' align='center' style='width: 100%;'>
	<tr><td colspan='{ColSpan}' style='height: 40px; vertical-align: middle; text-align: center;' class='tahomaSISREF_4'>SISREF - Sistema de Registro Eletrônico de Frequência</td></tr>
	<tr><td colspan='{ColSpan}' style='height: 35px; vertical-align: middle; text-align: center;' class='tahomaSISREF_4'><small>" . $_SESSION['sIMPTituloFormulario'] . "</small><br><br></td></tr>
	<tr><td colspan='{ColSpan}' style='font-family:verdana; font-size:11pt;height: 20px;  border: 0px solid #000000; text-align: center;'>&nbsp;</td></tr>
	<tr><td colspan='{ColSpan}' style='font-family:verdana; font-size:11pt;height: 15px;  border: 1px solid #e0e0e0; text-align: center;'><small>Emitido em: " . date('d/m/Y') . "</small></td></tr>
	<tr><td colspan='{ColSpan}' style='font-family:verdana; font-size:11pt;height: 5px;  border: 0px solid #e0e0e0; text-align: center;'></td></tr>
	<tr><td colspan='{ColSpan}' style='font-family:verdana; font-size:11pt;height: 20px;  border: 1px solid #000000; text-align: center;'>M&ecirc;s " . $comp . "&nbsp;&nbsp;Ano " . $year . "</td></tr>
	<tr><td colspan='{ColSpan}' style='font-family: Tahoma, verdana; font-size:10pt; height: 20px; border: 1px solid #000000; text-align: center;'>Lota&ccedil;&atilde;o <font style='border: 1px solid #000000; height: 30px;'>" . $sLotacao . "- " . $wnomelota . "</font></td></tr>
	<tr><td colspan='{ColSpan}'>";

$sub_titulo_topo_colunas = 8;
$sub_titulo_topo         = "
	<table border='1' cellpadding='0' cellspacing='0' style='border-collapse: collapse; width: 100%;' bordercolor='#F1F1E2'>
	<tr bgcolor='#DBDBB7'><td colspan='$sub_titulo_topo_colunas' align='center' style='height: 20px; padding: 4px;'>Servidores com ocorr&ecirc;ncia em " . $comp . "/" . $year . " que geram perda de remunera&ccedil;&atilde;o.</td></tr>
	<tr bgcolor='#DBDBB7'>
		<td style='width: 5px;   text-align: center;'><b>Seq.</b></td>
		<td style='width: 70px;  text-align: center;'><b>Matr&iacute;cula</b></td>
		<td style='width: 450px; text-align: center;'><b>NOME<b></td>
		<td style='width: 110px; text-align: center;'><b>C&oacute;digo Siapecad<b></td>
		<td style='width: 90px;  text-align: center;'><b>C&oacute;digo siape<b></td>
		<td style='width: 75px;  text-align: center;'><b>Data Inicial<b></td>
		<td style='width: 70px;  text-align: center;'><b>Data Final<b></td>
		<td style='width: 35px;  text-align: center;'><b>Dias<b></td>
	</tr>";

$sub_titulo_base = "
	</table>";

$titulo_base = "
	</td></tr>
	<tr><td><p><font size='1'>Obs: Os dados somente s&atilde;o exibidos nesse relat&oacute;rio ap&oacute;s fechado o mes para atualiza&ccedil;&atilde;o.</font></p></td></tr>
	</table>";

// monta o relatorio

$aDadosEncontrados = $_SESSION['saDadosEncontradosI'];
usort($aDadosEncontrados, "ordenaMultiArray");

$contar = 1;
$tam    = count($aDadosEncontrados);

if ($tam > 0)
{
    $linhas_por_pagina = 72;
    $nPaginas          = ($tam > $linhas_por_pagina ? number_format(($tam / $linhas_por_pagina + 1), 0, ',', '.') : 1);
    $nPagina           = 1;
    for ($x = 0; $x < $tam; $x++)
    {
        if ($x == 0 || ($x % $linhas_por_pagina) == 0)
        {
            if ($x > 0)
            {
                $idInner .= $sub_titulo_base . $titulo_base . '<pagebreak />';
            }
            $idInner .= str_replace('{ColSpan}', $sub_titulo_topo_colunas, $titulo_topo) . $sub_titulo_topo;
            $nPagina++;
        }
        $idInner .= "<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)' height='18'>";
        $idInner .= "<td align='center' class='tahomaSize_1'>" . number_format($contar++, 0, ',', '.') . "</td>";
        $idInner .= "<td align='center' class='tahomaSize_1'>" . tratarHTML($aDadosEncontrados[$x][0]) . "</td>";
        $idInner .= "<td align='left'   class='tahomaSize_1'>&nbsp;" . tratarHTML($aDadosEncontrados[$x][1]) . "</td>";
        $idInner .= "<td align='center' class='tahomaSize_1'>" . tratarHTML($aDadosEncontrados[$x][2]) . "</td>";
        $idInner .= "<td align='center' class='tahomaSize_1'>" . tratarHTML($aDadosEncontrados[$x][3]) . "</td>";
        $idInner .= "<td align='center' class='tahomaSize_1'>" . tratarHTML($aDadosEncontrados[$x][4]) . "</td>";
        $idInner .= "<td align='center' class='tahomaSize_1'>" . tratarHTML($aDadosEncontrados[$x][5]) . "</td>";
        $idInner .= "<td align='center' class='tahomaSize_1'>" . tratarHTML($aDadosEncontrados[$x][6]) . "</td>";
        $idInner .= "</tr>";
    }
}
else
{
    $idInner .= str_replace('{ColSpan}', $sub_titulo_topo_colunas, $titulo_topo) . $sub_titulo_topo;
    $nPagina++;
    $idInner .= "<tr><td style='text-align: center; vertical-align: middle; height: 25px;' colspan='" . $sub_titulo_topo_colunas . "'><font face='verdana' size='2'>N&atilde;o h&aacute; servidores com ocorr&ecirc;ncia!</font></td></tr>";
}

$idInner .= $sub_titulo_base . $titulo_base;

// Base do formulário
//
$idInner .= "
	</body>
	</html>";

$idInner = utf8_encode($idInner);

// salva a(s) paginas em arquivo
if ($nRowsArq <= 1)
{
    $oDBase->query("INSERT relatorios_arquivos SET compet='$year$comp', upag='$upag', tipo='siapecad', maquina='http://" . $_SERVER['SERVER_ADDR'] . "/sisref/relatorios/', arquivo='$arquivo', data_arquivo='$data_hoje' ");
}
else
{
    $oDBase->query("UPDATE relatorios_arquivos SET maquina='http://" . $_SERVER['SERVER_ADDR'] . "/sisref/relatorios/', arquivo='$arquivo' WHERE id = '$nId' ");
}

// memoria
$nMemoria = "120M";

// gera o arquivo em PDF
include_once( "gera_pdf.php" );


function ordenaMultiArray($a, $b)
{
    return strcmp($a[1] . $a[5], $b[1] . $b[5]);

}
