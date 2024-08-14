<?php

include_once( "config.php" );
include_once( "class_form.frequencia.php" );

verifica_permissao("logado");


// le dados sessao para uso na impressao
$idInner = $_SESSION['sIMPExtratoFrequencia'];
$idInner = str_replace("<td style='width: 105px; border: 0px solid #808080;'><a id='prepara_impressao' title='Preparar Página para Impressão' href=\"javascript:var tela = window.open('veponto_saldos_imp.php','','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,maximized=yes,width=640, height=665');\"><img src='" . _DIR_IMAGEM_ . "print.gif' height=16 border='0'><br>Imprimir</a></td>", "", $idInner);
$idInner = str_replace("style='margin: 0px 0px 0px 0px; text-align: center; width: 100%;'", "style='margin: 0px 0px 0px 0px; text-align: left; width: 480px;'", $idInner);
$idInner = str_replace("<td width='20%'><img src='" . _DIR_IMAGEM_ . "transp1x1.gif' height='16' width='105px' border='0'></td>", "", $idInner);
$idInner .= "<script>window.print();</script>";

print $idInner;

//$idInner = utf8_encode($idInner);

	// gera o arquivo em PDF
	//include_once( "gera_pdf.php" );
