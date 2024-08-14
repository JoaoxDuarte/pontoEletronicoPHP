<?php

// funcoes de uso geral
include_once( "config.php" );
include_once( "class_form.siape.competencia.php" );

// permissao de acesso
verifica_permissao("sRH ou Chefia");

// limpa siape do servidor
// para que o teste de erro de upag
// possa funcionar corretamente;
$_SESSION['sExc_Matricula_Siape'] = "";


## classe para montagem do formulario padrao
#
$oForm = new formSiapeCompetencia; // instancia o formulário
$oForm->setSoAno(true);
$oForm->setSoMes(true);

// destino e validação
$javascript   = array();
$javascript[] = 'entrada9_individual.js';

$oForm->setSubTitulo("Consultar Compensações do Servidor");

$oForm->setSiapeNome('siape');
$oForm->setSiapeTitulo('Matrícula SIAPE');
$oForm->setSiapeTituloClass('ft_13_001');

$oForm->setInputHidden('saldo', anti_injection($_REQUEST['saldo']));

$oForm->setSiapeCompetenciaDestino("entrada9.php"); // pagina de destino (action)
$oForm->exibeForm();

