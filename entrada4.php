<?php

// conexao ao banco de dados
// funcoes diversas
include_once("config.php");
include_once("class_form.frequencia.php");
include_once("hora_extra_autorizacao_funcoes.php");
include_once("class_ocorrencias_grupos.php");
include_once("src/controllers/DadosServidoresController.php");
include_once("src/controllers/TabBancoDeHorasAcumulosController.php");

verifica_permissao('logado', 'entrada.php');

//
// VERIFICA SE O ACESSO EH VIA ENTRADA.PHP
//
// Comentado temporariamente por n�o sabermos, de antem�o, os IPs da aplica��o
include_once('ilegal_entrada.php');

$sLotacao   = $_SESSION['sLotacao'];
$sMatricula = $_SESSION['sMatricula'];

$ini = $_SESSION['ini']; // inicio de funcionamento da unidade
$fim = $_SESSION['fim']; // fim de funcionamento da unidade

$sNome = $_SESSION['sNome'];  // nome do servidor

$entra = $_SESSION['entra'];  // hor�rio estabelecido de entrada ao servi�o
$sai   = $_SESSION['sai'];    // hor�rio estabelecido de sa�da (fim do expediente)
$iniin = $_SESSION['iniin'];  // hor�rio estabelecido do in�cio do almo�o
$fimin = $_SESSION['fimin'];  // hor�rio estabelecido do t�rmino do almo�o

$ent    = $_SESSION['ent'];    // hor�rio de entrada registrado pelo servidor
$iniint = $_SESSION['iniint']; // hor�rio de sa�da para o almo�o registrado pelo servidor
$fimint = $_SESSION['fimint']; // hor�rio de retorno do almo�o registrado pelo servidor
$saida  = $_SESSION['saida'];  // hor�rio de sa�da registrado pelo servidor

$autoriza = $_SESSION['autorizacao_dia_nao_util'];

$aut    = $_SESSION['aut'];    // autoriza��o da chefia imediata para entrada ou sa�da fora do hor�rio de funcionamento da unidade
$bhoras = $_SESSION['bhoras']; // autoriza��o da chefia imediata para compnesa��o de faltas justificadas, atrasos ou sa�das antecipadas
$horae  = $_SESSION['horae'];  // indica hor�rio especial - hor�rio de estudante obrigat�rio compensar as aus�ncias
$motivo = $_SESSION['motivo']; // motivo do hor�rio especial
$chefe  = $_SESSION['chefe'];  // indica se o servidor � chefe da unidade ou est� respondendo
$jnd    = $_SESSION['jnd'];    // jornada oficial do servidor
$codmun = $_SESSION['codmun']; // c�digo do munic�pio

$iniver = $_SESSION['iniver']; // in�cio do hor�rio de ver�o
$fimver = $_SESSION['fimver']; // t�rmino do hor�rio de ver�o

$_SESSION['registrar_justificativa'] = false; // Indica se o usu�rio pode registrar justificativa


// mensagens exibidas na finaliza��o
// do expeditente do servidor
//
$encerramento_mensagem = "";
$encerramento_aviso    = "";


// instancia class
$objOcorrenciasGrupos                 = new OcorrenciasGrupos();
$objDadosServidoresController         = new DadosServidoresController();
$objTabBancoDeHorasAcumulosController = new TabBancoDeHorasAcumulosController();

// situa��o cadastral
$sitcad = $objDadosServidoresController->getSigRegJur( $sMatricula );

// ocorr�ncias grupos
$codigosCompensaveis          = $objOcorrenciasGrupos->GrupoOcorrenciasNegativasDebitos( $sitcad, $exige_horarios=true );
$codigoCreditoPadrao          = $objOcorrenciasGrupos->CodigoCreditoPadrao( $sitcad );
$codigoFrequenciaNormalPadrao = $objOcorrenciasGrupos->CodigoFrequenciaNormalPadrao( $sitcad );
$codigoDebitoPadrao           = $objOcorrenciasGrupos->CodigoDebitoPadrao( $sitcad );


// definicao da competencia
$comp = date('mY');

$vDatas = date("Y-m-d");
$hoje   = date("d/m/Y");

$m = date("m");
$d = date("d");
$y = date("Y");


## Verifica se h� registro de:
#  - Frequ�ncia realizado neste dia;
#  - Hor�rio de fim do expediente j� realizado
#
VerificaRegistrosHorariosFrequenciaServidor(
    $sMatricula,     /* Matr�cula do Servidor Logado         */
    $vDatas,         /* Data atual de registro da frequ�ncia */
    'fim_expediente' /* Momento do registro                  */
);


