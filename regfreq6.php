<?php

include_once("config.php");

verifica_permissao("sRH");

$pSiape = $_REQUEST["mat"];
$mes    = $_REQUEST["mes"];
$ano    = $_REQUEST["ano"];
$anot   = $_REQUEST["anot"];
$cmd    = $_REQUEST["cmd"];

$caminho_modulo_utilizado = 'Frequência » Atualizar » Mes corrente » Alterar ocorrência';

// paginas de retorno
$_SESSION['voltar_nivel_0'] = $_SERVER['REQUEST_URI'] . "?mat=$pSiape&mes=$mes&ano=$ano&anot=$anot&cmd=$cmd";
$_SESSION['voltar_nivel_1'] = '';
$_SESSION['voltar_nivel_2'] = '';
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';

include_once( "veponto_formulario.php" );
