<?php

include_once("config.php");
include_once("class_form.frequencia.php");
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao("logado");

// dado enviado por formulario
$modo = anti_injection($_REQUEST['modo']);

//
// VERIFICA SE O ACESSO EH VIA ENTRADA.PHP
//
// Comentado temporariamente por não sabermos, de antemão, os IPs da aplicação
//include_once('ilegal_grava.php');

// arquivo de origem
$pagina_de_origem = $_SESSION['sHOrigem_3'];

// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();

switch (pagina_de_origem()) {
    case 'registro13.php':
        $pagina_retorno_sucesso = $_SESSION['voltar_nivel_0'];
        $pagina_retorno_erro = $_SESSION['voltar_nivel_3'];
        break;
    case 'veponto3.php':
    case 'entrada4.php':
    case 'regfreq4_form.php':
    case 'regfreq7.php':
    case 'registro8.php':
    case 'registro9.php':
    case 'registro12.php':
    case 'registro14.php':
        $pagina_retorno_sucesso = $_SESSION['voltar_nivel_0'];
        $pagina_retorno_erro = $_SESSION['voltar_nivel_3'];
        break;
    case 'registro15.php':
    default:
        $pagina_retorno_sucesso = ($_SESSION['sPaginaRetorno_sucesso2'] == '' ? $_SESSION['sPaginaRetorno_sucesso1'] : $_SESSION['sPaginaRetorno_sucesso2']);
        $pagina_retorno_erro = ($_SESSION['sPaginaRetorno_sucesso2'] == '' ? $_SESSION['sPaginaRetorno_sucesso1'] : $_SESSION['sPaginaRetorno_sucesso2']);
        break;
}

//linha que captura o ip do usuario.
$ip = getIpReal();

// instancia o banco de dados
$oDBase = new DataBase('PDO');


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setSeparador(0);
$oForm->setLargura("950px");
$oForm->setCaminho('Gravação');

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();


/* -----------------------------------*\
  |                                     |
  |   MODO 1                            |
  |                                     |
  |  Alteração: 08/12/2010              |
  |  Alteração: 11/07/2013              |
  |  Alteração: 08/02/2018              |
  \*----------------------------------- */

if ($modo == "1") {

    ## dados enviados por formulario
    #
    $dia = $_REQUEST['dia'];
    $mat = anti_injection($_REQUEST['mat']);
    $cmd = anti_injection($_REQUEST['cmd']);
    $jnd = anti_injection($_REQUEST['jnd']);
    $ocor = anti_injection($_REQUEST['ocor']);

    $comp = pega_a_competencia($dia); // $_REQUEST['compete'];
    $nome_do_arquivo = 'ponto' . $comp;

    $diac = conv_data($dia);

    ## dados em sessao
    #
    $sMatricula = $_SESSION["sMatricula"];

    if (soNumeros($ocor) == '') {
        mensagem("Selecionar uma ocorrência.", null, 1);
    } elseif (validaData($dia) == false || empty($mat)) {
        mensagem("Matrícula ou Data inválida, refaça a operação.", null, 1);
    } else {
        ## obtendo dados do servidor
        #
        $oDBase->setMensagem("Falha no registro do ponto");
        $oDBase->query("SELECT cad.entra_trab, cad.ini_interv, cad.sai_interv, cad.sai_trab, cad.cod_lot, cad.jornada, IFNULL(pto.idreg,'') AS idreg FROM servativ AS cad LEFT JOIN ponto$comp AS pto ON (cad.mat_siape = pto.siape AND pto.dia = :dia) WHERE mat_siape = :siape ", array(
            array(':siape', $mat, PDO::PARAM_STR),
            array(':dia', $diac, PDO::PARAM_STR),
        ));


        $oServidor = $oDBase->fetch_object();

        $lot = $oServidor->cod_lot;
        $entra = $oServidor->entra_trab;
        $sai = $oServidor->sai_trab;
        $iniin = $oServidor->ini_interv;
        $fimin = $oServidor->sai_interv;
        $idreg = $oServidor->idreg;


        ## instancia classe frequencia
        # cálculo das horas trabalhadas
        #
        $oFreq = new formFrequencia;
        $oFreq->setData($dia); // ano (data atual)
        $oFreq->setAnoHoje(date('Y')); // ano (data atual)
        $oFreq->setUsuario($sMatricula);      // matricula do usuario
        $oFreq->setLotacao($lot);      // lotação do servidor que se deseja alterar a frequencia
        $oFreq->setSiape($mat);        // matricula do servidor que se deseja alterar a frequencia
        $oFreq->setMes(dataMes($dia));          // mes que se deseja alterar a frequencia
        $oFreq->setAno(dataAno($dia));          // ano que se deseja alterar a frequencia
        $oFreq->setNomeDoArquivo($nome_do_arquivo); // nome do arquivo de trabalho, neste caso, o temporario
        $oFreq->loadDadosServidor();
        $oFreq->loadDadosSetor();
        $oFreq->pontoFacultativo();
        $oFreq->verificaSeDiaUtil();

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

        # valida a ocorrencia e horários informados
        # Verificando se a jornada do dia é inferior a jornada prevista (do cargo)
        #
        $oFreq->setDestino($voltar);

        // cálculo - horas do dia
        $oResultado = $oFreq->processaOcorrencias();
        $oco = $ocor; //$oResultado->ocorrencia;
        $jdia = $oResultado->jornada_realizada;
        $jp = $oResultado->jornada_prevista;
        $dif = $oResultado->jornada_diferenca;

        # horários processado
        #
        $he = $oResultado->entra;
        $hs = $oResultado->sai;
        $hie = $oResultado->intini;
        $his = $oResultado->intsai;

        ## Implementar busca para saber se já existe registro de ocorrência neste dia
        #
        $oDBase->setMensagem("Tabela de ponto inexistente ?");
        $oDBase->query("
            SELECT siape
            FROM ponto$comp
            WHERE
                dia = :dia AND siape = :siape
            ", array(
            array(':dia', $diac, PDO::PARAM_STR),
            array(':siape', $mat, PDO::PARAM_STR),
        ));

        if ($oDBase->num_rows() == 0) {
            $oDBase->setMensagem("Falha no registro do ponto");

            $gruposOcorrencias = carregaGruposOcorrencias($mat);
            $ocorrencias_negativas = $gruposOcorrencias['grupoOcorrenciasNegativasDebitos'];
            $servico_externo       = $gruposOcorrencias['codigoServicoExternoPadrao'];

            if (in_array($ocor, $ocorrencias_negativas)) {
                $oDBase->query("INSERT INTO ponto$comp SET dia = :dia, siape = :siape, oco = :oco, jornp = :jornp, jorndif = :jorndif, idreg = :idreg, ipch = :ipch, matchef = :matchef ", array(
                    array(':dia', $diac, PDO::PARAM_STR),
                    array(':siape', $mat, PDO::PARAM_STR),
                    array(':oco', $ocor, PDO::PARAM_STR),
                    array(':jornp', $jp, PDO::PARAM_STR),
                    array(':jorndif', $dif, PDO::PARAM_STR),
                    array(':idreg', 'C', PDO::PARAM_STR),
                    array(':ipch', $ip, PDO::PARAM_STR),
                    array(':matchef', $sMatricula, PDO::PARAM_STR),
                ));
            } elseif (in_array($ocor, $servico_externo)) { //"22222")
                $oDBase->query("INSERT INTO ponto$comp SET dia = :dia, siape = :siape, entra = :entra, intini = :intini, intsai = :intsai, sai = :sai, jornd = :jornd, jornp = :jornp, jorndif = :jorndif, oco = :oco, idreg = :idreg, ipch = :ipch, matchef = :matchef ", array(
                    array(':dia', $diac, PDO::PARAM_STR),
                    array(':siape', $mat, PDO::PARAM_STR),
                    array(':entra', $he, PDO::PARAM_STR),
                    array(':intini', $hie, PDO::PARAM_STR),
                    array(':intsai', $his, PDO::PARAM_STR),
                    array(':sai', $hs, PDO::PARAM_STR),
                    array(':jornd', $jdia, PDO::PARAM_STR),
                    array(':jornp', $jp, PDO::PARAM_STR),
                    array(':jorndif', '00:00', PDO::PARAM_STR),
                    array(':oco', $ocor, PDO::PARAM_STR),
                    array(':idreg', 'C', PDO::PARAM_STR),
                    array(':ipch', $ip, PDO::PARAM_STR),
                    array(':matchef', $sMatricula, PDO::PARAM_STR),
                ));
            } else {
                $oDBase->query("INSERT INTO ponto$comp SET dia = :dia, siape = :siape, oco = :oco, idreg = :idreg, ipch = :ipch, matchef = :matchef ", array(
                    array(':dia', $diac, PDO::PARAM_STR),
                    array(':siape', $mat, PDO::PARAM_STR),
                    array(':oco', $ocor, PDO::PARAM_STR),
                    array(':idreg', 'C', PDO::PARAM_STR),
                    array(':ipch', $ip, PDO::PARAM_STR),
                    array(':matchef', $sMatricula, PDO::PARAM_STR),
                ));
            }

            if ($cmd == "1") {
                mensagem("Ocorrência registrada com sucesso!", $sessao_navegacao->getPaginaPrimeira(), 1);
            } else {
                mensagem("Ocorrência registrada com sucesso!", pagina_de_origem(), 1);
            }
        } else {
            if ($cmd == "1") {
                mensagem("Já existe registro para esse dia, não foi registrada a ocorrencia!", $sessao_navegacao->getPaginaAnterior(), 1);
            } else {
                mensagem("Já existe registro para esse dia, não foi registrada a ocorrencia!", pagina_de_origem(), 1);
            }
        }
    }
} /* -----------------------------------*\
  |                                     |
  |   MODO 2                            |
  |                                     |
  |   - Alterado em 08/02/2018          |
  \*----------------------------------- */
