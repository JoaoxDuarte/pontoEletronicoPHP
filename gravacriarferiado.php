<?php

include_once("config.php");

verifica_permissao('logado');

// parametros passados por formulario
$dia          = anti_injection($_POST['dia']);
$mes          = anti_injection($_POST['mes']);
$desc         = retira_acentos(anti_injection($_POST['desc']));
$lot          = anti_injection($_POST['lot']);
$tipo         = anti_injection($_POST['tipo']);
$codmun       = anti_injection($_POST['codmun']);
$base_legal   = retira_acentos(anti_injection($_POST['base_legal']));
$ano          = date("Y");
$data_feriado = $ano . '-' . $mes . '-' . $dia;

// instancia o banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setMensagem('Erro no acesso ao banco de dados!');
$oDBase->setDestino($_SESSION['sHOrigem_1']);

$query = "
    INSERT INTO
        feriados_".date('Y')."(
            dia,
            mes,
            lot,
            tipo,
            codmun,
            base_legal,
            data_feriado,
            `desc`
        )   
    VALUES
        (
            :dia,
            :mes,
            :lot,
            :tipo,
            :codmun,
            :base_legal,
            :data_feriado,
            :desc
         )
";

$params = array(
    array( ':dia',          $dia,          PDO::PARAM_STR ),
    array( ':mes',          $mes,          PDO::PARAM_STR ),
    array( ':lot',          $lot,          PDO::PARAM_STR ),
    array( ':tipo',         $tipo,         PDO::PARAM_STR ),
    array( ':codmun',       $codmun,       PDO::PARAM_STR ),
    array( ':base_legal',   $base_legal,   PDO::PARAM_STR ),
    array( ':data_feriado', $data_feriado, PDO::PARAM_STR ),
    array( ':desc',         $desc,         PDO::PARAM_STR ),
);

$oDBase->query($query, $params);

$query = "
    INSERT INTO
        feriados(
            dia,
            mes,
            lot,
            tipo,
            codmun,
            base_legal,
            data_feriado,
            `desc`
        )   
    VALUES
        (
            :dia,
            :mes,
            :lot,
            :tipo,
            :codmun,
            :base_legal,
            :data_feriado,
            :desc
         )
";

$params = array(
    array( ':dia',          $dia,          PDO::PARAM_STR ),
    array( ':mes',          $mes,          PDO::PARAM_STR ),
    array( ':lot',          $lot,          PDO::PARAM_STR ),
    array( ':tipo',         $tipo,         PDO::PARAM_STR ),
    array( ':codmun',       $codmun,       PDO::PARAM_STR ),
    array( ':base_legal',   $base_legal,   PDO::PARAM_STR ),
    array( ':data_feriado', $data_feriado, PDO::PARAM_STR ),
    array( ':desc',         $desc,         PDO::PARAM_STR ),
);

$oDBase->query($query, $params);

// grava o LOG
registraLog("Incluiu dados da tabela de feriado, na máquina de IP " . getIpReal());
// fim do LOG

$tipo     = 'success';
$msg_erro = "Inclusão dos feriado registrado com sucesso!";
retornaInformacao($msg_erro, $tipo);

//exit();
