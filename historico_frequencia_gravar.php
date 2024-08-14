<?php

include_once("config.php");
include_once("class_ocorrencias_grupos.php");
include_once("class_form.frequencia.php");

verifica_permissao('sRH e sTabServidor');

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    // dados enviados por formulario
    $mat        = $_REQUEST['mat'];
    $dia        = $_REQUEST['dia'];
    $diac       = conv_data($dia);
    $jnd        = $_REQUEST['jnd']; // jornada de trabalho para o dia
    $lot        = $_REQUEST["lot"];
    $cmd        = $_REQUEST['cmd'];
    $ocor       = $_REQUEST['ocor'];
    $comp       = $_REQUEST['compete'];
    $grupo      = $_REQUEST['grupo'];
    $cod_sitcad = $_REQUEST['cod_sitcad'];

    // pega os dados dos registros dos hor�rios
    $he  = $_REQUEST['entra'];  // hora de entrada
    $hs  = $_REQUEST['saida'];  // hora de sa�da final
    $hie = $_REQUEST['iniint']; // in�cio do intervalo do almo�o
    $his = $_REQUEST['fimint']; // fim do intrervalo do almo�o
}
else
{
    // Valores passados - encriptados
    $dados = explode(":|:", base64_decode($dadosorigem));
    $grupo = $dados[0];
    $mat   = $dados[1];
    $comp  = $dados[2];
    $dia   = $dados[3];
    $diac  = conv_data($dia);
    $jnd   = $dados[4]; // jornada de trabalho para o dia
    $cmd   = $dados[5];
    $ocor  = $dados[6];
    $he    = $dados[7];  // hora de entrada
    $hie   = $dados[8];  // in�cio do intervalo do almo�o
    $his   = $dados[9];  // fim do intrervalo do almo�o
    $hs    = $dados[10]; // hora de sa�da final
    $lot   = $dados[11];
}

$sMatricula = $_SESSION["sMatricula"];

$_SESSION["dia_processado"] = inverteData($dia);
;
$jp                         = formata_jornada_para_hhmm($jnd); // jornada de trabalho no formato hh:mm (di�ria)
//pegando o ip do usuario
$ip                         = getIpReal(); //linha que captura o ip do usuario.

$data = data2arrayBR($dia);
$mes  = dataMes($dia);
$ano  = dataAno($dia);

$nome_do_arquivo = $_SESSION['sHArquivoTemp'];


$oDBase = selecionaServidor($mat);
$sitcad = $oDBase->fetch_object()->sigregjur;

// ocorr�ncias grupos
$obj = new OcorrenciasGrupos();
$codigoServicoExternoPadrao = $obj->CodigoServicoExternoPadrao($sitcad);


if ($_SESSION['voltar_nivel_4'] == '')
{
    $pagina_anterior = "historico_frequencia_alterar.php?dados=" . $_SESSION['voltar_nivel_3'];
}
else
{
    $pagina_anterior = $_SESSION['voltar_nivel_4'];
}

//
// VERIFICA SE O ACESSO EH VIA HOST AUTORIZADO
//
// Comentado temporariamente por n�o sabermos, de antem�o, os IPs da aplica��o
//include_once('ilegal_grava.php');
// instancia o banco de dados
$oDBase = new DataBase('PDO');


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Grava��o');
$oForm->setLargura("950px");
$oForm->setSeparador(0);

// Topo do formul�rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();


## instancia classe frequencia
# c�lculo das horas trabalhadas
#
$oFreq = new formFrequencia;
$oFreq->setDestino($pagina_anterior);
$oFreq->setAnoHoje(date('Y'));   // ano (data atual)
$oFreq->setUsuario($sMatricula); // matricula do usuario logado
$oFreq->setData($dia);    // data informada
$oFreq->setLotacao($lot); // lota��o do servidor que se deseja alterar a frequencia
$oFreq->setSiape($mat);   // matricula do servidor que se deseja alterar a frequencia
$oFreq->setMes($mes);     // mes que se deseja alterar a frequencia
$oFreq->setAno($ano);     // ano que se deseja alterar a frequencia
$oFreq->setNomeDoArquivo($nome_do_arquivo); // nome do arquivo de trabalho, neste caso, o temporario
$oFreq->loadDadosServidor();
$oFreq->loadDadosSetor();
$oFreq->pontoFacultativo();
$oFreq->verificaSeDiaUtil();

# ocorrencia
#
$oFreq->setCodigoOcorrencia($ocor);

# hor�rios informados
#
$oFreq->setEntrada($he);
$oFreq->setSaida($hs);
$oFreq->setInicioIntervalo($hie);
$oFreq->setFimIntervalo($his);

$oFreq->setRegistroServidor('N');

// verifica periodo do recesso
$oFreq->verificaPeriodoDoRecesso();