## Verifica Hor�rio de ver�o e o Fuso Hor�rio
#  - Atribui o hor�rio da entrada a $vHoras, ap�s as verifica��es
#

$vHoras = horario_de_verao($vDatas);


// registra a hora em que efetivamente foi registrada a sa�da
// evitando o uso de 'refresh'.
if (empty($_SESSION['hora_frequencia_finalizada']))
{
    $_SESSION['hora_frequencia_finalizada'] = $vHoras;
}
else
{
    $vHoras = $_SESSION['hora_frequencia_finalizada'];
}

// Verifica se o horario registrado eh menor
// ou maior que o da tabela de setores
if (($vHoras > $fim) && ($aut == "N"))
{
    $idsaida = 1;
}
elseif (($vHoras > $fim) && ($aut == "S"))
{
    $idh = 1;
}
else
{
    $idh = 2;
}


// instancia o banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setDestino("entrada.php");


// verifica se h� hor�rio de sa�da
if (time_to_sec($saida) > 0)
{
    retornaErro('entrada.php', "J� consta registro de encerramento de expediente!");
}
else
{
    // obtendo dados  do dia para verificar se ocorreram os quatro registros
    $oDBase->setMensagem("Problemas no acesso a Tabela FREQU�NCIA (matr�cula ".removeOrgaoMatricula($sMatricula).", ".$m."/".$y.") (E000014.".__LINE__.").");
    $oDBase->query("
    SELECT
        a.siape, a.entra, a.sai, a.intini, a.intsai, a.jornd, a.jornp,
        IF(IFNULL(c.cod_lot,'SEM COD_LOT')=b.cod_lot,'SIM','NAO') AS liberado_apos_22hs,
        b.sigregjur
    FROM
        ponto$comp AS a
    LEFT JOIN
        servativ AS b ON a.siape = b.mat_siape
    LEFT JOIN
        liberacao_acesso_especial AS c ON (a.siape = c.siape AND b.cod_lot = c.cod_lot)
    WHERE
        a.dia = :vdatas
        AND a.siape = :siape
    ",
    array(
        array(':vdatas', $vDatas, PDO::PARAM_STR),
        array(':siape', $sMatricula, PDO::PARAM_STR),
    ));


    $oDados = $oDBase->fetch_object();

    $linha = $oDBase->num_rows();

    if ($linha == 0)
    {
        retornaErro('entrada.php', 'Erro de processamento. Por favor, repita a opera��o!');
    }
    else
    {
        $he     = $oDados->entra;
        $hsaida = $oDados->sai;
        $hs     = $vHoras;
        $hie    = $oDados->intini;
        $his    = $oDados->intsai;
        $jp     = $oDados->jornp;
        //$je  = $oDados->jornd;
        $sitcad = $oDados->sigregjur;

        if ((time_to_sec($hie) > 0 && time_to_sec($his) > 0) && time_to_sec($his) >= time_to_sec($hs))
        {
            retornaErro('entrada.php', "N�o � permitido registrar sa�da inferior ao retorno do intervalo!");
        }
        elseif ((time_to_sec($hie) == 0 && time_to_sec($his) == 0) && time_to_sec($he) >= time_to_sec($hs))
        {
            retornaErro('entrada.php', "N�o � permitido registrar sa�da inferior ao hor�rio de entrada!");
        }
        elseif (time_to_sec($hie) > 0 && time_to_sec($his) == 0)
        {
            retornaErro('entrada.php', "N�o � permitido registrar sa�da com retorno do intervalo em branco quando tiver ocorrido in�cio do intervalo!");
        }


        // limite de horario de entrada e saida do �rg�o
        $limites_inss = horariosLimiteINSS();

        // verifica se o hor�rio de entrada � menor que limite de entrada definido
        if (time_to_sec($vHoras) <= time_to_sec($limites_inss['entrada']['horario']))
        {
            $vHoras = $limites_inss['entrada']['horario'];
        }
        // fim da definicao da hora
        // limita a sa�da: INSS �s 22:00:00; ME �s 23:59:00
        if (time_to_sec($vHoras) >= time_to_sec($limites_inss['saida']['horario']) && ($oDados->liberado_apos_22hs != 'SIM'))
        {
            $hs     = $limites_inss['saida']['horario'] . ':00';
            $vHoras = $limites_inss['saida']['horario'] . ':00';
        }


        ## instancia classe frequencia
        # c�lculo das horas trabalhadas
        #
	$oFreq    = new formFrequencia;
        $oFreq->setOrigem('entrada.php'); // Registra informacoes em sessao
        $oFreq->setAnoHoje($hoje);        // ano (data atual)
        $oFreq->setData($vDatas);        // ano (data atual)
        $oFreq->setUsuario($sMatricula);  // matricula do usuario
        $oFreq->setSiape($sMatricula);    // matricula do servidor que se deseja alterar a frequencia
        $oFreq->setLotacao($sLotacao);    // lota��o do servidor que se deseja alterar a frequencia
        $oFreq->setMes($m); // mes que se deseja alterar a frequencia
        $oFreq->setAno($y); // ano que se deseja alterar a frequencia
        $oFreq->setNomeDoArquivo('ponto' . $comp); // nome do arquivo de frequencia
        $oFreq->loadDadosServidor(); // le os dados do servidor e se turno estendido define a jornada de acordo
        $oFreq->loadDadosSetor();
        $oFreq->pontoFacultativo();
        $sDiaUtil = $oFreq->verificaSeDiaUtil(false);
        $oFreq->setJornada($jnd);

        $situacao_cadastral = $oFreq->getSituacaoCadastral();

        $oFreq->setEntrada($he);
        $oFreq->setInicioIntervalo($hie);
        $oFreq->setFimIntervalo($his);
        $oFreq->setSaida($hs);

        //$oFreq->setHorarioEspecial( 'S' );
        //$oFreq->setHorarioEspecialMotivo( 'X' );
        //$oFreq->setDiaUtil( 'S' );
        //$oFreq->setBancoCompensacao( 'N' );

        $oFreq->setRegistroServidor("S");


/* ******************************************************************** *
 * C�LCULA HORAS TRABALHADAS E DIFEREN�AS                               *
 * Retorna:                                                             *
 *          c�digo de ocorr�ncia (d�bito, cr�dito ou frequ�ncia normal) *
 *          diferen�a apurada                                           *
 *          jornada realizada no dia                                    *
 *          jornada prevista para o dia                                 *
 * ******************************************************************** */
        $aHorasDia        = ApuraDiferencaParaBancoDeCompensacao($oFreq, $bhoras);
        $oco              = $aHorasDia[0];
        $dif              = $aHorasDia[3];
        $jdia             = $aHorasDia[1];
        $jornada_prevista = $aHorasDia[2];

        
        // Verifica se o horario registrado eh menor
        // ou maior que o da tabela de setores e sendo,
        // se est� autorizado a registrar ap�s o limite
        if ((time_to_sec($vHoras) > time_to_sec($fim) && $aut == "N") && in_array($oco, $codigoCreditoPadrao))
        {
            $oFreq->setSaida( $fim );

            $aHorasDia = ApuraDiferencaParaBancoDeCompensacao($oFreq, $bhoras);
            $oco       = ($bhoras == 'S' ? $aHorasDia[0] : $codigoFrequenciaNormalPadrao[0]);
            $dif       = ($bhoras == 'S' ? $aHorasDia[3] : '00:00');

            $encerramento_aviso = "N�o consta autoriza��o da chefia para trabalho ap�s o hor�rio de funcionamento da unidade, as horas realizadas ap�s esse per�odo n�o ser�o computadas!";
        }
        else if ((time_to_sec($vHoras) > time_to_sec($fim) && $aut == "N"))
        {
            $oFreq->setSaida( $fim );

            $aHorasDia = ApuraDiferencaParaBancoDeCompensacao($oFreq, $bhoras);
            $oco       = ($bhoras == 'S' ? $aHorasDia[0] : $codigoFrequenciaNormalPadrao[0]);
            $dif       = ($bhoras == 'S' ? $aHorasDia[3] : '00:00');

            //$oco       = $codigoFrequenciaNormalPadrao[0];
            //$dif       = '00:00';
            $encerramento_aviso = "N�o consta autoriza��o da chefia para trabalho ap�s o hor�rio de funcionamento da unidade, as horas realizadas ap�s esse per�odo n�o ser�o computadas!";
        }
        else if ((time_to_sec($vHoras) > time_to_sec($fim) && $bhoras != 'S') && in_array($oco, $codigoCreditoPadrao))
        {
            $oco = $codigoFrequenciaNormalPadrao[0];
            $dif = '00:00';
        }


/* ******************************************************************** *
 * VERIFICA SE H� PERMISS�O PARA USUFRUTO DO BANCO DE HORAS             *
 * E SE EXISTE SALDO PARA USUFRUIR                                      *
 *     Valida limite semanal de usufruto                                *
 *     Valida limite mensal de usufruto                                 *
 *     (1) Verifica, tamb�m, se o saldo � negativo                      *
 *                                                                      *
 * $retornoBancoDeHoras->ocorrencia       // string  : ocorrencia destina��o        *
 * $retornoBancoDeHoras->horasNegativas   // boolean : Horas negativas (true)       *
 * $retornoBancoDeHoras->mensagemNegativa // astring : mensagem, se hora negativa   *
 *                                                                      *
 * (1) Alterado em: 31/08/2019 (Edinalvo Rosa)                          *
 * ******************************************************************** */

        if (in_array($oco, $codigoDebitoPadrao))
        {
            $dados = new stdClass();        
            $dados->siape            = $sMatricula;            // string  : matr�cula do servidor
            $dados->dia              = $vDatas;                // string  : data da ocorr�ncia
            $dados->ocorrencia       = $oco;                   // string  : c�digo de ocorr�ncia
            $dados->diferenca        = $dif;                   // string  : diferen�a no dia
            $dados->jornadaRealizada = $jdia;                  // string  : jornada realizada (cadastro/jornada hist�rico)
            $dados->jornadaPrevista  = $jornada_prevista;      // string  : jornada prevista (cadastro/jornada hist�rico)
            $dados->idreg            = "S";                    // string  : indica quem registrou (S)servidor, (C)hefia, etc
   
            $dados->grupo            = "";                     // string  : grupo/m�dulo (acompanhar,hist�rico,etc)
            $dados->tipoUsufruto     = "parcial";              // string  : tipo do usufruto (parcial,total)
            $dados->debitoPadrao     = $codigoDebitoPadrao[0]; // string  : c�digo d�bito padr�o
            $dados->registro_ip      = getIpReal();            // string  : IP da m�quina
            $dados->registro_siape   = "";                     // string  : Matr�cula do operador
            
            $retornoBancoDeHoras = $objTabBancoDeHorasAcumulosController->verificaCondicoesUsufrutoBancoDeHoras( $dados );
        }
        else
        {
            $retornoBancoDeHoras = new stdClass();
            $retornoBancoDeHoras->ocorrencia         = null;
            $retornoBancoDeHoras->horasNegativas     = false;
            $retornoBancoDeHoras->mensagemNegativa   = "";
            $retornoBancoDeHoras->diferenca          = null;
            $retornoBancoDeHoras->bool               = "";
            $retornoBancoDeHoras->horasUsadaUsufruto = 0;
        }


        ## - turno estendido
        #
        $turno_estendido = $oFreq->turnoEstendido('3'); // jornada
        $sTurnoEstendido = $oFreq->getTurnoEstendido(); // informa se o servidor encontra-se em unidade

        // autorizada a realizar o turno estendido
        ## ocupantes de fun��o
        #
        $ocupaFuncao     = $oFreq->getChefiaAtiva();
    }

    // linha que captura o ip do usuario.
    $ip = getIpReal();

    // atualiza o banco de dados da frequencia
    if (soNumeros($vDatas) != 0 && soNumeros($vHoras) != 0 && soNumeros($jdia) != 0)
    {
        if ( !is_null($retornoBancoDeHoras->ocorrencia) )
        {
            $dif = apuraDiferencasPontoAuxiliar( $sMatricula, $vDatas, $sLotacao, $jdia);
        }
        
        $oDBase->setMensagem("Falha no registro do ponto");
        $oDBase->query("
            UPDATE ponto$comp
            SET
                sai     = :vhoras,
                jornp   = :jornp,
                jorndif = :jorndif,
                jornd   = :jornd,
                oco     = :oco,
                ip4     = :ip4
            WHERE
                siape = :siape
                AND dia = :vdatas
            ", array(
            array(':vhoras',  $vHoras, PDO::PARAM_STR),
            array(':jornp',   $jornada_prevista, PDO::PARAM_STR),
            array(':jorndif', $dif, PDO::PARAM_STR),
            array(':jornd',   $jdia, PDO::PARAM_STR),
            array(':oco',     (is_null($retornoBancoDeHoras->ocorrencia) || empty($retornoBancoDeHoras->ocorrencia) ? $oco : $retornoBancoDeHoras->ocorrencia), PDO::PARAM_STR),
            array(':ip4',     $ip, PDO::PARAM_STR),
            array(':siape',   $sMatricula, PDO::PARAM_STR),
            array(':vdatas',  $vDatas, PDO::PARAM_STR),
        ));

        // grava o LOG
        registraLog(" registrou fim do expediente");
    }
    else
    {
        retornaErro('entrada.php', 'Erro de processamento. Por favor, repita a opera��o!');
    }
}

