<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH e sTabServidor');

// parametro passado por formulario
$siape = anti_injection($_REQUEST['siape']);
$modo  = anti_injection($_REQUEST['modo']);

$dt_atuallota = conv_data($_REQUEST['dt_ing_lot']); //converter datas para gravar
$atuallota    = anti_injection($_REQUEST['atuallota']);
$novalota     = anti_injection($_REQUEST['novalota']);
$dtingn       = conv_data($_REQUEST['dtingn']); //converter datas para gravar

// data saída da atual lotação
$dtsai1 = subtrai_dias_da_data($dtingn, 1);
$dtsai = (inverteData($dt_atuallota) > inverteData($dtsai1) ? $dt_atuallota : $dtsai);

$pagina_de_origem = "movimentaservidor.php";

$siape = getNovaMatriculaBySiape($siape);

$_SESSION['sMov_Matricula_Siape'] = $siape;
$_SESSION['sMov_Entra_Unidade']   = $dtingn;
$_SESSION['sMov_Nova_Unidade']    = $novalota;


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setSubTitulo("Movimentação de Servidores e Estagiários");

## Topo do formulário
#
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

## Base do formulário
#
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();


// mensagem
$mensagem = "";


## CLASS VALIDA
$validar = new valida();
$validar->setDestino( $pagina_de_origem );
$validar->setExibeMensagem( false );

## DATA DE INGRESSO NA NOVA LOTAÇÃO
$validar->data( $dtingn, "A data de ingresso na Nova Lotação deve ser informada!" );

## DATA DE SAÍDA DEVE SRE MAIOR QUE A DATA DE INGRESSO
if (inverteData($dt_atuallota) > inverteData($dtingn))
{
    $validar->setMensagem("- Data de início na Lotação Atual é maior que data de ingresso na Nova Lotação!\\n");
}

## NOVA LOTAÇÃO
if ($novalota == '00000000000000')
{
    $validar->setMensagem("- A Nova Lotação deve ser informada!\\n");
}
else if ($novalota == $atuallota) //ATUAL LOTAÇÃO
{
    $validar->setMensagem("- Selecione uma Nova Lotação!\\n");
}

// Exibe mensagem(ns) de erro, se houver
$validar->exibeMensagem();


## ############################### ##
##                                 ##
##            GRAVAÇÃO             ##
##                                 ##
## ############################### ##


if ($modo == "1")
{
    // nome do servidor
    $oServidor = dadosServidor($siape);

    // área e código da uorg da nova lotação
    $oSetor = dadosUnidade($novalota);

    // atualizando servativ
    // exibe mensagem de erro, caso ocorra,
    // e retorna para a página de origem
    if (atualizaServativ($siape, $oSetor->area, $dtingn, $novalota, $oSetor->cod_uorg, $atuallota, $dtsai))
    {
        // atualizando usuarios
        atualizaUsuarios($siape, $novalota);

        // registra em historico
        registraHistoricoDeLotacao($siape, $atuallota, $dtsai, $novalota, $dtingn);

        unset($_SESSION['sMov_Matricula_Siape']);
        unset($_SESSION['sMov_Entra_Unidade']);
        unset($_SESSION['sMov_Nova_Unidade']);

        // grava o LOG
        registraLog(" alterou a lotação do servidor " . $oServidor->nome_serv. ", Siape " . $siape);

        mensagem("Movimentação realizada com sucesso!", "movserv.php", 1);
    }
}
elseif ($modo == "2")
{

}


/*
 ************************************************
 *                                              *
 * FUNÇÕES DE APOIO                             *
 *                                              *
 ************************************************
 */

/*echo('<pre>');
var_export('eze');
die('</pre>');*/


/*
 * Nome do servidor/estagiário
 */
function dadosServidor($siape)
{
    global $pagina_de_origem;

    // instancia a base de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setDestino($pagina_de_origem); // se houver algum erro redireciona para o destino indica
    $oDBase->setMensagem("Erro: no acesso ao SERVATIV.");
    $oDBase->query("
    SELECT
        nome_serv
    FROM
        servativ
    WHERE
        mat_siape = :siape
    ",
    array(
        array( ':siape',       $siape,   PDO::PARAM_STR ),
    ));
    $oDados = $oDBase->fetch_object();

    return $oDados;
}


/*
 * Pega a área e o código da uorg da nova lotação
 */
function dadosUnidade($novalota='')
{
    global $pagina_de_origem;

    $siape = getNovaMatriculaBySiape($siape);

    // instancia a base de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setDestino($pagina_de_origem); // se houver algum erro redireciona para o destino indica
    $oDBase->setMensagem("Falha na movimentação do servidor.");
    $oDBase->query("
    SELECT
        cod_uorg, area
    FROM
        tabsetor
    WHERE
        codigo = :novalotacao
    ",
    array(
        array( ':novalotacao', $novalota, PDO::PARAM_STR ),
    ));
    $oDados = $oDBase->fetch_object();

    return $oDados;
}


/*
 * Atualiza o SERVATIV com os dados da da nova lotação
 */
