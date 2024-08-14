<?php

include_once('config.php');
include_once( "class_login.php" ); // classe com os metodos para a realização do login
include_once("Sigac.php");

$modo    = anti_injection($_REQUEST['modo']);
$modulo  = anti_injection($_REQUEST['modulo']);
$destino = anti_injection($_REQUEST['destino']);

$logado = $_SESSION['logado'];

if ($modulo != "")
{
    $ModuloPrincipalAcionado = $modulo;
}
else if ($_SESSION['sModuloPrincipalAcionado'] != "")
{
    $ModuloPrincipalAcionado = $_SESSION['sModuloPrincipalAcionado'];
}
else
{
    $ModuloPrincipalAcionado = 'entrada';
}

$ModuloPrincipalAcionado = strtr($ModuloPrincipalAcionado, array('app' => 'entrada', 'sogp' => 'rh'));
$destino = ($ModuloPrincipalAcionado == ".php" ? "entrada.php" : $ModuloPrincipalAcionado . ".php");


// Elimina a sessao
destroi_sessao();

switch ($modo)
{
    case 1:
        retornaErro('entrada.php', "Sessão finalizada.<br>Por favor, realize o login para ter acesso ao sistema!", true, 'info');
        break;
    default:
        if (strtoupper($logado) == "SIM")
        {
            if ($destino == 'entrada.php')
            {
                //Inicializo a sessão para que a mensagem seja enviada ao script entrada.php
                session_start();
                retornaErro($destino, "Sessão finalizada.", true, 'success');
            }
            else
            {
                retornaErro($destino, "Sessão finalizada.", true, 'success');
            }
            break;
        }
}

replaceLink($destino);
