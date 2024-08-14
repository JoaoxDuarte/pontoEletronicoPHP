<?php

// conexao ao banco de dados
// funcoes diversas
include_once( "config.php" );

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH');

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    $pSiape = $_REQUEST["mat"];
}
else
{
    $dados  = explode(":|:", base64_decode($dadosorigem));
    $pSiape = $dados[0];
    $lot    = $dados[1];
    $jnd    = $dados[2];
}

$oData = new trata_datasys();
$mes   = $oData->getMesAnterior();
$ano   = $oData->getAnoAnterior();

// dados voltar
$_SESSION['voltar_nivel_2'] = 'regfreq8.php?dados=' . $dadosorigem;
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';

$_SESSION['tabPosition'] = '#' . $lot;

$caminho_modulo_utilizado = 'Relatórios » Frequência » Homologados » Verificar';
$form_action              = "gravaregfreq1.php?modo=14";
$form_submit              = "return validar()";

include_once( "veponto_formulario.php" );
