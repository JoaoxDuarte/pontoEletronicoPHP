<?php

// conexao ao banco de dados, funcoes diversas
include_once("config.php");
include_once("class_form.frequencia.php");
include_once("class_ocorrencias_grupos.php");
include_once("comparecimento_tabela_auxiliar.php");

verifica_permissao("sRH ou Chefia");

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    // dados enviados por formulario
    $mat           = anti_injection($_REQUEST["mat"]);
    $dia2          = anti_injection($_REQUEST["dia2"]); // dia inicial
    $dia           = $_REQUEST["dia"];  // dia final
    $cmd           = anti_injection($_REQUEST["cmd"]);
    $jnd           = anti_injection($_REQUEST["jnd"]); // jornada de trabalho para o dia
    $compete       = anti_injection($_REQUEST["compete"]);
    $ocor          = anti_injection($_REQUEST["ocor"]);
    $lot           = anti_injection($_REQUEST["lot"]);
    $modo          = anti_injection($_REQUEST["modo"]);
    $dias_no_mes   = anti_injection($_REQUEST["dias_no_mes"]);
    $tipo_acao     = anti_injection($_REQUEST["tipo_acao"]);
    $tipo_inclusao = anti_injection($_REQUEST["tipo_inclusao"]);
}
else
{
    // Valores passados - encriptados
    $dados         = explode(":|:", base64_decode($dadosorigem));
    $mat           = $dados[0];
    $dia2          = $dados[1];
    $dia           = $dados[2];
    $cmd           = $dados[3];
    $jnd           = $dados[4]; // jornada de trabalho para o dia
    $compete       = $dados[5];
    $ocor          = $dados[6];
    $lot           = $dados[7];
    $modo          = $dados[8];
    $dias_no_mes   = $dados[9];
    $tipo_acao     = $dados[10];
    $tipo_inclusao = $dados[7];
}

$mat = getNovaMatriculaBySiape($mat);

// variaveis de trabalho
$mes = substr($compete, 0, 2);
$ano = substr($compete, -4);
$jnd = formata_jornada_para_hhmm($jnd);

$dt_ini = $dia2;
$dt_fim = $dia;
    

// carrega o nome da tabela de trabalho
// se for alteração via módulo histórico
// trás o nome da tabela temporária
$nome_do_arquivo = nomeTabelaFrequencia($tipo_acao, $compete);
    

$pagina_anterior = "frequencia_alterar_periodo.php?dados=" . $_SESSION['voltar_nivel_2'];

//pegando o ip do usuario
$ip         = getIpReal(); //linha que captura o ip do usuario.

// dados armazenados em sessao
$qcinzas    = $_SESSION["qcinzas"];
$sMatricula = $_SESSION["sMatricula"];

//
// VERIFICA SE O ACESSO EH VIA ENTRADA.PHP
//
// Comentado temporariamente por não sabermos, de antemão, os IPs da aplicação
//include_once('ilegal_grava.php');
// instancia o banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setDestino($pagina_anterior);


/* ----------------------------------------------------------*\
  |                                                            |
  |   MODO 10                                                  |
  |   - grava registro de ocorrência pela chefia - por período |
  |                                                            |
  \*---------------------------------------------------------- */


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

// validação
validacao_basica_dados_informados();

$dia1 = $ano . "-" . $mes . "-" . $dt_ini;
$dia2 = $ano . "-" . $mes . "-" . $dt_fim;

settype($dt_ini, "integer");
settype($dt_fim, "integer");

$mensagem_parcial = "";
$mensagem         = "Falha no registro das ocorrências!";