if ($sDiaUtil == false) // sendo sabado, domingo ou feriado
{
    $idif = (in_array($oco, $codigoFrequenciaNormalPadrao) ? 'N' : 'S');
}
else
{
    $idif = (in_array($oco, $codigosCompensaveis) ? 'N' : 'S');
}

if (empty($rows))
{
    $encerramento_mensagem = "Fim do expediente registrado com sucesso!";
}
else
{
    $encerramento_mensagem = "Voc� j� registrou fim do expediente!";
}

if ($idh == "1")
{
    if ($idsaida == "1")
    {
        $encerramento_aviso = "N�o consta autoriza��o da chefia para sa�da ap�s o hor�rio de funcionamento da unidade, as horas  realizadas ap�s esse per�odo n�o ser�o computadas!";
    }
    elseif ($idif == "S" && ($bhoras == 'S' || checkServidorHasAutorization() || verificaSeHaAutorizacaoHoraExtra($sMatricula, $vDatas)))
    {
        $encerramento_aviso = "Voc� ultrapassou a jornada legal, selecione na caixa abaixo a destina��o do cr�dito de horas!";
    }
}

//rotina para testar se a data atual � maior que fim de compensa��o do recesso
if (dataCompensacaoDoRecesso($vDatas) == true)
{
    $prazo = 1; // um est� dentro do prazo de compensa��o do recesso
}
else
{
    $prazo = 2; // dois est� fora do prazo de compensa��o do recesso
}

