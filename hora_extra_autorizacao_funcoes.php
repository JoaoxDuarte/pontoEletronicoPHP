<?php

include_once("config.php");
include_once("class_definir.jornada.php");
include_once("class_ocorrencias_grupos.php");


// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('logado');


/*
 * @info Seleciona registros com base no campo e dados informados
 *
 * @param string $var1    Campo de pesquisar
 * @param string $var2    Valor a pesquisar
 * @param string $groupby Campo a agrupar
 * @return  object  Resultado da pesquisa
 *
 * @author Edinalvo Rosa
 */
function pesquisaChaveEscolha($var1='',$var2='',$groupby="siape")
{
    if ($groupby !== "siape")
    {
        $_SESSION['sChaveCriterioExtra'] = array("chave" => $var1, "escolha" => $var2);
    }

    $query = "
    SELECT
        IFNULL(autorizacoes_hora_extra.id,0) AS id,
        servativ.mat_siape       AS matricula,
        servativ.mat_siape       AS siape,
        servativ.nome_serv       AS nome,
        servativ.cod_lot         AS setor,
        servativ.horae           AS horae,
        servativ.motivo          AS motivo,
        servativ.limite_horas    AS limite_horas,
        tabcargo.PERMITE_BANCO   AS permite_banco,
        tabsetor.periodo_excecao AS excecao,
        autorizacoes_hora_extra.data_inicio,
        autorizacoes_hora_extra.data_fim,
        CONCAT(
            DATE_FORMAT(autorizacoes_hora_extra.data_inicio,'%d/%m/%Y'),
            ' a ',
            DATE_FORMAT(autorizacoes_hora_extra.data_fim,'%d/%m/%Y')
        ) AS periodo,
        autorizacoes_hora_extra.horas
    FROM
        servativ
    LEFT JOIN
        autorizacoes_hora_extra ON servativ.mat_siape = autorizacoes_hora_extra.siape
    LEFT JOIN
        tabsetor ON servativ.cod_lot = tabsetor.codigo
    LEFT JOIN
        tabcargo ON servativ.cod_cargo = tabcargo.cod_cargo
    WHERE
        servativ.cod_sitcad NOT IN ('02','08','15','66')
        AND servativ.excluido = 'N'
        AND servativ.mat_siape <> :usuario
    ";

    $params = array();
    $params[] = array(":usuario", $_SESSION['sMatricula'], PDO::PARAM_STR);

    if ($_SESSION["sLog"] != "S" || $_SESSION["sLog"] == "S")
    {
        $query .= " AND tabsetor.upag = :upag ";
        $params[] = array(":upag", $_SESSION['upag'], PDO::PARAM_STR);
    }

    if ($_SESSION["sRH"] != "S" && $_SESSION["sAPS"] == "S")
    {
        $query .= " AND servativ.cod_lot = :lotacao ";
        $params[] = array(":lotacao", $_SESSION['sLotacao'], PDO::PARAM_STR);
    }

    switch ($var2)
    {
        case "siape":
            $novamatricula = getNovaMatriculaBySiape($var1);
            $query .= " AND servativ.mat_siape = :siape ";
            $params[] = array(":siape", $novamatricula, PDO::PARAM_STR);
            break;

        case "nome":
            $query .= " AND servativ.nome_serv LIKE :nome ";
            $params[] = array(":nome", "%$var1%", PDO::PARAM_STR);
            break;

        case "cargo":
            $query .= "
                AND (servativ.cod_cargo = :cargo
                OR tabcargo.desc_cargo LIKE :descricao)
            ";
            $params[] = array(":cargo",     $var1,     PDO::PARAM_STR);
            $params[] = array(":descricao", "%$var1%", PDO::PARAM_STR);
            break;

        case "lotacao":
            // uso de ":unidade" para não conflitar com ":lotacao" usada acima
            $query .= " AND servativ.cod_lot LIKE :unidade ";
            $params[] = array(":unidade", "%$var1%", PDO::PARAM_STR);
            break;
    }

    if ($groupby !== 'siape')
    {
        $query .= "
        AND NOT ISNULL(autorizacoes_hora_extra.data_inicio)
        AND NOT ISNULL(autorizacoes_hora_extra.data_fim)
        ";
    }

    $_SESSION['sSQLPesquisa'] = $query;

    if ($groupby == "siape")
    {
        $query .= "GROUP BY servativ.mat_siape ";
    }

    if ($var2 == "lotacao")
    {
        $query .= "ORDER BY servativ.cod_lot ";
    }
    else
    {
        $query .= "ORDER BY servativ.nome_serv, autorizacoes_hora_extra.data_inicio DESC ";
    }

    $oDBase = new DataBase('PDO');

    //if (!empty($var2))
    //{
        $oDBase->query($query, $params);
    //}

    return $oDBase;
}

