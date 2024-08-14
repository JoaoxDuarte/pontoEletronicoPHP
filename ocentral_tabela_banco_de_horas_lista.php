<?php

include_once( "config.php" );
include_once( "inc/datatables_funcoes_serverside.php" );

verifica_permissao("administracao_central");

$oDBase = new DataBase();

$colunas = array(
  'banco_de_horas.comp',
  'banco_de_horas.siape',
  'servativ.nome_serv AS nome',
  'banco_de_horas.debito_anterior',
  'banco_de_horas.creditos_corrente',
  'banco_de_horas.sub_total',
  'banco_de_horas.debitos_corrente',
  'banco_de_horas.total',
  'banco_de_horas.situacao'
);

$limit  = limit( $_POST );
$filter = filter( $_POST, $colunas );
$order  = order( $_POST, $colunas );

// APURA O TOTAL DE REGISTROS
$oDBase->query( "
SELECT " . implode(', ', $colunas) ."
FROM banco_de_horas
LEFT JOIN servativ ON banco_de_horas.siape = servativ.mat_siape
LEFT JOIN tabsetor ON servativ.cod_lot = tabsetor.codigo
WHERE tabsetor.upag = :upag " . $filter . "
ORDER BY banco_de_horas.siape, banco_de_horas.comp DESC
",
array(
    array( ':upag', $_SESSION['upag_selecao'], PDO::PARAM_STR ),
));
$recordsTotal = $oDBase->num_rows();


// seleciona os registros
$oDBase->query( "
SELECT " . implode(', ', $colunas) ."
FROM banco_de_horas
LEFT JOIN servativ ON banco_de_horas.siape = servativ.mat_siape
LEFT JOIN tabsetor ON servativ.cod_lot = tabsetor.codigo
WHERE tabsetor.upag = :upag " . $filter . "
" . (empty($order) ? "ORDER BY banco_de_horas.siape, banco_de_horas.comp DESC" : $order) . "
" . $limit,
array(
    array( ':upag', $_SESSION['upag_selecao'], PDO::PARAM_STR ),
));
$recordsFiltered = $oDBase->num_rows();

$result = array();

while ($dados = $oDBase->fetch_object())
{
    $result[] = array(
        substr($dados->comp,-2).'/'.substr($dados->comp,0,4),
        $dados->siape,
        str_to_utf8($dados->nome),
        $dados->debito_anterior,
        $dados->creditos_corrente,
        $dados->sub_total,
        $dados->debitos_corrente,
        $dados->total,
        str_to_utf8($dados->situacao)
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
