<?php

// conexao ao banco de dados
// funcoes diversas
include_once("config.php");

// class formulario
include_once("class_definir.jornada.php");

verifica_permissao("autorizados_login_principal");

// informar tipo da origem
// se rh.php, chefia.php ou entrada.php
$_SESSION['sHOrigem_1'] = "principal_abertura.php";

// verifica se unidade esta em turno estendido
$sAutorizadoTE = turnoEstendido();

// instancia o banco de dados
$oDBase = new DataBase('PDO');

## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setTituloTopoJanela( "Abertura" );

// Topo do formulário
//
$oForm->exibeTopoHTML();

if (checkIpAccess() == false)
{
    if(!isset($_SESSION['troca_contexto'])){
        mensagem("Você não se encontra na faixa de IP autorizada.", "principal.php");
        exit();
    }else{
        unset($_SESSION['troca_contexto']);
    }
}

// caixa de mensagens - dialog
preparaCaixaMensagem();

// grade de horário do turno estendido
quadroDeHorarioDoServidor();

// cronograma e link de intercorrências
quadroCronograma();

// intercorrências
listaIntercorrencias();

// mensagens - avisos
mensagensAvisos();

// registro fora do horario
// Entrada: 20 minutos antes e depois
// Saida..: 20 minutos antes
listaRegistroForaDoHorario($_SESSION['sLotacao'], $sAutorizadoTE);

// Base do formulário
//
$oForm->exibeBaseHTML();



/* ***********************************************************
 *                                                           *
 *                   FUNÇÕES COMPLEMENTARES                  *
 *                                                           *
 *********************************************************** */

##
# TURNO ESTENDIDO
#
# Estancia a class formFrequencia, para estabelecer se
# a unidade teve sua adesão ao turno estendido autorizada
#
##
function turnoEstendido()
{
    ## instancia classe que informa se a
    #  unidade encontra-se em turno estendido
    #
    $oJornada      = new DefinirJornada();
    $oJornada->setDestino('principal.php');
    $oJornada->setVoltar(1);
    $oJornada->setSiape($_SESSION['sMatricula']);
    $oJornada->setLotacao($_SESSION['sLotacao']);
    $oJornada->setData(date('d/m/Y'));
    //$oJornada->estabelecerJornada();
    $oJornada->leSupervisao();
    $sAutorizadoTE = $oJornada->autorizado_te;
    return $sAutorizadoTE;
}


##
# CAIXA DE MENSAGEM
#
# Monta o script básico para utilização de caixa de dialogo modal,
# para exibir mensagens e dados dos dias e motivos do sistema estar
# indisponivel nestes dias
#
##
function preparaCaixaMensagem()
{
    ?>
    <script>
        function verDialogMensagens(id)
        {
            if (id != null && id != '')
            {
                $('#' + id).modal('show');
            }
        }
    </script>
    <?php
}

##
# CRONOGRAMA
#
# Exibe o cronograma de operações do sistema.
# Período para homologação por chefias imediatas
# e do SOGP (recursos humanos)
#
##
function quadroCronograma()
{
    // $_SESSION['sRhi']  : Início do período de atuação do RH
    // $_SESSION['sRhf']  : Fim do período autorizado ao RH para manusear aquele mês
    // $_SESSION['sApsi'] : Homologação - data inicial
    // $_SESSION['sApsf'] : Homologação - data final
    ?>
    <div class="container margin-20">
        <div class="col-md-11">
            <table class="table table-striped table-bordered text-center">
                <thead>
                    <tr>
                        <th class="text-center uppercase" colspan="3">Cronograma do Período</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center" style="vertical-align:middle;">
                            <strong>RECURSOS HUMANOS - verifica&ccedil;&atilde;o e desomologa&ccedil;&atilde;o</strong>
                        </td>
                        <td class="text-center" style="vertical-align:middle;">
                            <strong><?= tratarHTML(dataseca($_SESSION['sRhi'])); ?></strong>
                        </td>
                        <td class="text-center" style="vertical-align:middle;">
                            <strong><?= tratarHTML(dataseca($_SESSION['sRhf'])); ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center" style="vertical-align:middle;">
                            <strong>CHEFIAS - Homologação</strong>
                        </td>
                        <td class="text-center" style="vertical-align:middle;">
                            <strong><?= tratarHTML(dataseca($_SESSION['sApsi'])); ?></strong>
                        </td>
                        <td class="text-center" style="vertical-align:middle;">
                            <strong><?= tratarHTML(dataseca($_SESSION['sApsf'])); ?></strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}


