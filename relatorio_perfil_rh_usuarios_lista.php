<?php

include_once( "config.php" );
include_once( "inc/datatables_funcoes_serverside.php" );

verifica_permissao("administracao_central");

set_time_limit( 10000 );
$colunas = array(
'siape',
'nome',
'cpf',
'datapt',
'dtfim',
'upag',
'prazo'
);

$limit  = limit( $_POST );
$filter = filter( $_POST, $colunas );
$order  = order( $_POST, $colunas );

$oDBase = new DataBase();

// Apura o total de registros
$oDBase->query("
    SELECT 0
    FROM `usuarios`
    JOIN `servativ` ON `usuarios`.`siape` = `servativ`.`mat_siape`
    WHERE (LEFT(`usuarios`.`acesso`, 3) = 'SNS')
");
$recordsTotal = $oDBase->num_rows();

// Seleciona os registros
$oDBase->query("
    SELECT `usuarios`.`siape`,
           `usuarios`.`nome`,
           `servativ`.`cpf`,
           `usuarios`.`datapt`,
           `usuarios`.`dtfim`,
           `usuarios`.`upag`,
           CASE `prazo`
               WHEN 0 THEN '0-Ativo'
               WHEN 1 THEN '1-Inativo'
           END AS `prazo`
    FROM `usuarios`
    JOIN `servativ` ON `usuarios`.`siape` = `servativ`.`mat_siape`
    WHERE (LEFT(`usuarios`.`acesso`, 3) = 'SNS')
    " . $filter . "
    " . (empty($order) ? "ORDER BY `usuarios`.`nome`" : $order) . "
    " . $limit
);
$recordsFiltered = $oDBase->num_rows();

$result = array();

while ($dados = $oDBase->fetch_object())
{
    $result[] = array(
    $dados->siape,
    utf8_encode($dados->nome),
    $dados->cpf,
    databarra($dados->datapt),
    databarra($dados->dtfim),
    $dados->upag,
    $dados->prazo
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