elseif ($modo == "2") //
{

    // dados enviados por formulario
    $dia = $_REQUEST['dia'];
    $mat = anti_injection($_REQUEST['mat']);
    $he = anti_injection($_REQUEST['entra']);
    $hie = anti_injection($_REQUEST['iniint']);
    $his = anti_injection($_REQUEST['fimint']);
    $hs = anti_injection($_REQUEST['hsaida']);
    $jnd = anti_injection($_REQUEST['jnd']);
    $cmd = anti_injection($_REQUEST['cmd']);
    $ocor = anti_injection($_REQUEST['ocor']);

    $comp = pega_a_competencia($dia); // $_REQUEST['compete'];
    $nome_do_arquivo = "ponto" . $comp;

    $diac = conv_data($dia);

    // dados em sessao
    $lot = $_SESSION["sLotacao"];
    $qcinzas = $_SESSION["qcinzas"];
    $sMatricula = $_SESSION["sMatricula"];

    if (soNumeros($ocor) == '') {
        mensagem("Selecionar uma ocorrência.", $pagina_retorno_sucesso, 1);
    } elseif (validaData($dia) == false || empty($mat)) {
        mensagem("Matrícula ou Data inválida, refaça a operação.", $pagina_retorno_sucesso, 1);
    } else {
        ## instancia classe frequencia
        # cálculo das horas trabalhadas
        #
        $oFreq = new formFrequencia;
        $oFreq->setData($dia); // ano (data atual)
        $oFreq->setAnoHoje(date('Y')); // ano (data atual)
        $oFreq->setUsuario($sMatricula); // matricula do usuario
        $oFreq->setLotacao($lot); // lotação do servidor que se deseja alterar a frequencia
        $oFreq->setSiape($mat); // matricula do servidor que se deseja alterar a frequencia
        $oFreq->setMes(dataMes($dia)); // mes que se deseja alterar a frequencia
        $oFreq->setAno(dataAno($dia)); // ano que se deseja alterar a frequencia
        $oFreq->setNomeDoArquivo($nome_do_arquivo); // nome do arquivo de trabalho, neste caso, o temporario
        $oFreq->loadDadosServidor();
        $oFreq->loadDadosSetor();
        $oFreq->pontoFacultativo();
        $oFreq->verificaSeDiaUtil();
        //$oFreq->setJornada( $jnd );
        # ocorrencia
        #
        $oFreq->setCodigoOcorrencia($ocor);

        # horários informados
        #
        $oFreq->setEntrada($he);
        $oFreq->setSaida($hs);
        $oFreq->setInicioIntervalo($hie);
        $oFreq->setFimIntervalo($his);

        # valida a ocorrencia e horários informados
        # Verificando se a jornada do dia é inferior a jornada prevista (do cargo)
        #
        $oFreq->setDestino($pagina_retorno_erro);

        $oFreq->validaParametros(2); // 2: jornada realizada menor que a jornada prevista

        // cálculo - horas do dia
        $oResultado = $oFreq->processaOcorrencias();

        //$ocor = $ocor; //$oResultado->ocorrencia;
        $jdia = $oResultado->jornada_realizada; // horas trabalhadas no dia
        $jp = $oResultado->jornada_prevista;    // jornada prevista para o dia
        $dif = $oResultado->jornada_diferenca;  // diferenca no dia

        # horários processado
        #
        $he = $oResultado->entra;
        $hs = $oResultado->sai;
        $hie = $oResultado->intini;
        $his = $oResultado->intsai;

        ## Implementar busca para saber se já existe registro de ocorrência neste dia
        #
        $oDBase->setMensagem("Falha no registro do ponto.\\nPor favor tente mais tarde.");

        $oDBase->query("SELECT siape FROM ponto$comp WHERE siape = :siape AND dia = :dia ", array(
            array(':dia', $diac, PDO::PARAM_STR),
            array(':siape', $mat, PDO::PARAM_STR),
        ));

        if ($oDBase->num_rows() == 0) {
            $oDBase->query("INSERT INTO ponto$comp (dia, siape, entra, intini , intsai, sai, jornd, jornp, jorndif, oco, idreg, ipch, matchef) VALUES (:dia, :siape, :entra, :intini, :intsai, :sai, :jornd, :jornp, :jorndif, :oco, :idreg, :ipch, :matchef) ", array(
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
                array(':idreg', 'C', PDO::PARAM_STR),
                array(':ipch', $ip, PDO::PARAM_STR),
                array(':matchef', $sMatricula, PDO::PARAM_STR),
            ));
            mensagem("Ocorrência registrada com sucesso!", $pagina_retorno_sucesso, 1);
        } else {
            //grava os dados anteriores
            gravar_historico_ponto($mat, $diac, 'A');

            $oDBase->query("UPDATE ponto$comp SET entra = :entra, intini = :intini, intsai = :intsai, sai = :sai, jorndif = :jornd, jornd = :jornp, jornp = :jorndif, oco = :oco, idreg = :idreg, ipch = :ipch, matchef = :matchef WHERE siape = :siape AND dia = :dia ", array(
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
                array(':idreg', 'A', PDO::PARAM_STR),
                array(':ipch', $ip, PDO::PARAM_STR),
                array(':matchef', $sMatricula, PDO::PARAM_STR),
            ));
            mensagem("Alteração realizada com sucesso!", $pagina_retorno_sucesso, 1);
        }
    }
} /* -----------------------------------*\
  |                                     |
  |   MODO 3                            |
  |                                     |
  |   - Módulo Homologação              |
  |   - Alterado em 08/02/2018          |
  \*----------------------------------- */
