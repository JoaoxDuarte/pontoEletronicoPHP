<?php


if ($_SESSION['sSenhaI'] == 'Sx' || $_SESSION['sSR'] == 'Sx')
{
    /* BRASIL */
    $sWhere  = "";
    $sTitulo = "BRASIL";
}
else if (($_SESSION['sRH'] == 'S' && $_SESSION['sTabServidor'] == 'S') || $_SESSION['sGEX'] == 'S')
{
    /* GERENCIA */
    $sWhere  = "AND und.upag = '" . $_SESSION['upag'] . "'";
    $sTitulo = $_SESSION['upag'];
}
else /* if ($_SESSION['sAPS'] == 'S') */
{
    /* SETOR */
    $sWhere  = "AND cad.cod_lot = '" . $_SESSION["sLotacao"] . "'";
    $sTitulo = $_SESSION["sLotacao"];
}
