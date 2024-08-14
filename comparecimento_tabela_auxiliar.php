<?php

include_once("config.php");
include_once("class_definir.jornada.php");
include_once("class_ocorrencias_grupos.php");
include_once("src/controllers/DadosServidoresController.php");


/**
 * @param  string  $siape  Matrícula do servidor
 * @param  string  $dia    Dia do registro
 * @return resource  Resultado da seleção
 */
function selecionarDadosPontoAuxiliar($siape,$dia,$oco)
{
    // Matricula no padrao orgao+siape
    $matricula = getNovaMatriculaBySiape($siape);
    $comp  = dataMes($dia) . dataAno($dia);

    $sql = "
    SELECT
        dia, siape, entra, intini, intsai, sai, jornd, jornp, jorndif, oco
            FROM ponto".$comp."_auxiliar
                WHERE dia = :dia
                      AND siape = :siape
                      AND oco = :oco
    ";

    $params = array(
        array(":dia",   conv_data($dia), PDO::PARAM_STR),
        array(":siape", $matricula,      PDO::PARAM_STR),
        array(":oco",   $oco,            PDO::PARAM_STR),
    );

    $oDBase = new DataBase('PDO');
    $oDBase->query( $sql, $params );

    return $oDBase;
}

/**
 * @info Incluir frequencia auxiliar
 *
 * @param  array    $paramsPonto
 *      array(
 *          'dia'            Data da ocorrência
 *          'matricula'      Matrícula SIAPE
 *          'hora_ini'       Hora inicial da ausência
 *          'hora_fim'       Hora final da ausência
 *          'tempo_consulta' Tempo decorrido (hora_fim - hora_ini)
 *          'deslocamento'   Tempo de deslocamento,
 *          'setor'          Unidade do servidor
 *          'idreg'          Quem registrou Chefia/RH/Servidor
 *          'oco'            Código da ocorrência
 *          'registro_ip'    IP da máquina do registro
 *          'registro_siape' Matrícula SIAPE de quem registrou
 *      );
 * @param  integer  $saldo
 * @return boolean  TRUE sucesso, FALSE erro
 */
