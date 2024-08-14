<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");

// formulario padrao para siape e competencia
include_once("class_form.siape.competencia.php");

// Verifica se existe um usu�rio logado e se possui permiss�o para este acesso
verifica_permissao('sRH e sTabServidor');

if (__HISTORICO_DESATIVADO__)
{
    //mensagem( "INFORMA��O:\\n\\n\\M�dulo Hist�rico em manuten��o." );

    replaceLink('principal_abertura.php');
    die();
}

$erro = $_REQUEST['erro'];

if ($erro == 'siape_vazia')
{
	mensagem( '� obrigat�rio informar a matr�cula com 7 caracteres!' );
	//replaceLink( 'historico_frequencia.php' );
}
else if ($erro == 'siape_usuario')
{
	mensagem( 'Voc� n�o pode alterar sua pr�pria frequ�ncia!' );
	//replaceLink( 'historico_frequencia.php' );
}

## classe para montagem do formulario padrao
#
$oForm = new formSiapeCompetencia; // instancia o formul�rio
$oForm->setJS( "historico_frequencia.js?v1.0.0.0.12" ); // script extras utilizados pelo formulario

$oForm->setCaminho( 'Frequ�ncia � RH Atualizar � Hist�rico' ); // localizacao deste formulario
$oForm->setSubTitulo( "Hist&oacute;rico - Manuten&ccedil;&atilde;o de Ocorr&ecirc;ncia" ); // sub-titulo principal

$oForm->setSiapeCompetenciaDestino( "#" ); // pagina de destino (action)
$oForm->setSiapeCompetenciaValidar( "javascript:return false;" ); // script de teste dos dados do formul�rio e envio (onSubmit)

// campos hidden
$oForm->initInputHidden();
$oForm->setInputHidden( "ano_hoje", date('Y') ); // ano da data atual
$oForm->setInputHidden( "usuario",  $_SESSION['sMatricula'] ); // matricula do usuario logado

$oForm->setSiapeNome( "siape"); // matricula para consulta
//$oForm->setSiapeTitulo( '' );
$oForm->setMesNome( "mes" ); // mes da consulta
$oForm->setMesTitulo( '' );
$oForm->setAnoNome( "ano" ); // ano da consulta
$oForm->setAnoTitulo( '' );
$oForm->setSiapeResponsavelNome( "siape_responsavel"); // matricula respons�vel
$oForm->setSiapeResponsavelTitulo( 'Respons�vel pela Solicita��o da Altera��o' );

// observacao a exibir ap�s o formul�rio
$oForm->setObservacaoBase( "A inclus&atilde;o de ocorr&ecirc;ncias no hist&oacute;rico dever&aacute; ser utilizada apenas para compet&ecirc;ncias de 10/2009 em diante, limitando-se sempre ao m�s anterior ao da homologa��o." );

$oForm->setOnLoad( "$('#siape').focus();" );

// exibe o formulario
$oForm->exibeForm();
