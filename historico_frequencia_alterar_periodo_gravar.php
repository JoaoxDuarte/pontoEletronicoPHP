<?php

// conexao ao banco de dados
// funcoes diversas
include_once("config.php");
include_once("class_form.frequencia.php");
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao('sRH e sTabServidor');

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    // dados enviados por formulario
    $mat         = $_REQUEST["mat"];
    $dia2        = $_REQUEST["dia2"]; // dia inicial
    $dia         = $_REQUEST["dia"];  // dia final
    $cmd         = $_REQUEST["cmd"];
    $jnd         = $_REQUEST["jnd"]; // jornada de trabalho para o dia
    $compete     = $_REQUEST["compete"];
    $ocor        = $_REQUEST["ocor"];
    $lot         = $_REQUEST["lot"];
    $modo        = $_REQUEST["modo"];
    $dias_no_mes = $_REQUEST["dias_no_mes"];
}
else
{
    // Valores passados - encriptados
    $dados       = explode(":|:", base64_decode($dadosorigem));
    $mat         = $dados[0];
    $dia2        = $dados[1];
    $dia         = $dados[2];
    $cmd         = $dados[3];
    $jnd         = $dados[4]; // jornada de trabalho para o dia
    $compete     = $dados[5];
    $ocor        = $dados[6];
    $lot         = $dados[7];
    $modo        = $dados[8];
    $dias_no_mes = $dados[9];
}


$oDBase = selecionaServidor($mat);
$sitcad = $oDBase->fetch_object()->sigregjur;

## ocorrências grupos
$obj = new OcorrenciasGrupos();
$grupoOcorrenciasViagem  = $obj->GrupoOcorrenciasViagem($sitcad);


// variaveis de trabalho
$mes = substr($compete, 0, 2);
$ano = substr($compete, - 4);
$jnd = formata_jornada_para_hhmm($jnd);

$dt_ini = $dia2;
$dt_fim = $dia;

$nome_do_arquivo = $_SESSION['sHArquivoTemp'];
$pagina_anterior = "historico_frequencia_alterar_periodo.php?dados=" . $_SESSION['voltar_nivel_2'];

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

// validação
validacao_basica_dados_informados();

$dia1 = $ano . "-" . $mes . "-" . $dt_ini;
$dia2 = $ano . "-" . $mes . "-" . $dt_fim;

$mensagem = "Falha no registro das ocorrências!";

