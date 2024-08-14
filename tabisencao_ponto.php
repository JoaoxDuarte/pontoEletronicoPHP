<?php
include_once("config.php");
include_once( "src/controllers/TabIsencaoPontoController.php" );

verifica_permissao("administracao_central");

$oIsento = new TabIsencaoPontoController();
$oIsento->showListaIsencaoPonto();