// exibe op��es para a destina��o das horas excedentes,
// quando houver e for autorizada compensa��o de d�bitos,
// sen�o links para visualizar a frequ�ncia e finaliza��o
// da se��o do usu�rio
//


// dados da unidade
$oDBase->query("
    SELECT
        und.descricao, taborgao.denominacao, taborgao.sigla
    FROM
        tabsetor AS und
    LEFT JOIN
        taborgao ON LEFT(und.codigo,5) = taborgao.codigo
    WHERE
        und.codigo = :codigo
    ", array(
    array(':codigo', $sLotacao, PDO::PARAM_STR),
));
$oSetor            = $oDBase->fetch_object();
$lotacao_descricao = $oSetor->descricao;    // descri��o do c�digo da unidade
$orgao_descricao   = $oSetor->denominacao;  // descri��o do c�digo do �rg�o
$orgao_sigla       = $oSetor->sigla;        // sigla do �rg�o



/* ******************************************************************** *
 * SALDO DE ACUMULO DE HORAS RECUPERADOS A SEGUIR                       *
 *                                                                      *
 * Carrega o saldo do ano                                               *
 * Carrega o saldo do m�s                                               *
 *                                                                      *
 * ******************************************************************** */
if (key_exists('horas_trabalhadas_diferenca_apurada',$_SESSION) && time_to_sec($_SESSION['horas_trabalhadas_diferenca_apurada']) > 0)
{
    $dif = $_SESSION['horas_trabalhadas_diferenca_apurada'];
}

