<?php

include_once( "config.php" );
include_once( "src/controllers/TabCargoController.php" );

verifica_permissao("manutencao_cargos");

$oCargo = new TabCargoController();
$oCargo->registrosCargoRetornoAjax();
