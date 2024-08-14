<?php
include_once("config.php");

verifica_permissao("sRH");

$teste = false;

if(!empty($_POST)){

    $alterar['ip'] = $_POST['ip_do_host'];
    $alterar['obs'] = $_POST['observacao'];
    $alterar['aut'] = $_POST['autorizado'];


    insert_configuracoes_host($alterar);
    registraLog("Cadastro de Host ".$alterar['ip']);

    $_SESSION["mensagem-usuario"] = "Configurações alteradas com sucesso.";

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
$oForm->setSubTitulo("Cadastrar Configuração");

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


?>
    <script>
        $(document).ready(function () {

            <?php if ($teste): ?>
                alert("Cadastro realizado com sucesso!");
                  window.location.href="host_autorizado_lista.php";
            <?php endif; ?>


            $(document).on('click', '#btn-salvar-ciclo', function () {
                if(!validateForm()){
                    return false;
                }


                jQuery('#form1').submit();

                //
                // jQuery.ajax({
                //     type: "POST",
                //     url: "host_inclusao.php",
                //     data: dados,
                //     success: function( data )
                //     {
                //         alert(data);
                //     alert("Cadastro realizado com sucesso!");
                //      window.location.href="host_autorizado_lista.php";
                //
                //     }
                // });

                return false;
            });

        });

        function validateForm() {

            var ip_do_host = $("[name='ip_do_host']").val();
            var autorizado = $("[name='autorizado']").val();
            var observacao = $("[name='observacao']").val();



            // Verifica se a data inicial foi informada
            if (ip_do_host === "") {
                alert("O IP é obrigatório!");
                return false;
            }
            if(validateIP(ip_do_host)){
                return true;
            } else {
                alert('Endereço IP inválido!');
                return false;
            }
            if (autorizado === "") {
                alert("Autorização é obrigatório!");
                return false;
            }
            if (observacao === "") {
                alert("Observação é obrigatório!");
                return false;
            }


            return true;
            // Vefifica se o range de datas selecionado é válido.
            // validateRangeDates();

        }
        function validateIP(ip) {
            //Check Format
            var ip = ip.split(".");

            if (ip.length != 4) {
                return false;
            }

            //Check Numbers
            for (var c = 0; c < 4; c++) {
                //Perform Test
                if ( ip[c] <= -1 || ip[c] > 255 ||
                    isNaN(parseFloat(ip[c])) ||
                    !isFinite(ip[c])  ||
                    ip[c].indexOf(" ") !== -1 ) {

                    return false;
                }
            }
            return true;
        }


    </script>
    <div class="portlet-body form">

        <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

        <form id="form1" name="form1" method="POST" action="host_inclusao.php">

            <div class="row">
                <div class="col-lg-6 col-md-3 col-xs-6 col-sm-3">
                    <font class="ft_13_003">IP:</font>
                    &nbsp;<input type="text" id="ip_do_host" name="ip_do_host" class="form-control"
                                 value="">
                </div>
            </div>

            <div class="row">

                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6">
                    <font class="ft_13_003">Observação:</font>
                    <textarea name="observacao" style="resize:none" class="form-control col-lg-6 col-md-6 col-xs-6 col-sm-6 " id="observacao" rows="6"></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6">
                    <font class="ft_13_003">Ativo:</font>
                    <select name="autorizado" class="form-control form-control-lg">
                        <option value="S">Sim</option>
                        <option value="S">Não</option>
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
                           href="javascript:window.location.replace('/sisref/host_autorizado_lista.php')" role="button">
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
