<?php

include_once("config.php");

verifica_permissao("sRH");

// dados
$modo = $_REQUEST['modo'];
$upag = $_REQUEST['upag'];
$lot  = $_REQUEST['lot'];

if ($modo == "1")
{
    //obtendo email do chefe
    $emails_para = emailChefiaTitularSubstituto($lot);
    enviarEmail($emails_para, 'FREQUENCIA(S) SEM HOMOLOGACAO', "<br><br><big>Senhor(a) Chefe,<br>Verificamos que vossa senhoria ainda não homologou a frequência dos servidores de seu setor e o prazo está encerrando. <br>Atenciosamente,<br> Gesetão de Pessoas.</big><br><br>");

    mensagem('Aviso encaminhado para ' . $emails_para, 'relfrqsetorp.php?upag=' . $upag);
}
