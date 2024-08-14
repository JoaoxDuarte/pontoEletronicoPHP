<?php

include_once( "config.php" );
include_once( "class_form.telas.php" );

verifica_permissao('sRH e sTabServidor');

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    $dados      = explode(":|:", base64_decode($dadosorigem));
    $mat        = $dados[0];
    $nome       = $dados[1];
    $lot        = $dados[2];
    $jnd        = formata_jornada_para_hhmm($dados[3]);
    $cod_sitcad = $dados[4];
    $cmd        = $dados[5];
    $tipo_acao  = $dados[6]; // tipo da acao, ex.: 'homologar_registros'
    $mes        = $dados[7];
    $ano        = $dados[8];
}

// dados voltar
$_SESSION['voltar_nivel_2'] = $dadosorigem;
$_SESSION['voltar_nivel_3'] = '';

## define competencia
#
$comp = $mes . $ano;

## instancia o banco de dados
$oDBase = new DataBase('PDO');

## classe para montagem do formulario
#
$oForm = new formTelas();
$oForm->setCaminho('Histórico » ... » Ocorrência » Alteração por Período');
$oForm->setShadowBox();
$oForm->setCSS(_DIR_CSS_ . 'estiloIE.css');
$oForm->setCSS(_DIR_CSS_ . "smoothness/jquery-ui-custom-px.min.css");
$oForm->setJS('historico_frequencia_alterar_periodo.js');
$oForm->setSeparador(0);
$oForm->setSeparadorBase(20);

$oForm->setSubTitulo("Histórico - Alteração de Ocorr&ecirc;ncia por Per&iacute;odo");

## Dados do servidor
#
$oForm->setFormAction('historico_frequencia_alterar_periodo_gravar.php');
$oForm->setFormOnSubmit('return validar();');
$oForm->initInputHidden();
$oForm->setInputHidden('modo', '10');
$oForm->setInputHidden('compete', $comp);
$oForm->setInputHidden('mes', $mes);
$oForm->setInputHidden('ano', $ano);
$oForm->setInputHidden('jnd', $jnd);
$oForm->setInputHidden('cmd', $cmd);
$oForm->setInputHidden('lot', $lot);
$oForm->setInputHidden('dias_no_mes', numero_dias_do_mes($mes, $ano));
$oForm->setInputHidden('hom1', '1');

$oForm->setFormNomeServidor($nome);
$oForm->setFormMatriculaSiape($mat);

$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

// monta a caixa de dialog para exibicao da mensagem/pagina
preparaDialogView(1020, 470);

$oForm->initHTML();
$oForm->setHTML($oForm->getAbreForm());

$oForm->setDadosDoServidor();
$oForm->setHTML($oForm->getInputHidden());

$oForm->setHTML("
		<div align='center'>
		<table width='95%' border='1' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse; margin-bottom: 0;'>
		<tr>
		<td width='63%' height='46' rowspan='2'>
		<p class='tahomaSize_2' align='left' style='margin-top: 0; margin-bottom: 0'>&nbsp;C&oacute;digo da Ocorr&ecirc;ncia:</p>
		<p align='left' style='margin-top: 0; margin-bottom: 0'>
	");

// tabela de ocorrencia
// montaSelectOcorrencias( <codigo da ocorrência>, <largura do campo select>, <se imprime/retorna texto>, <ocorrência para período> )
$oForm->setHTML(montaSelectOcorrencias($ocor, '', false, true, true));

preparaShowDivIFrame('ver_tabela_ocorrencias', 990, 475);

$oForm->setHTML("
		<a id='ver_tabela_ocorrencias' href=\"#\" src=\"tabela_ocorrencia_de_frequencia_visualizar.php\" title='Visualizar Tabela de Ocorrência.'><img border='0' src='" . _DIR_IMAGEM_ . "pesquisa.gif' width='17' height='17' align='absmiddle' alt='Visualizar Tabela de Ocorrência.'>
		</a></font></td>
    <td width='100%' align='center' colspan='2'>
		<p class='tahomaSize_2' align=\"center\" style=\"margin-top: 0; margin-bottom: 0\">
		Compet&ecirc;ncia:<br>
     <input name=\"competencia\" type=\"text\" class='centro' id=\"competencia\"  value=\"" . $mes . '/' . $ano . "\" size=\"10\" readonly>
		</p>
    </td>
		</tr>
		<tr>
		<td width='18%'>
			<p class='tahomaSize_2' align='center' style='margin-top: 0; margin-bottom: 0'>
			Dia Inicio da Ocorr&ecirc;ncia:<br>
			<input name='dia2' type='text' class='centro' id='dia2' value='' size='2' maxlength='2' onkeyup=\"javascript:ve(this.value);\" title='Digite o dia inicial do período, com dois dígitos!'>
			</p>
		</td>
		<td width='19%'>
			<p class='tahomaSize_2' align='center' style='margin-top: 0; margin-bottom: 0'>
			Dia Fim da Ocorr&ecirc;ncia:<br>
			<input name='dia' type='text' class='centro' id='dia' value='' size='2' maxlength='2' onkeyup=\"javascript:ve(this.value);\" title='Digite o dia final do período, com dois dígitos!'>
			</p>
		</td>
		</tr>
		</table>
		<br>
		<br>
		<div align='center'>
		<p>
		<table border='0' align='center'>
			<tr>
			<td align='right'>" . botao('Continuar', 'javascript:validar();') . "</td>
			<td>&nbsp;&nbsp;</td>
			<td align='left'>" . botao('Voltar', 'javascript:window.location.replace("historico_frequencia_registros.php?dados=' . $_SESSION['voltar_nivel_1'] . '");') . "</td>
			</tr>
		</table>
		</p>
		</div>
		</div>
	");

$oForm->setHTML($oForm->getFechaForm());
print $oForm->getHTML();

$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
