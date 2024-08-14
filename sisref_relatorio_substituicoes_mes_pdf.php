<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// permissao de acesso
verifica_permissao("sRH");

set_time_limit(0);

// instancia banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setMensagem("Não foi possível selecionar dados da substituição");

// arquivo
$oDBase->query($_SESSION['sisref_relatorio']);
$num = $oDBase->num_rows();

// dados da gerencia executiva
select_dadosgex($_SESSION['sLotacao'], $codgex, $nomegex, $ufgex, $idger);

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
		<table border='0' cellspacing='0' cellpadding='0' style='margin: 0px 0px 0px 0px; text-align: center; width: 100%;'>
		<tr>
		<td><p align='center' class='ft_18_001'>SISREF - Sistema de Registro Eletrônico de Frequência</p></td>
		</tr>
		</table>
		<table align='center' border='0' width='100%' cellspacing='0' cellpadding='0'>
		<tr><td align='center'><font class='ft_16_001'>Relatório de Substituições</font></td></tr>
		<tr><td align='center'><img src='" . _DIR_IMAGEM_ . "transp1x1.gif' height='20' border='0'></td></tr>
		</table>
		<fieldset align='center' class='ft_10_001' style='width: 820; text-align: center;'>Emitido em: " . date('d/m/Y') . "</fieldset>
		<table border='1' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#808040' width='100%' id='AutoNumber1'>
		<tr><td width='100%'  align='center' style='font-family:verdana; font-size:12pt'><font size='2' face='Tahoma'>
		" . $codgex . " - " . $nomegex . "
		</font></td></tr>
		<tr><td width='100%'  align='center' style='font-family:verdana; font-size:12pt'><font size='2' face='Tahoma'>
		" . $_SESSION['sisref_relatorio_mes'] . "&nbsp;&nbsp;Ano " . $_SESSION['sisref_relatorio_ano'] . "
		</font></td></tr>
		</table>
	";

$sub_titulo_topo = "
		<table border='1' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#F1F1E2' width='100%' id='AutoNumber2'>
		<tr bgcolor='#DBDBB7'>
			<td width='12%' align='center' height='18'><b>Matr&iacute;cula</td>
			<td width='50%'><b>&nbsp;NOME</td>
			<td width='11%'><div align='center'><strong>Fun&ccedil;&atilde;o</strong></div></td>
			<td width='14%' align='center'><b>Inicio</a></td>
			<td width='13%' align='center'><b>Fim</a></td>
		</tr>
	";

$sub_titulo_base = "
		</table>
	";

$titulo_base = "
		</table>
		<table align='center' border='0' width='100%' cellspacing='0' cellpadding='0'>
		<tr><td align='left'>
		<font size='1'>Obs: O relat&oacute;rio demonstra os registros de substitui&ccedil;&atilde;o que se iniciaram na compet&ecirc;ncia informada.</font>
		</td></tr>
		</table>
	";

// monta o relatorio
if ($num > 0)
{
    $linhas_por_pagina = 72;
    $nPaginas          = ($tam > $linhas_por_pagina ? number_format(($tam / $linhas_por_pagina + 1), 0, ',', '.') : 1);
    $nPagina           = 1;

    $x = 0;

    while ($pm = $oDBase->fetch_object())
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
        $x++;
        $idInner .= "<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)' height='18'>";
        $idInner .= "<td align='center' class='tahomaSize_1'>" . tratarHTML($pm->siape) . "</td>";
        $idInner .= "<td align='left'   class='tahomaSize_1'>&nbsp;" . tratarHTML($pm->nome_serv) . "</td>";
        $idInner .= "<td align='center' class='tahomaSize_1'>" . tratarHTML($pm->sigla) . "</td>";
        $idInner .= "<td align='center' class='tahomaSize_1'>" . tratarHTML($pm->inicio) . "</td>";
        $idInner .= "<td align='center' class='tahomaSize_1'>" . tratarHTML($pm->fim) . "</td>";
        $idInner .= "</tr>";
    }
}
else
{
    $idInner .= $titulo_topo . $sub_titulo_topo;
    $nPagina++;
    $idInner .= "<tr><td style='text-align: center; vertical-align: middle; height: 25px;' colspan='5'><font face='verdana' size='2'>N&atilde;o h&aacute; servidores em substituição!</font></td></tr>";
}

$idInner .= $sub_titulo_base . $titulo_base;

// Base do formulário
//
$idInner .= "
	</body>
	</html>";

//$idInner = utf8_encode($idInner);
// memoria
//$nMemoria = "120M";
// gera o arquivo em PDF
include_once( "gera_pdf.php" );
