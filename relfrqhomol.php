<?php

include_once( "config.php" );

verifica_permissao("sRH");

//definindo a competencia de homologacao
$oData = new trata_datasys();
$mes   = $oData->getMesAnterior();
$ano   = $oData->getAnoAnterior();


replaceLink("frequencia_verificar_homologados.php");

/*mensagem("Por favor, utilize a op��o:\\n\\t'Frequ�ncia->RH Atualizar->Verificar Homologa��es'.\\nNeste m�dulo estamos exibindo todas as frequ�ncias indicando as homologadas e n�o homologadas, por unidade.\\nAs unidades est�o listadas � esquerda na vertical", "frequencia_verificar_homologados.php");*/