/**
 * @info Cria uma nova autorização para o servidor ou atualiza a existente
 *
 * @param $post
 * @return bool|int|null|PDOStatement|resource
 */
function createUpdateAutorizacaoHoraExtra($post)
{
    $return = false;

    $post['siape'] = getNovaMatriculaBySiape($post['siape']);

    $oDBase = new DataBase('PDO');

    // CONSULTA PARA IDENTIFICAR SE JÁ EXISTE AUTORIZAÇÃO PARA O SERVIDOR
    $querySearch = "
    SELECT
        siape
            FROM autorizacoes_hora_extra
                WHERE autorizacoes_hora_extra.id = :id";
    $paramnsSearch = array(
        array(":id", $post['id'], PDO::PARAM_STR)
    );

    $oDBase->query($querySearch, $paramnsSearch);

    // SE EXISTIR SERA ATUALIZADO COM OS NOVOS VALORES, CASO CONTRÁRIO SERA CRIADO UM NOVO
    if ($oDBase->num_rows() > 0)
    {
        $numrows = updateAutorizacaoHoraExtra($post);
    }
    else
    {
        $numrows = insertAutorizacaoHoraExtra($post);
    }

    $return = ($numrows > 0);

    return $return;
}

/**
 * @info INSERT Autorização Serviço Extraordinário
 *
 * @param array $post
 */
function insertAutorizacaoHoraExtra($post)
{
    $queryInsert = "
    INSERT INTO autorizacoes_hora_extra
        SET
            siape                = :siape,
            setor                = :setor,
            data_inicio          = :data_inicio,
            data_fim             = :data_fim,
            horas                = :horas,
            documento            = :documento,
            acrescimo_autorizado = :acrescimo_autorizado,
            registrado_por       = :registrado_por,
            registrado_data      = NOW()
    ";

    $paramnsInsert = array(
        array(":siape",                $post['siape'],                  PDO::PARAM_STR),
        array(":setor",                $post['setor'],                  PDO::PARAM_STR),
        array(":data_inicio",          conv_data($post['data_inicio']), PDO::PARAM_STR),
        array(":data_fim",             conv_data($post['data_fim']),    PDO::PARAM_STR),
        array(":horas",                $post['horas'],                  PDO::PARAM_STR),
        array(":documento",            $post['documento'],              PDO::PARAM_STR),
        array(":acrescimo_autorizado", $post['acrescimo_autorizado'],   PDO::PARAM_STR),
        array(":registrado_por",       $_SESSION['sMatricula'],         PDO::PARAM_STR),
    );

    $oDBase = new DataBase();
    $oDBase->query($queryInsert, $paramnsInsert);
    registraLog("Inclusão de autorização de serviço(s) estraordinário(s) (Horas Extras)");

    return $oDBase->affected_rows();
}

/**
 * @info UPDATE Autorização Serviço Extraordinário
 *
 *
 * @param array $post
 */
