<?php
// conexao ao banco de dados
// funcoes diversas
include_once("config.php");

// class formulario
include_once("class_form.frequencia.php");

verifica_permissao("logado");

// informar tipo da origem
// se rh.php, chefia.php ou entrada.php
$_SESSION['sHOrigem_1'] = "principal_abertura.php";

// le a seção do usuario
$sRhi  = $_SESSION['sRhi'];   // Início do período de atuação do RH
$sRhf  = $_SESSION['sRhf'];   // Fim do período autorizado ao RH para manusear aquele mês
$sApsi = $_SESSION['sApsi']; // Homologação: data inicial
$sApsf = $_SESSION['sApsf']; // Homologação: data final
## instancia classe frequencia
#
$oFreq = new formFrequencia;
$oFreq->setOrigem('entrada.php'); // Registra informacoes em sessao
$oFreq->setSiape($_SESSION['sMatricula']);    // matricula do servidor que se deseja alterar a frequencia
$oFreq->setLotacao($_SESSION['sLotacao']);  // matricula do usuario
$oFreq->setMes(date("m")); // mes que se deseja alterar a frequencia
$oFreq->setAno(date("Y")); // ano que se deseja alterar a frequencia
$oFreq->loadDadosServidor();

$sAutorizadoTE = $oFreq->getTurnoEstendido();

// instancia o banco de dados
$oDBase = new DataBase('PDO');

## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho('Abertura');
$oForm->setCSS(_DIR_CSS_ . 'estilos.css');
$oForm->setCSS(_DIR_CSS_ . 'smoothness/jquery-ui-custom-px.min.css');
$oForm->setDialogModal();

$oForm->setSeparador(20);

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// caixa de mensagens - dialog
preparaCaixaMensagem();

// grade de horário do turno estendido
gradeHorarioTurnoEstendido($sAutorizadoTE);

// cronograma e link de intercorrências
?>
<table width="89%" border="1" align="center" cellpadding="0" cellspacing="0" bordercolor="#808040" id="AutoNumber1" style="border-collapse: collapse; vertical-align: top;">
    <tr>
        <td colspan="3" height="20" bgcolor="#DFDFBF"> <p align="center"><b>Cronograma do Período</b></td>
    </tr>
    <tr>
        <td width="65%">&nbsp;RECURSOS HUMANOS - verifica&ccedil;&atilde;o e desomologa&ccedil;&atilde;o</td>
        <td width="16%">
            <p align="center">
                <input type="text" name="rhi" size="10" maxlength='10' class='alinhadoAEsquerda' value="<?= dataseca($sRhi); ?>">
        </td>
        <td width="19%"> <p align="center">
                <input type="text" name="rhf" size="10" maxlength='10' class='alinhadoAEsquerda'value="<?= dataseca($sRhf); ?>">
        </td>
    </tr>
    <tr>
        <td width="65%">&nbsp;CHEFIAS - homologa&ccedil;&atilde;o</td>
        <td width="16%"> <p align="center">
                <input type="text" name="apsi" size="10" maxlength='10' class='alinhadoAEsquerda' value="<?= dataseca($sApsi); ?>">
        </td>
        <td width="19%"> <p align="center">
                <input type="text" name="apsf" size="10" maxlength='10' class='alinhadoAEsquerda' value="<?= dataseca($sApsf); ?>" >
        </td>
    </tr>
</table>
<table width="89%" border="0" align="center" cellpadding="0" cellspacing="0" style="vertical-align: top;">
    <tr><td height='40' style="text-align: center; vertical-align: middle;"><a id='show-dialog' href='#'>Clique aqui, para ver o Calendário das Intercorrências/Interrupções do sistema</a></td></tr>
</table>
<?php
// lista das unidades que realizaram alterações no histórico
listaUnidadesHistorico();

// mensagens - avisos
mensagensAvisos();

// intercorrências
listaIntercorrencias();

// registro fora do horario
// Entrada: 20 minutos antes e depois
// Saida..: 20 minutos antes
listaRegistroForaDoHorario($_SESSION['sLotacao'], $sAutorizadoTE);

// mensagem de bloqueio
mensagemBloqueio();

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

exit();

