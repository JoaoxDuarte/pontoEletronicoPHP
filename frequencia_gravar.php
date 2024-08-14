<?php

include_once( "config.php" );
include_once( "class_form.frequencia.php" );
include_once( "class_ocorrencias_grupos.php" );
include_once( "comparecimento_tabela_auxiliar.php" );
include_once( "src/controllers/TabBancoDeHorasAcumulosController.php" );
include_once( "src/controllers/TabBancoDeHorasCiclosController.php" );

verifica_permissao("sRH ou Chefia");

//
// VERIFICA SE O ACESSO
//
include_once('ilegal_grava.php');

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    // dados enviados por formulario
    $grupo         = anti_injection($_REQUEST['grupo']);
    $mat           = anti_injection($_REQUEST['mat']);
    $comp          = anti_injection($_REQUEST['compete']);
    $dia           = $_REQUEST['dia'];
    $diac          = conv_data($dia);
    $jnd           = anti_injection($_REQUEST['jnd']); // jornada de trabalho para o dia
    $cmd           = anti_injection($_REQUEST['cmd']);
    $ocor          = anti_injection($_REQUEST['ocor']);
    $lot           = anti_injection($_REQUEST["lot"]);
    $justchef      = utf8_decode(trata_aspas($_REQUEST['justchef']));
    $grupoOperacao = anti_injection($_REQUEST['grupoOperacao']);
    $ocor_origem   = anti_injection($_REQUEST['ocor_origem']);

    $cod_sitcad    = anti_injection($_REQUEST['cod_sitcad']);

    // pega os dados dos registros dos horários
    $he  = anti_injection($_REQUEST['entra']);  // hora de entrada
    $hs  = anti_injection($_REQUEST['saida']);  // hora de saída final
    $hie = anti_injection($_REQUEST['iniint']); // início do intervalo do almoço
    $his = anti_injection($_REQUEST['fimint']); // fim do intrervalo do almoço
}
else
{
    // Valores passados - encriptados
    $dados         = explode(":|:", base64_decode($dadosorigem));
    $grupo         = anti_injection($dados[0]);
    $mat           = anti_injection($dados[1]);
    $comp          = anti_injection($dados[2]);
    $dia           = $dados[3];
    $diac          = conv_data($dia);
    $jnd           = anti_injection($dados[4]); // jornada de trabalho para o dia
    $cmd           = anti_injection($dados[5]);
    $ocor          = anti_injection($dados[6]);
    $he            = anti_injection($dados[7]);  // hora de entrada
    $hie           = anti_injection($dados[8]);  // início do intervalo do almoço
    $his           = anti_injection($dados[9]);  // fim do intrervalo do almoço
    $hs            = anti_injection($dados[10]); // hora de saída final
    $lot           = anti_injection($dados[11]);
    $justchef      = utf8_decode(trata_aspas($dados[12]));
    $grupoOperacao = anti_injection($dados[13]);
    $ocor_origem   = anti_injection($dados[14]);
}

// ATRIBUTOS
$mat               = getNovaMatriculaBySiape($mat);
$sitcad            = getServidorByMatricula($mat)->sigregjur;
$idReg             = ($grupoOperacao == 'historico_manutencao' ? 'H' : define_quem_registrou($lot));

$ip_alterou        = ($idReg == 'C' ? 'ipch' : 'iprh');
$mat_alterou       = ($idReg == 'C' ? 'matchef' : 'siaperh');
$alterou_historico = ($grupoOperacao == 'historico_manutencao' ? ', acao_executada = :acao_executada' : '');

$sMatricula        = $_SESSION["sMatricula"];
$jp                = formata_jornada_para_hhmm($jnd); // jornada de trabalho no formato hh:mm (diária)

$ip                = getIpReal(); //linha que captura o ip do usuario.

$data              = data2arrayBR($dia);
$mes               = dataMes($dia);
$ano               = dataAno($dia);

$_SESSION['justificativa_chefia'] = $justchef;
$_SESSION["dia_processado"]       = inverteData($dia);


