<?php

include_once( "config.php" );

$sequencia_arquivo = 1;

$sequencia = 1;
$ano       = anti_injection($_POST['ano']);
$mes       = anti_injection($_POST['mes']);
$upag      = anti_injection($_POST['upag']);
$codigos_ocorrencia = $_POST['codigos_ocorrencia'];

//$result = str_to_utf8('Seq;Matricula;NOME;Cod. SiapeNet;Data Inicial;Data Final;Dias') . PHP_EOL;
$result = FazHeader( $mes, $ano, $sequencia_arquivo, getOrgaoByUpag() );

// seleção dos servidores
$oDBase = seleciona_servidores_por_ocorrencia();

while ($dados = $oDBase->fetch_object())
{
    $result .= DadosServidorFrequencia($dados->siape);
}

$result .= FazReg('','','','','','');

$result .= FazTrailler( $sequencia );

$myData = array(
    "draw"            => 0,
    "recordsTotal"    => 0,
    "recordsFiltered" => 0,
    'data'            => $result
);

print json_encode($myData);

exit();


/**
 * 
 * @global int $sequencia
 * @global string $ano
 * @global string $mes
 * @global string $codigos_ocorrencia
 * @global string $upag
 * 
 * @param string $siape
 * 
 * @return string
 */
function DadosServidorFrequencia($siape)
{
    global $sequencia, $ano, $mes, $codigos_ocorrencia, $upag;

    // $ano: ano da homologacao
    // $mes: mes da homologacao
    // atualiza a tabela com dados
    // siapecad referentes ao servidor
    ////atualiza_frqANO( $oServidor->mat_siape, $mes, $a    no, '', false );

    //atualiza_frqANO($siape, $mes, $ano, '', false, true, true);

    // selecao
    $sql = "
    SELECT
     cad.mat_siape, frq.dia_ini, frq.dia_fim, frq.cod_ocorr, frq.dias, frq.horas, frq.minutos, cad.nome_serv, cad.cod_lot, oco.siapecad, oco.cod_siape, oco.idsiapecad, oco.semrem
     FROM
     frq" . $ano . " AS frq
     LEFT JOIN
     servativ AS cad ON frq.mat_siape = cad.mat_siape
     LEFT JOIN
     tabsetor AS setor ON cad.cod_lot = setor.codigo
     LEFT JOIN
     tabocfre AS oco ON frq.cod_ocorr = oco.siapecad
     WHERE
     setor.upag = :upag
     AND frq.cod_ocorr IN (" . $codigos_ocorrencia . ")
     AND frq.compet = :compet
     ORDER BY
     frq.mat_siape, frq.dia_ini
    ";

    $params = array(
        array( ':upag',   $upag, PDO::PARAM_STR ),
        array( ':compet', $ano . $mes, PDO::PARAM_STR ),
    );

    // instancia banco de dados
    $oDBase = new DataBase('PDO');

    // dados do servidor
    $oDBase->query($sql, $params);
    
    while ($pm = $oDBase->fetch_object())
    {
        $result .= FazReg( 
                $_SESSION['cpf'], 
                getOrgaoByUpag(), 
                removeOrgaoMatricula($pm->mat_siape), 
                $ano . $mes . $pm->dia_ini, 
                $ano . $mes . $pm->dia_fim, 
                $pm->siapecad 
        );
        $sequencia++;
    }

    return $result;
}

/**
 * 
 * @global string $mes
 * @global string $ano
 * @global string $upag
 * @global string $codigos_ocorrencia
 * 
 * @return \DataBase
 */
function seleciona_servidores_por_ocorrencia()
{
    global $mes, $ano, $upag, $codigos_ocorrencia;

    // seleção dos servidores
    $sqlPonto = "
        SELECT
            pto.siape, cad.nome_serv AS nome
        FROM
            ponto" . $mes . $ano . " AS pto
        LEFT JOIN
            servativ AS cad ON pto.siape = cad.mat_siape
        LEFT JOIN
            tabsetor AS und ON cad.cod_lot = und.codigo
        WHERE
            und.upag = :upag
            AND pto.oco IN (" . $codigos_ocorrencia . ")
        GROUP BY
            pto.siape
        ORDER BY
            LTRIM(RTRIM(cad.nome_serv))
    ";

    $params = array(
        array( ':upag', $upag, PDO::PARAM_STR ),
    );

    // instancia banco de dados
    $oDBase = new DataBase('PDO');

    $oDBase->query($sqlPonto, $params);

    return $oDBase;
}


/**
 * @info Monta Header do txt para envio ao SERPRO
 * 
 * @param string $cMes
 * @param string $cAno
 * @param string $cSeq
 * @param string $cOrgao
 * 
 * @return string
 */
function FazHeader( $cMes, $cAno, $cSeq, $cOrgao ) {
    $cHeader  = "0";                // tipo do registro
    $cHeader .= strzero($cOrgao,5); // orgao
    // processamento
    $cHeader .= strzero($cAno,4);   // ano de referência
    $cHeader .= strzero($cMes,2);   // mes de referência
    $cHeader .= strzero($cSeq,3);   // seq do arquivo
    $cHeader .= space(40);          // filler
    $cHeader .= PHP_EOL;            // quebra de linha

    return ($cHeader);
}


/**
 * @info Monta linhas de registros em txt para envio ao SERPRO
 * 
 * @param string $cpf
 * @param string $orgao
 * @param string $siape
 * @param string $inicio
 * @param string $termino
 * @param string $codigo
 * 
 * @return string
 */
function FazReg( $cpf, $orgao, $siape, $inicio, $termino, $codigo ) {
  $cReg  = "1";                             // tipo do registro
  $cReg .= strzero(ltrim(rtrim($cpf)),11);  // cpf
  $cReg .= strzero(ltrim(rtrim($orgao)),5); // órgão
  $cReg .= strzero(ltrim(rtrim($siape)),7); // matricula siape
  $cReg .= inverteData($inicio);            // data início
  $cReg .= inverteData($termino);           // data término
  $cReg .= strzero($codigo,4);              // código cocorrência
  $cReg .= space(11);                       // filler
  $cReg .= PHP_EOL;                         // quebra de linha

  return ($cReg);
}


/**
 * @info Monta Trailler no txt para envio ao SERPRO
 * 
 * @param string $ultimo_reg
 * 
 * @return string
 */
function FazTrailler($ultimo_reg) {
   $cTrail  = "9";                    // tipo do registro
   $cTrail .= strzero($ultimo_reg,6); // qtd de registro
   $cTrail .= space(48);              // filler
   $cTrail .= PHP_EOL;                // quebra de linha

   return ($cTrail);
}


/**
 * @info completa com zeros a esquerda
 * 
 * @param string $valor
 * @param string $tam
 * @param string $dec
 * 
 * @return string
 */
function strzero($valor,$tam,$dec=0) 
{
    $sobra = $valor-intval($valor);
    if ($dec >= 1) 
    {
        if ($sobra>0) 
        {
            $valor = ($valor * 10);
        }
        
        $valor = ($valor / pow(10,$dec));
    } 
    else 
    {
        $dec = -1;
        if ($sobra>0) 
        {
            $valor = ($valor * 100);
        }
    }
    
    return substr(str_repeat("0",$tam).number_format($valor,$dec,",",""),-$tam);
}


/**
 * @info preenche com espaco em branco
 * 
 * @param string $tam
 * 
 * @return string
 */
function space($tam) 
{
    return str_repeat(" ",$tam);
}
