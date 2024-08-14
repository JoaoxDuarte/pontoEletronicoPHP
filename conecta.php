<?php

/*
 * Dados de acesso ao banco de dados
 *
 * */
include_once( "config.php" );

$oDBase = new DataBase('PDO');
$oDBase->conectar();

$link = $oDBase->getIdLink();
$con  = $oDBase->getSelecionadoDB();
