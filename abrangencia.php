<?php

if ($_SESSION['sSenhaI'] == 'S' || $_SESSION['sSR'] == 'S')
{
    /* BRASIL */
    $sWhere  = "";
    $sTitulo = "BRASIL";
}
elseif (($_SESSION['sRH'] == 'S' && $_SESSION['sTabServidor'] == 'S' && substr($_SESSION["sLotacao"], 2, 3) == '150') || $_SESSION['sSR'] == 'S')
{
    /* SUPERINTENDENCIA */
    $sWhere  = "AND und.regional = '" . $_SESSION['regional'] . "'";
    $sTitulo = $_SESSION["sSuperintendencia"];
}
elseif (($_SESSION['sRH'] == 'S' && $_SESSION['sTabServidor'] == 'S') || $_SESSION['sGEX'] == 'S')
{
    /* GERENCIA */
    $sWhere  = "AND und.upag = '" . $_SESSION['upag'] . "'";
    $sTitulo = $_SESSION["sGerencia"];
    $sTitulo = (substr_count($sTitulo, 'DIRE��O CENTRAL') > 0 ? 'DIRE��O CENTRAL' : $sTitulo);
}
elseif ($_SESSION['sAPS'] == 'S')
{
    /* SETOR */
    $sWhere  = "AND cad.cod_lot = '" . $_SESSION["sLotacao"] . "'";
    $sTitulo = $_SESSION["sLotacaoDescr"];
}