$saldoano    = getBalanceIntoYear($sMatricula);
$saldomes    = getBalanceIntoMonth($sMatricula);
$segundosdif = parseHoursInSeconds($dif);
$boolano     = false;
$boolmes     = false;
$boolcheck   = false;
$msg_mes     = false;
$msg_ano     = false;
$v_ano       = "00:00";
$v_mes       = "00:00";

$continuevalidacoes = true;

if(empty($saldoano))
{
    $saldoano = 0;
}

if(empty($saldomes))
{
    $saldomes = 0;
}

$limite_horas_anual_banco_de_horas  = grupoOcorrencias('limite_horas_anual_banco_de_horas');
$limite_horas_mensal_banco_de_horas = grupoOcorrencias('limite_horas_mensal_banco_de_horas');

$limite_banco_de_horas_anual  = time_to_sec($limite_horas_anual_banco_de_horas['limite_horas_anual_banco_de_horas']['horario']);
$limite_banco_de_horas_mensal = time_to_sec($limite_horas_mensal_banco_de_horas['limite_horas_mensal_banco_de_horas']['horario']);

// CASO J� TENHA ACUMULADO MAIS DE 100 HORAS NO ANO, NEM CONTINUA AS VALIDA��ES
if ($saldoano >= $limite_banco_de_horas_anual AND $continuevalidacoes AND !$retornoBancoDeHoras->horasNegativas) {
    $mensagem_ano = "Voc� j� excedeu as <b>100 horas</b> anuais permitidas para acumulo de banco de horas.";
    $boolano = true;
    $msg_ano = true;
    $continuevalidacoes = false;
}

