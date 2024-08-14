<?php

include_once( "config.php" );
include_once( "inc/datatables_funcoes_serverside.php" );

verifica_permissao("administracao_central");

$oDBase = new DataBase();

$colunas = array(
    'autorizacoes_servidores_usufruto.siape',
    'servativ.nome_serv AS nome',
    'autorizacoes_servidores_usufruto.ciclo_id',
    'autorizacoes_servidores_usufruto.data_inicio',
    'autorizacoes_servidores_usufruto.data_fim',
    'autorizacoes_servidores_usufruto.tipo_autorizacao'
);

$limit  = limit( $_POST );
$filter = filter( $_POST, $colunas );
$order  = order( $_POST, $colunas );

// APURA O TOTAL DE REGISTROS
$oDBase->query( "
SELECT " . implode(', ', $colunas) ."
FROM autorizacoes_servidores_usufruto
LEFT JOIN servativ ON autorizacoes_servidores_usufruto.siape = servativ.mat_siape
LEFT JOIN tabsetor ON servativ.cod_lot = tabsetor.codigo
WHERE tabsetor.upag = :upag " . $filter . "
ORDER BY tabsetor.codigo, autorizacoes_servidores_usufruto.ciclo_id, autorizacoes_servidores_usufruto.data_inicio
",
array(
    array( ':upag', $_SESSION['upag_selecao'], PDO::PARAM_STR ),
));
$recordsTotal = $oDBase->num_rows();


// seleciona os registros
$oDBase->query( "
SELECT " . implode(', ', $colunas) ."
FROM autorizacoes_servidores_usufruto
LEFT JOIN servativ ON autorizacoes_servidores_usufruto.siape = servativ.mat_siape
LEFT JOIN tabsetor ON servativ.cod_lot = tabsetor.codigo
WHERE tabsetor.upag = :upag " . $filter . "
" . (empty($order) ? "ORDER BY tabsetor.codigo, autorizacoes_servidores_usufruto.ciclo_id, autorizacoes_servidores_usufruto.data_inicio" : $order) . "
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
        $dados->ciclo_id,
        databarra($dados->data_inicio),
        databarra($dados->data_fim),
        $dados->tipo_autorizacao
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
