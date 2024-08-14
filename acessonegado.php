<?php

include_once( "config.php" );

if ($_SESSION['sHOrigem_1'] == 'entrada.php' || $_SESSION['sHOrigem_1'] == 'principal.php')
{
    $destino = ($_SESSION['sModuloPrincipalAcionado'] == 'sogp' ? 'rh' :
                   ($_SESSION['sModuloPrincipalAcionado'] == 'chefia' ? 'chefia' : "entrada")
               ) . ".php";
    destroi_sessao();
    unset($mensagemUsuario);
    $destino = (trim($destino) == '.php' ? 'login' : $destino);
}
else
{
    $destino = anti_injection($_REQUEST['destino']);
}


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Acesso Negado');
$oForm->setLargura("950px");
$oForm->setSeparador(20);

// Topo do formul�rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

mensagem( "Usu�rio sem permiss�o para essa tarefa!", $destino, 1 );

// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
