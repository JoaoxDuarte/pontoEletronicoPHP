<?php

include_once( "config.php" );

verifica_permissao("ponto_servidor");

$pSiape = anti_injection($_REQUEST["pSiape"]);
$mes    = anti_injection($_REQUEST["mes3"]);
$ano    = anti_injection($_REQUEST["ano3"]);
$cmd    = anti_injection($_REQUEST["cmd"]);

$caminho_modulo_utilizado = 'Frequência » Visualizar » Consulta frequência';


include_once( "veponto_formulario_regponto.php" );
