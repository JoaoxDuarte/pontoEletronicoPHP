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

// dados do operador
$sMatricula = $_SESSION["sMatricula"];
$ip         = getIpReal(); //linha que captura o ip do usuario.


## classe para montagem do formulario padrao
#
$oFormP     = new formPadrao;
$oFormP->setJS(_DIR_JS_ . 'desativa_teclas_f.js');
$oFormP->setSeparador(30);
//$oForm->setLogoExibe( true );
## Topo do formulário
#
$oFormP->exibeTopoHTML();
$oFormP->exibeCorpoTopoHTML();


/* ---------------------------------------------\
  |                                              |
  |   MODO: 7  -  grava registro de ocorrência   |
  |               pela chefia                    |
  |                                              |
  \--------------------------------------------- */
if ($modo == "7")
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

    $phpOrigem = $_SESSION['sHOrigem_3'];

    $nome_do_arquivo = (empty($_SESSION['sHArquivo']) ? "ponto$comp" : $_SESSION['sHArquivo'] );


    $oDBase = selecionaServidor($mat);
    $sitcad = $oDBase->fetch_object()->sigregjur;

    // ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $codigoServicoExternoPadrao = $obj->CodigoServicoExternoPadrao($sitcad);


    // passados por formulario
    $ocor  = anti_injection($_REQUEST['ocor']); // codigo da ocorrencia
    $he    = anti_injection($_REQUEST['entra']); // entrada
    $hie   = anti_injection($_REQUEST['iniint']); // saida para o almoco
    $his   = anti_injection($_REQUEST['fimint']); // retorno do almoco
    $hs    = anti_injection($_REQUEST['hsaida']); // fim do expediente
    ##
    # instancia o formulario para uso
    # das funcoes de frequencia
    #
		$oForm = new formFrequencia;
    $oForm->setDestino($phpOrigem);
    $oForm->setData($dia); // ano (data atual)
    $oForm->setAnoHoje(date('Y')); // ano (data atual)
    $oForm->setUsuario($_SESSION['sMatricula']);  // matricula do usuario
    $oForm->setData($dia);    // data informada
    //$oForm->setLotacao( $lot ); // lotação do servidor que se deseja alterar a frequencia
    $oForm->setSiape($mat); // matricula do servidor que se deseja alterar a frequencia
    $oForm->setMes($mes); // mes que se deseja alterar a frequencia
    $oForm->setAno($ano); // ano que se deseja alterar a frequencia
    $oForm->setNomeDoArquivo($nome_do_arquivo); // nome do arquivo de trabalho, neste caso, o temporario
    $oForm->loadDadosServidor();
    $oForm->loadDadosSetor();
    $oForm->pontoFacultativo();
    $oForm->verificaSeDiaUtil();

    # ocorrencia
    #
		$oForm->setCodigoOcorrencia($ocor);

    # horários informados
    #
		# horários informados
    #
		$oForm->setEntrada($he);
    $oForm->setSaida($hs);
    $oForm->setInicioIntervalo($hie);
    $oForm->setFimIntervalo($his);

    $oForm->setRegistroServidor('N');

    // verifica periodo do recesso
    $oForm->verificaPeriodoDoRecesso();

    # valida a ocorrencia e horários informados
    #
		$oForm->validaParametros(0); // ocorrencia, entrada, saida para o almoco, retorno do almoco e fim do expediente

    $jp    = $oForm->getJornada();
    $dutil = $oForm->getDiaUtil();

    // cálculo - horas do dia
    $oResultado = $oForm->processaOcorrencias();
    //$oco  = $oResultado->ocorrencia;
    $jdia       = $oResultado->jornada_realizada;
    $jp         = $oResultado->jornada_prevista;
    $dif        = $oResultado->jornada_diferenca;

    // carrega os horários após validação
    $he  = $oResultado->entra;
    $hs  = $oResultado->sai;
    $hie = $oResultado->intini;
    $his = $oResultado->intsai;

    $idReg = define_quem_registrou($lot);

    // instancia a base de dados
    $oDBase = new DataBase('PDO');

    // abrimos o banco de dados
    $oDBase->setDestino($phpOrigem); // se houver algum erro redireciona para o destino indica
    $oDBase->setMensagem("Falha na alteracao do registro do ponto.");

    if (in_array($ocor, $codigoServicoExternoPadrao)) //'22222'
    {
        $dif = '00:00';
    }

    $oDBase->query("UPDATE $nome_do_arquivo SET entra='$he', intini='$hie', intsai='$his', sai='$hs', jorndif='$dif', jornd='$jdia', jornp='$jp', oco='$ocor', idreg='$idReg', iprh='$ip', siaperh='$sMatricula'" . (substr_count($nome_do_arquivo, 'historico_temp_') > 0 ? ", acao_executada='A' " : " ") . "WHERE siape = '$mat' AND dia = '$diac' ");

    mensagem("Ocorrência alterada com sucesso!", $_SESSION['sHOrigem_2']);
}

/* ---------------------------------------------\
  |                                              |
  |   MODO: 8  -  grava registro de ocorrência   |
  |                                              |
  |                                              |
  \--------------------------------------------- */
