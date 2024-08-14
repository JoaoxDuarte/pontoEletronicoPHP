<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH');

// será utilizada para carregar o SQL gerado para impressao
// sua alimentação será via ajax (jquery.js), a chamada
// encontra-se no sorttable.js
$_SESSION['sSQLPesquisa']   = "";
$_SESSION['sChaveCriterio'] = "";

// pesquisa de dados
$pesquisa = "";

$var1 = anti_injection($_REQUEST["chave"]);
$var2 = anti_injection($_REQUEST["escolha"]);

$_SESSION['sChaveCriterio'] = array("chave" => $var1, "escolha" => $var2);

$pesquisa = "
SELECT
    a.mat_siape, a.nome_serv, a.cpf, a.cod_cargo, a.cod_lot, b.upag
FROM
    servativ AS a
LEFT JOIN
    tabsetor AS b ON a.cod_lot = b.codigo
LEFT JOIN
    tabcargo AS c ON a.cod_cargo = c.cod_cargo
WHERE
    true
";

//$_SESSION["sLog"] = "N";

if ($_SESSION["sLog"] != "S")
{
    $pesquisa .= " AND upag = '" . $_SESSION['upag'] . "' ";
}

if ($_SESSION["sRH"] != "S" && $_SESSION["sAPS"] == "S")
{
    $pesquisa .= " AND cod_lot = '" . $_SESSION['sLotacao'] . "' ";
}

switch ($var2)
{
    case "siape":
        $pesquisa .= " AND mat_siape = '$var1' ";
        break;

    case "nome":
        $pesquisa .= " AND nome_serv LIKE '%$var1%' ";
        break;

    case "cargo":
        $pesquisa .= " AND (a.cod_cargo = '$var1'
            OR c.desc_cargo LIKE '%$var1%')
            AND (cod_sitcad NOT IN ('02','15'))
            AND excluido = 'N' ";
        break;

    case "lotacao":
        $pesquisa .= " AND cod_lot LIKE '$var1%' AND (cod_sitcad NOT IN ('02','15')) AND excluido = 'N' ";
        break;
}

$_SESSION['sSQLPesquisa'] = $pesquisa;
$pesquisa                .= ($var2 == "lotacao" ? "ORDER BY cod_lot " : "ORDER BY nome_serv ");

$sequencia = 1;

if (!empty($var2))
{
    $oTbDados  = new DataBase('PDO');
    $oTbDados->query($pesquisa);
    $nRows     = $oTbDados->num_rows();
}


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setJS( 'js/phpjs.js' );
$oForm->setJS( 'js/jquery.blockUI.js?v2.38' );
$oForm->setJS( 'js/jquery.bgiframe.js' );
$oForm->setJS( 'js/plugins/jquery.dlg.min.js' );
$oForm->setJS( 'js/plugins/jquery.easing.js' );
$oForm->setJS( 'js/jquery.ui.min.js' );

$oForm->setOnLoad("$('#chave').focus();");

if (isset($_REQUEST["chave"]))
{
    //$oForm->setIconeParaImpressao("pesquisa_servidor_imp.php");
}
else
{
    $_SESSION['sColunaSortTable'] = "";
}

// Topo do formulário
//
$oForm->setSubTitulo($sFormsubTitulo);

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<script language="javascript">

$(document).ready(function ()
{
    $("form").keypress(function (e) {
        if (e.witch == 13)
        {
            validar();
        }
    });

    $("#btn-continuar").click(function () {
        validar();
    });

});

function validar()
{
    if ($('#chave').val().length == 0)
    {
        $('#chave').focus();
        mostraMensagem('É obrigatório informar a chave para pesquisa!', 'warning');
        return false;
    }
    else
    {
        // mensagem processando
        showProcessando();

        $('#form1').attr('action', "<?= $sFormAcao; ?>");
        $('#form1').submit();
    }
}

</script>

