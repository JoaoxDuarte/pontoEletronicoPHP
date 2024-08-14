<?php

/*
 * Mdulo acionado - Chefia
 */

include_once( 'config.php' );

$_SESSION['sModuloPrincipalAcionado'] = 'chefia';
$ModuloPrincipalAcionado              = 'chefia';

$_SESSION['sHOrigem_1'] = 'chefia.php';

include_once( 'principal.php' );
