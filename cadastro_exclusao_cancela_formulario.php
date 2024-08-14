<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH ou sTabServidor');

// valores enviados via formulario
$matricula = anti_injection($_REQUEST['siape']);

$destino = 'cadastro_exclusao_cancela.php';

// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();
$sessao_navegacao->initSessaoNavegacao();
$sessao_navegacao->setPagina($_SERVER['REQUEST_URI']);


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCSS("css/select2.min.css");
$oForm->setCSS("css/select2-bootstrap.css");
$oForm->setCSS("js/bootstrap-datepicker/css/bootstrap-datepicker3.standalone.min.css");

$oForm->setJS( "js/select2.full.js");
$oForm->setJS( 'js/bootstrap-datepicker/js/bootstrap-datepicker.min.js');
$oForm->setJS( 'js/bootstrap-datepicker/locales/bootstrap-datepicker.pt-BR.min.js');

$oForm->setSubTitulo("Cancela Exclus&atilde;o de Servidores/Estagi&aacute;rios");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// testa se foi passada a matrícula siape
// senão verifica se há dados na seção sobre a matrícula do servidor
if (empty($matricula) && empty($_SESSION['sExc_Matricula_Siape']))
{
    mensagem("Obrigatório informar a matrícula do Servidor!", $destino, 1);
}

if (empty($matricula))
{
    $matricula = $_SESSION['sExc_Matricula_Siape'];
}
else
{
    // salvamos a matrícula do servidor
    // para que o teste de erro de upag
    // possa funcionar corretamente caso aconteça algum
    // problema na exclusão e retorne para este script
    $_SESSION['sExc_Matricula_Siape'] = $matricula;
}

// instancia banco de dados
$oDBase = new DataBase('PDO');


$matricula = getNovaMatriculaBySiape($matricula);

//obtendo dados da exclusão
$oDBase->query("
SELECT
    a.mat_siape, a.mat_dtp, a.nome_serv, a.cod_lot, a.cod_sitcad, c.upag,
    IFNULL(b.cod_ocorr,'') AS cod_ocorr, d.desc_ocorr, b.cartorio, b.tp_doc,
    b.num_doc, b.reg_obito, b.fol_obito, b.liv_obito, b.cod_orgao, e.descdipl,
    DATE_FORMAT(a.dt_nasc, '%d%m%Y')    AS dt_nasc,
    DATE_FORMAT(b.dt_ocorr, '%d/%m/%Y') AS dt_ocorr,
    DATE_FORMAT(b.dt_doc, '%d/%m/%Y')   AS dt_doc,
    DATE_FORMAT(b.dt_obito, '%d/%m/%Y') AS dt_obito,
    DATE_FORMAT(b.dt_exped, '%d/%m/%Y') AS dt_exped,
    IF(ISNULL(b.siape),'N','S')         AS base_excluido
FROM
    servativ AS a
LEFT JOIN
    exclus AS b ON a.mat_siape = b.siape
LEFT JOIN
    tabsetor AS c ON a.cod_lot = c.codigo
LEFT JOIN
    tabocorr AS d ON b.cod_ocorr = d.cod_ocorr
LEFT JOIN
    tabdipl AS e ON b.tp_doc = e.coddipl
WHERE
    a.mat_siape = :siape
",
array(
    array( ':siape', $matricula, PDO::PARAM_STR )
));

$nRows     = $oDBase->num_rows();
$oServidor = $oDBase->fetch_object(); // informações do servidor

if ($nRows == 0)
{
    mensagem("Matrícula não consta do Cadastro!", $destino);
}
elseif (empty($oServidor->cod_ocorr))
{
    mensagem("Matrícula informada não consta da base de excluídos!", $destino);
}
else
{
    $tSiape          = $oServidor->mat_siape;
    $wnome           = $oServidor->nome_serv;
    $dt_nasc         = $oServidor->dt_nasc;
    $wlota           = $oServidor->cod_lot;
    $ocorr           = $oServidor->cod_ocorr;
    $ocorr_descricao = $oServidor->desc_ocorr;
    $dtocorr         = $oServidor->dt_ocorr;
    $sitcad          = $oServidor->cod_sitcad;

    // unidade pagadora do servidor e do usuário
    $upag_do_servidor = $oServidor->upag;
    $upag_do_usuario  = $_SESSION['upag'];

    // verifica se a upag do servidor eh a mesma do usuario
    if ($upag_do_servidor != $upag_do_usuario)
    {
        mensagem("Servidor cadastrado em outra UPAG!", $destino);
    }
}