for ($diax = $dt_ini; $diax <= $dt_fim; $diax++)
{
    $dia = $ano . "-" . $mes . "-" . $diax;

    //Implementar busca para saber se já ocorreu o registro de entrada no dia
    $oDBase->setMensagem("Tabela de ponto inexistente!");
    $oDBase->query("
		SELECT
		pto.entra, pto.intini, pto.intsai, pto.sai, pto.oco, pto.idreg, IFNULL(pto.matchef,'') AS matchef, IFNULL(pto.siaperh,'') AS siaperh
		FROM
		" . $nome_do_arquivo . " AS pto
		WHERE
		pto.siape='$mat'
		AND pto.dia='$dia'
		");
    $oPonto  = $oDBase->fetch_object();
    $nRows   = $oDBase->num_rows();
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

    ##
    #  cálculo - horas do dia
    #
	$oResultado = $oFreq->processaOcorrencias();
    //$oco = $oResultado->ocorrencia;
    $jdia       = $oResultado->jornada_realizada;
    $jp         = $oResultado->jornada_prevista;
    $dif        = $oResultado->jornada_diferenca;

    // carrega os horários após validação
    $he  = $oResultado->entra;
    $hs  = $oResultado->sai;
    $hie = $oResultado->intini;
    $his = $oResultado->intsai;

    $idReg = 'H';

    if (in_array($ocor, $grupoOcorrenciasViagem)) //'00128'
    {
        $dif = '00:00';
    }

    $oDBase->setMensagem("Falha no registro do ponto! ");

    if ($nRows == 0)
    {
        $mensagem = "Ocorrência(s) registrada(s) com sucesso!";

        if ($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N")
        {
            $oDBase->query("INSERT INTO " . $nome_do_arquivo . " (dia, siape, oco, idreg, jornp, jorndif, iprh, siaperh) VALUES ('$dia', '$mat', '$ocor', '$jp', '$jp', 'H', '$ip', '$sMatricula') ");
        }
        else
        if ($_SESSION['sRH'] == "S")
        {
            $oDBase->query("INSERT INTO " . $nome_do_arquivo . " (dia, siape, oco, idreg, jornp, jorndif, iprh, siaperh) VALUES ('$dia', '$mat', '$ocor', '$jp', '$jp', 'H', '$ip', '$sMatricula') ");
        }
    }
    else
    {
        $mensagem = "Ocorrência(s) registrada(s) com sucesso!";
        //if ($idreg == 'S' ||
        //	 ($idreg == 'A' && $matchef != '' && $_SESSION['sAPS'] == 'S') ||
        //	 ($idreg == 'A' && $siaperh != '' && $_SESSION['sRH'] == 'S') ||
        //	 (substr_count('C.:.X',$idreg) > 0 && $_SESSION['sAPS'] == 'S') ||
        //	 (substr_count('R.:.H.:.X',$idreg) > 0 && $_SESSION['sRH'] == 'S'))
        //{
        //grava os dados anteriores
        gravar_historico_ponto($mat, $dia, 'A');

        if ($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N")
        {
            $oDBase->query("UPDATE " . $nome_do_arquivo . " SET dia='$dia', siape='$mat', entra='$he', intsai='$his', intini='$hie', sai='$hs', oco='$ocor', idreg='H', jornd='00:00', jornp='$jp', jorndif='$jp', iprh='$ip', siaperh='$sMatricula', acao_executada='A' WHERE dia = '$dia' AND siape='$mat' ");
        }
        else
        if ($_SESSION['sRH'] == "S")
        {
            $oDBase->query("UPDATE " . $nome_do_arquivo . " SET dia='$dia', siape='$mat', entra='$he', intsai='$his', intini='$hie', sai='$hs', oco='$ocor', idreg='H', jornd='00:00', jornp='$jp', jorndif='$jp', iprh='$ip', siaperh='$sMatricula', acao_executada='A' WHERE dia = '$dia' AND siape='$mat' ");
        }
        //}
        //else
        //{
        //	$mensagem_parcial .= ($mensagem_parcial == "" ? "Sucesso parcial no registro das Ocorrência(s)!\\nExiste(m) dias com ocorrências registradas por outro perfil de usuário!\\n\\nAbaixo dias que não foram alterados:\\n".databarra($dia) : "\\n".databarra($dia));
        //}
    }
}

mensagem(($mensagem_parcial == '' ? $mensagem : $mensagem_parcial), "historico_frequencia_registros.php?dados=" . $_SESSION['voltar_nivel_1']);



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
        else
        if (soNumeros($dt_ini) == 0)
        {
            $mensagem .= "Informe o Dia Início da Ocorrência!\\n";
        }
        else
        if (strlen(ltrim(trim($dt_ini))) == 1)
        {
            $mensagem .= "Informe o Dia Início da Ocorrência com dois dígitos!\\n";
        }

        // data de término da ocorrência
        else
        if (soNumeros($dt_fim) == 0)
        {
            $mensagem .= "Informe o Dia Fim da Ocorrência!\\n";
        }
        else
        if (strlen(ltrim(trim($dt_fim))) == 1)
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
        else
        if (soNumeros($dt_ini) == 0)
        {
            $mensagem .= "Dia Início da Ocorrência inválido!\\n";
        }
        else
        if (soNumeros($dt_ini) > soNumeros($dt_fim))
        {
            $mensagem .= "Dia Início da Ocorrência MAIOR QUE Dia Fim da Ocorrência!\\n";
        }

        mensagem($mensagem, $pagina_anterior);
    }

}