elseif ($modo == "3") {

    /* Recebendo as variaveis do formulario */
    $dia = $_REQUEST['dia'];
    $mat = getNovaMatriculaBySiape(anti_injection($_REQUEST['mat']));
    $entra = anti_injection($_REQUEST['entra']);
    $iniint = anti_injection($_REQUEST['iniint']);
    $fimint = anti_injection($_REQUEST['fimint']);
    $hsaida = anti_injection($_REQUEST['hsaida']);
    $jornada_maxima = jornada_maxima_no_dia($_REQUEST['jornada_cargo']);

    $jnd = anti_injection($_REQUEST['jnd']); // jornada de trabalho para o dia
    // testa se a jornada informada
    // esta no formato HH:MM ou se eh
    // um numero inteiro e retorna no formato HH:MM
    $jnc = formata_jornada_para_hhmm($jnd);

    $comp = anti_injection($_REQUEST['compete']);
    $diac = conv_data($dia);
    $cmd = anti_injection($_REQUEST['cmd']);
    $ocor = anti_injection($_REQUEST['ocor']);
    $lot = anti_injection($_SESSION["sLotacao"]);
    $qcinzas = $_SESSION["qcinzas"];
    $sMatricula = anti_injection($_SESSION["sMatricula"]);
    $ano2 = substr($diac, 0, 4);

    $mensagem = "";
    $voltar = ($_SESSION['sPaginaRetorno_sucesso2'] == '' ? $_SESSION['sPaginaRetorno_sucesso'] : $_SESSION['sPaginaRetorno_sucesso2']);

    // pega os dados dos registros dos horários
    $he = $entra; // hora de entrada
    $hs = $hsaida; // hora de saída final
    $hie = $iniint; // início do intervalo do almoço
    $his = $fimint; // fim do intrervalo do almoço
    $jp = $jnc; // jornada de trabalho no formato hh:mm (diária)

    $data = data2arrayBR($dia);
    $mes = $data[1];
    $ano = $data[2];

    $nome_do_arquivo = 'ponto' . $mes . $ano;

    ## instancia classe frequencia
    # cálculo das horas trabalhadas
    #
    $oFreq = new formFrequencia;
    $oFreq->setData($dia); // ano (data atual)
    $oFreq->setAnoHoje(date('Y')); // ano (data atual)
    $oFreq->setUsuario($sMatricula);      // matricula do usuario
    $oFreq->setLotacao($lot);      // lotação do servidor que se deseja alterar a frequencia
    $oFreq->setSiape($mat);        // matricula do servidor que se deseja alterar a frequencia
    $oFreq->setMes($mes);          // mes que se deseja alterar a frequencia
    $oFreq->setAno($ano);          // ano que se deseja alterar a frequencia
    $oFreq->setNomeDoArquivo($nome_do_arquivo); // nome do arquivo de trabalho, neste caso, o temporario
    $oFreq->loadDadosServidor();
    $oFreq->loadDadosSetor();
    $oFreq->pontoFacultativo();
    $oFreq->verificaSeDiaUtil();
    //$oFreq->setJornada( $jnd );
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

    # valida a ocorrencia e horários informados
    # Verificando se a jornada do dia é inferior a jornada prevista (do cargo)
    #
    $oFreq->setDestino($voltar);

    $oFreq->validaParametros(1); // 1: jornada realizada maior que a jornada prevista

    $jp = $oFreq->getJornada();
    $dutil = $oFreq->getDiaUtil();

    // cálculo - horas do dia
    $oResultado = $oFreq->processaOcorrencias();
    $oco = $ocor; //$oResultado->ocorrencia;
    $jdia = $oResultado->jornada_realizada;
    $jp = $oResultado->jornada_prevista;
    $dif = $oResultado->jornada_diferenca;

    # horários processado
    #
    $he = $oResultado->entra;
    $hs = $oResultado->sai;
    $hie = $oResultado->intini;
    $his = $oResultado->intsai;

    //Implementar busca para saber se já ocorreu o registro de entrada no dia
    $oDBase->setMensagem("Problemas no acesso ao PONTO.\\nPor favor tente mais tarde.");

    $oDBase->query("SELECT entra, intini, intsai, sai, jornd, jornp, jorndif, oco, idreg, ip, ip2, ip3, ip4, ipch, iprh, matchef, siaperh FROM ponto$comp USE INDEX (siape) WHERE dia = :dia AND siape = :siape ", array(
        array(':dia', $diac, PDO::PARAM_STR),
        array(':siape', $mat, PDO::PARAM_STR),
    ));

    $rows = $oDBase->num_rows();
    $oPonto = $oDBase->fetch_object();
    $hentra = $oPonto->entra;
    $hintini = $oPonto->intini;
    $hintsai = $oPonto->intsai;
    $hsai = $oPonto->sai;
    $hjornd = $oPonto->jornd;
    $hjornp = $oPonto->jornp;
    $hjorndif = $oPonto->jorndif;
    $hoco = $oPonto->oco;
    $hidreg = $oPonto->idreg;
    $hip1 = $oPonto->ip;
    $hip2 = $oPonto->ip2;
    $hip3 = $oPonto->ip3;
    $hip4 = $oPonto->ip4;
    $hipch = $oPonto->ipch;
    $hiprh = $oPonto->iprh;
    $hmatchef = $oPonto->matchef;
    $hsiaperh = $oPonto->siaperh;
    $vHoras = strftime("%H:%M:%S", time());
    $vDatas = date("Y-m-d");

    $idReg = define_quem_registrou($lot);

    if ($rows == 0) {
        $oDBase->query("INSERT INTO ponto$comp SET entra= :entra, intini = :intini, intsai = :intsai, sai = :sai, jorndif = :jorndif, jornd = :jornd, jornp = :jornp, oco = :oco, idreg = :idreg, ipch = :ipch, matchef = :matchef, dia = :dia, siape = :siape", array(
            array(':dia', $diac, PDO::PARAM_STR),
            array(':siape', $mat, PDO::PARAM_STR),
            array(':entra', $he, PDO::PARAM_STR),
            array(':intini', $hie, PDO::PARAM_STR),
            array(':intsai', $his, PDO::PARAM_STR),
            array(':sai', $hs, PDO::PARAM_STR),
            array(':jorndif', $dif, PDO::PARAM_STR),
            array(':jornd', $jdia, PDO::PARAM_STR),
            array(':jornp', $jp, PDO::PARAM_STR),
            array(':oco', $ocor, PDO::PARAM_STR),
            array(':idreg', $idReg, PDO::PARAM_STR),
            array(':ipch', $ip, PDO::PARAM_STR),
            array(':matchef', $sMatricula, PDO::PARAM_STR)
        ));
        $mensagem = "Ocorrência registrada com sucesso!";
    } else {
        //grava os dados anteriores
        gravar_historico_ponto($mat, $diac, 'A');

        $oDBase->query("UPDATE ponto$comp SET entra = :entra, intini = :intini, intsai = :intsai, sai = :sai, jorndif = :jorndif, jornd = :jornd, jornp = :jornp, oco = :oco, idreg = :idreg, ipch = :ipch, matchef = :matchef, dia = :dia, siape = :siape WHERE siape = :siape AND dia = :dia ", array(
            array(':dia', $diac, PDO::PARAM_STR),
            array(':siape', $mat, PDO::PARAM_STR),
            array(':entra', $he, PDO::PARAM_STR),
            array(':intini', $hie, PDO::PARAM_STR),
            array(':intsai', $his, PDO::PARAM_STR),
            array(':sai', $hs, PDO::PARAM_STR),
            array(':jorndif', $dif, PDO::PARAM_STR),
            array(':jornd', $jdia, PDO::PARAM_STR),
            array(':jornp', $jp, PDO::PARAM_STR),
            array(':oco', $ocor, PDO::PARAM_STR),
            array(':idreg', $idReg, PDO::PARAM_STR),
            array(':ipch', $ip, PDO::PARAM_STR),
            array(':matchef', $sMatricula, PDO::PARAM_STR)
        ));
        $mensagem = "Alteração realizada com sucesso!";
    }

    if ($oDBase->affected_rows() == -1) {
        $mensagem = "Ocorrência não foi registrada, por favor\\nverifique os dados e/ou tente outra vez!";
        $voltar = $_SESSION['sPaginaRetorno_erro'];
    }

    if ($mensagem != '') {
        mensagem($mensagem, $voltar, 1);
    } else {
        voltar(1, $voltar);
    }
} /* -----------------------------------*\
  |                                     |
  |   MODO 4                            |
  |                                     |
  |   - Alterado em 19/12/2011          |
  |   - Alterado em 15/02/2013          |
  |   - Alterado em 08/02/2018          |
  \*----------------------------------- */
