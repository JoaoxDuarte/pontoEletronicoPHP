<?php

// conexao ao banco de dados
// funcoes diversas
include_once("config.php");
include_once("class_form.frequencia.php");
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao('logado');

$modo = anti_injection($_REQUEST['modo']);


// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();
$sessao_navegacao->setPagina($_SERVER['REQUEST_URI']);

//
// VERIFICA SE O ACESSO EH VIA ENTRADA.PHP
//
// Comentado temporariamente por não sabermos, de antemão, os IPs da aplicação
//include_once('ilegal_grava.php');
// instancia o banco de dados
$oDBase = new DataBase('PDO');

// Define as competencias
// Competência atual (mês e ano)
$data = new competencia();
$ano  = $data->ano;
$year = $data->year;
$comp = $data->comp;
$mes  = $data->mes;


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setSubTitulo( "Gravação" );

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


/* -------------------------------------------------------*\
  |                                                         |
  |     MODO 1                                              |
  |                                                         |
  |                                                         |
  \*------------------------------------------------------- */
if ($modo == "1")
{
    // Define as competencias
    $oData = new trata_datasys;
    $ano   = $oData->getAnoAnterior();
    $mes   = $oData->getMesAnterior();

    $mat = getNovaMatriculaBySiape($mat);

    $destino = "regfreq.php";

    //Implementar busca para saber se o mês ja foi inserido
    $objFreqAno = new OperaRegistroFRQANO; // Operações no BD frq<ano>
    $objFreqAno->setDestino($destino);
    $objFreqAno->setSiape($mat);
    $objFreqAno->setAno($ano);
    $objFreqAno->setMes($mes);
    $objFreqAno->setLotacao($lot);
    $objFreqAno->setNumeroDiasDoMes(numero_dias_do_mes($mes, $ano));

    //Buscando os meses pra saber há dias com ocorrência
    $objFreqAno->SelectMesSiape();

    if ($objFreqAno->num_rows() == 0)
    {
        $objFreqAno->DeleteMesSiape();
        $objFreqAno->InsertMesSiape();
        if ($objFreqAno->affected_rows() > 0)
        {
            $mensagem = "A Frequência foi registrada com sucesso!";
        }
        else
        {
            $mensagem = "Frequência não foi registrada!";
        }
    }
    else
    {
        $mensagem = "Já existe registro de frequência para essa matrícula!";
    }

    mensagem($mensagem, $destino, 1);
}


/* -------------------------------------------------------*\
  |                                                         |
  |     MODO 2                                              |
  |     - grava os dados da pontuação -                     |
  |                                                         |
  \*------------------------------------------------------- */
elseif ($modo == "2")
{
    $mat = getNovaMatriculaBySiape($mat);

    //Define as competencias
    if ($_SESSION['sRH'] == "S")
    {
        $year    = $a;
        $comp    = $m;
        $destino = "freqinclui.php";
    }
    else //if ($_SESSION['sRH']=="N")
    {
        // Define as competencias
        $oData   = new trata_datasys;
        $year    = $oData->getAnoAnterior();
        $comp    = $oData->getMesAnterior();
        $destino = "regfreqoco.php?mat=$mat&lot=$lot";
    }

    $numero_dias_do_mes = numero_dias_do_mes($comp, $year);

    $oDBase01 = new DataBase('PDO');

    //Implementar busca para saber se o mês ja foi inserido
    $oDBase01->setDestino($destino);
    $oDBase01->setMensagem("A busca falhou!");
    $oDBase01->query("SELECT compet FROM frq$year WHERE compet='$year$comp' AND mat_siape='$mat' AND dia_ini='01' AND dia_fim='$numero_dias_do_mes' AND cod_ocorr != '199' ");
    $row = $oDBase01->num_rows();

    $oDBase02 = new DataBase('PDO');

    //Faz a busca se o dia ja foi inserido em alguma ocorrência
    $oDBase02->query("SELECT dia_ini, dia_fim FROM frq$year WHERE mat_siape = '$mat' AND compet = '$year$comp' AND cod_ocorr != '199' ");
    $cont = 0;
    while ($line = $oDBase02->fetch_array())
    {
        if ($dt_ini >= $line['dia_ini'] && $dt_ini <= $line['dia_fim'])
        {
            $cont++;
        }
    }

    $oDBase02->data_seek(0);

    $oDBase03 = new DataBase('PDO');
    //Faz a busca se o dia ja foi inserido em alguma ocorrência
    $oDBase03->query("SELECT dia_ini, dia_fim FROM frq$year WHERE mat_siape = '$mat' AND compet = '$year$comp' AND cod_ocorr != '199' ");
    $cont1 = 0;
    while ($line  = $oDBase03->fetch_array())
    {
        if ($dt_fim >= $line['dia_ini'] && $dt_fim <= $line['dia_fim'])
        {
            $cont1++;
        }
    }

    //Só insere a frequência no mês, se não tiver uma lançada
    if ($rows == 0 && $cont == 0 && $cont1 == 0)
    {
        $oDBase04 = new DataBase('PDO');
        $oDBase04->setMensagem("Falha na exclusão do codigo 199!");
        $oDBase04->query("DELETE FROM frq$year WHERE compet = '$year$comp' AND cod_ocorr = '199' AND mat_siape = '$mat' ");

        $oDBase05 = new DataBase('PDO');
        $oDBase05->setMensagem("A busca falhou!");
        $oDBase05->query("INSERT INTO frq$year (compet, mat_siape, dia_ini, dia_fim, cod_ocorr, cod_lot) VALUES ('$year" . "$comp','$mat','$dt_ini','$dt_fim','$ocor','$lot') ");

        mensagem("A frequência foi incluída com sucesso!", $destino, 1);

        // grava o LOG
        registraLog("registrou ocorrências para o servidor");
        // fim do LOG
    }
    else
    {

        mensagem("Já existe ocorrência de frequência para esse servidor!", $destino, 1);
    }
}


/* -------------------------------------------------------*\
  |                                                         |
  |     MODO 3                                              |
  |     - grava exclusão de frequência -                    |
  |                                                         |
  \*------------------------------------------------------- */
elseif ($modo == "3")
{
    $mat = getNovaMatriculaBySiape($mat);

    $oDBase06 = new DataBase('PDO');
    $oDBase06->setMensagem("Inexiste registro a excluir!");
    $oDBase06->query("DELETE FROM frq$year WHERE mat_siape = '$mat' AND compet = '$year$mes' AND dia_ini = '$dia' ");
    mensagem("A frequência foi excluida com sucesso!", "freqveexclui.php?pSiape=$mat'", 1);
}


