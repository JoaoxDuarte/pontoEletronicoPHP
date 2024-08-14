<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// formulario padrao para siape e competencia
include_once("class_form.siape.competencia.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao("sRH e sTabServidor");

// pagina de retorno
$_SESSION['sPaginaDeRetorno1']      = 'gestao_veponto.php';
$_SESSION['sPaginaDeRetorno2']      = '';
$_SESSION['sPaginaDeRetorno3']      = '';
$_SESSION['sPaginaDeRetorno4']      = '';
$_SESSION['sPaginaRetorno_sucesso'] = '';

unset($_SESSION['sDadosParaVerComprovante']);
$_SESSION['sDadosParaVerComprovante'] = NULL;


## classe para montagem do formulario
#
$oForm = new formSiapeCompetencia; // instancia o formulário
$oForm->setJS("veponto.js"); 
$oForm->setJS("js/fc_data.js"); 
$oForm->setInputHidden('cmd', '1');
$oForm->setSiapeCompetenciaDestino("pontoser.php"); // pagina de destino (action)
$oForm->setSubTitulo("Consulta Frequ&ecirc;ncia");
$oForm->exibeForm();