if(!$boolano AND $continuevalidacoes AND !$retornoBancoDeHoras->horasNegativas) {

// MENSAGEM PARA VALIDA��ES MENSAIS DE ACUMULO DE HORAS

    if ($saldomes >= $limite_banco_de_horas_mensal) {
        $mensagem_mes = "Voc� j� excedeu as <b>40 horas</b> mensais permitidas para acumulo de banco de horas.";
        $boolmes = true;
        $msg_mes = true;
        $continuevalidacoes = false;
    } else {

        $valoratualizado = $segundosdif + $saldomes;

        if ($valoratualizado > $limite_banco_de_horas_mensal) {

            $v_mes = convertSecondsToHours($limite_banco_de_horas_mensal - $saldomes);
            $horasouminutos = (parseHoursInSeconds($v_mes) > 3599) ? 'horas':'minutos';
            $mensagem_mes = "Somente � permitido o acumulo de <b>" . $v_mes . "</b> ". $horasouminutos ." no dia, pois o limite mensal de <b>40 horas</b> ser� atingido.";
            $titilocheckbox = "Destinar " . $v_mes ." ". $horasouminutos ." para o banco de horas";
            $boolcheck = true;
            $msg_mes = true;
            $boolmes = true;
        }
    }

    $valoratualizado = $segundosdif + $saldoano;

    if ($valoratualizado > $limite_banco_de_horas_anual AND $continuevalidacoes AND !$retornoBancoDeHoras->horasNegativas) {

        $v_ano = convertSecondsToHours($limite_banco_de_horas_anual - $saldoano);
        $horasouminutos = (parseHoursInSeconds($v_ano) > 3599) ? 'horas' : 'minutos';
        $mensagem_ano = "Somente � permitido o acumulo de <b>" . $v_ano . "</b> " . $horasouminutos . " no dia, pois o limite anual de <b>100 horas</b> ser� atingido.";
        $titilocheckbox = "Destinar " . $v_ano . " " . $horasouminutos . " para o banco de horas";
        $boolcheck = true;
        $msg_ano = true;
        $boolano = true;

        if(!empty($saldoano)) {
            if (parseHoursInSeconds($v_mes) > parseHoursInSeconds($v_ano))
                $msg_mes = false;
        }
    }
}

$compensacao = ($bhoras == 'S');
$bancohoras  = (checkServidorHasAutorization() == true && ($boolmes == false || $boolano == false));
$horaextra   = (verificaSeHaAutorizacaoHoraExtra($sMatricula, $vDatas) == true);

$finalizacao_da_pagina = "";

if ($idif == "S" && ($prazo == 1 || $compensacao || $bancohoras || $horaextra))
{
    $title = _SISTEMA_SIGLA_ . ' | Destina��o de Cr�dito de Horas';
    $encerramento_mensagem .= "<br>Voc� ultrapassou a jornada legal, selecione na caixa abaixo a destina��o do cr�dito de horas!";

    $finalizacao_da_pagina = listboxDestinacao($sMatricula, $prazo, $bancohoras, $horaextra, $compensacao);
}
else
{
    $title = _SISTEMA_SIGLA_ . ' | Fim do expediente registrado';

    $encerramento_mensagem = "Fim do expediente registrado com sucesso!";
    $finalizacao_da_pagina .= "<p style='text-align:center;word-spacing:0px;width:100%;height:20px;margin-left:0px;margin-right:0px;margin-top:6px;'><a align='center' href='pontoser.php?cmd=1' target='new'><font size='2'>Visualizar Frequ&ecirc;ncia do M&ecirc;s.</font></a></p>";
}


// HORAS REALIZADAS NO DIA
$horasRealizadasNoDia = (time_to_sec($jdia) > 0 ? "+" : "") . $jdia . 'h';
        
// USUFRUTO BANCO DE HORAS (PARCIAL)
$usufrutoBancoDeHorasParcial = "+" . sec_to_time($retornoBancoDeHoras->horasUsadaUsufruto,'hh:mm') . 'h';

// RESULTADO DO DIA
$resultadoDoDia =  "00:00";

if (!empty($oco) && in_array($oco, $codigosCompensaveis) && time_to_sec($dif) > 0)
{
    $resultadoDoDia = "-" . $dif ;
}
else if ($oco == $codigoCreditoPadrao[0] && time_to_sec($dif) > 0)
{
    $resultadoDoDia = "+" . $dif;
}
else if ($oco == $codigoFrequenciaNormalPadrao[0])
{
    $resultadoDoDia = $dif;
}

$resultadoDoDia .= 'h';




## classe para montagem do formulario padrao
#
$title = _SISTEMA_SIGLA_ . ' | Registro de Comparecimento';

$oForm = new formPadrao();
$oForm->setJSSelect2();
$oForm->setJS( 'js/phpjs.js' );
$oForm->setJS( 'entrada4.js?v.0.0.0.1' );

// Topo do formul�rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<script>
    var voltarOrigem = "<?= $_SESSION['sHOrigem_1']; ?>";

    $(document).ready(function ()
    {
        $('#btn-enviar').click(function ()
        {
            verificadados();
        });
    });