elseif ($modo == "4") {
    $cmd = anti_injection($_REQUEST["cmd"]);
    $compete = anti_injection($_REQUEST["compete"]);
    $mat = anti_injection($_REQUEST["mat"]);
    $jnd = anti_injection($_REQUEST["jnd"]);
    $ocor = anti_injection($_REQUEST["ocor"]);
    $dia2 = $_REQUEST["dia2"];
    $dia = $_REQUEST["dia"];
    $lot = anti_injection($_REQUEST["lot"]);
    $dt_ini = $_REQUEST["dt_ini"];
    $dt_fim = $_REQUEST["dt_fim"];
    $qcinzas = $_SESSION["qcinzas"];
    $sMatricula = anti_injection($_SESSION["sMatricula"]);

    $dia1 = conv_data($dia2);
    $dia2 = conv_data($dia);
    //$jnd = "0".$jnd.":00";

    $jnd = formata_jornada_para_hhmm($jnd);

    // verifica se a competencia em homologacao e a mesma da inclusao
    $mes = substr($dia1, 5, 2);
    $ano = substr($dia1, 0, 4);
    $comp = $mes . $ano;

    $mes2 = substr($dia2, 5, 2);
    $ano2 = substr($dia2, 0, 4);
    $comp2 = $mes2 . $ano2;

    // Define competencia conforme o período corrente ou homologação
    if ($cmd == "2") {
        $anoh = date(Y);

        if (date(n) == "1") {
            $mesh = "12";
            $yearh = $anoh - 1;
            $compete = $mesh . $yearh;
        }

        if (date(n) < "11" && date(n) != "1") {
            $mesh = "0" . (date(n) - 1);
            $yearh = $anoh;
            $compete = $mesh . $yearh;
        }

        if (date(n) > "10") {
            $mesh = date(n) - 1;
            $yearh = $anoh;
            $compete = $mesh . $yearh;
        }
    }

    //
    //testa se o dia é da competencia atual
    if (($mes != date('m') || $ano != date('Y') || $mes2 != date('m') || $ano2 != date('Y')) && $cmd == 1) {
        mensagem("Não é permitido inserir dia de competência diferente da competência atual!", 'regfreq4_entra.php', 1);
    } elseif (($mes != $mesh || $ano != $yearh || $mes2 != $mesh || $ano2 != $yearh) && $cmd != 1) {
        mensagem("Não é permitido inserir dia de competência diferente da competência de homologação!", 'regfreq4_entra.php', 1);
    } else {
        //obtendo dados do servidor
        $oDBase->query("SELECT entra_trab, ini_interv, sai_interv, sai_trab, cod_lot, jornada FROM servativ WHERE mat_siape = :siape ", array(
            array(':siape', $mat, PDO::PARAM_STR),
        ));

        $oServativ = $oDBase->fetch_object();
        $entra = $oServativ->entra_trab;
        $sai = $oServativ->sai_trab;
        $iniin = $oServativ->ini_interv;
        $fimin = $oServativ->sai_interv;

        $jnd = $oServativ->jornada;
        $jor = $oServativ->jornada / 5;
        $j = '0' . $jor . ':00:00';

        $jnd = formata_jornada_para_hhmm($jnd);
        $j = formata_jornada_para_hhmm($jor);

        //Implementar busca para saber se já ocorreu o registro de entrada no dia
        $oDBase->setMensagem("Tabela de ponto inexistente");
        $oDBase->query("SELECT * FROM ponto$compete WHERE siape = :siape AND (dia >= :dia1 AND dia <= :dia2) ", array(
            array(':siape', $mat, PDO::PARAM_STR),
            array(':dia1', $dia1, PDO::PARAM_STR),
            array(':dia2', $dia2, PDO::PARAM_STR)
        ));
        $rows = $oDBase->num_rows();

        if ($rows == 0) {
            $oDBase->setMensagem("Falha no registro do ponto");

            $gruposOcorrencias = carregaGruposOcorrencias($mat);
            $ocorrencias_negativas = $gruposOcorrencias['grupoOcorrenciasNegativasDebitos'];
            $servico_externo       = $gruposOcorrencias['codigoServicoExternoPadrao'];

            if (in_array($ocor, $ocorrencias_negativas)) {
                for ($dia = $dia1; $dia <= $dia2; $dia++) {
                    //verifica se quarta-feira de cinzas ou dia 24/12 ou 31/12
                    $jnd = ponto_facultativo($dia, $jnd, $ano2, $entra, $sai, $iniin, $fimin);
                    $oDBase->query("INSERT INTO ponto$compete SET dia = :dia, siape = :siape, oco = :oco, jornp = :jornp, jorndif = :jorndif, idreg = :idreg, ipch = :ipch, matchef = :matchef ", array(
                        array(':dia', $dia, PDO::PARAM_STR),
                        array(':siape', $mat, PDO::PARAM_STR),
                        array(':oco', $ocor, PDO::PARAM_STR),
                        array(':jornp', $jnd, PDO::PARAM_STR),
                        array(':jorndif', $jndf, PDO::PARAM_STR),
                        array(':idreg', 'C', PDO::PARAM_STR),
                        array(':ipch', $ip, PDO::PARAM_STR),
                        array(':matchef', $sMatricula, PDO::PARAM_STR)
                    ));
                }
            } elseif (in_array($ocor, $servico_externo)) { //"22222"
                for ($dia = $dia1; $dia <= $dia2; $dia++) {
                    //verifica se quarta-feira de cinzas ou dia 24/12 ou 31/12
                    $jnd = ponto_facultativo($dia, $jnd, $ano2, $entra, $sai, $iniin, $fimin);
                    $oDBase->query("INSERT INTO ponto$compete SET siape = :siape, dia = :dia, entra = :entra, intini = :intini, intsai = :intsai, sai = :sai, jornd = :jornd, jornp = :jornp, jorndif = :jorndif, oco = :oco, idreg = :idreg, ipch = :ipch, matchef = :matchef ", array(
                        array(':siape', $mat, PDO::PARAM_STR),
                        array(':dia', $diac, PDO::PARAM_STR),
                        array(':entra', $entra, PDO::PARAM_STR),
                        array(':intini', $iniin, PDO::PARAM_STR),
                        array(':intsai', $fimin, PDO::PARAM_STR),
                        array(':sai', $sai, PDO::PARAM_STR),
                        array(':jornd', $jnd, PDO::PARAM_STR),
                        array(':jornp', $jnd, PDO::PARAM_STR),
                        array(':jorndif', '00:00', PDO::PARAM_STR),
                        array(':oco', $ocor, PDO::PARAM_STR),
                        array(':idreg', 'C', PDO::PARAM_STR),
                        array(':ipch', $ip, PDO::PARAM_STR),
                        array(':matchef', $sMatricula, PDO::PARAM_STR)
                    ));
                }
            } else {
                for ($dia = $dia1; $dia <= $dia2; $dia++) {
                    $oDBase->query("INSERT INTO ponto$compete SET dia = :dia, siape = :siape, oco = :oco, idreg = :idreg, ipch = :ipch, matchef = :matchef ", array(
                        array(':dia', $dia, PDO::PARAM_STR),
                        array(':siape', $mat, PDO::PARAM_STR),
                        array(':oco', $ocor, PDO::PARAM_STR),
                        array(':idreg', 'C', PDO::PARAM_STR),
                        array(':ipch', $ip, PDO::PARAM_STR),
                        array(':matchef', $sMatricula, PDO::PARAM_STR),
                    ));
                }
            }

            mensagem("Ocorrência registrada com sucesso!", "regfreq4_entra.php", 1);
        } else {
            mensagem("Existe registro que coincide com o período informado, verifique!", "regfreq4_entra.php", 1);
        }
    }
} /* -----------------------------------*\
  |                                     |
  |   MODO 5                            |
  |                                     |
  |   - Alterado em 08/02/2018          |
  \*----------------------------------- */
