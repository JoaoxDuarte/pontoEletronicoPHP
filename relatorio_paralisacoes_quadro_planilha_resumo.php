<?php

/* _________________________________________________________________________*\
  |                                                                           |
  |		PREPARA OS DIVERSOS TIPOS DE RELATÓRIOS                                 |
  |                                                                           |
  \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */

include_once('config.php');
include_once( "class_ocorrencias_grupos.php" );

session_start();

$liberado = $_SESSION['liberado_acesso'];
$gravar   = $_SESSION['liberado_gravar'];

if ($liberado == '')
{
    mensagem('Por favor, realize o Login\npara ter acesso a este módulo!', 'relatorio_paralisacoes_login.php');
}

set_time_limit(10 * 60);

//
// le os valores passados pelo formulario
//
$escolha_und  = anti_injection($_REQUEST["unidade"]);
$escolha_data = anti_injection($_REQUEST["data"]);
$escolha_data = ($escolha_data == '' ? date('d/m/Y') : $escolha_data);

$_SESSION['sEscolha_und']  = $escolha_und;
$_SESSION['sEscolha_data'] = $escolha_data;

// instancia o banco de dados
$oDBase = new DataBase('PDO');

//
// dados básicos para seleção dos dados desejados
//
$data_inicial = '21/06/2010';

$data_inicial = substr($data_inicial, 6, 4) . substr($data_inicial, 3, 2) . substr($data_inicial, 0, 2);
$data_final   = date('Ymd');

$data_escolhida_invertida = substr($escolha_data, 6, 4) . substr($escolha_data, 3, 2) . substr($escolha_data, 0, 2);
$data_escolhida_mysql     = substr($escolha_data, 6, 4) . '-' . substr($escolha_data, 3, 2) . '-' . substr($escolha_data, 0, 2);

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

$dia = conv_data($escolha_data);

/* _________________________________________________________________________*\
  |		OCORRÊNCIAS REGISTRADAS                                                 |
  \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */
