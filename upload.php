<?php

include_once("config.php");

$or   = anti_injection($_POST['or']);
$nome = anti_injection($_POST['nome']);
$tipo = anti_injection($_POST['tipo']);

$nome_temporario = $_FILES["Arquivo"]["tmp_name"];
$nome_real       = $nome . $tipo;


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Cadastro � Funcional � Enviar Foto');
$oForm->setSeparador(0);

// Topo do formul�rio
//
$oForm->setSubTitulo("Inclus�o de Servidores e Estagi�rios - Enviar Foto");

// Topo do formul�rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


if (!copy($nome_temporario, _DIR_FOTO_ . $nome_real))
{
    mensagem("Falha ao copiar a foto!\\nPor favor informem ao gestor do SISREF.");
    voltar(1);
}
else
{
    $_SESSION["foto"] = $nome_real;
//		mensagem( "Foto inclu�da com sucesso!\\nClique com o bot�o direito\\ndo mouse para atualizar a p�gina." );
    mensagem("Foto inclu�da com sucesso!.");
    voltar(2);
}

// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
