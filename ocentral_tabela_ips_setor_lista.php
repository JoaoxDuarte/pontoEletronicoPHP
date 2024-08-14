<?php

include_once( "config.php" );
include_once( "inc/datatables_funcoes_serverside.php" );

verifica_permissao("administracao_central");

$colunas = array(
  'id',
  'setor',
  'tabsetor.descricao',
  'endereco'
);

$limit  = limit( $_POST );
$filter = filter( $_POST, $colunas );
$order  = order( $_POST, $colunas );

$oDBase = new DataBase();

// APURA O TOTAL DE REGISTROS
$oDBase->query( "
SELECT " . implode(', ', $colunas) ."
FROM ips_setor
LEFT JOIN tabsetor ON ips_setor.setor = tabsetor.codigo
WHERE tabsetor.upag = :upag " . $filter . "
ORDER BY
    tabsetor.codigo
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
  ips_setor
LEFT JOIN
    tabsetor ON ips_setor.setor = tabsetor.codigo
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
        $dados->id,
        $dados->setor,
        str_to_utf8($dados->descricao),
        $dados->endereco
    );
}

$myData = array(
    "draw"            => (isset( $_POST['draw'] ) ? intval( $_POST['draw'] ) : 0),
    "recordsTotal"    => intval( $recordsTotal ),
    "recordsFiltered" => intval( $recordsTotal ),
    'data' => $result);

print json_encode($myData);
