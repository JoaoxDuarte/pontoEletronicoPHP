<?php

set_time_limit(0);

// funcoes de uso geral
include_once( "config.php" );
include_once( "sisref_relatorio_ocorrencia_nao_compensada_lista.naocompensados.php");
include_once( "class_ocorrencias_grupos.php" );

// dados passados por formulario
$siape = $_REQUEST['siape'];

$upag = $_SESSION['sIMPUpag']; // unidade pagadora (GEX/SR/ADM-DG)
$year = $_SESSION['sIMPYear']; // ano da homologacao
$comp = $_SESSION['sIMPComp']; // mes da homologacao
$mes  = $_SESSION['sIMPMes']; // ano da competencia que deveria ser compensada
$ano  = $_SESSION['sIMPAno']; // ano da competencia que deveria ser compensada
/*
  $upag = $_SESSION['sIMPUpag']; // unidade pagadora (GEX/SR/ADM-DG)
  $year = '2012'; //$_SESSION['sIMPYear']; // ano da homologacao
  $comp = '03'; //$_SESSION['sIMPComp']; // mes da homologacao
  $mes = '02'; //$_SESSION['sIMPMes']; // ano da competencia que deveria ser compensada
  $ano = '2012'; //$_SESSION['sIMPAno']; // ano da competencia que deveria ser compensada
 */

// variavel para reorno da pesquisa
$aDadosEncontrados = array();

// instancia banco de dados
$oDBase = new DataBase('PDO');

// dados do servidor
$oPonto = new DataBase('PDO');

// pega o código siape que corresponde ao
// código SiapeCAD 00172
$oDBase->query("SELECT d.siapecad, d.cod_siape FROM tabocfre AS d WHERE d.siapecad = '$codigo_debito' AND d.ativo = 'S' ");
$codigo_siape_para_00172 = $oDBase->fetch_object()->cod_siape;

// horas comuns (créditos/débitos/compensações)
$comp_inicial = ($mes == '' ? date('m') : $mes) . '/' . ($ano == '' ? date('Y') : $ano);
$comp_final   = $comp . '/' . $year;
$aHorasComuns = resultado_horas_comuns($siape, $comp_inicial, $comp_final, $comp_admissao, $comp_exclusao);

$oDBase->query("
SELECT
    banco_de_horas.siape,
    servativ.nome_serv AS nome,
    SEC_TO_TIME(ABS(TIME_TO_SEC(banco_de_horas.sub_total))) AS sub_total,
    servativ.sigregjur
FROM
    banco_de_horas
LEFT JOIN
    servativ ON banco_de_horas.siape = servativ.mat_siape
WHERE
    banco_de_horas.siape = :siape
    AND comp = :comp
    AND banco_de_horas.sub_total < 0
    AND banco_de_horas.tipo = '1'
ORDER BY
    banco_de_horas.siape
",
array(
    array(':siape', $siape, PDO::PARAM_STR),
    array(':comp',  $year . $comp, PDO::PARAM_STR)
));

$onc = $oDBase->fetch_object();


## ocorrências grupos
$obj = new OcorrenciasGrupos();
$codigosCompensaveis = $obj->GrupoOcorrenciasNegativasDebitos($onc->sigregjur, $exige_horarios=true);
$codigoDebitoPadrao  = $obj->CodigoDebitoPadrao($onc->sigregjur);


// codigos a pesquisar
$codigo_debito       = implode(',', $codigoDebitoPadrao); //'00172';
$codigos_a_compensar = implode(',', $codigosCompensaveis); //"'00172','55555','62010','62012','62014','99999'";


if ($oDBase->num_rows() > 0)
{
    // dados
    $siape       = $onc->siape;
    $nome        = htmlspecialchars(mb_convert_encoding(ajustar_acentos(retira_acentos($onc->nome)), "UTF-8", "ISO-8859-1"));
    $nFolha      = $onc->sub_total;
    $nFolhaSobra = $nFolha;

    $bFinaliza = false;
    $oPonto->query("
    SELECT
        DATE_FORMAT(dia,'%d') AS dia,
        siape,
        jorndif
    FROM
        ponto$mes$ano
    WHERE
        siape = :siape
        AND oco IN (".$codigos_a_compensar.")
    ORDER BY
        dia DESC
    ",
    array(
        array(':siape', $siape, PDO::PARAM_STR)
    ));

    while ($oFolha    = $oPonto->fetch_object())
    {
        $nDia        = $oFolha->dia;
        $nJornadaDif = $oFolha->jorndif;

        // calculo
        if ($nFolhaSobra > $nJornadaDif)
        {
            $nFolhaSobra = subtrairHoras($nJornadaDif, $nFolhaSobra);
            $total_horas = $nJornadaDif;
        }
        else
        {
            $total_horas = substr($nFolhaSobra, 0, 5);
            $bFinaliza   = true;
        }
        if ($total_horas != '00:00')
        {
            $aDadosEncontrados[] = array(
                'siape'        => $siape,
                'nome'         => "<font color='#c3c3c3'>$nome</font>",
                'cod_siapecad' => $codigo_debito,
                'cod_siape'    => $codigo_siape_para_00172,
                'dia'          => $nDia,
                'horas'        => $total_horas,
                'mensagem'     => ''
            );
            array_push($_SESSION['saDadosEncontradosF'], array($siape, $nome, $codigo_debito, $codigo_siape_para_00172, $nDia, $total_horas));
        }
        if ($bFinaliza == true)
        {
            break;
        }
    }

    $total_horas         = substr($nFolha, 0, 5);
    $aDadosEncontrados[] = array(
        'siape'        => $siape,
        'nome'         => "<b>$nome</b>",
        'cod_siapecad' => "<b>$codigo_debito</b>",
        'cod_siape'    => "<b>$codigo_siape_para_00172</b>",
        'dia'          => "<b>TOTAL</b>",
        'horas'        => "<b>" . $total_horas . "</b>",
        'mensagem'     => ''
    );
    array_push($_SESSION['saDadosEncontradosF'], array($siape, $nome, $codigo_debito, $codigo_siape_para_00172, "TOTAL", $total_horas));
}

$myData = array('dados' => $aDadosEncontrados);
print json_encode($myData);
