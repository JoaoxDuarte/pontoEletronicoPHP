<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "class_form.siape.php" );

// Verifica se existe um usuário logado e se possui permissão para este acesso
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
$oForm->setCaminho('Cadastro » Gerencial » Manter Histórico Função');
$oForm->setJS(_DIR_JS_ . 'desativa_teclas_f.js');

$oForm->setJS('manutencao_historico_funcao.js');

$oForm->setSeparador(30);
$oForm->setOnLoad("javascript:document.all['pSiape'].focus()");

// Topo do formulário
//
$oForm->setSubTitulo("Manutenção de Histórico de Função");

$oForm->setSiapeUsuario($_SESSION['sMatricula']);

$oForm->setSiapeTitulo('Informe a matrícula do servidor');
$oForm->setSiapeTituloClass('ft_13_001');
$oForm->setSiapeCaixa('800');
$oForm->setSiapeCaixaBorda('0');
$oForm->setSiapeCaixaWidth('790');

$oForm->setSiapeDestino("manutencao_historico_funcao_formulario.php");
$oForm->setSiapeValidar("javascript:return verificadados()");
$oForm->initSiapeOnkeyup();
$oForm->exibeForm();
