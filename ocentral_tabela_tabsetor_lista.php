<?php

include_once( "config.php" );
include_once( "inc/datatables_funcoes_serverside.php" );

verifica_permissao("administracao_central");

$colunas = array(
  'codigo',
  'descricao',
  'tb0700',
  'uorg_anterior',
  'cod_uorg',
  '`area`',
  'cod_uorg_pai',
  'uorg_pai',
  'upag',
  'ug',
  'inicio_atend',
  'fim_atend',
  'sigla',
  'tfreq',
  'dfreq',
  'ativo',
  'end_lota',
  'num_lota',
  'bairro_lota',
  'cidade_lota',
  'cep_lota',
  'tel_lota',
  'uf_lota',
  'codmun',
  'regiao',
  'regional',
  'fuso_horario',
  'horario_verao',
  'liberar_homologacao',
  'periodo_excecao'
);

$limit  = limit( $_POST );
$filter = filter( $_POST, $colunas );
$order  = order( $_POST, $colunas );

$oDBase = new DataBase();

// APURA O TOTAL DE REGISTROS
$oDBase->query( "
SELECT " . implode(', ', $colunas) ."
FROM tabsetor
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
  tabsetor
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
        $dados->codigo,
        str_to_utf8($dados->descricao),
        $dados->tb0700,
        $dados->uorg_anterior,
        $dados->cod_uorg,
        $dados->area,
        $dados->cod_uorg_pai,
        $dados->uorg_pai,
        $dados->upag,
        $dados->ug,
        $dados->inicio_atend,
        $dados->fim_atend,
        str_to_utf8($dados->sigla),
        $dados->tfreq,
        $dados->dfreq,
        $dados->ativo,
        str_to_utf8($dados->end_lota),
        str_to_utf8($dados->num_lota),
        str_to_utf8($dados->bairro_lota),
        str_to_utf8($dados->cidade_lota),
        $dados->cep_lota,
        $dados->tel_lota,
        $dados->uf_lota,
        $dados->codmun,
        str_to_utf8($dados->regiao),
        str_to_utf8($dados->regional),
        $dados->fuso_horario,
        $dados->horario_verao,
        $dados->liberar_homologacao,
        $dados->periodo_excecao
    );
}

$myData = array(
    "draw"            => (isset( $_POST['draw'] ) ? intval( $_POST['draw'] ) : 0),
    "recordsTotal"    => intval( $recordsTotal ),
    "recordsFiltered" => intval( $recordsTotal ),
    'data' => $result);

print json_encode($myData);
