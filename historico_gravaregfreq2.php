<?php

// Inicia a sessão e carrega as funções de uso geral
include_once( "config.php");
include_once("class_form.frequencia.php");
include_once( "class_ocorrencias_grupos.php" );

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH e sTabServidor');

// arquivo origem
$phpOrigem = $_SESSION['sHOrigem_4'];

// parametro passado por formulario
$modo = anti_injection($_REQUEST['modo']);
$mat  = anti_injection($_REQUEST['mat']);

// dados do operador
$sMatricula = $_SESSION["sMatricula"];
$ip         = getIpReal(); //linha que captura o ip do usuario.


## classe para montagem do formulario padrao
#
$oFormP     = new formPadrao;
$oFormP->setCSS(_DIR_CSS_ . 'estilos.css');
$oFormP->setJS(_DIR_JS_ . 'desativa_teclas_f.js');
$oFormP->setSeparador(30);
//$oForm->setLogoExibe( true );
## Topo do formulário
#
$oFormP->exibeTopoHTML();
$oFormP->exibeCorpoTopoHTML();


## Base do formulário
#
$oFormP->exibeCorpoBaseHTML();
$oFormP->exibeBaseHTML();


/* ---------------------------------------------\
  |                                              |
  |   MODO: 2                                    |
  |                                              |
  \--------------------------------------------- */
