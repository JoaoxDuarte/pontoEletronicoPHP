<?php

include_once( "config.php" );

verifica_permissao("sRH");

//definindo a competencia de homologacao
$oData = new trata_datasys();
$mes   = $oData->getMesAnterior();
$ano   = $oData->getAnoAnterior();


replaceLink("frequencia_verificar_homologados.php");

/*mensagem("Por favor, utilize a opção:\\n\\t'Frequência->RH Atualizar->Verificar Homologações'.\\nNeste módulo estamos exibindo todas as frequências indicando as homologadas e não homologadas, por unidade.\\nAs unidades estão listadas à esquerda na vertical", "frequencia_verificar_homologados.php");*/
