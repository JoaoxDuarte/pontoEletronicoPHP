<?php
// funcoes diversas
include_once("config.php");

// permissao de acesso
verifica_permissao("sAPS");


## classe para montagem do formulario padrao
#
$title = _SISTEMA_SIGLA_ . ' | Reiniciar Senhas';

$css   = array();
$css[] = _DIR_CSS_ . 'plugins/dlg.min.css';

$javascript   = array();
$javascript[] = _DIR_JS_ . 'phpjs.js';
$javascript[] = _DIR_JS_ . "jquery.blockUI.js?v2.38";
$javascript[] = _DIR_JS_ . "jquery.bgiframe.js";
$javascript[] = _DIR_JS_ . "plugins/jquery.dlg.min.js";
$javascript[] = _DIR_JS_ . "plugins/jquery.easing.js";
$javascript[] = _DIR_JS_ . "jquery.ui.min.js";

include("html/html-base.php");
include("html/header.php");
?>
<div class="container">
    <div class="row align-vertical" id="login">

        <div class="col-md-12 subtitle">
            <h6 class="lettering-tittle uppercase"><strong>Reiniciar Senhas</strong></h6>
        </div>
        <div class="col-md-12 margin-bottom-25"></div>

        <form id="form1" name="form1" method="POST">

            <div class="col-md-4 col-md-offset-4">
                <table class="table table-striped table-condensed table-bordered text-center">
                    <tbody>
                        <tr height='25'>
                            <td class='text-center'>
                                Matrícula<br>
                                <input tipo="siape" id="siape" name="siape" class="form-control text-center" size="7" maxlength="7" value="" onkeyup="javascript:ve(this.value);" type="text">
                            </td>
                            <td class='text-center'>
                                CPF<br>
                                <input tipo="cpf" id="cpf" name="cpf" class="form-control text-center" size="11" maxlength="11" value="" onkeyup="javascript:ve(this.value);" type="text">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="form-group col-md-8 text-center">
                    <div class="col-md-7 col-md-offset-3 margin-10">
                        <div class="col-md-6 text-right">
                            <a class="btn btn-success btn-primary" id="btn-reiniciar-senha">
                                <span class="glyphicon glyphicon-ok"></span> Continuar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div style="text-align:right;width:95%;margin:25px;font-size:9px;border:0px;">
                    <fieldset style="border:1px solid white;text-align:left;">
                        <legend style="font-size:12px;padding:0px;margin:0px;"><b>&nbsp;Informações&nbsp;</b></legend>
                        <p style="padding:1px;margin:0px;">
                            <b>Matrícula&nbsp;:&nbsp;</b>Matrícula do servidor/estagiário;
                        </p>
                        <p style="padding:1px;margin:0px;">
                            <b>CPF&nbsp;:&nbsp;</b>O número do CPF do servidor/estagiário;
                        </p>
                    </fieldset>
                </div>
            </div>

        </form>
    </div>
</div>

<script>

    $(document).ready(function ()
    {
        $('#btn-reiniciar-senha').click(function ()
        {
            $('#form1').attr('action', 'reiniciar_senhas_gravar.php');
            $('#form1').submit();
            return true;
        });
    });

    function ve(parm1)
    {
        var siape = $('#siape');
        var cpf = $('#cpf');

        if (siape.val().length == 7)
        {
            cpf.focus();
        }
    }

    $('#siape').focus();

</script>

<?php
include("html/footer.php");

DataBase::fechaConexao();
