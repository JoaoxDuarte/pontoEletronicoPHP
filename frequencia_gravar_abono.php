<?php

include_once("config.php");
include_once("class_form.frequencia.php");
include_once("class_ocorrencias_grupos.php");

verifica_permissao('sAPS');

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    $dados    = explode(":|:", base64_decode($dadosorigem));
    $mat      = $dados[0];
    $dia      = $dados[1];
    $cmd      = $dados[2];
    $oco      = $dados[3];
    $justchef = trim(trata_aspas($dados[4]));
    $grupo    = $dados[5];
    $diac     = conv_data($dia);
}

$qcinzas    = $_SESSION["qcinzas"];
$sMatricula = $_SESSION["sMatricula"]; // matrícula do servidor logado

$ip         = getIpReal(); //linha que captura o ip do usuario.

// trata a informação de retorno/destino
// conforme a origem homologação/acompanhamento
$destino_retorno = explode('dados=', $_SESSION['voltar_nivel_1']);

switch ($grupo)
{
    case "acompanhar_ve_ponto":
        $grupo            = "acompanhar";
        $destino_retorno  = $_SESSION['voltar_nivel_2'];
        $destino_retorno2 = "frequencia_justificativa_abono.php?dados=" . $_SESSION['voltar_nivel_3'];
        break;
    
    case 'historico_manutencao':
        $destino_retorno  = "historico_frequencia_registros.php:dados=" . $_SESSION['voltar_nivel_1'];
        $destino_retorno2 = "frequencia_justificativa_abono.php?dados=" . $_SESSION['voltar_nivel_2'];
        break;

    default:
        $destino_retorno  = "frequencia_" . $grupo . "_registros.php?dados=" .
            (count($destino_retorno) > 1 ? $destino_retorno[1] : $_SESSION['voltar_nivel_1']);
        $destino_retorno2 = "frequencia_justificativa_abono.php?dados=" . $_SESSION['voltar_nivel_2'];
}


include_once('ilegal_grava.php');


// Verifica se a justificativa foi informada pela chefia
if (empty($justchef) || strlen($justchef) < 15)
{
    mensagem("É obrigatório o preenchimento da justificativa com no mínimo 15 caracteres!", $destino_retorno2);
}

$mat = getNovaMatriculaBySiape($mat);

// Define as competencias
$mes  = dataMes($dia);
$ano  = dataAno($dia);
$year = $ano;
$comp = $mes . $year;

    
// carrega o nome da tabela de trabalho
// se for alteração via módulo histórico
// trás o nome da tabela temporária
$arquivo = nomeTabelaFrequencia($grupo, $comp);
    

// instancia o banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setDestino($destino_retorno);

## dados da frequencia do servidor
#
$oDBase->setMensagem("Falha no acesso a tabela Ponto");
$oDBase->query("
SELECT
    pto.entra, pto.intini, pto.intsai, pto.sai, pto.oco, cad.cod_lot, cad.sigregjur
FROM
    $arquivo AS pto
LEFT JOIN
    servativ AS cad ON pto.siape = cad.mat_siape
WHERE
    pto.siape = :siape
    AND pto.dia = :dia
", array(
    array(':siape', $mat, PDO::PARAM_STR),
    array(':dia', $diac, PDO::PARAM_STR),
));

$oPonto = $oDBase->fetch_object();
$nRows  = $oDBase->num_rows();
$he     = $oPonto->entra;
$hie    = $oPonto->intini;
$his    = $oPonto->intsai;
$hs     = $oPonto->sai;
$oco    = $oPonto->oco;
$lot    = $oPonto->cod_lot;
$sitcad = $oPonto->sigregjur;


$objOcorr = new OcorrenciasGrupos();
$passiveis_de_abono = $objOcorr->GrupoOcorrenciasPassiveisDeAbono($sitcad);
$codigo_de_abono    = $objOcorr->CodigoAbonoPadrao($sitcad);


$dia_nao_util = verifica_se_dia_nao_util($diac, $lot);

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$title = _SISTEMA_SIGLA_ . ' | Grava Abono de Ocorr&ecirc;ncia';

// css extra
$css = array();

// js extra
$javascript = array();

// Topo do formulário
//
$oForm->exibeTopoHTML();



