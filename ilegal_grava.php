<?php

include_once('config.php');

$path_parts = pathinfo($_SERVER['HTTP_REFERER']);
$pagina_de_origem = $path_parts['basename'];
//$pagina_de_origem = $path_parts['filename'];

// instancia a base de dados
$oDBase = new DataBase('PDO');

// hosts com acesso autorizado
//$oDBase->query("SELECT ip_do_host FROM tabhosts_acesso_autorizado WHERE ip_do_host='" . rtrim($_SERVER['SERVER_ADDR']) . "' AND autorizado='S' ");

//if (empty($pagina_de_origem) || ($oDBase->num_rows() == 0))
if (empty($pagina_de_origem))
{
    $logLotacao    = $_SESSION["sLotacao"];
    $logMatricula  = $_SESSION["sMatricula"];
    $logHoras      = strftime("%H:%M:%S", time());
    $logDatas      = date("Y-m-d");
    $logIp         = getIpReal(); //linha que captura o ip do usuario.
    $path_parts    = pathinfo($_SERVER["PHP_SELF"]);
    $logPagina     = $path_parts['basename'];
    $logParametros = '';
    $operacao      = 'Tentativa de registro de horário por fora da página correta, por alteração de endereço no browser.';

    foreach ($_REQUEST as $key => $value)
    {
        $valor = ($key == 'lSenha' ? "" : $value );
        $logParametros .= "$key: $valor :|:";
    }

    if (empty($logMatricula) || empty($pagina_de_origem))
    {
        $logQuery = "INSERT INTO ilegal_desconhecido (siape, operacao, datag, hora, maquina, setor, script, parametros) VALUES ('$logMatricula','$operacao','$logDatas','$logHoras','$logIp', '$logLotacao','$logPagina','$logParametros')";
    }
    elseif (!empty($logMatricula))
    {
        $logQuery = "INSERT INTO ilegal (siape, operacao, datag, hora, maquina, setor, script, parametros) VALUES ('$logMatricula','$operacao','$logDatas','$logHoras','$logIp', '$logLotacao','$logPagina','$logParametros')";
    }

    $logResult = $oDBase->query($logQuery);

    //mensagem("Por favor, realize o acesso através do endereço \\nhttp://www-sisref/rh ou http://www-sisref/chefia!!!\\nRegistrado o acesso indevido.", 'chefia.php', 1);
    mensagem("Por favor, realize o acesso através do endereço \\nhttps://sisref.sigepe.gov.br/rh ou https://sisref.sigepe.gov.br/chefia!!!\\nRegistrado o acesso indevido.", 'chefia.php', 1);
}
