<?php

include_once("config.php");
include_once("class_definir.jornada.php");
include_once("class_ocorrencias_grupos.php");
include_once("comparecimento_tabela_auxiliar.php");
include_once("src/controllesrs/DadosServidoresController.php");


/**
 * @param void
 * @return result
 */
function selecionaServidoresConsultaMedicaLista($matricula=NULL,$dia=null)
{
    $dia = (is_null($dia) ? date('d/m/Y') : $dia);

    $mes = dataMes($dia);
    $ano = dataAno($dia);

    if (is_null( $matricula ))
    {
        $where  = "
            compareceu_consulta_medica.setor = :setor
            AND compareceu_consulta_medica.siape <> :usuario
        ";
        $params = array(
            array(":setor",   $_SESSION['sLotacao'],   PDO::PARAM_STR),
            array(":usuario", $_SESSION['sMatricula'], PDO::PARAM_STR),
        );
    }
    else
    {
        // Matricula no padrao orgao+siape
        $matricula = getNovaMatriculaBySiape($matricula);

        $where  = "
            compareceu_consulta_medica.siape = :siape
            AND compareceu_consulta_medica.siape <> :usuario
        ";
        $params = array(
            array(":siape",   $matricula,              PDO::PARAM_STR),
            array(":usuario", $_SESSION['sMatricula'], PDO::PARAM_STR),
        );
    }

    $oDBase = new DataBase('PDO');

    $query = "
        SELECT
            compareceu_consulta_medica.id                          AS id,
            compareceu_consulta_medica.siape                       AS matricula,
            servativ.nome_serv                                     AS servidor,
            DATE_FORMAT(compareceu_consulta_medica.dia,'%d/%m/%Y') AS dia,
            compareceu_consulta_medica.hora_ini                    AS hora_ini,
            compareceu_consulta_medica.hora_fim                    AS hora_fim,
            compareceu_consulta_medica.tempo_consulta              AS consulta,
            /*compareceu_consulta_medica.deslocamento                AS deslocamento,*/
            SEC_TO_TIME(
                SUM(TIME_TO_SEC(compareceu_consulta_medica.tempo_consulta)
                /*+
                TIME_TO_SEC(compareceu_consulta_medica.deslocamento)*/)) AS total,
            (SELECT SEC_TO_TIME(
                SUM(TIME_TO_SEC(consulta.tempo_consulta)
                /*+
                TIME_TO_SEC(consulta.deslocamento)*/))
                FROM compareceu_consulta_medica AS consulta
                WHERE consulta.siape = compareceu_consulta_medica.siape) AS acumulado,
            SEC_TO_TIME(TIME_TO_SEC(CONCAT((servativ.jornada / 5 * 5.5),':00:00'))) AS limite,
            compareceu_consulta_medica.setor                               AS setor,
            compareceu_consulta_medica.idreg                               AS idreg,
            IF(DATE_FORMAT(dia,'%Y%m') = '".$ano.$mes."','editar','false') AS editar
        FROM
            compareceu_consulta_medica
        LEFT JOIN
            servativ ON compareceu_consulta_medica.siape = servativ.mat_siape
        WHERE
            " . $where . "
            AND YEAR(compareceu_consulta_medica.dia) = YEAR(NOW())
        GROUP BY
            compareceu_consulta_medica.siape, compareceu_consulta_medica.dia
        ORDER BY
            compareceu_consulta_medica.siape, compareceu_consulta_medica.dia
    ";

    $oDBase->query( $query, $params );

    return $oDBase;
}

/**
 * @param string $matricula
 * @param date $dia
 * @return bool
 */
function verifyComparecimentoConsultaMedicaDataJaCadastrada($matricula, $dia)
{
    // Matricula no padrao orgao+siape
    $matricula = getNovaMatriculaBySiape($matricula);

    $oDBase = new DataBase('PDO');

    $query = "
        SELECT
            id, siape, dia
        FROM compareceu_consulta_medica
        WHERE
            siape = :siape
            AND dia = :dia
    ";

    $oDBase->query($query, array(
        array(":siape", $matricula,      PDO::PARAM_STR),
        array(":dia",   conv_data($dia), PDO::PARAM_STR)
    ));

    return ($oDBase->num_rows() > 0);
}

/**
 * @param $matricula
 * @return object|stdClass
 * @info Retorna saldo do o servidor
 */
