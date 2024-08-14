<?php

include_once( "config.php" );
include_once( "inc/datatables_funcoes_serverside.php" );

verifica_permissao("administracao_central");

$oDBase = new DataBase();

$colunas = array(
    'servidores_autorizacao.id',
    'servidores_autorizacao.siape',
    'servativ.nome_serv',
    'servidores_autorizacao.justificativa',
    'servidores_autorizacao.data_inicio',
    'servidores_autorizacao.data_fim',
    'ips_autorizacao.endereco'
);

$limit  = limit( $_POST );
$filter = filter( $_POST, $colunas );
$order  = order( $_POST, $colunas );

// APURA O TOTAL DE REGISTROS
$oDBase->query( "
SELECT " . implode(', ', $colunas) ."
FROM servidores_autorizacao
INNER JOIN ips_autorizacao ON servidores_autorizacao.id = ips_autorizacao.servidor_autorizacao_id
LEFT JOIN servativ ON servidores_autorizacao.siape = servativ.mat_siape
LEFT JOIN tabsetor ON servativ.cod_lot = tabsetor.codigo
WHERE tabsetor.upag = :upag " . $filter . "
ORDER BY tabsetor.codigo
",
array(
    array( ':upag', $_SESSION['upag_selecao'], PDO::PARAM_STR ),
));
$recordsTotal = $oDBase->num_rows();


// seleciona os registros
$oDBase->query( "
SELECT " . implode(', ', $colunas) ."
FROM servidores_autorizacao
INNER JOIN ips_autorizacao ON servidores_autorizacao.id = ips_autorizacao.servidor_autorizacao_id
LEFT JOIN servativ ON servidores_autorizacao.siape = servativ.mat_siape
LEFT JOIN tabsetor ON servativ.cod_lot = tabsetor.codigo
WHERE tabsetor.upag = :upag " . $filter . "
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
        $dados->siape,
        str_to_utf8($dados->nome_serv),
        str_to_utf8($dados->justificativa),
        databarra($dados->data_inicio),
        databarra($dados->data_fim),
        $dados->endereco
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
