<?php

include_once( "config.php" );
include_once( "inc/datatables_funcoes_serverside.php" );

verifica_permissao("administracao_central");

$oDBase = new DataBase();

$colunas = array(
    'acumulos_horas.id', 
    'acumulos_horas.ciclo_id', 
    'ciclos.data_inicio', 
    'ciclos.data_fim', 
    'ciclos.orgao', 
    'taborgao.sigla', 
    'acumulos_horas.siape', 
    'servativ.nome_serv AS nome',
    'acumulos_horas.horas', 
    'acumulos_horas.usufruto' 
);

$limit  = limit( $_POST );
$filter = filter( $_POST, $colunas );
$order  = order( $_POST, $colunas );

// APURA O TOTAL DE REGISTROS
$oDBase->query( "
SELECT " . implode(', ', $colunas) ."
FROM acumulos_horas 
LEFT JOIN ciclos ON acumulos_horas.ciclo_id = ciclos.id 
LEFT JOIN taborgao ON ciclos.orgao = taborgao.codigo 
LEFT JOIN servativ ON acumulos_horas.siape = servativ.mat_siape 
LEFT JOIN tabsetor ON servativ.cod_lot = tabsetor.codigo
WHERE tabsetor.upag = :upag " . $filter . "
ORDER BY taborgao.codigo, ciclos.id, ciclos.data_inicio
",
array(
    array( ':upag', $_SESSION['upag_selecao'], PDO::PARAM_STR ),
));
$recordsTotal = $oDBase->num_rows();


// seleciona os registros
$oDBase->query( "
SELECT " . implode(', ', $colunas) ."
FROM acumulos_horas 
LEFT JOIN ciclos ON acumulos_horas.ciclo_id = ciclos.id 
LEFT JOIN taborgao ON ciclos.orgao = taborgao.codigo 
LEFT JOIN servativ ON acumulos_horas.siape = servativ.mat_siape 
LEFT JOIN tabsetor ON servativ.cod_lot = tabsetor.codigo
WHERE tabsetor.upag = :upag " . $filter . "
" . (empty($order) ? "ORDER BY taborgao.codigo, ciclos.id, ciclos.data_inicio" : $order) . "
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
        $dados->ciclo_id,
        databarra($dados->data_inicio),
        databarra($dados->data_fim),
        $dados->orgao,
        $dados->sigla,
        $dados->siape,
        str_to_utf8($dados->nome),
        sec_to_time($dados->horas,'hh:mm'),
        sec_to_time($dados->usufruto,'hh:mm')
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
