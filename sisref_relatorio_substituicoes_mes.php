<?php
// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// permissao de acesso
verifica_permissao("sRH");

// valores passados por formulário
$upag = ltrim(rtrim($_SESSION['upag']));
$mes  = $_REQUEST['mes'];
$ano  = $_REQUEST['ano'];

$competencia = $ano . $mes;

// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();

// instancia o banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setMensagem("Não foi possível selecionar dados da substituição");

// selecão dos dados
$_SESSION['sisref_relatorio_mes'] = $mes;
$_SESSION['sisref_relatorio_ano'] = $ano;
$_SESSION['sisref_relatorio']     = "
		SELECT
			subs.siape, subs.sigla, DATE_FORMAT(subs.inicio, '%d/%m/%Y') AS inicio, DATE_FORMAT(subs.fim, '%d/%m/%Y') AS fim, cad.nome_serv, subs.motivo, subs.numfunc, cad.cod_lot, chf.upag, chf.num_funcao
		FROM
			substituicao AS subs
		LEFT JOIN
			servativ AS cad ON subs.siape = cad.mat_siape
		LEFT JOIN
			tabfunc AS chf ON subs.numfunc = chf.num_funcao
		WHERE
			DATE_FORMAT(subs.inicio, '%Y%m') = '$competencia'
			AND chf.upag = $upag
		ORDER BY
			cad.nome_serv
	";

$oDBase->query($_SESSION['sisref_relatorio']);
$num = $oDBase->num_rows();

// dados da gerencia executiva
select_dadosgex($_SESSION['sLotacao'], $codgex, $nomegex, $ufgex, $idger);

## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho('Relatório » Gerencial » Substituições » Substituições da UPAG - Lista');
$oForm->setCSS(_DIR_CSS_ . 'estiloIE.css');
$oForm->setSeparador(0);
$oForm->setLargura(820);

$oForm->setIconeParaImpressao("sisref_relatorio_substituicoes_mes_pdf.php");

$oForm->setSubTitulo("Relatório de Substituições");

$oForm->setObservacaoTopo("Emitido em: " . date("d/m/Y"));

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="100%" id="AutoNumber1">
    <tr>
        <td width="100%"  align='center' style='font-family:verdana; font-size:12pt'>
            <font size="2" face="Tahoma"><?= tratarHTML($codgex) . ' - ' . tratarHTML($nomegex); ?></font>
        </td>
    </tr>
    <tr>
        <td width="100%"  align='center' style='font-family:verdana; font-size:12pt'>
            <font size="2" face="Tahoma">
            M&ecirc;s <input name="mes" type="text" class='alinhadoAoCentro' id="mes"  value='<?= tratarHTML($mes); ?>' size="7" readonly>
            Ano <input name="ano" type="text" class='alinhadoAoCentro' id="ano" value='<?= tratarHTML($ano); ?>' size="7" readonly>
            </font>
        </td>
    </tr>
</table>
<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#F1F1E2" width="100%" id="AutoNumber2" >
    <tr bgcolor="#DBDBB7">
        <td width="12%" align='center' height='18'><b>Matr&iacute;cula</b></td>
        <td width="50%"><b>&nbsp;Nome</b></td>
        <td width="11%"><b>Fun&ccedil;&atilde;o</b></td>
        <td width="14%" align='center'><b>Inicio</b></td>
        <td width="13%" align='center'><b>Fim</b></td>
    </tr>
    <?php
    if ($num > 0)
    {
        while ($pm = $oDBase->fetch_object())
        {
            ?>
            <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)' height='18'>
                <td align='center'><?= tratarHTML(removeOrgaoMatricula($pm->siape)); ?></td>
                <td><?= tratarHTML($pm->nome_serv); ?></td>
                <td align='center'><?= tratarHTML($pm->sigla); ?></td>
                <td align='center'><?= tratarHTML($pm->inicio); ?></td>
                <td align='center'><?= tratarHTML($pm->fim); ?></td>
            </tr>
            <?php
        }
    }
    else
    {
        ?>
        <font face='verdana' size='2'>Não há registros para essa competência!</font>
        <?php
    }
    ?>
</table>
<table align='center' border='0' width='100%' cellspacing='0' cellpadding='0'>
    <tr><td align='left'>
            <font size='1'>Obs: O relat&oacute;rio demonstra os registros de substitui&ccedil;&atilde;o que se iniciaram na compet&ecirc;ncia informada.</font>
        </td></tr>
</table>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
