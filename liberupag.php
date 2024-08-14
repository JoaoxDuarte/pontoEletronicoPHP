<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");


// Verifica se existe um usu�rio logado e se possui permiss�o para este acesso
verifica_permissao('sRH e sTabServidor');

// limpa siape do servidor
// para que o teste de erro de upag
// possa funcionar corretamente;
unset($_SESSION['sMov_Matricula_Siape']);

// limpa sess�o
unset($_SESSION['sMov_Entra_Unidade']);
unset($_SESSION['sMov_Nova_Unidade']);



## classe para montagem do formulario padrao
#
$oForm = new formPadrao(); // instancia o formul�rio
$oForm->setJS('liberupag.js'); // script extras utilizados pelo formulario
$oForm->setSubTitulo("Libera��o de Servidores para outra UPAG");

// Topo do formul�rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


if (verificaSeOrgaoTemMaisUpags() == false)
{
    mensagem( "N�o existe UPAG para destina��o da libera��o!", "principal_abertura.php" );
    die();
}


?>
<form method='POST' id='form1' name='form1'>

    <div valign='middle' class='col-md-12 text-center'>
        <div valign='middle' class='col-md-3 col-lg-offset-4 text-center'>
            <table class='table table-striped table-condensed table-bordered text-center'>
                <tr>
                    <td class='text-center col-md-2'>
                        <font class="ft_13_003">
                        &nbsp;Matr�cula SIAPE<br>&nbsp;
                        <input type="text" id="siape" name="siape" class="form-control alinhadoAEsquerda" size="7" maxlength="7" value="" onkeyup="javascript:ve(this.value);">
                        </font>
                    </td>
                </tr>
            </table>
        </div>

        <div class="form-group col-md-8 text-center">
            <div class="col-md-7 col-md-offset-6 margin-10">
                <div class="col-md-6 text-right">
                    <a class="btn btn-success btn-primary" id="btn-continuar">
                        <span class="glyphicon glyphicon-ok"></span> Continuar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div style='text-align:right;width:90%;margin:25px;font-size:9px;border:0px;'>
            <fieldset style='border:1px solid white;text-align:left;'>
                <legend style='font-size:12px;padding:0px;margin:0px;'><b>&nbsp;Informa��es&nbsp;</b></legend>
                <p style='padding:1px;margin:0px;'>
                    <b>Matr�cula SIAPE&nbsp;:&nbsp;</b><b></b>Matr�cula do servidor/estagi�rio;
                </p>
            </fieldset>
        </div>
    </div>
