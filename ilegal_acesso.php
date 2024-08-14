<?php

include_once('config.php');

$path_parts       = pathinfo($_SERVER['HTTP_REFERER']);
$pagina_de_origem = $path_parts['filename'];

// instancia a base de dados
$oDBase = new DataBase('PDO');

// hosts com acesso autorizado
$oDBase->query("SELECT ip_do_host FROM tabhosts_acesso_autorizado WHERE ip_do_host= :ip_do_host AND autorizado='S' ",array(
    array(":ip_do_host", ltrim(rtrim($_SERVER['SERVER_ADDR'])), PDO::PARAM_STR)
));

if (empty($pagina_de_origem) || ($oDBase->num_rows() == 0))
{
    $logLotacao    = $_SESSION["sLotacao"];
    $logMatricula  = $_SESSION["sMatricula"];
    $logHoras      = strftime("%H:%M:%S", time());
    $logDatas      = date("Y-m-d");
    $logIp         = getIpReal(); //linha que captura o ip do usuario.
    $path_parts    = pathinfo($_SERVER["PHP_SELF"]);
    $logPagina     = $path_parts['basename'];
    $logParametros = '';
    $operacao      = 'Tentativa de acesso por fora da p�gina do SISTEMA, por altera��o de endere�o no browser.';

    foreach ($_REQUEST as $key => $value)
    {
        $valor         = ($key == 'lSenha' ? "" : $value );
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

    mensagem("2 Por favor, realize o acesso atrav�s do endere�o http://www-sisref/ !!!\\nRegistrado o acesso indevido.", 'entrada.php', 1);
}
