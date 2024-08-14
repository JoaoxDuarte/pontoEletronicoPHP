<?php

include_once( "config.php" );

//verifica_permissao( "logado" );


set_time_limit(0);

// Le dados gravados em sessao
$texto   = "</fieldset>";
$idInner = $_SESSION['sIMPFormFrequencia'];
$pos     = strpos($idInner, $texto);
if ($pos === false)
{
    
}
else
{
    $idInner = substr($idInner, ($pos + strlen($texto)), strlen($idInner));
}
$idInner = str_replace("<!doctype html public \"-//w3c//dtd html 4.01 transitional//pt\"><html lang='pt-br'><head><title></title><meta http-equiv='Content-Language' content='pt-br'><meta http-equiv='Content-Type' content='text/html; charset=windows-1252'>", "", $idInner);
$idInner = str_replace("</head><body style='margin: 0px; background-color: #FFFFFF;'><script type='text/javascript' src=\"" . _DIR_JS_ . "menu/frames_body_array.js\"></script><script type='text/javascript' src=\"" . _DIR_JS_ . "menu/mmenu.js\" type=text/javascript></script>", "", $idInner);
$idInner = str_replace("</body></html>", "", $idInner);
$idInner = str_replace("<div id='dialog-saldos' title='Extrato Frequência' style='display: none; margin: 3px;'></div></td></tr></table><table><tr><td>", "", $idInner);
$idInner = str_replace("<a id='prepara_impressao' title='Preparar Página para Impressão' href=\"javascript:var tela = window.open('veponto_formulario_imp2.php','','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,maximized=yes,width=865, height=665');\"><img  src='" . _DIR_IMAGEM_ . "printer.gif' height=40 border='0'></a>", "", $idInner);
$idInner = str_replace("<fieldset class='fieldsetw' align='center' style='width: 790; word-spacing: 0; margin: 0px 0px 0px 0px;'>", "", $idInner);
$idInner = str_replace("<fieldset align='center' style='width: 790' valign='middle'>", "", $idInner);
$idInner = str_replace("</fieldset>", "", $idInner);
$idInner = str_replace("size='10'", "size='20'", $idInner);
$idInner = str_replace("size='65'", "size='150'", $idInner);
$idInner = str_replace("size='100'", "size='150'", $idInner);
$idInner = str_replace("size='8'", "size='20'", $idInner);
//print $idInner;
// gera o arquivo em PDF
include_once( "gera_pdf2.php" );
