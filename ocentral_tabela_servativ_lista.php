<?php

include_once( "config.php" );
include_once( "inc/datatables_funcoes_serverside.php" );

verifica_permissao("administracao_central");

set_time_limit( 10000 );


$campos = loadNameFieldsTables($tabela = 'servativ');

foreach( $campos as $key => $value)
{
    $colunas[] = $key;
}

$limit  = limit( $_POST );
$filter = filter( $_POST, $colunas );
$order  = order( $_POST, $colunas );

$oDBase = new DataBase();

// APURA O TOTAL DE REGISTROS
$oDBase->query( "
SELECT " . implode(', ', $colunas) ."
FROM servativ
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
FROM servativ
LEFT JOIN tabsetor ON servativ.cod_lot = tabsetor.codigo
WHERE tabsetor.upag = :upag " . $filter . "
" . (empty($order) ? "ORDER BY servativ.nome_serv" : $order) . "
" . $limit,
array(
    array( ':upag', $_SESSION['upag_selecao'], PDO::PARAM_STR ),
));
$recordsFiltered = $oDBase->num_rows();

$ind    = 0;
$result = array();

while ($dados = $oDBase->fetch_assoc())
{
    for ($id = 0; $id < count($colunas); $id++)
    {
        $field  = strtr($colunas[$id], array($tabela."." => ""));
        $type  = soLetras($campos[$tabela.".".$field][1]);

        switch ($type)
        {
            case 'date':
                $result[$ind][] = databarra($dados[$field]);
                break;

            case 'text':
            case 'char':
            case 'varchar':
                $result[$ind][] = str_to_utf8($dados[$field]);
                break;

            default:
                $result[$ind][] = $dados[$field];
                break;
        }
    }

    $ind++;
}

$myData = array(
    "draw"            => (isset( $_POST['draw'] ) ? intval( $_POST['draw'] ) : 0),
    "recordsTotal"    => intval( $recordsTotal ),
    "recordsFiltered" => intval( $recordsTotal ),
    'data'            => $result
);

print json_encode($myData);

exit();
