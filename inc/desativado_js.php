<?php
$qtd               = explode('/', $_SERVER['PHP_SELF']);
$path_dots_slashes = str_repeat("../", count($qtd) - 3);

// Inicia a sess�o e carrega as fun��es de uso geral
include_once( $path_dots_slashes . "config.php");

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setLogoExibe(true); // exibe a imagem do topo da p�gina
$oForm->setNoScriptAtivo(false);
$oForm->setNoFramesAtivo(false);
//$oForm->setNoPopUpAtivo(false);
$oForm->setObservacaoTopo("<font color=red size='2'>AVISO</font>");

// Topo do formul�rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<div align="center">
    Para utilizar o sistema � necess�rio que o <b>javascript</b> esteja habilitado em seu navegador!
    <br>
    Por favor, habilite o javascript e tente outra vez.
    <br>
</div>
<?php
// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