elseif ($modo == "5")//&& $lot != $_SESSION['sLotacao']
{
    // Valores passados - encriptados
    // Recebe os dados: mat, dia, nome, lot, idreg, c, oco
    $dadosorigem = $_REQUEST['dados'];

    if (empty($dadosorigem)) {
        $comp = anti_injection($_REQUEST["comp"]);
        $mat = anti_injection($_REQUEST["mat"]);
        $ocor = anti_injection($_REQUEST["oco"]);
        $dia = $_REQUEST["dia"];
        $lot = anti_injection($_REQUEST["lot"]);
        $sit = anti_injection($_REQUEST["sit"]);

        //testa secompetencia solicitada é anterior à da homologação
        //Define Competênciada homologação.
        $data = new trata_datasys();
        $anot = $data->getAnoHomologacao();
        $compt = $data->getMesHomologacao();
        $mest = $data->getMes();
    } else {
        /* Recebendo as variaveis do formulario */
        $dados = explode(":|:", base64_decode($dadosorigem));
        $comp = $dados[0];
        $mat = $dados[1];
        $dia = conv_data($dados[2]);

        $anot = substr($dia, 0, 4);
        $compt = substr($dia, 5, 2);
        $mest = substr($dia, 5, 2);
    }

    $sMatricula = $_SESSION["sMatricula"];

    $ano = substr($dia, 0, 4);
    $mes = substr($dia, 5, 2);
    $diat = substr($dia, 8, 2);

    if (("$ano-$mes-$diat" < "$anot-$compt-01") && ($dia != "0000-00-00")) {
        $sLotacao = $_SESSION["sLotacao"];
        $sMatricula = $_SESSION["sMatricula"];
        $vHoras = strftime("%H:%M:%S", time());
        $vDatas = date("Y-m-d");

        $oDBase->setMensagem("Falha no registro da operação");
        $oDBase->setDestino($pagina_de_origem);
        $oDBase->query("INSERT INTO ilegal (siape, operacao, datag, hora, maquina, setor) VALUES ('$sMatricula','Tentativa de excluir o dia $dia da ficha do servidor $mat pertencente a competências anteriores à homologação por alteração de endereço no browser.','$vDatas', '$vHoras','$ip', '$sLotacao') ");
        $oDBase->query("INSERT INTO ilegal SET siape = :siape, operacao = :operacao, datag = :datag, hora = :hora, maquina = :maquina, setor = :setor ", array(
            array(':siape', $sMatricula, PDO::PARAM_STR),
            array(':operacao', "Tentativa de excluir o dia " . $dia . " da ficha do servidor " . $mat . " pertencente a competências anteriores à homologação por alteração de endereço no browser.", PDO::PARAM_STR),
            array(':datag', $vDatas, PDO::PARAM_STR),
            array(':hora', $vHoras, PDO::PARAM_STR),
            array(':maquina', $ip, PDO::PARAM_STR),
            array(':setor', $sLotacao, PDO::PARAM_STR),
        ));
        mensagem("Você não tem permissão para essa tarefa!", null, 1);
        exit();
    } else {
        $idreg = define_quem_registrou();

        $oDBase->setMensagem("Falha no registro do ponto");
        $oDBase->query("SELECT entra, intini, intsai, sai, jornd, jornp, jorndif, oco, idreg, ip, ip2, ip3, ip4, ipch, iprh, matchef, siaperh FROM ponto$comp WHERE siape = :siape AND dia = :dia ", array(
            array(':siape', $mat, PDO::PARAM_STR),
            array(':dia', $dia, PDO::PARAM_STR)
        ));

        $rows = $oDBase->num_rows();

        $oPonto = $oDBase->fetch_object();

        $hentra = $oPonto->entra;
        $hintini = $oPonto->intini;
        $hintsai = $oPonto->intsai;
        $hsai = $oPonto->sai;
        $hjornd = $oPonto->jornd;
        $hjornp = $oPonto->jornp;
        $hjorndif = $oPonto->jorndif;
        $hoco = $oPonto->oco;
        $hidreg = $oPonto->idreg;
        $hip1 = $oPonto->ip;
        $hip2 = $oPonto->ip2;
        $hip3 = $oPonto->ip3;
        $hip4 = $oPonto->ip4;
        $hipch = $oPonto->ipch;
        $hiprh = $oPonto->iprh;
        $hmatchef = $oPonto->matchef;
        $hsiaperh = $oPonto->siaperh;
        $vHoras = strftime("%H:%M:%S", time());
        $vDatas = date("Y-m-d");

        //grava os dados anteriores
        gravar_historico_ponto($mat, $dia, 'E');

        $oDBase->setMensagem("Erro na exclusão da ocorrência");
        $oDBase->query("DELETE FROM ponto$comp WHERE dia = :dia AND siape = :siape ", array(
            array(':dia', $dia, PDO::PARAM_STR),
            array(':siape', $mat, PDO::PARAM_STR),
        ));

        $rows = $oDBase->affected_rows();

        if ($rows == 0) {
            mensagem("Erro na exclusão da ocorrência, por favor tente outra vez!", 'freqexclui.php', 1);
        } else {
            mensagem("Ocorrência excluída com sucesso!", 'freqexclui.php', 1);
        }
    }
} /* -----------------------------------*\
  |                                     |
  |   MODO 6                            |
  |   - exclui registro de um dia -     |
  |                                     |
  |   - Alterado em 08/02/2018          |
  \*----------------------------------- */
