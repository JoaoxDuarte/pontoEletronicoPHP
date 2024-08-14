<?php

include_once( 'config.php' );

verifica_permissao('administrador_e_chefe_de_rh');

$pesquisa                = addslashes($_REQUEST['pesquisa']);
$alterar                 = addslashes($_REQUEST['alterar']);
$_SESSION['searchCampo'] = ($alterar == 'S' ? $_SESSION['searchCampo'] : $pesquisa);

$gerencia = '';
if ($_SESSION['sSenhaI'] != "S")
{
    $gerencia = "  AND c.upag = '" . $_SESSION['upag'] . "'";
}

// instancia do banco de dados
$oDBase = new DataBase('PDO');

$result = array();

if (!empty($pesquisa))
{
    // instancia o banco de dados
    $oDBase->query(
    "SELECT
        IFNULL(a.siape,'')       AS siape,
        IFNULL(b.nome_serv,'')   AS nome,
        IFNULL(c.codigo,'')      AS setor,
        IFNULL(c.descricao,'')   AS setor_descricao,
        IFNULL(d.denominacao,'') AS orgao_descricao,
        IFNULL(a.senha,'')       AS senha,
        IFNULL(a.acesso,'')      AS acesso,
        IFNULL(a.privilegio,'')  AS privilegio,
        IFNULL(a.prazo,'')       AS prazo,
        IFNULL(a.magico,'')      AS magico,
        IFNULL(c.upag,'')        AS upag,
        IFNULL(b.cpf,'')         AS cpf
    FROM
        usuarios AS a
    LEFT JOIN
        servativ AS b ON a.siape = b.mat_siape
    LEFT JOIN
        tabsetor AS c ON b.cod_lot = c.codigo
    LEFT JOIN
        taborgao AS d ON LEFT(b.cod_lot,5) = d.codigo
    WHERE
        b.excluido = 'N'
        AND (
            b.nome_serv LIKE '%" . $pesquisa . "%'
            OR a.siape LIKE '%" . $pesquisa . "%'
            OR b.cod_lot LIKE '%" . $pesquisa . "%'
        )"
        . $gerencia . "
    ORDER BY
        c.upag,a.setor,b.nome_serv
    ");

    while ($linha = $oDBase->fetch_object())
    {
        $nome      = utf8_iso88591($linha->nome);
        $codigo    = $linha->setor;
        $setor     = substr($linha->setor, 5);
        $nomesetor = utf8_iso88591($linha->setor_descricao);
        $orgao     = substr($linha->setor, 0, 5);
        $nomeorgao = utf8_iso88591($linha->orgao_descricao);

        $result[] = array(
            'siape'     => $linha->siape,
            'nome'      => $nome,
            'codigo'    => $codigo,
            'setor'     => $setor,
            'nomesetor' => $nomesetor,
            'orgao'     => $orgao,
            'nomeorgao' => $nomeorgao,
            'acessos'   => $linha->acesso,
            'upag'      => $linha->upag,
            'cpf'       => $linha->cpf,
            'erro'      => ''
        );
    }
}

$myData = array('dados' => $result);

//        echo "<pre>";
//        var_dump($myData);
//        echo "</pre>";
//        die("asdd");
print json_encode($myData);