<form method="POST" id="form1" name="form1">
    <input type="hidden" name="modo" value="<?= tratarHTML($modo); ?>" >
    <input type="hidden" name="corp" value="<?= tratarHTML($corp); ?>">
    <table border="0" width="100%" cellspacing="0" cellpadding="0" bordercolordark="white" bordercolorlight="#336699">
        <tr>
            <td width="100%" colspan="3" style="word-spacing: 0; margin: 0">&nbsp;</td>
        </tr>
        <tr>
            <td width="100%" colspan="3" style="word-spacing: 0; margin: 0">
                <label>ÓRGÃO:</label>

            </td>
            <td width="100%" colspan="3" style="word-spacing: 0; margin: 0">

                <label>UPAG:</label>
            </td>
        </tr>
        <tr>
            <td colspan='3' align="center" style="word-spacing: 0; margin: 0">&nbsp;</td>
        </tr>
        <tr>
            <td width="50%" colspan="3" style="word-spacing: 0; margin: 0">

                <div class="col-md-6">
                    <div class="form-group ">Mês :
                <input type="text" name="mes" id="mes" class="form-control" >
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group ">Ano :
                        <input type="text" name="ano" id="ano"  class="form-control" >
                    </div>
                </div>

            </td>
            <td width="50%" colspan="3" style="word-spacing: 0; margin: 0">


            </td>
        </tr>

        <tr>
            <td colspan='3' align="center" style="word-spacing: 0; margin: 0">&nbsp;</td>
        </tr>

    </table>

    <div class="col-md-2 col-xs-6 col-md-offset-5 margin-30 margin-bottom-30">
        <a class="btn btn-success btn-block" id="btn-continuar" role="button">
            <span class="glyphicon glyphicon-search"></span> Pesquisar
        </a>
    </div>

    <br>
    <br>
</form>
<?php

if ($nRows > 0)
{
    ?>

    <div class='col-md-12' id='total_de_registros'>
        Total de <?= number_format($nRows,0,',','.'); ?> registros.
    </div>
    <table class='table table-striped table-condensed table-bordered table-hover text-center'>
        <thead>
            <tr>
                <th width="7%"  class="text-center">&nbsp;</th>
                <th width="9%"  class="text-center"><b>&nbsp;CONSULTA&nbsp;</b></th>
                <th width="9%"  class="text-center"><b>&nbsp;SIAPE&nbsp;</b></th>
                <th width="50%" class="text-center"><b>&nbsp;NOME&nbsp;</b></th>
                <th width="10%" class="text-center"><b>&nbsp;CPF&nbsp;</b></th>
                <th width="10%" class="text-center"><b>&nbsp;CARGO&nbsp;</b></th>
                <th width="10%" class="text-center"><b>&nbsp;LOTAÇÃO&nbsp;</b></th>
            </tr>
        </thead>
        <tbody>
            <?php

            while ($pm_partners = $oTbDados->fetch_object())
            {
                ?>
                <tr>
                    <td width="7%"  class='text-center'>&nbsp;<?= tratarHTML($sequencia++); ?>&nbsp;</td>
                    <td width="9%"  class='text-center'>&nbsp;<a href="<?= tratarHTML($sFormDestino); ?>?dados=<?= criptografa($pm_partners->mat_siape); ?>">FUNCIONAL</a>&nbsp;</td>
                    <td width="9%"  class='text-center'>&nbsp;<?= tratarHTML($pm_partners->mat_siape); ?>&nbsp;</td>
                    <td width="50%" class='text-left'>&nbsp;<?= tratarHTML($pm_partners->nome_serv); ?>&nbsp;</td>
                    <td width="10%" class='text-center'>&nbsp;<?= tratarHTML($pm_partners->cpf); ?>&nbsp;</td>
                    <td width="10%" class='text-center'>&nbsp;<?= tratarHTML($pm_partners->cod_cargo); ?>&nbsp;</td>
                    <td width="10%" class='text-center'>&nbsp;<?= tratarHTML($pm_partners->cod_lot); ?>&nbsp;</td>
                </tr>
                <?php
            } // fim do while

            ?>
        </tbody>
    </table>
    <?php
}
else if (!empty($var2))
{
    unset($_REQUEST["chave"]);
    mensagem("Nenhum registro selecionado!");
}

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
