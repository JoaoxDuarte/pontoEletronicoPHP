<?php

include_once( "config.php" );
include_once( "class_form_delegacao.php" );

verifica_permissao("sRH ou Chefia");

// parametros
$siape  = anti_injection(getNovaMatriculaBySiape($_REQUEST['matricula']));

// pega o nome do arquivo origem
$_SESSION['voltar_nivel_2'] = 'delegacao.php?modo=9';


## classe para montagem do formulario padrao
#
$oForm = new Delegacao();
$oForm->setCaminho('Cadastro » Gerencial » Delegação » Cancelar delegação');
$oForm->setJSDatePicker();
$oForm->setJS('js/jquery.mask.min.js');
$oForm->setJS("delegaato.js");
$oForm->setSeparador(0);

$oForm->setSubTitulo("Cancelamento de Delegação de Competência");

$oForm->setObservacaoTopo("Informe os dados a seguir");

$oForm->setMatricula($siape);

$oForm->exibeFormulario( '10' );
