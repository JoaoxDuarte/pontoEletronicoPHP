<?php

include_once( "config.php" );

verifica_permissao('logado');

//testa se competencia solicitada é anterior à da homologação
$ano  = substr($dia, 6, 4);
$mes  = substr($dia, 3, 2);
$diat = substr($dia, 0, 2);

//Define Competênciada homologação.
$data  = new trata_datasys;
$anot  = $data->getAnoAnterior();
$compt = $data->getMesAnterior();

// instancia BD
$oDBase = new DataBase('PDO');

// hosts com acesso autorizado
$oDBase->query("SELECT ip_do_host FROM tabhosts_acesso_autorizado WHERE ip_do_host='" . rtrim($_SERVER['SERVER_ADDR']) . "' AND autorizado='S' ");

if ($oDBase->num_rows() == 0 || ("$ano-$mes-$diat" < "$anot-$compt-01" && "$ano-$mes-$diat" != "0000-00-00"))
{
    $sLotacao   = $_SESSION["sLotacao"];
    $sMatricula = $_SESSION["sMatricula"];
    $vHoras     = strftime("%H:%M:%S", time());
    $vDatas     = date("Y-m-d");
    $ip         = getIpReal(); //linha que captura o ip do usuario.

    $oDBase->setMensagem("Falha no registro da operação");
    $oDBase->query("INSERT INTO ilegal (siape, operacao, datag, hora, maquina, setor) VALUES ('$sMatricula', 'Tentativa de excluir o dia $dia da ficha do servidor $pSiape pertencente a competências anteriores à homologação por alteração de endereço no browser.', '$vDatas', '$vHoras', '$ip', '$sLotacao') ");

    //voltar(1, 'acessonegado.php');
}
