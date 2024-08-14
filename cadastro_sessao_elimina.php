<?php

// apaga estes atributos da sessão
if ((isset($_SESSION['cad_wnome']) && !empty($_SESSION['cad_wnome']))
    || (isset($_SESSION['cad_tSiape']) && !empty($_SESSION['cad_tSiape'])))
{
    unset($_SESSION['cad_wnome']);
    unset($_SESSION['cad_tSiape']);
    unset($_SESSION['cad_Siapecad']);
    unset($_SESSION['cad_idunica']);
    unset($_SESSION['cad_Situacao']);
    unset($_SESSION['cad_wcargo']);
    unset($_SESSION['cad_nivel']);
    unset($_SESSION['cad_Regjur']);
    unset($_SESSION['cad_wdatinss']);
    unset($_SESSION['cad_Jornada']);
    unset($_SESSION['cad_datjorn']);
    unset($_SESSION['cad_defvis']);
    unset($_SESSION['cad_pis']);
    unset($_SESSION['cad_cpf']);
    unset($_SESSION['cad_dtnasc']);
    unset($_SESSION['cad_wlota']);
    unset($_SESSION['cad_datlot']);
    unset($_SESSION['cad_loca']);
    unset($_SESSION['cad_datloca']);
    unset($_SESSION['cad_email']);
    
    unset($_SESSION['cad_horae']);
    unset($_SESSION['cad_processo']);
    unset($_SESSION['cad_motivo']);
    unset($_SESSION['cad_dthe']);
    unset($_SESSION['cad_dthefim']);
    unset($_SESSION['cad_upg']);
    unset($_SESSION['limite-horas']);
    unset($_SESSION['plantao-medico']);
}
