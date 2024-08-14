<?php

include_once( "config.php" );
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao("logado");


set_time_limit(180);

$dados = $_REQUEST['dados'];

// Le dados gravados em sessao
$pagina_de_origem = $_SESSION['sIMPPaginaOrigem'];
$pSiape           = $_SESSION['sIMPMatricula'];
$nome             = $_SESSION['sIMPNome'];
$mes              = $_SESSION['sIMPMes'];
$ano              = $_SESSION['sIMPAno'];
$mes2             = $_SESSION['sIMPMes2'];
$ano2             = $_SESSION['sIMPAno2'];
$mes_inicio       = $_SESSION['sIMPMes_Inicio'];
$mes_final        = $_SESSION['sIMPMes_Final'];
$comp_final       = $_SESSION['sIMPComp_Final'];
$cmd              = $_SESSION['sIMPCmd'];
$lot              = $_SESSION['sIMPLotacao'];
$lot_descricao    = $_SESSION['sIMPLotacaoDescricao'];
$magico           = $_SESSION['sIMPMagico'];

$anomes_admissao    = $_SESSION['sIMPAnoMes_admissao'];
$anomes_exclusao    = $_SESSION['sIMPAnomes_exclusao'];
$situacao_cadastral = $_SESSION['sIMPSituacao_cadastral'];

$banco_compensacao      = $_SESSION['sIMPBanco_compensacao'];
$banco_compensacao_tipo = $_SESSION['sIMPBanco_compensacao_tipo'];

$processo_hespecial = $_SESSION['sIMPProcesso_hespecial'];
$data_hespecial     = $_SESSION['sIMPData_hespecial'];
$hora_especial      = $_SESSION['sIMPHora_especial'];

$horario_do_setor_inicio = $_SESSION['sIMPHorario_do_setor_inicio'];
$horario_do_setor_fim    = $_SESSION['sIMPHorario_do_setor_fim'];

$entrada_no_servico  = $_SESSION['sIMPEntrada_no_servico'];  // horário estabelecido de entrada ao serviço
$saida_do_servico    = $_SESSION['sIMPSaida_do_servico'];    // horário estabelecido do término do almoço
$saida_para_o_almoco = $_SESSION['sIMPSaida_para_o_almoco']; // horário estabelecido de saída (fim do expediente)
$volta_do_almoco     = $_SESSION['sIMPVolta_do_almoco'];     // horário estabelecido do início do almoço

$caminho_modulo_utilizado = retira_acentos(str_replace("»", ">", $_SESSION['sIMPCaminho']));
$titulo_do_formulario     = $_SESSION['sIMPTituloFormulario'];


//instancia o banco de dados
$oDBase = new DataBase('PDO');


$comp_admissao = substr($anomes_admissao, 6, 4) . substr($anomes_admissao, 3, 2);
$comp_exclusao = substr($anomes_exclusao, 6, 4) . substr($anomes_exclusao, 3, 2);

