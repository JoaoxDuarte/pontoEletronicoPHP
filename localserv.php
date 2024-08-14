<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");
include_once( "class_form.siape.php" );

// Verifica se existe um usu�rio logado e se possui permiss�o para este acesso
verifica_permissao('sRH e sTabServidor');

// limpa siape do servidor
// para que o teste de erro de upag
// possa funcionar corretamente;
$_SESSION['sMov_Matricula_Siape'] = "";

// pega o nome do arquivo origem
$pagina_de_origem = pagina_de_origem();


## classe para montagem do formulario padrao
#
$oForm = new formSiape();
$oForm->setCaminho('Cadastro � Movimenta��o � Alterar Localiza��o');
$oForm->setCSS(_DIR_CSS_ . 'estilos.css');
$oForm->setSeparador(30);
$oForm->setOnLoad("javascript:document.all['pSiape'].focus()");

// Topo do formul�rio
//
$oForm->setSubTitulo("Localizar Servidores/Estagi&aacute;rios");

$oForm->setSiapeUsuario($_SESSION['sMatricula']);

$oForm->setSiapeTitulo('Informe a matr�cula do servidor/estagi�rio');
$oForm->setSiapeTituloClass('ft_13_001');
$oForm->setSiapeCaixa('800');
$oForm->setSiapeCaixaBorda('0');
$oForm->setSiapeCaixaWidth('790');

$oForm->setSiapeDestino("localizaservidor.php");
$oForm->exibeForm();
