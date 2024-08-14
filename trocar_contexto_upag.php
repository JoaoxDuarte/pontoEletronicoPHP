<?php
include_once("config.php");

$upagSelecionada = anti_injection($_REQUEST['upagContexto']);

if ( strlen($upagSelecionada) == 14 )
{
    $_SESSION['upag'] = $_REQUEST['upagContexto'];
    $_SESSION['troca_contexto'] = true;
}

header('Location: /sisref/principal_abertura.php');