</script>

<div class="container">
    <div class="row" style="padding-top:100px;">

        <?php
        
        if (!empty($encerramento_aviso))
        {
            echo getMensagemErroHTML($encerramento_aviso, 'warning');
        }
        
        if (!empty($encerramento_mensagem))
        {
            echo getMensagemErroHTML($encerramento_mensagem, 'info');
        }

        if ($msg_ano)
        {
            echo getMensagemErroHTML($mensagem_ano, 'warning');
            $valorcheck = parseHoursInSeconds($v_ano);
        }

        if ($msg_mes)
        {
            echo getMensagemErroHTML($mensagem_mes, 'warning');
            $valorcheck = parseHoursInSeconds($v_mes);
        }

        if ($retornoBancoDeHoras->horasNegativas)
        {
            echo getMensagemErroHTML($retornoBancoDeHoras->mensagemNegativa, 'warning');
        }

        ?>
    </div>

    <!-- Row Referente aos dados dos funcion�rios  -->
    <div class="row margin-10">
        <div class="subtitle">
            <h4 class="lettering-tittle uppercase"><strong>Meus Dados</strong></h4>
        </div>

        <div class="col-md-12">
            <div class="row">
                <table class="table text-center">
                    <thead>
                        <tr>
                            <th class="text-center text-nowrap" style='vertical-align:middle;'>Mat. SIAPE</th>
                            <th class="text-center" style='vertical-align:middle;'>NOME</th>
                            <th class="text-center" style='vertical-align:middle;'>�RG�O</th>
                            <th class="text-center" style='vertical-align:middle;'>LOTA��O</th>
                            <th class="text-center" style='vertical-align:middle;'>COMPENSA��O</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><h4><?= tratarHTML(removeOrgaoMatricula($sMatricula)); ?></h4></td>
                            <td class="text-left col-xs-4"><h4><?= tratarHTML($sNome); ?></h4></td>
                            <td class="text-center col-xs-2 text-nowrap"><h4><?= tratarHTML(getOrgaoMaisSigla( $sLotacao )); ?></h4></td>
                            <td class="text-left col-xs-4"><h4><?= tratarHTML(getUorgMaisDescricao( $sLotacao )); ?></h4></td>
                            <td class="text-center col-xs-2 text-nowrap" style='color:red;'>
                                <h4>
                                    <strong><?= ($bhoras != "S" ? "N�O " : ""); ?>AUTORIZADA</strong>
                                </h4>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <!-- Row Referente aos dados Setor do funcionario  -->
    <div class="row margin-10">
        <div class="subtitle">
            <h4 class="lettering-tittle uppercase"><strong>Dados do seu Setor</strong></h4>
        </div>
        <div class="col-md-12" id="dados-setor">
            <div class="col-md-3">
                <h5>
                    <strong>Horario do Setor</strong>
                </h5>
                <p>
                    <strong><?= tratarHTML($ini); ?></strong> as <strong><?= tratarHTML($fim); ?></strong>
                </p>
            </div>
            <div class="col-md-3">
                <h5><strong>Hora de Entrada</strong></h5>
                <p><?= tratarHTML($entra); ?></p>
            </div>
            <div class="col-md-3">
                <h5><strong>Intervalo</strong></h5>
                <p><?= tratarHTML($iniin); ?> as <?= tratarHTML($fimin); ?></p>
            </div>
            <div class="col-md-3">
                <h5><strong>Hora de Saida</strong></h5>
                <p><?= tratarHTML($sai); ?></p>
            </div>
        </div>
    </div>
    <!-- Row referente a Comparecimento-->
    <div class="row comparecimento">
        <h3>Registro de comparecimento</h3>
    </div>
    <!-- -->
    <div class="row" id="registros">
        <div class="col-md-12">
            <div class="col-md-6 col-md-offset-3">
                <h4>Hor�rios do servidor - <?= tratarHTML($hoje); ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <h5><strong>Entrada</strong></h5>
            <p><?= tratarHTML($he . 'h'); ?></p>
        </div>
        <div class="col-md-6">
            <h5><strong>Intervalo</strong></h5>
            <p>
                <?= tratarHTML(' ' . $hie . ' as ' . $his . 'h '); ?>
            </p>
        </div>
        <div class="col-md-3">
            <h5><strong>Sa�da</strong></h5>
            <p>
                <?= tratarHTML($hs . 'h'); ?>
            </p>
        </div>
    </div>
        
    <?php if ($retornoBancoDeHoras->horasUsadaUsufruto > 0): ?>
    
        <div class="row" id="registros">
            <div class="col-md-4">
                <h5><strong>Horas realizadas no dia</strong></h5>
                <p>
                    <?= tratarHTML( $horasRealizadasNoDia ); ?>
                </p>
            </div>
        
            <div class="col-md-4">
                <h5><strong>Usufruto Banco de Horas (parcial)</strong></h5>
                <p>
                    <?= tratarHTML( $usufrutoBancoDeHorasParcial ); ?>
                </p>
            </div>
        
            <div class="col-md-4">
                <h5><strong>Resultado do dia</strong></h5>
                <p>
                    <?= tratarHTML( $resultadoDoDia ); ?>
                </p>
            </div>
        </div>
        
    <?php else: ?>

        <div class="row" id="registros">
            <div class="col-md-6">
                <h5><strong>Horas realizadas no dia</strong></h5>
                <p>
                    <?= tratarHTML( $horasRealizadasNoDia ); ?>
                </p>
            </div>
            <div class="col-md-6">
                <h5><strong>Resultado do dia</strong></h5>
                <p>
                    <?= tratarHTML( $resultadoDoDia ); ?>
                </p>
            </div>
        </div>

    <?php endif; ?>

    <!-- -->
    <div class="row">
        <form class="form form-inline" id="form1" name='form1' method='post' action='#' onsubmit='javascript:return false;'>
            <input type="hidden" name='modo' id='modo' value='8'>
            <input type="hidden" name="dia" id="dia" value="<?= tratarHTML($hoje); ?>">
            <input type="hidden" name="compete" id="compete" value="<?= tratarHTML($comp); ?>">
            <input type="hidden" name="siape" id="siape" value="<?= tratarHTML($sMatricula); ?>">

            <div class="col-md-12">

                <?= $finalizacao_da_pagina; ?>

                <div class="col-md-12 col-md-offset-4 margin-bottom-25" style="/*padding-left:20px;*/">
                    <div class="form-group" style="/*padding-left:20px;*/padding-bottom:20px;">
                        <?php if($boolcheck): ?>

                            <div class="checkbox">
                                <label><input name="compensar-banco" type="checkbox" value="<?= tratarHTML($valorcheck); ?>" class="compensar-horas"></label>
                            </div>

                            <?= $titilocheckbox ?>

                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-12 col-md-offset-4 margin-bottom-25" style="padding-left:125px;">
                <div class="form-group" style="padding-left:20px;padding-bottom:20px;">
                    <?php

                    if ($idif == "S")
                    {
                        if ($idif == "S" && ($prazo == 1 || $compensacao || $bancohoras || $horaextra))
                        {
                            ?>
                            <button type="button" id="btn-enviar" class="btn btn-success btn-block">
                                <span class="glyphicon glyphicon-ok"></span> Concluir
                            </button>
                            <?php
                        }
                    }
                    else
                    {
                        ?>
                        <!--
                        <a class="btn btn-success btn-block" href="entrada.php" role="button">
                            <span class="glyphicon glyphicon-log-out"></span> Encerrar
                        </a>
                        -->
                        <?php
                    }
                    ?>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
