<?php
/**
 * +-------------------------------------------------------------+
 * | @description : Solicita Autorização Trabalho Dia Não Útil   |
 * |                                                             |
 * | @author  : Edinalvo Rosa                                    |
 * +-------------------------------------------------------------+
 * 
 */

// funcoes de uso geral
include_once( "config.php" );
include_once('gestao_liberar_homologacao_funcoes.php');


// permissao de acesso
verifica_permissao("sRH");

// pagina atual
$_SESSION['voltar_nivel_1'] = $_SERVER['REQUEST_URI'];


$title = _SISTEMA_SIGLA_ . ' | Liberação de Prazo para Homologação';

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setJSDialogProcessando();
$oForm->setJSDatePicker();
$oForm->setJS( "js/phpjs.js" );

$oForm->setJS("gestao_liberar_homologacao.js?v.0.0.0.0.0.4");

$oForm->setSubTitulo("Liberação de Prazo para Homologação");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// seleciona registros de solicitação anterior
$html_rows = montaTabelaSolicitacoes($_SESSION['sLotacao']);

// formulário homologação - solicitações
formularioHomologacaoSolicitacoes($html_rows);

// janela modal - exibir justificativas
modalJustificativa();

DataBase::fechaConexao();


// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();


exit();
