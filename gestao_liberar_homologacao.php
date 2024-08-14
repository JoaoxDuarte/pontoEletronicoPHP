<?php
/**
 * +-------------------------------------------------------------+
 * | @description : Solicita Autoriza��o Trabalho Dia N�o �til   |
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


$title = _SISTEMA_SIGLA_ . ' | Libera��o de Prazo para Homologa��o';

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setJSDialogProcessando();
$oForm->setJSDatePicker();
$oForm->setJS( "js/phpjs.js" );

$oForm->setJS("gestao_liberar_homologacao.js?v.0.0.0.0.0.4");

$oForm->setSubTitulo("Libera��o de Prazo para Homologa��o");

// Topo do formul�rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// seleciona registros de solicita��o anterior
$html_rows = montaTabelaSolicitacoes($_SESSION['sLotacao']);

// formul�rio homologa��o - solicita��es
formularioHomologacaoSolicitacoes($html_rows);

// janela modal - exibir justificativas
modalJustificativa();

DataBase::fechaConexao();


// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();


exit();
