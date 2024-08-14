<?php

// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("sRH e sTabServidor");


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setSubTitulo("Altera&ccedil;&atilde;o de Dados de Ocupante de Fun&ccedil;&atilde;o");


// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<form method="POST" action="altfuncserv2.php" id="form1" name="form1">

    <div style='width:400px;height:60px;text-align:center;padding:20px 0px 0px 0px;margin:0 auto;'>
        <div class='verdanaSize_1' style='float:left;font-weight:bold;text-align:left; width:100px;'>
            Matrícula:<br>
            <input type="text" name="matricula" class="caixa" size="7" maxlength="7">
        </div>

        <div class='verdanaSize_1' style='float:left;font-weight:bold;text-align:left; margin:0 auto;'>
            Situação ocupante:<br>
            <select name="sit" size="1" class="drop" id="select2" >
                <option value="V"<?= ($sit == "V" ? " selected" : ""); ?>>Selecione a Situação </option>
                <option value="T"<?= ($sit == "T" ? " selected" : ""); ?>>TITULAR</option>
                <option value="S"<?= ($sit == "S" ? " selected" : ""); ?>>SUBSTITUTO</option>
                <option value="R"<?= ($sit == "R" ? " selected" : ""); ?>>INTERINO</option>
            </select>
        </div>
    </div>

    <div style='display:block;width:100%;text-align:center;'>
        <input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" >
    </div>

</form>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
