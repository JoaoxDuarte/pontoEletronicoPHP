<?php

/**
 * +-------------------------------------------------------------+
 * | @description : Acompanhar os Registros de Frequência        |
 * |                realizados por servidores/estagiários        |
 * |                                                             |
 * | @author  : Carlos Ausgusto                                  |
 * | @version : Edinalvo Rosa                                    |
 * +-------------------------------------------------------------+
 * */
// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("sAPS");

// endereço de retorno
$_SESSION['sPaginaRetorno_inicio'] = "regfreq4.php?cmd=1&orig=1";
$_SESSION["sVePonto"]              = "regfreq4.php?cmd=1&orig=1";

// dados para o formulario
$form_destino = array("regfreq4.php", "cmd=1&orig=1");
$form_caminho = "Acompanhar";

include_once( "frequencia_entra_formulario.php" );
