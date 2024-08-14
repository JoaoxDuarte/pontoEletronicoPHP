<?php
include_once("config.php");
include_once( "src/controllers/TabFuncaoController.php" );

verifica_permissao("manutencao_funcoes");

$obj = new TabFuncaoController();

switch ($_POST['acaoMetodo'])
{
    case 'incluir':
        $obj->showFormularioIncluir();
        break;

    case 'alterar':
        $obj->showFormularioAlterar();
        break;

    case 'excluir':
        $obj->showLista();
        break;

    default:
        $obj->showLista();
        break;
}
