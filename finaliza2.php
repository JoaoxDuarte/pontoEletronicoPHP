<?php

include_once( 'config.php' );

$destino = anti_injection($_REQUEST['modulo']);

include_once( 'finaliza.php' );
