<?php

include_once( "config.php" );
include_once( "class_form_delegacao.php" );

verifica_permissao("sRH ou Chefia");

// dados formulario
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    headere("location: acessonegado.php");
}
else
{
    $dados = descriptografa( $dadosorigem );
    $siape = getNovaMatriculaBySiape($dados);
}

$_SESSION['voltar_nivel_2'] = 'delegacao.php?modo=10';


## classe para montagem do formulario padrao
#
$oForm = new Delegacao();
$oForm->setCaminho('Cadastro » Gerencial » Delegação » Incluir delegação » Registro');
$oForm->setJSDatePicker();
$oForm->setJS('js/jquery.mask.min.js');
$oForm->setJS("delegaato.js?v1.0");
$oForm->setSeparador(0);

$oForm->setSubTitulo("Registro de Delegação de Competência");

$oForm->setObservacaoTopo("Informe os Dados abaixo");

$oForm->setMatricula($siape);

$oForm->exibeFormulario( '9' ); // $modo = '9'
