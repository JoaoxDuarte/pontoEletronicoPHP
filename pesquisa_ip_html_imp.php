<?php

include_once( "config.php" );

verifica_permissao("logado");

set_time_limit(180);

$idInner = str_replace("&nbsp;&nbsp;", "", $_SESSION['sIMPFormFrequencia']);
$idInner = str_replace("<img  src='" . _DIR_IMAGEM_ . "printer.gif' height=40 border='0'>", "", $idInner);
$idInner = str_replace(".width1 { width: 1%; }", ".width1 { width: 12%; }", $idInner);

// gera o arquivo em PDF
include_once( "gera_pdf.php" );
