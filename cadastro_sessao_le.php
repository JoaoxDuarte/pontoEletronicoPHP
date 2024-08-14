<?php

// carrega variaveis com valores de sessao
if ((isset($_SESSION['cad_wnome']) && !empty($_SESSION['cad_wnome']))
    || (isset($_SESSION['cad_tSiape']) && !empty($_SESSION['cad_tSiape'])))
{
    $wnome    = $_SESSION['cad_wnome'];
    $wnome_social    = $_SESSION['cad_wnome_social'];
    $tSiape   = $_SESSION['cad_tSiape'];
    $Siapecad = $_SESSION['cad_Siapecad'];
    $idunica  = $_SESSION['cad_idunica'];
    $Situacao = $_SESSION['cad_Situacao'];
    $email    = $_SESSION['cad_email'];
    $wcargo   = $_SESSION['cad_wcargo'];
    $nivel    = $_SESSION['cad_nivel'];
    $Regjur   = $_SESSION['cad_Regjur'];
    $wdatinss = databarra($_SESSION['cad_wdatinss']);
    $Jornada  = $_SESSION['cad_Jornada'];
    $Jornada_cargo  = $_SESSION['cad_Jornada_cargo'];
    $dtjorn   = databarra($_SESSION['cad_datjorn']);
    $defvis   = $_SESSION['cad_defvis'];
    $pis      = $_SESSION['cad_pis'];
    $cpf      = $_SESSION['cad_cpf'];
    $dtnasc   = databarra($_SESSION['cad_dtnasc']);
    $wlota    = $_SESSION['cad_wlota'];
    $datlot   = databarra($_SESSION['cad_datlot']);
    $loca     = $_SESSION['cad_loca'];
    $datloca  = databarra($_SESSION['cad_datloca']);
    
    $horae    = $_SESSION['cad_horae'];
    $processo = $_SESSION['cad_processo'];
    $mothe    = $_SESSION['cad_motivo'];
    $dthe     = databarra($_SESSION['cad_dthe']);
    $dthefim  = databarra($_SESSION['cad_dthefim']);

    $upg      = $_SESSION['cad_upg'];
}
