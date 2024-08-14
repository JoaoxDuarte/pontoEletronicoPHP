<?php

include_once('config.php');

$path_parts       = pathinfo($_SERVER['HTTP_REFERER']);
$pagina_de_origem = $path_parts['filename'];

// página autorizadas
$autorizadas = array( 'entrada', 'entrada1', 'entrada2', 'entrada3', 'entrada4' );

// instancia a base de dados
$oDBase = new DataBase('PDO');

if (empty($pagina_de_origem) || in_array($pagina_de_origem,$autorizadas) == false)
{
    $logLotacao    = $_SESSION["sLotacao"];
    $logMatricula  = $_SESSION["sMatricula"];
    $logHoras      = strftime("%H:%M:%S", time());
    $logDatas      = date("Y-m-d");
    $logIp         = getIpReal(); //linha que captura o ip do usuario.
    $path_parts    = pathinfo($_SERVER["PHP_SELF"]);
    $logPagina     = $path_parts['basename'];
    $logParametros = '';
    $operacao      = 'Tentativa de registro de horário por fora da página entrada.php por alteração de endereço no browser.';

    if (isset($_REQUEST))
    {
        foreach ($_REQUEST as $key => $value)
        {
            $valor         = ($key == 'lSenha' ? "" : $value );
            $logParametros .= "$key: $valor :|:";
        }
    }

    if (empty($logMatricula) || empty($pagina_de_origem))
    {
        $logQuery = "
        INSERT INTO ilegal_desconhecido
        SET
            siape      = :siape,
            operacao   = :operacao,
            datag      = :datag,
            hora       = :hora,
            maquina    = :maquina,
            setor      = :setor,
            script     = :script,
            parametros = :parametros
        ";
    }
    elseif (!empty($logMatricula))
    {
        $logQuery = "
        INSERT INTO ilegal
        SET
            siape      = :siape,
            operacao   = :operacao,
            datag      = :datag,
            hora       = :hora,
            maquina    = :maquina,
            setor      = :setor,
            script     = :script,
            parametros = :parametros
        ";
    }

    $logResult = $oDBase->query($logQuery,
        array(
            array( ':siape',      $logMatricula,  PDO::PARAM_STR ),
            array( ':operacao',   $operacao,      PDO::PARAM_STR ),
            array( ':datag',      $logDatas,      PDO::PARAM_STR ),
            array( ':hora',       $logHoras,      PDO::PARAM_STR ),
            array( ':maquina',    $logIp,         PDO::PARAM_STR ),
            array( ':setor',      $logLotacao,    PDO::PARAM_STR ),
            array( ':script',     $logPagina,     PDO::PARAM_STR ),
            array( ':parametros', $logParametros, PDO::PARAM_STR ),
        ));

    ## classe para montagem do formulario padrao
    #
    $oForm = new formPadrao();
    $oForm->setTituloTela('1');
    $oForm->exibeTopoHTML();
    $oForm->exibeCorpoTopoHTML();

    $oForm->exibeCorpoBaseHTML();
    $oForm->exibeBaseHTML();

    mensagem("Por favor, realize o acesso através do endereço http:\/\/www-sisref\/!\\nRegistrado o acesso indevido.", 'entrada.php');
    
    exit();
}
