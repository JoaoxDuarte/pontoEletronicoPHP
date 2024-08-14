<?php

include_once( "config.php" );
include_once( "inc/datatables_funcoes_serverside.php" );

verifica_permissao("administracao_central");

$oDBase = new DataBase();

$colunas = array(
  'substituicao.id',
  'substituicao.siape',
  'servativ.nome_serv',
  'substituicao.numfunc',
  'tabfunc.desc_func',
  'substituicao.upai',
  'substituicao.sigla',
  'substituicao.inicio',
  'substituicao.fim',
  'substituicao.motivo',
  'tabmotivo_substituicao.descricao',
  'substituicao.situacao',
  'substituicao.siape_registro',
  'substituicao.data_registro'
);

$limit  = limit( $_POST );
$filter = filter( $_POST, $colunas );
$order  = order( $_POST, $colunas );


// APURA O TOTAL DE REGISTROS
$oDBase->query( "
SELECT " . implode(', ', $colunas) ."
FROM substituicao
LEFT JOIN tabmotivo_substituicao ON substituicao.motivo = tabmotivo_substituicao.codigo
LEFT JOIN servativ ON substituicao.siape = servativ.mat_siape
LEFT JOIN tabfunc ON substituicao.numfunc = tabfunc.num_funcao
LEFT JOIN tabsetor ON servativ.cod_lot = tabsetor.codigo
WHERE tabsetor.upag = :upag " . $filter . "
ORDER BY servativ.nome_serv
",
array(
    array( ':upag', $_SESSION['upag_selecao'], PDO::PARAM_STR ),
));
$recordsTotal = $oDBase->num_rows();


// seleciona os registros
$oDBase->query( "
SELECT " . implode(', ', $colunas) ."
FROM substituicao
LEFT JOIN tabmotivo_substituicao ON substituicao.motivo = tabmotivo_substituicao.codigo
LEFT JOIN servativ ON substituicao.siape = servativ.mat_siape
LEFT JOIN tabfunc ON substituicao.numfunc = tabfunc.num_funcao
LEFT JOIN tabsetor ON servativ.cod_lot = tabsetor.codigo
WHERE tabsetor.upag = :upag " . $filter . "
" . (empty($order) ? "ORDER BY servativ.nome_serv" : $order) . "
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
        $dados->siape,
        str_to_utf8($dados->nome_serv),
        $dados->numfunc,
        str_to_utf8($dados->desc_func),
        $dados->upai,
        $dados->sigla,
        databarra($dados->inicio),
        databarra($dados->fim),
        str_to_utf8($dados->motivo),
        str_to_utf8($dados->descricao),
        str_to_utf8($dados->situacao),
        $dados->siape_registro,
        databarra($dados->data_registro),
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