function verifySaldoComparecimentoConsultaMedica($matricula)
{
    // Matricula no padrao orgao+siape
    $matricula = getNovaMatriculaBySiape($matricula);

    $oDBase = new DataBase('PDO');

    $query = "
    SELECT
	    SEC_TO_TIME(
	    	SUM(TIME_TO_SEC(compareceu_consulta_medica.tempo_consulta))
    	) AS acumulado,
    	SEC_TO_TIME(
    		(TIME_TO_SEC(CONCAT((servativ.jornada / 5 * 5.5),':00:00')))
    		-
    		(SUM(TIME_TO_SEC(compareceu_consulta_medica.tempo_consulta)))
    	) AS saldo,
    	SEC_TO_TIME(TIME_TO_SEC(CONCAT((servativ.jornada / 5 * 5.5),':00:00'))) AS limite
    FROM
        compareceu_consulta_medica
    LEFT JOIN
        servativ ON compareceu_consulta_medica.siape = servativ.mat_siape
    WHERE
        compareceu_consulta_medica.siape = :siape
        AND compareceu_consulta_medica.siape <> :usuario
        AND YEAR(compareceu_consulta_medica.dia) = YEAR(NOW())
    GROUP BY
        compareceu_consulta_medica.siape
    ";

    $params = array(
        array(":siape",   $matricula,              PDO::PARAM_STR),
        array(":usuario", $_SESSION['sMatricula'], PDO::PARAM_STR),
    );

    $oDBase->query( $query, $params );

    return $oDBase->fetch_assoc();
}

/**
 * @param void
 * @return DataBase
 */
function createComparecimentoConsultaMedica()
{
    $return = false;

    $diferenca = (time_to_sec($_POST['hora_fim']) - time_to_sec($_POST['hora_ini']));

    $dia            = conv_data($_POST['dia']);
    $matricula      = $_POST['servidor'];
    $hora_ini       = $_POST['hora_ini'];
    $hora_fim       = $_POST['hora_fim'];
    $tempo_consulta = sec_to_time($diferenca,'hh:mm');
    $deslocamento   = '00:00:00'; //$_POST['deslocamento']; // temporariamente suspenso (zerado)
    $setor          = $_SESSION['uorg'];
    $idreg          = 'C';
    $registro_ip    = getIpReal();
    $registro_siape = $_SESSION['sMatricula'];

    // Matricula no padrao orgao+siape
    $matricula = getNovaMatriculaBySiape($matricula);

    if (time_to_sec($tempo_consulta) > 0)
    {
        $codigoConsultaMedica = codigoComparecimentoConsultaMedicaPadrao($matricula);

        $oDBase = new DataBase('PDO');

        $sql = "
        INSERT INTO compareceu_consulta_medica
            (id, dia, siape, hora_ini, hora_fim, tempo_consulta, deslocamento, setor, idreg, registro_ip, registro_data, registro_siape)
            VALUES
            (0, :dia, :siape, :hora_ini, :hora_fim, :tempo_consulta, :deslocamento, :setor, :idreg, :registro_ip, NOW(), :registro_siape)
        ";

        $params = array(
            array(":dia",            $dia,            PDO::PARAM_STR),
            array(":siape",          $matricula,      PDO::PARAM_STR),
            array(":hora_ini",       $hora_ini,       PDO::PARAM_STR),
            array(":hora_fim",       $hora_fim,       PDO::PARAM_STR),
            array(":tempo_consulta", $tempo_consulta, PDO::PARAM_STR),
            array(":deslocamento",   $deslocamento,   PDO::PARAM_STR),
            array(":setor",          $setor,          PDO::PARAM_STR),
            array(":idreg",          $idreg,          PDO::PARAM_STR),
            array(":registro_ip",    $registro_ip,    PDO::PARAM_STR),
            array(":registro_siape", $registro_siape, PDO::PARAM_STR),
        );

        // inclusão
        $oDBase->query( $sql, $params );

        // historico
        $sql      = strtr($sql,array('compareceu_consulta_medica' => 'compareceu_consulta_medica_historico'));
        $sql      = strtr($sql,array(', registro_siape)' => ", registro_siape, acao) "));
        $sql      = strtr($sql,array(', :registro_siape)' => ", :registro_siape, :acao) "));
        $params[] = array(":acao", 'I', PDO::PARAM_STR);

        $oDBase->query( $sql, $params );

        // registra em log a ação
        $oServidor = selecionaServidor($matricula);
        $nome_servidor = $oServidor->fetch_object()->nome_serv;
        registraLog( "Incluido registro de comparecimento a consulta médica de ".$nome_servidor.", dia ".databarra($dia) );

        $paramsPonto = array(
            'dia'            => $dia,
            'matricula'      => $matricula,
            'hora_ini'       => $hora_ini,
            'hora_fim'       => $hora_fim,
            'tempo_consulta' => $tempo_consulta,
            'deslocamento'   => $deslocamento,
            'setor'          => $setor,
            'idreg'          => $idreg,
            'oco'            => $codigoConsultaMedica,
            'registro_ip'    => $registro_ip,
            'registro_siape' => $registro_siape,
        );

        $horas = verifySaldoComparecimentoConsultaMedica($matricula);

        incluirPontoAuxiliar( $paramsPonto, $horas['saldo'] );

        $return = true;
    }

    return $return;
}