# valida a ocorrencia e hor�rios informados
#
switch ($grupo)
{
    case 'credito': $oFreq->validaParametros(1);
        break; // 1: jornada realizada maior que a jornada prevista
    case 'debito': $oFreq->validaParametros(2);
        break; // 2: jornada realizada menor que a jornada prevista
    case 'outros':
    default:
        $oFreq->validaParametros(0); // 0: outras ocorr�ncias, sem teste de hor�rios
        break;
}

$jp    = $oFreq->getJornada();
$dutil = $oFreq->getDiaUtil();

// calculou as horas do dia enquanto validava parametros - $oFreq->validaParametros(..)
$oco   = $oFreq->getHorasCalculada(0); // c�digo da ocorr�ncia
$jdia  = $oFreq->getHorasCalculada(1); // jornada realizada
$jp    = $oFreq->getHorasCalculada(2); // jornada prevista
$dif   = $oFreq->getHorasCalculada(3); // jornada diferen�a
/*
  // c�lculo da horas do dia
  $oResultado = $oFreq->processaOcorrencias();
  //$oco  = $oResultado->ocorrencia;
  $jdia = $oResultado->jornada_realizada;
  $jp   = $oResultado->jornada_prevista;
  $dif  = $oResultado->jornada_diferenca;

  // carrega os hor�rios ap�s valida��o
  $he  = $oResultado->entra;
  $hs  = $oResultado->sai;
  $hie = $oResultado->intini;
  $his = $oResultado->intsai;
 */
$idReg = 'H';

//Implementar busca para saber se j� ocorreu o registro de entrada no dia
$oTbDados    = new DataBase('PDO');
$oTbDados->setMensagem("Problemas no acesso ao HIST�RICO.\\nPor favor tente mais tarde.");
$oTbDados->query("SELECT dia, siape, entra, intini , intsai, sai, jornd, jornp, jorndif, oco, idreg, iprh, siaperh FROM " . $nome_do_arquivo . " WHERE dia ='$diac' and siape = '$mat' ");
$oPontoAgora = $oTbDados->fetch_object();

if ($oTbDados->num_rows() == 0)
{
    $oTbDados->query("INSERT INTO " . $nome_do_arquivo . " (dia, siape, entra, intini , intsai, sai, jornd, jornp, jorndif, oco, idreg, iprh, siaperh, acao_executada) VALUES ('$diac', '$mat', '$he', '$hie', '$his', '$hs', '$jdia', '$jp', '$dif', '$ocor', '$idReg', '$ip', '$sMatricula', 'I') ");

    if ($oTbDados->affected_rows() == 0)
    {
        mensagem("Ocorr�ncia n�o foi registrada, por favor\\nverifique os dados e/ou tente outra vez!", $pagina_anterior);
    }
    else
    {
        mensagem("Ocorr�ncia registrada com sucesso!", "historico_frequencia_registros.php?dados=" . $_SESSION['voltar_nivel_1']);
    }
}
else
{
    if (in_array($ocor, $codigoServicoExternoPadrao)) //'22222'
    {
        $dif = '00:00';
    }

    $oTbDados->query("UPDATE " . $nome_do_arquivo . " SET entra='$he', intini='$hie', intsai='$his', sai='$hs', jorndif='$dif', jornd='$jdia', jornp='$jp', oco='$ocor', idreg='$idReg', iprh='$ip', siaperh='$sMatricula', acao_executada='A' WHERE siape='$mat' AND dia='$diac' ");

    if ($oTbDados->affected_rows() == 0)
    {
        if ($oPontoAgora->entra == $he && $oPontoAgora->intini == $hie && $oPontoAgora->intsai == $his && $oPontoAgora->sai == $hs && $oPontoAgora->jorndif == $dif && $oPontoAgora->jornd == $jdia && $oPontoAgora->jornp == $jp && $oPontoAgora->oco == $ocor && $oPontoAgora->idreg == $idReg && $oPontoAgora->iprh == $ip && $oPontoAgora->siaperh == $sMatricula)
        {
            mensagem("Altera��o n�o realizada!\\nOs dados informados j� constam na frequ�ncia do servidor/estagi�rio.\\nPor favor verifique as informa��es (dia,hor�rios,etc.), e tente outra vez!", "historico_frequencia_registros.php?dados=" . $_SESSION['voltar_nivel_1']);
        }
        else
        {
            mensagem("Altera��o n�o realizada, por favor\\nverifique os hor�rios e tente outra vez!", $pagina_anterior);
        }
    }
    else
    {
        mensagem("Altera��o realizada com sucesso!", "historico_frequencia_registros.php?dados=" . $_SESSION['voltar_nivel_1']);
    }
    replaceLink($pagina_anterior);
}