function incluirPontoAuxiliar($paramsPonto,$saldo=0)
{
    // Matricula no padrao orgao+siape
    $matricula = getNovaMatriculaBySiape($paramsPonto['matricula']);

    $return = false;

    $comp = dataMes($paramsPonto['dia']) . dataAno($paramsPonto['dia']);

    $dia      = conv_data($paramsPonto['dia']);
    $ipalt    = getIpReal();
    $idaltexc = 'I';
    $siapealt = $paramsPonto['registro_siape'];

    if ($paramsPonto['idreg'] == 'C')
    {
        $ipch     = $paramsPonto['registro_ip'];
        $iprh     = '';
        $matchef  = $paramsPonto['registro_siape'];
        $siaperh  = '';
    }
    else
    {
        $ipch     = '';
        $iprh     = $paramsPonto['registro_ip'];
        $matchef  = '';
        $siaperh  = $paramsPonto['registro_siape'];
    }

    $paramsPonto['ipch']    = $ipch;
    $paramsPonto['iprh']    = $iprh;
    $paramsPonto['matchef'] = $ipch;
    $paramsPonto['siaperh'] = $iprh;

    $oDBase = new DataBase('PDO');
    $oDBase2 = new DataBase('PDO');

    // inclui no ponto auxiliar
    $sql = "
    INSERT ponto".$comp."_auxiliar
        (dia, siape, entra, intini, intsai, sai, jornd, jornp, jorndif, oco, just, seq, idreg, ip, ip2, ip3, ip4, justchef, ipch, iprh, matchef, siaperh)
        VALUES
        (:dia, :siape, :entra, '00:00:00', '00:00:00', :sai, :jornd, '00:00', '00:00', :oco, '', :seq, :idreg, '', '', '', '', '', :ipch, :iprh, :matchef, :siaperh)
    ";

    $params = array(
        array(":dia",     conv_data($dia),                PDO::PARAM_STR),
        array(":siape",   $matricula,                     PDO::PARAM_STR),
        array(":entra",   $paramsPonto['hora_ini'],       PDO::PARAM_STR),
        array(":sai",     $paramsPonto['hora_fim'],       PDO::PARAM_STR),
        array(":jornd",   $paramsPonto['tempo_consulta'], PDO::PARAM_STR),
        array(":oco",     $paramsPonto['oco'],            PDO::PARAM_STR),
        array(":seq",     '',                             PDO::PARAM_STR),
        array(":idreg",   $paramsPonto['idreg'],          PDO::PARAM_STR),
        array(":ipch",    $ipch,                          PDO::PARAM_STR),
        array(":iprh",    $iprh,                          PDO::PARAM_STR),
        array(":matchef", $matchef,                       PDO::PARAM_STR),
        array(":siaperh", $siaperh,                       PDO::PARAM_STR),
    );

    // inclusão
    $oDBase->query( $sql, $params );
    $linhas_afetadas = $oDBase->affected_rows();

    $return = ($linhas_afetadas > 0);

    // inclui no historico do ponto auxiliar
    $sql = "
    INSERT histponto".$comp."_auxiliar
        (dia, siape, entra, intini, intsai, sai, jornd, jornp, jorndif,
         oco, just, idreg, ip, ip2, ip3, ip4, justchef, ipch, iprh,
         matchef, siaperh, diaalt, horaalt, siapealt, ipalt, idaltexc)
        VALUES
        (:dia, :siape, :entra, '00:00:00', '00:00:00', :sai, :jornd,
         '00:00', '00:00', :oco, '', :idreg, '', '', '', '', '', :ipch,
         :iprh, :matchef, :siaperh, DATE_FORMAT(NOW(),'%Y-%m-%d'),
         DATE_FORMAT(NOW(),'%H:%i:%s'), :siapealt, :ipalt, :idaltexc)
    ";

    $params = array(
        array(":dia",      conv_data($dia),                 PDO::PARAM_STR),
        array(":siape",    $matricula,                      PDO::PARAM_STR),
        array(":entra",    $paramsPonto['hora_ini'],        PDO::PARAM_STR),
        array(":sai",      $paramsPonto['hora_fim'],        PDO::PARAM_STR),
        array(":jornd",    $paramsPonto['tempo_consulta'],  PDO::PARAM_STR),
        array(":oco",      $paramsPonto['oco'],             PDO::PARAM_STR),
        array(":idreg",    $paramsPonto['idreg'],           PDO::PARAM_STR),
        array(":ipch",     $ipch,                           PDO::PARAM_STR),
        array(":iprh",     $iprh,                           PDO::PARAM_STR),
        array(":matchef",  $matchef,                        PDO::PARAM_STR),
        array(":siaperh",  $siaperh,                        PDO::PARAM_STR),
        array(":siapealt", $siapealt,                       PDO::PARAM_STR),
        array(":ipalt",    $ipalt,                          PDO::PARAM_STR),
        array(":idaltexc", $idaltexc,                       PDO::PARAM_STR),
    );


    // limite de horas consulta medica
    $limite_consulta = horariosLimite();
    $secLimiteHoras  = time_to_sec($limite_consulta['limite_consulta']);

    $secSaldo = time_to_sec($saldo);

    $oDBase2->query( $sql, $params );

    if ($secSaldo > $secLimiteHoras)
    {
        $secSaldo = ($secSaldo - $secLimiteHoras);
    }

    AlterarPontoPrincipal( $paramsPonto, $por_exclusao = false, $secSaldo );

    return $return;
}

