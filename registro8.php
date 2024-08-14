<?php

include_once( "config.php" );
include_once( "class_form.telas.php" );
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao("sRH ou Chefia");

// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();
$sessao_navegacao->setPagina($_SERVER['REQUEST_URI']);

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    $mat   = anti_injection($_REQUEST["mat"]);
    $nome  = anti_injection($_REQUEST["nome"]);
    $dia   = $_REQUEST["dia"];
    $ocor  = anti_injection($_REQUEST["ocor"]);
    $lot   = anti_injection($_REQUEST["lot"]);
    $cmd   = $_REQUEST["c"];
    $idreg = anti_injection($_REQUEST["idreg"]);
    $jnd   = anti_injection($_REQUEST["jnd"]);
}
else
{
    $dados = explode(":|:", base64_decode($dadosorigem));
    $mat   = $dados[0];
    $nome  = $dados[1];
    $dia   = $dados[2];
    $ocor  = $dados[3];
    $lot   = $dados[4];
    $idreg = $dados[5];
    $cmd   = $dados[6];
    $jnd   = $dados[7];
}

$aJornada = explode(':', $jnd);
if (count($aJornada) > 1)
{
    $jnd = ($aJornada[0] * 5);
}

include_once( "ilegal4.php" );

## define competencia
#
$data = data2arrayBR($dia);
$mes  = $data[1];
$ano  = $data[2];
$year = $ano;
$comp = $mes . $year;


## ocorrências grupos
$obj = new OcorrenciasGrupos();
$codigosCompensaveis = $obj->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios=true);
$codigosCredito      = $obj->CodigosCredito($sitcad,$temp=false);


## instancia o banco de dados
$oDBase = new DataBase('PDO');

// frequencia
$oDBase->query("
		SELECT
			pto.entra, pto.intini, pto.intsai, pto.sai
		FROM
			ponto" . $mes . $ano . " AS pto
		WHERE
			pto.dia = '" . conv_data($dia) . "' AND pto.siape = '" . $mat . "'
	");
$oPonto = $oDBase->fetch_object();


## classe para montagem do formulario
#
$oForm = new formTelas();
$oForm->setJS('registro8.js');
$oForm->setSeparador(0);
$oForm->setSeparadorBase(20);
$oForm->setCaminho('Frequência » ... » Ocorrência');

$oForm->setSubTitulo("Registro de Ocorr&ecirc;ncia");

## Dados do servidor
#
$oForm->setFormAction('gravaregfreq2.php');
$oForm->setFormOnSubmit('return verificadados();');
$oForm->initInputHidden();
$oForm->setInputHidden('modo', '3');
$oForm->setInputHidden('compete', $comp);
$oForm->setInputHidden('mes', $mes);
$oForm->setInputHidden('ano', $ano);
$oForm->setInputHidden('jnd', $jnd);
$oForm->setInputHidden('jn', $jnd / 5);
$oForm->setInputHidden('cmd', $cmd);
$oForm->setInputHidden('lot', $lot);
$oForm->setInputHidden('dias_no_mes', numero_dias_do_mes($mes, $ano));
$oForm->setInputHidden('hom1', '1');
$oForm->setInputHidden('horsaida', $hs);
$oForm->setInputHidden('horsaida2', $vHoras);
$oForm->setInputHidden('lot', $lot);
$oForm->setInputHidden('cmd', $cmd);

$oForm->setInputHidden('idreg',  $idreg);
$oForm->setInputHidden('entra',  $oPonto->entra);
$oForm->setInputHidden('iniint', $oPonto->intini);
$oForm->setInputHidden('fimint', $oPonto->intsai);
$oForm->setInputHidden('hsaida', $oPonto->sai);

$oForm->setInputHidden('debitosCompensaveis', implode(',', $codigosCompensaveis));
$oForm->setInputHidden('codigosCreditos',     implode(',', $codigosCredito));

$oForm->setFormMatriculaSiape(removeOrgaoMatricula($mat));
$oForm->setFormNomeServidor($nome);

$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

$oForm->initHTML();
$oForm->setHTML($oForm->getAbreForm());

$oForm->setDadosDoServidor();
$oForm->setHTML($oForm->getInputHidden());

$oForm->setHTML("
	<div align='center'>
	<table width='95%' border='1' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse; margin-bottom: 0;'>
	<tr>
	<td width='81%' height='39'>
	<p align='left' style='margin-top: 0; margin-bottom: 0'><font size='2' face='Tahoma'>C&oacute;digo da Ocorr&ecirc;ncia:</font></p>
	<p align='left' style='margin-top: 0; margin-bottom: 0'>
	<select name='ocor' size='1' class='drop' id='ocor' title='Selecione a ocorrência!'>");

// tabela de ocorrencia
$oDBase->query("SELECT siapecad, desc_ocorr, cod_ocorr FROM tabocfre WHERE resp IN ('CH','AB'" . ($_SESSION["sRH"] == "S" ? ",'RH'" : "") . ") AND (siapecad != '00195') AND ativo = 'S' ORDER BY desc_ocorr ");
while ($campo = $oDBase->fetch_array())
{
    $oForm->setHTML("<option value='" . $campo[0] . "'" . ($campo[0] == $ocor ? ' selected' : '') . ">" . ($campo[0] == '-----' ? '----- Selecione uma Ocorrência -----' : $campo[0] . " - " . substr($campo[1], 0, 60) . " -  SIRH " . $campo[2]) . "</option>");
}
// Fim da tabela de ocorrencia

$oForm->setHTML("
	</select>
	<a href=\"javascript:Abre('tabocfre.php',1060,350)\"><img border='0' src='" . _DIR_IMAGEM_ . "pesquisa.gif' width='17' height='17' align='absmiddle' alt='Visualizar detalhes da ocorrência.'>
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
			<td align='right'>" . botao('Continuar', 'javascript: verificadados();') . "</td>
			<td>&nbsp;&nbsp;</td> ");

if (substr_count($sessao_navegacao->getPagina(0), 'regfreq2.php') > 0)
{
    $oForm->setHTML("<td align='center'>" . botao('Voltar', 'javascript:voltar(1,"' . $sessao_navegacao->getPaginaAnterior() . '");') . "</td>");
}
else
{
    $oForm->setHTML("<td align='center'>" . botao('Voltar', 'javascript:voltar(1,"' . $sessao_navegacao->getPagina(0) . '");') . "</td>");
}

$oForm->setHTML("
			</tr>
		</table>
		</p>
	</div>
	</div>");

$oForm->setHTML($oForm->getFechaForm());
print $oForm->getHTML();

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