/**
 * @param void
 * @return void
 */
function deleteComparecimentoConsultaMedica($idreg="C")
{
    $oDBase = new DataBase('PDO');

    // dados do servidor
    $oDBase->query("
    SELECT
        compareceu_consulta_medica.dia,
        compareceu_consulta_medica.siape,
        servativ.nome_serv AS nome,
        compareceu_consulta_medica.hora_ini,
        compareceu_consulta_medica.hora_fim,
        compareceu_consulta_medica.tempo_consulta,
        compareceu_consulta_medica.deslocamento,
        compareceu_consulta_medica.setor
    FROM
        compareceu_consulta_medica
    LEFT JOIN
        servativ ON compareceu_consulta_medica.siape = servativ.mat_siape
    WHERE
        compareceu_consulta_medica.id = :id
    ",
    array(
        array(":id", $_GET['id'], PDO::PARAM_INT),
    ));
    $oDados = $oDBase->fetch_object();

    $sql = "
    INSERT INTO compareceu_consulta_medica_historico
    SET
        dia            = :dia,
        siape          = :siape,
        hora_ini       = :hora_ini,
        hora_fim       = :hora_fim,
        tempo_consulta = :tempo_consulta,
        deslocamento   = :deslocamento,
        setor          = :setor,
        idreg          = :idreg,
        acao           = :acao,
        registro_ip    = :registro_ip,
        registro_data  = NOW(),
        registro_siape = :registro_siape
    ";

    $params = array(
        array(":dia",            $oDados->dia,            PDO::PARAM_STR),
        array(":siape",          $oDados->siape,          PDO::PARAM_STR),
        array(":hora_ini",       $oDados->hora_ini,       PDO::PARAM_STR),
        array(":hora_fim",       $oDados->hora_fim,       PDO::PARAM_STR),
        array(":tempo_consulta", $oDados->tempo_consulta, PDO::PARAM_STR),
        array(":deslocamento",   $oDados->deslocamento,   PDO::PARAM_STR),
        array(":setor",          $oDados->setor,          PDO::PARAM_STR),
        array(":idreg",          $idreg,                  PDO::PARAM_STR),
        array(":acao",           'E',                     PDO::PARAM_STR),
        array(":registro_ip",    getIpReal(),             PDO::PARAM_STR),
        array(":registro_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
    );

    // código consulta médica
    $codigoConsultaMedica = codigoComparecimentoConsultaMedicaPadrao($oDados->siape);
    
    // inclusão historico
    $oDBase->query( $sql, $params );

    deletePontoAuxiliar($params, $codigoConsultaMedica);

    // apaga o registro
    $oDBase->query("DELETE FROM compareceu_consulta_medica WHERE compareceu_consulta_medica.id = :id", array(array(":id", $_GET['id'], PDO::PARAM_INT)));
    
    // registra em log a ação
    registraLog("Deletado registro de comparecimento a consulta médica de ".$oDados->nome.", dia ".databarra($oDados->dia));

    if ($oDBase->affected_rows() > 0)
    {
        mensagem( "Registro excluído com sucesso!" );
    }
    else
    {
        mensagem( "Problema na exclusão do registro!" );
    }
}


/**
 * @param string $mat
 * @return string Código ocorrência consulta médica
 */
function codigoComparecimentoConsultaMedicaPadrao($mat)
{
    // dados servidodr
    $dadosServidor = new DadosServidoresController();
    $sitcad = $dadosServidor->getSigRegJur( $mat );
    
    // código consulta médica
    $objOcorrenciasGrupos = new OcorrenciasGrupos();
    $codigoConsultaMedicaPadrao = $objOcorrenciasGrupos->CodigoConsultaMedicaPadrao( $sitcad );
    
    return $codigoConsultaMedicaPadrao[0];
}