// Relatorios
$idInner = "
	<!doctype html public \"-//w3c//dtd html 4.01 transitional//pt\">
	<html lang=\"pt-br\">
	<head>
	<title></title>
	<meta http-equiv=\"Content-Language\" content=\"pt-br\">
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1252\">
	<link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "estilo.css'></style>
	<link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "class_formpadrao.css'></style>
	<style>
	.ftFormFreq-tit-bc { font-family: verdana,arial,tahoma; font-size: 7pt; font-weight: bold; color: #000000; background-color: #DFDFBF; text-align: center; }
	.ftFormFreq-tit-bc-3 { font-family: verdana,arial,tahoma; font-size: 7pt; font-weight: bold; color: #000000; background-color: #DFDFBF; text-align: center; }
	.ftFormFreq-bc-1 { font-size: 7.5pt; font-weight: bold; color: #000000; background-color: #DFDFBF; text-align: center; vertical-align: middle; }
	.ftFormFreq-cn-1 { font-family: verdana,arial,tahoma; font-size: 7.5pt; color: #000000; text-align: center; vertical-align: middle; }
	.ftFormFreq-cn-2 { font-family: verdana,arial,tahoma; font-size: 7.7pt; color: #000000; text-align: center; vertical-align: middle; }
	.ftFormFreq-c { font-size: 7.5pt; color: #000000; background-color: #FFFFFF; text-align: center; vertical-align: middle; }
	.ftFormFreq-bc { font-size: 7.5pt; color: #000000; font-weight: bold; background-color: #FFFFFF; text-align: center; vertical-align: middle; }
	.ftFormFreq-c-2 { font-size: 8pt; color: #000000; background-color: #FFFFFF; text-align: center; vertical-align: middle; }
	.ftFormFreq-c-3 { font-size: 9pt; color: #000000; background-color: #FFFFFF; text-align: center; vertical-align: middle; }
	.ftFormFreq-bc-2 { font-family: Tahoma,verdana,arial; font-size: 11pt; font-weight: bold; color: #333300; background-color: #FFFFFF; text-align: center; vertical-align: middle; }

	.borderTRBL { border-top:1px solid #808040;border-right:1px solid #808040;border-bottom:1px solid #808040;border-left:1px solid #808040; }
	.borderRBL  { border-right:1px solid #808040;border-bottom:1px solid #808040;border-left:1px solid #808040; }
	.borderRB   { border-right:1px solid #808040;border-bottom:1px solid #808040; }
	.borderBL   { border-bottom:1px solid #808040;border-left:1px solid #808040; }

	</style>
	<script type='text/javascript' src='" . _DIR_JS_ . "funcoes.js'></script>
	</head>
	<body style='margin: 0px; background-color: #FFFFFF;'>
	<center>";

//$ano = 2009;
$quebra   = "";
$contador = 0;

for ($i = $mes_inicio; $i <= 13; $i++)
{

    if ($i == 13)
    {
        $ano++;
        $i = 1;
    }

    $mes            = substr("00" . $i, -2);
    $comp           = $mes . $ano;
    $comp_invertida = $ano . $mes;

    if ($comp_invertida > $comp_final)
    {
        break;
    }
    elseif ($comp_admissao > $comp_invertida)
    {
        continue;
    }

    $sem_registros_para_exibir = "Sem registros para exibir!";
    if ($comp_admissao > $comp_invertida && $i < $mes_final)
    {
        continue;
    }

    $quebra = (empty($quebra) ? 'nao' : 'sim');

    //obtem dados da homologação
    $status = str_replace("Ã", "&Atilde;", verifica_se_mes_homologado($pSiape, $ano . $mes));


    //$rpont = "SELECT  entra, date_format(dia, '%d/%m/%Y') as dia, intini, intsai, sai, jornd, jornp, jorndif, oco, just, idreg FROM ponto$comp USE INDEX (siape) WHERE siape = '$pSiape' order by dia";
    $oDBase->setMensagem("VePonto: Erro no acesso ao banco de dados!\\nPor favor, tente mais tarde.");
    $oDBase->query("
    SELECT
        pto.entra,
        DATE_FORMAT(pto.dia, '%d/%m/%Y') AS dia,
        pto.intini,
        pto.intsai,
        pto.sai,
        pto.jornd,
        pto.jornp,
        pto.jorndif,
        pto.oco,
        pto.just,
        pto.idreg,
        tabocfre.desc_ocorr AS dcod,
        tabsetor.codmun,
        tabsetor.codigo,
        pto.idreg,
        pto.ip,
        pto.matchef,
        pto.siaperh,
        servativ.sigregjur
    FROM
        ponto$comp AS pto
    LEFT JOIN
        tabocfre ON pto.oco = tabocfre.siapecad
    LEFT JOIN
        servativ ON pto.siape = servativ.mat_siape
    LEFT JOIN
        tabsetor ON servativ.cod_lot = tabsetor.codigo
    WHERE
        pto.siape = :siape
        AND pto.dia <> '0000-00-00'
    ORDER BY
        pto.dia
    ",
    array(
        array(':siape', $pSiape, PDO::PARAM_STR)
    ));


    if ($i != $mes_inicio)
    {
        $idInner .= "<pagebreak />";
    }

    $idInner .= "<table style='width: 790px; text-align: center;' cellspacing='0' cellpadding='0'>
		<tr><td>";
    $idInner .= "<table style='width: 100%; text-align: center;' cellspacing='0' cellpadding='0'>
		<tr><td>";

    $idInner .= "<div align='center' style='width: 100%'><h3><table align='center' border='0' cellspacing='0' cellpadding='0' style='width: 100%;'><tr><td width='20%'>&nbsp;</td><td style='width: 60%; height: 55px; vertical-align: middle;'><p align='center' class='ft_18_001'>" . _SISTEMA_TITULO_NOME_ . "</p></td><td width='20%'><img border='0' height='40' width='105' src='" . _DIR_IMAGEM_ . "transp1x1.gif'></td></tr><tr><td colspan='3' style='text-align: center; vertical-align: top; height: 40px;'><font class='ft_16_001'>" . $titulo_do_formulario . "</font></td></tr><tr><td colspan='3' align='center'><img src='" . _DIR_IMAGEM_ . "transp1x1.gif' style='height: 20px; border: 0px;'></td></tr></table></h3></div>";

    $idInner .= "<table border='0' cellpadding='0' cellspacing='0' class='tablew2' style='width: 100%; text-align: center; border-collapse: collapse'><tr><td>";

    $idInner .= "<table class='tablew21' width='100%' border='1' align='center' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse'><tr><td colspan='5' valign='middle' height='25' class='ftFormFreq-bc-2'>" . $mes . "/" . $ano . "</td></tr>";

    if ($pagina_de_origem == 'entrada6.php')
    {
        $idInner .= "<tr><td height='20' class='ftFormFreq-tit-bc' width='100%' colspan='4'>SITUACAO</td><td height='20' class='ftFormFreq-tit-bc'>ADMISSAO</td></tr>";
        $idInner .= "<tr><td height='20' class='ftFormFreq-bc' width='100%' colspan='4'>" . $status . "</td><td height='20' class='ftFormFreq-c-2'>" . $anomes_admissao . "</td></tr>";
        //$idInner .= "<tr><td height='20' class='ftFormFreq-tit-bc' width='54%' colspan='5'>SITUACAO</td></tr><tr><td height='20' class='ftFormFreq-bc' colspan='5'>".$status."</td></tr>";
    }
    else
    {
        /*
          switch ($banco_compensacao_tipo)
          {
          case 0: $situacaoBancoHoras = 'NAO AUTORIZADA'; break;
          case 1: $situacaoBancoHoras = 'AUTORIZADA<br>No início do expediente'; break;
          case 2: $situacaoBancoHoras = 'AUTORIZADA<br>No final do expediente'; break;
          case 3: $situacaoBancoHoras = 'AUTORIZADA<br>No início e/ou fim do expediente'; break;
          }
         */
        $idInner .= "<tr><td height='20' class='ftFormFreq-tit-bc'>COMPENSACAO</td><td height='20' class='ftFormFreq-tit-bc' width='54%' colspan='3'>SITUACAO</td><td height='20' class='ftFormFreq-tit-bc'>ADMISSAO</td></tr>";
        $idInner .= "<tr><td height='20' colspan='1' class='ftFormFreq-c'>" . ($banco_compensacao == 'S' ? "" : "NAO ") . "AUTORIZADA" . "</td><td height='20' colspan='3' class='ftFormFreq-bc'>" . $status . "</td><td height='20' colspan='1' class='ftFormFreq-c-2'>" . $anomes_admissao . "</td></tr>";
    }

    $idInner .= "<tr><td height='20' class='ftFormFreq-tit-bc'>SIAPE</td><td height='20' class='ftFormFreq-tit-bc' width='54%' colspan='4'>NOME</td></tr><tr><td height='25' width='10%' align='center'><input type='text' id='siape' name='siape' class='centro' value='" . $pSiape . "' size='15' readonly></td><td height='25' colspan='4' align='centro'>&nbsp;<input type='text' id='nome' name='nome' class='Caixa' value='" . $nome . "' size='100' readonly>&nbsp;</td></tr><tr><td height='20' colspan='5' class='ftFormFreq-tit-bc'><div align='center'><b>LOTA&Ccedil;&Atilde;O</b></div></td></tr><tr><td height='25' colspan='5' style='width: 100%; text-align: center;'>&nbsp;<input name='lotacao' type='text' class='centro' id='lotacao' value='" . $lot . "' size='15' readonly><input name='lotacao_descricao' type='text' class='Caixa' id='lotacao_descricao' value='" . $lot_descricao . "' size='90' readonly></td></tr>";

    if ($pagina_de_origem != 'entrada6.php')
    {
        $idInner .= "<tr><td colspan='1' rowspan='2' class='ftFormFreq-tit-bc-3'>Horário do Setor</td><td height='20' colspan='3' class='ftFormFreq-tit-bc-3'>Hor&aacute;rio do Servidor</td><td height='20' rowspan='2' class='ftFormFreq-tit-bc-3'>Hor&aacute;rio Especial</td></tr>";
        $idInner .= "<tr><td width='18%' height='20' class='ftFormFreq-tit-bc-3'>Entrada</td><td width='36%' height='20' class='ftFormFreq-tit-bc-3'>Intervalo</td><td width='18%' height='20' class='ftFormFreq-tit-bc-3'>Sa&iacute;da</td></tr>";
        $idInner .= "<tr><td height='25' colspan='1' align='center' nowrap>&nbsp;" . $horario_do_setor_inicio . "&nbsp;<font class='ftFormFreq-c-3'>&agrave;s</font>&nbsp;" . $horario_do_setor_fim . "&nbsp;</td><td height='25' align='center'>&nbsp;" . $entrada_no_servico . "&nbsp;</td><td height='25' align='center'>&nbsp;" . $saida_para_o_almoco . "&nbsp;<font class='ftFormFreq-c-3'>&agrave;s</font>&nbsp;" . $volta_do_almoco . "&nbsp;</td><td height='25' align='center'>&nbsp;" . $saida_do_servico . "&nbsp;</td><td height='25' colspan='1' class='ftFormFreq-c'><b>" . ($hora_especial == "S" ? "SIM, $processo_hespecial" : "N&Atilde;O") . "</b></td></tr>";
    }

    $idInner .= "</table></td></tr><tr><td colspan='4'>
		<table class='tablew21' width='790px' border='1' align='center' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse'>
		<tr>
		<td width='12%' class='ftFormFreq-bc-1' height='22'>Dia</td>
		<td width='9%'  class='ftFormFreq-bc-1' nowrap>Entrada</td>
		<td width='9%'  class='ftFormFreq-bc-1' nowrap>Ida Intervalo</td>
		<td width='9%'  class='ftFormFreq-bc-1' nowrap>Volta Intervalo</td>
		<td width='9%'  class='ftFormFreq-bc-1' nowrap>Sa&iacute;da</td>
		<td width='9%'  class='ftFormFreq-bc-1' nowrap>Jornada<br>do Dia</td>
		<td width='10%' class='ftFormFreq-bc-1' nowrap>" . ($_SESSION['sIMPAutorizadoTE'] == 'S' ? 'Turno Previsto' : 'Jornada prevista') . "</td>
		<td width='9%'  class='ftFormFreq-bc-1' nowrap>Resultado<br>do Dia</td>
		<td width='8%'  class='ftFormFreq-bc-1' nowrap>Ocorr&ecirc;ncia</td>";

    if ($pagina_de_origem != 'entrada6.php')
    {
        $idInner .= "<td width='9%' class='ftFormFreq-bc-1' nowrap>Registrado</td>";
    }

    $idInner .= "</tr>";

    $nlinhas = $oDBase->num_rows();

    if ($nlinhas == 0)
    {
        if ($comp_admissao > $comp_invertida && $i == $mes_final)
        {
            $sem_registros_para_exibir = "<br>" . ($situacao_cadastral == '66' ? "ESTAGI&Aacute;RIO(A) REGISTRADO(A) EM " : "SERVIDOR(A) ADMITIDO(A)/REGISTRADO(A) EM ") . $anomes_admissao . "<br>- Sem Registro de Frequ&ecirc;ncia em meses anteriores -<br><br>";
        }
        $idInner .= "<tr><td colspan='10' height='30' align='center'>" . $sem_registros_para_exibir . "</td></tr>";
    }
    else
    {
        $umavez      = true;
        while ($pm_partners = $oDBase->fetch_object())
        {
            if ($umavez == true)
            {
                $umavez       = false;
                $dia_nao_util = marca_dias_nao_util($mes, $ano, $pm_partners->codmun, $pm_partners->codigo);


                ## ocorrências grupos
                $obj = new OcorrenciasGrupos();
                $codigosCompensaveis              = $obj->GrupoOcorrenciasNegativasDebitos($pm_partners->sigregjur, $exige_horarios=true);
                $codigosDebito                    = $obj->CodigosDebito($pm_partners->sigregjur);
                $codigosCredito                   = $obj->CodigosCredito($pm_partners->sigregjur, $temp=true);
                $codigosTrocaObrigatoria          = $obj->CodigosTrocaObrigatoria($pm_partners->sigregjur);

                $grupoOcorrenciasPassiveisDeAbono = $obj->GrupoOcorrenciasPassiveisDeAbono($pm_partners->sigregjur);

                $codigoSemFrequenciaPadrao        = $obj->CodigoSemFrequenciaPadrao($pm_partners->sigregjur);
                $codigoRegistroParcialPadrao      = $obj->CodigoRegistroParcialPadrao($pm_partners->sigregjur);
            }

            $xdia       = $pm_partners->dia;
            $background = $dia_nao_util[$xdia][0];
            $color      = $dia_nao_util[$xdia][1];
            $regjustab  = "regjustab.php?mat=$pSiape&nome=$nome&dia=$dia&oco=$oco&cmd=1";
            switch ($pagina_de_origem)
            {
                case 'pontoser.php':
                    $codigo_de_ocorrencia = "<a href='regjust.php?mat=" . $pSiape . "&comp=" . $comp . "&dia=" . $pm_partners->dia . "&oco=" . $pm_partners->oco . "&cmd=" . $cmd . "'>" . $pm_partners->oco . "</a>";
                    break;
                case 'veponto2.php':
                    $codigo_de_ocorrencia = "<a href= 'vejust.php?mat=" . $pSiape . "&nome=" . $nome . "&comp=" . $comp . "&dia=" . $pm_partners->dia . "&just=" . str_replace('"', '', $pm_partners->just) . "&oco=" . $pm_partners->oco . "&rg=" . $pm_partners->idreg . "&c=" . $cmd . "'>" . $pm_partners->oco . "</a>";
                    break;
                case 'regfreq8.php':
                default:
                    $codigo_de_ocorrencia = $pm_partners->oco;
                    break;
            }
            $registrado_por = define_quem_registrou_descricao($pm_partners, $situacao_cadastral, $comp_invertida);

            $font_i_color = "";
            $sinal        = '&nbsp;';
            $font_f_color = "";

            // elimina "/" e ":", depois define o tipo como inteiro
            // para garantir a resultado do teste a seguir
            $jornada_dif = alltrim(sonumeros($pm_partners->jorndif), '0');
            settype($jornada_dif, 'integer');


            if (!empty($jornada_dif) && in_array($pm_partners->oco, $codigosCompensaveis))
            {
                $font_i_color = "<font color='red'>";
                $font_f_color = "</font>";
                $sinal        = "<font color='red'> - </font>";
            }
            else if (!empty($jornada_dif) && in_array($pm_partners->oco, $codigosDebito))
            {
                $font_i_color = "<font color='red'>(";
                $font_f_color = ")</font>";
                $sinal        = "";
            }
            else if (!empty($jornada_dif) && in_array($pm_partners->oco, $codigosCredito))
            {
                $sinal = " + ";
            }

            $idInner .= retira_acentos("
				<tr style='" . $background . "'>
				<td class='ftFormFreq-cn-1 borderRBL' style='" . $color . "' title='" . $dia_nao_util[$xdia][4] . "'>" . rtrim(ltrim($dia_nao_util[$xdia][2])) . '&nbsp;' . $xdia . $dia_nao_util[$xdia][3] . "</td>
				<td class='ftFormFreq-cn-1 borderRB' style='" . $color . "'>" . tratarHTML($pm_partners->entra) . "</td>
				<td class='ftFormFreq-cn-1 borderRB' style='" . $color . "'>" . tratarHTML($pm_partners->intini) . "</td>
				<td class='ftFormFreq-cn-1 borderRB' style='" . $color . "'>" . tratarHTML($pm_partners->intsai) . "</td>
				<td class='ftFormFreq-cn-1 borderRB' style='" . $color . "'>" . tratarHTML($pm_partners->sai) . "</td>
				<td class='ftFormFreq-cn-1 borderRB' style='" . $color . "'>" . tratarHTML($pm_partners->jornd) . "</td>
				<td class='ftFormFreq-cn-1 borderRB' style='" . $color . "'>" . tratarHTML($pm_partners->jornp) . "</td>
				<td class='ftFormFreq-cn-1 borderRB' style='" . $color . "; text-align: center;'>
				<table border='0' cellpadding='0' cellspacing='0'>
				<tr>
				<td class='ftFormFreq-cn-1' style='" . $color . "; text-align: center;' width='13'>" . tratarHTML($font_i_color) . $sinal . $font_f_color . "</td>
				<td class='ftFormFreq-cn-1' style='" . $color . "; text-align: center;' width='37'>" . tratarHTML($font_i_color) . tratarHTML($pm_partners->jorndif) . tratarHTML($font_f_color) . "</td>
				</tr>
				</table>
				</td>
				<td class='ftFormFreq-cn-1 borderRB' style='" . $color . ";' title='" . tratarHTML($pm_partners->dcod) . "'>" . $codigo_de_ocorrencia . "</td>");

            if ($pagina_de_origem != 'entrada6.php')
            {
                $idInner .= "<td class='ftFormFreq-cn-1 borderRB' style='" . $color . "'>" . tratarHTML($registrado_por) . "</td>";
            }

            $idInner .= "</tr>";
        } // fim do while

        $idInner .= "</table></td></tr><tr><td colspan='4'>";

        // ROTINA DE TOTALIZAÇÃO DAS HORAS

        $total_horas = rotina_de_totalizacao_de_horas($pSiape, $comp);

        // FIM DO CALCULO DOS TOTAIS

        $rowspan       = 0;
        $width_percent = '100%';
        $nbspace       = '&nbsp;';
        switch ($pagina_de_origem)
        {
            case 'regfreq8.php':
                $rowspan       = 1;
                $regfreq7      = "<td rowspan='4' valign='middle' width='21%'><div align='center'>&nbsp;&nbsp;<font style='font-size: 10; font-family: verdana;'>A&ccedil;&atilde;o:</font><a href='regfreq7.php?mat=" . tratarHTML($pSiape) . "' style='color: #0055AA; font-size: 10;'>REJEITAR</a>&nbsp;&nbsp;</div></td>";
                $width_percent = '66%';
                $nbspace       = "<img height='1' width='77' src='" . _DIR_IMAGEM_ . "transp1x1.gif'>";
                break;
        }
        $rowspan += ($total_horas->recesso[1] != 0 ? 1 : 0);
        $rowspan += ($total_horas->instrutoria[1] != 0 ? 1 : 0);
        $rowspan += ($total_horas->extras[1] != 0 ? 1 : 0);

        $idInner .= "<table class='tablew21' width='790px' border='1' align='right' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse'>";

        if ($regfreq7 != '')
        {
            $idInner .= "<tr>" . tratarHTML($regfreq7) . "<td colspn='4' width='" . tratarHTML($width_percent) . "'><div align='center'>&nbsp;</div></td></tr>";
        }

        if (substr($total_horas->recesso[1], 1, 5) != '00:00')
        {
            $idInner .= "<tr><td width='75%' align='center' class='ftFormFreq-cn-1'>Total de horas de recesso anual</td><td width='9%' align='center' class='ftFormFreq-cn-1'>&nbsp;" . tratarHTML($total_horas->recesso[1]) . "&nbsp;</td><td width='9%' align='center' class='ftFormFreq-cn-1'>&nbsp;" . tratarHTML($total_horas->recesso[0]) . "&nbsp;</td><td width='9%' align='center' class='ftFormFreq-cn-1'>" . tratarHTML($nbspace) . "</td></tr>";
        }

        if (substr($total_horas->instrutoria[1], 1, 5) != '00:00')
        {
            $idInner .= "<tr><td align='center' class='ftFormFreq-cn-1'>Total de horas de instrutoria</td><td align='center' class='ftFormFreq-cn-1'>&nbsp;" . tratarHTML($total_horas->instrutoria[1]) . "&nbsp;</td><td align='center' class='ftFormFreq-cn-1'>&nbsp;" . tratarHTML($total_horas->instrutoria[0]) . "&nbsp;</td><td align='center' class='ftFormFreq-cn-1'>" . tratarHTML($nbspace) . "</td></tr>";
        }

        if (substr($total_horas->extras[1], 1, 5) != '00:00')
        {
            $idInner .= "<tr><td align='center' class='ftFormFreq-cn-1'>Total de Horas-extras</td><td align='center' class='ftFormFreq-cn-1'>&nbsp;" . tratarHTML($total_horas->extras[1]) . "&nbsp;</td><td align='center' class='ftFormFreq-cn-1'>&nbsp;" . tratarHTML($total_horas->extras[0]) . "&nbsp;</td><td align='center' class='ftFormFreq-cn-1'>&nbsp;</td></tr>";
        }
    }

    $idInner .= "</table></td></tr></table></td></tr><tr><td colspan='4' style='border-top: 0 solid #808040; border-left: 0 solid #808040; border-right: 0 solid #808040; border-bottom: 0 solid #808040; text-align: left; width: 100%;'><table class='tablew21' border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse; width: 100%;'><tr><td style='width: 55px;'>&nbsp;</td><td style='font-size: 8px; text-align: left;'><font color='red'><b>D: </b></font>Domingo&nbsp;&nbsp;&nbsp;&nbsp;<font color=red><b>S: </b></font>Sabado&nbsp;&nbsp;&nbsp;&nbsp;<font color=red><b>F: </b></font>Feriado/Facultativo (Posicione o mouse sobre o dia para ver a descricao)</td></tr></table></td></tr><tr><td colspan='4'>";

    ## *******************************************************
    # SALDOS DE HORAS COMUNS NO MES
    #
		# Atribui o código html resultante a uma variavel "$html"
    # se o valor de "$bExibeResultados" for igual a "true"
    # ********************************************************
    #

		$bSoSaldo         = true;
    $bParcial         = ($status == 'HOMOLOGADO' ? false : true);
    $bImprimir        = false;
    $bExibeResultados = false;
    $relatorioTipo    = '0';
    //$mes2 = date('m');
    //$ano2 = date('Y');
    $tipo             = 0;

    //
    // $pSiape : definido no início do script
    // $mes    : definido no início do script
    // $ano    : definido no início do script
    // $mes2   : definido no início do script
    // $ano2   : definido no início do script
    include_once( "veponto_saldos.php" );

    $idInner .= $html;

    #
    ## *******************************************************

    $idInner .= "</td></tr></table></td></tr></table>";
}

$idInner .= "</center></body></html>";

// gera o arquivo em PDF
include_once( "gera_pdf.php" );