</form>


    <div class="row margin-25">
        <table class="table table-striped table-bordered text-center table-hover">
            <thead>
                <tr>
                    <th colspan="5">
                        <h4 class="text-center">
                            <?= tratarHTML(getOrgaoMaisSigla($_SESSION['upag'])); ?>
                            <br>
                            <?= tratarHTML(getUorgMaisDescricao($_SESSION['upag'])); ?>
                        </h4>
                        <br>
                        <div class="text-right btn-xs" style="font-size:9px;font-weight:normal;">
                            <span class="glyphicon glyphicon-info-sign btn-xs text-left" aria-hidden="true"></span> Coloque o mouse sobre a imagem para ver a descri��o da unidade
                        </div>
                   </th>
                </tr>
                <tr>
                    <th class="text-center">MATR�CULA</th>
                    <th class="text-center">NOME</th>
                    <th class="text-center">LIBERADO</th>
                    <th class="text-center">DESTINO</th>
                    <th class="text-center">RECEBIDO</th>
                </tr>
            </thead>
            <tbody id='registros_selecionados' class='sse_listar'>
                <?php

                $oDBase = listaLiberadosParaOutraUpag();

                if ($oDBase->num_rows() === 0)
                {
                    ?>
                    <tr>
                        <td colspan='5'>
                            <font face='verdana' size='2'>N�o h� servidores liberados para outra UPAG!</font>
                        </td>
                    </tr>
                    <?php
                }

                if ($oDBase->num_rows() > 0)
                {
                    while ($pm = $oDBase->fetch_object())
                    {
                        ?>
                        <tr>
                            <td class="text-center">
                                <?= tratarHTML(removeOrgaoMatricula($pm->siape)); ?>
                            </td>
                            <td class="text-left" style="text-indent:3px;">
                                <?= tratarHTML($pm->nome); ?>
                            </td>
                            <td class="text-center">
                                <?=
                                tratarHTML(databarra($pm->dtlibera)) . '<br>' .
                                tratarHTML(removeOrgaoMatricula($pm->siape_registro));
                                ?>
                            </td>
                            <td class="text-center" >
                                <a href="javascript:void(0);"
                                   data-toggle="tooltip"
                                   title="<?= getOrgaoMaisSigla().'<br>'.tratarHTML($pm->destino); ?>"
                                   style="color:#52504E;text-decoration:none;">
                                <?= tratarHTML(removeOrgaoLotacao($pm->lotdest)); ?>
                                <span class="glyphicon glyphicon-info-sign btn-xs" aria-hidden="true"></span>
                                </a>
                            </td>
                            <td class="text-center">
                                <?php if ($pm->dtrecebe == '0000-00-00'): ?>
                                    <b style="color:red;font-weight:bold;">Pendente</b>
                                <?php else: ?>
                                    <?=
                                    tratarHTML(databarra($pm->dtrecebe)) . '<br>' .
                                    tratarHTML(removeOrgaoMatricula($pm->siape_recebe));
                                    ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                    }
                }

                ?>
            </tbody>
        </table>
    </div>
<?php

// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();


/*
 ************************************************
 *                                              *
 * FUN��ES DE APOIO                             *
 *                                              *
 ************************************************
 */

/*
 * Servidores Liberados
 */
function listaLiberadosParaOutraUpag()
{
    $upag = $_SESSION['upag'];

    // instancia a base de dados
    $oDBase = new DataBase('PDO');

    $oDBase->query( "
    SELECT
        liberupag.siape,
        (SELECT servativ.nome_serv
            FROM servativ
                WHERE servativ.mat_siape = liberupag.siape) AS nome,
        liberupag.lotor,
        liberupag.lotdest,
        (SELECT tabsetor.descricao
            FROM tabsetor
                WHERE tabsetor.codigo = liberupag.lotdest) AS destino,
        liberupag.dtlibera,
        liberupag.dtrecebe,
        liberupag.siape_registro,
        (SELECT servativ.nome_serv
            FROM servativ
                WHERE servativ.mat_siape = liberupag.siape_registro) AS nome_origem,
        liberupag.siape_recebe,
        (SELECT servativ.nome_serv
            FROM servativ
                WHERE servativ.mat_siape = liberupag.siape_recebe) AS nome_destino,
        liberupag.data_recebe,
        liberupag.data_registro
    FROM
        liberupag
    LEFT JOIN
        tabsetor ON liberupag.lotor = tabsetor.codigo
    WHERE
        tabsetor.upag = :upag
    ",
    array(
        array( ':upag', $upag, PDO::PARAM_STR),
    ));

    return $oDBase;
}


/*
 * @info Verifica se o �rg�o possui mais de uma UPAG
 *
 * @param void
 * @return boolean True : h� mais de uma UPAG
 */
function verificaSeOrgaoTemMaisUpags()
{
    $orgao = getOrgaoByUpag($_SESSION['upag']);

    // instancia a base de dados
    $oDBase = new DataBase('PDO');

    $oDBase->query( "
    SELECT
        tabsetor.upag
    FROM
        tabsetor
    WHERE
        LEFT(tabsetor.upag,5) = :orgao
    GROUP BY
        tabsetor.upag
    ORDER BY
        tabsetor.upag
    ",
    array(
        array( ':orgao', $orgao, PDO::PARAM_STR),
    ));

    return ($oDBase->num_rows() > 1);
}
