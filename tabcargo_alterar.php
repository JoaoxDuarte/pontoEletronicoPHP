<?php
include_once("config.php");
include_once( "src/controllers/TabCargoController.php" );

verifica_permissao("manutencao_cargos");

$oIsento = new TabCargoController();
$oIsento->showFormularioAlterarCargo();
