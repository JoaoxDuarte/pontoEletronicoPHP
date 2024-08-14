<?php

	// Inicia a sessão e carrega as funções de uso geral
	include_once("config.php");

	// formulario padrao para siape e competencia
	include_once("class_form.siape.php");

	// Verifica se existe um usuário logado e se possui permissão para este acesso
	verifica_permissao( 'sRH e sTabServidor' );


	## classe para montagem do formulario padrao
	#
	$oForm = new formSiape; // instancia o formulário
	$oForm->setCaminho( 'Utilitários » Manutenção » Corrigir Substituição' );
	$oForm->setSubTitulo( "Correção de Problemas com Substituição" );
	$oForm->setOnLoad( "javascript: if(document.getElementById('pSiape')) { document.getElementById('pSiape').focus() };" );
	$oForm->setSeparador( 30 );

	$oForm->setSiapeUsuario( $_SESSION['sMatricula'] );

	// destino e validação
	$oForm->setJS( "utilitarios_corrigir_substituicao.js" ); // script extras utilizados pelo formulario

	// siape
	$oForm->setSiapeCaixa( "222px" );
	$oForm->setSiapeCaixaWidth( "26%" );
	$oForm->setSiapeCaixaBorda('0');
	$oForm->setSiapeTitulo( "Informe a matrícula siape do servidor" );

	$oForm->setSiapeDestino( "utilitarios_corrigir_substituicao_form.php" ); // pagina de destino (action)
	$oForm->setSiapeValidar( "javascript:return validar();" ); // script de teste dos dados do formulário e envio (onSubmit)

	// exibe o formulario
	$oForm->exibeForm();
