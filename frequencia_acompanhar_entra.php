<?php

/**
 * +-------------------------------------------------------------+
 * | @description : Acompanhar os Registros de Frequência        |
 * |                realizados por servidores/estagiários        |
 * |                                                             |
 * | @author  : Carlos Augusto                                   |
 * | @version : Edinalvo Rosa                                    |
 * +-------------------------------------------------------------+
 * */
// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("sAPS");

// dados para o formulario
$form_destino = array("frequencia_acompanhar_registros.php", base64_encode("2:|:1:|:" . $_SESSION['sLotacao'] . ":|:" . date('d/m/Y')));
$form_caminho = "Acompanhar";

// grava o LOG
registraLog(" acessou o Módulo " . $form_caminho, "", "", $form_caminho);

include_once( "frequencia_entra_formulario.php" );
