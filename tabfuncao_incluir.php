<?php
include_once("config.php");
include_once( "src/controllers/TabFuncaoController.php" );

verifica_permissao("manutencao_funcoes");

$obj = new TabFuncaoController();
$obj->showFormularioIncluir();
