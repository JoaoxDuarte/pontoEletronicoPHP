<?php

include_once( "config.php" );

verifica_permissao("sAPS");

// pega o nome do arquivo origem
$pagina_de_origem = pagina_de_origem();

$diaa = $_REQUEST["dia"];
$orig = anti_injection($_REQUEST["orig"]);
$cmd  = anti_injection($_REQUEST["cmd"]);

// dados para retorno a este script
$_SESSION['sPaginaRetorno_sucesso'] = $_SERVER['REQUEST_URI'] . "&dia=$diaa&cmd=$cmd&orig=$orig";

include_once( 'regfreq4_form.php' );
