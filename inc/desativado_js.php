<?php
$qtd               = explode('/', $_SERVER['PHP_SELF']);
$path_dots_slashes = str_repeat("../", count($qtd) - 3);

// Inicia a sessão e carrega as funções de uso geral
include_once( $path_dots_slashes . "config.php");

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setLogoExibe(true); // exibe a imagem do topo da página
$oForm->setNoScriptAtivo(false);
$oForm->setNoFramesAtivo(false);
//$oForm->setNoPopUpAtivo(false);
$oForm->setObservacaoTopo("<font color=red size='2'>AVISO</font>");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<div align="center">
    Para utilizar o sistema é necessário que o <b>javascript</b> esteja habilitado em seu navegador!
    <br>
    Por favor, habilite o javascript e tente outra vez.
    <br>
</div>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