elseif ($modo == "6") {

    // dados enviados por formulario
    $comp = anti_injection($_REQUEST["comp"]);
    $mat = anti_injection($_REQUEST["mat"]);
    $sit = anti_injection($_REQUEST["sit"]);

    // dados em sessao
    $sMatricula = $_SESSION["sMatricula"];

    $idreg = define_quem_registrou();

    if (isset($_REQUEST["c"])) {
        $total_linhas = count($_REQUEST["c"]);

        for ($i = 0; $i < $total_linhas; $i++) {
            $oDBase->setMensagem("Erro na exclusão da ocorrência");

            //grava os dados anteriores em historico
            gravar_historico_ponto($mat, $_REQUEST["c"][$i], 'E');

            if ($total_linhas == 1 || $_SESSION['sRH'] == "S") {
                $oDBase->query("DELETE FROM ponto$comp WHERE dia = :dia AND siape = :siape ", array(
                    array(':dia', $_REQUEST["c"][$i], PDO::PARAM_STR),
                    array(':siape', $mat, PDO::PARAM_STR)
                ));
            } else {
                $oDBase->query("DELETE FROM ponto$comp WHERE dia = :dia AND siape = :siape AND idreg = :idreg ", array(
                    array(':dia', $_REQUEST["c"][$i], PDO::PARAM_STR),
                    array(':siape', $mat, PDO::PARAM_STR),
                    array(':idreg', $idreg, PDO::PARAM_STR),
                ));
            }
        }

        $pagina_de_origem = (empty($_SESSION['sHOrigem_4']) ? pagina_de_origem() : $_SESSION['sHOrigem_4']);

        if ($total_linhas > 1 || $rows == 0) {
            mensagem("Você assinalou ocorrências incluídas por diversos usuários.\\nSomente as registradas pelo mesmo perfil de usuários foram excluídas!", $pagina_de_origem, 1);
        } else {
            mensagem("Ocorrência(s) excluída(s) com sucesso!", $pagina_de_origem, 1);
        }
    }
}

