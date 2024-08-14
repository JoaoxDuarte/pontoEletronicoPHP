<?php

include_once( "config.php" );

$ano = $_POST['ano'];

$oDBase = new DataBase();

$oDBase->query( "
SELECT
    servativ.mat_siape,
    servativ.nome_serv,
    DATE_FORMAT(autorizacoes_hora_extra.data_inicio,'%d/%m/%Y') AS data_inicio,
    DATE_FORMAT(autorizacoes_hora_extra.data_fim,'%d/%m/%Y') AS data_fim,
    autorizacoes_hora_extra.horas,
    (SELECT IFNULL(SEC_TO_TIME(SUM(TIME_TO_SEC(pto.jorndif))),'00:00:00') FROM ponto01".$ano." AS pto WHERE pto.siape = autorizacoes_hora_extra.siape AND pto.oco = '02828' AND (pto.dia BETWEEN autorizacoes_hora_extra.data_inicio AND autorizacoes_hora_extra.data_fim)) AS horas_realizadas,
    (SELECT IFNULL(SUM(TIME_TO_SEC(pto.jorndif)),0) FROM ponto01".$ano." AS pto WHERE pto.siape = autorizacoes_hora_extra.siape AND pto.oco = '02828' AND (pto.dia BETWEEN autorizacoes_hora_extra.data_inicio AND autorizacoes_hora_extra.data_fim)) AS horas_total,
    (SELECT IFNULL(TIME_TO_SEC(autorizacoes_hora_extra.horas) - SUM(TIME_TO_SEC(pto.jorndif)),0) FROM ponto01".$ano." AS pto WHERE pto.siape = autorizacoes_hora_extra.siape AND pto.oco = '02828' AND (pto.dia BETWEEN autorizacoes_hora_extra.data_inicio AND autorizacoes_hora_extra.data_fim)) AS horas_saldo
FROM
    autorizacoes_hora_extra
LEFT JOIN
    servativ ON autorizacoes_hora_extra.siape = servativ.mat_siape
LEFT JOIN
    tabsetor ON servativ.cod_lot = tabsetor.codigo
WHERE 
    YEAR(autorizacoes_hora_extra.data_inicio) = :ano
    AND tabsetor.upag = :upag
ORDER BY
    autorizacoes_hora_extra.siape, autorizacoes_hora_extra.data_inicio
",
array(
    array( ':ano',  $ano,              PDO::PARAM_STR ),
    array( ':upag', $_SESSION['upag'], PDO::PARAM_STR ),
));

$result = array();

while ($dados = $oDBase->fetch_object())
{
    $result[] = array(
        $dados->mat_siape,
        utf8_encode($dados->nome_serv),
        $dados->data_inicio,
        $dados->data_fim,
        substr($dados->horas,0,5),
        substr($dados->horas_realizadas,0,5),
        substr($dados->horas_total,0,5)
    );
}

$myData = array(
    'data' => $result
);

print json_encode($myData);
