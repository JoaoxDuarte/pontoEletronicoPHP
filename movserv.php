<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH e sTabServidor');

// limpa siape do servidor
// para que o teste de erro de upag
// possa funcionar corretamente;
unset($_SESSION['sMov_Matricula_Siape']);

// limpa sessão
unset($_SESSION['sMov_Entra_Unidade']);
unset($_SESSION['sMov_Nova_Unidade']);


## classe para montagem do formulario padrao
#
$oForm = new formPadrao(); // instancia o formulário
$oForm->setJS('movserv.js?v.0.0.0.7'); // script extras utilizados pelo formulario
$oForm->setSubTitulo("Movimentação de Servidores/Estagiários");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
<form method='POST' id='form1' name='form1' action='#' onSubmit='javascript:return false;'>

    <div valign='middle' class='col-md-12 text-center'>
        <div valign='middle' class='col-md-3 col-lg-offset-4 text-center'>
            <table class='table table-striped table-condensed table-bordered text-center'>
                <tr>
                    <td class='text-center col-md-2'>
                        <font class="ft_13_003">
                        &nbsp;Matrícula SIAPE<br>&nbsp;
                        <input type="text" id="siape" name="siape" class="form-control alinhadoAEsquerda" size="7" maxlength="7" value="">
                        </font>
                    </td>
                </tr>
            </table>
        </div>

        <div class="form-group col-md-8 text-center">
            <div class="col-md-7 col-md-offset-6 margin-10">
                <div class="col-md-6 text-right">
                    <a class="btn btn-success btn-primary" id="btn-continuar">
                        <span class="glyphicon glyphicon-ok"></span> Continuar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div style='text-align:right;width:90%;margin:25px;font-size:9px;border:0px;'>
            <fieldset style='border:1px solid white;text-align:left;'>
                <legend style='font-size:12px;padding:0px;margin:0px;'><b>&nbsp;Informações&nbsp;</b></legend>
                <p style='padding:1px;margin:0px;'>
                    <b>Matrícula SIAPE&nbsp;:&nbsp;</b><b></b>Matrícula do servidor/estagiário;
                </p>
            </fieldset>
        </div>
    </div>
</form>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
