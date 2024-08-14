<?php

// funcoes de uso geral
include_once( "config.php" );

// dados passados por formulario
$json_decode = true;

$bImprime = (isset($_REQUEST['siape']));

$siape = (isset($_REQUEST['siape']) ? anti_injection($_REQUEST['siape']) : $siape);
$ano   = (isset($_REQUEST['ano']) ? anti_injection($_REQUEST['ano']) : $ano);

// instancia banco de dados
$oDBase = new DataBase('PDO');

// dados do servidor
$mes_ini = ($ano == "2009" ? 10 : 1);
$mes_fim = ($ano == date("Y") ? date('n') : 12);

for ($i = $mes_ini; $i <= $mes_fim; $i++)
{
    $mes = substr('00' . $i, -2);
    atualiza_frqANO($siape, $mes, $ano, '', false, true);
}
$oDBase->query("
SELECT
    frq.compet, frq.dia_ini, frq.dia_fim, frq.cod_ocorr, servativ.cod_lot
FROM
    frq$ano AS frq
LEFT JOIN 
    servativ ON frq.mat_siape = servativ.mat_siape
WHERE
    frq.mat_siape = '$siape'
    AND SUBSTRING(frq.compet,1,4) = '$ano'
ORDER BY
    frq.compet, frq.dia_ini
");
$rows1 = $oDBase->num_rows();

if ($rows1 == 0)
{
    $idInner .= '<tr><td colspan="13" class="text-center">Sem registros para exibir!</font></b></div></td></tr>';
}
else
{
    $v1   = array();
    while ($line = $oDBase->fetch_object())
    {
        // mês e ano da competência
        $mes = substr($line->compet, 4, 2);
        $ano = substr($line->compet, 0, 4);

        // último dia do mês da competência
        $ultimo_dia_do_mes = numero_dias_do_mes($mes, $ano);

        // diaq inicial e final da ocorrencia
        $dia_ini = $line->dia_ini;
        $dia_fim = $line->dia_fim;

        for ($i = $dia_ini; $i <= $dia_fim; $i++)
        {
            $ind            = substr('00' . $i, -2);
            $ocorrencia     = $line->cod_ocorr;
            $v1[$ind][$mes] = ($ultimo_dia_do_mes == $i && empty($ocorrencia) ? "--" : $ocorrencia);
        }
    }

    $idInner .= "";
    for ($dia = 1; $dia <= 31; $dia++)
    {
        $bg_color = ($dia % 2 == 0 ? '#EEEEEE' : '#FFFFFF');
        $ind_dia  = substr('00' . $dia, -2);
        $idInner  .= "<tr height='20'>";
        $idInner  .= "<td class='text-center'><div align='center'><b>$ind_dia</b></div></td>";
        for ($mes = 1; $mes <= 12; $mes++)
        {
            $ind_mes    = substr('00' . $mes, -2);
            $ocorrencia = $v1[$ind_dia][$ind_mes];
            
            $dados = marcaDiasNaoUteis($ind_dia,$ind_mes,$ano,$ocorrencia);

            $idInner .= "<td class='text-center'>$dados</td>";
        }
        $idInner .= "</tr>";
    }
}

if ($bImprime == true)
{
    print $idInner;
}


/* ***********************************************************
 *                                                           *
 *                   FUNÇÕES COMPLEMENTARES                  *
 *                                                           *
 *********************************************************** */
function marcaDiasNaoUteis($dia,$mes,$ano,$ocorrencia)
{
    $ocorr = (empty($ocorrencia) ? "--" : $ocorrencia);
    $str   = "";
    $hoje  = date('w', mktime(0, 0, 0, $mes, $dia, $ano));

    switch ($hoje)
    {
        case '0':
            $str = "(Domingo)";
            break;
        
        case '6':
            $str = "(Sábado)";
            break;
        
        default:
            $data = $dia.'/'.$mes.'/'.$ano;
            if (eh_ponto_facultativo( $data ))
            {
                $str = "(Facultativo)";
            }
            
            if (eh_feriado( $data ))
            {
                $str = "(Feriado)";
            }
    }

    if (empty($str))
    {
        $str = "<div style='text-align:center;'>$ocorr</div>";
    }
    else
    {
        $str = "<div style='text-align:center;'>$ocorr<div style='text-align:center;color:red;font-size:9px;'>$str</div></div>";
    }

    return $str;
}