if ($modo == "2") //
{

    /* Recebendo as variaveis do formulario */
    $dia    = $_REQUEST['dia'];
    $mat    = anti_injection($_REQUEST['mat']);
    $entra  = anti_injection($_REQUEST['entra']); // entrada
    $iniint = anti_injection($_REQUEST['iniint']); // saida para o almoco
    $fimint = anti_injection($_REQUEST['fimint']); // retorno do almoco
    $hsaida = anti_injection($_REQUEST['hsaida']); // fim do expediente
    $jnd    = anti_injection($_REQUEST['jnd']);
    $tipo   = explode(':', $jnd);
    if (count($tipo) == 1)
    {
        $jnc = $jnd / 5;
        $jnc = "0" . $jnc . ":00";
    }
    else
    {
        $jnc = $jnd;
    }
    $diac = conv_data($dia);
    $mes  = substr($diac, 5, 2);
    $ano  = substr($diac, 0, 4);
    $comp = (empty($_REQUEST['compete']) ? $mes . $ano : anti_injection($_REQUEST['compete']));
    $cmd  = (empty($_REQUEST['cmd']) ? $modo : anti_injection($_REQUEST['cmd']));
    $ocor = anti_injection($_REQUEST['ocor']);
    $lot  = $_SESSION["sLotacao"];

    $nome_do_arquivo = (empty($_SESSION['sHArquivo']) ? "ponto$comp" : $_SESSION['sHArquivo'] );

    $he  = $entra;
    $hs  = $hsaida;
    $hie = $iniint;
    $his = $fimint;
    $jp  = $jnc;

    ////////////////////////////
    //
		// Solução temporária até normalizar e unificar
    // o tratamento da jornada
    //
		////////////////////////////
    // verifica autorizacao
    $oJornadaTE = new DefinirJornada;
    $oJornadaTE->setSiape($mat);
    $oJornadaTE->setData($dia);
    $oJornadaTE->leDadosServidor();

    $oDBase   = $oJornadaTE->PesquisaJornadaHistorico($mat, $dia);
    $oJornada = $oDBase->fetch_object();

    $jornadaTE = $oJornada->jornada;

    //
    ////////////////////////////
    ##
    # instancia o formulario para uso
    # das funcoes de frequencia
    #
		$oForm = new formFrequencia;
    $oForm->setData($dia); // ano (data atual)
    $oForm->setAnoHoje(date('Y')); // ano (data atual)
    $oForm->setUsuario($_SESSION['sHUsuario']);  // matricula do usuario
    $oForm->setSiape($mat); // matricula do servidor que se deseja alterar a frequencia
    $oForm->setMes($mes); // mes que se deseja alterar a frequencia
    $oForm->setAno($ano); // ano que se deseja alterar a frequencia
    $oForm->setNomeDoArquivo($nome_do_arquivo); // nome do arquivo de trabalho, neste caso, o temporario

    $oForm->setJornada($jornadaTE);

    # ocorrencia
    #
		$oForm->setCodigoOcorrencia($ocor);

    # horários informados
    #
		$oForm->setEntrada($he);
    $oForm->setSaida($hs);
    $oForm->setInicioIntervalo($hie);
    $oForm->setFimIntervalo($his);

    # valida a ocorrencia e horários informados
    # Verificando se a jornada do dia é inferior a jornada prevista (do cargo)
    #
		$oForm->setDestino($phpOrigem);
    $oForm->setRegistroServidor("N");
    $oForm->validaParametros(2); // ocorrencia, entrada, saida para o almoco, retorno do almoco e fim do expediente
    //rotina para definira a matricula do usuario a ser gravada face o perfil RH ou CH
    if (($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N") && ($_SESSION['sHOrigem_1'] != 'historico_frequencia.php'))
    {
        $siapeCH = $sMatricula;
        $siapeRH = '';
        $ipCH    = $ip;
        $ipRH    = '';
        $idreg   = 'C';
    }
    elseif (($_SESSION['sRH'] == "S"))
    {
        $siapeCH = '';
        $siapeRH = $sMatricula;
        $ipCH    = '';
        $ipRH    = $ip;
        $idreg   = ($_SESSION['sHOrigem_1'] == 'historico_frequencia.php' ? 'H' : 'R');
    }

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    //Implementar busca para saber se já ocorreu o registro de entrada no dia
    $oDBase->query("SELECT * FROM $nome_do_arquivo WHERE dia='$diac' and siape='$mat' ");
    $rows = $oDBase->num_rows();

    if ($rows == 0)
    {
        // Inclusão do registro
        $oDBase->query("INSERT INTO $nome_do_arquivo SET dia='$diac', siape='$mat', entra='$he', intini='$hie', intsai='$his', sai='$hs', jorndif='" . $oForm->getHorasCalculada(3) . "', jornd='" . $oForm->getHorasCalculada(1) . "', jornp='" . $oForm->getHorasCalculada(2) . "', oco='$ocor', idreg='$idreg', ipch='$ipCH', iprh='$ipRH', matchef='$siapeCH', siaperh='$siapeRH'" . ($nome_do_arquivo == 'historico_temporario' ? ", acao_executada='I' " : " "));
        mensagem("Ocorrência registrada com sucesso!", _SESSION['sHOrigem_2']);
    }
    else
    {
        // Alteração do registro
        $oDBase->query("UPDATE $nome_do_arquivo SET entra='$he', intini='$hie', intsai='$his', sai='$hs', jorndif='" . $oForm->getHorasCalculada(3) . "', jornd='" . $oForm->getHorasCalculada(1) . "', jornp='" . $oForm->getHorasCalculada(2) . "', oco='$ocor', idreg='$idreg', ipch='$ipCH', iprh='$ipRH', matchef='$siapeCH', siaperh='$siapeRH'" . ($nome_do_arquivo == 'historico_temporario' ? ", acao_executada='I'" : "") . " WHERE siape='$mat' and dia='$diac' ");
        mensagem("Atualização registrada com sucesso!", $_SESSION['sHOrigem_2']);
    }
}

/* ---------------------------------------------\
  |                                              |
  |   MODO: 3                                    |
  |                                              |
  \--------------------------------------------- */
elseif ($modo == "3")
{

    // Valores passados - encriptados
    // Recebe os dados: mat, dia, nome, lot, idreg, c, oco
    $dadosorigem = $_REQUEST['dados'];
    $dadosorigem = (empty($dadosorigem) ? $_SESSION['sDadosC'] : $dadosorigem);

    /* Recebendo as variaveis do formulario */
    $dados      = explode(":|:", base64_decode($dadosorigem));
    $dia        = $dados[0];
    $nome       = $dados[1];
    $ocor_antes = $dados[2];

    $cmd = $_SESSION['sHCmd'];

    $mat  = $_SESSION['sHSiape'];
    $mes  = $_SESSION['sHMes']; // mes
    $ano  = $_SESSION['sHAno']; // ano
    $comp = $mes . $ano; // competencia (mmaaaa)

    $diac = conv_data($dia);

    $sMatricula = $_SESSION["sMatricula"];
    $ip         = getIpReal(); //linha que captura o ip do usuario.

    $phpOrigem = $_SESSION['sHOrigem_4'];

    $nome_do_arquivo = (empty($_SESSION['sHArquivo']) ? "ponto$comp" : $_SESSION['sHArquivo'] );

    // passados por formulario
    $ocor = anti_injection($_REQUEST['ocor']); // codigo da ocorrencia
    $he   = anti_injection($_REQUEST['entra']); // entrada
    $hie  = anti_injection($_REQUEST['iniint']); // saida para o almoco
    $his  = anti_injection($_REQUEST['fimint']); // retorno do almoco
    $hs   = anti_injection($_REQUEST['hsaida']); // fim do expediente

    /*
      if ($he=='00:00:00' && substr_count("00000.:.00128", $ocor) > 0)
      {
      $he = $oForm->getCadastroEntrada();
      $hs = $oForm->getCadastroSaida();
      $hie = $oForm->getCadastroInicioIntervalo();
      $his = $oForm->getCadastroFimIntervalo();
      }
     */

    ////////////////////////////
    //
		// Solução temporária até normalizar e unificar
    // o tratamento da jornada
    //
		////////////////////////////
    // verifica autorizacao
    $oJornadaTE = new DefinirJornada;
    $oJornadaTE->setSiape($mat);
    $oJornadaTE->setData($dia);
    $oJornadaTE->leDadosServidor();

    $oDBase   = $oJornadaTE->PesquisaJornadaHistorico($mat, $dia);
    $oJornada = $oDBase->fetch_object();

    $jornadaTE = $oJornada->jornada;

    //
    ////////////////////////////
    ##
    # instancia o formulario para uso
    # das funcoes de frequencia
    #
		$oForm = new formFrequencia;
    $oForm->setData($dia); // ano (data atual)
    $oForm->setAnoHoje(date('Y')); // ano (data atual)
    $oForm->setUsuario($_SESSION['sHUsuario']);  // matricula do usuario
    $oForm->setSiape($mat); // matricula do servidor que se deseja alterar a frequencia
    $oForm->setMes($mes); // mes que se deseja alterar a frequencia
    $oForm->setAno($ano); // ano que se deseja alterar a frequencia
    $oForm->setNomeDoArquivo($nome_do_arquivo); // nome do arquivo de trabalho, neste caso, o temporario

    $oForm->setJornada($jornadaTE);

    # ocorrencia
    #
		$oForm->setCodigoOcorrencia($ocor);

    # horários informados
    #
		$oForm->setEntrada($he);
    $oForm->setSaida($hs);
    $oForm->setInicioIntervalo($hie);
    $oForm->setFimIntervalo($his);

    # valida a ocorrencia e horários informados
    #
		$oForm->setDestino($phpOrigem);
    $oForm->setRegistroServidor("N");
    $oForm->validaParametros(1); // ocorrencia, entrada, saida para o almoco, retorno do almoco e fim do expediente
    //rotina para definira a matricula do usuario a ser gravada face o perfil RH ou CH
    if (($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N") && ($_SESSION['sHOrigem_1'] != 'historico_frequencia.php'))
    {
        $siapeCH = $sMatricula;
        $siapeRH = '';
        $ipCH    = $ip;
        $ipRH    = '';
        $idreg   = 'C';
    }
    elseif (($_SESSION['sRH'] == "S"))
    {
        $siapeCH = '';
        $siapeRH = $sMatricula;
        $ipCH    = '';
        $ipRH    = $ip;
        $idreg   = ($_SESSION['sHOrigem_1'] == 'historico_frequencia.php' ? 'H' : 'R');
    }

    //Implementar busca para saber se já ocorreu o registro de entrada no dia
    $oDBase = new DataBase('PDO');

    // Alteração do registro
    $oDBase->query("UPDATE $nome_do_arquivo SET entra='$he', intini='$hie', intsai='$his', sai='$hs', jorndif='" . ($ocor == '00000' ? '00:00' : $oForm->getHorasCalculada(3)) . "', jornd='" . $oForm->getHorasCalculada(1) . "', jornp='" . $oForm->getHorasCalculada(2) . "', oco='$ocor', idreg='$idreg', ipch='$ipCH', iprh='$ipRH', matchef='$siapeCH', siaperh='$siapeRH'" . ($nome_do_arquivo == 'historico_temporario' ? ", acao_executada='I' " : " ") . " WHERE siape='$mat' and dia='$diac' ");
    mensagem("Atualização registrada com sucesso!", $_SESSION['sHOrigem_2']);
}

/* ---------------------------------------------\
  |                                              |
  |   MODO: 4                                    |
  |                                              |
  \--------------------------------------------- */
elseif ($modo == "4")
{

    // Valores passados - encriptados
    // Recebe os dados: mat, dia, nome, lot, idreg, c, oco
    $dadosorigem = $_REQUEST['dados'];
    $dadosorigem = (empty($dadosorigem) ? $_SESSION['sDadosC'] : $dadosorigem);

    /* Recebendo as variaveis do formulario */
    $dados      = explode(":|:", base64_decode($dadosorigem));
    $dia        = $dados[0];
    $nome       = $dados[1];
    $ocor_antes = $dados[2];

    $cmd = $_SESSION['sHCmd'];

    $mat  = $_SESSION['sHSiape'];
    $mes  = $_SESSION['sHMes']; // mes
    $ano  = $_SESSION['sHAno']; // ano
    $comp = $mes . $ano; // competencia (mmaaaa)

    $diac = conv_data($dia);

    $sMatricula = $_SESSION["sMatricula"];
    $ip         = getIpReal(); //linha que captura o ip do usuario.

    $phpOrigem = $_SESSION['sHOrigem_4'];

    $nome_do_arquivo = (empty($_SESSION['sHArquivo']) ? "ponto$comp" : $_SESSION['sHArquivo'] );


    $oDBase = selecionaServidor($mat);
    $sitcad = $oDBase->fetch_object()->sigregjur;

    // ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $grupoOcorrenciasNegativasDebitos = $obj->GrupoOcorrenciasNegativasDebitos($sitcad);
    $codigosLicençaComRemuneracao     = $obj->CodigosLicençaComRemuneracao($sitcad);
    $codigoServicoExternoPadrao       = $obj->CodigoServicoExternoPadrao($sitcad);


    // passados por formulario
    //$dia2 = $_POST["dia2"];
    //$dia = $_POST["dia"];
    $ocor       = anti_injection($_REQUEST['ocor']); // codigo da ocorrencia
    $he         = anti_injection($_REQUEST['entra']); // entrada
    $hie        = anti_injection($_REQUEST['iniint']); // saida para o almoco
    $his        = anti_injection($_REQUEST['fimint']); // retorno do almoco
    $hs         = anti_injection($_REQUEST['hsaida']); // fim do expediente

    ////////////////////////////
    //
		// Solução temporária até normalizar e unificar
    // o tratamento da jornada
    //
		////////////////////////////
    // verifica autorizacao
    $oJornadaTE = new DefinirJornada;
    $oJornadaTE->setSiape($mat);
    $oJornadaTE->setData(databarra($dia));
    $oJornadaTE->leDadosServidor();

    $oDBase   = $oJornadaTE->PesquisaJornadaHistorico($mat, databarra($dia));
    $oJornada = $oDBase->fetch_object();

    $jornadaTE = $oJornada->jornada;

    //
    ////////////////////////////

    ##
    # instancia o formulario para uso
    # das funcoes de frequencia
    #
		$oForm = new formFrequencia;
    $oForm->setData($dia); // ano (data atual)
    $oForm->setAnoHoje(date('Y')); // ano (data atual)
    $oForm->setUsuario($_SESSION['sHUsuario']);  // matricula do usuario
    $oForm->setSiape($mat); // matricula do servidor que se deseja alterar a frequencia
    $oForm->setMes($mes); // mes que se deseja alterar a frequencia
    $oForm->setAno($ano); // ano que se deseja alterar a frequencia
    $oForm->setNomeDoArquivo($nome_do_arquivo); // nome do arquivo de trabalho, neste caso, o temporario
    # ocorrencia
    #
		$oForm->setCodigoOcorrencia($ocor);

    // le dados do servidor e setor
    $oForm->loadDadosServidor();
    $oForm->loadDadosSetor();

    $oForm->setJornada($jornadaTE);

    $dia1 = dataDia($diac);
    $dia2 = dataDia($diac);

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setMensagem("Falha na alteração do registro do ponto");

    for ($dia = $dia1; $dia <= $dia2; $dia++)
    {
        // registra dia para os cálculos
        $diac = $oForm->getAno() . '-' . $oForm->getMes() . '-' . substr('00' . $dia, -2);
        $oForm->setData($diac);

        // Verifica se é natal, ano novo ou quarta feira de cinzas.
        $oForm->pontoFacultativo();

        // Verifica se dia útil
        $oForm->verificaSeDiaUtil();

        $oForm->setEntrada($he);
        $oForm->setSaida($hs);
        $oForm->setInicioIntervalo($hie);
        $oForm->setFimIntervalo($his);

        // jornada
        $jnd = $oForm->getJornada();

        //obtendo dados do servidor
        $entra = $oForm->getEntrada();
        $sai   = $oForm->getSaida();
        $iniin = $oForm->getInicioIntervalo();
        $fimin = $oForm->getFimIntervalo();

        //rotina para definira a matricula do usuario a ser gravada face o perfil RH ou CH
        if (($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N") && ($_SESSION['sHOrigem_1'] != 'historico_frequencia.php'))
        {
            $siapeCH = $sMatricula;
            $siapeRH = '';
            $ipCH    = $ip;
            $ipRH    = '';
            $idreg   = 'C';
        }
        elseif (($_SESSION['sRH'] == "S"))
        {
            $siapeCH = '';
            $siapeRH = $sMatricula;
            $ipCH    = '';
            $ipRH    = $ip;
            $idreg   = ($_SESSION['sHOrigem_1'] == 'historico_frequencia.php' ? 'H' : 'R');
        }

        if (in_array($ocor, $grupoOcorrenciasNegativasDebitos)) // "00111.:.00129.:.00137.:.00167.:.00168.:.02323.:.55555.:.99999"
        {
            // zera os horários, registra jornada no dia igual a 00:00, jornada prevista igual a 08:00 e diferenca da jornada como 08:00 (negativo)
            $oDBase->query("UPDATE $nome_do_arquivo SET entra='00:00:00', intini='00:00:00', intsai='00:00:00', sai='00:00:00', jornd='00:00', jornp='$jnd', jorndif='$jnd', oco='$ocor', idreg='$idreg', ipch='$ipCH', iprh='$ipRH', matchef='$siapeCH', siaperh='$siapeRH'" . ($nome_do_arquivo == 'historico_temporario' ? ", acao_executada='A' " : " ") . "WHERE siape='$mat' and dia='$diac' ");
        }
        else if (in_array($ocor, $codigosLicençaComRemuneracao)) // "00146"
        {
            $oDBase->query("UPDATE $nome_do_arquivo SET entra='$he', intini='$hie', intsai='$his', sai='$hs', jornd='00:00', jornp='00:00', jorndif='00:00', oco='$ocor', idreg='$idreg', ipch='$ipCH', iprh='$ipRH', matchef='$siapeCH', siaperh='$siapeRH'" . ($nome_do_arquivo == 'historico_temporario' ? ", acao_executada='A' " : " ") . "WHERE siape='$mat' and dia='$diac' ");
        }
        else //if (in_array($ocor, $codigosLicençaComRemuneracao)) // "22222"
        {
            $oDBase->query("UPDATE $nome_do_arquivo SET entra='$he', intini='$hie', intsai='$his', sai='$hs', jornd='$jnd', jornp='$jnd', jorndif='00:00', oco='$ocor', idreg='$idreg', ipch='$ipCH', iprh='$ipRH', matchef='$siapeCH', siaperh='$siapeRH'" . ($nome_do_arquivo == 'historico_temporario' ? ", acao_executada='A' " : " ") . "WHERE siape='$mat' and dia='$diac' ");
        }

        mensagem("Ocorrência registrada com sucesso!", $_SESSION['sHOrigem_2']);
    }
}

/* ---------------------------------------------\
  |                                              |
  |   MODO: 4b                                   |
  |                                              |
  \--------------------------------------------- */
elseif ($modo == "4b")
{

    // Valores passados
    $cmd     = anti_injection($_REQUEST['cmd']);
    $mat     = anti_injection($_REQUEST['mat']);
    $mes     = anti_injection($_REQUEST['mes']);
    $ano     = anti_injection($_REQUEST['ano']);
    $dia_ini = $_REQUEST['dia_ini'];
    $dia_fim = $_REQUEST['dia_fim'];
    $comp    = $mes . $ano; // competencia (mmaaaa)

    $jnd           = anti_injection($_REQUEST['jnd']);
    $jd2           = anti_injection($_REQUEST['jd2']);
    $nome          = anti_injection($_REQUEST['nome']);
    $ocor          = anti_injection($_REQUEST['ocor']);
    $dutil         = anti_injection($_REQUEST['dutil']);
    $compete       = anti_injection($_REQUEST['compete']);
    $jornada_cargo = anti_injection($_REQUEST['jornada_cargo']);

    $diac = $ano . '-' . $mes . '-01';

    $sMatricula = $_SESSION["sMatricula"];
    $ip         = getIpReal(); //linha que captura o ip do usuario.

    $phpOrigem = $_SESSION['sHOrigem_3'];

    $nome_do_arquivo = (empty($_SESSION['sHArquivo']) ? "ponto$comp" : $_SESSION['sHArquivo'] );


    $oDBase = selecionaServidor($mat);
    $sitcad = $oDBase->fetch_object()->sigregjur;

    // ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $grupoOcorrenciasNegativasDebitos = $obj->GrupoOcorrenciasNegativasDebitos($sitcad);
    $codigosLicençaComRemuneracao     = $obj->CodigosLicençaComRemuneracao($sitcad);
    $codigoServicoExternoPadrao       = $obj->CodigoServicoExternoPadrao($sitcad);


    //rotina para definira a matricula do usuario a ser gravada face o perfil RH ou CH
    if (($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N") && ($_SESSION['sHOrigem_1'] != 'historico_frequencia.php'))
    {
        $siapeCH = $sMatricula;
        $siapeRH = '';
        $ipCH    = $ip;
        $ipRH    = '';
        $idreg   = 'C';
    }
    elseif (($_SESSION['sRH'] == "S"))
    {
        $siapeCH = '';
        $siapeRH = $sMatricula;
        $ipCH    = '';
        $ipRH    = $ip;
        $idreg   = ($_SESSION['sHOrigem_1'] == 'historico_frequencia.php' ? 'H' : 'R');
    }

    ////////////////////////////
    //
		// Solução temporária até normalizar e unificar
    // o tratamento da jornada
    //
		////////////////////////////
    // verifica autorizacao
    $oJornadaTE = new DefinirJornada;
    $oJornadaTE->setSiape($mat);
    $oJornadaTE->setData(databarra($diac));
    $oJornadaTE->leDadosServidor();

    $oDBase   = $oJornadaTE->PesquisaJornadaHistorico($mat, databarra($diac));
    $oJornada = $oDBase->fetch_object();

    $jornadaTE = $oJornada->jornada;

    //
    ////////////////////////////
    ##
    # instancia o formulario para uso
    # das funcoes de frequencia
    #
		$oForm = new formFrequencia;
    $oForm->setData($diac); // ano (data atual)
    $oForm->setAnoHoje(date('Y')); // ano (data atual)
    $oForm->setUsuario($_SESSION['sHUsuario']);  // matricula do usuario
    $oForm->setSiape($mat); // matricula do servidor que se deseja alterar a frequencia
    $oForm->setMes($mes); // mes que se deseja alterar a frequencia
    $oForm->setAno($ano); // ano que se deseja alterar a frequencia
    $oForm->setNomeDoArquivo($nome_do_arquivo); // nome do arquivo de trabalho, neste caso, o temporario
    # ocorrencia
    #
		$oForm->setCodigoOcorrencia($ocor);

    // le dados do servidor e setor
    $oForm->loadDadosServidor();
    $oForm->loadDadosSetor();

    $oForm->setJornada($jornadaTE);

    //obtendo dados do servidor
    $he  = $oJornada->entra_trab;
    $hs  = $oJornada->ini_interv;
    $hie = $oJornada->sai_interv;
    $his = $oJornada->sai_trab;

    $dia1 = $dia_ini;
    $dia2 = $dia_fim;

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setMensagem("Falha na alteração do registro do ponto");

    for ($dia = $dia1; $dia <= $dia2; $dia++)
    {
        // registra dia para os cálculos
        $diac = $oForm->getAno() . '-' . $oForm->getMes() . '-' . substr('00' . $dia, -2);
        $oForm->setData($diac);

        // Verifica se é natal, ano novo ou quarta feira de cinzas.
        $oForm->pontoFacultativo();

        // Verifica se dia útil
        $oForm->verificaSeDiaUtil();

        // jornada
        $jnd = $oForm->getJornada();

        if (in_array($ocor, $grupoOcorrenciasNegativasDebitos)) //"00111.:.00129.:.00137.:.00167.:.00168.:.02323.:.02525.:.55555.:.99999"
        {
            // zera os horários, registra jornada no dia igual a 00:00, jornada prevista igual a 08:00 e diferenca da jornada como 08:00 (negativo)
            $oDBase->query("UPDATE $nome_do_arquivo SET entra='00:00:00', intini='00:00:00', intsai='00:00:00', sai='00:00:00', jornd='00:00', jornp='$jnd', jorndif='$jnd', oco='$ocor', idreg='$idreg', ipch='$ipCH', iprh='$ipRH', matchef='$siapeCH', siaperh='$siapeRH'" . ($nome_do_arquivo == 'historico_temporario' ? ", acao_executada='A' " : " ") . "WHERE siape='$mat' and dia='$diac' ");
        }
        else if (in_array($ocor, $codigosLicençaComRemuneracao)) // "00146"
        {
            $oDBase->query("UPDATE $nome_do_arquivo SET entra='$he', intini='$hie', intsai='$his', sai='$hs', jornd='00:00', jornp='00:00', jorndif='00:00', oco='$ocor', idreg='$idreg', ipch='$ipCH', iprh='$ipRH', matchef='$siapeCH', siaperh='$siapeRH'" . ($nome_do_arquivo == 'historico_temporario' ? ", acao_executada='A' " : " ") . "WHERE siape='$mat' and dia='$diac' ");
        }
        else //if (in_array($ocor, $codigoServicoExternoPadrao)) //"22222"
        {
            $oDBase->query("UPDATE $nome_do_arquivo SET entra='$he', intini='$hie', intsai='$his', sai='$hs', jornd='$jnd', jornp='$jnd', jorndif='00:00', oco='$ocor', idreg='$idreg', ipch='$ipCH', iprh='$ipRH', matchef='$siapeCH', siaperh='$siapeRH'" . ($nome_do_arquivo == 'historico_temporario' ? ", acao_executada='A' " : " ") . "WHERE siape='$mat' and dia='$diac' ");
        }
    }

    mensagem("Ocorrência registrada com sucesso!", $_SESSION['sHOrigem_2']);
}
