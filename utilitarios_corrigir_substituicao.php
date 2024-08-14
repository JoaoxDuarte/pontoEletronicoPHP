<?php

	// Inicia a sess�o e carrega as fun��es de uso geral
	include_once("config.php");

	// formulario padrao para siape e competencia
	include_once("class_form.siape.php");

	// Verifica se existe um usu�rio logado e se possui permiss�o para este acesso
	verifica_permissao( 'sRH e sTabServidor' );


	## classe para montagem do formulario padrao
	#
	$oForm = new formSiape; // instancia o formul�rio
	$oForm->setCaminho( 'Utilit�rios � Manuten��o � Corrigir Substitui��o' );
	$oForm->setSubTitulo( "Corre��o de Problemas com Substitui��o" );
	$oForm->setOnLoad( "javascript: if(document.getElementById('pSiape')) { document.getElementById('pSiape').focus() };" );
	$oForm->setSeparador( 30 );

	$oForm->setSiapeUsuario( $_SESSION['sMatricula'] );

	// destino e valida��o
	$oForm->setJS( "utilitarios_corrigir_substituicao.js" ); // script extras utilizados pelo formulario

	// siape
	$oForm->setSiapeCaixa( "222px" );
	$oForm->setSiapeCaixaWidth( "26%" );
	$oForm->setSiapeCaixaBorda('0');
	$oForm->setSiapeTitulo( "Informe a matr�cula siape do servidor" );

	$oForm->setSiapeDestino( "utilitarios_corrigir_substituicao_form.php" ); // pagina de destino (action)
	$oForm->setSiapeValidar( "javascript:return validar();" ); // script de teste dos dados do formul�rio e envio (onSubmit)

	// exibe o formulario
	$oForm->exibeForm();
