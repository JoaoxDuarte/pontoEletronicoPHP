<?php

include_once("config.php");
include_once("class_form.frequencia.php");
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao('sRH e sTabServidor');

// dados enviados por formulario
$mat  = addslashes($_REQUEST["mat"]);
$dia  = $_REQUEST["dia"];
$diac = conv_data($dia);
$cmd  = addslashes($_REQUEST["cmd"]);
$oco  = addslashes($_REQUEST["oco"]);
$just = trata_aspas($_REQUEST['just']);

$qcinzas    = $_SESSION["qcinzas"];
$sMatricula = $_SESSION["sMatricula"];

$ip = getIpReal(); //linha que captura o ip do usuario.

$nome_do_arquivo = $_SESSION['sHArquivoTemp'];


$oDBase = selecionaServidor($siape);
$sitcad = $oDBase->fetch_object()->sigregjur;

## ocorrências grupos
$obj = new OcorrenciasGrupos();
$grupoOcorrenciasPassiveisDeAbono = $obj->GrupoOcorrenciasPassiveisDeAbono($sitcad, $exige_horarios=true);
$codigoAbonoPadrao                = $obj->CodigoAbonoPadrao($sitcad);


//
// VERIFICA SE O ACESSO EH VIA ENTRADA.PHP
//
// Comentado temporariamente por não sabermos, de antemão, os IPs da aplicação
//include_once('ilegal_grava.php');

// Define as competencias
$mes             = dataMes($dia);
$ano             = dataAno($dia);
$year            = $ano;
$comp            = $mes . $year;

// instancia o banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setMensagem("Falha ao registrar o abono do dia");
$oDBase->setDestino($_SERVER['REQUEST_URI']);

## dados da frequencia do servidor
#
$oDBase->query("
		SELECT
    pto.entra, pto.intini, pto.intsai, pto.sai, pto.oco
		FROM
    " . $nome_do_arquivo . " AS pto
		WHERE
    pto.siape = '$mat'
    AND pto.dia = '$diac'
	");
$oPonto = $oDBase->fetch_object();
$nRows  = $oDBase->num_rows();
$he     = $oPonto->entra;
$hie    = $oPonto->intini;
$his    = $oPonto->intsai;
$hs     = $oPonto->sai;
$oco    = $oPonto->oco;


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Gravação');
$oForm->setCSS(_DIR_CSS_ . 'estiloIE.css');
$oForm->setLargura("950px");
$oForm->setSeparador(0);

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();



## verifica se o código de ocorrência
#  encontra-se entre os permitidos abonar
#
if ($oco == '' || ($oco != '' && in_array($oco, $grupoOcorrenciasPassiveisDeAbono))) //"00172_00129_55555_62010_62012_88888_99999"
{
    ## instancia classe frequencia
    # cálculo das horas trabalhadas
    #
    $oFreq = new formFrequencia();
    $oFreq->setOrigem($_SERVER['REQUEST_URI']); // Registra informacoes em sessao
    $oFreq->setAnoHoje(date('Y'));        // ano (data atual)
    $oFreq->setUsuario($sMatricula);  // matricula do usuario
    $oFreq->setData($dia);        // ano (data atual)
    //$oFreq->setLotacao( $sLotacao );    // lotação do servidor que se deseja alterar a frequencia
    $oFreq->setSiape($mat);    // matricula do servidor que se deseja alterar a frequencia
    $oFreq->setMes($mes); // mes que se deseja alterar a frequencia
    $oFreq->setAno($ano); // ano que se deseja alterar a frequencia
    $oFreq->setNomeDoArquivo($nome_do_arquivo); // nome do arquivo de frequencia
    $oFreq->loadDadosServidor();
    $oFreq->loadDadosSetor();
    $oFreq->pontoFacultativo();
    $oFreq->verificaSeDiaUtil();

    # ocorrencia
    #
    $oFreq->setCodigoOcorrencia( $codigoAbonoPadrao[0] );

    # horários informados
    #
    $oFreq->setEntrada($he);
    $oFreq->setSaida($hs);
    $oFreq->setInicioIntervalo($hie);
    $oFreq->setFimIntervalo($his);

    $oFreq->setRegistroServidor('N');

    # valida a ocorrencia e horários informados
    #
    $oFreq->setDestino($_SERVER['REQUEST_URI']);

    $oFreq->validaParametros(0); // 0: outras ocorrências, sem teste de horários

    $jp    = $oFreq->getJornada();
    $dutil = $oFreq->getDiaUtil();

    // cálculo - horas do dia
    $oResultado = $oFreq->processaOcorrencias();
    //$oco  = $oResultado->ocorrencia;
    $jdia       = $oResultado->jornada_realizada;
    $jp         = $oResultado->jornada_prevista;
    $dif        = $oResultado->jornada_diferenca;

    // carrega os horários após validação
    $he  = $oFreq->getEntrada();
    $hs  = $oFreq->getSaida();
    $hie = $oFreq->getInicioIntervalo();
    $his = $oFreq->getFimIntervalo();

    $ocorr = $codigoAbonoPadrao[0];


    ## Incluindo/alterando dados do dia abonado
    #
    if ($nRows == 0)
    {
        $oDBase->query("INSERT INTO " . $nome_do_arquivo . " (dia, siape,  entra, intini , intsai, sai, jornd, jornp, jorndif, oco, idreg, ip, ip2, ip3, ip4, justchef, iprh, siaperh, acao_executada) VALUES ('$diac', '$mat', '$he', '$hie', '$his', '$hs', '$jdia', '$jp', '00:00', '$ocorr', 'H', '$ip', '', '', '', '', '$just', '$ip', '$sMatricula', 'I') ");
    }
    else
    {
        //grava os dados anteriores
        gravar_historico_ponto($mat, $diac, 'A');

        $oDBase->query("UPDATE " . $nome_do_arquivo . " SET entra='$he', intini='$hie', intsai='$his', sai='$hs', jornd='$jdia', jornp='$jp', jorndif='00:00', oco='$ocor', idreg='H', justchef='$just', iprh='$ip', siaperh='$sMatricula', acao_executada='A' WHERE siape='$mat' AND dia='$diac' ");
    }

    // indica atualiza ficha de frequencia (FRQano)
    $oDBase->query("UPDATE usuarios SET recalculo = 'S', refaz_frqano = 'S' WHERE siape = '$sMatricula' ");

    mensagem("Abono registrado com sucesso!", "historico_frequencia_registros.php?dados=" . $_SESSION['voltar_nivel_1']);
}
else
{
    mensagem("Não é permitido abonar dia com ocorrência diferente de\\n" . implode(', ', $grupoOcorrenciasPassiveisDeAbono) . "!", null, 1);
}
