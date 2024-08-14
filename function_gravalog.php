<?php

date_default_timezone_set('America/Sao_Paulo');

function GravaLog($texto)
{
    $data = date("Ymd");
    $hora = date("H:i:s");
    $ip_client = $_SERVER['REMOTE_ADDR'];
    $ip_server = $_SERVER['SERVER_ADDR'];

    //pega o path completo
    $caminho_atual = getcwd();

    //muda o contexto de execução para a pasta logs
    //chdir($caminho_atual."/logs");

    //Nome do arquivo:
    $arquivo = "sisref_$data.log";

    //Texto a ser impresso no log:
    $texto = "[$hora][$ip_client][$ip_server]> $texto \n";

    $manipular = fopen("$arquivo", "a+b");
    fwrite($manipular, $texto);
    fclose($manipular);

    //Volta o contexto de execução para o caminho em que estava antes
    //chdir($caminho_atual);
}