##
# QUADRO DE HORÁRIO
#
# Exibe o quadro de horário dos servidores que
# estão registrado no setor/unidade
#
##
function quadroDeHorarioDoServidor()
{
    ?>
    <table class="table text-center">
        <tr>
            <td class='text-center' style="border-top:0px;">
                <a href='reghorario_grade.php' target='new'>Clique aqui para ver e imprimir o<br>QUADRO DE HORÁRIO DOS SERVIDORES DA UNIDADE</a>
            </td>
        </tr>
    </table>

    <!-- Modal -->
    <div class='modal fade' id='quadro-horario' tabindex='-1' role='dialog' aria-labelledby='myModalLabel'>
        <div class='modal-dialog' role='document'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                    <h4 class='modal-title' id='myModalLabel'>Intercorrências/Interrupções do Sistema</h4>
                </div>
                <div id='modalBody' class='modal-body'>
                    <!-- <?php include_once "reghorario_grade.php"; ?> -->
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-default' data-dismiss='modal'>Fechar</button>
                </div>
            </div>
        </div>
    </div>
    <?php
}


##
# MENSAGENS / AVISOS
#
# Mensagens cadastradas no sistema para
# informar aos servidores/RHs/chefias
# orientações ou fatos relevantes
#
##
function mensagensAvisos()
{
    //
    // C: Chefia
    // R: Recursos Humanos
    // S: Servidores
    // T: Todos
    //
	$oDBase    = new DataBase('PDO');
    $oDBase->query("SELECT date_format(data_aviso,'%d-%m-%Y') as dtaviso, mensagem, janela, janela_altura, alerta, ativo, date_format(data_expirar,'%Y%m%d') as data_expirar, publico FROM avisos WHERE publico IN ('T','C','R','A') ORDER BY data_aviso DESC ");
    $tbnrows   = $oDBase->num_rows();
    $msgalerta = array();
    if ($tbnrows > 0)
    {
        while ($oAvisos = $oDBase->fetch_object())
        {
            if ($oAvisos->alerta == 'S' && $oAvisos->ativo == 'S' && $oAvisos->data_expirar >= date("Ymd"))
            {
                if ($_SESSION['sRH'] == 'S' && substr_count('R_A', $oAvisos->publico) > 0)
                {
                    $msgalerta[] = array($oAvisos->mensagem, $oAvisos->janela, $oAvisos->janela_altura);
                }
                else if ($_SESSION['sAPS'] == 'S' && substr_count('C_A', $oAvisos->publico) > 0)
                {
                    $msgalerta[] = array($oAvisos->mensagem, $oAvisos->janela, $oAvisos->janela_altura);
                }
            }
        }
    }
    $tmvetor = count($msgalerta);
    if ($tmvetor > 0)
    {
        $mensagem_aviso = preparaTextArea(ltrim(rtrim(tratarHTML($msgalerta[$tmvetor - 1][0]))), 'para_alert');
        $mensagem_aviso = str_replace("{funcao}", '', $mensagem_aviso);
        $mensagem_aviso = str_replace("{nome}", ' ' . nome_sobrenome(tratarHTML($_SESSION['sNome'])), $mensagem_aviso);
        $mensagem_aviso = str_replace("{grupo}", $mensagem_grupo, $mensagem_aviso);
        $width          = $msgalerta[$tmvetor - 1][1];
        $height         = $msgalerta[$tmvetor - 1][2];
        ?>
        <div id='dialog-mensagem' title='Mensagem' style='display: none; margin: 3px;'><?= $mensagem_aviso; ?></div>
        <script>
            $(function ()
            {
                $("#dialog-mensagem").dialog("option", "width", "<?= tratarHTML($width); ?>");
                $("#dialog-mensagem").dialog("option", "height", "<?= tratarHTML($height); ?>");
                $('#dialog-mensagem').dialog('open');
            });
        </script>
        <?php
    }
}


