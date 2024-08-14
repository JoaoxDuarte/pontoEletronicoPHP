<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// formulario padrao para siape e competencia
include_once("class_form.siape.competencia.php");
// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('gravar_frequencia');


//$javascript = array();
//$javascript[] = "pesquisa_ip.js";

## classe para montagem do formulario padrao
#

$oForm = new formSiapeCompetencia; // instancia o formulário
$oForm->setJS("js/fc_data.js"); // script extras utilizados pelo formulario
$oForm->setJS("pesquisa_ip.js"); // script extras utilizados pelo formulario
//$oForm->setSiapeCompetenciaDestino("pesquisa_ip_html.php"); // pagina de destino (action)
$oForm->setSiapeCompetenciaValidar("javascript:return validar();"); // script de teste dos dados do formulário e envio (onSubmit)
$oForm->setSeparador(30);

$oForm->setCaminho('Utilitários » Auditoria » Identificar IP'); // localizacao deste formulario
$oForm->setSubTitulo("Consulta IP de Registro de Frequência"); // sub-titulo principal
$oForm->setOnLoad("$('#pSiape').focus();");

// campos hidden
$oForm->initInputHidden();
$oForm->setInputHidden("an", date('Y')); // ano da data atual
// definicao de campos
$oForm->setSiapeNome("pSiape"); // matricula para consulta
$oForm->setMesNome("mes"); // mes da consulta
$oForm->setMesTitulo('');
$oForm->setAnoNome("ano"); // ano da consulta
$oForm->setAnoTitulo('');

// exibe o formulario
$oForm->exibeForm();
