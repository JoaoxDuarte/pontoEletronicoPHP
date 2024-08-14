<?php

include_once( "config.php" );
include_once("class_ocorrencias_grupos.php");

if (empty($dia))
{
    $dia = $_REQUEST['dia'];
}

## ocorrências grupos
$obj = new OcorrenciasGrupos();
$codigosCompensaveis = $obj->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios=true);

//Inicio da verificação de dia útil
// $dia e $lot devem ser definidos no script de origem
// N - não é dia útil
// S - sim é dia útil

$dutil = (verifica_se_dia_nao_util($dia, $lot) == true ? "N" : "S" );

if ($dutil == "N" && ($ocor != '' && (in_array($ocor,$codigosCompensaveis) > 0)))
{
    mensagem(" Não é permitido lançar essa ocorrência em dia não útil!", null, 1);
}
