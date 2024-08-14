<?php
include_once("config.php");

verifica_permissao("tabela_prazos");


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho("Tabelas");
$oForm->setCSS(_DIR_CSS_ . "estilos.css");
$oForm->setOnLoad("javascript: if($('#pSiape')) { $('#pSiape').focus() };");
$oForm->setLargura('950');
$oForm->setSeparador(0);

// Topo do formulário
//
$oForm->setSubTitulo("Corre&ccedil;&atilde;o de Problemas com Substitui&ccedil;&atilde;o");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<form method="POST" action="suporte_substituicao.php" id="form1" name="form1">
    <div align="center">
        <p><b><font size="2">Informe a matrícula do servidor</font></b></p>
        <p><font size=1>
            <input name="matricula" type="text" class="caixa" id="matricula" size="7" maxlength="7" ">
            </font></p>
    </div>
    <p align="center" style="word-spacing: 0; margin-top: 0; margin-bottom: 0">&nbsp;</p>
    <p align="center" style="word-spacing: 0; margin: 0"><input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" ></p>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
