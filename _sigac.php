<?php

include_once("config.php");
include_once("Sigac.php");

$sigac = new Sigac();


echo('<pre>');
//var_export($_SESSION);
die('</pre>');

if($_GET['delete'] == 'sim') {

    $sigac->destroyTokenAccess($_SESSION['SIGAC_TOKEN_ACCESS']);

} else {
    $teste = $sigac->validateTokenAccess($_SESSION['SIGAC_TOKEN_ACCESS']);
}