## verifica se o código de ocorrência
#  encontra-se entre os permitidos abonar
#
if (($oco == '' || ($oco != '' && in_array($oco, $passiveis_de_abono))) && $dia_nao_util === false)
{
    ## instancia classe frequencia
    # cálculo das horas trabalhadas
    #
    $oFreq = new formFrequencia();
    $oFreq->setOrigem($destino_retorno); // Registra informacoes em sessao
    $oFreq->setAnoHoje(date('Y'));              // ano (data atual)
    $oFreq->setUsuario($sMatricula);            // matricula do usuario
    $oFreq->setData($dia);                      // ano (data atual)
    //$oFreq->setLotacao( $sLotacao );          // lotação do servidor que se deseja alterar a frequencia
    $oFreq->setSiape($mat);                     // matricula do servidor que se deseja alterar a frequencia
    $oFreq->setMes($mes);                       // mes que se deseja alterar a frequencia
    $oFreq->setAno($ano);                       // ano que se deseja alterar a frequencia
    $oFreq->setNomeDoArquivo($arquivo);  // nome do arquivo de frequencia
    $oFreq->loadDadosServidor();
    $oFreq->loadDadosSetor();
    $oFreq->pontoFacultativo();
    $oFreq->verificaSeDiaUtil();

    # ocorrencia
    #
    $oFreq->setCodigoOcorrencia( $codigo_de_abono[0] );

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

    ## Incluindo/alterando dados do dia abonado
    #
    if ($nRows == 0)
    {

        $oDBase->setMensagem("Falha ao registrar o abono do dia (inclusão)");
        //$oDBase->query("SET GLOBAL sql_mode=''"); // corrige problema com gravação do IP
        $oDBase->query("
        INSERT INTO $arquivo
        SET
            dia      = :dia,
            siape    = :siape,
            entra    = :entra,
            intini   = :intini,
            intsai   = :intsai,
            sai      = :sai,
            jornd    = :jornd,
            jornp    = :jornp,
            jorndif  = :jorndif,
            oco      = :oco,
            idreg    = :idreg,
            justchef = :justchef,
            ip       = '',
            ipch     = :ipch,
            matchef  = :matchef
        ", array(
            array(':dia', $diac, PDO::PARAM_STR),
            array(':siape', $mat, PDO::PARAM_STR),
            array(':entra', $he, PDO::PARAM_STR),
            array(':intini', $hie, PDO::PARAM_STR),
            array(':intsai', $his, PDO::PARAM_STR),
            array(':sai', $hs, PDO::PARAM_STR),
            array(':jornd', $jdia, PDO::PARAM_STR),
            array(':jornp', $jp, PDO::PARAM_STR),
            array(':jorndif', '00:00', PDO::PARAM_STR),
            array(':oco', $codigo_de_abono[0], PDO::PARAM_STR),
            array(':idreg', 'A', PDO::PARAM_STR),
            array(':justchef', $justchef, PDO::PARAM_STR),
            array(':ipch', $ip, PDO::PARAM_STR),
            array(':matchef', $sMatricula, PDO::PARAM_STR),
        ));

    }
    else
    {

        //grava os dados anteriores
        if ($nome_do_arquivo != $_SESSION['sHArquivoTemp'])
        {
            gravar_historico_ponto($mat, $diac, 'A');
        }

        $oDBase->setMensagem("Falha ao registrar o abono do dia (alteração)");
        $oDBase->query("
        UPDATE $arquivo
        SET
            entra    = :entra,
            intini   = :intini,
            intsai   = :intsai,
            sai      = :sai,
            jorndif  = :jorndif,
            oco      = :oco,
            idreg    = :idreg,
            justchef = :justchef,
            ipch     = :ipch,
            matchef  = :matchef
        WHERE
            siape = :siape
            AND dia = :dia
        ", array(
            array(':dia', $diac, PDO::PARAM_STR),
            array(':siape', $mat, PDO::PARAM_STR),
            array(':entra', $he, PDO::PARAM_STR),
            array(':intini', $hie, PDO::PARAM_STR),
            array(':intsai', $his, PDO::PARAM_STR),
            array(':sai', $hs, PDO::PARAM_STR),
            array(':jorndif', '00:00', PDO::PARAM_STR),
            array(':oco', $codigo_de_abono[0], PDO::PARAM_STR),
            array(':idreg', 'A', PDO::PARAM_STR),
            array(':justchef', $justchef, PDO::PARAM_STR),
            array(':ipch', $ip, PDO::PARAM_STR),
            array(':matchef', $sMatricula, PDO::PARAM_STR),
        ));
    }

    // indica atualiza ficha de frequencia (FRQano)
    $oDBase->query("UPDATE usuarios SET recalculo = 'S', refaz_frqano = 'S' WHERE siape = :siape ", array(
        array(':siape', $sMatricula, PDO::PARAM_STR),
    ));

    mensagem("Abono registrado com sucesso!", $destino_retorno);
}
else
{
    $str                = implode(', ', $passiveis_de_abono);
    $passiveis_de_abono = substr_replace($str, " e ", strrpos($str, ", "), 2);
    mensagem("Não é permitido abonar dia(s) não útil(eis) ou com ocorrência diferente de<br>" . $passiveis_de_abono . "!", $destino_retorno);
}


// Base do formulário
//
$oForm->exibeBaseHTML();
