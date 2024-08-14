<?php

include_once( "config.php" );
include_once( "class_ocorrencias_grupos.php" );

function calculaHorasDoRecesso($sMatricula = '', $ano = '', $compensacao_fim = '')
{

    // pesquisa a existencia na base de dados
    $oDBase = selecionaServidor($siape);
    $sitcad = $oDBase->fetch_object()->sigregjur;

    // instancia grupo de ocorrencia
    $obj = new OcorrenciasGrupos();

    $codigoCreditoRecessoPadrao = $obj->CodigoCreditoRecessoPadrao( $sitcad );
    $codigoDebitoRecessoPadrao  = $obj->CodigoDebitoRecessoPadrao( $sitcad );
    $codigoCreditoPadrao         = $obj->CodigoCreditoPadrao( $sitcad );
    $codigoDebitoPadrao          = $obj->CodigoDebitoPadrao( $sitcad );

    $ano = (empty($ano) || $ano < 2010 ? date('Y') : $ano);

    /* OUTUBRO   */ list( $ano_inicio, $ano_fim, $recesso10, $horas10, $tcre10 ) = somaRecesso($sMatricula, "10", $ano, $obj, $sitcad);
    /* NOVEMBRO  */ list( $ano_inicio, $ano_fim, $recesso11, $horas11, $tcre11 ) = somaRecesso($sMatricula, "11", $ano, $obj, $sitcad);
    /* DEZEMBRO  */ list( $ano_inicio, $ano_fim, $recesso12, $horasc12, $tcre12, $horasd12, $tdeb12 ) = somaRecesso($sMatricula, "12", $ano, $obj, $sitcad);
    /* JANEIRO   */ list( $ano_inicio, $ano_fim, $recesso01, $horasc01, $tcre01, $horasd01, $tdeb01 ) = somaRecesso($sMatricula, "01", $ano, $obj, $sitcad);
    /* FEVEREIRO */ list( $ano_inicio, $ano_fim, $recesso02, $horasc02, $tcre02 ) = somaRecesso($sMatricula, "02", $ano, $obj, $sitcad);
    /* MARÇO     */ list( $ano_inicio, $ano_fim, $recesso03, $horasc03, $tcre03 ) = somaRecesso($sMatricula, "03", $ano, $obj, $sitcad);
    /* ABRIL     */ list( $ano_inicio, $ano_fim, $recesso04, $horasc04, $tcre04 ) = somaRecesso($sMatricula, "04", $ano, $obj, $sitcad);

    // verifica se há dados de recesso
    $recesso = ($recesso10 + $recesso11 + $recesso12 + $recesso01 + $recesso02 + $recesso03 + $recesso04);

    //calculando o resultado
    $totcre = ($tcre10 + $tcre11 + $tcre12 + $tcre01 + $tcre02 + $tcre03 + $tcre04);

    //convertendo
    $tocre  = $totcre;
    $hor    = floor($tocre / 3600);
    $tocre  -= $hor * 3600;
    $min    = floor($tocre / 60);
    $tocre  -= $min * 60;
    $seg    = floor($tocre);
    $subtot = "<font face='Tahoma' size='3'>" . ($totcre > 0 ? "+ " . sprintf("%02s:%02s:%02s", $hor, $min, $seg) : "--------") . "</font>";

    $horasd12 = sec_to_time(time_to_sec($horasd12) + time_to_sec($horasd01));
    if (time_to_sec('100:00:00') > time_to_sec($horasd12))
    {
        $horasd12 = substr($horasd12, 1, strlen($horasd12));
    }
    $tdeb12 += $tdeb01;

    $cod = "";
    if ($tdeb12 > $totcre)
    {
        $resultado = $tdeb12 - $totcre;
        $cod       = $codigoDebitoRecessoPadrao[0];
        $msg       = "Horas a compensar até " . compensacao_fim;
    }
    elseif ($tdeb12 <= $totcre)
    {
        $resultado = $totcre - $tdeb12;
        $msg       = "Recesso Compensado";
    }

    //convertendo
    $tocre = $resultado;
    $hor   = floor($tocre / 3600);
    $tocre -= $hor * 3600;
    $min   = floor($tocre / 60);
    $tocre -= $min * 60;
    $seg   = floor($tocre);
    $tot   = sprintf("%02s:%02s:%02s", $hor, $min, $seg);

    $total2 = $tot;

    if ($tdeb12 > $totcre)
    {
        $tot = "<font face='Tahoma' size='4' color='#FF0000'>- " . substr($tot, 0, 5) . "</font>";
    }
    elseif ($tdeb12 <= $totcre)
    {
        $tot = "<font face='Tahoma' size='4' color='#0000FF'>+ " . substr($tot, 0, 5) . "</font>";
    }

    return array($cod, $tot, $ano_inicio, $ano_fim, $recesso, $horas10, $horas11, $horasc12, $horasd12, $horasc01, $horasc02, $horasc03, $horasc04, $subtot, $total2, $msg);

}

