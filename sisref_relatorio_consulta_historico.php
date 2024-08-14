<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao("sRH");

// dados via formulario
$siape = getNovaMatriculaBySiape($_REQUEST['pSiape']);

// instancia banco de dados
$oDBase = new DataBase('PDO');

// consulta historico de lotacao
$oDBase->query("
		SELECT
			hlot.siape, cad.nome_serv AS nome, hlot.cod_lot, und.codigo, und.descricao, DATE_FORMAT(hlot.dt_ing_lot,'%d/%m/%Y') AS dt_ing_lot, DATE_FORMAT(hlot.dt_sai_lot,'%d/%m/%Y') AS dt_sai_lot
		FROM
			histlot AS hlot
		LEFT JOIN
			servativ AS cad ON hlot.siape = cad.mat_siape
		LEFT JOIN
			tabsetor AS und ON hlot.cod_lot = und.codigo
		WHERE
			hlot.siape = '" . $siape . "'
		ORDER BY
			hlot.dt_ing_lot DESC
	");

$oHistorico = $oDBase->fetch_object();

## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho('Relatório » Movimentação » Consulta Histórico - Lista');
$oForm->setCSS(_DIR_CSS_ . 'smoothness/jquery-ui-custom-px.min.css');
$oForm->setDialogModal();
$oForm->setSeparador(0);

$oForm->setSubTitulo("Histórico de Movimentação do Servidor");

// Topo do formulário
//
$oForm->exibeTopoHTML();

$html            = '';
$html_corpo      = $oForm->montaCorpoTopoHTML();
$html_corpo_base = $oForm->montaCorpoBaseHTML();


if ($oDBase->num_rows() == 1)
{
    mensagem("Servidor não encontrado!", null, 1);
}
else
{
    $html .= "
	  <table border='1' cellpadding='0' cellspacing='0' style='border-collapse: collapse; vertical-align: top' valign='top' bordercolor='#808040' width='99%'>
			<tr>
				<td colspan='3' height='25px' bgcolor='#DFDFBF'>
					<p align='center'><b>HISTÓRICO DE LOTAÇÕES DO SERVIDOR</b></p>
				</td>
			</tr>
			<tr>
				<td colspan='3' height='30px'>
					<p align='left' valign='middle'>
					<strong>&nbsp;Matr&iacute;cula</strong>:
					<input type='text' name='siape' size='7' readonly='7' class='alinhadoAEsquerda' value='" . tratarHTML(removeOrgaoMatricula($oHistorico->siape)) . "'>
					<strong>Nome</strong>:
					<input type='text' name='nome' size='60' readonly='60' class='alinhadoAEsquerda' value='" . tratarHTML($oHistorico->nome) . "'>
					</p>
				</td>
			</tr>
			<tr>
				<td width='64%' bgcolor='#DFDFBF' style='padding: 2px;'>&nbsp;<strong>Setores de Lota&ccedil;&atilde;o</strong></td>
				<td width='15%' bgcolor='#DFDFBF' align='center'><strong>Data de Ingresso</strong></td>
				<td width='15%' bgcolor='#DFDFBF' align='center' nowrap><strong>Data de Sa&iacute;da</strong></td>
			</tr>
		";

    $unidade_atual = true;
    $oDBase->data_seek();
    while ($oHistorico    = $oDBase->fetch_object())
    {
        $html          .= "
			<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)'>
				<td class='tahomaSize_2' width='64%' height='25px' align='left' >
				  <div style='float: left; width: 15px; text-align: center; vertical-align: bottom;'>
						<img src='" . _DIR_IMAGEM_ . ($unidade_atual == true ? "ativar_on.gif" : "ativar_off.gif") . "'>
					</div>
				  <div style='float: left; width: 80%; text-align: left; vertical-align: middle;'>
						" . tratarHTML($oHistorico->cod_lot) . " - " . tratarHTML($oHistorico->descricao) . "
					</div>
				</td>
				<td class='tahomaSize_2' width='l5%' align='center'>" . tratarHTML($oHistorico->dt_ing_lot) . "</td>
				<td class='tahomaSize_2' width='15%' align='center'>" . tratarHTML($oHistorico->dt_sai_lot) . "</td>
			</tr>
			";
        $unidade_atual = false;
    } // fim do while

    $html .= "
		</table>
		<div class='tahomaSize_1' width='100%' align='left'>
			&nbsp;<img src='" . _DIR_IMAGEM_ . "ativar_on.gif'> Indica a unidade atual de lotação do servidor
		</div>
		";
}

print $html_corpo;

print $html;

print $html_corpo_base;

// Base do formulário
//
$oForm->exibeBaseHTML();
