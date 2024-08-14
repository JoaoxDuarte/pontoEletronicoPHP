<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "class_form.siape.php" );

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH e sTabServidor');

include_once( "frequencia_rh_mes_corrente.php" );

/*
## classe para montagem do formulario padrao
#
$oForm = new formSiape();
$oForm->setCaminho('Frequência » Atualizar » Mês corrente » Incluir ocorrência');
//$oForm->setJS("freqinclui.js");
$oForm->setOnLoad("javascript: if($('#pSiape')) { $('#pSiape').focus() };");
$oForm->setSeparador(30);

$oForm->setSubTitulo("Inclusão de Ocorrência");
$oForm->setObservacaoTopo("Utilize essa op&ccedil;&atilde;o para inclus&atilde;o de ocorr&ecirc;ncias no m&ecirc;s corrente");

//define a competencia
$oForm->setSiapeAno(date('Y'));
$oForm->setSiapeMes(date('m'));
$oForm->setSiapeYear(date('Y'));
$oForm->setSiapeCmd("1");

// matrícula do servidor logado
$oForm->setSiapeUsuario($_SESSION['sMatricula']);

$oForm->setSiapeNome('pSiape');
$oForm->setSiapeTitulo('Matrícula do servidor/estagiário');
$oForm->setSiapeTituloClass('ft_13_001');
$oForm->setSiapeCaixa('800');
$oForm->setSiapeCaixaBorda('0');
$oForm->setSiapeCaixaWidth('790');

$oForm->setSiapeDestino("freqinclui2.php");
$oForm->setSiapeValidar("return validar()");
$oForm->exibeForm();
*/