/* -------------------------------------------------------*\
  |                                                         |
  |     MODO 4                                              |
  |     - grava retificação de frequência -                 |
  |                                                         |
  \*------------------------------------------------------- */
elseif ($modo == "4")
{
    $mat = getNovaMatriculaBySiape($mat);

    $oDBase07 = new DataBase('PDO');
    $oDBase07->setMensagem("Inexiste registro a retificar!");
    $oDBase07->query("UPDATE frq$year SET cod_ocorr = '$ocor', dia_ini = '$dt_ini', dia_fim = '$dt_fim' WHERE mat_siape = '$mat' AND compet = '$year$comp' AND dia_ini = '$d_ini' AND dia_fim = '$d_f' ");
    mensagem("Frequência retificada com sucesso!", "freqveretifica.php?pSiape=$mat'", 1);
}


/* -------------------------------------------------------*\
  |                                                         |
  |     MODO 5                                              |
  |     - grava registro de comparecimento -                |
  |                                                         |
  \*------------------------------------------------------- */
elseif ($modo == "5")
{
    $vHoras = strftime("%H:%M:%S", time());
    $vDatas = date("Y-m-d");
    //$hoje = date("d/m/Y");
    $comp   = date('mY');

    $sMatricula = $_SESSION['sMatricula'];

    $oDBase08 = new DataBase('PDO');
    //Implementar busca para saber se já ocorreu o registro no dia
    $oDBase08->setMensagem("Pesquisa de registro falhou!");
    $oDBase08->query("SELECT siape, dia  FROM ponto$comp WHERE dia='$vDatas' and siape='$sMatricula' ");
    $rows = $oDBase08->num_rows();

    //Buscando os meses pra saber os dias e verificar se há ocorrência
    if ($rows == 0)
    {
        $oDBase09 = new DataBase('PDO');
        $oDBase09->setMensagem("Falha no registro do ponto!");
        $oDBase09->query("INSERT INTO ponto$comp (siape, dia, entra) VALUES ('$sMatricula','$vDatas','$vHoras') ");
        mensagem("A Frequência foi registrada com sucesso!", null, 1);
    }
    else
    {
        $oDBase10 = new DataBase('PDO');
        $oDBase10->setMensagem("Falha na exclusão do codigo 199!");
        $oDBase10->query("DELETE FROM frq$year WHERE compet = '$year$comp' AND cod_ocorr = '199' AND mat_siape = '$mat' ");
        $oDBase011 = new DataBase('PDO');
        $oDBase011->setMensagem("Falha no registro da frequência!");
        $oDBase011->query("INSERT INTO frq$year (compet, mat_siape, dia_ini, dia_fim, cod_ocorr, cod_lot) VALUES ('$year$comp','$mat','01','31','000','$lot') ");
        mensagem("A frequência foi registrada com sucesso!", "regfreq.php", 1);
    }
}


/* -------------------------------------------------------*\
  |                                                         |
  |     MODO 6                                              |
  |     - grava registro de ocorrência pela chefia -        |
  |                                                         |
  \*------------------------------------------------------- */
