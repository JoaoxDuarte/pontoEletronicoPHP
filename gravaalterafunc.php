<?php

// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao('sRH e sTabServidor');

$numfunc   = anti_injection($_POST['numfunc']);
$descricao = anti_injection($_POST['descricao']);
$codigo    = anti_injection($_POST['codigo']);
$upag      = anti_injection($_POST['upag']);
$lot       = anti_injection($_POST['lot']);
$ativo     = anti_injection($_POST['ativo']);
$resp      = anti_injection($_POST['resp']);

// instancia o banco de dados
$oDBase = new DataBase('PDO');

// gravando os dados
$oDBase->query("UPDATE tabfunc SET desc_func='$descricao', cod_funcao='$codigo', upag='$upag', cod_lot='$lot', ativo='$ativo', resp_lot='$resp' WHERE num_funcao = '$numfunc' ");


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho('Tabelas � Fun��es � Manuten��o');
$oForm->setCSS(_DIR_CSS_ . 'estilos.css');
$oForm->setSeparador(0);

$oForm->setSubTitulo("Confirma��o de Grava��o");

// Topo do formul�rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

if ($oDBase->affected_rows() > 0)
{
    registraLog(" alterou os dados da Fun��o $descricao, Codigo $codigo,");
    mensagem("Dados gravados com sucesso!");
}
else
{
    mensagem("Dados N�O foram gravados!");
}

// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();


