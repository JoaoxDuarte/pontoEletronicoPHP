<?php

include_once("config.php");

// TODO: Verificar permissões de forma correta
verifica_permissao('logado');

// parametros passados por formulario
$id           = isset($_POST['id']) ? $_POST['id'] : '';
$dia          = anti_injection($_POST['dia']);
$mes          = anti_injection($_POST['mes']);
$desc         = retira_acentos(anti_injection($_POST['desc']));
$descricao    = retira_acentos(anti_injection($_POST['descricao']));
$lot          = anti_injection($_POST['lot']);
$tipo         = anti_injection($_POST['tipo']);
$codmun       = isset($_POST['codmun']) ? anti_injection($_POST['codmun']) : '';
$data_feriado = anti_injection(conv_data($_POST['data_feriado']));
$hora_inicio  = anti_injection($_POST['hora_inicio']);
$hora_termino = anti_injection($_POST['hora_termino']);
$grupo        = anti_injection($_POST['grupo']);
$sigla        = anti_injection($_POST['sigla']);
$carga_horaria = anti_injection($_POST['carga_horaria']);

// instancia o banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setMensagem('Erro no acesso ao banco de dados!');
$oDBase->setDestino($_SESSION['sHOrigem_1']);

if($id){
    $query = "
        UPDATE feriados_ponto_facultativo SET
            dia = :dia,
            mes = :mes,
            `desc` = :desc,
            descricao = :descricao,
            lot = :lot,
            tipo = :tipo,
            codmun = :codmun,
            carga_horaria = :carga_horaria,
            data_feriado = :data_feriado,
            hora_inicio = :hora_inicio,
            hora_termino = :hora_termino,
            grupo = :grupo,
            sigla = :sigla
        WHERE id = :id
    ";

    $params = array(
        array( ':dia', $dia, PDO::PARAM_STR ),
        array( ':mes', $mes, PDO::PARAM_STR ),
        array( ':desc', $desc, PDO::PARAM_STR ),
        array( ':descricao',  $descricao, PDO::PARAM_STR ),
        array( ':lot', $lot, PDO::PARAM_STR ),
        array( ':tipo',  $tipo, PDO::PARAM_STR ),
        array( ':codmun', $codmun, PDO::PARAM_STR ),
        array( ':carga_horaria', $carga_horaria, PDO::PARAM_STR ),
        array( ':data_feriado', $data_feriado, PDO::PARAM_STR ),
        array( ':hora_inicio',  $hora_inicio, PDO::PARAM_STR ),
        array( ':hora_termino', $hora_termino, PDO::PARAM_STR ),
        array( ':grupo', $grupo,  PDO::PARAM_STR ),
        array( ':sigla', $sigla,  PDO::PARAM_STR ),
        array( ':id', $id, PDO::PARAM_STR )
    );

    $oDBase->query($query, $params);

}else{

    $query = "
    INSERT INTO
         feriados_ponto_facultativo(
            dia,
            mes,
            `desc`,
            descricao,
            lot,
            tipo,
            codmun,
            carga_horaria,
            data_feriado,
            hora_inicio,
            hora_termino,
            grupo,
            sigla
        )   
    VALUES
        (
            :dia,
            :mes,
            :desc,
            :descricao,
            :lot,
            :tipo,
            :codmun,
            :carga_horaria,
            :data_feriado,
            :hora_inicio,
            :hora_termino,
            :grupo,
            :sigla
            )
    ";

    $params = array(
        array( ':dia', $dia, PDO::PARAM_STR ),
        array( ':mes', $mes, PDO::PARAM_STR ),
        array( ':desc', $desc, PDO::PARAM_STR ),
        array( ':descricao',  $descricao, PDO::PARAM_STR ),
        array( ':lot', $lot, PDO::PARAM_STR ),
        array( ':tipo',  $tipo, PDO::PARAM_STR ),
        array( ':codmun', $codmun, PDO::PARAM_STR ),
        array( ':carga_horaria', $carga_horaria, PDO::PARAM_STR ),
        array( ':data_feriado', $data_feriado, PDO::PARAM_STR ),
        array( ':hora_inicio',  $hora_inicio, PDO::PARAM_STR ),
        array( ':hora_termino', $hora_termino, PDO::PARAM_STR ),
        array( ':grupo', $grupo,  PDO::PARAM_STR ),
        array( ':sigla', $sigla,  PDO::PARAM_STR )
    );

    $oDBase->query($query, $params);
}

registraLog("Incluiu dados da tabela de feriado, na máquina de IP " . getIpReal());

$tipo     = 'success';
$msg_erro = "Salvamento dos dados do ponto facultativo registrado com sucesso!";
retornaInformacao($msg_erro, $tipo);

//exit();