elseif ($modo == "6")
{
    $dia     = $_REQUEST['dia'];
    $jnd     = anti_injection($_REQUEST['jnd']);
    $ocor    = anti_injection($_REQUEST['ocor']);
    $mat     = anti_injection($_REQUEST['mat']);
    $lot     = anti_injection($_REQUEST['lot']);
    $hom1    = anti_injection($_REQUEST['hom1']);
    $compete = anti_injection($_REQUEST['compete']);

    $qcinzas    = $_SESSION["qcinzas"];
    $sMatricula = $_SESSION["sMatricula"];

    $mat = getNovaMatriculaBySiape($mat);

    $ip = getIpReal(); //linha que captura o ip do usuario.

    $diac = conv_data($dia);
    $mes  = dataMes($dia);
    $ano  = dataAno($dia);

    $comp = pega_a_competencia($dia); // $_REQUEST['compete'];
    ## Verifica se eh homologacao
    #  ou acompanhamento da frequência
    #

	if ($hom1 == "1")
    {
        $destino = "regfreq3.php?mat=$mat&lot=$lot";
    }
    else
    {
        $destino = "regfreq4_entra.php";
    }

    ## verifica se a competencia em homologacao eh a mesma da inclusao
    #
    if ($compete != $comp)
    {
        mensagem("Só é permitido inserir dia da competência de homologação!", $destino, 1);
    }
    elseif (soNumeros($ocor) == '')
    {
        mensagem("Selecione uma ocorrência.", $destino, 1);
    }
    elseif (validaData($diac) == false || empty($mat))
    {
        mensagem("Matrícula ou Data inválida, refaça a operação.", $destino, 1);
    }
    else
    {
        ## obtendo dados do servidor
        #
        $oDBase012 = new DataBase('PDO');
        $oDBase012->setMensagem("Falha no registro do ponto");
        $oDBase012->query("
        SELECT
        	cad.entra_trab, cad.ini_interv, cad.sai_interv, cad.sai_trab, cad.cod_lot, cad.jornada, IFNULL(pto.idreg,'') AS idreg
        FROM
        	servativ AS cad
        LEFT JOIN
        	ponto$comp AS pto ON (cad.mat_siape = pto.siape AND pto.dia = '$diac')
        WHERE
        	mat_siape = '$mat'
    	");

        $oServidor = $oDBase012->fetch_object();

        $lot   = $oServidor->cod_lot;
        $entra = $oServidor->entra_trab;
        $sai   = $oServidor->sai_trab;
        $iniin = $oServidor->ini_interv;
        $fimin = $oServidor->sai_interv;
        $idreg = $oServidor->idreg;


        ## instancia classe frequencia
        # jornada
        #
    	$oFreq = new formFrequencia;
        $oFreq->setOrigem($pagina_de_origem); // Registra informacoes em sessao
        $oFreq->setData($dia);        // ano (data atual)
        $oFreq->setAnoHoje(date('Y'));        // ano (data atual)
        $oFreq->setUsuario($sMatricula);  // matricula do usuario
        $oFreq->setLotacao($lot);      // lotação do servidor que se deseja alterar a frequencia
        $oFreq->setSiape($mat);    // matricula do servidor que se deseja alterar a frequencia
        $oFreq->setMes($mes); // mes que se deseja alterar a frequencia
        $oFreq->setAno($ano); // ano que se deseja alterar a frequencia
        $oFreq->setNomeDoArquivo('ponto' . $comp); // nome do arquivo de frequencia
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
    	$oFreq->setDestino($destino);

        // cálculo - horas do dia
        $oResultado = $oFreq->processaOcorrencias();
        $oco        = $ocor; //$oResultado->ocorrencia;
        $jdia       = $oResultado->jornada_realizada;
        $jp         = $oResultado->jornada_prevista;
        $dif        = $oResultado->jornada_diferenca;

        # horários processado
        #
    	$he  = $oResultado->entra;
        $hs  = $oResultado->sai;
        $hie = $oResultado->intini;
        $his = $oResultado->intsai;


        ## ocorrências grupos
        $gruposOcorrencias = carregaGruposOcorrencias($mat);
        $codigosDebitos             = $gruposOcorrencias['codigosDebitos'];
        $codigoServicoExternoPadrao = $gruposOcorrencias['codigoServicoExternoPadrao'];


        ## Implementar busca para saber se já existe registro de ocorrência neste dia
        #
        $oDBase013 = new DataBase('PDO');
        $oDBase013->setMensagem("Tabela de ponto inexistente!");
        $oDBase013->query("SELECT siape FROM ponto$comp WHERE dia='$diac' AND siape='$mat' ");

        if ($oDBase013->num_rows() == 0)
        {
            $oDBase014 = new DataBase('PDO');
            $oDBase014->setMensagem("Falha no registro do ponto");

            if (in_array($ocor, $codigosDebitos)) // "00111_00129_00137_00167_00168_02323_02525_55555"
            {
                $oDBase014->query("INSERT INTO ponto$comp SET dia='$diac', siape='$mat', oco='$ocor', jornp='$jp', jorndif='$dif', idreg='C', ipch='$ip', matchef='$sMatricula' ");
            }
            elseif (in_array($ocor, $codigoServicoExternoPadrao)) // "22222"
            {
                $oDBase014->query("INSERT INTO ponto$comp SET dia='$diac', siape='$mat', entra='$he', intini='$hie', intsai='$his', sai='$hs', jornd='$jdia', jornp='$jp', jorndif='00:00', oco='$ocor', idreg='C', ipch='$ip', matchef='$sMatricula' ");
            }
            else
            {
                $oDBase014->query("INSERT INTO ponto$comp SET dia='$diac', siape='$mat', oco='$ocor', idreg='C', ipch='$ip', matchef='$sMatricula' ");
            }

            mensagem("Ocorrência registrada com sucesso!", $destino, 1);
        }
        else
        {
            mensagem("Já existe registro para esse dia, não foi registrada a ocorrencia!", $destino, 1);
        }
    }
}


/* -------------------------------------------------------*\
  |                                                         |
  |     MODO 7                                              |
  |     - grava registro de ocorrência pela chefia -        |
  |                                                         |
  \*------------------------------------------------------- */
elseif ($modo == "7")
{
    $dia        = $_REQUEST['dia'];
    $comp       = anti_injection($_REQUEST['compete']);
    $ocor       = anti_injection($_REQUEST['ocor']);
    $mat        = anti_injection($_REQUEST['mat']);
    $lot        = anti_injection($_REQUEST['lot']);
    $cmd        = anti_injection($_REQUEST['cmd']);
    $idreg      = anti_injection($_REQUEST['idreg']);
    $diac       = conv_data($dia);
    $mes        = substr($comp, 0, 2);
    $ano        = substr($comp, 2, 4);
    $qcinzas    = $_SESSION["qcinzas"];
    $sMatricula = $_SESSION["sMatricula"];
    $ip         = getIpReal(); //linha que captura o ip do usuario.

    $mat        = getNovaMatriculaBySiape($mat);

    if ($_SESSION['sRH'] == "S" && $_SESSION['sAPS'] == "N" && $idreg == "C")
    {
        mensagem("Não é permitido alterar ocorrência incluída por outro perfil de usuário!", pagina_de_origem(), 1);
    }
    elseif ($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N" && $idreg == "R")
    {
        mensagem("Não é permitido alterar ocorrência incluída por outro perfil de usuário!", pagina_de_origem(), 1);
    }
    elseif ($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "S" && ($idreg == "R" || $idreg == "C"))
    {
        //
    }

    if (validaData($diac) == false || empty($mat))
    {
        mensagem("Matrícula ou Data inválida, refaça a operação.", pagina_de_origem(), 1);
    }

    include_once( "dutil.php" );
    $oDBase015 = new DataBase('PDO');
    //obtendo dados do servidor
    $oDBase015->query("SELECT entra_trab, ini_interv, sai_interv, sai_trab, cod_lot, jornada FROM servativ  WHERE mat_siape = '$mat' ");
    $oServativ = $oDBase015->fetch_object();
    $entra     = $oServativ->entra_trab;
    $sai       = $oServativ->sai_trab;
    $iniin     = $oServativ->ini_interv;
    $fimin     = $oServativ->sai_interv;
    $jnd       = $oServativ->jornada / 5;
    $jnd       = "0" . $jnd . ":00";

    // Verifica se é natal, ano novo ou quarta feira de cinzas.
    // Carga horária de 6 horas no Natal e Ano Novo de 2009, e 4 horas na Quarta-feira de Cinzas de 2010.
    // Já atende ao critério para apuração da carga horária dos dias de ponto facultativo
    // (natal e ano novo de 2010, e a quarta feira de cinzas de 2011).

    $jnd = ponto_facultativo($dia, $jnd, $ano, $entra, $sai, $iniin, $fimin);


    //rotina para definir a matricula do usuario a ser gravada face o perfil RH ou CH
    if ($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N")
    {
        $siape1 = $sMatricula;
        $siape2 = '';
        $ip1    = $ip;
        $ip2    = '';
    }
    else
    {
        $siape1 = '';
        $siape2 = $sMatricula;
        $ip1    = '';
        $ip2    = $ip;
    }

    //recupera dados do registro de frequencia para gravar no histórico antes da gravação da alteração
    gravar_historico_ponto($mat, $diac);


    ## ocorrências grupos
    $gruposOcorrencias = carregaGruposOcorrencias($mat);
    $codigosDebitos             = $gruposOcorrencias['codigosDebitos'];
    $codigoServicoExternoPadrao = $gruposOcorrencias['codigoServicoExternoPadrao'];
    $grupoOcorrenciasFerias     = $gruposOcorrencias['grupoOcorrenciasFerias'];


    // se forem selecionadas uma das ocorrencias abaixo
    // mantemos o horario registrado antes
    $oDBase016 = new DataBase('PDO');
    if ($ocor != '' && in_array($ocor, $codigosDebitos)) // "00168 00111 00129 00137 00167 00168 02323 02525 55555"
    {
        $oDBase016->query("UPDATE ponto$comp SET jornd = '00:00', jornp = '$jnd', jorndif = '$jnd', oco = '$ocor', ipch = '$ip1', iprh = '$ip2', matchef = '$siape1', siaperh = '$siape2' WHERE siape = '$mat' AND dia = '$diac' ");
    }
    else if (in_array($ocor, $grupoOcorrenciasFerias)) //"00169"
    {
        // ferias
        $oDBase016->query("UPDATE ponto$comp SET entra = '00:00:00', intini = '00:00:00' , intsai = '00:00:00', sai = '00:00:00', jornd = '00:00', jornp = '00:00', jorndif = '00:00', oco = '$ocor', ipch = '$ip1', iprh = '$ip2', matchef = '$siape1', siaperh = '$siape2' WHERE siape = '$mat' AND dia = '$diac' ");
    }
    else
    {
        // Serviço Externo (22222) e outros ????????????
        $oDBase016->query("UPDATE ponto$comp SET entra = '$entra', intini  = '$iniin', intsai = '$fimin', sai = '$sai', jornd = '$jnd', jornp = '$jnd', jorndif = '00:00', oco = '$ocor', ipch = '$ip1', iprh = '$ip2', matchef = '$siape1', siaperh = '$siape2' WHERE siape = '$mat' AND dia = '$diac' ");
    }

    if ($oDBase016->affected_rows() > 0)
    {
        mensagem("Ocorrência alterada com sucesso!");
    }
    else
    {
        mensagem("Falha na alteracao do registro do ponto,\\nou Alteração já realizada!\\n" . $dberro);
    }

    if (($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N") || ($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "S"))
    {
        if ($cmd == '1')
        {

        }
        else
        {
            voltar(1, "regfreq3.php?mat=$mat&lot=$lot");
        }
    }
    elseif ($_SESSION['sRH'] == "S" && $_SESSION['sAPS'] == "N")
    {
        $mes = substr($comp, 0, 2);
        $ano = substr($comp, 2, 4);

        if ($cmd == '1')
        {
            voltar(1, "freqaltera.php");
        }
        else
        {
            voltar(1, "freqaltera2.php");
        }
    }

    voltar(3);
}


/* -------------------------------------------------------*\
  |                                                         |
  |     MODO 8                                              |
  |     - grava registro de ocorrência pela chefia -        |
  |                                                         |
  \*------------------------------------------------------- */
elseif ($modo == "8")
{
    $siape        = anti_injection($_REQUEST["siape"]);
    $lotacao      = anti_injection($_REQUEST["lotacao"]);
    $mes2         = anti_injection($_REQUEST["mes2"]);
    $ano2         = anti_injection($_REQUEST["ano2"]);
    $comp         = $mes2 . $ano2;
    $comp_inverte = $ano2 . $mes2;

    $mat        = $siape;
    $sMatricula = $_SESSION["sMatricula"];
    $dthomol    = date("Y-m-d");

    $mat   = getNovaMatriculaBySiape($mat);
    $siape = getNovaMatriculaBySiape($siape);


    ## ocorrências grupos
    $gruposOcorrencias = carregaGruposOcorrencias($mat);
    $codigoSemFrequenciaPadrao   = $gruposOcorrencias['codigoSemFrequenciaPadrao'];
    $codigoRegistroParcialPadrao = $gruposOcorrencias['codigoRegistroParcialPadrao'];
    $codigosTrocaObrigatoria     = $gruposOcorrencias['codigosTrocaObrigatoria'];

    $registroParcialPadrao = implode(', ', $codigoRegistroParcialPadrao);
    $semFrequenciaPadrao   = implode(', ', $codigoSemFrequenciaPadrao);
    $trocaObrigatoria      = $codigosTrocaObrigatoria[0];


    $se_codigo_88888          = anti_injection($_REQUEST['teste']);
    $se_codigo_99999          = anti_injection($_REQUEST['teste9']);
    $se_codigo_tracos         = anti_injection($_REQUEST['teste_tracos']);
    $total_de_registro_no_mes = anti_injection($_REQUEST['teste2']);
    $total_de_dias_no_mes     = anti_injection($_REQUEST['teste3']);

    if ($se_codigo_88888 > 0 && $se_codigo_99999 > 0 && $se_codigo_tracos > 0)
    {
        mensagem('Não é permitido homologar frequência com código ' . $registroParcialPadrao . ',\\n' . $semFrequenciaPadrao . ' e \"' . $trocaObrigatoria . '\" na ficha do servidor!', $sessao_navegacao->getPaginaAnterior(), 1);
    }
    else if ($se_codigo_88888 > 0 && $se_codigo_99999 > 0) // 88888 e 99999
    {
        mensagem('Não é permitido homologar frequência com código ' . $registroParcialPadrao . ' e ' . $semFrequenciaPadrao . ' na ficha do servidor!', $sessao_navegacao->getPaginaAnterior(), 1);
    }
    else if ($se_codigo_88888 > 0 && $se_codigo_tracos > 0) // 88888 e -----
    {
        mensagem('Não é permitido homologar frequência com código ' . $registroParcialPadrao . ' e \"' . $trocaObrigatoria . '\" na ficha do servidor!', $sessao_navegacao->getPaginaAnterior(), 1);
    }
    else if ($se_codigo_99999 > 0 && $se_codigo_tracos > 0) // 99999 e -----
    {
        mensagem('Não é permitido homologar frequência com código ' . $semFrequenciaPadrao . ' e \"' . $trocaObrigatoria . '\" na ficha do servidor!', $sessao_navegacao->getPaginaAnterior(), 1);
    }
    else if ($se_codigo_88888 > 0)
    {
        mensagem('Não é permitido homologar frequência com código ' . $registroParcialPadrao . ' na ficha do servidor!', $sessao_navegacao->getPaginaAnterior(), 1);
    }
    else if ($se_codigo_99999 > 0)
    {
        mensagem('Não é permitido homologar frequência com código ' . $semFrequenciaPadrao . ' na ficha do servidor!', $sessao_navegacao->getPaginaAnterior(), 1);
    }
    else if ($se_codigo_tracos > 0)
    {
        mensagem('Não é permitido homologar frequência com código \"' . $trocaObrigatoria . '\" na ficha do servidor!', $sessao_navegacao->getPaginaAnterior(), 1);
    }
    else if ($total_de_registro_no_mes < $total_de_dias_no_mes)
    {
        mensagem('Está faltando dias na ficha do servidor complete para que seja possível homologar!', $sessao_navegacao->getPaginaAnterior(), 1);
    }
    $oDBase017 = new DataBase('PDO');
    $oDBase017->setMensagem("Falha no registro de homologados");
    $oDBase017->query("UPDATE servativ SET  freqh = 'S' WHERE  mat_siape='$siape' ");

    $oDBase018 = new DataBase('PDO');
    $oDBase018>query("SELECT * FROM homologados WHERE mat_siape='$mat' AND compet='$comp_inverte' ");
    $rowsh = $oDBase018->num_rows();

    $oDBase019 = new DataBase('PDO');
    if ($rowsh == 0)
    {
        $oDBase019->query("INSERT INTO homologados (compet, mat_siape, homologado, homologado_siape, homologado_data) VALUES ( '$comp_inverte', '$mat', 'S', '$sMatricula', '$dthomol') ");
    }
    else
    {
        $oDBase019->query("UPDATE homologados SET homologado = 'S', homologado_siape =  '$sMatricula', homologado_data = '$dthomol' WHERE compet = '$comp_inverte' and mat_siape = '$mat' ");
    }
    $oDBase020 = new DataBase('PDO');
    //obtendo dados de homologação do setor
    $oDBase020->query("SELECT freqh FROM servativ WHERE chefia = 'N' AND freqh = 'N' AND cod_lot = '$lotacao' AND cod_sitcad NOT IN ('02','08','15') AND excluido = 'N' ");
    $rows = $oDBase020->num_rows();


    if ($rows == 0)
    {
        $oDBase021 = new DataBase('PDO');
        $oDBase021->query("UPDATE tabsetor SET  tfreq = 'S', dfreq = 'N' WHERE  codigo = '$lotacao' ");
    }

    $oDBase022 = new DataBase('PDO');
    $oDBase022->query("UPDATE usuarios SET recalculo='S', refaz_frqano='S' WHERE siape = '$mat' ");

    //Fim da atualização de banco de horas
    mensagem("Homologação realizada com sucesso!", $sessao_navegacao->getPagina(0), 1);
}


/* --------------------------------------------*\
  |                                              |
  |   MODO 10                                    |
  |   - grava registro de ocorrência pela chefia |
  |                                              |
  \*-------------------------------------------- */
elseif ($modo == "10")
{
    $compete = anti_injection($_REQUEST["compete"]);
    $cmd     = anti_injection($_REQUEST["cmd"]);
    $mat     = anti_injection($_REQUEST["mat"]);

    $mat = getNovaMatriculaBySiape($mat);

    $jnd     = anti_injection($_REQUEST["jnd"]);
    $ocor    = anti_injection($_REQUEST["ocor"]);
    $dia2    = $_REQUEST["dia2"];
    $dia     = $_REQUEST["dia"];
    $lot     = anti_injection($_REQUEST["lot"]);
    $dt_ini  = ($_REQUEST["dt_ini"] == '' ? substr($dia2, 0, 2) : $_REQUEST["dt_ini"]);
    $dt_fim  = ($_REQUEST["dt_fim"] == '' ? substr($dia, 0, 2) : $_REQUEST["dt_fim"]);

    $qcinzas    = $_SESSION["qcinzas"];
    $sMatricula = $_SESSION["sMatricula"];
    $oDBase023 = new DataBase('PDO');

    $ip        = getIpReal(); //linha que captura o ip do usuario.
    //obtendo dados do servidor
    $oDBase023->query("SELECT cad.entra_trab, cad.ini_interv, cad.sai_interv, cad.sai_trab, cad.cod_lot, cad.cod_sitcad FROM servativ AS cad WHERE cad.mat_siape = '$mat' ");
    $oServidor = $oDBase023->fetch_object();

    $entra      = $oServidor->entra_trab;
    $sai        = $oServidor->sai_trab;
    $iniin      = $oServidor->ini_interv;
    $fimin      = $oServidor->sai_interv;
    $cod_sitcad = $oServidor->cod_sitcad;

    // valida os dados assados por formulario
    /*
      $mensagem = "";
      if (soNumeros($ocor) == "") { $mensagem .= "Selecione uma ocorrência!\n"; }
      if (strlen($dt_ini) < 2)    { $mensagem .= 'O dia de início da ocorrência é obrigatório com dois caracteres!\n'; }
      if (strlen($dt_fim) < 2)    { $mensagem .= 'O dia de fim da ocorrência é obrigatório com dois caracteres!\n'; }
      if (($mes == '02' && $dt_fim > '29' && ((($ano % 4) == 0 && ($ano % 100) != 0) || (($ano % 400) == 0))))
      {
      $mensagem .= 'Dia Fim inválido para ano bisexto nesse mês!';
      }
      if (($mes == '02' && $dt_fim > '28' && ((($ano % 4) != 0 && ($ano % 100) != 0))))
      {
      $mensagem .= 'Dia Fim inválido para esse mês!';
      }
      if(($mes == '01' && $dt_fim > '31') || ($mes == '03' && $dt_fim > '31' ) || ($mes == '05' && $dt_fim > '31') || ($mes == '07' && $dt_fim > '31' ) || ($mes == '08' && $dt_fim > '31' ) || ($mes == '10' && $dt_fim > '31') || ($mes == '12' && $dt_fim > '31'))
      {
      $mensagem .= 'Dia Fim inválido para esse mês!';
      }
      if( ($mes == '04' && $dt_fim > '30') || ($mes == '06' && $dt_fim > '30') || ($mes == '09' && $dt_fim > '30') || ($mes == '11' && $dt_fim > '30'))
      {
      $mensagem .= 'Dia Fim inválido para esse mês!';
      }
      if ($mensagem != "")
      {
      mensagem( $mensagem );
      ....
      ....
     */

    if (soNumeros($ocor) == "")
    {
        mensagem("Selecione uma ocorrência!");
    }
    elseif ($dia == '0000-00-00' || empty($dia) || $dia2 == '0000-00-00' || empty($dia2) || empty($mat) || empty($jnd))
    {
        mensagem("Horário informado não é inferior à jornada legal do servidor!", null, 1);
    }
    else
    {
        if (($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N" && $_SESSION['sTabServidor'] == "N") || ($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "S" && $_SESSION['sTabServidor'] == "N"))
        {
            $dia1 = conv_data($dia2);
            $dia2 = conv_data($dia);
            $jnd  = "0" . $jnd . ":00";

            // verifica se a competencia em homologacao e a mesma da inclusao
            $mes  = substr($dia1, 5, 2);
            $ano  = substr($dia1, 0, 4);
            $comp = $mes . $ano;

            $mes2  = substr($dia2, 5, 2);
            $ano2  = substr($dia2, 0, 4);
            $comp2 = $mes2 . $ano2;

            if (($compete != $comp) || ($compete != $comp2))
            {
                mensagem("Só é permitido inserir dia da competência de homologação!", null, 1);
            }
        }
        elseif ($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "S" && $_SESSION['sTabServidor'] == "S")
        {

            if ($cmd == "1")
            {
                $mes     = anti_injection($_REQUEST["mes"]);
                $ano     = anti_injection($_REQUEST["ano"]);
                $dia1    = $ano . "-" . $mes . "-" . $dt_ini;
                $dia2    = $ano . "-" . $mes . "-" . $dt_fim;
                $compete = $mes . $ano;
                $jnd     = "0" . $jnd . ":00";
            }
            else
            {
                if (!empty($dt_ini) && !empty($dt_fim))
                {
                    $dia1 = substr($compete, 2, 4) . "-" . substr($compete, 0, 2) . "-" . $dt_ini;
                    $dia2 = substr($compete, 2, 4) . "-" . substr($compete, 0, 2) . "-" . $dt_fim;
                }
                else
                {
                    $dia1 = conv_data($dia2);
                    $dia2 = conv_data($dia);
                }
                $jnd = "0" . $jnd . ":00";

                // verifica se a competencia em homologacao e a mesma da inclusao
                $mes  = substr($dia1, 5, 2);
                $ano  = substr($dia1, 0, 4);
                $comp = $mes . $ano;

                $mes2  = substr($dia2, 5, 2);
                $ano2  = substr($dia2, 0, 4);
                $comp2 = $mes2 . $ano2;

                if (($compete != $comp) || ($compete != $comp2))
                {
                    mensagem("Só é permitido inserir dia da competência de homologação!", null, 1);
                }
            }
        }
        elseif ($_SESSION['sAPS'] == "N" && $_SESSION['sRH'] == "S" && $_SESSION['sTabServidor'] == "S")
        {

            $mes = anti_injection($_REQUEST["mes"]);
            $ano = anti_injection($_REQUEST["ano"]);

            $dia1    = $ano . "-" . $mes . "-" . $dt_ini;
            $dia2    = $ano . "-" . $mes . "-" . $dt_fim;
            $compete = $mes . $ano;
            $jnd     = "0" . $jnd . ":00";

            $mes2  = substr($dia2, 5, 2);
            $ano2  = substr($dia2, 0, 4);
            $comp2 = $mes2 . $ano2;
        }


        ## ocorrências grupos
        $gruposOcorrencias = carregaGruposOcorrencias($mat);
        $codigosDebitos = $gruposOcorrencias['codigosDebitos'];


        $mensagem = "Falha no registro das ocorrências!";

        for ($diax = $dt_ini; $diax <= $dt_fim; $diax++)
        {
            $dia = substr($compete, 2, 4) . "-" . substr($compete, 0, 2) . "-" . substr('00'.$diax,-2);

            if (validaData($dia) == false)
            {
                $mensagem = "Sucesso parcial no registro das Ocorrência(s)!";
                continue;
            }

            //verifica se quarta-feira de cinzas ou dia 24/12 ou 31/12
            $jnd = ponto_facultativo($dia, $jnd, $ano2, $entra, $sai, $iniin, $fimin);

            //Implementar busca para saber se já ocorreu o registro de entrada no dia
            $oDBase024 = new DataBase('PDO');
            $oDBase024->setMensagem("Tabela de ponto inexistente");

            $oDBase024->query("SELECT idreg, IFNULL(matchef,'') AS matchef, IFNULL(siaperh,'') AS siaperh FROM ponto$compete WHERE dia='$dia' AND siape='$mat' ");
            $rows = $oDBase024->num_rows();

            if ($rows == 0)
            {
                $mensagem = (substr($mensagem,0,5) == 'Falha' ? "Ocorrência(s) registrada(s) com sucesso!" : $mensagem);

                if ($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N")
                {
                    $oDBase025 = new DataBase('PDO');
                    if (in_array($ocor, $codigosDebitos)) //'00168 00129 00137 00167 00111 55555 02323 02525 62012'
                    {
                        $oDBase025->query("INSERT INTO ponto$compete (dia, siape, oco, jornp, jorndif, idreg, ip, ip2, ip3, ip4, ipch, iprh, matchef) VALUES ('$dia', '$mat', '$ocor','$jnd', '$jnd', 'C', '', '', '', '', '$ip', '', '$sMatricula') ");
                    }
                    else
                    {
                        $oDBase025->query("INSERT INTO ponto$compete (dia, siape, oco, idreg, ip, ip2, ip3, ip4, ipch, iprh, matchef) VALUES ('$dia', '$mat', '$ocor', 'C', '', '', '', '', '$ip', '', '$sMatricula') ");
                    }
                }
                elseif ($_SESSION['sRH'] == "S")
                {
                    $oDBase026 = new DataBase('PDO');
                    if (in_array($ocor, $codigosDebitos)) //'00168 00129 00137 00167 00111 55555 02323 02525 62012'
                    {
                        $oDBase026->query("INSERT INTO ponto$compete (dia, siape, oco, jornp, jorndif, idreg, ip, ip2, ip3, ip4, ipch, iprh, siaperh) VALUES ('$dia', '$mat', '$ocor','$jnd', '$jnd', 'R', '', '', '', '', '', '$ip', '$sMatricula') ");
                    }
                    else
                    {
                        $oDBase026->query("INSERT INTO ponto$compete (dia, siape, oco, idreg, ip, ip2, ip3, ip4, ipch, iprh, siaperh) VALUES ('$dia', '$mat', '$ocor', 'R',   '', '',  '', '', '', '$ip', '$sMatricula') ");
                    }
                }
            }
            else
            {
                $idreg_chefia = ['C','A'];
                $idreg_rh     = ['R','H','X'];

                list( $idreg, $matchef, $siaperh ) = $oDBase024->fetch_array();

                if ($idreg == 'S' || (in_array($idreg, $idreg_chefia) && $matchef != '' && $_SESSION['sAPS'] == 'S') || (in_array($idreg, $idreg_rh) && $_SESSION['sRH'] == 'S'))
                {
                    $mensagem = (substr($mensagem,0,5) == 'Falha' ? "Ocorrência(s) registrada(s) com sucesso!" : $mensagem);

                    //grava os dados anteriores
                    gravar_historico_ponto($mat, $dia, 'A');

                    if ($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N")
                    {
                        if (in_array($ocor, $codigosDebitos)) //'00168 00129 00137 00167 00111 55555 02323 02525 62012'
                        {
                            $oDBase027 = new DataBase('PDO');
                            $oDBase027->query("UPDATE ponto$compete SET dia='$dia', siape='$mat', oco='$ocor', jornp='$jnd', jorndif='$jnd', idreg='C', ipch='$ip', matchef='$sMatricula' WHERE dia = '$dia' AND siape='$mat' ");
                        }
                        else
                        {
                            $oDBase028 = new DataBase('PDO');
                            $oDBase028->query("UPDATE ponto$compete SET dia='$dia', siape='$mat', oco='$ocor', idreg='C', ipch='$ip', matchef='$sMatricula' WHERE dia = '$dia' AND siape='$mat' ");
                        }
                    }
                    elseif ($_SESSION['sRH'] == "S")
                    {
                        if (in_array($ocor, $codigosDebitos)) //'00168 00129 00137 00167 00111 55555 02323 02525 62012'
                        {
                            $oDBase029 = new DataBase('PDO');
                            $oDBase029->query("UPDATE ponto$compete SET dia='$dia', siape='$mat', oco='$ocor', jornp='$jnd', jorndif='$jnd', idreg='R', iprh='$ip', siaperh='$sMatricula' WHERE dia = '$dia' AND siape='$mat' ");
                        }
                        else
                        { $oDBase030 = new DataBase('PDO');
                            $oDBase030->query("UPDATE ponto$compete SET dia='$dia', siape='$mat', oco='$ocor', idreg='R', iprh='$ip', siaperh='$sMatricula' WHERE dia = '$dia' AND siape='$mat' ");
                        }
                    }
                }
                else
                {
                    $mensagem = "Sucesso parcial no registro das Ocorrência(s)!\nExiste(m) dias com ocorrências registradas\npor outro perfil de usuário!";
                }
            }
        }
    }

    switch (pagina_de_origem())
    {
        case 'freqinclui2.php':
            $destino = $_SESSION['inclusaoOrigem'];
            if (substr($mensagem,0,5) == 'Falha')
            {
                $destino = $sessao_navegacao->getPagina(0);
            }
            else if ($cod_sitcad == '08')
            {
                $oDBase = new DataBase('PDO');
                $oDBase->query("UPDATE servativ SET freqh='S' WHERE siape = '$mat' ");
            }
            mensagem($mensagem,$destino);
            break;

        case 'registro10.php':
            mensagem($mensagem,$sessao_navegacao->getPagina(1));
            break;
    }
}


/* -------------------------------------------------------*\
  |                                                        |
  |     MODO 11                                            |
  |     - grava registro de abono pela chefia -            |
  |                                                        |
  \*----------------------------------------------------- */
elseif ($modo == "11")
{
    ## dados enviados por formulario
    #
    $cmd        = anti_injection($_REQUEST["cmd"]);
    $mat        = anti_injection($_REQUEST["mat"]);
    $dia        = $_REQUEST["dia"];
    $diac       = conv_data($dia);
    $oco        = anti_injection($_REQUEST["oco"]);
    $just       = str_replace("'", "`", $_REQUEST['just']);
    $just       = str_replace('"', "`", $just);
    $qcinzas    = $_SESSION["qcinzas"];
    $sMatricula = $_SESSION["sMatricula"];
    $ip         = getIpReal(); //linha que captura o ip do usuario.

    $mat = getNovaMatriculaBySiape($mat);


    ## ocorrências grupos
    $gruposOcorrencias = carregaGruposOcorrencias($mat);
    $passiveisDeAbono = $gruposOcorrencias['passiveisDeAbono'];


    ## de acordo com o valor de $cmd
    #  a competência e pagina de origem
    #  tem definições diferentes
    #
    if ($cmd == "1")
    {
        $comp             = date('mY');
        $pagina_de_origem = "regfreq4_entra.php";
    }
    elseif ($cmd == "2")
    {
        $data             = data2arrayBR($dia);
        $mes              = $data[1];
        $ano              = $data[2];
        $year             = $ano;
        $comp             = $mes . $year;
        $pagina_de_origem = "regfreq3.php?mat=$mat&lot=$lot&jnd=$jnd";
    }

    ## define a pagina de retorno em caso de erro
    #
    $oDBase->setDestino($pagina_de_origem);

    ## verifica se o código de ocorrência
    #  encontra-se entre os permitidos abonar
    #
    if (validaData($diac) == false)
    {
        $mensagem = "Data inválida, refaça a operação.";
    }
    else if ($oco == '' || ($oco != '' && in_array($oco, $passiveisDeAbono))) //"00172_00129_55555_88888_99999"
    {
        ## instancia classe frequencia
        # cálculo das horas trabalhadas
        #
    	$oFreq = new formFrequencia;
        $oFreq->setOrigem($pagina_de_origem); // Registra informacoes em sessao
        $oFreq->setAnoHoje(substr($dia, 6, 4));        // ano (data atual)
        $oFreq->setData($dia);        // ano (data atual)
        $oFreq->setUsuario($sMatricula);  // matricula do usuario
        $oFreq->setSiape($mat);    // matricula do servidor que se deseja alterar a frequencia
        //$oFreq->setLotacao( $sLotacao );    // lotação do servidor que se deseja alterar a frequencia
        $oFreq->setMes(substr($dia, 3, 2)); // mes que se deseja alterar a frequencia
        $oFreq->setAno(substr($dia, 6, 4)); // ano que se deseja alterar a frequencia
        $oFreq->setNomeDoArquivo('ponto' . $comp); // nome do arquivo de frequencia
        $oFreq->pontoFacultativo();
        $oFreq->verificaSeDiaUtil();

        $oFreq->loadDadosServidor();
        $entra = $oFreq->getEntrada();
        $sai   = $oFreq->getSaida();
        $iniin = $oFreq->getInicioIntervalo();
        $fimin = $oFreq->getFimIntervalo();
        $jnd   = $oFreq->getJornada();
        $j     = formata_jornada_para_hhmm($jnd);

        ## ocupantes de função
        #
    	$ocupaFuncao = $oFreq->getChefiaAtiva();

        if ($ocupaFuncao == 'S')
        {
            // - Se titular da função ou em efetiva
            //   substituição, a jornada eh de 40hs
            $jnd = 40;
            $j   = formata_jornada_para_hhmm($jnd); // compatibilidade
            $oFreq->setJornada($jnd);
            $oFreq->setJ($j);
        }

        $oFreq->loadDadosSetor();
        $oFreq->loadDadosPonto($dia);

        $oPontoResult = $oFreq->getConexaoBD();
        $nRows        = $oPontoResult->num_rows();
        if ($nRows > 0)
        {
            $oPonto = $oPontoResult->fetch_object();
            $entra  = $oPonto->entra;
            $iniin  = $oPonto->intini;
            $fimin  = $oPonto->intsai;
            $sai    = $oPonto->sai;
        }
        else
        {
            $entra = '00:00:00';
            $iniin = '00:00:00';
            $fimin = '00:00:00';
            $sai   = '00:00:00';
        }

        $hEntrada   = (time_to_sec($iniin) == 0 && time_to_sec($sai) == 0   ? '00:00:00' : $entra);
        $hIniAlmoco = (time_to_sec($entra) == 0                             ? '00:00:00' : $iniin);
        $hFimAlmoco = (time_to_sec($entra) == 0 || time_to_sec($iniin) == 0 ? '00:00:00' : $fimin);
        $hSaida     = (time_to_sec($entra) == 0 && time_to_sec($sai) == 0   ? '00:00:00' : $sai);

        $oFreq->setEntrada($hEntrada);
        $oFreq->setInicioIntervalo($hIniAlmoco);
        $oFreq->setFimIntervalo($hFimAlmoco);
        $oFreq->setSaida($hSaida);

        $aHorasDia = $oFreq->calculaHorasTrabalhadas();
        //$oco = $aHorasDia[0];
        $dif       = $aHorasDia[3];
        $jdia      = $aHorasDia[1];

        ## - turno estendido
        #
    	$turno_estendido = $oFreq->turnoEstendido('3'); // jornada
        $sTurnoEstendido = $oFreq->getTurnoEstendido(); // informa se o servidor encontra-se em unidade autorizada a realizar o turno estendido

        ## mensagem padrao no caso de sucesso
        #
    	$mensagem= "Abono registrado com sucesso!";

        ## Incluindo/alterando dados do dia abonado
        #
        $oDBase031 = new DataBase('PDO');
    	$oDBase031->setMensagem("Falha no abono do dia");
        if ($nRows == 0)
        {
            $oDBase031->query("INSERT INTO ponto$comp (dia, siape,  entra, intini , intsai, sai, jornd, jornp, jorndif, oco, idreg, ip, ip2, ip3, ip4, justchef, ipch, iprh, matchef) VALUES ('$diac', '$mat', '$entra', '$iniin', '$fimin', '$sai', '$jdia', '$j', '00:00', '00000', 'A', '$ip', '', '', '', '$just', '$ip', '', '$sMatricula') ");
        }
        else
        {
            //grava os dados anteriores
            gravar_historico_ponto($mat, $diac, 'A');

            $oDBase031->query("UPDATE ponto$comp SET entra='$entra', intini='$iniin', intsai='$fimin', sai='$sai', jornd='$jdia', jornp='$j', jorndif='00:00', oco='00000', idreg='A', justchef='$just', ipch='$ip', matchef='$sMatricula' WHERE siape='$mat' AND dia='$diac' ");
        }

        $oDBase032 = new DataBase('PDO');

        // indica atualiza ficha de frequencia (FRQano)
        $oDBase032->query("UPDATE usuarios SET recalculo = 'S', refaz_frqano = 'S' WHERE siape = '$mat' ");
    }
    else
    {
        $mensagem = "Não é permitido abonar dia com ocorrência diferente de 00172, 55555 ou 88888!";
    }

    mensagem($mensagem, $pagina_de_origem, 1);
}


/* -------------------------------------------------------*\
  |                                                        |
  |     MODO 14                                            |
  |     - registra frequência como homologada -            |
  |                                                        |
  |  Alteração: 08/07/2013                                 |
  \*----------------------------------------------------- */
elseif ($modo == "14")
{
    // dados via formulario
    $siape = anti_injection($_REQUEST["siape"]);
    $mes   = anti_injection($_REQUEST["mes"]);
    $ano   = anti_injection($_REQUEST["ano"]);

    $siape = getNovaMatriculaBySiape($siape);

    // atualiza cadastro do servidor
    $oDBase048 = new DataBase('PDO');
    $oDBase048->setMensagem("Falha no registro da verificação de homologação!");
    $oDBase048->query("UPDATE servativ SET freqh = 'V' WHERE mat_siape = '" . $siape . "' ");

    // atualiza homologados
    $oDBase049 = new DataBase('PDO');
    $oDBase049->query("UPDATE homologados SET homologado='V' WHERE compet = '" . $ano . $mes . "' AND mat_siape = '$siape' ");
    //var_dump('skalkalks');die;


    ?>
    <script>
      window.history.go(-1);
        //window.parent.closeIFrame();
    </script>
    <?php
    mensagem("Verificação de homologação realizada com sucesso!");
}

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();



/* ************************************************************************ *
 *                                                                          *
 *                          FUNÇÕES COMPLEMENTARES                          *
 *                                                                          *
 * ************************************************************************ */

/*
 * @info Carrega os grupos de ocorrência
 *
 * @param string $mat Matrícula do servidor
 * @return array Ocorrências
 */
function carregaGruposOcorrencias($mat)
{
    $array = array();

    $oDBase = selecionaServidor($mat);
    $sitcad = $oDBase->fetch_object()->sigregjur;

    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $array['codigoSemFrequenciaPadrao']   = $obj->CodigoSemFrequenciaPadrao($sitcad);
    $array['codigoRegistroParcialPadrao'] = $obj->CodigoRegistroParcialPadrao($sitcad);
    $array['codigosTrocaObrigatoria']     = $obj->CodigosTrocaObrigatoria($sitcad);
    $array['grupoOcorrenciasFerias']      = $obj->GrupoOcorrenciasFerias($sitcad);
    $array['codigosDebitos']              = $obj->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios = true);
    $array['passiveisDeAbono']            = $obj->GrupoOcorrenciasPassiveisDeAbono($sitcad);
    $array['codigoServicoExternoPadrao']  = $obj->CodigoServicoExternoPadrao($sitcad);

    return $array;
}