function updateAutorizacaoHoraExtra($post)
{
    gravar_historico_hora_extra($post['id'], 'A');

    $queryUpdate = "
    UPDATE autorizacoes_hora_extra
        SET autorizacoes_hora_extra.data_inicio = :data_inicio,
            autorizacoes_hora_extra.data_fim = :data_fim,
            autorizacoes_hora_extra.horas = :horas,
            autorizacoes_hora_extra.documento = :documento,
            autorizacoes_hora_extra.acrescimo_autorizado = :acrescimo_autorizado,
            autorizacoes_hora_extra.registrado_por = :registrado_por
                WHERE id = :id";

    $paramnsUpdate = array(
        array(":id",                   $post['id'],                     PDO::PARAM_STR),
        array(":data_inicio",          conv_data($post['data_inicio']), PDO::PARAM_STR),
        array(":data_fim",             conv_data($post['data_fim']),    PDO::PARAM_STR),
        array(":horas",                $post['horas'],                  PDO::PARAM_STR),
        array(":documento",            $post['documento'],              PDO::PARAM_STR),
        array(":acrescimo_autorizado", $post['acrescimo_autorizado'],   PDO::PARAM_STR),
        array(":registrado_por",       $_SESSION['sMatricula'],         PDO::PARAM_STR)
    );

    $oDBase = new DataBase('PDO');
    $oDBase->query($queryUpdate, $paramnsUpdate);

    registraLog("Alteração da autorização de serviço(s) estraordinário(s) (Horas Extras)");

    return $oDBase->affected_rows();
}

/**
 * @param integer $id   Número do registro
 * @param string  $nome Nome do servidor
 * @return void
 */
function deleteAutorizacaoHoraExtra($id,$nome)
{
    $oDBase = new DataBase('PDO');

    // CONSULTA PARA IDENTIFICAR SE JÁ EXISTE AUTORIZAÇÃO PARA O SERVIDOR
    $query = "
    SELECT
        siape
            FROM autorizacoes_hora_extra
                WHERE autorizacoes_hora_extra.id = :id";
    $params = array(
        array(":id", $id, PDO::PARAM_STR),
    );

    $oDBase->query($query, $params);

    // SE EXISTIR SERA DELETADO
    if ($oDBase->num_rows() > 0)
    {
        gravar_historico_hora_extra($id, 'E');

        // apaga o registro
        $oDBase->query( "
        DELETE FROM autorizacoes_hora_extra
            WHERE autorizacoes_hora_extra.id = :id",
        array(
            array(":id", $id, PDO::PARAM_INT)));

        // registra em log a ação
        registraLog("Deletado registro de período de serviço extraordinário de ".$nome);
    }
}