##
# INTERCORRÊNCIAS
#
# Informações sobre dias e motivos
# do sistema estar indisponivel
#
##
function listaIntercorrencias()
{
    $oDBase = new DataBase('PDO');
    $oDBase->query("SELECT DATE_FORMAT(interrupcao,'%d/%m/%Y') AS dia_ini, DATE_FORMAT(interrupcao,'%H:%i') AS hora_ini, DATE_FORMAT(retorno,'%d/%m/%Y') AS dia_fim, DATE_FORMAT(retorno,'%H:%i') AS hora_fim, observacao FROM interrupcoes_sistema ORDER BY interrupcao ");

    $html = "
	<table width='89%' border='0' align='center' cellpadding='0' cellspacing='0' style='vertical-align: top;'>
		<tr><td height='40' style='text-align: center; vertical-align: middle;'><a id='show-dialog' href='javascript:verDialogMensagens(\"dialog-intercorrencias\");'>Clique aqui para ver o Calendário das Intercorrências/Interrupções do Sistema</a></td></tr>
	</table>

    <!-- Modal -->
    <div class='modal fade' id='dialog-intercorrencias' tabindex='-1' role='dialog' aria-labelledby='myModalLabel'>
        <div class='modal-dialog' role='document'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                    <h4 class='modal-title' id='myModalLabel'>Intercorrências/Interrupções do Sistema</h4>
                </div>
                <div id='modalBody' class='modal-body'>

                    <table class='table table-striped table-condensed table-bordered text-center'>
                        <thead>
                            <tr class='ui-widget-header' style='height: 20px'>
                	            <th class='sr-td1 text-nowrap'>&nbsp;Data/Hora - Inicial&nbsp;</th>
                	            <th class='sr-td1 text-nowrap'>&nbsp;Data/Hora - Final&nbsp;</th>
                	            <th class='sr-td2 text-nowrap'>&nbsp;Observação</th>
                            </tr>
                        </thead>
                        <tbody>
    ";

    while ($oParadas = $oDBase->fetch_object())
    {
        $html .= "
        <tr style='height: 15px'>
            <td class='sr-td1 sr-tdtop text-nowrap'>&nbsp;" . tratarHTML($oParadas->dia_ini) . ' - ' . tratarHTML($oParadas->hora_ini) . "&nbsp;</td>
            <td class='sr-td1 sr-tdtop text-nowrap'>&nbsp;" . tratarHTML($oParadas->dia_fim) . ' - ' . tratarHTML($oParadas->hora_fim) . "&nbsp;</td>
            <td class='sr-td2 sr-tdtop'>&nbsp;" . preparaTextArea(ltrim(rtrim(tratarHTML($oParadas->observacao))), 'para_html') . "</td>
        </tr>
        ";
    }

    $html .= "
                        </tbody>
                    </table>

                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-default' data-dismiss='modal'>Fechar</button>
                </div>
            </div>
        </div>
    </div>
    ";

    print $html;
}


##
# REGISTRO FORA DO HORÁRIO - LIMITA EM DIA(S) (Padrão 1 dia)
#
# Listas os servidores que registraram entrada
# 20 minutos antes ou depois do horário de início
# do expediente que encontra-se registrado no cadastro
# do servidor, e também a saída registrada 20 minutos
# antes do estabelecido pela chefia no cadastro
#
##
function listaRegistroForaDoHorario($lotacao = '', $sAutorizadoTE = '', $dias = 1)
{
    if ($sAutorizadoTE == 'S' && $_SESSION["sAPS"] == "S")
    {
        $html = carregaRegistroForaDoHorario($lotacao, $sAutorizadoTE, $dias);
        //print $html;
    }
}