/**
 * @param  array    $paramsPonto = array(
 *          'dia'            Data da ocorrência
 *          'matricula'      Matrícula SIAPE
 *          'hora_ini'       Hora inicial da ausência
 *          'hora_fim'       Hora final da ausência
 *          'tempo_consulta' Tempo decorrido (hora_fim - hora_ini)
 *          'deslocamento'   Tempo de deslocamento,
 *          'setor'          Unidade do servidor
 *          'idreg'          Quem registrou Chefia/RH/Servidor
 *          'oco'            Código da ocorrência
 *          'registro_ip'    IP da máquina do registro
 *          'registro_siape' Matrícula SIAPE de quem registrou
 *          'ipch'           IP da chefia, se for a chefia quem registra
 *          'iprh'           IP do RH, se for o RH quem registra
 *          'matchef'        Matrícula da chefia, se for a chefia quem registra
 *          'siaperh'        Matrícula do servidor do RH, se for o RH quem registra
 *      );
 *         boolean $por_exclusao
 * @return boolean  TRUE sucesso, FALSE erro
 */
function AlterarPontoPrincipal( $paramsPonto, $por_exclusao = false, $limite_horas = 0 )
{
    // instancia classes
    $objDadosServidoresController = new DadosServidoresController();
    $objOcorrenciasGrupos         = new OcorrenciasGrupos();

    // Matricula no padrao orgao+siape
    $matricula = getNovaMatriculaBySiape($paramsPonto['matricula']);
    $sitcad    = $objDadosServidoresController->getSigRegJur($paramsPonto['matricula']);

    $dia        = conv_data($paramsPonto['dia']);
    $comp       = dataMes($dia) . dataAno($dia);
    $novo_saldo = NULL;
    $jornada    = JornadaDoServidor($matricula,$_SESSION['uorg'],$dia);

    $oDBase  = new DataBase('PDO');

    // inclusão
    $oDBase->query( "
    SELECT
        pto.dia, pto.siape, pto.entra, pto.intini, pto.intsai, pto.sai,
        pto.jornd, pto.jornp, pto.jorndif, pto.oco,
	IF(ptoa.oco=:oco,SEC_TO_TIME(TIME_TO_SEC(pto.jornd)+TIME_TO_SEC(ptoa.jornd)),0) AS tempo_diferenca
    FROM
        ponto".$comp." AS pto
    LEFT JOIN 
        ponto".$comp."_auxiliar AS ptoa ON pto.siape = ptoa.siape
    WHERE
        pto.dia = :dia
        AND pto.siape = :siape
    ", array(
        array(":dia",   $dia,                PDO::PARAM_STR),
        array(":siape", $matricula,          PDO::PARAM_STR),
        array(":oco",   $paramsPonto['oco'], PDO::PARAM_STR),
    ));

    if ($oDBase->num_rows() == 0)
    {
        sleep(1);

        $ponto = $oDBase->fetch_object();
        $novo_saldo = time_to_sec($ponto->tempo_diferenca); 

        if ($novo_saldo == 0)
        {
            $oco = '00000';
        }
        else
        {
            $oco = '99999';
        }
        
        // inclui no ponto principal
        $sql = "
        INSERT ponto".$comp."_auxiliar
            (dia, siape, entra, intini, intsai, sai, jornd, jornp, jorndif, oco, just, seq, idreg, ip, ip2, ip3, ip4, justchef, ipch, iprh, matchef, siaperh)
            VALUES
            (:dia, :siape, :entra, '00:00:00', '00:00:00', :sai, :jornd, '00:00', '00:00', :oco, '', :seq, :idreg, '', '', '', '', '', :ipch, :iprh, :matchef, :siaperh)
        ";

        $params = array(
            array(":dia",     conv_data($dia),       PDO::PARAM_STR),
            array(":siape",   $matricula,            PDO::PARAM_STR),
            array(":entra",   '00:00',               PDO::PARAM_STR),
            array(":sai",     '00:00',               PDO::PARAM_STR),
            array(":jornd",   '00:00',               PDO::PARAM_STR),
            array(":jorndif", sec_to_time($novo_saldo,'hh:mm'), PDO::PARAM_STR),
            array(":oco",     '00:00',               PDO::PARAM_STR),
            array(":seq",     '',                    PDO::PARAM_STR),
            array(":idreg",   $paramsPonto['idreg'], PDO::PARAM_STR),
            array(":ipch",    $ipch,                 PDO::PARAM_STR),
            array(":iprh",    $iprh,                 PDO::PARAM_STR),
            array(":matchef", $matchef,              PDO::PARAM_STR),
            array(":siaperh", $siaperh,              PDO::PARAM_STR),
        );

        // inclusão
        $oDBase->query( $sql, $params );
    }
    else 
    {
        $CodigoDiferenca      = $objOcorrenciasGrupos->SaldoDiferencasMultiocorrencias($sitcad,$temp=true);
        $CodigoHoraExtra      = $objOcorrenciasGrupos->CodigoHoraExtraPadrao($sitcad);
        $CodigoCredito        = $objOcorrenciasGrupos->CodigoCreditoPadrao($sitcad);
        $CodigoConsultaMedica = $objOcorrenciasGrupos->CodigoConsultaMedicaPadrao($sitcad);

        $SaldoNegativo = $objOcorrenciasGrupos->SaldoNegativo();
        $SaldoPositivo = $objOcorrenciasGrupos->SaldoPositivo();

        $ponto = $oDBase->fetch_object();

        $secTempoConsulta = time_to_sec($paramsPonto['tempo_consulta']);
        if ($secTempoConsulta > time_to_sec($ponto->jorndif) && !in_array($ponto->oco,$SaldoPositivo))
        {
            $secTempoConsulta = $limite_horas;
        }
        $secJornDiferenca = time_to_sec($ponto->jorndif);
        $secJornada       = time_to_sec(formata_jornada_para_hhmm($jornada));

        // ocorrências com diferença
        if (in_array($ponto->oco,$CodigoDiferenca))
        {
            if ($por_exclusao == true)
            {
                if (in_array($paramsPonto['oco'], $CodigoConsultaMedica))
                {
                    $novo_saldo = time_to_sec($ponto->tempo_diferenca);
                }
                else
                {
                    $novo_saldo9 = ($secJornDiferenca + $ponto->tempo_diferenca);
                    $novo_saldo8 = (in_array($ponto->oco,$CodigoHoraExtra)
                                    && $novo_saldo9 > time_to_sec('02:00')
                                    ? time_to_sec('02:00') : $novo_saldo9);
                    $novo_saldo  = (in_array($ponto->oco,$CodigoCredito)
                                    && $novo_saldo8 > time_to_sec('02:00')
                                    ? time_to_sec('02:00') : $novo_saldo8);
                }
            }
            else
            {
                $novo_saldo = ($secJornDiferenca - $secTempoConsulta);
                if ($novo_saldo < 0)
                {
                    $novo_saldo = 0;
                }
            }

            $novo_saldo = ($novo_saldo > $secJornada ? $secJornada : $novo_saldo);
        }

        if (!is_null($novo_saldo))
        {
            sleep(1);

            gravar_historico_ponto($matricula, $dia, $oper = 'A');

            $novo_saldo = ($novo_saldo > 0 ? $novo_saldo : 0);

            $oDBase->query( "
            UPDATE ponto".$comp."
                SET jorndif = '".sec_to_time($novo_saldo,'hh:mm')."'
            WHERE
                dia = :dia
                AND siape = :siape
            ", array(
                array(":dia",   $dia,                PDO::PARAM_STR),
                array(":siape", $matricula,          PDO::PARAM_STR),
            ));
        }
    }
}

