<?php
// conexao ao banco de dados
// funcoes diversas
include_once( "config.php" );

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH e sTabServidor');

// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();
$sessao_navegacao->initSessaoNavegacao();
$sessao_navegacao->setPagina($_SERVER['REQUEST_URI']);

//definindo a competencia de homologacao
$oData = new trata_datasys();
$mes   = $oData->getMesAnterior();
$ano   = $oData->getAnoAnterior();

// dados do usuário logado
$sLotacao = $_SESSION['sLotacao'];
$upag     = $_SESSION['upag'];

// instancia banco de dados
$oDBase    = new DataBase('PDO');
$oDBaseUnd = new DataBase('PDO');

// lista de servidores cedidos e descentralizados
// sem verificação de homologação da frequência
$oDBase->query("
    SELECT
    	cad.mat_siape, cad.nome_serv, cad.cod_lot, cad.jornada, cad.freqh, und.upag, und.descricao, IFNULL(hom.homologado,'N') AS homologado
    FROM
    	servativ AS cad
    LEFT JOIN
    	tabsetor AS und ON cad.cod_lot = und.codigo
    LEFT JOIN
    	homologados AS hom ON (cad.mat_siape = hom.mat_siape) AND (hom.compet = '" . $ano . $mes . "')
    WHERE
    	cad.cod_sitcad IN ('08','18') AND IFNULL(hom.homologado,'N') = 'N' AND und.upag = '" . $upag . "'
    ORDER BY
        cad.nome_serv
");
$num = $oDBase->num_rows();

// dados da unidade da SOGP
$oDBaseUnd->query("SELECT descricao FROM tabsetor WHERE codigo = '$sLotacao' ");
$unidade_descricao = $oDBaseUnd->fetch_object()->descricao;


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Relatórios » Frequência » Cedidos e descentralizados');
$oForm->setDialogModal();
$oForm->setLargura("950px");

$oForm->setSubTitulo("Relat&oacute;rio de Servidores Cedidos e Descentralizados no m&ecirc;s de homologa&ccedil;&atilde;o");

$oForm->setSeparador(0);

$oForm->setObservacaoTopo("
		Observação: Com as informações encaminhadas pelo Órgão cessionário ou de exercício descentralizado a Gestão de Pessoas (RH) deve<br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; lançar a frequência do servidor por intermédio do menu 'Frequência->Atualizar->Mês em homologação'.
	");

$oForm->setObservacaoBase("
		Observação: Com as informações encaminhadas pelo Órgão cessionário ou de exercício descentralizado a Gestão de Pessoas (RH) deve<br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; lançar a frequência do servidor por intermédio do menu 'Frequência->Atualizar->Mês em homologação'.
	");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<div class="row">
    <div class="col-md-2">
        M&ecirc;s <input name='mes' type='text' class='form-control alinhadoAoCentro' id='mes'  value='<?= tratarHTML($mes); ?>' size='7' readonly>
    </div>
    <div class="col-md-2">
        Ano <input name='ano' type='text' class='form-control alinhadoAoCentro' id='ano' value='<?= tratarHTML($ano); ?>' size='7' readonly>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        Lota&ccedil;&atilde;o
        <input name='lot' type='text' class='form-control alinhadoAoCentro' id='lot' value='<?= tratarHTML(getUorgMaisDescricao( $sLotacao )); ?>' size='60' readonly>
    </div>
</div>

<div align='left' style='text-align: left;'>Total de <?= number_format($num, 0, ',', '.'); ?> registros.</div>

<table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter">
    <tr bgcolor="#DBDBB7">
        <td align='center' style='width: 1%; height: 25px; text-align: center;'>&nbsp;<b>SEQ.&nbsp;</td>
        <td align='center' style='width: 13%; text-align: center;'><b>Matrícula</td>
        <td align='left'   style='width: 60%; text-align: left; text-indent: 3px;'><b>Nome<b></td>
        <td align='center' style='width: 12%; text-align: center;'><b>Lotação<b></td>
        <td align='center' style='width: 14%; text-align: center;'><b>Ação<b></td>
    </tr>
    <?php

    $regfreq8 = base64_encode(tratarHTML($pm->mat_siape) . ":|:" . tratarHTML($pm->cod_lot) . ":|:" . tratarHTML($pm->jornada));

    if ($num > 0)
    {
        while ($pm = $oDBase->fetch_object())
        {
            ?>
            <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)' height='18'>
                <td align='center'>&nbsp;<?= ++$sequencia; ?>&nbsp;</td>
                <td align='center'>&nbsp;<?= tratarHTML(removeOrgaoMatricula($pm->mat_siape)); ?>&nbsp;</td>
                <td align='left'>&nbsp;<?= tratarHTML($pm->nome_serv); ?></td>
                <td align='center'><?= tratarHTML($pm->cod_lot); ?>&nbsp;</td>
                <td align='center'>&nbsp;<a href='regfreq8.php?dados=<?= $regfreq8; ?>'>Verificar</a>&nbsp;</td>
            </tr>
            <?php
        }
    }
    else
    {
        mensagem("Não há servidores cedidos/descentralizados sem frequência!", null, 1);
    }

    ?>
</table>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
