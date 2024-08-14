<?php

/*
 * Módulo acionado - SOGP (RH)
 */

include_once( 'config.php' );

$_SESSION['sModuloPrincipalAcionado'] = 'sogp';
$ModuloPrincipalAcionado              = 'sogp';

$_SESSION['sHOrigem_1'] = 'rh.php';

include_once( 'principal.php' );