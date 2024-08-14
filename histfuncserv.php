<?php
include_once( "config.php" );

verifica_permissao("sRH");

// instancia o BD
$oDBase = new DataBase('PDO');


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho("Relatórios » Gerencial » Ocupantes de Fun&ccedil;&otilde;es");
$oForm->setJQuery();
$oForm->setCSS(_DIR_CSS_ . "estilos_new_laytou.css");
$oForm->setOnLoad("javascript: if($('#pSiape')) { $('#pSiape').focus() };");
$oForm->setLargura('920px');
$oForm->setSeparador(0);

$oForm->setSubTitulo("Consulta Histórico de Funções do Servidor");

$oForm->setObservacaoTopo("Informe a matrícula do servidor");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<form method="POST" action="conshistfunc.php" id="form1" id="form1">


    <div class="row col-md-3 col-md-offset-5" style="padding:0px 0px 0px 43px;">
        <label class="control-label" for="name">
            Matrícula          :                          </label>
        <input type="text" id="pSiape" name="pSiape" class="form-control" size="7" maxlength="7" value="" onkeyup="javascript:ve(this.value);" style="width:100px">



        <div class="col-md-12 margin-25">
            <div class="text-left">

                <button type="submit" class="btn btn-sucess  btn-primary" id="btn-continuar">
                    <span class="glyphicon glyphicon-ok"></span> OK
                </button>
            </div>

        </div>
    </div>

</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
