<?php

include_once('config.php');

$dt_limite           = $_REQUEST['prorrogado_ate'];
$liberar_homologacao = $_REQUEST['liberar_homologacao'];

$oDBase = new DataBase('PDO');

$sql        = "SELECT descricao FROM tabsetor WHERE codigo = :unidade ORDER BY codigo LIMIT 1 ";
$resLot     = $oDBase->query(
    $sql,
    array(
        array( ':unidade', $unidade, PDO::PARAM_STR),
    ));
$numrowsLot = $oDBase->num_rows();
$oSetor     = $oDBase->fetch_object();
$descricao  = htmlspecialchars(mb_convert_encoding($oSetor->descricao, "UTF-8", "ISO-8859-1"));

if ($numrowsLot == 0)
{
    $erro = 'Unidade Inexistente!';
}

$sql     = "
    SELECT
        IFNULL(mes_ano_homologacao,'') AS mes_ano_homologacao, 
        IFNULL(solicitante,'') AS solicitante, 
        IFNULL(email_solicitando,'') AS email_solicitando, 
        IFNULL(unidade,'') AS unidade, 
        DATE_FORMAT(IFNULL(prorrogado_ate,''),'%d/%m/%Y') AS prorrogado_ate, 
        DATE_FORMAT(IFNULL(data_registro,''),'%d/%m/%Y') AS data_registro, 
        email_destinatarios
    FROM
        liberacao_homologacao
    WHERE
        unidade = :unidade
        AND mes_ano_homologacao = :mes_ano_homologacao
    ORDER BY
        mes_ano_homologacao, unidade DESC
    LIMIT 1 ";
$res     = $oDBase->query(
    $sql,
    array(
        array( ':unidade',             $unidade,             PDO::PARAM_STR),
        array( ':mes_ano_homologacao', $mes_ano_homologacao, PDO::PARAM_STR),
    ));
$numrows = $oDBase->num_rows();
$oDados  = $oDBase->fetch_object();

if ($numrows > 0)
{
    $mes_ano_homologacao = $oDados->mes_ano_homologacao;
    $solicitante         = htmlspecialchars(mb_convert_encoding($oDados->solicitante, "UTF-8", "ISO-8859-1"));
    $email_solicitando   = str_replace("<br />", "\n", retira_acentos($oDados->email_solicitando, ''));
    $unidade             = $oDados->unidade;
    $prorrogado_ate      = $oDados->prorrogado_ate;
    $data_registro       = $oDados->data_registro;
    $email_destinatarios = $oDados->email_destinatarios;

    $result[] = array(
        'mes_ano_homologacao' => $mes_ano_homologacao,
        'solicitante'         => $solicitante,
        'email_solicitando'   => $email_solicitando,
        'unidade'             => $unidade,
        'descricao'           => $descricao,
        'prorrogado_ate'      => $prorrogado_ate,
        'data_registro'       => $data_registro,
        'email_destinatarios' => $email_destinatarios,
        'erro'                => $erro
    );
}
else
{
    $result[] = array(
        'mes_ano_homologacao' => '',
        'solicitante'         => '',
        'email_solicitando'   => '',
        'unidade'             => $unidade,
        'descricao'           => $descricao,
        'prorrogado_ate'      => '',
        'data_registro'       => '',
        'email_destinatarios' => '',
        'erro'                => $erro
    );
}

$myData = array('dados' => $result);

print json_encode($myData);

exit();
