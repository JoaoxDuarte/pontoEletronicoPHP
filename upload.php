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
$oForm->setCaminho('Cadastro » Funcional » Enviar Foto');
$oForm->setSeparador(0);

// Topo do formulário
//
$oForm->setSubTitulo("Inclusão de Servidores e Estagiários - Enviar Foto");

// Topo do formulário
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
//		mensagem( "Foto incluída com sucesso!\\nClique com o botão direito\\ndo mouse para atualizar a página." );
    mensagem("Foto incluída com sucesso!.");
    voltar(2);
}

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
