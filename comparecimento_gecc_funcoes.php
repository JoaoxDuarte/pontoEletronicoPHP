<?php

include_once("config.php");
include_once("class_definir.jornada.php");
include_once("class_ocorrencias_grupos.php");
include_once("comparecimento_tabela_auxiliar.php");

/*
 * @param void
 * @return  object  Resultado da pesquisa
 */
function pesquisaChaveEscolha($var1='',$var2='',$groupby="siape")
{
    if ($groupby !== "siape")
    {
        $_SESSION['sChaveCriterioGECC'] = array("chave" => $var1, "escolha" => $var2);
    }

    $query = "
    SELECT
        IFNULL(compareceu_gecc.id,0) AS id,
        servativ.mat_siape       AS matricula,
        servativ.mat_siape       AS siape,
        servativ.nome_serv       AS nome,
        servativ.cod_lot         AS setor,
        compareceu_gecc.data_ini,
        compareceu_gecc.data_fim,
        CONCAT(
            DATE_FORMAT(compareceu_gecc.data_ini,'%d/%m/%Y'),
            ' a ',
            DATE_FORMAT(compareceu_gecc.data_fim,'%d/%m/%Y')
        ) AS periodo,
        compareceu_gecc.hora_ini,
        compareceu_gecc.hora_fim,
        CONCAT(
            compareceu_gecc.hora_ini,
            ' às ',
            compareceu_gecc.hora_fim
        ) AS horario,
        compareceu_gecc.horas
    FROM
        servativ
    LEFT JOIN
        compareceu_gecc ON servativ.mat_siape = compareceu_gecc.siape
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

    if ($_SESSION["sRH"] != "S")
    {
        $query .= " AND tabsetor.upag = :upag ";
        $params[] = array(":upag", $_SESSION['upag'], PDO::PARAM_STR);
    }

    if ($_SESSION["sAPS"] == "S")
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
        AND NOT ISNULL(compareceu_gecc.data_ini)
        AND NOT ISNULL(compareceu_gecc.data_fim)
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
        $query .= "ORDER BY servativ.nome_serv, compareceu_gecc.data_ini DESC ";
    }

    $oDBase = new DataBase('PDO');

    //if (!empty($var2))
    //{
        $oDBase->query($query, $params);
    //}

    return $oDBase;
}

/**
 * @param  string  $siape  Matrícula do servidor
 * @param  string  $dia    Dia do registro
 * @return resource  Resultado da seleção
 */
function selecionarDadosGECC($id)
{
    $oDBase = new DataBase();

    $sql = "
    SELECT
        id, siape, data_ini, data_fim, hora_ini, hora_fim, horas,
        acrescimo_autorizado, setor, idreg, registro_ip, registro_data,
        registro_siape
            FROM compareceu_gecc
                WHERE compareceu_gecc.id = :id";
    $paramnsSearch = array(
        array(":id", $id, PDO::PARAM_STR),
    );

    $oDBase->query($sql, $paramnsSearch);

    return $oDBase;
}

/**
 * @param $post
 * @return bool|int|null|PDOStatement|resource
 * @info Cria uma nova autorização para o servidor ou atualiza a existente
 */
