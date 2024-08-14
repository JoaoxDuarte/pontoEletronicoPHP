<?php
// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("sLog");

$inicio = $_REQUEST['inicio'];
$final  = $_REQUEST['fim'];
$chave  = $_REQUEST['chave'];

// instancia o BD
$oDBase = new DataBase('PDO');
    
if ($chave == "")
{
    $pesquisa = "
    SELECT 
        aconteceu, 
        DATE_FORMAT(datag, '%d/%m/%Y') AS datainicio 
    FROM 
        dedo 
    WHERE 
        datag BETWEEN '" . conv_data($inicio) . "' AND '" . conv_data($final) . "' 
    ";
}
else
{
    $chave    = "%" . $chave . "%";
    
    $pesquisa = "
    SELECT 
        aconteceu, 
        DATE_FORMAT(datag, '%d/%m/%Y') AS datainicio 
    FROM 
        dedo 
    WHERE 
        aconteceu LIKE '$chave' 
        AND datag BETWEEN '" . conv_data($inicio) . "' AND '" . conv_data($final) . "' 
    ";
}

$oDBase->query($pesquisa);


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho('Tabelas » Lotações » Manutenção');
$oForm->setSeparador(0);

$oForm->setSubTitulo("Relatório de Auditoria do Sistema");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<div id='container'>
    
    <table border="0" width="100%" cellspacing="0" cellpadding="0" bordercolordark="white" bordercolorlight="#336699" >
        <tr>
            <td width="91%" height="67">
                <h3>Eventos ocorridos entre <?= tratarHTML($inicio); ?>&nbsp;a&nbsp;<?= tratarHTML($final); ?></h3>
            </td>
        </tr>
    </table>
    
    <table id="myTable" class="table table-striped table-condensed table-bordered table-hover text-left">
        <thead>
            <tr bgcolor="">
                <th width="100%">
                    <p style="word-spacing: 0; margin: 0">
                        <font face="Verdana" size="1" color="#">&nbsp;<b>EVENTO</b></font>
                    </p>
                </th>
            </tr>
        </thead>
        
        <tbody>
            <?php while ($pm_partners = $oDBase->fetch_array()): ?>
        
                <tr>
                    <td width="100%">
                        <p style="word-spacing: 0; margin: 0">
                            <font face="Verdana" size="1" color="#808080">
                                <?= tratarHTML($pm_partners['aconteceu']); ?>&nbsp;do dia&nbsp;<?= tratarHTML($pm_partners['datainicio']); ?>
                            </font>
                        </p>
                    </td>
                </tr>
        
            <?php endwhile; ?>
            
        </tbody>
</table>
</div>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