function preparaCaixaMensagem()
{
    ?>
    <style>
        td { font-family:verdana; font-size:8pt }
        input { font-family:verdana; font-size:8pt; color:#333300; border:1p solid #FFFFFF }
        .sr-td1 { border-left: 1px solid #A6C9E2; border-bottom: 1px solid #A6C9E2; }
        .sr-td2 { border-left: 1px solid #A6C9E2; border-bottom: 1px solid #A6C9E2; border-right: 1px solid #A6C9E2; }
        .sr-tdtop    { vertical-align: top;    }
        .sr-tdcenter { vertical-align: middle; }
        .sr-font10 { font-size: 10px; }
    </style>
    <script>
        $(function ()
        {
            $('#dialog-mensagem').dialog({
                autoOpen: false,
                modal: true,
                closeOnEscape: false,
                height: 'auto',
                width: 650
            });
            $('#dialog-intercorrencias').dialog({
                autoOpen: false,
                modal: true,
                closeOnEscape: false,
                height: 'auto',
                width: 650
            });
            $('#show-dialog').click(function ()
            {
                $('#dialog-intercorrencias').dialog('open');
            });
        });
    </script>
    <?php

}

function gradeHorarioTurnoEstendido($sAutorizadoTE = '')
{
    if ($sAutorizadoTE == 'S')
    {
        ?>
        <table width="89%" border="0" align="center" cellpadding="0" cellspacing="0" style="vertical-align: top;">
            <tr><td height='40' style="text-align: center; vertical-align: middle;"><a href='reghorario_grade.php' target='new' style='font-size: 11px;'>Clique aqui, para ver e imprimir a GRADE DE HORÁRIO DO TURNO ESTENDIDO</a></td></tr>
        </table>
        <?php
    }

}

function listaUnidadesHistorico()
{
    // atribui o numero da regional
    $sSR = substr($_SESSION['sLotacao'], 0, 5);
    switch ($sSR)
    {
        case '01001': $sRegional = '0';
            break;
        case '21150': $sRegional = '1';
            break;
        case '11150': $sRegional = '2';
            break;
        case '20150': $sRegional = '3';
            break;
        case '15150': $sRegional = '4';
            break;
        case '23150': $sRegional = '5';
            break;
        default:
            $sRegional = ($_SESSION['sSenhaI'] == 'S' ? "'0','1','2','3','4','5'" : '');
            break;
    }

    // se o servidor eh de regional
    // exibe total de historico
    if ($sRegional != '' && $sRH == 'S')
    {
        $oDBase   = new DataBase('PDO');
        $oDBase->query("SELECT IF(b.regional='0','DIRECAO CENTRAL',IF(b.regional='1','SR SUDESTE I',IF(b.regional='2','SR SUDESTE II',IF(b.regional='3','SR SUL',IF(b.regional='4','SR NORDESTE',IF(b.regional='5','SR NORTE CENTRO-OESTE','')))))) AS dc_sr, b.cidade_lota AS unidade, b.uf_lota AS uf, a.lotacao, IF(c.gerencia='DIRETORIA','DIRECAO CENTRAL',c.gerencia) AS descricao, COUNT(*) AS alterados, DATE_FORMAT(a.datahora,'%d/%m/%Y') AS datahora, b.regional FROM control_historico AS a LEFT JOIN tabsetor AS b ON a.lotacao=b.codigo LEFT JOIN upag AS c ON b.upag=c.upag_cod WHERE b.regional IN ($sRegional) AND a.operacao LIKE 'Gravou%' AND c.desativado='nao' GROUP BY lotacao ORDER BY b.regional,IF(SUBSTR(a.lotacao,3,3)='150',1,2),a.lotacao,a.datahora,a.siape,a.compet ");
        $nNumRows = $oDBase->num_rows();

        // exibe dados se a regional
        //
		if ($nNumRows > 0)
        {
            ?>
            <br>
            <br>
            <table width="89%" border="0" align="center" cellpadding="0" cellspacing="0" bordercolor="#808040" id="AutoNumber1" style="border-collapse: collapse">
                <tr>
                    <td colspan="7" height="20" bgcolor="#DFDFBF"> <p align="center"><b>UNIDADES QUE UTILIZARAM O HISTÓRICO</b></td>
                </tr>
                <?php
                $contar = array(0, 0, 0, 0, 0, 0);
                $quebra = '';
                while ($oUnd   = $oDBase->fetch_object())
                {
                    $contar[$oUnd->regional] += 1;
                    if ($quebra == '' || $quebra != $oUnd->dc_sr)
                    {
                        ?>
                        <tr><td colspan='7'>&nbsp;</td></tr>
                        <tr id="GRP_<?= tratarHTML($oUnd->regional); ?>" style='cursor: pointer;'><td valign='bottom' colspan='7' style='text-indent: 20px;'>&nbsp;<i><b><?= tratarHTML($oUnd->dc_sr); ?></b></i>&nbsp;</td></tr>
                        <tr id='GRP_<?= tratarHTML($oUnd->regional); ?>_1' style="display:inline;">
                            <td bgcolor="#efefde" align='center'><b>SEQ.</b></td>
                            <td bgcolor="#efefde" align='center'><b>UNIDADE</b></td>
                            <td bgcolor="#efefde" align='center'><b>UF</b></td>
                            <td bgcolor="#efefde" align='center'><b>CÓDIGO</b></td>
                            <td bgcolor="#efefde" align='center'><b>DESCRIÇÃO</b></td>
                            <td bgcolor="#efefde" align='center'><b>ALTERAÇÕES</b></td>
                            <td bgcolor="#efefde" align='center'><b>INICIOU EM</b></td>
                        </tr>
                        <?php
                        $quebra = $oUnd->dc_sr;
                    }
                    ?>
                    <tr id='GRP_<?= $oUnd->regional; ?>_2' style="display: <?= ($oUnd->regional == $sRegional ? '' : ''); ?>" onmouseover='pinta(1, this)' onmouseout='pinta(2, this)'>
                        <td style="vertical-align: bottom; text-align: right;">&nbsp;<?= number_format(tratarHTML($contar[$oUnd->regional]), 0, ',', '.'); ?>&nbsp;&nbsp;</td>
                        <td style="vertical-align: bottom;">&nbsp;<?=tratarHTML( $oUnd->unidade); ?>&nbsp;</td>
                        <td style="vertical-align: bottom; text-align: center;">&nbsp;<?= tratarHTML($oUnd->uf); ?>&nbsp;</td>
                        <td style="vertical-align: bottom;">&nbsp;<?= tratarHTML($oUnd->lotacao); ?>&nbsp;</td>
                        <td style="vertical-align: bottom;">&nbsp;<?= tratarHTML($oUnd->descricao); ?>&nbsp;</td>
                        <td style="vertical-align: bottom; text-align: center;">&nbsp;<?= number_format(tratarHTML($oUnd->alterados), 0, ',', '.'); ?><img src='<?= _DIR_IMAGEM_; ?>transp1x1.gif' border='0' width='20' height='1'></td>
                        <td style="vertical-align: bottom; text-align: center;">&nbsp;<?= tratarHTML($oUnd->datahora); ?>&nbsp;</td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php
        }
    }

}

function mensagensAvisos()
{
    //
    // C: Chefia
    // R: Recursos Humanos
    // S: Servidores
    // T: Todos
    //
	$oDBase    = new DataBase('PDO');
    $oDBase->query("SELECT date_format(data_aviso,'%d-%m-%Y') as dtaviso, mensagem, alerta, ativo, date_format(data_expirar,'%Y%m%d') as data_expirar, publico FROM avisos WHERE publico IN ('T','C','R') ORDER BY data_aviso DESC limit 1 ");
    $tbnrows   = $oDBase->num_rows();
    $msgalerta = array();
    if ($tbnrows > 0)
    {
        while ($oAvisos = $oDBase->fetch_object())
        {
            $dtaviso      = $oAvisos->dtaviso;
            $txtaviso     = $oAvisos->mensagem;
            $alerta       = $oAvisos->alerta;
            $ativo        = $oAvisos->ativo;
            $data_expirar = $oAvisos->data_expirar;
            if ($alerta == 'S' && $ativo == 'S' && $data_expirar >= date("Ymd"))
            {
                if ($_SESSION['sRH'] == 'S')
                {
                    $msgalerta[] = $txtaviso;
                }
                elseif ($oAvisos->publico != 'R')
                {
                    $msgalerta[] = $txtaviso;
                }
            }
        }
    }
    $tmvetor = count($msgalerta);
    if ($tmvetor > 0)
    {
        ?>
        <div id='dialog-mensagem' title='Mensagem' style='display: none; margin: 3px;'>
            <?= preparaTextArea(trim(tratarHTML($msgalerta[$tmvetor - 1])), 'para_alert'); ?>
        </div>
        <script>
            $(function ()
            {
                $('#dialog-mensagem').dialog('open');
            });
        </script>
        <?php
    }
}

##
# Intercorrências
#
function listaIntercorrencias()
{
    $oDBase = new DataBase('PDO');
    $oDBase->query("SELECT DATE_FORMAT(interrupcao,'%d/%m/%Y') AS dia_ini, DATE_FORMAT(interrupcao,'%H:%i') AS hora_ini, DATE_FORMAT(retorno,'%d/%m/%Y') AS dia_fim, DATE_FORMAT(retorno,'%H:%i') AS hora_fim, observacao FROM interrupcoes_sistema ORDER BY interrupcao ");

    $html = "
	<div id='dialog-intercorrencias' title='Intercorrências SISREF' style='display: none;'>
	<table cellpadding='0' cellspacing='0'>
	<tr class='ui-widget-header' style='height: 20px'>
		<td class='sr-td1' nowrap>&nbsp;Data/Hora - Inicial&nbsp;</td>
		<td class='sr-td1' nowrap>&nbsp;Data/Hora - Final&nbsp;</td>
		<td class='sr-td2' nowrap>&nbsp;Observação</td>
	</tr>";

    while ($oParadas = $oDBase->fetch_object())
    {
        $html .= "
		<tr style='height: 15px'>
		<td class='sr-td1 sr-tdtop'>&nbsp;" . tratarHTML($oParadas->dia_ini) . ' - ' . tratarHTML($oParadas->hora_ini) . "&nbsp;</td>
		<td class='sr-td1 sr-tdtop'>&nbsp;" . tratarHTML($oParadas->dia_fim) . ' - ' . tratarHTML($oParadas->hora_fim) . "&nbsp;</td>
		<td class='sr-td2 sr-tdtop'>&nbsp;" . preparaTextArea(ltrim(rtrim(tratarHTML($oParadas->observacao))), 'para_html') . "</td>
		</tr>";
    }

    $html .= "
	</table>
	</div>";

    print $html;
}

function listaRegistroForaDoHorario($lotacao = '', $sAutorizadoTE = '')
{
    if ($sAutorizadoTE == 'S')
    {
        $oDBase          = new DataBase('PDO');
        $oDBase->query("SELECT COUNT(*) AS total FROM servativ AS b WHERE b.cod_lot='$lotacao' AND b.excluido='N' ");
        $nTotalDaUnidade = $oDBase->fetch_object()->total;

        $oDBase->query("SELECT a.siape, b.nome_serv, DATE_FORMAT(a.data_registro,'%d/%m/%Y') AS data_registro, SUBSTR(a.entrada_definida,1,5) AS entrada_definida, SUBSTR(a.entrada_realizada,1,5) AS entrada_realizada, SUBSTR(a.entrada_diferenca,1,5) AS entrada_diferenca, SUBSTR(a.saida_definida,1,5) AS saida_definida, SUBSTR(a.saida_realizada,1,5) AS saida_realizada, SUBSTR(a.saida_diferenca,1,5) AS saida_diferenca FROM control_entrada_saida AS a LEFT JOIN servativ AS b ON a.siape=b.mat_siape WHERE b.cod_lot='$lotacao' ORDER BY DATE_FORMAT(a.data_registro,'%Y%m%d') DESC, LTRIM(RTRIM(b.nome_serv)) ");
        $nTotalOcorrencias = $oDBase->num_rows();

        $html = "
		<div id='dialog-ForaDoHorario' title='Registro de Frequência fora do(s) Horário(s) definido(s)' style='display:;'>
		<table border='0' cellpadding='0' cellspacing='0'>
		<tr style='height: 20px'>
			<td colspan='9' nowrap>&nbsp;Total de servidores: ".tratarHTML($nTotalOcorrencias)." / ".tratarHTML($nTotalDaUnidade)." &nbsp;</td>
		</tr>
		<tr class='ui-widget-header'>
			<td class='sr-td1' nowrap rowspan='2'>&nbsp;DIA&nbsp;</td>
			<td class='sr-td1' nowrap rowspan='2'>&nbsp;SIAPE&nbsp;</td>
			<td class='sr-td1' nowrap rowspan='2'>&nbsp;NOME&nbsp;</td>
			<td class='sr-td1' nowrap colspan='3' align='center'>ENTRADA</td>
			<td class='sr-td1' nowrap colspan='3' align='center'>SAÍDA</td>
		</tr>
		<tr class='ui-widget-header'>
			<td class='sr-td1 sr-font10 sr-tdcenter' nowrap>&nbsp;DEFINIDA&nbsp;</td>
			<td class='sr-td1 sr-font10 sr-tdcenter' nowrap>&nbsp;REGISTRADA&nbsp;</td>
			<td class='sr-td1 sr-font10 sr-tdcenter' nowrap>&nbsp;DIFERENÇA&nbsp;</td>
			<td class='sr-td1 sr-font10 sr-tdcenter' nowrap>&nbsp;DEFINIDA&nbsp;</td>
			<td class='sr-td1 sr-font10 sr-tdcenter' nowrap>&nbsp;REGISTRADA&nbsp;</td>
			<td class='sr-td1 sr-font10 sr-tdcenter' nowrap>&nbsp;DIFERENÇA&nbsp;</td>
		</tr>";

        $data_registro = '';
        while ($oLinhas       = $oDBase->fetch_object())
        {
            $html          .= "
			<tr style='height: 15px'>
				<td class='sr-td2 sr-tdcenter'>&nbsp;" . ($oLinhas->data_registro == $data_registro ? '' : tratarHTML($oLinhas->data_registro)) . "&nbsp;</td>
				<td class='sr-td1 sr-tdcenter'>&nbsp;" . tratarHTML($oLinhas->siape) . "&nbsp;</td>
				<td class='sr-td1 sr-tdcenter' nowrap>&nbsp;" . tratarHTML($oLinhas->nome_serv) . "&nbsp;</td>
				<td class='sr-td2 sr-tdcenter'>&nbsp;" . tratarHTML($oLinhas->entrada_definida) . "&nbsp;</td>
				<td class='sr-td2 sr-tdcenter'>&nbsp;" . tratarHTML($oLinhas->entrada_realizada) . "&nbsp;</td>
				<td class='sr-td2 sr-tdcenter'>&nbsp;" . tratarHTML($oLinhas->entrada_diferenca) . "&nbsp;</td>
				<td class='sr-td2 sr-tdcenter'>&nbsp;" . tratarHTML($oLinhas->saida_definida) . "&nbsp;</td>
				<td class='sr-td2 sr-tdcenter'>&nbsp;" . tratarHTML($oLinhas->saida_realizada) . "&nbsp;</td>
				<td class='sr-td2 sr-tdcenter'>&nbsp;" . tratarHTML($oLinhas->saida_diferenca) . "&nbsp;</td>
			</tr>";
            $data_registro = $oLinhas->data_registro;
        }

        $html .= "
		</table>
		</div>";

        $_SESSIOIN['teRegistroForaDoHorario'] = $html;
        print $html;
    }
}

function mensagemBloqueio($exibir = false, $unidades = '01')
{
    if ($exibir == true && substr_count($unidades, substr($_SESSION['sLotacao'], 0, 2)) > 0)
    {
        ?>
        <div id='dialog-mensagem' title='Nova Estrutura' style='display: none;'>
            <table>
                <tr>
                    <td colspan='2'>Caros Colegas,</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><br>O sistema estará bloqueado nos dias 26/01 e 27/01/2012 para implantação<br>da nova estrutura (2011).</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>Maiores informações entrem em contato com a Equipe do SGPADM (Gestão de pessoas)</td>
                </tr>
                <tr>
                    <td colspan='2'><br>Atenciosamente.</td>
                    </td>
                </tr>
                <tr>
                    <td colspan='2'><br>Equipe SISREF</td>
                    </td>
                </tr>
            </table>
        </div>
        <script>
            $(function ()
            {
                $('#dialog-mensagem').dialog('open');
            });
        </script>
        <?php
    }
}
