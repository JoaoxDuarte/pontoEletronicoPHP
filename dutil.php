<?php

include_once( "config.php" );
include_once("class_ocorrencias_grupos.php");

if (empty($dia))
{
    $dia = $_REQUEST['dia'];
}

## ocorr�ncias grupos
$obj = new OcorrenciasGrupos();
$codigosCompensaveis = $obj->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios=true);

//Inicio da verifica��o de dia �til
// $dia e $lot devem ser definidos no script de origem
// N - n�o � dia �til
// S - sim � dia �til

$dutil = (verifica_se_dia_nao_util($dia, $lot) == true ? "N" : "S" );

if ($dutil == "N" && ($ocor != '' && (in_array($ocor,$codigosCompensaveis) > 0)))
{
    mensagem(" N�o � permitido lan�ar essa ocorr�ncia em dia n�o �til!", null, 1);
}