// carrega o nome da tabela de trabalho
// se for alteração via módulo histórico
// trás o nome da tabela temporária
$nome_do_arquivo = nomeTabelaFrequencia($grupoOperacao, $comp);


// INSTANCIA CLASSES
$objOcorrenciasGrupos                 = new OcorrenciasGrupos();
$objTabBancoDeHorasCiclosController   = new TabBancoDeHorasCiclosController();
$objTabBancoDeHorasAcumulosController = new TabBancoDeHorasAcumulosController();

// ocorrencias
$grupoOcorrenciasViagem        = $objOcorrenciasGrupos->GrupoOcorrenciasViagem( $sitcad );
$ocorrenciasExigeJustificativa = $objOcorrenciasGrupos->OcorrenciasExigeJustificativa( $sitcad, $idReg );
$codigoServicoExternoPadrao    = $objOcorrenciasGrupos->CodigoServicoExternoPadrao( $sitcad );



/* ************************************************************************** *
 *                                                                            *
 *                               BANCO DE HORAS                               *
 *                                                                            *
 * ************************************************************************** */
//VERIFICA SE A OCORRÊNCIA ORIGEM DO REGISTRO É DO TIPO USUFRUTO
$objDados = new stdClass();
$objDados->siape        = $mat;
$objDados->dia          = $dia;
$objDados->lot          = $lot;
$objDados->ocor_origem  = $ocor_origem;
$objDados->ocor_destino = $ocor;
$objDados->grupo        = $grupoOperacao;

//$usofruto_ocorrencia_destino = $objTabBancoDeHorasAcumulosController->verificaSeOcorrenciaTipoUsufrutoBancoDeHoras( $objDados );
//fimDie(__LINE__, $usofruto_ocorrencia_destino, false, __FILE__ . '<br>' . __FUNCTION__ . ' ' . __CLASS__ . ' ' . __METHOD__);
$usofruto_ocorrencia_destino = new stdClass();
$usofruto_ocorrencia_destino->mensagem = "";


/* ************************************************************************** *
 *                                                                            *
 *                  PROCEDIMENTOS PARA GRAVAÇÃO DA ALTERAÇÃO                  *
 *                                                                            *
 * ************************************************************************** */

// dados voltar
switch ($grupoOperacao)
{
    case 'acompanhar_ve_ponto':
        $pagina_inicial  = $_SESSION['voltar_nivel_2'];
        $pagina_anterior = ($_SESSION['voltar_nivel_4'] == "" ? "frequencia_alterar.php?dados=" . $_SESSION['voltar_nivel_3'] : $_SESSION['voltar_nivel_4']);
        break;

    case 'acompanhar':
        $pagina_inicial  = $_SESSION['voltar_nivel_1'];
        $pagina_anterior = ($_SESSION['voltar_nivel_5'] == "" ? $_SESSION['voltar_nivel_4'] : $_SESSION['voltar_nivel_5']);
        break;

    case 'rh_mes_corrente':
        $pagina_inicial  = "frequencia_rh_mes_corrente_registros.php?dados=" . $_SESSION['voltar_nivel_1'];
        $pagina_anterior = ($_SESSION['voltar_nivel_4'] == "" ? "frequencia_alterar.php?dados=" . $_SESSION['voltar_nivel_3'] : $_SESSION['voltar_nivel_4']);
        break;

    case 'rh_mes_homologacao':
        $pagina_inicial  = "frequencia_rh_mes_homologacao_registros.php?dados=" . $_SESSION['voltar_nivel_1'];
        $pagina_anterior = ($_SESSION['voltar_nivel_4'] == "" ? "frequencia_alterar.php?dados=" . $_SESSION['voltar_nivel_3'] : $_SESSION['voltar_nivel_4']);
        break;

    case "historico_manutencao":
        $pagina_inicial  = "historico_frequencia_registros.php?dados=" . $_SESSION['voltar_nivel_1'];
        $pagina_anterior = ($_SESSION['voltar_nivel_4'] == "" ? "frequencia_alterar.php?dados=" . $_SESSION['voltar_nivel_3'] : $_SESSION['voltar_nivel_4']);
        break;

    default:
        $pagina_inicial  = "frequencia_homologar_registros.php?dados=" . $_SESSION['voltar_nivel_1'];
        $pagina_anterior = ($_SESSION['voltar_nivel_4'] == "" ? "frequencia_alterar.php?dados=" . $_SESSION['voltar_nivel_3'] : $_SESSION['voltar_nivel_4']);
        break;
}


