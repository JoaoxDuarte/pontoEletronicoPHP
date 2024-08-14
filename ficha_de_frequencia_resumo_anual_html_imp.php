<?php

include_once( "config.php" );

//verifica_permissao( 'logado' );
verifica_permissao('sRH ou Chefia');

set_time_limit(0);

// Le dados gravados em sessao
$mat                      = $_SESSION['sIMPMatricula'];
$ano                      = $_SESSION['sIMPAno'];
$caminho_modulo_utilizado = retira_acentos(str_replace("»", ">", $_SESSION['sIMPCaminho']));
$cmd                      = $_SESSION['sIMPCmd'];
$qlotacao                 = $_SESSION['sIMPLotacao'];
$wnomelota                = $_SESSION['sIMPLotacaoDescricao'];
$titulo_do_formulario     = $_SESSION['sIMPTituloFormulario'];
$pagina_de_origem         = $_SESSION['sIMPPaginaOrigem'];
$magico                   = $_SESSION['sIMPMagico'];

// le dados da frequencia historica
$oDBase = new DataBase('PDO');
$oDBase->setMensagem("Não foi possível emitir relatório com os dados informados");
$oDBase->setDestino("ficha_de_frequencia_resumo_anual.php?mat=$mat&ano=$ano");

// freq ANO
$oDBase->query("SELECT * FROM frq$ano WHERE mat_siape = :siape " , array(
    array( ':siape', $mat, PDO::PARAM_STR )
));

// cadastro
$oDBase->query("SELECT a.mat_siape, a.nome_serv, a.cod_lot, a.dt_adm, b.descricao, b.upag FROM servativ AS a LEFT JOIN tabsetor AS b ON a.cod_lot = b.codigo WHERE mat_siape = :siape ", array(
    array( ':siape', $mat, PDO::PARAM_STR )
));
$oServidor = $oDBase->fetch_object();
$r1        = $oServidor->mat_siape;
$r2        = $oServidor->nome_serv;
$r3        = $oServidor->cod_lot;
$r4        = databarra($oServidor->dt_adm);
$upg       = $oServidor->upag;
$wnomelota = $oServidor->descricao;

if ($upg != $_SESSION['upag'] && $_SESSION['sSenhaI'] != 'S')
{
    mensagem("Não é permitido visualizar ficha de frequência de servidor de outra UPAG!", "consfreq");
}

// Relatorios
$idInner = "<!doctype html public \"-//w3c//dtd html 4.01 transitional//pt\"><html lang=\"pt-br\"><head><title></title><meta http-equiv=\"Content-Language\" content=\"pt-br\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1252\"><link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "print3b.css' media='print'></style><link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "estilo.css'></style><link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "class_formpadrao.css'></style><link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "estiloIE.css'></style><script type='text/javascript' src='" . _DIR_JS_ . "funcoes.js'></script></head><body style='margin: 0px; background-color: #FFFFFF;'>";

$idInner .= "<style>.ftFormFreq-tit-bc { font-family: verdana,arial,tahoma; font-size: 7pt; font-weight: bold; color: #000000; background-color: #DFDFBF; text-align: center; }.ftFormFreq-tit-bc-3 { font-family: verdana,arial,tahoma; font-size: 7pt; font-weight: bold; color: #000000; background-color: #DFDFBF; text-align: center; }.ftFormFreq-bc-1 { font-size: 7.5pt; font-weight: bold; color: #000000; background-color: #DFDFBF; text-align: center; vertical-align: middle; }.ftFormFreq-cn-1 { font-family: verdana,arial,tahoma; font-size: 7.5pt; color: #000000; text-align: center; vertical-align: middle; }.ftFormFreq-cn-2 { font-family: verdana,arial,tahoma; font-size: 7.7pt; color: #000000; text-align: center; vertical-align: middle; }.ftFormFreq-c { font-size: 7.5pt; color: #000000; background-color: #FFFFFF; text-align: center; vertical-align: middle; }.ftFormFreq-bc { font-size: 7.5pt; color: #000000; font-weight: bold; background-color: #FFFFFF; text-align: center; vertical-align: middle; }.ftFormFreq-c-2 { font-size: 8pt; color: #000000; background-color: #FFFFFF; text-align: center; vertical-align: middle; }.ftFormFreq-c-3 { font-size: 9pt; color: #000000; background-color: #FFFFFF; text-align: center; vertical-align: middle; }.ftFormFreq-bc-2 { font-family: Tahoma,verdana,arial; font-size: 11pt; font-weight: bold; color: #333300; background-color: #FFFFFF; text-align: center; vertical-align: middle; }</style>";

$idInner .= "<table align='center' style='page-break-before: always'><tr><td><table align='center' style='width: 100%;' cellspacing='0' cellpadding='0'><tr><td>";