function JornadaDoServidor($siape='',$lotacao='',$dia='')
{
    // verifica autorizacao
    $oJornadaTE = new DefinirJornada();
    $oJornadaTE->setSiape($siape);
    $oJornadaTE->setLotacao($lotacao);
    $oJornadaTE->setData($dia);
    $oJornadaTE->setChefiaAtiva();
    $oJornadaTE->estabelecerJornada();

    return $oJornadaTE->getJornada();
}

/**
 * @param void
 * @return void
 */
function deletePontoAuxiliar($dados,$oco)
{
    /*
    $params = array(
    0    array(":dia",            $oDados->dia,            PDO::PARAM_STR),
    1    array(":siape",          $oDados->siape,          PDO::PARAM_STR),
    2    array(":hora_ini",       $oDados->hora_ini,       PDO::PARAM_STR),
    3    array(":hora_fim",       $oDados->hora_fim,       PDO::PARAM_STR),
    4    array(":tempo_consulta", $oDados->tempo_consulta, PDO::PARAM_STR),
    5    array(":deslocamento",   $oDados->deslocamento,   PDO::PARAM_STR),
    6    array(":setor",          $oDados->setor,          PDO::PARAM_STR),
    7    array(":idreg",          $idreg,                  PDO::PARAM_STR),
    8    array(":acao",           'E',                     PDO::PARAM_STR),
    9    array(":registro_ip",    getIpReal(),             PDO::PARAM_STR),
   10    array(":registro_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
    );
     */

    // Matricula no padrao orgao+siape
    $matricula = getNovaMatriculaBySiape($dados[1][1]);

    // dados
    $comp     = dataMes($dados[0][1]) . dataAno($dados[0][1]);
    $dia      = conv_data($dados[0][1]);
    $ipalt    = getIpReal();
    $idaltexc = $dados[8][1];
    $siapealt = $dados[10][1];

    // grava os dados anteriores
    // no historico do ponto
    gravarHistoricoPontoAuxiliar($matricula,$dia,$oco,'E');

    // alterar principal
    $paramsPonto = array(
        'dia'            => $dia,
        'matricula'      => $matricula,
        'tempo_consulta' => $dados[4][1],
        'setor'          => $dados[6][1],
        'idreg'          => $dados[7][1],
        'oco'            => $oco,
        'registro_ip'    => $dados[9][1],
        'registro_siape' => $dados[10][1],
        'ipch'           => $dados[9][1],
        'iprh'           => '',
        'matchef'        => $dados[10][1],
        'siaperh'        => '',
    );

    AlterarPontoPrincipal( $paramsPonto, $por_exclusao = true );

    // instancia banco de dados
    $oDBase = new DataBase('PDO');

    // apaga o registro
    $oDBase->query("
    DELETE FROM ponto".$comp."_auxiliar
    WHERE
        siape = :siape
        AND dia = :dia
        AND oco = :oco
    ",
    array(
        array(":siape", $matricula,      PDO::PARAM_STR),
        array(":dia",   conv_data($dia), PDO::PARAM_STR),
        array(":oco",   $oco,            PDO::PARAM_STR),
    ));
}


