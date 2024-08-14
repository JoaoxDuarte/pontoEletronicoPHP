<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH ou Chefia');

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
    a.mat_siape, 
    a.nome_serv, 
    a.cpf, 
    a.cod_cargo, 
    a.cod_lot, 
    b.upag, 
    a.cod_sitcad,
    d.descsitcad,
    c.desc_cargo
FROM
    servativ AS a
LEFT JOIN
    tabsetor AS b ON a.cod_lot = b.codigo
LEFT JOIN
    tabcargo AS c ON a.cod_cargo = c.cod_cargo
LEFT JOIN
    tabsitcad AS d ON a.cod_sitcad = d.codsitcad
WHERE
    excluido = 'N'
    AND (cod_sitcad NOT IN ('02','15'))
    AND LEFT(a.cod_lot,5) = '". getOrgaoByUpag($_SESSION['upag'])."' 
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

        $novamatricula = getNovaMatriculaBySiape($var1);

        $pesquisa .= " AND mat_siape = '$novamatricula' ";
        break;

    case "nome":
        $pesquisa .= " AND nome_serv LIKE '%$var1%' ";
        break;

    case "cargo":
        $pesquisa .= " AND (a.cod_cargo = '$var1'
            OR c.desc_cargo LIKE '%$var1%') ";
        break;

    case "lotacao":
        $pesquisa .= " AND cod_lot LIKE '%$var1%' ";
        break;
}

$_SESSION['sSQLPesquisa'] = $pesquisa;
$pesquisa                .= ($var2 == "lotacao" ? "ORDER BY cod_lot " : "ORDER BY nome_serv ");

$sequencia = 1;

switch ($var2)
{
    case 'nome':
        $escolha_nome = 'selected';
        break;

    case 'cargo':
        $escolha_cargo = 'selected';
        break;

    case 'lotacao':
        $escolha_lotacao = 'selected';
        break;

    case 'siape':
        $escolha_siape = 'selected';
        break;

    case 'todos':
        $escolha_todos = 'selected';
        break;

    default:
        $escolha_nenhum = 'selected';
        break;
}

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
$oForm->setJS( 'pesquisa_servidor_formulario.js' );

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
<form method="POST" id="form1" name="form1" action="#" onSubmit="javascript:return false;">
    <input type="hidden" name="modo" value="<?= tratarHTML($modo); ?>" >
    <input type="hidden" name="corp" value="<?= tratarHTML($corp); ?>">
    <input type="hidden" id="form_action" name="form_action" value="<?= tratarHTML($sFormAcao); ?>">

    <div class="row margin-10">
        <div class="row">
            <div class="col-md-2 text-right" style="margin-top: 8px;">
                <p><b>Selecione o Filtro: </b></p>
            </div>
            <div class="col-md-4 text-left">
                <select class="form-control ciclos" name="escolha" id="escolha">
                    <option value=''        <?= tratarHTML($escolha_nenhum);  ?>> Selecione uma opção </option>
                    <option value='siape'   <?= tratarHTML($escolha_siape);   ?>> Por Siape </option>
                    <option value='nome'    <?= tratarHTML($escolha_nome);    ?>> Por Nome </option>
                    <option value='cargo'   <?= tratarHTML($escolha_cargo);   ?>> Por Cargo </option>
                    <option value='lotacao' <?= tratarHTML($escolha_lotacao); ?>> Por Lotação </option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2 text-right" style="margin-top: 8px;">
                <p><b>Chave: </b></p>
            </div>
            <div class="col-md-8 text-left">
                <input type="text" class='form-control' id="chave" name="chave" title="Não informe pontos" size="28" maxlength="28" value='<?= tratarHTML($var1); ?>'>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2 col-xs-6 col-md-offset-5 margin-30 margin-bottom-30">
                <button class="btn btn-success btn-block" id="btn-continuar" role="button">
                    <span class="glyphicon glyphicon-search"></span> Pesquisar
                </button>
            </div>
        </div>
    </div>
</form>

<div class='col-md-12' id='total_de_registros'>
    Total de <?= tratarHTML(number_format($nRows,0,',','.')); ?> registros.
</div>
<table class='table table-striped table-condensed table-bordered table-hover text-center'>
    <thead>
        <tr>
            <th width="7%"  class="text-center">&nbsp;</th>
            <th width="9%"  class="text-center"><b>&nbsp;CONSULTA&nbsp;</b></th>
            <th width="9%"  class="text-center"><b>&nbsp;SIAPE&nbsp;</b></th>
            <th width="50%" class="text-center"><b>&nbsp;NOME&nbsp;</b></th>
            <th width="10%" class="text-center"><b>&nbsp;CPF&nbsp;</b></th>
            <!-- <th width="10%" class="text-center"><b>&nbsp;SIT.CAD.&nbsp;</b></th> -->
            <th width="10%" class="text-center"><b>&nbsp;CARGO&nbsp;</b></th>
            <th width="10%" class="text-center"><b>&nbsp;LOTAÇÃO&nbsp;</b></th>
        </tr>
    </thead>
    <tbody>
    <?php

    if ($nRows > 0)
    {
        while ($pm_partners = $oTbDados->fetch_object())
        {
            ?>
            <tr>
                <td width="7%"  class='text-center'>&nbsp;<?= tratarHTML($sequencia++); ?>&nbsp;</td>
                <td width="9%"  class='text-center'>&nbsp;<a href="<?= tratarHTML($sFormDestino); ?>?dados=<?= criptografa(tratarHTML($pm_partners->mat_siape)); ?>">FUNCIONAL</a>&nbsp;</td>
                <td width="9%"  class='text-center'>&nbsp;<?= tratarHTML(substr($pm_partners->mat_siape,5,11)); ?>&nbsp;</td>
                <td width="50%" class='text-left'>&nbsp;<?= tratarHTML($pm_partners->nome_serv); ?>&nbsp;</td>
                <td width="10%" class='text-center'>&nbsp;<?= tratarHTML($pm_partners->cpf); ?>&nbsp;</td>
                <!--
                <td width="10%" class='text-center' alt='<?= tratarHTML($pm_partners->descsitcad); ?>' title='<?= tratarHTML($pm_partners->descsitcad); ?>'>&nbsp;<?= tratarHTML($pm_partners->cod_sitcad); ?>&nbsp;</td>
                -->
                <td width="10%" class='text-center' alt='<?= tratarHTML($pm_partners->desc_cargo); ?>' title='<?= tratarHTML($pm_partners->desc_cargo); ?>'>&nbsp;<?= tratarHTML($pm_partners->cod_cargo); ?>&nbsp;</td>
                <td width="10%" class='text-center'>&nbsp;<?= tratarHTML($pm_partners->cod_lot); ?>&nbsp;</td>
            </tr>
            <?php
        } // fim do while
    }
    else if (!empty($var2))
    {
        unset($_REQUEST["chave"]);
        //mensagem("Nenhum registro selecionado!");
        ?>
        <tr>
            <td colspan="7" width="100%" class='text-center'>&nbsp;Nenhum registro selecionado!&nbsp;</td>
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