for ($diax = $dt_ini; $diax <= $dt_fim; $diax++)
{
    $dia_alterar = $ano . "-" . $mes . "-" . str_pad($diax, 2, 0, STR_PAD_LEFT);
    
    if (validaData($dia_alterar) == false)
    {
        continue;
    }

    //Implementar busca para saber se já ocorreu o registro de entrada no dia
    $oDBase->setMensagem("Tabela de ponto inexistente!");
    $oDBase->query("
    SELECT
        pto.entra, pto.intini, pto.intsai, pto.sai, pto.oco, pto.idreg,
        IFNULL(pto.matchef,'') AS matchef, IFNULL(pto.siaperh,'') AS siaperh
    FROM
        $nome_do_arquivo AS pto
    WHERE
        pto.siape = :siape
        AND pto.dia = :dia
    ", array(
        array(':siape', $mat, PDO::PARAM_STR),
        array(':dia',   $dia_alterar, PDO::PARAM_STR),
    ));

    $nRows = $oDBase->num_rows();


    $oPonto  = $oDBase->fetch_object();
    $he      = $oPonto->entra;
    $hie     = $oPonto->intini;
    $his     = $oPonto->intsai;
    $hs      = $oPonto->sai;
    $idreg   = $oPonto->idreg;
    $matchef = $oPonto->matchef;
    $siaperh = $oPonto->siaperh;


    ## instancia classe frequencia
    # cálculo das horas trabalhadas
    #
    $oFreq = new formFrequencia;
    $oFreq->setDestino($pagina_anterior);
    $oFreq->setAnoHoje(date('Y'));   // ano (data atual)
    $oFreq->setUsuario($sMatricula); // matricula do usuario logado
    $oFreq->setData($dia_alterar);    // data informada
    $oFreq->setLotacao($lot); // lotação do servidor que se deseja alterar a frequencia
    $oFreq->setSiape($mat);   // matricula do servidor que se deseja alterar a frequencia
    $oFreq->setMes($mes);     // mes que se deseja alterar a frequencia
    $oFreq->setAno($ano);     // ano que se deseja alterar a frequencia
    $oFreq->setNomeDoArquivo($nome_do_arquivo); // nome do arquivo de trabalho, neste caso, o temporario
    $oFreq->loadDadosServidor();
    $oFreq->loadDadosSetor();
    $oFreq->pontoFacultativo();
    $oFreq->verificaSeDiaUtil();


    //if ( !empty($usofruto_ocorrencia_destino->mensagem) ){
    //    mensagem($usofruto_ocorrencia_destino->mensagem, $pagina_anterior);
    //    exit();
    //}


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

    // para registro no histórico da ação ou grupo de registro
    // Ex.: A : ação alteração (chefia/RH)
    //      C : registro chefia
    //      R : registro RH
    $idReg = define_quem_registrou($lot);


    ## definir grupo de registro
    ##

    // estas ações só chefia executa
    $tipos_de_acao = array(
        'acompanhar',
        'acompanhar_ve_ponto',
        'acompanhar_registros',
        'homologar_registros'
    );

    $idregPonto = define_quem_registrou($lot);

    if ($tipo_acao == 'historico_manutencao')
    {
        $idregPonto = 'H';

        $obj = new OcorrenciasGrupos();
        $grupoOcorrenciasViagem = $obj->GrupoOcorrenciasViagem( $oFreq->getSigRegJur() );
        if (in_array($ocor, $grupoOcorrenciasViagem))
        {
            $dif = '00:00';
        }
    }

    $ip_alterou        = ($idregPonto == 'C' ? 'ipch' : 'iprh');
    $mat_alterou       = ($idregPonto == 'C' ? 'matchef' : 'siaperh');
    $alterou_historico = ($tipo_acao == 'historico_manutencao' ? ', acao_executada = :acao_executada' : '');

    #
    ## fim definir grupo de registro

    $oDBase->setMensagem("Falha no registro do ponto! ");

    if ($nRows == 0)
    {
        $mensagem = "Ocorrência(s) registrada(s) com sucesso!";

        if (($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N") || ($_SESSION['sRH'] == "S"))
        {
            $sql = "
            INSERT INTO $nome_do_arquivo
            SET
                dia     = :dia,
                siape   = :siape,
                oco     = :ocor,
                idreg   = :idreg,
                jornp   = :jornp,
                jorndif = :jorndif,
                ip      = '',
                ".$ip_alterou."  = :ip_alterou,
                ".$mat_alterou." = :mat_alterou
                ".$alterou_historico."
            ";
            
            $params = array(
                array(':dia',         $dia_alterar, PDO::PARAM_STR),
                array(':siape',       $mat,         PDO::PARAM_STR),
                array(':ocor',        $ocor,        PDO::PARAM_STR),
                array(':idreg',       $idregPonto,  PDO::PARAM_STR),
                array(':jornp',       $jp,          PDO::PARAM_STR),
                array(':jorndif',     $dif,         PDO::PARAM_STR),
                array(':ip_alterou',  $ip,          PDO::PARAM_STR),
                array(':mat_alterou', $sMatricula,  PDO::PARAM_STR),
            );

            if ($tipo_acao == 'historico_manutencao')
            {
                $params[] = array(':acao_executada', 'S',  PDO::PARAM_STR);
            }
            
            $oDBase->query( $sql, $params );
        }
    }
    else
    {
        $mensagem = "Ocorrência(s) registrada(s) com sucesso!";

        //if ($idreg == 'S' ||
        //    ($idreg == 'A' && $matchef != '' && $_SESSION['sAPS'] == 'S') ||
        //    ($idreg == 'A' && $siaperh != '' && $_SESSION['sRH'] == 'S') ||
        //    (substr_count('C.:.X', $idreg) > 0 && $_SESSION['sAPS'] == 'S') ||
        //    (substr_count('R.:.H.:.X', $idreg) > 0 && $_SESSION['sRH'] == 'S'))
        //{
            //grava os dados anteriores
            gravar_historico_ponto($mat, $dia_alterar, 'A');
            if (($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N") || ($_SESSION['sRH'] == "S"))
            {
                $sql = "
                UPDATE $nome_do_arquivo
                SET
                    dia     = :dia,
                    siape   = :siape,
                    entra   = :entra,
                    intsai  = :intsai,
                    intini  = :intini,
                    sai     = :sai,
                    oco     = :ocor,
                    idreg   = :idreg,
                    jornd   = :jornd,
                    jornp   = :jornp,
                    jorndif = :jorndif,
                    ".$ip_alterou."  = :ip_alterou,
                    ".$mat_alterou." = :mat_alterou
                    ".$alterou_historico."
                WHERE
                    dia = :dia
                    AND siape = :siape
                ";
                
                $params = array(
                    array(':dia',         $dia_alterar, PDO::PARAM_STR),
                    array(':siape',       $mat,         PDO::PARAM_STR),
                    array(':entra',       $he,          PDO::PARAM_STR),
                    array(':intsai',      $his,         PDO::PARAM_STR),
                    array(':intini',      $hie,         PDO::PARAM_STR),
                    array(':sai',         $hs,          PDO::PARAM_STR),
                    array(':ocor',        $ocor,        PDO::PARAM_STR),
                    array(':idreg',       $idregPonto,  PDO::PARAM_STR),
                    array(':jornd',       '00:00',      PDO::PARAM_STR),
                    array(':jornp',       $jp,          PDO::PARAM_STR),
                    array(':jorndif',     $jp,          PDO::PARAM_STR),
                    array(':ip_alterou',  $ip,          PDO::PARAM_STR),
                    array(':mat_alterou', $sMatricula,  PDO::PARAM_STR),
                );

                if ($tipo_acao == 'historico_manutencao')
                {
                    $params[] = array(':acao_executada', 'S',  PDO::PARAM_STR);
                }
            
                $oDBase->query( $sql, $params );
            }
        //}
        //else
        //{
        //    $mensagem_parcial .= ($mensagem_parcial == "" ? "Sucesso parcial no registro das Ocorrência(s)!\\nExiste(m) dias com ocorrências registradas por outro perfil de usuário!\\n\\nAbaixo dias que não foram alterados:\\n" . databarra($dia_alterar) : "\\n" . databarra($dia_alterar));
        //}
    }

    // verifica se há registro de comparecimento
    // a consulta médica ou exame ou GECC
    AjustaSaldoFrequenciaSeConsultaMedicaRegistrada($mat, $dia_alterar);
}

$mensagem = ($mensagem_parcial == '' ? $mensagem : $mensagem_parcial);

switch ($tipo_acao)
{
    case 'homologar_registros':
        mensagem($mensagem, "frequencia_homologar_registros.php?dados=" . $_SESSION['voltar_nivel_1']);
        break;
    
    case 'rh_mes_corrente':
        mensagem($mensagem, "frequencia_rh_mes_corrente_registros.php?dados=" . $_SESSION['voltar_nivel_1']);
        break;
    
    case 'rh_mes_homologacao':
        mensagem($mensagem, "frequencia_rh_mes_homologacao_registros.php?dados=" . $_SESSION['voltar_nivel_1']);
        break;
    
    case 'historico_manutencao':
        mensagem($mensagem, "historico_frequencia_registros.php?dados=" . $_SESSION['voltar_nivel_1']);
        break;
    
    default:
        mensagem($mensagem, $_SESSION['voltar_nivel_1']);
        break;
}



// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();



function validacao_basica_dados_informados()
{
    global $mat, $ocor, $dt_ini, $dt_fim, $pagina_anterior, $dias_no_mes;

    // falta matrícula
    if (empty($mat))
    {
        mensagem("Houve erro no processamento, refaça a operação!", $pagina_anterior);
    }

    if ((soNumeros($ocor) == "") || (soNumeros($dt_ini) == 0) || (strlen(ltrim(trim($dt_ini))) == 1) || (soNumeros($dt_fim) == 0) || (strlen(ltrim(trim($dt_fim))) == 1))
    {
        $mensagem = "";

        // código da ocorrência
        if (soNumeros($ocor) == "")
        {
            $mensagem .= "Selecione uma ocorrência!\\n";
        }

        // data de início da ocorrência
        else if (soNumeros($dt_ini) == 0)
        {
            $mensagem .= "Informe o Dia Início da Ocorrência!\\n";
        }
        else if (strlen(ltrim(trim($dt_ini))) == 1)
        {
            $mensagem .= "Informe o Dia Início da Ocorrência com dois dígitos!\\n";
        }

        // data de término da ocorrência
        else if (soNumeros($dt_fim) == 0)
        {
            $mensagem .= "Informe o Dia Fim da Ocorrência!\\n";
        }
        else if (strlen(ltrim(trim($dt_fim))) == 1)
        {
            $mensagem .= "Informe o Dia Fim da Ocorrência com dois dígitos!\\n";
        }

        mensagem($mensagem, $pagina_anterior);
    }

    if ((soNumeros($dt_fim) > $dias_no_mes) || (soNumeros($dt_ini) == 0) || (soNumeros($dt_ini) > soNumeros($dt_fim)))
    {
        $mensagem = "";

        if (soNumeros($dt_fim) > $dias_no_mes)
        {
            $mensagem .= "Dia Fim da Ocorrência inválido para esse mês!\\n";
        }
        else if (soNumeros($dt_ini) == 0)
        {
            $mensagem .= "Dia Início da Ocorrência inválido!\\n";
        }
        else if (soNumeros($dt_ini) > soNumeros($dt_fim))
        {
            $mensagem .= "Dia Início da Ocorrência MAIOR QUE Dia Fim da Ocorrência!\\n";
        }

        mensagem($mensagem, $pagina_anterior);
    }

}