// instancia o banco de dados
$oDBase = new DataBase('PDO');


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


## VALIDAÇÃO DA MATRÍCULA
## VALIDAÇÃO DA COMPETÊNCIA
## VALIDAÇÃO DA DATA
## VALIDAÇÃO DA OCORRÊNCIA
#
// class valida
$validacao = new valida();
$validacao->setDestino( $pagina_anterior );
$validacao->setExibeMensagem( false );

## MATRÍCULA SIAPE
$validacao->siape( $mat );

## DATA
$data = '01/'.substr($comp,0,2).'/'.substr($comp,-4); 
$validacao->data( $data, "- Competência inválida!\\n" );
$validacao->data( $dia, "- Data inválida!\\n" );

if (strtr($ocor,array('-' => '')) === '')
{
    $validacao->setMensagem('- Ocorrência inválida/vazia!\\n');
}

// Exibe mensagem(ns) de erro, se houver
$validacao->exibeMensagem();



## instancia classe frequencia
# cálculo das horas trabalhadas
#
$oFreq = new formFrequencia;
$oFreq->setDestino($pagina_anterior);
$oFreq->setAnoHoje(date('Y'));   // ano (data atual)
$oFreq->setUsuario($sMatricula); // matricula do usuario logado
$oFreq->setData($dia);    // data informada
$oFreq->setLotacao($lot); // lotação do servidor que se deseja alterar a frequencia
$oFreq->setSiape($mat);   // matricula do servidor que se deseja alterar a frequencia
$oFreq->setMes($mes);     // mes que se deseja alterar a frequencia
$oFreq->setAno($ano);     // ano que se deseja alterar a frequencia
$oFreq->setNomeDoArquivo($nome_do_arquivo); // nome do arquivo de trabalho, neste caso, o temporario
$oFreq->loadDadosServidor();
$oFreq->loadDadosSetor();
$oFreq->pontoFacultativo();
$oFreq->verificaSeDiaUtil();


if ( !empty($usofruto_ocorrencia_destino->mensagem) ){
    mensagem($usofruto_ocorrencia_destino->mensagem, $pagina_anterior);
    exit();
}

# ocorrencia
#
$oFreq->setCodigoOcorrencia($ocor);

# horários informados
#
$oFreq->setEntrada($he);
$oFreq->setSaida($hs);
$oFreq->setInicioIntervalo($hie);
$oFreq->setFimIntervalo($his);

$oFreq->setRegistroServidor('N');

// verifica periodo do recesso
$oFreq->verificaPeriodoDoRecesso();

$jp    = $oFreq->getJornada();
$dutil = $oFreq->getDiaUtil();

# valida a ocorrencia e horários informados
#
switch ($grupo)
{
    case 'credito': $oFreq->validaParametros(1);
        break; // 1: jornada realizada maior que a jornada prevista
    case 'debito': $oFreq->validaParametros(2);
        break; // 2: jornada realizada menor que a jornada prevista
    case 'outros':
    default:
        $oFreq->validaParametros(0); // 0: outras ocorrências, sem teste de horários
        break;
}

$jp    = $oFreq->getJornada();
$dutil = $oFreq->getDiaUtil();

##
#  cálculo - horas do dia
#
$oResultado = $oFreq->processaOcorrencias();
//$oco        = $oResultado->ocorrencia;
$jdia       = $oResultado->jornada_realizada;
$jp         = $oResultado->jornada_prevista;
$dif        = $oResultado->jornada_diferenca;

if ($grupoOperacao == 'historico_manutencao' && in_array($ocor, $grupoOcorrenciasViagem))
{
    //$dif = '00:00';
}