/* ---------------------------------------*\
  |                                         |
  |   MODO 7                                |
  |   - grava registro devolução a chefia - |
  |                                         |
  |   - Alterado em 08/02/2018              |
  \*--------------------------------------- */
elseif ($modo == "7") {
    // dados enviados por formulario
    $siape = anti_injection($_REQUEST["siape"]);
    $motivo = trata_aspas($_REQUEST["motivo"]);
    $comp = anti_injection($_REQUEST["comp"]);

    // dados em sessão
    $matrh = $_SESSION['sMatricula'];
    $sMatricula = $_SESSION["sMatricula"];

    // data atual - formato americano
    $oData = new trata_datasys();
    $ano = $oData->getAnoHomologacao();
    $mes = $oData->getMesHomologacao();

    $data_desomologacao = date("Y-m-d");

    // pagina de origem
    $pagina_de_origem = pagina_de_origem();

    // banco de dados
    $oDBase->setDestino($pagina_de_origem);

    //obtendo dados do servidor
    $oDBase->setMensagem("Tabela de servidores inexistente");
    $oDBase->query("
    SELECT
        cad.mat_siape, cad.nome_serv, cad.cod_lot, cad.jornada, cad.freqh,
        und.upag, und.descricao, und.uorg_pai, cad.chefia,
        IF(IFNULL(hom.homologado,'N')='N' OR hom.homologado NOT IN ('V','S'),'N','S') AS homologado
    FROM
        servativ AS cad
    LEFT JOIN
        tabsetor AS und ON cad.cod_lot = und.codigo
    LEFT JOIN
        homologados AS hom ON (cad.mat_siape = hom.mat_siape)
        AND (hom.compet = :compet)
    WHERE
        cad.mat_siape = :siape
	AND cad.excluido = 'N'
	AND cad.cod_sitcad NOT IN ('02','15','08')
	AND IFNULL(hom.homologado,'N') = 'S'
    ORDER BY
        cad.mat_siape
    ", array(
        array(':siape', $siape, PDO::PARAM_STR),
        array(':compet', $ano . $mes, PDO::PARAM_STR),
    ));

    $rows = $oDBase->num_rows();

    $oDados = $oDBase->fetch_object();
    $nome = $oDados->nome_serv;  // nome do servidor
    $freqh = $oDados->homologado; // situação da frequência (homologada: sim ou não)
    $lot = $oDados->cod_lot;    // unidade de lotação do servidor
    $upg = $oDados->upag;       // obtem dados da upag para saber se é a mesma do usuario
    $codlot_chefia = $oDados->uorg_pai;   // unidade de lotação da chefia imediata
    $chefia = $oDados->chefia;     // se eh ocupante de função (sim ou não)
    // frequencia homologada, desomologa e encaminha email
    if ($rows > 0) {
        // verifica se o usuario logado pertence a mesma upag
        if ($_SESSION['sRH'] == "S" && $upg != $_SESSION['upag']) {
            mensagem("Não é permitido acesso a dados de servidor/estagiário de outra UPAG!", $pagina_de_origem, 1);
        }

        //obtendo email do servidor de rh que esta devolvendo a frequencia
        $oDBase->setMensagem("Tabela de servidores inexistente");

        $oDBase->query("SELECT nome_serv, email FROM servativ WHERE mat_siape = :siape ", array(
            array(':siape', $matrh, PDO::PARAM_STR),
        ));

        $oDados = $oDBase->fetch_object();
        $nomerh = $oDados->nome_serv;
        $emailrh = $oDados->email;

        // obtendo email do chefe (titular e substituto)
        if ($chefia == 'S') {
            $emails_para = emailChefiaTitularSubstituto($codlot_chefia);
        } else {
            $emails_para = emailChefiaTitularSubstituto($lot);
        }
        //$emails_para .= ($emails_para == "" ? "" : ","); // . $emailrh;
        // atualiza o cadastro,
        // homologados e setor
        $oDBase->setMensagem("Falha na devolução da homologacao");
        $oDBase->setDestino(($_SESSION['voltar_nivel_2'] == '' ? $pagina_de_origem : $_SESSION['voltar_nivel_2']));

        $oDBase->query("UPDATE servativ SET freqh = 'N', motidev = :motidev WHERE  mat_siape = :siape ", array(
            array(':motidev', $motivo, PDO::PARAM_STR),
            array(':siape', $siape, PDO::PARAM_STR),
        ));

        $compet = substr($comp, -4) . substr($comp, 0, 2);

        $oDBase->query("UPDATE homologados SET homologado = :homologado, desomologado_motivo = :desomologado_motivo, desomologado_siape = :desomologado_siape, desomologado_data = :desomologado_data WHERE compet = :compet AND mat_siape = :siape ", array(
            array(':homologado', 'N', PDO::PARAM_STR),
            array(':desomologado_motivo', $motivo, PDO::PARAM_STR),
            array(':desomologado_siape', $sMatricula, PDO::PARAM_STR),
            array(':desomologado_data', $data_desomologacao, PDO::PARAM_STR),
            array(':compet', $compet, PDO::PARAM_STR),
            array(':mat_siape', $siape, PDO::PARAM_STR),
        ));

        $oDBase->query("UPDATE tabsetor SET dfreq = 'S', liberar_homologacao = (SELECT DATE_FORMAT(IF(DAYOFWEEK(DATE_ADD(NOW(),INTERVAL 1 DAY))=1 OR DAYOFWEEK(DATE_ADD(NOW(),INTERVAL 1 DAY))=7,DATE_ADD(NOW(),INTERVAL 3 DAY),DATE_ADD(NOW(),INTERVAL 1 DAY)),'%Y-%m-%d') AS autorizado) WHERE codigo = :codigo ", array(
            array(':codigo', $codlot_chefia, PDO::PARAM_STR),
        ));

        // verifica a existencia dos emails
        // necessários para o envio da mensagem
        if ($emails_para == "" || $emails_para == $emailrh) {
            $mensagem = "Desomologação realizada com sucesso!\\nPor favor informe a chefia que a frequência do\\nservidor $nome foi devolvida, houve problema\\nno envio do Email!";
        } else {
            enviarEmail($emails_para, 'DESOMOLOGACAO DE FREQUENCIA', "<br><br><big>Informamos que foi desomologada a frequência do(a) servidor(a) $nome, siape $siape, por $motivo.</big><br><br>");
            $mensagem = "Desomologação realizada com sucesso!";
        }
    } else {
        $mensagem = "Frequência do servidor não foi homologada pela chefia!";
    }

    if ($_SESSION['voltar_nivel_1'] == 'frequencia_verificar_homologados.php')
    {
        //$pagina_de_origem = $_SESSION['voltar_nivel_1'];
        ?>
        <script>
            window.parent.closeIFrame();
        </script>
        <?php
    }
    else
    {
        mensagem($mensagem, $pagina_de_origem, 0);
    }
}


  /* ---------------------------------------*\
  |                                         |
  |   MODO 8                                |
  |   - grava a destinação dos créditos -   |
  |                                         |
  |   - Alterado em 08/02/2018              |
  \*--------------------------------------- */
