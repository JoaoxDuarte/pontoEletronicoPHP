<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// formulario padrao para siape e competencia
include_once("class_form.competencia.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('estrategica');


## classe para montagem do formulario padrao
#
$oForm = new formCompetencia; // instancia o formulário
$oForm->setJS("pesquisa_ip.js"); // script extras utilizados pelo formulario
$oForm->setCompetenciaDestino("regsalterados.php"); // pagina de destino (action)
$oForm->setCompetenciaValidar("javascript:return validar();"); // script de teste dos dados do formulário e envio (onSubmit)
$oForm->setSeparador(30);

$oForm->setCaminho('Utilitários » Auditoria » Registros alterados » Alteração de frequência'); // localizacao deste formulario
$oForm->setSubTitulo("Consulta Servidores com Registro de Frequência Alterado(s) ou Excluído(s)"); // sub-titulo principal
$oForm->setOnLoad("$('#mes').focus();");

// campos hidden
$oForm->initInputHidden();
$oForm->setInputHidden("an", date('Y')); // ano da   data atual
// definicao de campos
$oForm->setMesNome("mes"); // mes da consulta
$oForm->setMesTitulo('Mês');
$oForm->setAnoNome("ano"); // ano da consulta
$oForm->setAnoTitulo('Ano');


// exibe o formulario
$oForm->exibeForm();
