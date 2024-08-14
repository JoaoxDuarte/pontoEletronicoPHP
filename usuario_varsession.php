<?php

// cria a seção do usuario e identifica suas permissões
$_SESSION["sMatricula"]            = $sMatricula;
$_SESSION["logado"]                = $logado;   // Registra que está logado
$_SESSION["sNome"]                 = $sNome;    // Nome do Servidor
$_SESSION['sIdentificacaoApelido'] = $identificacao_apelido; // identificacao ou apelido
$_SESSION["sSenha"]                = $sSenha;   // Senha de acesso ao aplicativo
$_SESSION["sLotacao"]              = $sLotacao; // Unidade de lotacação do servidor
$_SESSION["sLotacaoDescr"]         = $sLotacaoDescr; // Descrição da Unidade de lotacação do servidor
$_SESSION["sGerencia"]             = $sGerencia;
$_SESSION["sSuperintendencia"]     = $sSuperintendencia;

$_SESSION["sPrivilegio"] = $sPrivilegio;
$_SESSION["orgao"]       = $orgao;    // Órgão de lotaçao, código SIAPE
$_SESSION["uorg"]        = $uorg;     // Unidade organizacional, código SIAPE
$_SESSION["upag"]        = $upag;     // Unidade pagadora, código SIAPE
$_SESSION["regional"]    = $regional; // Regional de vinculação da unidade
$_SESSION["cpf"]         = $sCPF;     // Regional de vinculação da unidade

$_SESSION["delegacao_inicio"] = $delegacao_inicio;
$_SESSION["delegacao_fim"]    = $delegacao_fim;

// Prazos
$_SESSION['sMesi']              = $sMesi;    // Mês e Ano de competencia, referencia para o cronograma do mês em curso
$_SESSION['sMesf']              = $sMesf;    // Sem uso
$_SESSION['sRhi']               = $sRhi;     // Início do período de atuação do RH
$_SESSION['sRhf']               = $sRhf;     // Fim do período autorizado ao RH para manusear aquele mês
$_SESSION['sApsi']              = $sApsi;    // Homologação: data inicial
$_SESSION['sApsf']              = $sApsf;    // Homologação: data final
$_SESSION['sGbnini']            = $sGbnini;  // ???
$_SESSION['sGbninf']            = $sGbninf;  // ???
$_SESSION['sOutchei']           = $sOutchei; // ???
$_SESSION['sOutchef']           = $sOutchef; // ???
$_SESSION['sRmi']               = $sRmi;     // ???
$_SESSION['sRmf']               = $sRmf;     // ???
$_SESSION['sCadi']              = $sCadi;    // ???
$_SESSION['sCadf']              = $sCadf;    // ???
$_SESSION['magico']             = $magico;   // ???
$_SESSION['iniver']             = $iniver;  // Início do horário de verão
$_SESSION['fimver']             = $fimver;   // Fim do horário de verão
$_SESSION['qcinzas']            = $qcinzas;  // Quarta-feira de cinzas
$_SESSION['sPermissoesAcessos'] = $sTripa; // Permissões, acessos

$_SESSION["searchCampo"] = "";

// Permissões
// Guarda em sessão o perfil do usuario
$modulos = $_SESSION['sModulos'];

for ($sn = 0; $sn < count($modulos); $sn++)
{
    $var            = $modulos[$sn]['varsession'];
    $$var           = substr($sTripa, ($modulos[$sn]['cod'] - 1), 1);
    $_SESSION[$var] = $$var;
}
