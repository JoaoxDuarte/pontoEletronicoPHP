<?php
include_once("config.php");
verifica_permissao("sRH");
$teste = false;

if (!empty($_POST)) {
    $alterar['DESC_CARGO'] = $_POST['nome'];
    $alterar['COD_CARGO'] = $_POST['codigo'];
    $alterar['PERMITE_BANCO'] = $_POST['permite'];
    $alterar['SUBSIDIOS'] = $_POST['subsidios'];

    cadastrarCargoFuncao($alterar);
    $mensagemUsuario = "Cargo cadastrado com sucesso.";
    $teste = true;

    // mensagem
    replaceLink( "cargos_funcoes.php");
}


if(!empty($_GET['validar-codigo'])){

    $oDBase = new DataBase('PDO');
    $oDBase->query("SELECT * FROM tabcargo WHERE COD_CARGO = :codigo ",
        array(
            array(":codigo", $_GET['codigo'], PDO::PARAM_STR)
        )
    );

    $bool = $oDBase->fetch_assoc();

    if(!$bool) {
        echo json_encode(['success' => tratarHTML($bool)]);
        die;
    }

    echo json_encode(['success' => true]);
    die;
}



$sLotacao = $_SESSION["sLotacao"];
$mensagemUsuario = $_SESSION["mensagem-usuario"];

// dados voltar
$_SESSION['voltar_nivel_2'] = "cargos_funcoes.php";
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';
$_SESSION['voltar_nivel_5'] = '';

$oForm = new formPadrao();
$oForm->setSubTitulo("Cadastrar Cargo");

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

    var validacode;

    $(document).ready(function () {
        <?php if($teste): ?>
            alert("Cadastro realizado com sucesso!");
            window.location.href = "cargos_funcoes.php";
        <?php endif; ?>

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

        validarCode(codigo);

        setTimeout(function () {
            if (validacode) {
                alert("Já existe um cargo cadastrado com esse mesmo código!");
                return false;
            }else{
                $("#form1").submit();
            }
        }, 2000);

        return false;
    }

    function validarCode(codigo) {
        $.get(
            "cargos_funcoes_cadastrar.php",
            "validar-codigo=true&" +
            "codigo=" + codigo,
            function (data) {
                parsed = JSON.parse(data);

                if (parsed.success) {
                    validacode = parsed.success;
                }else{
                    validacode = parsed.success;
                }
            });
    }
</script>

<div class="portlet-body form">

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <form id="form1" name="form1" method="POST" action="cargos_funcoes_cadastrar.php">
        <input type="hidden" value="" id="mensagens" name="mensagens">
        <div class="row">
            <div class="col-lg-6 col-md-3 col-xs-6 col-sm-3 margin-10">
                <font class="ft_13_003">Código:</font>
                <input type="text" name="codigo" maxlength="6" class="form-control">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-md-3 col-xs-6 col-sm-3 margin-10">
                <font class="ft_13_003">Nome:</font>
                <input type="text" name="nome" class="form-control" maxlength="42">
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 col-md-3 col-xs-6 col-sm-3 margin-10">
                <font class="ft_13_003">Permite banco de horas?</font>
                <select class="form-control" name="permite">
                    <option value="SIM" selected>SIM</option>
                    <option value="NAO">NÃO</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 col-md-3 col-xs-6 col-sm-3 margin-10">
                <font class="ft_13_003">Subsídios?</font>
                <select class="form-control" name="subsidios">
                    <option value="SIM">SIM</option>
                    <option value="NAO" selected>NÃO</option>
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