##
# Mensagens e avisos da
# Assessoria de Comunica��o Social
#
// FIXME: voltar com essa fun��o depois
//mensagens_comunicacao_social();
##
# Registra se a entrada foi 20 min antes
# ou 20 min depois do hor�rio de sa�da definido
#
//mensagemHorarioDifere($sMatricula, 'limite_saida', $sTurnoEstendido, $ocupaFuncao, '', '', $sai, $vHoras, $sDiaUtil, $vDatas);

// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

DataBase::fechaConexao();





/* ******************************************************************** *
 * C�LCULA HORAS TRABALHADAS E DIFEREN�AS                               *
 * Retorna:                                                             *
 *          c�digo de ocorr�ncia (d�bito, cr�dito ou frequ�ncia normal) *
 *          diferen�a apurada                                           *
 *          jornada realizada no dia                                    *
 *          jornada prevista para o dia                                 *
 * ******************************************************************** */
function ApuraDiferencaParaBancoDeCompensacao($oFreq, $bhoras = '')
{
    $aHorasDia = array();

    if (is_object($oFreq))
    {
        if (checkServidorHasAutorization() == true && $bhoras != 'S')
        {
            $oFreq->setBancoCompensacao( "S" );

            $aHorasDia = $oFreq->calculaHorasTrabalhadas();

            $_SESSION['horas_trabalhadas_diferenca_apurada'] = $aHorasDia[3];

            $oFreq->setBancoCompensacao( $bhoras );
        }

        $aHorasDia = $oFreq->calculaHorasTrabalhadas();
    }

    return $aHorasDia;
}