function createAutorizacaoGECC($post)
{
    $return = false;

    $siape = getNovaMatriculaBySiape($post['siape']);

    $idreg = define_quem_registrou(
        $_SESSION['sLotacao'],
        chefia_ativa($_SESSION['sMatricula'], date('Y-m-d'))
    );

    // CONSULTA PARA IDENTIFICAR SE JÁ EXISTE AUTORIZAÇÃO PARA O SERVIDOR
    $oDBase = selecionarDadosGECC($post['id']);

    // SE EXISTIR SERA ATUALIZADO COM OS NOVOS VALORES, CASO CONTRÁRIO SERA CRIADO UM NOVO
    if ($oDBase->num_rows() == 0)
    {
        $queryInsert = "
        INSERT INTO compareceu_gecc
            SET compareceu_gecc.siape                = :siape,
                compareceu_gecc.setor                = :setor,
                compareceu_gecc.data_ini             = :data_ini,
                compareceu_gecc.data_fim             = :data_fim,
                compareceu_gecc.hora_ini             = :hora_ini,
                compareceu_gecc.hora_fim             = :hora_fim,
                compareceu_gecc.horas                = :horas,
                compareceu_gecc.acrescimo_autorizado = :acrescimo_autorizado,
                compareceu_gecc.idreg                = :idreg,
                compareceu_gecc.registro_ip          = :registro_ip,
                compareceu_gecc.registro_data        = NOW(),
                compareceu_gecc.registro_siape       = :registro_siape
        ";

        $paramnsInsert = array(
            array(":siape",                $siape,                        PDO::PARAM_STR),
            array(":setor",                $post['setor'],                PDO::PARAM_STR),
            array(":data_ini",             conv_data($post['data_ini']),  PDO::PARAM_STR),
            array(":data_fim",             conv_data($post['data_fim']),  PDO::PARAM_STR),
            array(":hora_ini",             $post['hora_ini'],             PDO::PARAM_STR),
            array(":hora_fim",             $post['hora_fim'],             PDO::PARAM_STR),
            array(":horas",                $post['horas'],                PDO::PARAM_STR),
            array(":acrescimo_autorizado", $post['acrescimo_autorizado'], PDO::PARAM_STR),
            array(":idreg",                $idreg,                        PDO::PARAM_STR),
            array(":registro_ip",          getIpReal(),                   PDO::PARAM_STR),
            array(":registro_siape",       $_SESSION['sMatricula'],       PDO::PARAM_STR),
        );

        $oDBase->query($queryInsert, $paramnsInsert);

        inclusaoPontoAuxiliarGECC($post);

        registraLog("Inclusão da autorização de execução GECC");
    }

    $result = ($oDBase->affected_rows() > 0);

    return $result;

}

function inclusaoPontoAuxiliarGECC($post)
{
    if ( !empty($post['data_inicio_anterior']) && !empty($post['data_fim_anterior']))
    {
        manutencaoPontoAuxiliarGECC($post,$post['data_inicio_anterior'],$post['data_fim_anterior'],$excluir=true);
    }
    manutencaoPontoAuxiliarGECC($post,$post['data_ini'],$post['data_fim'],$excluir=false);
}