// carrega os horários após validação
$he  = $oResultado->entra;
$hs  = $oResultado->sai;
$hie = $oResultado->intini;
$his = $oResultado->intsai;


//Implementar busca para saber se já ocorreu o registro de entrada no dia
$oTbDados = new DataBase('PDO');
$oTbDados->setDestino($_SESSION['voltar_nivel_1']);
$oTbDados->setMensagem("Problemas no acesso ao PONTO.\\nPor favor tente mais tarde.");
$oTbDados->query("
SELECT
    dia, siape, entra, intini, intsai, sai, jornd, jornp, jorndif, oco,
    idreg, ipch, matchef, justchef
FROM $nome_do_arquivo
WHERE
    dia = :dia
    AND siape = :siape
", array(
    array(':dia', $diac, PDO::PARAM_STR),
    array(':siape', $mat, PDO::PARAM_STR),
));
$oPontoAgora = $oTbDados->fetch_object();

if ($oTbDados->num_rows() == 0)
{
    verifica_se_justificativa_obrigatoria();

    $sql = "
    INSERT INTO $nome_do_arquivo
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
        ip2      = '',
        ip3      = '',
        ip4      = '',
        ".$ip_alterou."  = :ip_alterou,
        ".$mat_alterou." = :mat_alterou
        ".$alterou_historico."
    ";
    
    $params =  array(
        array(':dia', $diac, PDO::PARAM_STR),
        array(':siape', $mat, PDO::PARAM_STR),
        array(':entra', $he, PDO::PARAM_STR),
        array(':intini', $hie, PDO::PARAM_STR),
        array(':intsai', $his, PDO::PARAM_STR),
        array(':sai', $hs, PDO::PARAM_STR),
        array(':jornd', $jdia, PDO::PARAM_STR),
        array(':jornp', $jp, PDO::PARAM_STR),
        array(':jorndif', $dif, PDO::PARAM_STR),
        array(':oco', $ocor, PDO::PARAM_STR),
        array(':idreg', $idReg, PDO::PARAM_STR),
        array(':justchef', $justchef, PDO::PARAM_STR),
        array(':ip_alterou',  $ip,          PDO::PARAM_STR),
        array(':mat_alterou', $sMatricula,  PDO::PARAM_STR),
    );

    if ($grupoOperacao == 'historico_manutencao')
    {
        $params[] = array(':acao_executada', 'S',  PDO::PARAM_STR);
    }
    
    $oTbDados->setMensagem("Problemas no acesso ao PONTO (inclusão).\\nPor favor tente mais tarde.");
    $oTbDados->query( $sql, $params );

    if ($oTbDados->affected_rows() == 0)
    {
        mensagem("Ocorrência não foi registrada, por favor\\nverifique os dados e/ou tente outra vez!", $pagina_anterior);
    }
    else
    {
        $_SESSION['justificativa_chefia']   = '';
        $_SESSION['justificativa_servidor'] = '';
        $oTbDados->query("UPDATE usuarios SET recalculo = 'S', refaz_frqano = 'S' WHERE siape = :siape ", array(
            array(':siape', $mat, PDO::PARAM_STR),
        ));
        mensagem("Ocorrência registrada com sucesso!", $pagina_inicial);
    }
}
else
{
    verifica_se_justificativa_obrigatoria(trata_aspas($oPontoAgora->justchef)); // verifica, neste caso, se havia justificativa anterior

    if (in_array($ocor, $codigoServicoExternoPadrao))
    {
        $dif = '00:00';
    }

    //grava os dados anteriores
    if ($nome_do_arquivo != $_SESSION['sHArquivoTemp'])
    {
    gravar_historico_ponto($mat, $diac, 'A');
    }

    $sql = "
    UPDATE " . $nome_do_arquivo . " 
        SET 
            entra = :entra, 
            intini = :intini, 
            intsai = :intsai, 
            sai = :sai, 
            jorndif = :jorndif, 
            jornd = :jornd, 
            jornp = :jornp, 
            oco = :oco, 
            idreg = :idreg, 
            justchef = :justchef, 
            ".$ip_alterou."  = :ip_alterou,
            ".$mat_alterou." = :mat_alterou
            ".$alterou_historico."
    WHERE 
        siape = :siape 
        AND dia = :dia 
    ";
    
    $params = array(
        array(':siape', $mat, PDO::PARAM_STR),
        array(':dia', $diac, PDO::PARAM_STR),
        array(':entra', $he, PDO::PARAM_STR),
        array(':intini', $hie, PDO::PARAM_STR),
        array(':intsai', $his, PDO::PARAM_STR),
        array(':sai', $hs, PDO::PARAM_STR),
        array(':jorndif', $dif, PDO::PARAM_STR),
        array(':jornd', $jdia, PDO::PARAM_STR),
        array(':jornp', $jp, PDO::PARAM_STR),
        array(':oco', $ocor, PDO::PARAM_STR),
        array(':idreg', $idReg, PDO::PARAM_STR),
        array(':justchef', $justchef, PDO::PARAM_STR),
        array(':ip_alterou',  $ip,          PDO::PARAM_STR),
        array(':mat_alterou', $sMatricula,  PDO::PARAM_STR),
    );

    if ($grupoOperacao == 'historico_manutencao')
    {
        $params[] = array(':acao_executada', 'S',  PDO::PARAM_STR);
    }
    
    $oTbDados->setMensagem("Problemas no acesso ao PONTO (alteração).\\nPor favor tente mais tarde.");
    $oTbDados->query( $sql, $params );

    if ($oTbDados->affected_rows() == 0)
    {
        if ($oPontoAgora->entra == $he && $oPontoAgora->intini == $hie && $oPontoAgora->intsai == $his && $oPontoAgora->sai == $hs && 
            $oPontoAgora->jorndif == $dif && $oPontoAgora->jornd == $jdia && $oPontoAgora->jornp == $jp && $oPontoAgora->oco == $ocor && 
            $oPontoAgora->idreg == $idReg && $oPontoAgora->ipch == $ip && $oPontoAgora->matchef == $sMatricula && $oPontoAgora->justchef == $justchef)
        {
            mensagem("Alteração não realizada!\\nOs dados informados já constam na frequência do servidor/estagiário.\\nPor favor verifique as informações (dia,horários,etc.), e tente outra vez!", $pagina_anterior);
        }
        else
        {
            mensagem("Alteração não realizada, por favor\\nverifique os dados e/ou tente outra vez!", $pagina_anterior);
        }
    }
    else
    {
        // verifica se há registro de comparecimento
        // a consulta médica ou exame ou GECC
        AjustaSaldoFrequenciaSeConsultaMedicaRegistrada($mat, $diac);

        $_SESSION['justificativa_chefia']   = '';
        $_SESSION['justificativa_servidor'] = '';
        $oTbDados->query("UPDATE usuarios SET recalculo = 'S', refaz_frqano = 'S' WHERE siape = :siape ", array(
            array(':siape', $mat, PDO::PARAM_STR),
        ));
        mensagem("Alteração realizada com sucesso!", $pagina_inicial);
    }
}


// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();



/*
 * Verifica se a justificativa é obrigatória
 * e/ou se havia justificativa e foi apagada.
 */
function verifica_se_justificativa_obrigatoria($justchef_antes = '')
{
    global $ocor, $ocor_origem, $justchef, $pagina_anterior, $ocorrenciasExigeJustificativa;

    $ocorrencia_exige_justificativa = ($ocor != '' && in_array($ocor,$ocorrenciasExigeJustificativa));
    $justificativa_nao_atende_regra = (trim($justchef) == '' || strlen(trim($justchef)) < 15);
    $justificativa_foi_apagada      = (empty(trim($justchef)) && trata_aspas(trim($justchef_antes)) != '');

    if ($ocorrencia_exige_justificativa && ($justificativa_nao_atende_regra || $justificativa_foi_apagada))
    {
        mensagem("É obrigatório o preenchimento da justificativa da chefia com no mínimo 15 caracteres!", $pagina_anterior);
        exit();
    }

}
