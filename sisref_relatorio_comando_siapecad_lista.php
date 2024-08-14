<?php

set_time_limit(0);

// funcoes de uso geral
include_once( "config.php" );
include_once( "class_ocorrencias_grupos.php" );

// dados passados por formulario
$siape = $_REQUEST['siape'];

$upag  = $_SESSION['sIMPUpag']; // unidade pagadora (GEX/SR/ADM-DG)
$year  = $_SESSION['sIMPYear']; // ano da homologacao
$comp  = $_SESSION['sIMPComp']; // mes da homologacao

// variavel para reorno da pesquisa
$aDadosEncontrados = array();


$oDBase = selecionaServidor($siape);
$sitcad = $oDBase->fetch_object()->sigregjur;


## ocorrências grupos
$obj = new OcorrenciasGrupos();
$excluirDosSemRemuneracao = $obj->ExcluirDosSemRemuneracao($sitcad, $exige_horarios=true);


//atualiza_frqANO( $oServidor->mat_siape, $comp, $year, '', false );
atualiza_frqANO($siape, $comp, $year, '', false, true);

// instancia banco de dados
$oDBase = new DataBase('PDO');

// dados do servidor
$oDBase->query("
SELECT
	frq.mat_siape, frq.dia_ini, frq.dia_fim, frq.cod_ocorr, frq.dias, frq.horas, frq.minutos,
	cad.nome_serv, cad.cod_lot, oco.siapecad, oco.cod_siape, oco.idsiapecad
FROM
	frq$year AS frq
LEFT JOIN
	servativ AS cad ON frq.mat_siape = cad.mat_siape
LEFT JOIN
	tabocfre oco ON frq.cod_ocorr = oco.siapecad
WHERE
	frq.mat_siape = :siape
	AND frq.compet = :comp
	AND frq.cod_ocorr NOT IN (" . tratarHTML(implode(',', $excluirDosSemRemuneracao)) . ")
	AND oco.idsiapecad = 'S'
ORDER BY
	frq.mat_siape, frq.dia_ini
",
array(
    array(':siape', tratarHTML($siape), PDO::PARAM_STR),
    array(':comp',  tratarHTML($year).tratarHTML($comp), PDO::PARAM_STR)
));

$num = $oDBase->num_rows();

if ($num > 0)
{
    while ($pm = $oDBase->fetch_object())
    {
        $idsipc = $pm->idsiapecad;
        if ($idsipc == "S")
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