function somaRecesso($sMatricula = '', $mes = '', $ano = '', $obj, $sitcad)
{
    // códigos de ocorrências
    $codigoCreditoRecessoPadrao = $obj->CodigoCreditoRecessoPadrao( $sitcad );
    $codigoDebitoRecessoPadrao  = $obj->CodigoDebitoRecessoPadrao( $sitcad );
    $grupoOcorrenciasViagem     = $obj->GrupoOcorrenciasViagem( $sitcad );

    $codigos_creditos = implode(",", $codigoCreditoRecessoPadrao) . "," .
                        implode(",", $grupoOcorrenciasViagem); //"'02424','00128'";
    $codigos_debitos  = implode(",", $codigoDebitoRecessoPadrao); //"'02323'";

    // ano inicio e fim
    $mes     = (empty($mes) ? date("m") : $mes);
    $anocomp = (empty($ano) ? date("Y") : $ano);
    $macomp  = ($mes == "10" || $mes == "11" || $mes == "12" ? ($ano - 1) : $ano) . $mes;

    $ano_inicio = ($mes == "10" || $mes == "11" || $mes == "12" ? ($ano - 1) : $ano);
    $ano_fim    = $ano;

    // variaveis
    $horasCredito = 0;
    $segsCredito  = 0;
    $horasDebito  = 0;
    $segsDebito   = 0;
    //
    // instancia o banco de dados
    $oDBase       = new DataBase('PDO');
    $oDBase->setMensagem("Erro no acesso ao banco de dados (cálculo RECESSO)");

    $oDBase->query(
    "SELECT
        DATE_FORMAT(recesso_inicio_compensacao,'%Y-%m-%d') AS compensacao_inicio,
        DATE_FORMAT(recesso_fim_compensacao,'%Y-%m-%d')    AS compensacao_fim
    FROM
        tabrecesso_fimdeano
    WHERE
        LEFT(periodo,4) < :ano
    GROUP BY
        LEFT(periodo,4)
    ORDER BY
        LEFT(periodo,4) DESC ",
    array(
        array(':ano', $ano, PDO::PARAM_STR)
    ));

    $oRecesso           = $oDBase->fetch_object();
    $compensacao_inicio = $oRecesso->compensacao_inicio;
    $compensacao_fim    = $oRecesso->compensacao_fim;

    if (!empty($sMatricula) && (
        ($macomp >= $ano_inicio . "10" && $anocomp >= 2012) ||
         $macomp >= $ano_inicio . "11" ||
         $macomp >= $ano_inicio . "12" ||
         $macomp >= $ano_fim    . "01" ||
         $macomp >= $ano_fim    . "02" ||
         $macomp >= $ano_fim    . "03" ||
        ($macomp >= $ano_fim    . "04" && ($anocomp > 2010 && $anocomp < 2013)))
    )
    {
        $arquivo = "ponto" . $mes . ($mes == "10" || $mes == "11" || $mes == "12" ? $ano_inicio : $ano_fim);

        if (existeDBTabela($arquivo, 'sisref'))
        {
            $oDBase->query("
            SELECT
                a.siape AS siape,
                SEC_TO_TIME(SUM(TIME_TO_SEC(IF(a.oco IN (" . $codigos_creditos . "),a.jorndif,0)))) AS horas2424,
                SUM(TIME_TO_SEC(IF(a.oco IN (" . $codigos_creditos . "),a.jorndif,0)))              AS segundos2424,
                SEC_TO_TIME(SUM(TIME_TO_SEC(IF(a.oco IN (" . $codigos_debitos . "),a.jorndif,0))))  AS horas2323,
                SUM(TIME_TO_SEC(IF(a.oco IN (" . $codigos_debitos . "),a.jorndif,0)))               AS segundos2323,
                CONCAT(SUBSTR(dia,6,2),SUBSTR(dia,1,4)) AS comp
            FROM
                " . $arquivo . " AS a
            WHERE
                a.siape = :siape
                AND a.oco IN (" . $codigos_debitos . "," . $codigos_creditos . ")
                AND (a.dia >= :comp_inicio AND a.dia <= :comp_fim)
            GROUP BY a.siape ",
            array(
                array(':siape', $sMatricula, PDO::PARAM_STR),
                array(':comp_inicio', $compensacao_inicio, PDO::PARAM_STR),
                array(':comp_fim', $compensacao_fim, PDO::PARAM_STR)
            ));

            $recesso = $oDBase->num_rows();
            $oSoma   = $oDBase->fetch_object();
        }
        $horasCredito = $oSoma->horas2424;
        $segsCredito  = $oSoma->segundos2424;
        $horasDebito  = $oSoma->horas2323;
        $segsDebito   = $oSoma->segundos2323;
    }

    return array($ano_inicio, $ano_fim, $recesso, $horasCredito, $segsCredito, $horasDebito, $segsDebito);

}
