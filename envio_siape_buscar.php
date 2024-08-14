<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Dados par ao formulario
$sFormCaminho   = 'Cadastro » Funcional » Consultar';
$sFormsubTitulo = "Envio de ocorrência para o SIAPE";
$sFormAcao      = "cadastro_consulta.php";
$sFormSubmit    = "return validar()";
$sFormDestino   = "cadastro_consulta_formulario.php";

$_SESSION['sPaginaRetorno_sucesso'] = $sFormAcao;

// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();
$sessao_navegacao->initSessaoNavegacao();
$sessao_navegacao->setPagina($_SERVER['REQUEST_URI']);

include_once( "pesquisa_envio_siape.php" );