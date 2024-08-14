<?php
include_once("config.php");
//verifica_permissao("sRH ou Chefia");
verifica_permissao("sRH");
if(!empty($_POST)){


    $alterar['emails'] = $_POST['emails'];
    $alterar['ativo'] = $_POST['ativo'];
    $alterar['campo'] = $_POST['campo'];

    update_configuracoes_suporte($alterar);
    registraLog("Alterou o registro ".$alterar['campo']);

    $mensagemUsuario = "Configurações alteradas com sucesso.";
}


$sLotacao = $_SESSION["sLotacao"];
$mensagemUsuario = $_SESSION["mensagem-usuario"];

// dados voltar
$_SESSION['voltar_nivel_2'] = "frequencia_acompanhar_registros_horario_servico.php?dados=" . $dadosorigem;
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';
$_SESSION['voltar_nivel_5'] = '';

$oForm = new formPadrao();
$oForm->setSubTitulo("Editar Configuração");

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


// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
$dados_configuracao = getConfiguracaoSuporteAlterar($_GET['id']);
if($dados_configuracao['ativo']){
    $ativo = '';
    if($dados_configuracao['ativo']=='N') {
        $ativo .= '<option value="' . tratarHTML($dados_configuracao['ativo']) . '">Não</option><option value="S">Sim</option>';
    }else{
        $ativo .= '<option value="' . tratarHTML($dados_configuracao['ativo']) . '">Sim</option><option value="N">Não</option>';
    }


}


?>
    <script>
        $(document).ready(function () {



            $(document).on('click', '#btn-salvar-ciclo', function () {
                if(!validateForm()){
                    return false;
                }


                var dados = jQuery('#form1').serialize();


                jQuery.ajax({
                    type: "POST",
                    url: "suporte_alterar.php",
                    data: dados,
                    success: function( data )
                    {

                         alert('Alteração realizada com sucesso!');
                         window.location.href="configuracao_suporte_lista.php";

                    }
                });

                return false;
            });

        });

        function validateForm() {

            var emails = $("[name='emails']").val();
            var ativo = $("[name='ativo']").val();

            // Verifica se a data inicial foi informada
            if (emails === "") {
                alert("E-mail é obrigatórios!");
                return false;
            }

            if (ativo === "") {
                alert("Ativo é obrigatório!");
                return false;
            }


            return true;


        }




    </script>
    <div class="portlet-body form">

        <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

            <form id="form1" name="form1" method="POST" action="suporte_alterar.php">

            <input type="hidden" value="" id="mensagens" name="mensagens">
            <div class="row">
                <div class="col-lg-6 col-md-3 col-xs-6 col-sm-3">
                    <font class="ft_13_003">Campo:</font>
                    &nbsp;<input type="text" id="campo" name="campo" class="form-control"
                                 value="<?= tratarHTML($dados_configuracao['campo']); ?>"  readonly>
                </div>
            </div>
                <div class="row">
                    <div class="col-lg-6 col-md-3 col-xs-6 col-sm-3">
                        <font class="ft_13_003">E-mail:</font>
                        &nbsp;<input type="text" id="emails" name="emails" class="form-control"
                                     value="<?= tratarHTML($dados_configuracao['emails']); ?>" >
                    </div>
                </div>




            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6">
                    <font class="ft_13_003">Ativo:</font>
                    <select name="ativo" class="form-control form-control-lg">
                        <?= $ativo; ?>
                    </select>
                    </div>
            </div>

            <div class="row">
                <br>
                <div class="form-group col-md-12 text-center">
                    <div class="col-md-2"></div>
                    <div class="col-md-2 col-xs-4 col-md-offset-2">

                        <a class="btn btn-success btn-block" id="btn-salvar-ciclo" role="button">
                            <span class="glyphicon glyphicon-ok"></span> Salvar
                        </a>
                    </div>
                    <div class="col-md-2 col-xs-4">
                        <a class="btn btn-danger btn-block" id="btn-voltar"
                           href="javascript:window.location.replace('/sisref/configuracao_suporte_lista.php')" role="button">
                            <span class="glyphicon glyphicon-arrow-left"></span> Cancelar
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
