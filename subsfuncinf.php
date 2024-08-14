<?php

include_once( "config.php" );

verifica_permissao('gravar_frequencia');

// matricula do substituto
//   - para uso em caso de erro
//     e retorno a página de dados
$_SESSION['sMatriculaSubstitutoEfetivar'] = "";

## classe para montagem do formulario padrao
#
//$oForm->setSiapeUsuario($_SESSION['sMatricula']);
//$oForm->setSiapeDestino("substfunc.php");
//$oForm->setSiapeRetorno("principal_abertura.php");


## classe para montagem do formulario padrao
#
$title = _SISTEMA_SIGLA_ . ' | Substituição';

$oForm = new formPadrao();
$oForm->setCSS( 'css/plugins/dlg.min.css' );
$oForm->setJS( 'js/phpjs.js' );
$oForm->setJS( "js/jquery.blockUI.js?v2.38" );
$oForm->setJS( "js/jquery.bgiframe.js" );
$oForm->setJS( "js/plugins/jquery.dlg.min.js" );
$oForm->setJS( "js/plugins/jquery.easing.js" );
$oForm->setJS( "js/jquery.ui.min.js" );
$oForm->setJS( "subsfuncinf.js?v.0.0.0.0.1" );

$oForm->setSubTitulo("Efetivar Substitui&ccedil;&atilde;o de Fun&ccedil;&atilde;o");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
        <form id="form1" name="form1" method="POST" action="javascript:void(0);" onsubmit="javascript:return false;">

            <div class="col-md-2 col-md-offset-5">
                <table class="table table-striped table-condensed table-bordered text-center">
                    <tbody>
                        <tr height='25'>
                            <td class='text-center'>
                                Matrícula<br>
                                <input tipo="matricula" id="matricula" name="matricula" class="form-control text-center" size="7" maxlength="7" value="" type="text">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="form-group text-center">
                    <a class="btn btn-success btn-primary" id="btn-continuar">
                        <span class="glyphicon glyphicon-ok"></span> Continuar
                    </a>
                </div>
            </div>

            <div class="col-md-12">
                <div style="text-align:right;width:95%;margin:25px;font-size:9px;border:0px;">
                    <fieldset style="border:1px solid white;text-align:left;">
                        <legend style="font-size:12px;padding:0px;margin:0px;"><b>&nbsp;Informações&nbsp;</b></legend>
                        <p style="padding:1px;margin:0px;">
                            <b>Matrícula&nbsp;:&nbsp;</b>Matrícula do servidor/estagiário.
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

DataBase::fechaConexao();
