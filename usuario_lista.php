<?php
/* _________________________________________________________________________*\
  |                                                                           |
  |   MANUTEN��O DAS PERMISS�ES DO USUARIO CADASTRADO                         |
  |                                                                           |
  \*������������������������������������������������������������������������� */

include_once('config.php');

verifica_permissao('administrador_e_chefe_de_rh');


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho('Utilit�rios � Usu�rios � Alterar/Excluir Usu�rio');
$oForm->setJS(_DIR_JS_ . "jquery.blockUI.js?v2.38");
$oForm->setJS(_DIR_JS_ . "plugins/jquery.dlg.min.js");
$oForm->setJS(_DIR_JS_ . "plugins/jquery.easing.js");
$oForm->setJS(_DIR_JS_ . "jquery.ui.min.js");
$oForm->setJS( "usuario_lista.js?v.0.0.2" );
$oForm->setOnLoad("if($('#pesquisa')) { $('#pesquisa').focus(); }");
$oForm->setLargura('900px');
$oForm->setSeparador(0);

$oForm->setSubTitulo("Manuten��o de Usu�rios do Sistema");

if ($_SESSION['sSenhaI'] == "S")
{
    $oForm->setObservacaoTopo("Digite a informa��o desejada ou parte da mesma<br>(matr�cula ou nome do usu�rio ou c�digo da unidade)");
}


// Topo do formul�rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


if ($_SESSION['sSenhaI'] == "S")
{
    $destino = "usuario_alt.php";
    ?>
    <style>
        td  { font-family: verdana; font-size: 9pt; }
        .drop  { font-family: arial,verdana; font-size: 8pt; }
        fieldset { font-family: verdana; font-size: 9pt; width: 100%; }
    </style>
    
    <form id="dados" name='dados' method="POST" action="#">
        <div valign='middle' class='col-md-12 text-center'>
            <div valign='middle' class='col-md-6 col-lg-offset-3 text-center'>
                <table class='table table-striped table-condensed table-bordered text-center'>
                    <tr>
                        <td class='text-center col-md-6'>
                            <font class="ft_13_003">&nbsp;Matr�cula/Nome/Lota��o<br>&nbsp;
                            <input type="text" id="pesquisa" name="pesquisa" class="form-control" size="50" maxlength="100" value="">
                            </font>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div id='mensagem_aviso'></div>
    </form>

    <br>
    <label>LISTA DE USU�RIOS<br></label>
    <div id='id_registros' style='font-size: 10px;'></div>

    <div id='id_lista' style='text-align: center; width: 90%;'></div>

    <script language="JavaScript">
        var urlDestino = '<?= tratarHTML($destino); ?>';
        var sMatricula = '<?= tratarHTML($_SESSION["sMatricula"]); ?>';
        $('#pesquisa').val('<?= tratarHTML($_SESSION["searchCampo"]); ?>');
        $('#pesquisa').focus();
        if ($('#pesquisa').val() != '')
        {
            dadosCadUsuarios();
        }
    </script>
    <?php
}
else
{
    $destino = "alterausurhg.php";
    ?>
    <style>
        td  { font-family: verdana; font-size: 9pt; }
        .drop  { font-family: arial,verdana; font-size: 8pt; }
        fieldset { font-family: verdana; font-size: 9pt; width: 90%; }
    </style>
    <div align='center'>
        <label>LISTA DE USU�RIOS</label>
        <div id='id_registros' style='font-size: 10px;'></div>

        <form id="form1" name='form1' method="POST" action="#">
        <div id='id_lista' style='width: 100%;'></div>
        </form>

        <script language="JavaScript">
            var urlDestino = '<?= tratarHTML($destino); ?>';
            var sMatricula = '<?= tratarHTML($_SESSION["sMatricula"]); ?>';
            var sRH = '<?= tratarHTML($_SESSION["sRH"]); ?>';
            dadosCadUsuariosRH();
        </script>
    </div>
    <?php
}

// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

?>