function atualizaServativ($siape, $area, $dtingn, $novalota, $uorg, $lota, $dtsai)
{
    global $pagina_de_origem;

    $siape = getNovaMatriculaBySiape($siape);

    // instancia a base de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setDestino($pagina_de_origem); // se houver algum erro redireciona para o destino indica
    $oDBase->setMensagem("Erro: na atualização de SERVATIV.");
    $oDBase->query("
    UPDATE
        servativ
    SET
        `area`      = :area,
        cod_uorg    = :cod_uorg,
        cod_lot     = :cod_lot,
        dt_ing_lot  = :dt_ing_lot,
        cod_lot_ant = :cod_lot_ant,
        dt_sai_lot  = :dt_sai_lot,
        cod_loc     = :cod_loc,
        dt_ing_loc  = :dt_ing_loc,
        cod_loc_ant = :cod_loc_ant,
        dt_sai_loc  = :dt_sai_loc
    WHERE
        mat_siape = :siape
    ",
    array(
        array( ':area',        $area,              PDO::PARAM_STR ),
        array( ':cod_uorg',    $uorg,              PDO::PARAM_STR ),
        array( ':cod_lot',     $novalota,          PDO::PARAM_STR ),
        array( ':dt_ing_lot',  conv_data($dtingn), PDO::PARAM_STR ),
        array( ':cod_lot_ant', $lota,              PDO::PARAM_STR ),
        array( ':dt_sai_lot',  conv_data($dtsai),  PDO::PARAM_STR ),
        array( ':cod_loc',     $novalota,          PDO::PARAM_STR ),
        array( ':dt_ing_loc',  conv_data($dtingn), PDO::PARAM_STR ),
        array( ':cod_loc_ant', $lota,              PDO::PARAM_STR ),
        array( ':dt_sai_loc',  conv_data($dtsai),  PDO::PARAM_STR ),
        array( ':siape',       $siape,             PDO::PARAM_STR ),
    ));
    $nRows = $oDBase->affected_rows();

    if ($nRows == 0)
    {
        //mensagem('Movimentação não registrada!\nPor favor, repita a operação.', $pagina_de_origem);
        //return false;
    }
    
    return true;
}


/*
 * Atualiza o USUARIOS com dados da nova lotação
 */
function atualizaUsuarios($siape, $novalota)
{
    global $pagina_de_origem;

    $siape = getNovaMatriculaBySiape($siape);

    // instancia a base de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setDestino($pagina_de_origem); // se houver algum erro redireciona para o destino indica
    $oDBase->setMensagem("Erro: na atualização do USUÁRIO.");
    $oDBase->query("
    UPDATE
        usuarios
    SET
        setor  = :novalotacao,
        acesso = 'NNSNNNNNNNNNN',
        portaria = '',
        datapt = '0000-00-00',
        ptfim = '',
        dtfim = '0000-00-00'
    WHERE
        siape = :siape
    ",
    array(
        array( ':siape',       $siape,    PDO::PARAM_STR ),
        array( ':novalotacao', $novalota, PDO::PARAM_STR ),
    ));
}


/*
 * Registra em HISTORICO DE LOTACAO (HISTLOT)
 */
function registraHistoricoDeLotacao($siape, $lota, $dtsai, $novalota, $dtingn)
{
    global $pagina_de_origem;

    $siape = getNovaMatriculaBySiape($siape);

    // instancia a base de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setDestino($pagina_de_origem); // se houver algum erro redireciona para o destino indica
    $oDBase->setMensagem("Erro: na atualização do HISTLOT.");
    $oDBase->query("
    SELECT
        seq
    FROM
        histlot
    WHERE
        siape = :siape
        AND cod_lot = :cod_lot
        AND dt_sai_lot = '0000-00-00 00:00:00'
    ORDER BY
        dt_ing_lot DESC, dt_sai_lot
    LIMIT 1
    ",
    array(
        array( ':cod_lot', $lota,   PDO::PARAM_STR ),
        array( ':siape',   $siape, PDO::PARAM_STR ),
    ));
    $id_seq = $oDBase->fetch_object()->seq;

    if ($oDBase->num_rows() > 0 && !empty($id_seq))
    {
        $oDBase->query("
        UPDATE
            histlot
        SET
            dt_sai_lot    = DATE_SUB(:dt_ing_lot, INTERVAL 1 DAY),
            siape_alterou = :siape_alterou,
            data_alterou  = NOW()
        WHERE
            seq = :seq
        ",
        array(
            array( ':seq',           $id_seq,                 PDO::PARAM_STR ),
            array( ':dt_ing_lot',    $dtingn,                  PDO::PARAM_STR ),
            array( ':siape_alterou', $_SESSION['sMatricula'], PDO::PARAM_STR ),
        ));
    }

    // inserindo dados no historico
    $oDBase->query("
    INSERT INTO
        histlot
    SET
        siape          = :siape,
        cod_lot        = :cod_lot,
        dt_ing_lot     = :dt_ing_lot,
        siape_registro = :siape_registro,
        data_registro  = NOW()
    ",
     array(
        array( ':siape',          $siape,                  PDO::PARAM_STR ),
        array( ':cod_lot',        $novalota,               PDO::PARAM_STR ),
        array( ':dt_ing_lot',     $dtingn,                 PDO::PARAM_STR ),
        array( ':siape_registro', $_SESSION['sMatricula'], PDO::PARAM_STR ),
     ));
}