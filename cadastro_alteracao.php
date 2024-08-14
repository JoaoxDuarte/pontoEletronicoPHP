<?php

// Inicia a sessão e carrega as funções de uso geral
include_once( "config.php" );

include_once( "cadastro_sessao_elimina.php" );

// Dados par ao formulario
$sFormCaminho   = 'Cadastro » Funcional » Alterar';
$sFormsubTitulo = "Alterar dados Funcionais";
$sFormAcao      = "cadastro_alteracao.php";
$sFormSubmit    = "";
$sFormDestino   = "cadastro_alteracao_formulario.php";

// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();
$sessao_navegacao->initSessaoNavegacao();
$sessao_navegacao->setPagina($_SERVER['REQUEST_URI']);

include_once( "pesquisa_servidor_formulario.php" );
