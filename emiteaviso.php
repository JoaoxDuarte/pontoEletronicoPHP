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
    enviarEmail($emails_para, 'FREQUENCIA(S) SEM HOMOLOGACAO', "<br><br><big>Senhor(a) Chefe,<br>Verificamos que vossa senhoria ainda n�o homologou a frequ�ncia dos servidores de seu setor e o prazo est� encerrando. <br>Atenciosamente,<br> Geset�o de Pessoas.</big><br><br>");

    mensagem('Aviso encaminhado para ' . $emails_para, 'relfrqsetorp.php?upag=' . $upag);
}
