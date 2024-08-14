<?php

include_once( "config.php" );
include_once( "src/controllers/TabHistHomologadosController.php" );

verifica_permissao("logs");

$obj = new TabHistHomologadosController();
$obj->registrosTabHistHomologadosRetornoAjax();