/**  @Function
 *
 * @param  string  $matricula  Matricula siape
 * @param  string> $diac       Data invertida (aaaa-mm-dd)
 * @param  string> $oco        Código da ocorrência
 * @param  string> $oper       Operação executada (alteração,etc)
 * @return : void
 * @usage  : gravarHistoricoPontoAuxiliar('9999999','2011-11-10','xxxxx','X');                                                |
 * @author  Edinalvo Rosa
 *
 * @info  Registra em histórico os dados do ponto auxiliar antes da alteração
 */
function gravarHistoricoPontoAuxiliar($matricula='', $dia='', $oco='', $oper='A')
{
    // Matricula no padrao orgao+siape
    $matricula = getNovaMatriculaBySiape($matricula);

    // dados do usuario cadastrador
    $sUsuario = $_SESSION["sMatricula"];

    //linha que captura o ip do usuario.
    $ip = getIpReal();

    // competencia referente a data indicada
    $comp = dataMes($dia) . dataAno($dia);

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    // mensagem em caso de erro no acesso aa tabela
    $oDBase->setMensagem("Falha no registro do histórico de frequência!");

    // grava os dados anteriores
    // no historico do ponto
    $oDBase->query("
    INSERT INTO histponto".$comp."_auxiliar
    SELECT
        dia, siape, entra, intini, intsai, sai, jornd, jornp, jorndif, oco, idreg,
        ip, ip2, ip3, ip4, IFNULL(ipch,'') AS ipch, IFNULL(iprh,'') AS iprh,
        matchef, siaperh, DATE_FORMAT(NOW(),'%Y-%m-%d') AS diaalt,
        DATE_FORMAT(NOW(),'%H:%i:%s') AS horaalt, :usuario, :ip, :oper, just,
        justchef
    FROM
        ponto".$comp."_auxiliar
    WHERE
        dia = :dia
        AND siape = :siape
        AND oco = :oco
    ",
    array(
        array(':siape',   $matricula, PDO::PARAM_STR),
        array(':dia',     $dia,       PDO::PARAM_STR),
        array(':usuario', $sUsuario,  PDO::PARAM_STR),
        array(':ip',      $ip,        PDO::PARAM_STR),
        array(':oper',    $oper,      PDO::PARAM_STR),
        array(":oco",     $oco,       PDO::PARAM_STR),
    ));
}


