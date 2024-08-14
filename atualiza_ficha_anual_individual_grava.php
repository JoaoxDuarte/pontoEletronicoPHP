<?php

/**
 * +-------------------------------------------------------------+
 * | @description : Acerto da Ficha Anual - Grava                |
 * |                                                             |
 * | @author  : Carlos Augusto                                   |
 * | @version : Edinalvo Rosa                                    |
 * +-------------------------------------------------------------+
 * */
// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");

// Verifica se existe um usu�rio logado e se possui permiss�o para este acesso
verifica_permissao("sLog");

$mes   = $_REQUEST['mes'];
$ano   = $_REQUEST['ano'];
$siape = $_REQUEST['siape'];

$comp = $mes . $ano;

// Atualizando FRQano
atualiza_frqANO($siape, $mes, $ano, "atualiza_ficha_anual_individual.php");

mensagem("Opera��o realizada com sucesso!", "atualiza_ficha_anual_individual.php", 1);
exit();