$idInner .= "<div align='center' style='width: 100%'><h3><table align='center' border='0' cellspacing='0' cellpadding='0' style='width: 100%;'><tr><td width='20%'>&nbsp;</td><td style='width: 60%; height: 55px; vertical-align: middle;'><p align='center' class='ft_18_001'>SISREF - Sistema de Registro Eletr&ocirc;nico de Frequ&ecirc;ncia</p></td><td width='20%'><img border='0' height='40' width='105' src='" . _DIR_IMAGEM_ . "transp1x1.gif'></td></tr><tr><td colspan='3' style='text-align: center; vertical-align: top; height: 40px;'><font class='ft_16_001'>" . $titulo_do_formulario . "</font></td></tr><tr><td colspan='3' align='center'><img src='" . _DIR_IMAGEM_ . "transp1x1.gif' style='height: 20px; border: 0px;'></td></tr></table></h3></div>";

$idInner .= "<table border='0' cellpadding='0' cellspacing='0' class='tablew2' style='width: 100%; text-align: center; border-collapse: collapse'><tr><td><table class='tablew21' width='100%' border='1' align='center' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse'><tr><td colspan='5' valign='middle' height='25' class='ftFormFreq-bc-2'>Ano: " . $ano . "</td></tr><tr><td height='20' class='ftFormFreq-tit-bc'>SIAPE</td><td height='20' class='ftFormFreq-tit-bc' width='54%' colspan='4'>NOME</td></tr><tr><td height='25' width='10%' align='center'><input type='text' id='siape' name='siape' class='centro' value='" . $r1 . "' size='15' readonly></td><td height='25' colspan='4' align='centro'>&nbsp;<input type='text' id='nome' name='nome' class='Caixa' value='" . $r2 . "' size='100' readonly>&nbsp;</td></tr><tr><td height='20' class='ftFormFreq-tit-bc'>ADMISSÃO</td><td height='20' class='ftFormFreq-tit-bc' width='54%' colspan='4'>UNIDADE</td></tr><tr><td height='25' width='10%' align='center'><input type='text' id='dt_adm' name='dt_adm' class='centro' value='" . $r4 . "' size='20' readonly></td><td height='25' colspan='4' align='centro'>&nbsp;<input type='text' id='nome' name='nome' class='Caixa' value='" . $r3 . '-' . $wnomelota . "' size='100' readonly>&nbsp;</td></tr></table></td></tr><tr><td>";

$idInner .= "<table class='tablew21' border='1' cellpadding='0' cellspacing='0' bordercolor='#808040' style=' width: 890; border-collapse: collapse;  text-align: center;'>";
$idInner .= "<tr>";
$idInner .= "<td class='ftFormFreq-bc-1' width='30' height='20'>&nbsp;DIAS/MÊS&nbsp;</td>";
$idInner .= "<td class='ftFormFreq-bc-1' nowrap>&nbsp;JAN&nbsp;</td>";
$idInner .= "<td class='ftFormFreq-bc-1' nowrap>&nbsp;FEV&nbsp;</td>";
$idInner .= "<td class='ftFormFreq-bc-1' nowrap>&nbsp;MAR&nbsp;</td>";
$idInner .= "<td class='ftFormFreq-bc-1' nowrap>&nbsp;ABR&nbsp;</td>";
$idInner .= "<td class='ftFormFreq-bc-1' nowrap>&nbsp;MAI&nbsp;</td>";
$idInner .= "<td class='ftFormFreq-bc-1' nowrap>&nbsp;JUN&nbsp;</td>";
$idInner .= "<td class='ftFormFreq-bc-1' nowrap>&nbsp;JUL&nbsp;</td>";
$idInner .= "<td class='ftFormFreq-bc-1' nowrap>&nbsp;AGO&nbsp;</td>";
$idInner .= "<td class='ftFormFreq-bc-1' nowrap>&nbsp;SET&nbsp;</td>";
$idInner .= "<td class='ftFormFreq-bc-1' nowrap>&nbsp;OUT&nbsp;</td>";
$idInner .= "<td class='ftFormFreq-bc-1' nowrap>&nbsp;NOV&nbsp;</td>";
$idInner .= "<td class='ftFormFreq-bc-1' nowrap>&nbsp;DEZ&nbsp;</td>";
$idInner .= "</tr>";

// seleciona os dados do ano selecionado
$siape = $r1;
include_once( "ficha_de_frequencia_resumo_anual_html_lista.php" );

$idInner .= "</table></td></tr></table></td></tr><tr><td><div class='ft_10_001' style='text-align: right; vertical-align: bottom;'><img src='" . _DIR_IMAGEM_ . "ip.gif' border='0' width='12' alt='IP - Computador'>" . getIpReal() . "</div></td></tr></table></td></tr></table></body></html>";

$idInner = utf8_encode($idInner);

// gera o arquivo em PDF
include_once( "gera_pdf.php" );
