<?php

include_once( 'config.php' );

// estes dados servem apenas para testar
// se as informa��es vem do supervisao
//
$siape   = anti_injection($_REQUEST['tSiape']);
$entrada = anti_injection($_REQUEST['entra']) . ':00';

$sAutorizadoTE = anti_injection($_REQUEST['sAutorizadoTE']);
$ocupaFuncao   = anti_injection($_REQUEST['ocupaFuncao']);

$jornada      = anti_injection($_REQUEST['jornada']);
$horasJornada = formata_jornada_para_hhmm($jornada) . ':00';

// instancia do banco de dados SISREF
$oDBase = new DataBase('PDO');

// limite de horario de entrada e saida do �rg�o
$limites_inss   = horariosLimiteINSS();
$entrada_minima = time_to_sec($limites_inss['entrada']['horario']); // registra entrada a partir deste hor�rio, ex.: 6:30
$entrada_maxima = time_to_sec($limites_inss['saida']['horario']); // registra sa�da at� este hor�rio, ex.: 22:00
$entrada_maxima = $entrada_maxima - time_to_sec('06:00:00'); // limita o hor�rio m�nimo para defini��o de sa�da, ex.: 16:00

$msg_erro = '';
$result   = array();

if (time_to_sec($entrada) < $entrada_minima)
{
    $msg_erro = '- Hor�rio de entrada n�o pode ser menor que ' . sec_to_time($entrada_minima, 'hh:mm') . ' horas!';
}
if (time_to_sec($entrada) > $entrada_maxima)
{
    $msg_erro = '- Hor�rio de entrada n�o pode ser maior que ' . sec_to_time($entrada_maxima, 'hh:mm') . ' horas!';
}

if ($msg_erro != '')
{
    $result[0] = array(
        'siape' => '',
        'saida' => '',
        'erro'  => utf8_iso88591($msg_erro)
    );
}
else
{
    if (($sAutorizadoTE == 'S' && $ocupaFuncao == 'N') || $jornada < 40)
    {
        $oDBase->query("SELECT SEC_TO_TIME(TIME_TO_SEC('$entrada')+TIME_TO_SEC('$horasJornada')) AS sai_trab ");
        $oSaida = $oDBase->fetch_object();
        $saida  = substr($oSaida->sai_trab, 0, 5);
    }

    $result[0] = array(
        'siape' => $siape,
        'saida' => $saida,
        'erro'  => ''
    );
}

$myData = array('dados' => $result);

print json_encode($myData);