elseif ($modo == "8")
{

    // parametro passado por formulario
    $observacao = trata_aspas($_REQUEST["observacao"]);

    // dados em sessao
    $mat     = $_SESSION['sHSiape'];
    $mes     = $_SESSION['sHMes']; // mes
    $ano     = $_SESSION['sHAno']; // ano
    $comp    = $mes . $ano; // competencia (mmaaaa)
    $compinv = $ano . $mes;

    // data atual
    $dthomol = date("Y-m-d");

    // marcação: Homologação (Chefia) ou Verificação (RH)
    $freqh = 'V';

    // insstancia a base de dados
    $oDBase = new DataBase('PDO');

    // grava o texto (observacao)
    $oDBase->setDestino($phpOrigem);
    $oDBase->setMensagem("Falha na leitura da observação (histórico)");
    $oDBase->query("SELECT * FROM historico_observacoes WHERE siape = :siape AND compet = :compet ", array(
        array(":compet", $compinv, PDO::PARAM_STR),
        array(":siape", $mat, PDO::PARAM_STR)
    ));
    $rows = $oDBase->num_rows();

    if ($rows == 0)
    {
        $oDBase->setMensagem("Falha na inclusão da observação (histórico)");
        $oDBase->query("INSERT INTO historico_observacoes (compet, siape, observacao, ip, siaperh, registrado_em) VALUES ( :compet, :siape, :observacao, :ip, :siaperh, now() ) ", array(
            array(":compet", $compinv, PDO::PARAM_STR),
            array(":siape", $mat, PDO::PARAM_STR),
            array(":observacao", $observacao, PDO::PARAM_STR),
            array(":ip", $ip, PDO::PARAM_STR),
            array(":siaperh", $sMatricula, PDO::PARAM_STR)
        ));
    }
    else
    {
        $oDBase->setMensagem("Falha na gravação da observação (histórico)");
        $oDBase->query("UPDATE historico_observacoes SET compet = :compet, siape = :siape, observacao = :observacao, ip = :ip, siaperh = :siaperh, registrado_em = now() WHERE compet = :compet and siape = :siape ", array(
            array(":compet", $compinv, PDO::PARAM_STR),
            array(":siape", $mat, PDO::PARAM_STR),
            array(":observacao", $observacao, PDO::PARAM_STR),
            array(":ip", $ip, PDO::PARAM_STR),
            array(":siaperh", $sMatricula, PDO::PARAM_STR),
            array(":compet", $compinv, PDO::PARAM_STR),
            array(":siape", $mat, PDO::PARAM_STR)
        ));
    }

    // registra a verifcação no banco de historico da homologacao
    $oDBase->setMensagem("Falha no registro da homologação (histórico)");
    $oDBase->query("SELECT * FROM homologados WHERE mat_siape = :mat_siape and compet = :compet ", array(
        array(":mat_siape", $mat, PDO::PARAM_STR),
        array(":compet", $compinv, PDO::PARAM_STR)
    ));
    $rowsh = $oDBase->num_rows();

    if ($rowsh == 0)
    {
        $oDBase->query("INSERT INTO homologados (homologado, mat_siape, homologado, homologado_siape, homologado_data) VALUES ( :homologado, :mat_siape, :homologado, :homologado_siape, :homologado_data) ", array(
        array(":homologado", $compinv, PDO::PARAM_STR),
        array(":mat_siape", $mat, PDO::PARAM_STR),
        array(":homologado", $freqh, PDO::PARAM_STR),
        array(":homologado_siape", $sMatricula, PDO::PARAM_STR),
        array(":homologado_data", $dthomol, PDO::PARAM_STR),
    ));
    }
    else
    {
        $oDBase->query("UPDATE homologados SET homologado = :homologado, homologado_siape = :homologado_siape, homologado_data = :homologado_data WHERE compet = :compet and mat_siape = :mat_siape ",array(
            array(":homologado", $freqh, PDO::PARAM_STR),
            array(":homologado_siape", $sMatricula, PDO::PARAM_STR),
            array(":homologado_data", $dthomol, PDO::PARAM_STR),
            array(":compet", $compinv, PDO::PARAM_STR),
            array(":mat_siape", $mat, PDO::PARAM_STR),
        ));
    }

    ##
    # instancia o formulario para uso
    # das funcoes de frequencia
    #
		$oForm = new formFrequencia;
    $oForm->setSiape($mat); // matricula do servidor que se deseja alterar a frequencia
    $oForm->setMes($mes); // mes que se deseja alterar a frequencia
    $oForm->setAno($ano); // ano que se deseja alterar a frequencia

    $oForm->copiaTemporarioParaPonto();

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->query("INSERT INTO control_historico SET ip='" . $ip . "', siape_rh='" . $_SESSION['sHUsuario'] . "', lotacao='" . $_SESSION['sLotacao'] . "', siape='" . $_SESSION['sHSiape'] . "', compet='" . $_SESSION['sHAno'] . $_SESSION['sHMes'] . "', operacao='Gravou alteracoes.', datahora=NOW() ");

    // indica atualiza ficha de frequencia (FRQano)
    //atualiza_frqANO($mat, $mes, $ano, $_SESSION['sHOrigem_4'], false, true);
    $oDBase->query("UPDATE usuarios SET recalculo='S', refaz_frqano='S' WHERE siape = :siape ", array(
            array(":siape", $mat, PDO::PARAM_STR))
    );

    mensagem("Operação realizada com sucesso!", $_SESSION['sHOrigem_1']);
}


## Base do formulário
#
$oFormP->exibeCorpoBaseHTML();
$oFormP->exibeBaseHTML();
