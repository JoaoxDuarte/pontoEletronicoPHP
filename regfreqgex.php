<?php

include_once( "config.php" );

verifica_permissao("sAPS");

// pega o nome do arquivo origem
$pagina_de_origem = pagina_de_origem();

if ($_SESSION['sAPS'] == "S") // só aps
    $diaa     = $_REQUEST["dia"];
$orig     = anti_injection($_REQUEST["orig"]);
$cmd      = anti_injection($_REQUEST["cmd"]);
$qlotacao = anti_injection($_REQUEST["qlotacao"]);

$sr_gerencial = 'sim';

// dados para retorno a este script
$_SESSION['sPaginaRetorno_sucesso'] = $_SERVER['REQUEST_URI'] . "&dia=$diaa&cmd=$cmd&orig=$orig";

include_once( 'regfreq4_form.php' );