?>
<script>
$(document).ready(function ()
{
    // Set the "bootstrap" theme as the default theme for all Select2
    // widgets.
    //
    // @see https://github.com/select2/select2/issues/2927
    $.fn.select2.defaults.set("theme", "bootstrap");

    var placeholder = "Selecione uma Opção";

    $(".select2-single").select2({
        placeholder: placeholder,
        width: null,
        containerCssClass: ':all:'
    });
    $(".select2-single").prop("disabled", true);

    $('#dt-container .input-group.date').datepicker({
        format: "dd/mm/yyyy",
        language: "pt-BR",
        //daysOfWeekDisabled: "0,6",
        autoclose: true,
        todayHighlight: true,
        toggleActive: true,
        enableOnReadonly: false
    });

    $("#btn-continuar").click(function ()
    {
        validar();
    });
});

function validar()
{
    $("#form1").attr("action", "cadastro_exclusao_cancela_grava.php");
    $('#form1').submit();
}
</script>

<form id="form1" name='form' method='post'>
    <input type="hidden" name="dt_nasc" value='<?= tratarHTML($dt_nasc); ?>'>
    <input type="hidden" name="sitcad"  value='<?= tratarHTML($sitcad); ?>'>

    <table class="table table-condensed table-bordered text-center">
        <tr>
            <td height="47" colspan="3" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Nome:</label>
                <input type="text" id="wnome" name="wnome" value='<?= tratarHTML($wnome); ?>' size="60" maxlength="60" class='form-control text-uppercase' readonly>
            </td>
            <td nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Mat.Siape:</label>
                <input type="text" id="tSiape" name="tSiape" value='<?= tratarHTML(removeOrgaoMatricula( $tSiape )); ?>' size="7" maxlength="7" onkeyup="javascript:ve(this.value);" class='form-control' readonly>
            </td>
            <td rowspan="2" nowrap style="text-align:center;vertical-align:middle;padding:0px 5px 5px 5px;border-width:0px;">
                <p class='p2'><img src="<?= tratarHTML(retornaFoto($tSiape)); ?>" width="82" height="110"></p>
                <br>
            </td>
        </tr>
        <tr>
            <td colspan="3" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Motivo da exclus&atilde;o:</label>
                <select size="1" id="codocor" name="codocor" class='form-control select2-single' readonly>
                    <?= listaCodOcorrenciaDeExclusao(tratarHTML($ocorr)); ?>
                </select>
            </td>
            <td nowrap class='text-left' style='border-width:0px;'>
                <div class="col-md-2" id='dt-container' style='padding:0px;border-width:0px;'>
                    <label class='control-label'>Data:</label>
                    <div class="input-group date">
                        <input type="text" id="Dataocor" name="Dataocor" value='<?= tratarHTML($dtocorr); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnBlur="javascript:ve(this.value);" OnKeyPress="formatar(this, '##/##/####')" class='form-control' readonly><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
            </td>

            </td>
        </tr>
    </table>

    <div class="form-group">
        <div class="col-md-12">
            <div class="col-md-2 col-md-offset-4">
                <button type="submit" id="btn-continuar" class="btn btn-success btn-block">
                    <span class="glyphicon glyphicon-ok"></span> Gravar
                </button>
            </div>
            <div class="col-md-2">
                <a class="btn btn-danger btn-block" href="javascript:window.location.replace('cadastro_exclusao_cancela.php');">
                    <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                </a>
            </div>
        </div>
    </div>

</form>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();


/*
 * Lista a tabela de códigos de situação
 * cadastral de servidores/estagiários
 */

function listaCodOcorrenciaDeExclusao($codoc = '')
{
    global $sitcad;

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    // situacao cadastral for 66 (estagiario)
    // os codigos de ocorrencias serão limitados
    if ($sitcad == "66")
    {
        $query1 = "SELECT cod_ocorr AS codigo, desc_ocorr AS descricao FROM tabocorr WHERE cod_ocorr IN ('02014','02030','07027','02081') ORDER BY desc_ocorr ";
    }
    else
    {
        $query1 = "SELECT cod_ocorr AS codigo, desc_ocorr AS descricao FROM tabocorr ORDER BY desc_ocorr ";
    }
    $oDBase->query($query1);

    $lista       = '';
    $lista       .= "<option value='00'>Selecione uma opção</option>";
    while ($oOcorrencia = $oDBase->fetch_object())
    {
        $lista .= "<option value='" . tratarHTML($oOcorrencia->codigo) . "'";
        if ($oOcorrencia->codigo == $codoc)
        {
            $lista .= " selected";
        }
        $lista .= ">" . tratarHTML($oOcorrencia->codigo) . " - " . tratarHTML($oOcorrencia->descricao) . "</option>";
    }
    // Fim da tabela
    return $lista;

}
