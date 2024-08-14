<?php

include_once("config.php");

verifica_permissao('sRH ou sTabServidor');

// dados em sessao
$sLotacao = $_SESSION['sLotacao'];
$upag     = $_SESSION['upag'];

// unidade e data, dia, ano e competência
$qlotacao = $sLotacao;
$vDatas   = date("Y-m-d");
$dia      = date("d/m/Y");
$ano      = date('Y');
$comp     = date('mY');

// instancia banco de dados
$oDBase = new DataBase('PDO');

// seleção dos dados
// servidor e setor
$oDBase->query("
SELECT
    a.mat_siape, a.nome_serv, a.cod_lot, b.lotdest,
    DATE_FORMAT(b.dtlibera, '%d/%m/%Y') AS dtlibera,
    DATE_FORMAT(b.data_registro, '%d/%m/%Y') AS data_registro,
    c.descricao,
    IFNULL(d.sigla,'') AS sigla, IFNULL(d.denominacao,'') AS denominacao
FROM
    servativ AS a
LEFT JOIN
    liberupag AS b ON a.mat_siape = b.siape
LEFT JOIN
    tabsetor AS c ON b.lotdest = c.codigo
LEFT JOIN
    taborgao AS d ON LEFT(b.lotdest,5) = d.codigo
WHERE
    /*c.upag = :upag
    AND */b.dtrecebe = '0000-00-00'
ORDER BY
    a.nome_serv
",
array(
    array( ':upag', $upag, PDO::PARAM_STR ),
));
$nRows = $oDBase->num_rows();


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setSubTitulo("Servidores Pendentes de Recebimento na UPAG");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<table class="table table-condensed table-bordered table-hover text-center">
    <thead>
        <tr>
            <th width="11%" class='text-center' style='vertical-align:middle;'>SIAPE</th>
            <th width="64%" class='text-center' style='vertical-align:middle;'>Nome</th>
            <th width="15%" class='text-center' style='vertical-align:middle;'>Dia Liberação</th>
            <th width="15%" class='text-center' style='vertical-align:middle;'>Liberação Registrada Em</th>
            <th width="10%" class='text-center' style='vertical-align:middle;'>Ação</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($nRows > 0)
        {
            while ($pm = $oDBase->fetch_object())
            {
                ?>
                <tr height='18'>
                    <td class='text-center'><?= tratarHTML(removeOrgaoMatricula($pm->mat_siape)); ?></td>
                    <td class='text-left'>
                        <?= tratarHTML($pm->nome_serv); ?><br>
                        <b>Destino:</b>
                        <?php

                        if (strlen(trim($pm->sigla)) > 0)
                        {
                            print tratarHTML(substr($pm->lotdest,0,5)) . ' - ' . tratarHTML($pm->sigla) . "<br>";
                            print "<img src='imagem/transp1x1.gif' width='10px'>";
                            print tratarHTML(substr($pm->lotdest,0,5))
                                . '.'   . tratarHTML(substr($pm->lotdest,5))
                                . ' - ' . tratarHTML($pm->descricao);
                        }
                        else
                        {
                            print tratarHTML($pm->lotdest) . ' - ' . tratarHTML($pm->descricao);
                        }

                        ?>
                    </td>
                    <td class='text-center'><?= tratarHTML($pm->dtlibera); ?></td>
                    <td class='text-center'><?= tratarHTML($pm->data_registro); ?></td>
                    <td class='text-center' title='Utilize essa opção para receber o servidor em sua UPAG.'><a href='gravaliberupag.php?modo=2&mat=<?= tratarHTML($pm->mat_siape); ?>'>Receber</a></td>
                </tr>
                <?php
            }
        }
        else
        {
            ?>
            <tr height='18'>
                <td colspan='4'>Sem registro para exibir</td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