function gravar_historico_hora_extra($id, $oper='A')
{
    $oDBase = new DataBase('PDO');

    // inclusão historico
    $oDBase->query( "
    INSERT autorizacoes_hora_extra_historico
        SELECT 0, autorizacoes_hora_extra.siape,
            autorizacoes_hora_extra.setor,
            autorizacoes_hora_extra.data_inicio,
            autorizacoes_hora_extra.data_fim,
            autorizacoes_hora_extra.horas,
            autorizacoes_hora_extra.documento,
            autorizacoes_hora_extra.acrescimo_autorizado,
            autorizacoes_hora_extra.registrado_por,
            autorizacoes_hora_extra.registrado_data,
            autorizacoes_hora_extra.homologado_por,
            autorizacoes_hora_extra.homologado_data, :idreg, :siape, NOW()
                    FROM autorizacoes_hora_extra
                        WHERE autorizacoes_hora_extra.id = :id
    ",
    array(
        array(":id",    $id,                     PDO::PARAM_STR),
        array(":siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
        array(":idreg", $oper,                   PDO::PARAM_STR)
    ));
}

/**
 * @param $siape
 * @param $start_date
 * @param $end_date
 * @return bool  Retorna FALSE se data já cadastrada para o servidor.
 * @info Verifica período de hora extra
 */
function verificaSePeriodoHoraExtraJaCadastrado($post)
{
    $siape      = $post['siape'];
    $start_date = $post['data_inicio'];
    $end_date   = $post['data_fim'];
    $id         = $post['id'];

    $siape = getNovaMatriculaBySiape($siape);

    $oDBase = new DataBase('PDO');

    $oDBase->query("
    SELECT
        IFNULL((SELECT
                    id
                FROM
                    autorizacoes_hora_extra
                WHERE
                    autorizacoes_hora_extra.id <> :id
                    AND autorizacoes_hora_extra.siape = :siape
                    AND (:start_date >= autorizacoes_hora_extra.data_inicio
                         AND :start_date <= autorizacoes_hora_extra.data_fim)),0) AS inicio_cadastrado,
        IFNULL((SELECT
                    id
                FROM
                    autorizacoes_hora_extra
                WHERE
                    autorizacoes_hora_extra.id <> :id
                    AND autorizacoes_hora_extra.siape = :siape
                    AND (:end_date >= autorizacoes_hora_extra.data_inicio
                         AND :end_date <= autorizacoes_hora_extra.data_fim)),0) AS fim_cadastrado",
        array(
            array(":id",         $id,                    PDO::PARAM_INT),
            array(":siape",      $siape,                 PDO::PARAM_STR),
            array(":start_date", conv_data($start_date), PDO::PARAM_STR),
            array(":end_date",   conv_data($end_date),   PDO::PARAM_STR),
        )
    );

    return $oDBase->fetch_object();
}

/**
 * @param $siape
 * @param $dia
 * @return boolean  TRUE há autorização hora extra
 * @info Verifica se autorizado hora extra
 */
function verificaSeHaAutorizacaoHoraExtra($siape,$dia)
{
    $matricula = getNovaMatriculaBySiape($siape);

    $oDBase = new DataBase('PDO');

    $oDBase->query("
    SELECT
        siape
            FROM
                autorizacoes_hora_extra
                    WHERE
                        autorizacoes_hora_extra.siape = :siape
                        AND (:dia >= autorizacoes_hora_extra.data_inicio
                            AND :dia <= autorizacoes_hora_extra.data_fim)
    ",
    array(
        array(":siape", $matricula,      PDO::PARAM_STR),
        array(":dia",   conv_data($dia), PDO::PARAM_STR),
    ));

    return ($oDBase->num_rows() > 0);
}

/**
 * @param void
 * @return array  Com campos como chave e seus valores limite
 * @info Carrega limites de hora extra
 */
function configLimitesHoraExtra()
{
    $array = array();

    $oDBase = new DataBase('PDO');

    $oDBase->query("SELECT
                        campo, minutos
                            FROM config_basico
                                WHERE grupo = 'hora_extra'");

    while ($rows = $oDBase->fetch_object())
    {
        $array[$rows->campo] = $rows->minutos;
    }

    return $array;

}

/**
 * @param  float
 * @return  float  Horas extras acumuladas no exercício
 * @info Carrega limites de hora extra
 */
function acumuladoHoraExtraNoAno($siape,$ano=NULL,$retorna='sec')
{
    $siape = getNovaMatriculaBySiape($siape);

    $ano = (is_null($ano) ? date('Y') : $ano);

    $horas_acumuladas = 0;

    $sql = "
    SELECT
        SUM(TIME_TO_SEC(horas)) AS segundos,
        IF(LENGTH(SEC_TO_TIME(SUM(TIME_TO_SEC(horas)))) > 8,
            SUBSTR(SEC_TO_TIME(SUM(TIME_TO_SEC(horas))),1,6),
            SUBSTR(SEC_TO_TIME(SUM(TIME_TO_SEC(horas))),1,5)) AS hhmm
    FROM autorizacoes_hora_extra
    WHERE
        siape = :siape
        AND YEAR(data_inicio) = :ano
    ";

    $params = array(
        array( ':siape', $siape, PDO::PARAM_STR ),
        array( ':ano',   $ano,   PDO::PARAM_STR ),
    );

    $oDBase = new DataBase('PDO');

    $oDBase->query( $sql, $params );

    $dados = $oDBase->fetch_object();

    if ($retorna == 'sec')
    {
        $horas_acumuladas = (int) $dados->segundos;
    }
    else
    {
        $horas_acumuladas = $dados->hhmm;
    }

    return $horas_acumuladas;
}

function verificaHoraExtraNoPeriodo($siape, $start_date, $end_date, $horas, $acrescimo)
{
    $siape = getNovaMatriculaBySiape($siape);

    $mensagem = "";

    $limites = configLimitesHoraExtra();
    $maximo_dia           = time_to_sec($limites['limite_diario_hora_extra']);
    $maximo_mes           = time_to_sec($limites['limite_mensal_hora_extra']);
    $maximo_ano           = time_to_sec($limites['limite_anual_hora_extra']);
    $maximo_ano_acrescimo = time_to_sec($limites['limite_anual_acrescimo_hora_extra']);

    $maximo_ano_com_acrescimo = ($maximo_ano + $maximo_ano_acrescimo);

    $dias_no_periodo = (dif_data(conv_data($start_date),conv_data($end_date))+1);
    $meses_no_periodo = ((dataMes($end_date) - dataMes($start_date)) + 1);

    // divide horas informadas por dias no período, para
    // verificar se excedem as 2 horas permitidas por dia
    $horas_por_dias = ($horas / $dias_no_periodo);

    $horas_acumuladas = acumuladoHoraExtraNoAno($siape,dataAno($start_date),$retorna='sec');

    $horas_totais = $horas + $horas_acumuladas;

    // testa limites horas no ano
    if (($horas > $maximo_ano_com_acrescimo) || ($horas_totais > $maximo_ano_com_acrescimo))
    {
        $mensagem .= (empty($mensagem) ? "" : "<br>")
                     . "Horas informadas/acumuladas acima do limite máximo de "
                     . sec_to_time($maximo_ano_com_acrescimo,'hh:mm')
                     . ' (' . sec_to_time($maximo_ano,'hh:mm')
                     . ' + '
                     . sec_to_time($maximo_ano_acrescimo,'hh:mm')
                     . ')';
    }
    else if (($horas > $maximo_ano || $horas_totais > $maximo_ano) && $acrescimo == 'N')
    {
        $mensagem .= (empty($mensagem) ? "" : "<br>")
                     . "Horas informadas/acumuladas acima do limite máximo anual de "
                     . sec_to_time($maximo_ano,'hh:mm');
    }
    else if ($dias_no_periodo == 1 && $horas > $maximo_dia)
    {
        $mensagem .= (empty($mensagem) ? "" : "<br>")
                     . "Horas informadas/acumuladas acima do limite diário ("
                     . sec_to_time($maximo_dia,'hh:mm')
                     . ')';
    }
    else if ($meses_no_periodo == 1 && $horas > $maximo_mes)
    {
        $mensagem .= (empty($mensagem) ? "" : "<br>")
                     . "Horas informadas/acumuladas acima do limite mensal ("
                     . sec_to_time($maximo_mes,'hh:mm') . ')';
    }
    else if ($horas_por_dias > $maximo_dia)
    {
        $mensagem .= (empty($mensagem) ? "" : "<br>")
                     . "Horas informadas, para o período, excedem o limite diário ("
                     . sec_to_time($maximo_dia,'hh:mm')
                     . ')';
    }

    return $mensagem;
}

function verificaSePodeEditarPeriodo($siape, $start_date, $end_date, $horas=0)
{
    $horas_realizadas = verificaHorasDestinadasParaHoraExtra($siape, $start_date, $end_date);

    // informação de retorno
    $info = ['blocked' => false , 'titulo' => 'Permitido'];

    /*
    if ((inverteData($start_date) <= date('Ymd')))
    {
        $mensagem = 'Data Inicial não pode ser alterada!';
        $info = ['blocked' => true, 'titulo' => $mensagem];
    }

    // Verifica se já passou a data de início ou fim do período
    if ((inverteData($end_date) <= date('Ymd')))
    {
        $mensagem = 'Data Final não pode ser alterada!';
        $info = ['blocked' => true, 'titulo' => $mensagem];
    }

    // Verifica se já passou a data de início ou fim do período
    if ((inverteData($start_date) <= date('Ymd')) && (inverteData($end_date) <= date('Ymd')))
    {
        $mensagem = 'Período não pode ser alterado!';
        $info = ['blocked' => true, 'titulo' => $mensagem];
    }
    */

    // Verifica se há registro de hora extra no ponto
    //if ((inverteData($start_date) <= date('Ymd')) && (inverteData($end_date) <= date('Ymd')))
    if ($horas > 0 && $horas_realizadas >= $horas)
    {
        $mensagem = 'Período não pode ser alterado!';
        $info = ['blocked' => true, 'titulo' => $mensagem];
    }

   return $info;
}

function verificaHorasDestinadasParaHoraExtra($siape, $start_date, $end_date)
{
    $horas = 0;

    $siape = getNovaMatriculaBySiape($siape);

    $obj = new OcorrenciasGrupos();
    $oco = $obj->CodigoHoraExtraPadrao()[0];

    $oDBase = new DataBase('PDO');

    $params = array(
        array(":siape",       $siape,                 PDO::PARAM_STR),
        array(":data_inicio", conv_data($start_date), PDO::PARAM_STR),
        array(":data_fim",    conv_data($end_date),   PDO::PARAM_STR),
        array(":oco",         $oco,                   PDO::PARAM_STR),
    );

    $ano     = dataAno($start_date);
    $mes_ini = dataMes($start_date);
    $mes_fim = dataMes($end_date);

    for ($mes = $mes_ini; $mes <= $mes_fim; $mes++)
    {
        $sql = "
        SELECT
            IF(ISNULL(SUM(TIME_TO_SEC(jorndif))),
                0,
                SUM(TIME_TO_SEC(jorndif))) AS horas
        FROM ponto" . str_pad($mes,2,"0",STR_PAD_LEFT) . $ano . "
        WHERE siape = :siape
              AND (dia >= :data_inicio AND dia <= :data_fim)
              AND oco = :oco ";

        $oDBase->query( $sql, $params );

        $horas += $oDBase->fetch_object()->horas;
    }

    return $horas;
}

function CarregaRegistrosHoraExtra($id)
{
    $sql = "
    SELECT
        autorizacoes_hora_extra.siape,
        servativ.nome_serv AS nome,
        IF(autorizacoes_hora_extra.setor = '',
		servativ.cod_lot,
		autorizacoes_hora_extra.setor) AS setor,
        autorizacoes_hora_extra.data_inicio,
        autorizacoes_hora_extra.data_fim,
        autorizacoes_hora_extra.horas,
        autorizacoes_hora_extra.documento,
        autorizacoes_hora_extra.acrescimo_autorizado
            FROM autorizacoes_hora_extra
		LEFT JOIN servativ ON autorizacoes_hora_extra.siape = servativ.mat_siape
                    WHERE autorizacoes_hora_extra.id = :id";
    $params = array(
        array(":id", $id, PDO::PARAM_STR),
    );

    $oDBase = new DataBase();
    $oDBase->query($sql, $params);

    // SE EXISTIR SERA DELETADO
    if ($oDBase->num_rows() > 0)
    {
        return $oDBase->fetch_object();
    }

    return NULL;
}

/**
 * @param  boolean
 * @return  void
 * @info Titulo das colunas da tabela
 */
function ImprimirTituloDasColunasHoraExtra($subView=false)
{
    if ($subView == false)
    {
        ?>
        <tr>
            <td class="text-center" style='vertical-align:middle;'>Matrícula</td>
            <td class="text-left"   style='vertical-align:middle;width:490px;'>Nome do Servidor</td>
            <td class="text-center" style='vertical-align:middle;'>Permite Serviço<br>Extraordinário</td>
            <td class="text-center" style='vertical-align:middle;'>Ações</td>
        </tr>
        <?php
    }
    else
    {
        ?>
        <tr>
            <td class="text-left"   style='vertical-align:middle;width:490px;'>Nome do Servidor</td>
            <td class="text-center" style='vertical-align:middle;'>Período Autorizado</td>
            <td class="text-center" style='vertical-align:middle;'>Ações</td>
        </tr>
        <?php
    }
}

function ImprimirDadosHoraExtra($rco, $oDBase)
{
    $bool = verificaPermissoesAcumulo(
        $rco->siape,
        $rco->horae,
        $rco->motivo,
        $rco->limite_horas,
        $rco->permite_banco,
        $rco->excecao,
        $rco->plantao_medico
    );

    ?>
    <tr>
        <?php

        if (empty($rco->periodo))
        {
            ?>
            <td class="text-center"><?= removeOrgaoMatricula($rco->siape); ?></td>
            <?php
        }
        else
        {
            ?>
            <td class="text-nowrap" title=""
                data-toggle="collapse"
                data-target="#collapse<?= tratarHTML($rco->siape); ?>">
                <a href="#." style="text-decoration:underline;">
                    <span id="collapse<?= tratarHTML($rco->siape); ?>span" class="glyphicon glyphicon-plus"></span>
                </a>&nbsp;&nbsp;<?= removeOrgaoMatricula($rco->siape); ?>
            </td>
            <?php
        }

        ?>
        <td class="text-left"><?= tratarHTML($rco->nome); ?></td>
        <?php

        if ($bool['blocked'])
        {
            ?>
            <td align='center'><img style="cursor: pointer" border='0' src='<?= _DIR_IMAGEM_; ?>warning.png' width='16' height='16' align='absmiddle' alt='Editar' title='<?= tratarHTML($bool['titulo']); ?>'></td>
            <?php
        }
        else
        {
            ?>
            <td align='center'><img style="cursor: pointer" border='0' src='<?= _DIR_IMAGEM_; ?>visto_blue.gif' width='16' height='16' align='absmiddle' title='<?= tratarHTML($bool['titulo']); ?>'></td>
            <?php
        }

        if (!$bool['blocked'])
        {
            $editar_registro = base64_encode("siape=".$rco->siape."&id=".$rco->id);
            $editar_alt      = $bool['titulo'] . (empty($rco->periodo) ? " Incluir" : " Editar");

            ?>
            <td align='center'>
                <form method="POST" name="form-autorizacoes" class="formsiape" action="hora_extra_autorizacao_registro.php" onSubmit="javascript:return false;">
                    <input type="hidden" name="siape" value="">
                    <button type='button' class='btn btn-default save' data-dismiss='modal' id="adicionar" style="margin-left: 10%;" data-siape="<?= $rco->siape; ?>" >Adicionar Período</button>
                </form>
            </td>
            <?php
        }
        else
        {
            ?>
            <td align='center'>&nbsp;</td>
            <?php
        }
        ?>
    </tr>
    <?php
}

function ImprimirDadosHoraExtraDetalhes($rco, $oDBase)
{
    ?>
    <tr style='padding:0px;margin:0px;border-collapse: collapse;'>
        <td style='padding:0px;margin:0px;border-collapse:collapse;text-align:right;'></td>
        <td style='padding:0px;margin:0px;border-collapse:collapse;text-align:right;' colspan="8">

            <table id="collapse<?= tratarHTML($rco->siape); ?>" class="table table-striped table-bordered text-center collapse out" style='width:100%;margin-top:5px;margin-left:0px;'>
                <thead>
                    <tr>
                        <?php ImprimirTituloDasColunasHoraExtra($subView=true); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    while ($rows = $oDBase->fetch_object())
                    {
                        ?>
                        <tr>
                            <td class="text-left"><?= tratarHTML($rows->nome); ?></td>
                            <td class="text-center"><?= tratarHTML($rows->periodo); ?></td>
                            <?php

                            $bool_periodo = verificaSePodeEditarPeriodo(
                                $rows->siape,
                                $rows->data_inicio,
                                $rows->data_fim,
                                $rows->horas
                            );

                            if (!$bool_periodo['blocked'])
                            {
                                $editar_registro = base64_encode("siape=".$rows->siape."&id=".$rows->id);
                                $editar_alt      = $bool['titulo'] . (empty($rows->periodo) ? " Incluir" : " Editar");

                                ?>
                                <td align='center'>
                                    <a href='hora_extra_autorizacao_registro.php?dados=<?= tratarHTML($editar_registro); ?>'>
                                        <img border='0' src='<?= _DIR_IMAGEM_; ?>edicao2.jpg' width='16' height='16'
                                             align='absmiddle' alt='<?php tratarHTML($editar_alt); ?>' title='<?php tratarHTML($editar_alt); ?>'></a>
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <a class="delete-consulta"
                                        data-id="<?= tratarHTML($rows->id); ?>"
                                        data-nome="<?= tratarHTML($rows->nome); ?>">
                                        <img border='0' src='<?= _DIR_IMAGEM_; ?>lixeira2.jpg' width='16' height='16'
                                             align='absmiddle' alt='Excluir' title='Excluir'></a>
                                </td>
                                <?php
                            }
                            else
                            {
                                ?>
                                <td align='center'><img style="cursor: pointer" border='0' src='<?= _DIR_IMAGEM_; ?>warning.png' width='16' height='16' align='absmiddle' title='<?= tratarHTML($bool_periodo['titulo']); ?>'></td>
                                <?php
                            }

                            ?>
                        </tr>
                        <?php
                    }

                    ?>
                </tbody>
            </table>

        </td>
    </tr>
    <?php
}
