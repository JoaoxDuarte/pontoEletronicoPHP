<?php

include_once( "config.php" );
include_once( "inc/datatables_funcoes_serverside.php" );

verifica_permissao("administracao_central");

$colunas = array(
    'id',
    'ocupantes.MAT_SIAPE',
    'servativ.nome_serv',
    'SIT_OCUP',
    'NUM_FUNCAO',
    'RESP_LOT',
    'COD_DOC1',
    'NUM_DOC1',
    'DT_DOC1',
    'COD_DOC2',
    'NUM_DOC2',
    'DT_DOC2',
    'COD_DOC3',
    'NUM_DOC3',
    'DT_DOC3',
    'COD_DOC4',
    'NUM_DOC4',
    'DT_DOC4',
    'DT_ALTERA',
    'DT_INICIO',
    'DT_FIM',
    'servativ.COD_SERV',
    'DT_ATUAL',
    'DECIR',
    'DTDECIR'
);

$limit  = limit( $_POST );
$filter = filter( $_POST, $colunas );
$order  = order( $_POST, $colunas );

$oDBase = new DataBase();

// APURA O TOTAL DE REGISTROS
$oDBase->query( "
SELECT " . implode(', ', $colunas) ."
FROM ocupantes
LEFT JOIN servativ ON ocupantes.MAT_SIAPE = servativ.mat_siape
LEFT JOIN tabsetor ON servativ.cod_lot = tabsetor.codigo
WHERE tabsetor.upag = :upag " . $filter . "
ORDER BY servativ.nome_serv
",
array(
    array( ':upag', $_SESSION['upag_selecao'], PDO::PARAM_STR ),
));
$recordsTotal = $oDBase->num_rows();



$oDBase->query( "
SELECT
  " . implode(', ', $colunas) ."
FROM
  ocupantes
LEFT JOIN
    servativ ON ocupantes.MAT_SIAPE = servativ.mat_siape
LEFT JOIN
    tabsetor ON servativ.cod_lot = tabsetor.codigo
WHERE
    tabsetor.upag = :upag
    " . $filter . "
" . (empty($order) ? "ORDER BY servativ.nome_serv" : $order) . "
" . $limit,
array(
    array( ':upag', $_SESSION['upag_selecao'], PDO::PARAM_STR ),
));

$result = array();

while ($dados = $oDBase->fetch_object())
{
    $result[] = array(
        $dados->id,
        $dados->MAT_SIAPE,
        str_to_utf8($dados->nome_serv),
        $dados->SIT_OCUP,
        $dados->NUM_FUNCAO,
        $dados->RESP_LOT,
        $dados->COD_DOC1,
        $dados->NUM_DOC1,
        databarra($dados->DT_DOC1),
        $dados->COD_DOC2,
        $dados->NUM_DOC2,
        databarra($dados->DT_DOC2),
        $dados->COD_DOC3,
        $dados->NUM_DOC3,
        databarra($dados->DT_DOC3),
        $dados->COD_DOC4,
        $dados->NUM_DOC4,
        databarra($dados->DT_DOC4),
        databarra($dados->DT_ALTERA),
        databarra($dados->DT_INICIO),
        databarra($dados->DT_FIM),
        $dados->servativ.COD_SERV,
        databarra($dados->DT_ATUAL),
        $dados->DECIR,
        databarra($dados->DTDECIR),
    );
}

$myData = array(
    "draw"            => (isset( $_POST['draw'] ) ? intval( $_POST['draw'] ) : 0),
    "recordsTotal"    => intval( $recordsTotal ),
    "recordsFiltered" => intval( $recordsTotal ),
    'data' => $result);

print json_encode($myData);