/**  @Function
 *
 * @param  string  $siape    Matrícula do servidor
 * @param  string  $dia      Data do registro da frequência
 * @param  string  $unidade  Unidade do servidor
 * @return void
 * @author Edinalvo Rosa
 * @info   Ajusta saldo da frequencia se houver registro de
 *         consulta médica ou GECC
 */
function AjustaSaldoFrequenciaSeConsultaMedicaRegistrada($siape, $dia, $unidade=NULL)
{
    $sql = "
    SELECT tempo_consulta
        FROM compareceu_consulta_medica
            WHERE
                siape = :siape
                AND dia = :dia";

    $params = array(
        array(":dia",   $dia,   PDO::PARAM_STR),
        array(":siape", $siape, PDO::PARAM_STR),
    );

    // inclusão
    $oDBase = new DataBase('PDO');
    $oDBase->query( $sql, $params );

    if ($oDBase->num_rows() > 0)
    {
        $horarios_limite = horariosLimite($unidade);

        $tempo_consulta = $oDBase->fetch_object()->tempo_consulta;

        $paramsPonto = array(
            'dia'            => $dia,
            'matricula'      => $siape,
            'tempo_consulta' => $tempo_consulta,
        );

        AlterarPontoPrincipal( $paramsPonto, false, time_to_sec($horarios_limite['horas_excedentes']) );
    }
}


/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : horariosLimite                               |
 * | @description : Carrega limites                              |
 * | @param  : <string>  - $unidade                              |
 * | @return : void                                              |
 * | @author : Edinalvo Rosa                                     |
 * +-------------------------------------------------------------+
 * */
function horariosLimite($unidade=NULL)
{
    $unidade = (is_null($unidade) ? $_SESSION['sLotacao'] : $unidade);

    $horarios_limite = array(
        'entrada_orgao'    => '05:00',
        'saida_orgao'      => '22:00',
        'entrada_unidade'  => '07:00',
        'saida_unidade'    => '19:00',
        'limite_consulta'  => '44:00',
        'horas_excedentes' => '02:00',
    );

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    // seleciona limite de horário inicial e final do Órgão/Sistema
    $oDBase->query( "
    SELECT campo, minutos
        FROM config_basico
    " );

    if ($oDBase->num_rows() > 0)
    {
        while ($horarios = $oDBase->fetch_object())
        {
            switch ($horarios->campo)
            {
                case 'limite_hora_entrada_inss':
                    $horarios_limite['entrada_orgao'] = $horarios->minutos;
                    break;

                case 'limite_hora_saida_inss':
                    $horarios_limite['saida_orgao'] = $horarios->minutos;
                    break;

                case 'limite_horas_excedentes_por_dia':
                    $horarios_limite['horas_excedentes'] = $horarios->minutos;
                    break;

                case 'limite_horas_anual_consulta_medica':
                    $horarios_limite['limite_consulta'] = $horarios->minutos;
                    break;

                default:
                    $horarios_limite[$horarios->campo] = $horarios->minutos;
                    break;
            }
        }
    }

    // seleciona limite de horário inicial e final da Unidade
    $oDBase->query( "SELECT inicio_atend, fim_atend FROM tabsetor WHERE codigo = '".$unidade."' " );

    if ($oDBase->num_rows() > 0)
    {
        $horario = $oDBase->fetch_object();
        $horarios_limite['entrada_unidade']  = $horario->inicio_atend;
        $horarios_limite['saida_unidade']    = $horario->fim_atend;
    }

    return $horarios_limite;
}
