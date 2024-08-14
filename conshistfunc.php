<?php
include_once( "config.php" );

verifica_permissao("sRH");

$siape = anti_injection($_POST['pSiape']);
$pSiape = $_SESSION["orgao"].$siape;

// instancia o BD
$oDBase = new DataBase('PDO');

// dados
$oDBase->query("SELECT hist.mat_siape, hist.nome_serv, hist.sit_ocup, hist.num_funcao,
date_format(hist.dt_inicio, '%d/%m/%Y') as dt_inicio, date_format(hist.dt_fim, '%d/%m/%Y') as dt_fim,
func.cod_lot, func.cod_funcao, func.num_funcao, func.desc_func, func.registro_siape
FROM historic as hist
LEFT JOIN tabfunc as func on func.num_funcao = hist.num_funcao
WHERE hist.mat_siape = '$pSiape'
ORDER BY hist.dt_inicio");

$oHist = $oDBase->fetch_object();
$siape = $oHist->mat_siape;
$nome  = $oHist->nome_serv;


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho("Relatórios » Gerencial » Ocupantes de Fun&ccedil;&otilde;es");
$oForm->setJQuery();
$oForm->setCSS(_DIR_CSS_ . "estiloIE.css");
$oForm->setOnLoad("javascript: if($('#pSiape')) { $('#pSiape').focus() };");
$oForm->setLargura('920px');
$oForm->setSeparador(0);

$oForm->setSubTitulo("Consulta Histórico de Funções do Servidor");

$oForm->setObservacaoTopo("Informe a matrícula do servidor");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
<table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter">
    <tr>
        <td colspan="5">
            <p align="left">
                <div class="row">
                <div class="col-md-1">
                    <strong>Matr&iacute;cula</strong>:
                </div>
                <div class="col-md-2">
                    <input type="text" name="siape" size="7" class='form-control alinhadoAEsquerda' value="<?= tratarHTML($siape); ?>" readonly>
                </div>
                <div class="col-md-1">
                    <strong>Nome</strong>:
                </div>
                <div class="col-md-6">
                    <input type="text" name="nome" size="60" class='form-control alinhadoAEsquerda' value="<?= tratarHTML($nome); ?>" readonly>
                </div>
                </div>


        </td>
    </tr>
    <tr>
        <td width="54%">&nbsp;<strong>Fun&ccedil;&otilde;es Exercidas</strong></td>
        <td width="9%"><div align="center"><strong>Lota&ccedil;&atilde;o</strong></div></td>
        <td width="9%"><div align="center"><strong>S&iacute;mbolo</strong></div></td>
        <td width="14%"> <p align="center"><strong>Data de ingresso</strong></td>
        <td width="14%"> <p align="center"><strong>Data de sa&iacute;da</strong></td>
    </tr>
    <?php

    if ($oDBase->num_rows() == 0)
    {
        mensagem("Não Consta histórico de exercício de funções para esse servidor!");
    }

    $pm_partners = $oHist;
    do
    {
        ?>
        <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)'>
            <td width="54%" align='left'  ><font size="1" face="Tahoma"><?= tratarHTML($pm_partners->desc_func); ?></font></td>
            <td width="9%"  align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners->cod_lot); ?></font></td>
            <td width="9%"  align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners->cod_funcao); ?></font></td>
            <td width="14%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners->dt_inicio); ?></font></td>
            <td width="14%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners->dt_fim); ?></font></td>
        </tr>
        <?php
    }
    while ($pm_partners = $oDBase->fetch_object());
    // fim do while

    ?>
</table>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