elseif ($modo == "8")
{
    /* Recebendo as variaveis do formulario */
    $dia            = conv_data($_REQUEST['dia']);
    $siape          = anti_injection($_REQUEST['siape']);
    $destinacao     = anti_injection($_REQUEST['destinacao']);
    $compensarbanco = anti_injection($_REQUEST['compensar-banco']);

    // banco de horas
    $gruposOcorrencias = carregaGruposOcorrencias($siape);
    $bancoDeHorasCredito = $gruposOcorrencias['codigoBancoDeHorasCreditoPadrao'];
    $bancoDeHorasDebito  = $gruposOcorrencias['codigoBancoDeHorasDebitoPadrao'];

    if ($destinacao == 0)
    {
        mensagem("É obrigatória a destinação do crédito de horas!", 'entrada4.php', 1);
    }
    else
    {
        $comp = pega_a_competencia($dia); // $_REQUEST['compete'];

        //grava os dados anteriores
        gravar_historico_ponto($siape, $dia, 'A');

        if (in_array($destinacao, $bancoDeHorasCredito)) // == "34343") {
        {
            // Recupera as horas a mais feitas pelo servidor neste dia.
            $overtime = getOvertimeServidor($siape, $dia, "ponto" . $comp);

            //Ciclo vigente do servidor
            $ciclo = getCicloBySiape($siape);
            $ciclo_id = $ciclo['id'];

            // Grava horas na tabela de acumulos
            saveOvertimeInDatabase($siape, $overtime , $ciclo_id);

            //Ciclo vigente do servidor
            $ciclo = getCicloBySiape($siape);
            $ciclo_id = $ciclo['id'];

            // Grava histórico
            saveHistoricalOvertime($siape, $ciclo_id, $overtime,0);

            //mensagem("Destinação efetuada com sucesso!", "pontoser.php?cmd=1&orig=1", 0);

            $query = "
                UPDATE
                    ponto$comp
                SET
                    oco     = :oco,
                    jorndif = :jorndif
                WHERE
                    siape = :siape
                    AND dia = :dia
            ";
            
            $params = array(
                array( ':siape',   $siape,      PDO::PARAM_STMT ),
                array( ':dia',     $dia,        PDO::PARAM_STMT ),
                array( ':oco',     $destinacao, PDO::PARAM_STMT ),
                array( ':jorndif', sec_to_time($overtime,'hh:mm'), PDO::PARAM_STMT ),
            );
        }
        else
        {
            $query = "
                UPDATE
                    ponto$comp
                SET
                    oco     = :oco
                WHERE
                    siape = :siape
                    AND dia = :dia
            ";
            
            $params = array(
                array( ':siape',   $siape,      PDO::PARAM_STMT ),
                array( ':dia',     $dia,        PDO::PARAM_STMT ),
                array( ':oco',     $destinacao, PDO::PARAM_STMT ),
            );
        }

        if(!empty($compensarbanco))
            $destinacao =  $bancoDeHorasDebito; //"15975";

        if(in_array($destinacao, $bancoDeHorasDebito)) //"15975"){
        {
            //Ciclo vigente do servidor
            $ciclo = getCicloBySiape($siape);
            $ciclo_id = $ciclo['id'];

            // Grava horas na tabela de acumulos
            saveOvertimeInDatabase($siape, $compensarbanco , $ciclo_id);

            //Ciclo vigente do servidor
            $ciclo = getCicloBySiape($siape);
            $ciclo_id = $ciclo['id'];

            // Grava histórico
            saveHistoricalOvertime($siape, $ciclo_id, $compensarbanco,0);
        }


        // atualiza a ocorrência
        $oDBase->setDestino($pagina_de_origem);
        $oDBase->setMensagem("Falha no registro da destinação do crédito de horas!");

        $oDBase->query( $query,$params );

        // indica a ncessidade de recalculo do
        // total de horas comuns do mes corrente.
        $oDBase->query("
        UPDATE
            usuarios
        SET
            recalculo    = :recalculo,
            refaz_frqano = :refaz_frqano
        WHERE
            siape = :siape
        ",
        array(
            array( ':siape',        $siape, PDO::PARAM_STMT ),
            array( ':recalculo',    'S',    PDO::PARAM_STMT ),
            array( ':refaz_frqano', 'S',    PDO::PARAM_STMT ),
        ));

        // registra fim do acesso ao sistema
        registraLog("finalizou o acesso ao SISREF ");

        //DataBase::fechaConexao();
        $_SESSION['hora_frequencia_finalizada'] = NULL;
        unset($_SESSION['hora_frequencia_finalizada']);


        // ATUALIZA INFORMAÇÕES DO SERVIDOR DE ACORDO COM O RETORNO DA API DO SIAPE
        if (updateServerBySiape($sMatricula))
        {
            $_SESSION['ano_inicial'] = date('Y');
            $_SESSION['mes_inicial']= date('m');
            $_SESSION['ano_final']= date('Y');
            $_SESSION['mes_final']= date('m');

            updateAfastamentosBySiape($sMatricula);
        }

        
        mensagem("Destinação efetuada com sucesso!", "pontoser.php?cmd=1&orig=1", 0);
    }
}



/* ************************************************************************ *
 *                                                                          *
 *                          FUNÇÕES COMPLEMENTARES                          *
 *                                                                          *
 * ************************************************************************ */

function carregaGruposOcorrencias($mat)
{
    $array = array();

    $oDBase = selecionaServidor($mat);
    $sitcad = $oDBase->fetch_object()->sigregjur;

    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $array['grupoOcorrenciasViagem']           = $obj->GrupoOcorrenciasViagem($sitcad);
    $array['codigoServicoExternoPadrao']       = $obj->CodigoServicoExternoPadrao($sitcad);
    $array['grupoOcorrenciasNegativasDebitos'] = $obj->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios = false);
    $array['codigoBancoDeHorasCreditoPadrao']  = $obj->CodigoBancoDeHorasCreditoPadrao($sitcad);
    $array['codigoBancoDeHorasDebitoPadrao']   = $obj->CodigoBancoDeHorasDebitoPadrao($sitcad);

    return $array;
}
