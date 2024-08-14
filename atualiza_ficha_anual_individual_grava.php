<?php

/**
 * +-------------------------------------------------------------+
 * | @description : Acerto da Ficha Anual - Grava                |
 * |                                                             |
 * | @author  : Carlos Augusto                                   |
 * | @version : Edinalvo Rosa                                    |
 * +-------------------------------------------------------------+
 * */
// Inicia a sessуo e carrega as funчѕes de uso geral
include_once("config.php");

// Verifica se existe um usuсrio logado e se possui permissуo para este acesso
verifica_permissao("sLog");

$mes   = $_REQUEST['mes'];
$ano   = $_REQUEST['ano'];
$siape = $_REQUEST['siape'];

$comp = $mes . $ano;

// Atualizando FRQano
atualiza_frqANO($siape, $mes, $ano, "atualiza_ficha_anual_individual.php");

mensagem("Operaчуo realizada com sucesso!", "atualiza_ficha_anual_individual.php", 1);
exit();
