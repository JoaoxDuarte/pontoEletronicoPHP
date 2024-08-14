<?php

set_time_limit(0);

// funcoes de uso geral
include_once( "config.php" );
include_once( "class_ocorrencias_grupos.php" );

// dados passados por formulario
$siape = $_REQUEST['siape'];
$ocorr = $_REQUEST['ocorr'];

$year              = $_SESSION['sIMPYear']; // ano da homologacao
$comp              = $_SESSION['sIMPComp']; // mes da homologacao
// variavel para reorno da pesquisa
$aDadosEncontrados = array();

// sem matricula
if (!empty($siape))
{
    $oDBase = selecionaServidor($siape);
    $sitcad = $oDBase->fetch_object()->sigregjur;


    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $excluirDosSemRemuneracao = $obj->ExcluirDosSemRemuneracao($onc->sigregjur, $exige_horarios=true);


    // instancia banco de dados
    $oDBase = new DataBase('PDO');

    // atualiza_frqANO(
    //   <matricula do servidor>, <mes>, <ano>,
    //   [[<arquivo destino>], [[<barra de progresso>], [[<calcular>], [<processa competencia atual>]]]]
    // );
    atualiza_frqANO($siape, $comp, $year, '', false, true, ($ocorr != ''));

    // dados do servidor
    if ($ocorr == '')
    {
        $oDBase->query("
        SELECT
            a.mat_siape,
            a.dia_ini,
            a.dia_fim,
            a.cod_ocorr,
            a.dias,
            a.horas,
            a.minutos,
            b.nome_serv,
            b.cod_lot,
            d.siapecad,
            d.cod_siape,
            d.semrem
        FROM
            frq$year AS a
        LEFT JOIN
            servativ AS b ON a.mat_siape = b.mat_siape
        LEFT JOIN
            tabocfre d ON a.cod_ocorr = d.siapecad
        WHERE
            a.mat_siape = :siape
            AND a.cod_ocorr NOT IN (" . implode(',', $excluirDosSemRemuneracao) . ")
            AND a.compet = :comp
            AND d.semrem = 'S'
        ORDER BY
            a.mat_siape, a.dia_ini
        ",
        array(
            array(':siape', $siape, PDO::PARAM_STR),
            array(':comp',  $year.$comp, PDO::PARAM_STR),
        ));
    }
    else
    {
        $oDBase->query("
        SELECT
            a.mat_siape,
            a.dia_ini,
            a.dia_fim,
            a.cod_ocorr,
            a.dias,
            a.horas,
            a.minutos,
            b.nome_serv,
            b.cod_lot,
            d.siapecad,
            d.cod_siape,
            d.semrem
        FROM
            frq$year AS a
        LEFT JOIN
            servativ AS b ON a.mat_siape = b.mat_siape
        LEFT JOIN
            tabocfre d ON a.cod_ocorr = d.siapecad
        WHERE
            a.mat_siape = :siape
            AND a.cod_ocorr = :ocorr
            AND a.compet = :comp
        ORDER BY
            b.nome_serv, a.dia_ini
        ",
        array(
            array(':siape', $siape, PDO::PARAM_STR),
            array(':comp',  $year.$comp, PDO::PARAM_STR),
            array(':ocorr', $ocorr, PDO::PARAM_STR)
        ));
    }

    $num = $oDBase->num_rows();

    if ($num > 0)
    {
        while ($pm = $oDBase->fetch_object())
        {
            $nome                = htmlspecialchars(mb_convert_encoding(ajustar_acentos(retira_acentos($pm->nome_serv)), "UTF-8", "ISO-8859-1"));
            $aDadosEncontrados[] = array(
                'siape'        => $pm->mat_siape,
                'nome'         => $nome,
                'cod_siapecad' => $pm->siapecad,
                'cod_siape'    => $pm->cod_siape,
                'dia_ini'      => "$pm->dia_ini/$comp",
                'dia_fim'      => "$pm->dia_fim/$comp",
                'dias'         => $pm->dias,
                'mensagem'     => ''
            );
            array_push($_SESSION['saDadosEncontradosI'], array($pm->mat_siape, $nome, $pm->siapecad, $pm->cod_siape, "$pm->dia_ini/$comp", "$pm->dia_fim/$comp", $pm->dias));
        }
    }
}

$myData = array('dados' => $aDadosEncontrados);
print json_encode($myData);
