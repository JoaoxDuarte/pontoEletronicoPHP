<?php

// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao('tabela_prazos');

$modo = anti_injection($_REQUEST['modo']);

$id      = anti_injection($_REQUEST['id']);
$dia     = anti_injection($_REQUEST['dia']);
$mes     = anti_injection($_REQUEST['mes']);
$desc    = retira_acentos(anti_injection($_REQUEST['sDescricao']));
$lot     = isset($_REQUEST['lot']) && !empty($_REQUEST['lot']) ? anti_injection($_REQUEST['lot']) : '';
$uf_lota = anti_injection($_REQUEST['uf_lota']);
$tipo    = anti_injection($_REQUEST['tipo']);
$codmun  = isset($_REQUEST['codmun']) && !empty($_REQUEST['codmun']) ? anti_injection($_REQUEST['codmun']) : '';
$flegal  = retira_acentos(anti_injection($_REQUEST['flegal']));

// instancia o banco de dados
$oDBase = new DataBase('PDO');

$ano     = date("Y");
$datafer = $ano . '-' . $mes . '-' . $dia;

$uf_lota = carregaMunicipioUF($codmun);

switch ($modo)
{
    case '1':
        $ano_base = "";
        $query    = "
            UPDATE feriados".$ano_base."
            SET
                dia          = :dia,
                mes          = :mes,
                data_feriado = :datafer,
                base_legal   = :flegal
            WHERE
                id = :id
        ";
        $params   = array(
            array( ':dia', $dia, PDO::PARAM_STR ),
            array( ':mes', $mes, PDO::PARAM_STR ),
            array( ':data_feriado', $datafer, PDO::PARAM_STR ),
            array( ':base_legal',   $flegal, PDO::PARAM_STR ),
            array( ':id',           $id, PDO::PARAM_STR ),
        );
        $oDBase->query( $query, $params );
        $feriados     = $oDBase->affected_rows();

        $query = strtr($query, array("feriados" => "feriados_".$ano));
        $oDBase->query( $query, $params );
        $feriados_ano = $oDBase->affected_rows();

        break;

    case '2':
        $ano_base = "";
        $query    = "
            UPDATE feriados".$ano_base."
            SET
                dia          = :dia,
                mes          = :mes,
                `desc`       = :desc,
                lot          = :lot,
                tipo         = :tipo,
                codmun       = :codmun,
                data_feriado = :data_feriado,
                base_legal   = :base_legal
            WHERE
                id = :id
        ";
        $params   = array(
            array( ':dia',    $dia, PDO::PARAM_STR ),
            array( ':mes',    $mes, PDO::PARAM_STR ),
            array( ':desc',   $desc, PDO::PARAM_STR ),
            array( ':lot',    $lot, PDO::PARAM_STR ),
            array( ':tipo',   $tipo, PDO::PARAM_STR ),
            array( ':codmun', $codmun, PDO::PARAM_STR ),
            array( ':data_feriado', $datafer, PDO::PARAM_STR ),
            array( ':base_legal',   $flegal, PDO::PARAM_STR ),
            array( ':id',           $id, PDO::PARAM_STR ),
        );
        $oDBase->query( $query, $params );
        $feriados     = $oDBase->affected_rows();

        $query = strtr($query, array("feriados" => "feriados_".$ano));
        $oDBase->query( $query, $params );

        $feriados_ano = $oDBase->affected_rows();

        break;
}

if ($feriados > 0 || $feriados_ano > 0)
{
    //registraLog(" alterou os dados do Feriado $datafer,");
    //setMensagemUsuario("Dados do feriado gravados com sucesso!", 'success');
    //replaceLink('tabferiados.php');
    retornaInformacao("Dados do feriado gravados com sucesso!", 'success');
}
else
{
    //setMensagemUsuario("Dados do feriado NÃO foram gravados!", 'danger');
    //replaceLink('tabferiados.php');
    retornaInformacao("Dados do feriado NÃO foram gravados!", 'danger');
}

exit();



## ################################################ ##
##                                                  ##
##              FUNÇÕES COMPLEMENTARES              ##
##                                                  ##
## ################################################ ##

function carregaMunicipioUF($codmun)
{
    $oDBase = new DataBase('PDO');
    
    $oDBase->query("
    SELECT uf 
        FROM cidades 
            WHERE numero = :codmun
    ",
    array(
        array( ':codmun', $codmun, PDO::PARAM_STR ),
    ));
       
    return "";
}
