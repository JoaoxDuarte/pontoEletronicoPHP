<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
//include_once("PogProgressBar.php");
// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('estrategica');

$mes = anti_injection($_REQUEST["mes"]);
$ano = anti_injection($_REQUEST["ano"]);

$comp = $ano . '-' . $mes;
$pesq = $mes . $ano;

// instancia o bano de dados
$oDBase = new DataBase('PDO');
$oDBase->query('
		SELECT
			hpto.siape, cad.nome_serv, cad.cod_sitcad
		FROM
			histponto' . $pesq . ' AS hpto
		LEFT JOIN
			servativ AS cad ON hpto.siape = cad.mat_siape
		GROUP BY
			hpto.siape
		ORDER BY
			cad.nome_serv
	');
$tot    = $oDBase->num_rows();

//$objBar = new PogProgressBar( 'pb' );
//$objBar->setTheme( 'blue');
//$objBar->draw('');

$nlinha = 0;

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Utilitários » Auditoria » Registros alterados » Alteração de frequência » Lista');
$oForm->setSubTitulo("Registro de Frequ&ecirc;ncia Alterado ou Exclu&iacute;do");
$oForm->setSeparador(0);

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

echo '
	<table class="thin sortable draggable" border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#F1F1E2" width="100%" id="AutoNumber2">
		<tr bgcolor="#DBDBB7">
			<td width="8%" align="center"><b> <div align="center">Siape</div></td>
			<td width="58%"><b>Nome</td>
			<td width="16%"><div align="center"><strong>Situa&ccedil;&atilde;o</strong></div></td>
			<td width="18%"><div align="center"><strong>A&ccedil;&atilde;o</strong></div></td>
		</tr>
	';

while ($pm = $oDBase->fetch_object())
{
    $sit = ($pm->cod_sitcad == "66" ? 'ETG' : 'RJU');

    echo '
		<tr onmouseover="pinta(1,this)" onmouseout="pinta(2,this)" height="18">
		<td align="center"><font color="#000000">' . tratarHTML($pm->siape) . '</td>
		<td>' . tratarHTML($pm->nome_serv) . '</td>
		<td align="center">' . tratarHTML($sit) . '</td>
		<td align="center"><a href="histfreq.php?mat=' . tratarHTML($pm->siape) . '&comp=' . tratarHTML($pesq) . '" target="new">Visualizar Registros</a></td>
		</tr>';

    //$nlinha++;
    //$msg_processando = 'Processando '.$pm->nome_serv.' ( '.round($nlinha * 100 / $tot).' / '.$tot.' )';
    //$objBar->setProgress( ($nlinha * 100 / $tot), $nlinha, $tot, 'top', $msg_processando );
    //usleep( 40 );
}

echo '
	</table>
	<table width="100%" border="1" cellpadding="0" cellspacing="0" bordercolor="#F1F1E2" class="thin sortable draggable" id="AutoNumber2" style="border-collapse: collapse" >
		<tr bgcolor="#DBDBB7">
			<td width="82%"><div align="center"><strong>Total</strong></div></td>
			<td width="18%"><div align="center"><strong>' . tratarHTML($tot) . '</strong></div></td>
		</tr>
	</table>';

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

//$objBar->hide();