$oDBase->query("
SELECT
    a.siapecad as oco, a.desc_ocorr as descricao
FROM
    tabocfre As a
WHERE
    ativo = 'S'
ORDER BY
    a.siapecad
");
$nrows = $oDBase->num_rows();

$arrOcorr          = array();
$arrOcorr['total'] = array('TOTAL DE SERVIDORES', 0);
while ($oOcor             = $oDBase->fetch_object())
{
    $arrOcorr[$oOcor->oco] = array($oOcor->oco . ' - ' . $oOcor->descricao, 0);
}


/* _________________________________________________________________________*\
  |		SELEÇÃO DOS DADOS                                                       |
  \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */
$x = "
SELECT
    b.mat_siape, b.nome_serv, d.nome_ger, c.cod_gex, c.nome_gex, b.cod_lot, IF(IFNULL(g.lot_nsiape,9)=9,e.descricao,g.lot_nsiape) AS cod_lot_descricao, b.cod_cargo, f.desc_cargo AS cargo_descricao, i.sit_ocup, u.cod_funcao, b.entra_trab, e.upag, b.cod_uorg, d.id_ger
FROM
    servativ AS b
LEFT JOIN
    lotacao_nova AS g ON b.cod_uorg = g.uorg_anterior
LEFT JOIN
    dados_gex AS c ON IF(SUBSTR(b.cod_lot,4,2)='00',CONCAT(LEFT(b.cod_lot,2),'001'),IF(SUBSTR(b.cod_lot,3,3)='150',LEFT(b.cod_lot,5),CONCAT(LEFT(b.cod_lot,2),'0',SUBSTR(b.cod_lot,4,2)))) = c.cod_gex
LEFT JOIN
    dados_ger AS d ON c.regional = d.id_ger
LEFT JOIN
    tabsetor AS e ON b.cod_lot = e.codigo AND e.ativo = 'S'
LEFT JOIN
    tabcargo AS f ON b.cod_cargo = f.cod_cargo
LEFT JOIN
    ocupantes AS i ON b.mat_siape = i.mat_siape
LEFT JOIN
    tabfunc AS u ON i.num_funcao = u.num_funcao
WHERE
    b.excluido = 'N'
    AND b.cod_sitcad NOT IN ('08','15','02')
";

if (!empty($escolha_und))
{
    if (substr($escolha_und, 0, 1) == 's')
    {
        $x .= "AND d.id_ger = '" . substr($escolha_und, 1, 1) . "' ";
    }
    elseif (substr($escolha_und, 0, 1) == 'g')
    {
        $x .= "AND e.upag = '" . substr($escolha_und, 1, 9) . "' ";
    }
    else
    {
        $x .= "AND b.cod_uorg = '$escolha_und' ";
    }
}
if (!empty($escolha_cargo))
{
    switch ($escolha_cargo)
    {
        case 'medico': $x .= "AND f.desc_cargo LIKE '%medico%' ";
            break;
        case 'analista': $x .= "AND f.desc_cargo LIKE '%analista do seguro social%' ";
            break;
        case 'tecnico': $x .= "AND f.desc_cargo LIKE '%tecnico do seguro social%' ";
            break;
        default: $x .= "AND f.cod_cargo = '$escolha_cargo' ";
            break;
    }
}
$x .= "
	GROUP BY b.mat_siape
	ORDER BY d.id_ger, IF(LEFT(b.cod_lot,2)='01',0,IF(SUBSTR(b.cod_lot,3,3)='150',1,2)), b.cod_lot, b.nome_serv ";

$oDBase->query($x);
$nrows = $oDBase->num_rows();

/* _________________________________________________________________________*\
  |		PREPARA OS DADOS PARA IMPRESSÃO/EXIBIÇÃO                                |
  \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */
$nome_regional = '';
$nome_gerencia = '';
$dia           = substr($escolha_data, 6, 4) . '-' . substr($escolha_data, 3, 2) . '-' . substr($escolha_data, 0, 2);
while (list($siape, $nome_serv, $nome_ger, $cod_gex, $nome_gex, $cod_lot, $cod_lot_descricao, $cod_cargo, $cargo_descricao, $sit_ocup, $cod_funcao, $entra_trab, $upag, $cod_uorg, $id_ger) = $oDBase->fetch_array())
{
    if (($sit_ocup == 'T' && ($cod_funcao == 'DAS1014' || $cod_funcao == 'DAS1015' || $cod_funcao == 'DAS1016' || $cod_funcao == 'DAS1024' || $cod_funcao == 'DAS1025' || $cod_funcao == 'DAS1026')))
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


        $nome_regional = $nome_ger;
        $nome_gerencia = $nome_gex;
        $entra         = '';
        $x             = "
			SELECT a.entra, a.oco
			FROM ponto$mes_do_ponto AS a
			WHERE a.dia = '$dia' AND a.siape='$siape'
			ORDER BY a.siape, a.dia ";
        $oDBase->query($x);
        $nPtos         = $oDBase->num_rows();
        list( $entra, $oco ) = $oDBase->fetch_array();

        $arrOcorr['total'][1] = $arrOcorr['total'][1] + 1;

        if ($nPtos == 0 || $oco == $codigo_sem_frequencia)
        {
            $arrOcorr[$codigo_sem_frequencia][1] = $arrOcorr[$codigo_sem_frequencia][1] + 1;
            $oco                  = $codigo_sem_frequencia;
        }
        else
        {
            $arrOcorr[$oco][1] = $arrOcorr[$oco][1] + 1;
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
$html = "
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
	<table border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#A7A754; width: 600px;'>";

if ($escolha_und != '')
{
    $html .= "
		<tr style='font-size: 10px;'>
			<td colspan='2' nowrap><b>&nbsp;Superintendência:</b>&nbsp;" . tratarHTML($nome_regional) . "</td>
		</tr>";

    if (strlen(ltrim(rtrim($escolha_und))) != 2)
    {
        $html .= "
			<tr style='font-size: 10px;'>
				<td colspan='2' nowrap><b>Gerência Executiva:</b>&nbsp;" . tratarHTML($nome_gerencia) . "</td>
			</tr>";
    }
}

$html .= "
	<tr style='background-color: #C6C6FF; font-size: 10px; font-weight: bold;'>
		<td colspan='2' nowrap>&nbsp;&nbsp;OCORRÊNCIAS</td>
	</tr>";

$nlinha    = 0;
$fim_dados = count($arrOcorr);
foreach ($arrOcorr as $cod_ocor => $dados)
{
    $nlinha++;
    $ocorrência = retira_acentos($dados[0]);
    $total      = $dados[1];

    if ($total > 0)
    {
        $html .= "<tr>";
        $html .= "<td class='bairro' nowrap>&nbsp;".tratarHTML($ocorrência)."&nbsp;</td>";
        $html .= "<td class='bairro' align='right'>&nbsp;" . number_format(tratarHTML($total), 0, ',', '.') . "&nbsp;&nbsp;</td>";
        $html .= "</tr>";
    }
} // foreach

$html .= "
	</table>
	</td>
	</tr>
	</table>";

$arquivo = 'medicos_greve_resumo_' . $escolha_und . '_' . $escolha_data . ".xls";

// Configurações header para forçar o download
header("Content-type: application/octet-stream");
header("Content-type: application/x-msexcel");
header("Content-Disposition: attachment; filename=$arquivo");
header("Content-Description: PHP Generated Data");

// Envia o conteúdo do arquivo
echo $html;
exit;
