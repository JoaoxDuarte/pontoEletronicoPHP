<?php

include_once( "config.php" );
include_once( "inc/datatables_funcoes_serverside.php" );

verifica_permissao("administracao_central");

set_time_limit( 10000 );

$colunas = array(
    'siape',
    'nome',
    'acesso',
    'setor',
    'privilegio',
    'senha',
    'prazo',
    'magico',
    'upag',
    'defvis',
    'portaria',
    'datapt',
    'ptfim',
    'dtfim',
    'recalculo',
    'refaz_frqano',
    'nome_soundex'
);

$limit  = limit( $_POST );
$filter = filter( $_POST, $colunas );
$order  = order( $_POST, $colunas );

$oDBase = new DataBase();

// APURA O TOTAL DE REGISTROS
$oDBase->query( "
SELECT " . implode(', ', $colunas) ."
FROM usuarios
WHERE usuarios.upag = :upag " . $filter . "
ORDER BY usuarios.nome
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
  usuarios
WHERE
    usuarios.upag = :upag
    " . $filter . "
" . (empty($order) ? "ORDER BY usuarios.nome" : $order) . "
" . $limit,
array(
    array( ':upag', $_SESSION['upag_selecao'], PDO::PARAM_STR ),
));
$recordsFiltered = $oDBase->num_rows();

$result = array();

while ($dados = $oDBase->fetch_object())
{
    $result[] = array(
    $dados->siape,
    str_to_utf8($dados->nome),
    $dados->acesso,
    $dados->setor,
    $dados->privilegio,
    $dados->senha,
    $dados->prazo,
    $dados->magico,
    $dados->upag,
    $dados->defvis,
    str_to_utf8($dados->portaria),
    databarra($dados->datapt),
    str_to_utf8($dados->ptfim),
    databarra($dados->dtfim),
    $dados->recalculo,
    $dados->refaz_frqano,
    $dados->nome_soundex
    );
}

$myData = array(
    "draw"            => (isset( $_POST['draw'] ) ? intval( $_POST['draw'] ) : 0),
    "recordsTotal"    => intval( $recordsTotal ),
    "recordsFiltered" => intval( $recordsTotal ),
    'data'            => $result
);

print json_encode($myData);

exit();