function manutencaoPontoAuxiliarGECC($post,$start_date,$end_date,$excluir=false)
{
    $siape = getNovaMatriculaBySiape($post['siape']);

    $di = inverteData($start_date);
    $df = inverteData($end_date);


    // Garantindo que uma é menor que a outra
    if ($di <= $df)
    {
        $oco = codigoPadraoGECC($siape);
        
        $dias = dias_decorr($start_date, $end_date);

        $dia = $start_date;

        for ($x = 1; $x <= $dias; $x++)
        {
            $paramsPonto = array(
                'dia'            => $dia,
                'matricula'      => $siape,
                'hora_ini'       => $post['hora_ini'],
                'hora_fim'       => $post['hora_fim'],
                'tempo_consulta' => $post['horas'],
                'deslocamento'   => '',
                'setor'          => $post['setor'],
                'idreg'          => $idreg,
                'oco'            => $oco,
                'registro_ip'    => getIpReal(),
                'registro_siape' => $_SESSION['sMatricula'],
            );

            $oDBase = selecionarDadosPontoAuxiliar($siape,$dia,$oco);

            if ($oDBase->num_rows() == 0 && $excluir == false)
            {
                incluirPontoAuxiliar($paramsPonto,$saldo=0);
            }
            else if ($excluir == true)
            {
                $params = array(
                    array(":dia",            $dia,                    PDO::PARAM_STR),
                    array(":siape",          $siape,                  PDO::PARAM_STR),
                    array(":hora_ini",       $post['hora_ini'],       PDO::PARAM_STR),
                    array(":hora_fim",       $post['hora_fim'],       PDO::PARAM_STR),
                    array(":tempo_consulta", $post['horas'],          PDO::PARAM_STR),
                    array(":deslocamento",   '00:00',                 PDO::PARAM_STR),
                    array(":setor",          $post['setor'],          PDO::PARAM_STR),
                    array(":idreg",          $idreg,                  PDO::PARAM_STR),
                    array(":acao",           'E',                     PDO::PARAM_STR),
                    array(":registro_ip",    getIpReal(),             PDO::PARAM_STR),
                    array(":registro_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
                );

                deletePontoAuxiliar($params,$oco);
            }

            $dia = soma_dias_a_data($dia, 1);
            $dias_seguidos .= $dia .'<br>';
        }
    }
}

/**
 * @param void
 * @return void
 */
function deleteAutorizacaoGECC($id,$dados,$idreg="C")
{
    $oDBase = new DataBase('PDO');

    // IDENTIFICAR SE JÁ EXISTE
    $query = "
    SELECT
        siape, data_ini, data_fim, hora_ini, hora_fim, horas,
        acrescimo_autorizado, setor, idreg, registro_ip, 'E' AS acao,
        registro_data, registro_siape
            FROM compareceu_gecc
                WHERE compareceu_gecc.id = :id";

    $params = array(
        array(":id", $id, PDO::PARAM_STR),
    );


    $oDBase->query($query, $params);
    $oDados = $oDBase->fetch_object();

    // SE EXISTIR SERA DELETADO
    if ($oDBase->num_rows() > 0)
    {
        gravar_historico_gecc($id, 'E');

        // apaga o registro
        $oDBase->query( "
        DELETE FROM compareceu_gecc
            WHERE compareceu_gecc.id = :id",
        array(
            array(":id", $id, PDO::PARAM_INT)));

        // ajuste no ponto auxiliar
        $params = array(
            array(":dia",            $oDados->data_ini,       PDO::PARAM_STR),
            array(":siape",          $oDados->siape,          PDO::PARAM_STR),
            array(":hora_ini",       $oDados->hora_ini,       PDO::PARAM_STR),
            array(":hora_fim",       $oDados->hora_fim,       PDO::PARAM_STR),
            array(":tempo_consulta", $oDados->horas,          PDO::PARAM_STR),
            array(":deslocamento",   0,                       PDO::PARAM_STR),
            array(":setor",          $oDados->setor,          PDO::PARAM_STR),
            array(":idreg",          $idreg,                  PDO::PARAM_STR),
            array(":acao",           'E',                     PDO::PARAM_STR),
            array(":registro_ip",    getIpReal(),             PDO::PARAM_STR),
            array(":registro_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
        );
        
        $codigoPadraoGECC = codigoPadraoGECC( $oDados->siape );

        deletePontoAuxiliar( $params, $codigoPadraoGECC );

        // registra em log a ação
        registraLog("Deletado registro da autorização de execução GECC de ".$dados->nome.", dia ".databarra($dados->dia));
    }
}


function gravar_historico_gecc($id, $oper='A')
{
    $oDBase = new DataBase('PDO');

    // inclusão historico
    $oDBase->query( "
    INSERT compareceu_gecc_historico
        SELECT 0,
            compareceu_gecc.siape,
            compareceu_gecc.data_ini,
            compareceu_gecc.data_fim,
            compareceu_gecc.data_ini,
            compareceu_gecc.data_fim,
            compareceu_gecc.horas,
            compareceu_gecc.acrescimo_autorizado,
            compareceu_gecc.setor,
            compareceu_gecc.idreg,
            compareceu_gecc.registro_ip,
            compareceu_gecc.registro_data,
            compareceu_gecc.registro_siape,
            :oper,
            :operador_siape,
            NOW()
                FROM compareceu_gecc
                    WHERE compareceu_gecc.id = :id
    ",
    array(
        array(":id",             $id,                     PDO::PARAM_STR),
        array(":oper",           $oper,                   PDO::PARAM_STR),
        array(":operador_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
    ));
}

/**
 * @param $siape
 * @param $start_date
 * @param $end_date
 * @return bool  Retorna FALSE se data já cadastrada para o servidor.
 * @info Verifica período de hora extra
 */
function verificaSePeriodoGECCJaCadastrado($siape,$start_date,$end_date,$start_hora,$end_hora)
{
    $siape = getNovaMatriculaBySiape($siape);

    $oDBase = new DataBase('PDO');

    $oDBase->query("
    SELECT
        IF(IFNULL((SELECT
                    siape
                FROM
                    compareceu_gecc
                WHERE
                    compareceu_gecc.siape = :siape
                    AND (:start_date BETWEEN compareceu_gecc.data_ini 
                                         AND compareceu_gecc.data_fim)                     
        ),'')='','N','S') AS inicio_cadastrado,
        IF(IFNULL((SELECT
                    siape
                FROM
                    compareceu_gecc
                WHERE
                    compareceu_gecc.siape = :siape
                    AND (:end_date BETWEEN compareceu_gecc.data_ini 
                                       AND compareceu_gecc.data_fim)
        ),'')='','N','S') AS fim_cadastrado
            FROM compareceu_gecc
    ",
    array(
        array(":siape",      $siape,                 PDO::PARAM_STR),
        array(":start_date", conv_data($start_date), PDO::PARAM_STR),
        array(":end_date",   conv_data($end_date),   PDO::PARAM_STR),
    ));

    return $oDBase->fetch_object();
}

/**
 * @param void
 * @return array  Com campos como chave e seus valores limite
 * @info Carrega limites de hora extra
 */
function configLimitesGECC()
{
    $array = array();

    $limites_inss = horariosLimiteINSS();
    $array['saida']   = $limites_inss['saida']['horario'];
    $array['entrada'] = $limites_inss['entrada']['horario'];

    $oDBase = new DataBase('PDO');

    $oDBase->query("SELECT
                        campo, minutos
                            FROM config_basico
                                WHERE grupo = 'gecc'");

    while ($rows = $oDBase->fetch_object())
    {
        $array[$rows->campo] = $rows->minutos;
    }

    return $array;

}

/**
 * @param  float
 * @return  float  Horas GECC acumuladas no exercício
 * @info Carrega limites de GECC
 */
function acumuladoGECCNoAno($siape,$ano=NULL,$retorna='sec')
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
    FROM compareceu_gecc
    WHERE
        siape = :siape
        AND YEAR(data_ini) = :ano
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
        $horas_acumuladas = $dados->segundos;
    }
    else
    {
        $horas_acumuladas = $dados->hhmm;
    }

    return $horas_acumuladas;
}

function verificaGECCNoPeriodo($siape, $start_date, $end_date, $horas, $acrescimo)
{
    $siape = getNovaMatriculaBySiape($siape);

    $mensagem = "";

    $limites = configLimitesGECC();
    $maximo_dia           = time_to_sec($limites['limite_diario_gecc']);
    $maximo_ano           = time_to_sec($limites['limite_anual_gecc']);
    $maximo_ano_acrescimo = time_to_sec($limites['limite_anual_acrescimo_gecc']);

    $maximo_ano_com_acrescimo = ($maximo_ano + $maximo_ano_acrescimo);

    $dias_no_periodo = (dif_data(conv_data($start_date),conv_data($end_date))+1);
    $meses_no_periodo = ((dataMes($end_date) - dataMes($start_date)) + 1);

    // divide horas informadas por dias no período, para
    // verificar se excedem as 2 horas permitidas por dia
    $horas_por_dias = ($horas / $dias_no_periodo);

    $horas_acumuladas = acumuladoGECCNoAno($siape,dataAno($start_date),$retorna='sec');

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


    return $mensagem;
}

function CarregaRegistrosGECC($id)
{
    $sql = "
    SELECT
        compareceu_gecc.siape,
        servativ.nome_serv AS nome,
        IF(compareceu_gecc.setor = '',
		servativ.cod_lot,
		compareceu_gecc.setor) AS setor,
        compareceu_gecc.data_ini,
        compareceu_gecc.data_fim,
        compareceu_gecc.hora_ini,
        compareceu_gecc.hora_fim,
        compareceu_gecc.horas,
        compareceu_gecc.acrescimo_autorizado
            FROM compareceu_gecc
		LEFT JOIN servativ ON compareceu_gecc.siape = servativ.mat_siape
                    WHERE compareceu_gecc.id = :id";
    $params = array(
        array(":id", $id, PDO::PARAM_STR),
    );

    $oDBase = new DataBase('PDO');
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
function ImprimirTituloDasColunasGECC($subView=false)
{
    if ($subView == false)
    {
        ?>
        <tr>
            <td class="text-center" style='vertical-align:middle;'>Matrícula</td>
            <td class="text-left"   style='vertical-align:middle;width:490px;'>Nome do Servidor</td>
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
            <td class="text-center" style='vertical-align:middle;'>Horário</td>
            <td class="text-center" style='vertical-align:middle;'>Ações</td>
        </tr>
        <?php
    }
}

function ImprimirDadosGECC($rco, $oDBase)
{
    ?>
    <tr>
        <?php

        if (empty($rco->periodo))
        {
            ?>
            <td class="text-center"><?= tratarHTML(removeOrgaoMatricula($rco->siape)); ?></td>
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
                </a>&nbsp;&nbsp;<?= tratarHTML(removeOrgaoMatricula($rco->siape)); ?>
            </td>
            <?php
        }

        ?>
        <td class="text-left"><?= tratarHTML($rco->nome); ?></td>
        <td align='center'>
            <form method="POST" name="form-autorizacoes" class="formsiape" action="comparecimento_gecc_registro.php" onSubmit="javascript:return false;">
                <input type="hidden" name="siape" value="">
                <button type='button' class='btn btn-default save' data-dismiss='modal' id="adicionar" style="margin-left: 10%;" data-siape="<?= tratarHTML($rco->siape); ?>" >Adicionar Período</button>
            </form>
        </td>
    </tr>
    <?php
}

function ImprimirDadosGECCDetalhes($rco, $oDBase)
{
    ?>
    <tr style='padding:0px;margin:0px;border-collapse: collapse;'>
        <td style='padding:0px;margin:0px;border-collapse:collapse;text-align:right;'></td>
        <td style='padding:0px;margin:0px;border-collapse:collapse;text-align:right;' colspan="8">

            <table id="collapse<?= tratarHTML($rco->siape); ?>" class="table table-striped table-bordered text-center collapse out" style='width:100%;margin-top:5px;margin-left:0px;'>
                <thead>
                    <tr>
                        <?php ImprimirTituloDasColunasGECC($subView=true); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    while ($rows = $oDBase->fetch_object())
                    {
                        $editar_registro = base64_encode("siape=".$rows->siape."&id=".$rows->id);
                        $editar_alt      = $bool['titulo'] . (empty($rows->periodo) ? " Incluir" : " Editar");

                        ?>
                        <tr>
                            <td class="text-left"><?= tratarHTML($rows->nome); ?></td>
                            <td class="text-center"><?= tratarHTML($rows->periodo); ?></td>
                            <td class="text-center"><?= tratarHTML($rows->horario); ?></td>
                            <td align='center'>
                                <a class="delete-consulta"
                                    data-id="<?= tratarHTML($rows->id); ?>"
                                    data-nome="<?= tratarHTML($rows->nome); ?>"
                                    style="cursor:pointer;">
                                    <img border='0' src='<?= _DIR_IMAGEM_; ?>lixeira2.jpg' width='16' height='16'
                                         align='absmiddle' alt='Excluir' title='Excluir'></a>
                            </td>
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


/**
 * @param string $mat
 * @return string Código ocorrência GECC
 */
function codigoPadraoGECC($mat)
{
    // dados servidodr
    $dadosServidor = new DadosServidoresController();
    $sitcad = $dadosServidor->getSigRegJur( $mat );
    
    // código consulta médica
    $objOcorrenciasGrupos = new OcorrenciasGrupos();
    $codigoCreditoInstrutoriaPadrao = $objOcorrenciasGrupos->CodigoCreditoInstrutoriaPadrao( $sitcad );
    
    return $codigoCreditoInstrutoriaPadrao[0];
}
