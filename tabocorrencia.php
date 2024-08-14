<?php
include_once("config.php");
include_once( "src/controllers/TabOcorrenciaController.php" );

verifica_permissao("manutencao_ocorrencias");

$obj = new TabOcorrenciaController();
$obj->showLista();
