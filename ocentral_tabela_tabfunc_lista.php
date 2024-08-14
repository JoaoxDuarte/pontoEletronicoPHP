<?php

include_once( "config.php" );
include_once( "inc/datatables_funcoes_serverside.php" );

verifica_permissao("administracao_central");

$colunas = array(
  'NUM_FUNCAO',
  'COD_FUNCAO',
  'DESC_FUNC',
  'COD_LOT',
  'tabfunc.cod_uorg',
  'tabfunc.UPAG',
  'tabsetor.cod_uorg AS tabsetor_cod_uorg',
  'tabsetor.UPAG AS tabsetor_upag',
  'SIT_PAG',
  'INDSUBS',
  'RESP_LOT',
  'tabfunc.ATIVO',
  'TIPO'
);

$limit  = limit( $_POST );
$filter = filter( $_POST, $colunas );
$order  = order( $_POST, $colunas );

$oDBase = new DataBase();

// APURA O TOTAL DE REGISTROS
$oDBase->query( "
SELECT " . implode(', ', $colunas) ."
FROM tabfunc
LEFT JOIN tabsetor ON tabfunc.COD_LOT = tabsetor.codigo
WHERE tabsetor.upag = :upag " . $filter . "
ORDER BY tabsetor.codigo
",
array(
    array( ':upag', $_SESSION['upag_selecao'], PDO::PARAM_STR ),
));
$recordsTotal = $oDBase->num_rows();


// seleciona os registros
$oDBase->query( "
SELECT
  " . implode(', ', $colunas) ."
FROM
  tabfunc
LEFT JOIN
    tabsetor ON tabfunc.COD_LOT = tabsetor.codigo
WHERE
    tabsetor.upag = :upag
    " . $filter . "
" . (empty($order) ? "ORDER BY tabsetor.codigo" : $order) . "
" . $limit,
array(
    array( ':upag', $_SESSION['upag_selecao'], PDO::PARAM_STR ),
));
$recordsFiltered = $oDBase->num_rows();

$result = array();

while ($dados = $oDBase->fetch_object())
{
    $result[] = array(
        $dados->NUM_FUNCAO,
        $dados->COD_FUNCAO,
        str_to_utf8($dados->DESC_FUNC),
        $dados->COD_LOT,
        $dados->cod_uorg,
        $dados->UPAG,
        $dados->tabsetor_cod_uorg,
        $dados->tabsetor_upag,
        $dados->SIT_PAG,
        $dados->INDSUBS,
        $dados->RESP_LOT,
        $dados->ATIVO,
        $dados->TIPO
    );
}

$myData = array(
    "draw"            => (isset( $_POST['draw'] ) ? intval( $_POST['draw'] ) : 0),
    "recordsTotal"    => intval( $recordsTotal ),
    "recordsFiltered" => intval( $recordsTotal ),
    'data' => $result);

print json_encode($myData);
