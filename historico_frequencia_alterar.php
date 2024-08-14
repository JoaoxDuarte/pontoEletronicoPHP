<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "class_form.telas.php" );
include_once( "class_ocorrencias_grupos.php" );

// Verifica se existe um usuário logado e se possui permissão para este acesso
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
    $dia        = $dados[2];
    $ocor       = $dados[3];
    $lot        = $dados[4];
    $idreg      = $dados[5];
    $cmd        = $dados[6];
    $jnd        = $dados[7];
    $cod_sitcad = $dados[8];
    $pagina     = $dados[9];
}


$oDBase = selecionaServidor($mat);
$sitcad = $oDBase->fetch_object()->sigregjur;

## ocorrências grupos
$obj = new OcorrenciasGrupos();
$grupoOcorrenciasNegativasDebitos = $obj->GrupoOcorrenciasNegativasDebitos($sitcad);
$codigosCredito                   = $obj->CodigosCredito($sitcad);
$grupoOcorrenciasViagem           = $obj->GrupoOcorrenciasViagem($sitcad);

$debitos  = implode(',', array_merge($grupoOcorrenciasNegativasDebitos,$grupoOcorrenciasViagem));
$creditos = implode(',', $codigosCredito);


// dados voltar
$_SESSION['voltar_nivel_3'] = $dadosorigem;
$_SESSION['voltar_nivel_4'] = '';

$aJornada = explode(':', $jnd);
if (count($aJornada) > 1)
{
    $jnd = ($aJornada[0] * 5);
}

// Comentado temporariamente por não sabermos, de antemão, os IPs da aplicação
//include_once( "ilegal_acesso.php" );
## define competencia
#
$data = data2arrayBR($dia);
$mes  = $data[1];
$ano  = $data[2];
$year = $ano;
$comp = $mes . $year;

## classe para montagem do formulario
#
$oForm = new formTelas();
$oForm->setCaminho('Frequência » RH Atualizar » Histórico » Manutenção - Alterar');
$oForm->setShadowBox();
$oForm->setCSS(_DIR_CSS_ . "smoothness/jquery-ui-custom-px.min.css");
$oForm->setJS('historico_frequencia_alterar.js');
$oForm->setSeparador(0);
$oForm->setSeparadorBase(20);

$oForm->setSubTitulo("Hist&oacute;rico - Manuten&ccedil;&atilde;o de Ocorr&ecirc;ncia - Alterar");

## Dados do servidor
#
$oForm->setFormAction('#');
$oForm->setFormOnSubmit('return verificadados();');
$oForm->initInputHidden();
$oForm->setInputHidden('modo', 'alterar');
$oForm->setInputHidden('compete', $comp);
$oForm->setInputHidden('mes', $mes);
$oForm->setInputHidden('ano', $ano);
$oForm->setInputHidden('jnd', $jnd);
$oForm->setInputHidden('jn', formata_jornada_para_hhmm($jnd));
$oForm->setInputHidden('cmd', $cmd);
$oForm->setInputHidden('lot', $lot);
$oForm->setInputHidden('dias_no_mes', numero_dias_do_mes($mes, $ano));
$oForm->setInputHidden('hom1', '1');
$oForm->setInputHidden('horsaida', $hs);
$oForm->setInputHidden('horsaida2', $vHoras);

$grupo = agrupa_ocorrencias('__');

$oForm->setInputHidden('credito', $credito);
$oForm->setInputHidden('debito', $debito);
$oForm->setInputHidden('outros', "");

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
	<input type='hidden' id='dados_ocorrencia' name='dados_ocorrencia' value='" . base64_encode(tratarHTML($mat) . ':|:' . tratarHTML($dia) . ':|:' . tratarHTML($cmd) . ':|:' . tratarHTML($ocor)) . "'>
	<input type='hidden' id='dados_grupo' name='dados_grupo' value='" . base64_encode('outros|credito|debito') . "'>
	<input type='hidden' id='cod_sitcad' name='cod_sitcad' value='" . tratarHTML($cod_sitcad) . "'>
	<div align='center'>
	<table width='95%' border='1' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse; margin-bottom: 0;'>
	<tr>
	<td width='81%' height='39'>
	<p align='left' style='margin-top: 0; margin-bottom: 0'><font size='2' face='Tahoma'>&nbsp;C&oacute;digo da Ocorr&ecirc;ncia:</font></p>
	<p align='left' style='margin-top: 0; margin-bottom: 0'>
	");

// tabela de ocorrencia
$oForm->setHTML(montaSelectOcorrencias($ocor, '', false, false, true));

preparaShowDivIFrame('ver_tabela_ocorrencias', 990, 475);

$oForm->setHTML("
	<a id='ver_tabela_ocorrencias' href=\"#\" src=\"tabela_ocorrencia_de_frequencia_visualizar.php\" title='Visualizar Tabela de Ocorrência.'><img border='0' src='imagem/pesquisa.gif' width='17' height='17' align='absmiddle' alt='Visualizar Tabela de Ocorrência.'>
	</a></font></td>
	<td width='19%'>
	<p align='center' style='margin-top: 0; margin-bottom: 0'><font size='2' face='Tahoma'>Dia da Ocorr&ecirc;ncia:</font></p>
	<p align='center' style='margin-top: 0; margin-bottom: 0'>
	<input name='dia' type='text' class='Caixa' id='dia'  OnKeyPress=\"formatar(this, '##/##/####')\"  value='" . tratarHTML($dia) . "' size='11' maxlength='10' title='Digite o dia sem pontos e barras!!' readonly>
	</td>
	</tr>
	</table>
	<br>
	<br>
	<div align='center'>
		<p>
		<table border='0' align='center'>
			<tr>
			<td align='right'>" . botao('Continuar', 'javascript:return verificadados();') . "</td>
			<td>&nbsp;&nbsp;</td>
	");

$oForm->setHTML("<td align='center'>" . botao('Voltar', 'javascript:window.location.replace("historico_frequencia_registros.php?dados=' . tratarHTML($_SESSION['voltar_nivel_1']) . '");') . "</td>");

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
