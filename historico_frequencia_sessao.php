<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH e sTabServidor');


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setSubTitulo("Hist&oacute;rico - Manuten&ccedil;&atilde;o de Ocorr&ecirc;ncia");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// dados
$ano_hoje = anti_injection($_REQUEST['ano_hoje']);
$usuario  = getNovaMatriculaBySiape( anti_injection($_REQUEST['usuario']) );
$siape    = getNovaMatriculaBySiape( anti_injection($_REQUEST['siape']) );
$mes      = anti_injection($_REQUEST['mes']);
$ano      = anti_injection($_REQUEST['ano']);

$siape_responsavel = getNovaMatriculaBySiape( anti_injection($_REQUEST['siape_responsavel']) );

$urlDestino = 'historico_frequencia.php';



## ############################### ##
##                                 ##
##            VALIDAÇÃO            ##
##                                 ##
## ############################### ##

// mensagem
$mensagem = "";

// class valida
$validar = new valida();
$validar->setDestino( $destino_erro );
$validar->setExibeMensagem( false );

## MATRÍCULA SIAPE
$validar->siape( $usuario, ' do Usuário' );
$validar->siape( $siape, ' da frequência a ser alterada' );
$validar->siape( $siape_responsavel, ' do solicitante' );

if ( !empty($siape) )
{
    $validar->siaperh( $siape );
}

if ( !empty($siape) && !empty($siape_responsavel) )
{
    $validar->siapeResponsavel( $siape, $siape_responsavel );
}

$upag_servidor = getUpag( $siape );

$validar->upagrh( $upag_servidor );
$validar->mes( $mes );
$validar->ano( $ano );

$mensagem = $validar->getMensagem();
if ( !empty($mensagem) )
{
    setMensagemUsuario( $validar->getMensagem(), 'danger' );
}

$mensagem = $validar->getMensagem();

if ( empty($mensagem) )
{
    // nome do arquivo de trabalho
    $_SESSION['sHArquivoTemp'] = 'historico_temp_' . $siape;

    // apaga arquivo temporario, se existir
    $oDBase = new DataBase();
    $oDBase->setMensagem( 'nulo' );
    $oDBase->query( "DROP TABLE IF EXISTS ".$_SESSION['sHArquivoTemp'] );

    // registra o acesso no controle hsitorico
    $oDBase->query( "
    INSERT INTO
        control_historico
    SET
        ip       = :ip,
        siape_rh = :siape_rh,
        lotacao  = :lotacao,
        siape    = :siape,
        compet   = :compet,
        operacao = 'Acessou historico.',
        datahora = NOW()
    ", array(
        array( ':ip',       $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR ),
        array( ':siape_rh', $usuario,                PDO::PARAM_STR ),
        array( ':lotacao',  $_SESSION['sLotacao'],   PDO::PARAM_STR ),
        array( ':siape',    $siape,                  PDO::PARAM_STR ),
        array( ':compet',   $ano.$mes,               PDO::PARAM_STR ),
    ));

    ## instancia o formulario
    #
    $oForm = new formFrequencia();
    $oForm->setSiape( $siape ); // matricula do servidor que se deseja alterar a frequencia
    $oForm->setMes( $mes ); // mes que se deseja alterar a frequencia
    $oForm->setAno( $ano ); // ano que se deseja alterar a frequencia
    $oForm->setNomeDoArquivo( $_SESSION['sHArquivoTemp'] ); // nome do arquivo de trabalho, neste caso, o temporario

    # copia os registros do ponto
    # para o arquivo temporario
    #
    $oForm->copiaPontoParaTemporario();

    $parametros  = base64_encode($ano_hoje.':|:'.$usuario.':|:'.$siape.':|:'.$mes.':|:'.$ano.':|:'.$siape_responsavel);

    $urlDestino = 'historico_frequencia_registros.php?dados=' . $parametros;
}

replaceLink( $urlDestino );
