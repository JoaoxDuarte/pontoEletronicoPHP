<?php

// cria a se��o do usuario e identifica suas permiss�es
$_SESSION["sMatricula"]            = $sMatricula;
$_SESSION["logado"]                = $logado;   // Registra que est� logado
$_SESSION["sNome"]                 = $sNome;    // Nome do Servidor
$_SESSION['sIdentificacaoApelido'] = $identificacao_apelido; // identificacao ou apelido
$_SESSION["sSenha"]                = $sSenha;   // Senha de acesso ao aplicativo
$_SESSION["sLotacao"]              = $sLotacao; // Unidade de lotaca��o do servidor
$_SESSION["sLotacaoDescr"]         = $sLotacaoDescr; // Descri��o da Unidade de lotaca��o do servidor
$_SESSION["sGerencia"]             = $sGerencia;
$_SESSION["sSuperintendencia"]     = $sSuperintendencia;

$_SESSION["sPrivilegio"] = $sPrivilegio;
$_SESSION["orgao"]       = $orgao;    // �rg�o de lota�ao, c�digo SIAPE
$_SESSION["uorg"]        = $uorg;     // Unidade organizacional, c�digo SIAPE
$_SESSION["upag"]        = $upag;     // Unidade pagadora, c�digo SIAPE
$_SESSION["regional"]    = $regional; // Regional de vincula��o da unidade
$_SESSION["cpf"]         = $sCPF;     // Regional de vincula��o da unidade

$_SESSION["delegacao_inicio"] = $delegacao_inicio;
$_SESSION["delegacao_fim"]    = $delegacao_fim;

// Prazos
$_SESSION['sMesi']              = $sMesi;    // M�s e Ano de competencia, referencia para o cronograma do m�s em curso
$_SESSION['sMesf']              = $sMesf;    // Sem uso
$_SESSION['sRhi']               = $sRhi;     // In�cio do per�odo de atua��o do RH
$_SESSION['sRhf']               = $sRhf;     // Fim do per�odo autorizado ao RH para manusear aquele m�s
$_SESSION['sApsi']              = $sApsi;    // Homologa��o: data inicial
$_SESSION['sApsf']              = $sApsf;    // Homologa��o: data final
$_SESSION['sGbnini']            = $sGbnini;  // ???
$_SESSION['sGbninf']            = $sGbninf;  // ???
$_SESSION['sOutchei']           = $sOutchei; // ???
$_SESSION['sOutchef']           = $sOutchef; // ???
$_SESSION['sRmi']               = $sRmi;     // ???
$_SESSION['sRmf']               = $sRmf;     // ???
$_SESSION['sCadi']              = $sCadi;    // ???
$_SESSION['sCadf']              = $sCadf;    // ???
$_SESSION['magico']             = $magico;   // ???
$_SESSION['iniver']             = $iniver;  // In�cio do hor�rio de ver�o
$_SESSION['fimver']             = $fimver;   // Fim do hor�rio de ver�o
$_SESSION['qcinzas']            = $qcinzas;  // Quarta-feira de cinzas
$_SESSION['sPermissoesAcessos'] = $sTripa; // Permiss�es, acessos

$_SESSION["searchCampo"] = "";

// Permiss�es
// Guarda em sess�o o perfil do usuario
$modulos = $_SESSION['sModulos'];

for ($sn = 0; $sn < count($modulos); $sn++)
{
    $var            = $modulos[$sn]['varsession'];
    $$var           = substr($sTripa, ($modulos[$sn]['cod'] - 1), 1);
    $_SESSION[$var] = $$var;
}
