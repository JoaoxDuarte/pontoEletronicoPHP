<?php

// memoria
$nMemoria       = (isset($nMemoria) ? $nMemoria : "64M");
$retira_acentos = (isset($retira_acentos) ? $retira_acentos : false);

// define informacoes para o uso da mPDF50
define('_MPDF_URI', 'app.lib/mpdf/'); // must be  a relative or absolute URI - not a file system path
ini_set('display_errors', '0');
ini_set("memory_limit", $nMemoria);

/** Coloque essas duas linhas no cabeçalho do site * */
ini_set('zlib.output_compression', 'On');
ini_set('zlib.output_compression_level', '1');

include_once( "app.lib/mpdf/mpdf.php" );

// cria um novo container PDF no formato A4
$mpdf        = new mPDF('pt-BR', 'A4');
$mpdf->debug = true;

//Algumas configurações do PDF
$mpdf->SetDisplayMode('fullpage');
$mpdf->SetCompression(true);

// permite a conversao (opcional)
$mpdf->allow_charset_conversion = true;
$mpdf->charset_in               = 'UTF-8';
$mpdf->SetWatermarkImage(_DIR_IMAGEM_ . 'sisref.gif', 0.15, 'F');
$mpdf->showWatermarkImage       = false;

// barra de progresso na criacao do PDF
//$mpdf->progbar_heading = utf8_encode('SISREF - Gerando o relatório em PDF');
$mpdf->progbar_heading = utf8_encode('SISREF - Gerando o relat&oacute;rio em PDF');
if ($tipoStartProgressBarOutput == '' || $tipoStartProgressBarOutput == '0')
{
    $mpdf->StartProgressBarOutput();
}
else
{
    $mpdf->StartProgressBarOutput($tipoStartProgressBarOutput);
}

// topo e rodape da página
$mpdf->SetHeader("$caminho_modulo_utilizado|| ({PAGENO}/{nb})");
$mpdf->SetFooter("{DATE j/m/Y} {DATE H:i}|$arquivo|Fonte: SISREF");

if ($retira_acentos == false)
{
    $mpdf->WriteHTML($idInner);
}
else
{
    //$mpdf->WriteHTML( retira_acentos($idInner) );
    $mpdf->WriteHTML(retira_acentos(ajustar_acentos($idInner)));
}

if (!empty($arquivo))
{
    $mpdf->Output("{$_SERVER['DOCUMENT_ROOT']}/relatorios/{$arquivo}", 'F');
}

$mpdf->Output();

exit();
