<?php
include_once("config.php");
include_once( "src/controllers/TabEscalasController.php" );

verifica_permissao("escalas");

$oDados = new TabEscalasController();

$retorno = "";

if(!empty($_POST))
{
    $alterar['jornada']   = $_POST['jornada'];
    $alterar['trabalhar'] = $_POST['trabalhar'];
    $alterar['folgar']    = $_POST['folgar'];
    $alterar['descricao'] = $_POST['descricao'];
    $alterar['ativo']     = $_POST['ativo'];

    $retorno = $oDados->insert($alterar);
}

$sLotacao        = $_SESSION["sLotacao"];
$mensagemUsuario = $_SESSION["mensagem-usuario"];


$oForm = new formPadrao();
$oForm->setJS("js/jquery.mask.min.js");
$oForm->setSubTitulo( "Cadastrar Escalas" );


// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
<script>
    $(document).ready(function () {
        <?php if ($retorno == "ja_existe"): ?>
            mostraMensagem('Escala já consta do Cadastro!', 'danger', "tabescalas.php");
        <?php endif; ?>

        <?php if ($retorno == "gravou"): ?>
            mostraMensagem('Inclusão realizada com sucesso!', 'success', "tabescalas.php");
        <?php endif; ?>

        <?php if ($retorno == "nao_gravou"): ?>
            mostraMensagem('Inclusão NÃO foi realizada!', 'danger', "tabescalas.php");
        <?php endif; ?>

        $('#btn-salvar').on('click', function () {
            if(!validateForm()){
                return false;
            }

            jQuery('#form1').attr('onsubmit', 'javascript:return true;');
            jQuery('#form1').submit();

            return false;
        });
    });

    function validateForm() {

        var trabalhar = $("[name='trabalhar']").val();
        var folgar    = $("[name='folgar']").val();
        var descricao = $("[name='descricao']").val();

        // Verifica se a data inicial foi informada
        if (parseInt(trabalhar, 10) == 0) {
            mostraMensagem('Horas de trabalho é obrigatória!', 'warning');
            return false;
        }

        if(parseInt(folgar, 10) == 0){
            mostraMensagem('Horas de folga é obrigatória!', 'warning');
            return false;
        }

        if(descricao == ""){
            mostraMensagem('Nome da escala é obrigatória!', 'warning');
            return false;
        }

        return true;
    }
    
    $('.horas').mask('00:00');
</script>

<div class="portlet-body form">

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <form id="form1" name="form1" method="POST" action="tabescalas_incluir.php" onsubmit="return false;">

        <div class="row col-md-offset-3">
            <div class="col-md-12 margin-10">
                <font class="ft_13_003">Jornadas permitidas:</font>
                <div class="col-md-12">
                    <div class="col-md-2" style="width:75px;">
                        <input type="radio" class="custom-control-input" id="jornada" name="jornada" value="40" checked> 40
                    </div>
                    <div class="col-md-2" style="width:75px;">
                        <input type="radio" class="custom-control-input" id="jornada" name="jornada" value="30"> 30
                    </div>
                    <div class="col-md-2" style="width:75px;">
                        <input type="radio" class="custom-control-input" id="jornada" name="jornada" value="24"> 24
                    </div>
                    <div class="col-md-2" style="width:75px;">
                        <input type="radio" class="custom-control-input" id="jornada" name="jornada" value="20"> 20
                    </div>
                </div>
            </div>
        </div>

        <div class="row col-md-offset-3">
            <div class="col-lg-4 col-md-4 col-xs-4 col-sm-4 margin-10" style="margin-right:-88px;">
                <font class="ft_13_003">Trabalhar:</font>
                &nbsp;<input type="text" id="trabalhar" name="trabalhar" class="form-control horas" size="2" maxlength="2" value="" style="width:150px;">
            </div>
            <div class="col-lg-1 col-md-1 col-xs-1 col-sm-1 margin-10" style="padding-top:22px;padding-bottom:3px;margin-right:-34px;font-size:20px;font-weight:bold;">
                X
            </div>
            <div class="col-lg-4 col-md-4 col-xs-4 col-sm-4 margin-10">
                <font class="ft_13_003">Folgar:</font>
                &nbsp;<input type="text" id="folgar" name="folgar" class="form-control horas" size="2" maxlength="2" value="" style="width:150px;" pattern="[0-9]+$">
            </div>
        </div>

        <div class="row col-md-offset-3">
            <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6 margin-10">
                <font class="ft_13_003">Nome:</font>
                &nbsp;<input type="text" id="descricao" name="descricao" class="form-control" size="300" maxlength="300" value="" style="width:500px;">
            </div>
        </div>

        <div class="row col-md-offset-3">
            <div class="col-lg-4 col-md-4 col-xs-4 col-sm-4 margin-10">
                <font class="ft_13_003">Ativo:</font>
                <select id="ativo" name="ativo" class="form-control select2-single">
                    <option value='N'>N&atilde;o</option>
                    <option value='S' selected>Sim</option>
                </select>
            </div>
        </div>

        <div class="row">
            <br>
            <div class="form-group col-md-12 text-center">
                <div class="col-md-2"></div>
                <div class="col-md-2 col-xs-4 col-md-offset-2">
                    <a class="btn btn-success btn-block" id="btn-salvar" role="button">
                        <span class="glyphicon glyphicon-ok"></span> Salvar
                    </a>
                </div>
                <div class="col-md-2 col-xs-4">
                    <a class="btn btn-danger btn-block" id="btn-voltar"
                       href="javascript:window.location.replace('tabescalas.php')" role="button">
                        <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                    </a>
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>
    </form>
</div>
<?php

//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
