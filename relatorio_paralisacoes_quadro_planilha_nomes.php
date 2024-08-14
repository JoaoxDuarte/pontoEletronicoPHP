<?php

/* _________________________________________________________________________*\
  |                                                                           |
  |		PREPARA OS DIVERSOS TIPOS DE RELATÓRIOS                                 |
  |                                                                           |
  \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */

include_once('config.php');
include_once('relatorio_paralisacoes_classes.php');
include_once( "class_ocorrencias_grupos.php" );

//verifica_permissao('estrategica');
session_start();

set_time_limit(0);

$sLotacao = $_SESSION["sLotacao"];

$oco_escolha   = anti_injection($_REQUEST["oco"]);
$escolha_und   = $_SESSION['sEscolha_und'];
$escolha_data  = $_SESSION['sEscolha_data'];
$escolha_cargo = $_SESSION['sEscolha_cargo'];

// instancia o banco de dados
$oDBase = new DataBase('PDO');

//
// dados básicos para seleção dos dados desejados
//
$data_inicial = $escolha_data;
$data_inicial = substr($data_inicial, 6, 4) . substr($data_inicial, 3, 2) . substr($data_inicial, 0, 2);
$data_final   = date('Ymd');

$data_escolhida_invertida = substr($escolha_data, 6, 4) . substr($escolha_data, 3, 2) . substr($escolha_data, 0, 2);

if (empty($escolha_data))
{
    $data_inicial = date('Ymd'); //substr($data_inicial,6,4).substr($data_inicial,3,2).substr($data_inicial,0,2);
    $data_final   = date('Ymd');
    $mes_do_ponto = date('mY');
}
else
{
    $data_inicial = $data_escolhida_invertida;
    $data_final   = $data_escolhida_invertida;
    $mes_do_ponto = substr($escolha_data, 3, 2) . substr($escolha_data, 6, 4);
}

/* _________________________________________________________________________*\
  |		SELEÇÃO DOS DADOS                                                       |
  \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */
$oSQL          = new sql_seleciona();
$oSQL->setUnidadeEscolhida($escolha_und);
$sqlServidores = $oSQL->servidores();
$oDBase->query($sqlServidores);
$nrows         = $oDBase->num_rows();


/* _________________________________________________________________________*\
  |		PREPARA OS DADOS PARA IMPRESSÃO/EXIBIÇÃO                                |
  \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */

// $dados_ponto[0]  : data
// $dados_ponto[1]  : siape
// $dados_ponto[2]  : nome do servidor
// $dados_ponto[3]  : ocorrência
// $dados_ponto[4]  : superintendencia
// $dados_ponto[5]  : codigo da gerencia
// $dados_ponto[6]  : gerencia
// $dados_ponto[7]  : codigo da unidade
// $dados_ponto[8]  : descricao da unidade
// $dados_ponto[9]  : codigo do cargo
// $dados_ponto[10] : descricao do cargo
// $dados_ponto[11] : hora de entrada oficial
// $dados_ponto[12] : hora de registro
$dados_ponto = array();

$dia = conv_data($escolha_data);
while (list($siape, $nome_serv, $nome_ger, $cod_gex, $nome_gex, $cod_lot, $cod_lot_descricao, $cod_cargo, $cargo_descricao, $sit_ocup, $cod_funcao, $entra_trab, $upag, $cod_uorg) = $oDBase->fetch_array())
{
    if (($sit_ocup == 'T' && ($cod_funcao == 'DAS1014' || $cod_funcao == 'DAS1015' || $cod_funcao == 'DAS1016' || $cod_funcao == 'DAS1024' || $cod_funcao == 'DAS1025' || $cod_funcao == 'DAS1026')) || $siape == '1287194' || $siape == '1286962')
    {

    }
    else
    {
        $oServidor = selecionaServidor($siape);
        $sitcad = $oServidor->fetch_object()->sigregjur;


        ## ocorrências grupos
        $obj = new OcorrenciasGrupos();
        $codigoSemFrequenciaPadrao  = $obj->CodigoSemFrequenciaPadrao($sitcad);

        // codigos a pesquisar
        $codigo_sem_frequencia = implode(',', $codigoSemFrequenciaPadrao); //'99999';


        $oSQL           = new sql_seleciona();
        $oSQL->setSiape($siape);
        $oSQL->setDiaEscolhido($dia);
        $oSQL->setCompetencia($mes_do_ponto);
        $oSQL->setOcorrenciaEscolhida($oco_escolha);
        $oSQL->setCargoEscolhido($escolha_cargo);
        $sqlOcorrencias = $oSQL->ocorrencias();
        $oDBase->query($sqlOcorrencias);
        $nPtos          = $oDBase->num_rows();
        list( $entra, $oco, $descricao ) = $oDBase->fetch_array();

        if ($nPtos == 0)
        {
            if ($oco_escolha == $codigo_sem_frequencia || $oco_escolha == 'total')
            {
                $dados_ponto[] = array(databarra($dia), $siape, $nome_serv, $codigo_sem_frequencia.' - SEM FREQUENCIA', $nome_ger, $cod_gex, $nome_gex, $cod_lot, $cod_lot_descricao, $cod_cargo, $cargo_descricao, $entra_trab, $entra);
            }
        }
        elseif ($oco == $oco_escolha || $oco_escolha == 'total')
        {
            $dados_ponto[] = array(databarra($dia), $siape, $nome_serv, $descricao, $nome_ger, $cod_gex, $nome_gex, $cod_lot, $cod_lot_descricao, $cod_cargo, $cargo_descricao, $entra_trab, $entra);
        }
    }
} // while

