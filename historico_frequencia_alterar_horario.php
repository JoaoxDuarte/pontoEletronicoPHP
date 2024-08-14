<?php

// funcoes de uso geral
include_once( "config.php" );
include_once( "class_form.telas.php" );
include_once( "class_ocorrencias_grupos.php" );

// permissao de acesso
verifica_permissao('sRH e sTabServidor');

// valores registrado em sessao
$sMatricula = $_SESSION["sMatricula"];
$magico     = $_SESSION["magico"];

// parametros passados por formulario
$mat        = addslashes($_REQUEST['mat']);
$dia        = $_REQUEST['dia'];
$cmd        = addslashes($_REQUEST['cmd']);
$ocor       = addslashes($_REQUEST['ocor']);
$lot        = addslashes($_REQUEST['lot']);
$grupo      = addslashes($_REQUEST['grupo']);
$cod_sitcad = addslashes($_REQUEST['cod_sitcad']);

$mat = getNovaMatriculaBySiape($mat);

// dados do servidor
$oDBase = selecionaServidor( $mat );
$oServidor = $oDBase->fetch_object();
$nome   = $oServidor->nome_serv;
$lot    = $oServidor->cod_lot;
$jnd    = $oServidor->jornada;
$sitcad = $oServidor->sigregjur;
$jnc    = formata_jornada_para_hhmm($jnd);

// dados da frequencia do servidor
$oDBase = selecionaPontoServidor( $mat, $dia, $_SESSION['sHArquivoTemp'] );
$oPonto = $oDBase->fetch_object();
$entra  = $oPonto->entra;
$iniint = $oPonto->intini;
$fimint = $oPonto->intsai;
$sai    = $oPonto->sai;


// ocorrências grupos
$obj = new OcorrenciasGrupos();
$codigoCreditoRecessoPadrao    = $obj->CodigoCreditoRecessoPadrao($sitcad);
$codigoDebitoRecessoPadrao     = $obj->CodigoDebitoRecessoPadrao($sitcad);
$codigoDebitoInstrutoriaPadrao = $obj->CodigoDebitoInstrutoriaPadrao($sitcad);
$eventosEsportivos             = $obj->EventosEsportivos($todos='debito');
$codigosDebitos                = $obj->GrupoOcorrenciasPassiveisDeAbono($sitcad);
$grupoOcorrenciasViagem        = $obj->GrupoOcorrenciasViagem($sitcad);
//'00172__62010__62012__02323__02525__55555__99999'


// dados voltar
$_SESSION['voltar_nivel_4'] = $_SERVER['REQUEST_URI'];

if (in_array($ocor,$codigoDebitoRecessoPadrao) && (dataUsoDoRecesso($dia) == false))
{
    mensagem("Não é permitido lançar recesso (" . implode(', ', $codigoDebitoRecessoPadrao) . ") fora do período legal!", "historico_frequencia_alterar.php?dados=" . $_SESSION['voltar_nivel_3']);
}
if (in_array($ocor,$codigoCreditoRecessoPadrao) && (dataCompensacaoDoRecesso($dia) == false))
{
    mensagem("Não é permitido lançar compensação de recesso (" . implode(', ', $codigoCreditoRecessoPadrao) . ") fora do período legal!", "historico_frequencia_alterar.php?dados=" . $_SESSION['voltar_nivel_3']);
}
if (verifica_se_dia_nao_util($dia, $lot) == true && in_array($ocor,$codigosDebitos))
{
    mensagem("Não é permitido lançar a ocorrência " . $ocor . ", em dia não útil!", "historico_frequencia_alterar.php?dados=" . $_SESSION['voltar_nivel_3']);
}

## competencia
#
$data = data2arrayBR($dia);
$mes  = dataMes($dia);
$ano  = dataAno($dia);
$year = $ano;
$comp = $mes . $year;

$diac = conv_data($dia);


## classe para montagem do formulario
#
$oForm = new formTelas();
$oForm->setCaminho('Histórico » ... » Alterar Ocorrência');
$oForm->setJS(_DIR_JS_ . 'phpjs.js');
$oForm->setJS('historico_frequencia_alterar_horario.js');
$oForm->setSeparador(0);
$oForm->setSeparadorBase(20);

$oForm->setSubTitulo("Histórico - Alterar Registro de Ocorrência");

## Dados do servidor
#
$oForm->setFormAction('historico_frequencia_gravar.php');
$oForm->setFormOnSubmit('return validar();');
$oForm->initInputHidden();
$oForm->setInputHidden('modo', 'alterar');
$oForm->setInputHidden('compete', $comp);
$oForm->setInputHidden('ocor', $ocor);
$oForm->setInputHidden('jnd', $jnd);
$oForm->setInputHidden('cmd', $cmd);
$oForm->setInputHidden('lot', $lot);
$oForm->setInputHidden('grupo', $grupo);

$oForm->setInputHidden('jn', formata_jornada_para_hhmm($jnd));
$oForm->setInputHidden('mes', $mes);
$oForm->setInputHidden('ano', $ano);
$oForm->setInputHidden('dias_no_mes', numero_dias_do_mes($mes, $ano));
$oForm->setInputHidden('hom1', '1');
$oForm->setInputHidden('horsaida', $hs);
$oForm->setInputHidden('horsaida2', $vHoras);

