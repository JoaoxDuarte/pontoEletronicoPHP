<?php

// funcoes de uso geral
include_once( "config.php" );

define('TEMPOEX', 3 * 60); /* tempo de expiração da sessão em minutos */

//todas as páginas que quiser um lifetime para a sessão
ini_set('session.gc_probability', 100);
ini_set('session.gc_maxlifetime', TEMPOEX);
ini_set('session.cookie_lifetime', TEMPOEX);
ini_set('session.cache_expire', TEMPOEX);

// permissao de acesso
verifica_permissao("logado");

// dados passados por formulario e sessao
$pSiape = $_SESSION["sMatricula"];
$cmd    = ($_REQUEST["cmd"] == '' ? $_SESSION['entrada1_cmd_2'] : anti_injection($_REQUEST["cmd"]));
$orig   = ($_REQUEST["orig"] == '' ? $_SESSION['orig'] : anti_injection($_REQUEST["orig"]));

// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();
$sessao_navegacao->initSessaoNavegacao();
$sessao_navegacao->setPagina($_SERVER['REQUEST_URI']);

unset($_SESSION['sDadosParaVerComprovante']);

$title = _SISTEMA_SIGLA_ . ' | Consulta Frequência de Meses Anteriores';

$css = array();

$javascript   = array();
$javascript[] = _DIR_JS_ . 'fc_data.js';
$javascript[] = 'js/jquery.blockUI.js?v2.38';
$javascript[] = 'js/jquery.bgiframe.js';
$javascript[] = 'js/plugins/jquery.dlg.min.js';
$javascript[] = 'js/plugins/jquery.easing.js';
$javascript[] = 'js/jquery.ui.min.js';
$javascript[] = 'entrada8.js?v1.0.0.0.01';

include("html/html-base.php");
include("html/header.php");

include("html/form-entrada-8.php");

include("html/footer.php");

DataBase::fechaConexao();
