<?php
include_once("config.php");
verifica_permissao("administracao_central");
$teste = false;
if (!empty($_POST)) {
    $alterar['valor'] = $_POST['valor'];
    $alterar['id'] = $_POST['id'];

    updateConfiguracoesSigac($alterar);
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

$oForm = new formPadrao();
$oForm->setSubTitulo("Editar Parâmetro");

$css = array();
$css[] = 'js/bootstrap-datepicker/css/bootstrap-datepicker3.standalone.min.css';
$css[] = 'js/ckeditor/skins/moono/editor.css';

$javascript = array();
$javascript[] = 'js/bootstrap-datepicker/js/bootstrap-datepicker.min.js';
$javascript[] = 'js/bootstrap-datepicker/locales/bootstrap-datepicker.pt-BR.min.js';
$javascript[] = 'js/ckeditor/ckeditor.js';
$javascript[] = 'js/ckeditor/config.js';
$javascript[] = 'js/ckeditor/lang/pt-br.js';
$javascript[] = 'js/ckeditor/styles.js';
$javascript[] = 'js/jquery.mask.min.js';


// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
$dados_configuracao = getParamentroSigac($_GET['id']);

?>
<script>
$(document).ready(function () {
    <?php  if($teste): ?>
        alert("Alterado com sucesso!");
        window.location.href = "configuracoes_sigac.php";
    <?php endif; ?>

    $(document).on('click', '#btn-salvar-parametro', function () {
        if (!validateForm()) {
            return false;
        }

        jQuery('#form1').submit();

        return false;
    });
});

function validateForm()
{
    var valor = $("[name='valor']").val();

    if (valor === "")
    {
        alert("Valor é obrigatório!");
        return false;
    }

    return true;
}
</script>

<div class="portlet-body form">

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <form id="form1" name="form1" method="POST" action="configuracoes_sigac_alterar.php">
        <input type="hidden" value="<?= tratarHTML($_GET['id']); ?>" name="id">
        <input type="hidden" value="" id="mensagens" name="mensagens">
        <div class="row">
            <div class="col-lg-6 col-md-3 col-xs-6 col-sm-3">
                <font class="ft_13_003">Campo:</font>
                <input type="text" id="campo" name="campo" class="form-control" value="<?= tratarHTML($dados_configuracao['campo']); ?>" readonly>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-md-3 col-xs-6 col-sm-3">
                <font class="ft_13_003">Valor:</font>
                <input type="text" id="valor" name="valor" class="form-control minutos" value="<?= tratarHTML($dados_configuracao['valor']); ?>" maxlength="500">
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
                       href="javascript:window.location.replace('/sisref/configuracoes_sigac.php')" role="button">
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
