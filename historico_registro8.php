<?php

// Inicia a sessão e carrega as funções de uso geral
include_once( "config.php" );
include_once( "class_ocorrencias_grupos.php" );
include_once( "class_form.registro.ocorrencia.php" );

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH e sTabServidor');

// testa se o acesso é válido
if (empty($_SESSION['sHOrigem_2']))
{
    mensagem('Acesso inválido!', 'historico_frequencia.php', 1);
}

// grava em sessao dados do script atual
//$_SESSION['sHOrigem_1'] : historico_frequencia.php
//$_SESSION['sHOrigem_2'] : historico_regfreq3.php
$_SESSION['sHOrigem_3'] = ($_SESSION['sHOrigem_3'] == "" ? $_SERVER['REQUEST_URI'] : $_SESSION['sHOrigem_3']); // historico_registro8.php
$_SESSION['sHOrigem_4'] = '';

// Valores passados - encriptados
// Recebe os dados: mat, dia, nome, lot, idreg, c, oco
$dadosorigem = $_REQUEST['dados'];
$dadosorigem = (empty($dadosorigem) ? $_SESSION['sDadosC'] : $dadosorigem);

$dados = explode(":|:", base64_decode($dadosorigem));
$dia   = $dados[0];
$nome  = $dados[1];
$ocor  = $dados[2];

$mat  = $_SESSION['sHSiape'];
$mes  = $_SESSION['sHMes']; // mes
$ano  = $_SESSION['sHAno']; // ano
$comp = $mes . $ano; // competencia (mmaaaa)


$oDBase = selecionaServidor($mat);
$sitcad = $oDBase->fetch_object()->sigregjur;

## ocorrências grupos
$obj = new OcorrenciasGrupos();
$codigosCompensaveis  = $obj->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios=true);
$codigosCredito       = $obj->CodigosCredito($sitcad,$temp=false);
$ocorrenciaLimiteDias = $obj->OcorrenciaLimiteDias($sitcad);


$_SESSION['sHIdReg'] = 'R';
$_SESSION['sHCmd']   = '1';
$_SESSION['sDadosC'] = $dadosorigem;

# monta formulario
#
$oForm = new formRegistraOcorrencia;
$oForm->setDestino($_SESSION['sHOrigem_2']);
$oForm->setHistoryGo(0);
$oForm->setCaminho('Frequência » Atualizar » Histórico » Manutenção » Alterar');
$oForm->setLargura(820);
$oForm->setCSS(_DIR_CSS_ . "estilos.css");
$oForm->setJS("historico_registro8.js");

$oForm->setFormAction("historico_gravaregfreq1.php?modo=7");
$oForm->setFormSubmit("return verificadados()");
$oForm->setSeparador(0);
$oForm->setSeparadorBase(15);

// sub-titulo
$oForm->setSubTitulo("Registro de Ocorr&ecirc;ncia");
//$oForm->setObservacaoBase( "Clique no campo 'Código da Ocorrência', digite parte da descrição da ocorrência e/ou código, em seguida selecione a ocorrência desejada. Exemplo: 00000 normal." );
// dados
$oForm->setSiape($mat);
$oForm->setNome($nome);
$oForm->setData($dia);
$oForm->setCodigoOcorrencia($ocor);
$oForm->loadDadosServidor();
$oForm->loadDadosSetor();

// campos hidden
$oForm->setInputHidden('dados', $dadosorigem);

$oForm->setInputHidden('debitosCompensaveis', implode(',', $codigosCompensaveis));
$oForm->setInputHidden('codigosCreditos',     implode(',', $codigosCredito));
$oForm->setInputHidden('ocorrenciaLimiteDias', implode(',', $ocorrenciaLimiteDias));

// exibe o formulario
$oForm->exibeForm();
