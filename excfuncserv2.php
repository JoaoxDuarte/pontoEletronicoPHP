<?php

include_once("config.php");

verifica_permissao('sRH e sTabServidor');

// recebe os parametros passados
$matricula  = anti_injection($_REQUEST['matricula']);

// recupera dados gravados em sessao
if (isset($_SESSION['sSiape']) && $_SESSION['sSiape'] != NULL)
{
    $matricula = $_SESSION['sSiape'];
    $funcao    = $_SESSION['sNumFuncao'];
    $exclusao  = $_SESSION['sExclusao'];
}

// grava em sessao dados do script atual
//$_SESSION['sHOrigem_1'] = "excfuncserv.php";
$_SESSION['sHOrigem_2'] = 'excfuncserv2.php'; //$_SERVER['REQUEST_URI']; // historico_regfreq3.php
$_SESSION['sHOrigem_3'] = '';
$_SESSION['sHOrigem_4'] = '';

// Pesquisa no cadastro
// Se não achar, exibe msg erro
// e volta a página anterior
$oDBase = verificaCadastro( $matricula );

// dados do servidor
$oServidor = $oDBase->fetch_object();
$nome      = $oServidor->nome_serv;
$sitcad    = $oServidor->cod_sitcad;
$lotat     = $oServidor->cod_lot;
$dinglota  = $oServidor->dt_ing_lot;
$ocupa_funcao = $oServidor->ocupa_funcao;

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setJSSelect2();
$oForm->setJS("excfuncserv2.js?v1.0.1");
$oForm->setOnLoad("javascript: if($('#matricula')) { $('#matricula').focus() };");

$oForm->setSubTitulo("Vac&acirc;ncia de Ocupante de Fun&ccedil;&atilde;o");


// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// verifica se ocupa função
if ($ocupa_funcao == "S")
{
    ?>
    <form id="form1" name='form1' method='post' onsubmit="javascript:return false;">
        <input type="hidden" name="sitcad" value='<?= tratarHTML($sitcad); ?>'>

        <table class="table table-condensed table-bordered text-center">
            <tr>
                <td height="47" colspan="3" nowrap class='text-left' style='border-width:0px;'>
                    <label class='control-label'>Nome:</label>
                    <input type="text" id="nome" name="nome" value='<?= tratarHTML($nome); ?>' size="60" maxlength="60" class='form-control text-uppercase' readonly>
                </td>
                <td nowrap class='text-left' style='border-width:0px;'>
                    <label class='control-label'>Mat.Siape:</label>
                    <input type="text" id="matricula" name="matricula" value='<?= tratarHTML(removeOrgaoMatricula( $matricula )); ?>' size="7" maxlength=   "7" onkeyup="javascript:ve(this.value);" class='form-control' readonly>
                </td>
                <td rowspan="2" nowrap style="text-align:center;vertical-align:middle;padding:0px 5px 5px 5px;border-width:0px;">
                    <p class='p2'><img src="<?= tratarHTML(retornaFoto($matricula)); ?>" style="width:82px;height:110px;padding:5px 0px 0px 0px;"></p>
                    <br>
                </td>
            </tr>
            <tr>
                <td colspan="4" nowrap class='text-left' style='border-width:0px;'>
                    <label class='control-label'>Funções Ocupadas:</label>
                    <select size="1" id="funcao" name="funcao" class='form-control select2-single'>
                        <?= listaFuncoesOcupadas( tratarHTML($matricula), tratarHTML($funcao)); ?>
                    </select>
                </td>
            </tr>
        </table>

        <table class='table table-condensed text-center'>
            <tr>
                <td nowrap class='text-center' style='border-width:0px;'>
                    <label class='control-label'>Se deseja excluir por erro selecione SIM na caixa abaixo.</label>
                </td>
            </tr>
            <tr>
                <td class='text-left' style='border-width:0px;'>
                    <div class="col-md-2 col-md-offset-5">
                        <select size="1" id="exclusao" name="exclusao" class='form-control select2-single'>
                            <option value="N" <?= ($exclusao == 'N' ? ' selected' : ''); ?>>NÃO</option>
                            <option value="S" <?= ($exclusao == 'S' ? ' selected' : ''); ?>>SIM</option>
                        </select>
                    </div>
                </td>
                <td class='text-left' style='border-width:0px;'>&nbsp;&nbsp;&nbsp;</td>
            </tr>
        </table>

        <div class="form-group">
            <div class="col-md-12">
                <div class="col-md-2 col-md-offset-4">
                    <button type="submit" id="btn-continuar" class="btn btn-success btn-block">
                        <span class="glyphicon glyphicon-ok"></span> Continuar
                    </button>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-danger btn-block" href="javascript:window.location.replace('<?= $_SESSION['sHOrigem_1']; ?>');">
                        <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                    </a>
                </div>
            </div>
        </div>

        <div>
            <div style='text-align:right;width:100%;margin:0px;font-size:9px;border:0px;'>
                <fieldset style='border:1px solid white;text-align:left;'>
                    <legend style='font-size:10px;padding:0px;margin:0px;'><b>&nbsp;Informações&nbsp;</b></legend>
                    Nos campos que possuem uma lista para selecionar item, digite parte da descrição do item e em seguida selecione-o.
                </fieldset>
            </div>
        </div>

    </form>
    <?php
}
else
{
    mensagem( $nome . ' não é ocupante de função!', "excfuncserv.php");
}


