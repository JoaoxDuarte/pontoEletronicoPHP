<?php

include_once("config.php");
verifica_permissao("sRH");
$teste = false;

if (!empty($_POST)) {
    $alterar['DESC_CARGO']    = $_POST['nome'];
    $alterar['COD_CARGO']     = $_POST['codigo'];
    $alterar['PERMITE_BANCO'] = $_POST['permite'];
    $alterar['SUBSIDIOS']     = $_POST['subsidios'];
    $id = $_POST['id'];

    updateCargoFuncao($alterar , $id);
    $mensagemUsuario = "Cargo alterado com sucesso.";
    $teste = true;

    // mensagem
    replaceLink( "cargos_funcoes.php");
}

$sLotacao = $_SESSION["sLotacao"];
$mensagemUsuario = $_SESSION["mensagem-usuario"];

// dados voltar
$_SESSION['voltar_nivel_2'] = "cargos_funcoes.php";
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';
$_SESSION['voltar_nivel_5'] = '';

$oForm = new formPadrao();
$oForm->setSubTitulo("Editar Cargo");

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
$cargo = getCargo($_GET['codcargo']);

?>
<script>
$(document).ready(function () {
    $(document).on('click', '#btn-salvar-cargo', function () {
        if (!validateForm()) {
            return false;
        }

        jQuery('#form1').submit();

        return false;
    });

});

function validateForm() {

    var nome = $("[name='nome']").val();
    var codigo = $("[name='codigo']").val();

    if (nome === "") {
        alert("Código do cargo é uma informação obrigatória!");
        return false;
    }

    if (codigo === "") {
        alert("Nome do cargo é uma informação obrigatória!");
        return false;
    }

    return true;
}
</script>

<div class="portlet-body form">

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <form id="form1" name="form1" method="POST" action="cargos_funcoes_alterar.php">
        <input type="hidden" value="<?= tratarHTML($_GET['codcargo']); ?>" name="id">
        <input type="hidden" value="" id="mensagens" name="mensagens">
        <div class="row">
            <div class="col-lg-6 col-md-3 col-xs-6 col-sm-3 margin-10">
                <font class="ft_13_003">Código:</font>
                <input type="text" name="codigo" maxlength="6" class="form-control" readonly value="<?= tratarHTML($cargo['COD_CARGO']); ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-md-3 col-xs-6 col-sm-3 margin-10">
                <font class="ft_13_003">Nome:</font>
                <input type="text" name="nome" class="form-control" value="<?= tratarHTML($cargo['DESC_CARGO']); ?>" maxlength="42">
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 col-md-3 col-xs-6 col-sm-3 margin-10">
                <font class="ft_13_003">Permite banco de horas?</font>
                <select class="form-control" name="permite">

                    <?php if($cargo['PERMITE_BANCO'] == "SIM"): ?>
                        <option value="SIM" selected>SIM</option>
                        <option value="NAO">NÃO</option>
                    <?php else: ?>
                        <option value="SIM">SIM</option>
                        <option value="NAO" selected>NÃO</option>
                    <?php endif; ?>

                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 col-md-3 col-xs-6 col-sm-3 margin-10">
                <font class="ft_13_003">Subsídios?</font>
                <select class="form-control" name="subsidios">

                    <?php if($cargo['SUBSIDIOS'] == "SIM"): ?>
                        <option value="SIM" selected>SIM</option>
                        <option value="NAO">NÃO</option>
                    <?php else: ?>
                        <option value="SIM">SIM</option>
                        <option value="NAO" selected>NÃO</option>
                    <?php endif; ?>

                </select>
            </div>
        </div>

        <div class="row margin-25">
            <div class="form-group col-md-12 text-center">
                <div class="col-md-2 col-xs-4 col-md-offset-2">

                    <a class="btn btn-success btn-block" id="btn-salvar-cargo" role="button">
                        <span class="glyphicon glyphicon-ok"></span> Salvar
                    </a>
                </div>
                <div class="col-md-2 col-xs-4">
                    <a class="btn btn-danger btn-block" id="btn-voltar"
                       href="javascript:window.location.replace('/sisref/cargos_funcoes.php')" role="button">
                        <span class="glyphicon glyphicon-arrow-left"></span> Voltar
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
