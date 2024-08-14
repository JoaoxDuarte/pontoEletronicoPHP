<?php
include_once("config.php");

verifica_permissao("hora_extra_limites");

$teste = false;
if (isset($_POST['valor'])) {
    $alterar['valor'] = $_POST['valor'];
    $alterar['id']    = $_POST['id'];

    updateConfiguracoesHoraExtra($alterar);
    registraLog("Alterou o campo de id " . $alterar['id']);
    $mensagemUsuario = "Configurações alteradas com sucesso.";
    $teste = true;
}


$sLotacao = $_SESSION["sLotacao"];
$mensagemUsuario = $_SESSION["mensagem-usuario"];

// dados voltar
$_SESSION['voltar_nivel_2'] = "frequencia_acompanhar_registros_horario_servico.php?dados=" . $dadosorigem;
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';
$_SESSION['voltar_nivel_5'] = '';

$dados_configuracao = getParamentroHoraExtra($_POST['id']);



$oForm = new formPadrao();
$oForm->setJSDatePicker();
$oForm->setJSCKEditor();
$oForm->setJS( "js/jquery.mask.min.js" );
$oForm->setSubTitulo("Editar Parâmetro");


// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<script>
$(document).ready(function ()
{
    <?php if($teste): ?>
        mostraMensagem("Alterado com sucesso!", "success", "configuracoes_limites_hora_extra.php");
    <?php endif; ?>

    $('#valor').mask('00:00');

    $(document).on('click', '#btn-salvar-parametro', function () {
        if (!validateForm()) {
            return false;
        }

        jQuery('#form1').submit();

        return false;
    });
});

function validateForm() {

    var valor = $("[name='valor']").val();

    if (valor === "") {
        mostraMensagem("Valor é obrigatório!", "warning");
        return false;
    }

    return true;
}
</script>

<div class="portlet-body form">

    <form id="form1" name="form1" method="POST" action="configuracoes_limites_hora_extra_alterar.php">
        <input type="hidden" value="<?= tratarHTML($_POST['id']); ?>" name="id">
        <input type="hidden" value="" id="mensagens" name="mensagens">
        <div class="row">
            <div class="col-lg-6 col-md-3 col-xs-6 col-sm-3">
                <font class="ft_13_003">Campo:</font>
                <input type="text" id="campo" name="campo" class="form-control" value="<?= tratarHTML($dados_configuracao['mensagem']); ?>" readonly>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-md-3 col-xs-6 col-sm-3">
                <font class="ft_13_003">Valor:</font>
                <input type="text" id="valor" name="valor" class="form-control minutos" value="<?= tratarHTML($dados_configuracao['valor']); ?>" maxlength="150">
            </div>
        </div>

        <div class="row">
            <br>
            <div class="form-group col-md-12 text-center">
                <div class="col-md-2 col-xs-4 col-md-offset-2">

                    <a class="btn btn-success btn-block" id="btn-salvar-parametro" role="button">
                        <span class="glyphicon glyphicon-ok"></span> Salvar
                    </a>
                </div>
                <div class="col-md-2 col-xs-4">
                    <a class="btn btn-danger btn-block" id="btn-voltar"
                       href="javascript:window.location.replace('configuracoes_limites_hora_extra.php')" role="button">
                        <span class="glyphicon glyphicon-arrow-left"></span> Cancelar
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
<?php

//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