##
# CARREGA - REGISTRO FORA DO HORÁRIO
#
# Listas os servidores que registraram entrada
# 20 minutos antes ou depois do horário de início
# do expediente que encontra-se registrado no cadastro
# do servidor, e também a saída registrada 20 minutos
# antes do estabelecido pela chefia no cadastro
#
##
function carregaRegistroForaDoHorario($lotacao = '', $sAutorizadoTE = '', $dias = 0)
{
    if ($sAutorizadoTE == 'S' && $_SESSION["sAPS"] == "S")
    {
        $oDBase          = new DataBase('PDO');
        $oDBase->query("SELECT COUNT(*) AS total FROM servativ AS b WHERE b.cod_lot='$lotacao' AND b.excluido='N' ");
        $nTotalDaUnidade = $oDBase->fetch_object()->total;

        $oDBase->query("SELECT a.siape, b.nome_serv, DATE_FORMAT(a.data_registro,'%d/%m/%Y') AS data_registro, SUBSTR(a.entrada_definida,1,5) AS entrada_definida, SUBSTR(a.entrada_realizada,1,5) AS entrada_realizada, SUBSTR(a.entrada_diferenca,1,5) AS entrada_diferenca, SUBSTR(a.saida_definida,1,5) AS saida_definida, SUBSTR(a.saida_realizada,1,5) AS saida_realizada, SUBSTR(a.saida_diferenca,1,5) AS saida_diferenca FROM control_entrada_saida AS a LEFT JOIN servativ AS b ON a.siape=b.mat_siape WHERE b.cod_lot='$lotacao' ORDER BY DATE_FORMAT(a.data_registro,'%Y%m%d') DESC, LTRIM(RTRIM(b.nome_serv)) ");
        $nTotalOcorrencias = $oDBase->num_rows();

        $html = "
		<br>
		<table border='0' cellpadding='0' cellspacing='0' width='300px'>
		<tr style='height: 20px'>
			<td colspan='9' class='ui-widget-header' nowrap>&nbsp;Registro de Frequência fora do(s) Horário(s) definido(s) - Total de ocorrências/servidores: $nTotalOcorrencias / $nTotalDaUnidade &nbsp;</td>
		</tr>
		</table>
		<div id='dialog-ForaDoHorario' class='sr-td1 ui-widget-content' title='Registro de Frequência fora do(s) Horário(s) definido(s)' style='text-align: left; display:; padding:10px; width:300px;'>
		<div id='accordionForaDoHorario'>";

        $html_table_titulo = "
		<tr class='ui-widget-header'>
			<!-- <td class='sr-td1' nowrap rowspan='2'>&nbsp;DIA&nbsp;</td> //-->
			<td class='sr-td1 sr-tdcenter' nowrap rowspan='2'>&nbsp;SIAPE&nbsp;</td>
			<td class='sr-td1 sr-tdcenter' nowrap rowspan='2'>&nbsp;NOME&nbsp;</td>
			<td class='sr-td1 sr-tdcenter' nowrap colspan='3' align='center'>ENTRADA</td>
			<td class='sr-td1 sr-tdcenter' nowrap colspan='3' align='center'>SAÍDA</td>
		</tr>
		<tr class='ui-widget-header'>
			<td class='sr-td1 sr-font10 sr-tdmiddle sr-tdcenter' nowrap>&nbsp;DEFINIDA&nbsp;</td>
			<td class='sr-td1 sr-font10 sr-tdmiddle sr-tdcenter' nowrap>&nbsp;REGISTRADA&nbsp;</td>
			<td class='sr-td1 sr-font10 sr-tdmiddle sr-tdcenter' nowrap>&nbsp;DIFERENÇA&nbsp;</td>
			<td class='sr-td1 sr-font10 sr-tdmiddle sr-tdcenter' nowrap>&nbsp;DEFINIDA&nbsp;</td>
			<td class='sr-td1 sr-font10 sr-tdmiddle sr-tdcenter' nowrap>&nbsp;REGISTRADA&nbsp;</td>
			<td class='sr-td1 sr-font10 sr-tdmiddle sr-tdcenter' nowrap>&nbsp;DIFERENÇA&nbsp;</td>
		</tr>";

        $contar        = 0;
        $dias_exibir   = 0;
        $primeiraData  = '';
        $data_registro = '';
        while ($oLinhas       = $oDBase->fetch_object())
        {
            if ($data_registro == '' || $data_registro != $oLinhas->data_registro)
            {
                if ($data_registro == '')
                {
                    $primeiraData = "dia" . inverteData($oLinhas->data_registro);
                }
                else
                {
                    if ($dias != 0 && $dias_exibir == $dias)
                    {
                        break;
                    }
                    $html   = str_replace("{total}", $contar, $html);
                    $html   .= "
					</table>
					</div>";
                    $contar = 0;
                }
                $dias_exibir++;
                $html .= "
				<h3><a href='#' algin='left'>" . tratarHTML($oLinhas->data_registro) . " - Ocorrências: {total}</a></h3>
				<div style='border-top: 1px solid #A6C9E2;'>
				<table border='0' cellpadding='0' cellspacing='0' width='100%' style='border-top: 1px solid #A6C9E2;'>";
                $html .= $html_table_titulo;
            }
            $contar++;
            $html .= "
			<tr style='height: 15px; background-color: #FFFFFF;'>
				<!-- <td class='sr-td2 sr-tdmiddle'>&nbsp;" . ($oLinhas->data_registro == $data_registro ? '' : tratarHTML($oLinhas->data_registro)) . "&nbsp;</td> //-->
				<td class='sr-td1 sr-tdmiddle sr-tdcenter'>&nbsp;" . tratarHTML($oLinhas->siape) . "&nbsp;</td>
				<td class='sr-td1 sr-tdmiddle' nowrap>&nbsp;" . tratarHTML($oLinhas->nome_serv) . "&nbsp;</td>";
            if ($oLinhas->entrada_definida == '00:00' && $oLinhas->entrada_realizada == '00:00' && $oLinhas->entrada_diferenca == '00:00')
            {
                $html .= "
				<td class='sr-td2 sr-tdmiddle sr-tdcenter'>&nbsp;-&nbsp;</td>
				<td class='sr-td2 sr-tdmiddle sr-tdcenter'>&nbsp;-&nbsp;</td>
				<td class='sr-td2 sr-tdmiddle sr-tdcenter'>&nbsp;-&nbsp;</td>";
            }
            else
            {
                $html .= "
				<td class='sr-td2 sr-tdmiddle sr-tdcenter'>&nbsp;" . ($oLinhas->entrada_definida == '00:00' ? "<font color='red'><b>" . tratarHTML($oLinhas->entrada_definida) . "</b></font>" : tratarHTML($oLinhas->entrada_definida)) . "&nbsp;</td>
				<td class='sr-td2 sr-tdmiddle sr-tdcenter'>&nbsp;" . tratarHTML($oLinhas->entrada_realizada) . "&nbsp;</td>
				<td class='sr-td2 sr-tdmiddle sr-tdcenter'>&nbsp;" . tratarHTML($oLinhas->entrada_diferenca) . "&nbsp;</td>";
            }

            if ($oLinhas->saida_definida == '00:00' && $oLinhas->saida_realizada == '00:00' && $oLinhas->saida_diferenca == '00:00')
            {
                $html .= "
				<td class='sr-td2 sr-tdmiddle sr-tdcenter'>&nbsp;-&nbsp;</td>
				<td class='sr-td2 sr-tdmiddle sr-tdcenter'>&nbsp;-&nbsp;</td>
				<td class='sr-td2 sr-tdmiddle sr-tdcenter'>&nbsp;-&nbsp;</td>";
            }
            else
            {
                $html .= "
				<td class='sr-td2 sr-tdmiddle sr-tdcenter'>&nbsp;" . ($oLinhas->saida_definida == '00:00' && $oLinhas->saida_realizada != '00:00' && $oLinhas->saida_diferenca != '00:00' ? "<font color='red'><b>" . tratarHTML($oLinhas->saida_definida) . "</b></font>" : $oLinhas->saida_definida) . "&nbsp;</td>
				<td class='sr-td2 sr-tdmiddle sr-tdcenter'>&nbsp;" . tratarHTML($oLinhas->saida_realizada) . "&nbsp;</td>
				<td class='sr-td2 sr-tdmiddle sr-tdcenter'>&nbsp;" . tratarHTML($oLinhas->saida_diferenca) . "&nbsp;</td>";
            }
            $html          .= "
			</tr>";
            $data_registro = $oLinhas->data_registro;
        }

        $html = str_replace("{total}", $contar, $html);
        $html .= "
		</table>
		</div>
		</div>";

        $html .= "
		<script>
		$(function() {
			var icons = {
				header: 'ui-icon-circle-arrow-e',
				headerSelected: 'ui-icon-circle-arrow-s'
			};
			$( '#accordionForaDoHorario' ).accordion({
				collapsible: true,
				icons: icons,
				fillSpace: false,
				autoHeight: true
			});
		});
		</script>";

        return $html;
    }
}