$oForm->setInputHidden('codigosDebitos', implode(',', $codigosDebitos)); //'00172__62010__62012__02323__02525__55555__99999';
$oForm->setInputHidden('recessoDebito', implode(',', $codigoDebitoRecessoPadrao));  //'02323';
$oForm->setInputHidden('recessoCredito', implode(',', $codigoCreditoRecessoPadrao)); //'02424';
$oForm->setInputHidden('codigoDebitoInstrutoriaPadrao', implode(',', $codigoDebitoInstrutoriaPadrao)); //02525
$oForm->setInputHidden('eventosEsportivos', implode(',', $eventosEsportivos)); // 62010_62012_62014
$oForm->setInputHidden('grupoOcorrenciasViagem', implode(',', $grupoOcorrenciasViagem)); // 00218

$oForm->setFormNomeServidor($nome);
$oForm->setFormMatriculaSiape($mat);
$oForm->setFormOcorrencia($ocor);

$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

$oForm->initHTML();
$oForm->setHTML($oForm->getAbreForm());

$oForm->setDadosDoServidor();
$oForm->setHTML($oForm->getInputHidden());

// tabela ocorrencias
$ocorrencia = $oForm->getFormOcorrencia();

$oForm->setHTML("
	<table id='AutoNumber1' border='1' cellpadding='0' cellspacing='0' style='border-color: #808040; border-collapse: collapse; margin-bottom: 0; text-align: center; width: 95%;' align='center'>
	<tr><td colspan='5' style='width: 100%; height: 30;'><font class='ft_13_002'>&nbsp;" . tratarHTML($ocorrencia) . ' - ' . utf8_decode($oForm->control_tabela_ocorrencias->getOcorrenciaDescricao($ocorrencia)) . "&nbsp;</font></td></tr>
	<tr><td style='width: 15%; height: 39;'><font class=\"ft_13_003\">&nbsp;
	Dia da Ocorr&ecirc;ncia:<br>&nbsp;<input type=\"text\" tipo=\"siape\" id=\"dia\" name=\"dia\" class=\"caixa\" size=\"11\" maxlength=\"11\" value=\"" . tratarHTML($dia) . "\" onkeypress=\"formatar(this, '##/##/####')\" readonly></font></td><td width='22%'><font class=\"ft_13_003\">&nbsp;
	Hora de In&iacute;cio do Expediente:<br>&nbsp;<input type=\"text\" tipo=\"siape\" id=\"entra\" name=\"entra\" class=\"alinhadoAoCentro\" title=\"Digite o horário sem pontos no formato 000000!\" size=\"8\" maxlength=\"8\" value=\"" . tratarHTML($entra) . "\" onkeypress=\"formatar(this, '##:##:##')\"></font></td><td width='22%'><font class=\"ft_13_003\">&nbsp;
	Hora de In&iacute;cio do Intervalo:<br>&nbsp;<input type=\"text\" tipo=\"siape\" id=\"iniint\" name=\"iniint\" class=\"alinhadoAoCentro\" title=\"Digite o horário sem pontos no formato 000000!\" size=\"8\" maxlength=\"8\" value=\"" . tratarHTML($iniint) . "\" onkeypress=\"formatar(this, '##:##:##')\"></font></td><td width='22%'><font class=\"ft_13_003\">&nbsp;
	Hora de Retorno do Intervalo:<br>&nbsp;<input type=\"text\" tipo=\"siape\" id=\"fimint\" name=\"fimint\" class=\"alinhadoAoCentro\" title=\"Digite o horário sem pontos no formato 000000!\" size=\"8\" maxlength=\"8\" value=\"" . tratarHTML($fimint) . "\" onkeypress=\"formatar(this, '##:##:##')\"></font></td><td width='22%'><font class=\"ft_13_003\">&nbsp;
	Hor&aacute;rio da Sa&iacute;da:<br>&nbsp;<input type=\"text\" tipo=\"siape\" id=\"hsaida\" name=\"hsaida\" class=\"alinhadoAoCentro\" title=\"Digite o horário sem pontos no formato 000000!\" size=\"8\" maxlength=\"8\" value=\"" . tratarHTML($sai) . "\" onkeypress=\"formatar(this, '##:##:##')\"></font></td></tr></table><div align='center'>
	<div align='center'>
		<p>
		<table border='0' align='center'>
			<tr>
			<td align='right'>" . botao('Continuar', 'javascript:return validar();') . "</td>
			<td>&nbsp;&nbsp;</td>
	");

$oForm->setHTML("<td align='center'>" . botao('Voltar', 'javascript:window.location.replace("historico_frequencia_alterar.php?dados=' . $_SESSION['voltar_nivel_3'] . '")') . "</td>");

$oForm->setHTML("
			</tr>
		</table>
		</p>
	</div>
	</div>
	");

$oForm->setHTML($oForm->getFechaForm());
print $oForm->getHTML();

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