$fim_dados = count($dados_ponto);


/* _________________________________________________________________________*\
  |		R E L A T Ó R I O                                                       |
  \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */


/* _________________________________________________________________________*\
  |		INÍCIO DO HTML                                                          |
  \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */
$relatorio = "
	<html>
	<head>
	<meta http-equiv=Content-Type content='text/html; charset=windows-1252'>
	<meta name=ProgId content=Excel.Sheet>
	<meta name=Generator content='Microsoft Excel 12'>
	<link rel='stylesheet' type='text/css' href='" . _DIR_CSS_ . "estiloIE.css'>
	<style>
		.bairro {
				vertical-align: top;
				border:1pt solid #DEDEBC;
				font-family : Trebuchet MS;
				font-size: 10px;
				font-weight:bold;
				color: #000099;
		}
		.bairro2 {
				vertical-align: top;
				font-family : Trebuchet MS;
				font-size: 12px;
				font-weight:bold;
				color: #000099;
		}
	</style>
	</head>
	<body>
	<table>
	<tr>
	<td align='center'>
	<br>
	<table border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#A7A754; width: 790px;'>";

$cabecalho = "
	<tr style='height: 20px; vertical-align: middle;'>
	<td align='center' style='background-color:#DDDDFF; width: 7%;'><b>Siape</b></td>
	<td align='center' style='background-color:#DDDDFF; width: 35%;'><b>Nome</b></td>
	<td align='center' style='background-color:#DDDDFF; width: 48%;'><b>Ocorrência</b></td>
	<td align='center' style='background-color:#DDDDFF; width: 48%;'><b>Unidade</b></td>
	</tr>\n";

if ($nrows > 0)
{

    $nlinha   = 0;
    $nimoveis = 0;
    $nund     = 0;
    $ngex     = 0;
    $nger     = 0;
    $codger   = '';
    $codgex   = '';

    for ($i = 0; $i < $fim_dados; $i++)
    {
        $data              = $dados_ponto[$i][0];
        $siape             = $dados_ponto[$i][1];
        $nome_serv         = $dados_ponto[$i][2];
        $ocorrencia        = $dados_ponto[$i][3];
        $nome_ger          = retira_acentos($dados_ponto[$i][4]);
        $cod_gex           = $dados_ponto[$i][5];
        $nome_gex          = retira_acentos($dados_ponto[$i][6]);
        $cod_lot           = $dados_ponto[$i][7];
        $cod_lot_descricao = retira_acentos($dados_ponto[$i][8]);

        if ($i == 0)
        {
            $relatorio .= "<tr><td colspan='4' style='color: #004080; font-family: arial; font-size: 12; font-weight: bold;'>" . ($oco_escolha == 'total' ? "TODOS OS SERVIDORES" : tratarHTML($ocorrencia)) . "</td></tr>\n";
        }

        if ($codgex != $cod_gex || $codger != $id_ger)
        {
            if (($codgex != $cod_gex && $nome_ger != $nome_gex) || $codger != $nome_ger)
            {
                $relatorio .= "<tr><td colspan='4'>&nbsp;</td></tr>\n";
            }
            if ($codger != $nome_ger)
            {
                $relatorio .= "<tr><td colspan='4' style='background-color: #E2E2E2; font-family: arial; font-size: 10; font-weight: bold;'>".tratarHTML($nome_ger)."</td></tr>\n";
                $codger    = $id_ger;
            }
            if ($codgex != $cod_gex && $nome_ger != $nome_gex)
            {
                $relatorio .= "<tr><td colspan='4' style='background-color: #E2E2E2; font-family: arial; font-size: 10; font-weight: bold;'>".tratarHTML($nome_gex)."</td></tr>\n";
                $codgex    = $cod_gex;
            }
            $relatorio .= $cabecalho;
        }

        $relatorio .= "
			<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)'>
				<td class='bairro' align='center'>&nbsp;".tratarHTML($siape)."&nbsp;</td>
				<td class='bairro' nowrap>&nbsp;".tratarHTML($nome_serv)."&nbsp;</td>
				<td class='bairro' nowrap>&nbsp;".tratarHTML($ocorrencia)."&nbsp;</td>
				<td class='bairro' nowrap>&nbsp;".tratarHTML($cod_lot_descricao)."&nbsp;</td>
			</tr>";

        $nlinha++;
        $nimoveis++;
        $nger++;
        $ngex++;
        $nund++;

        $fim_quebra_ger = $nome_ger;
        $fim_quebra_gex = $nome_gex;
        $fim_quebra_und = $quebra_und_descricao;
    } // for
} // numrows

$relatorio .= "
	</table>
	<br>
	<span style='font-family:tahoma;font-size:9pt'>&nbsp;Nº de Servidores: <b>" . number_format($fim_dados, 0, ',', '.') . "</b></span>";

$arquivo = 'ocorrencia_resumo_' . tratarHTML($escolha_und) . '_' . tratarHTML($escolha_data) . '_' . tratarHTML($oco_escolha) . ".xls";

// Configurações header para forçar o download
header("Content-type: application/octet-stream");
header("Content-type: application/x-msexcel");
header("Content-Disposition: attachment; filename=$arquivo");
header("Content-Description: PHP Generated Data");

// Envia o conteúdo do arquivo
echo $relatorio;
exit;