$oDBase->close();

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();



## ################################################ ##
##                                                  ##
##              FUNÇÕES COMPLEMENTARES              ##
##                                                  ##
## ################################################ ##

function verificaCadastro($matricula=null)
{
    $matricula = getNovaMatriculaBySiape($matricula);

    // instancia banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setDestino( $_SESSION['sHOrigem_1'] );
    $oDBase->setMensagem('Problemas no acesso ao Cadastro!');

    $oDBase->query("
    SELECT
        servativ.nome_serv,
        servativ.cod_lot,
        DATE_FORMAT(servativ.dt_ing_lot,'%d/%m/%Y') AS dt_ing_lot,
        tabsetor.upag,
        servativ.cod_sitcad,
        IF(NOT ISNULL(ocupantes.mat_siape)
            AND DATE_FORMAT(ocupantes.dt_fim,'%Y-%m-%d') = '0000-00-00','S','N') AS ocupa_funcao
    FROM
        servativ
    LEFT JOIN
        tabsetor ON servativ.cod_lot = tabsetor.codigo
    LEFT JOIN
        ocupantes ON servativ.mat_siape = ocupantes.mat_siape
    WHERE
        servativ.mat_siape = :siape
        AND servativ.excluido = 'N'
        AND tabsetor.upag = :upag
        AND servativ.cod_sitcad NOT IN ('02','08','15','66')
    ",
    array(
        array(":siape", $matricula,        PDO::PARAM_STR),
        array(":upag",  $_SESSION['upag'], PDO::PARAM_STR),
    ));

    if ($oDBase->num_rows() == 0)
    {
        mensagem( "Servidor não encontrado!", 'excfuncserv.php' );
    }

    return $oDBase;
}


function listaFuncoesOcupadas($siape="",$funcao=NULL)
{
    global $sit2;

    $siape = getNovaMatriculaBySiape($siape);

    // tipo de situacao de ocupacao
    $sit2 = array(
        'T' => "TITULAR",
        'S' => "SUBSTITUTO",
        'R' => "INTERINO",
        /*'E' => "EVENTUAL",*/
    );

    // instancia banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setDestino( $_SESSION['sHOrigem_1'] );
    $oDBase->setMensagem('Problemas no acesso ao Cadastro!');

    $oDBase->query( "
    SELECT
        ocupantes.num_funcao,
        ocupantes.sit_ocup,
        tabfunc.cod_funcao,
        tabfunc.desc_func,
        tabfunc.cod_lot
    FROM
        ocupantes
    LEFT JOIN
        tabfunc ON ocupantes.num_funcao = tabfunc.num_funcao
    WHERE
        tabfunc.upag = :upag
        AND ocupantes.mat_siape = :siape
        AND DATE_FORMAT(ocupantes.dt_fim,'%Y-%m-%d') = '0000-00-00'
     ORDER BY
        ocupantes.num_funcao
    ",
    array(
        array( ':siape', $siape,            PDO::PARAM_STR ),
        array( ':upag',  $_SESSION['upag'], PDO::PARAM_STR ),
    ));

    $opcoes = "<option value='00000'>SELECIONE UMA OPÇÃO</option>";

    while ($linha = $oDBase->fetch_object())
    {
        $opcoes .= "<option value='" . $linha->num_funcao . "'"
        . ($linha->num_funcao == $funcao ? " selected" : "") . ">"
        . $linha->num_funcao . " - "
        . $linha->cod_lot . " - "
        . $linha->desc_func
        . " (" . uc_words($sit2[$linha->sit_ocup]) . ")"
        . "</option>";
    }

    $oDBase->close();

    return $opcoes;
}
