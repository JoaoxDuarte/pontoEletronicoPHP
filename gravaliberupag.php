<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH e sTabServidor');

// parametro passado por formulario
$siape = anti_injection($_POST['siape']);
$modo  = anti_injection($_REQUEST['modo']);

$dt_atuallota = conv_data($_POST['dt_ing_lot']); //converter datas para gravar
$atuallota    = anti_injection($_POST['atuallota']);
$novalota     = anti_injection($_POST['novalota']);
$dtingn       = conv_data($_POST['dtingn']); //converter datas para gravar


// data saída da atual lotação
$dtsai1 = subtrai_dias_da_data($dtingn, 1);
$dtsai  = (inverteData($dt_atuallota) > inverteData($dtsai1) ? $dt_atuallota : $dtsai);

$pagina_de_origem = "liberupag1.php";

$_SESSION['sMov_Matricula_Siape'] = $siape;
$_SESSION['sMov_Entra_Unidade']   = $dtingn;
$_SESSION['sMov_Nova_Unidade']    = $novalota;



## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setSubTitulo("Liberação de Servidor para outra UPAG");

## Topo do formulário
#
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();



## ############################### ##
##                                 ##
##           VALIDAÇÃO             ##
##                                 ##
## ############################### ##

$validar = new valida();
$validar->setDestino( $pagina_de_origem );
$validar->setExibeMensagem( false );

## DATA DE INGRESSO NA NOVA LOTAÇÃO
$validar->data( $dtingn, "A data de ingresso na Nova Lotação deve ser informada!" );

if ($modo == "1" && $validar->getMensagem() != "") 
{
    // Há erro na matrícula informada
    $validar->exibeMensagem();
    die();
}

## DATA DE SAÍDA DEVE SRE MAIOR QUE A DATA DE INGRESSO
if ($modo == "1" && (inverteData($dt_atuallota) > inverteData($dtingn)))
{
    $validar->setMensagem("Data de início na Lotação Atual é maior que data de ingresso na Nova Lotação!\\n");
    $validar->exibeMensagem();
    die();
}

## NOVA LOTAÇÃO
if ($modo == "1" && ($novalota == '00000000000000' || $novalota == '000000000'))
{
    $validar->setMensagem("A Nova Lotação deve ser informada!\\n");
    $validar->exibeMensagem();
    die();
}

if ($modo == "1" && $novalota == $atuallota) //ATUAL LOTAÇÃO
{
    $validar->setMensagem("Selecione uma Nova Lotação!\\n");
    $validar->exibeMensagem();
    die();
}



## ############################### ##
##                                 ##
##            GRAVAÇÃO             ##
##                                 ##
## ############################### ##


if ($modo == "1")
{
    incluiLiberaUPAG($siape, $atuallota, $novalota, $dtingn);

    mensagem("Liberação realizada com sucesso!", "liberupag.php");
}
else if ($modo == "2")
{
    $pagina_de_origem = "recupag.php";

    $siape = anti_injection($_GET["mat"]);

    // dados da liberação
    $oLiberar = dadosLiberacao( $siape );

    // Obtém dados da uorg e upag
    // para saber são a mesma do usuário
    $oSetor = dadosUnidade( $oLiberar->lotdest );

    //atualizando cadastro
    atualizaServativ($siape, $oSetor->area, $oLiberar->dtlibera, $oLiberar->lotdest, $oSetor->uorg, $oLiberar->lotor);

    // atualizando histórico de lotação
    registraHistoricoDeLotacao($siape, $oLiberar->lotor, $oLiberar->lotdest, $oLiberar->dtlibera);

    //atualizando usuarios
    atualizaUsuarios($siape, $oLiberar->lotdest, $oSetor->upag);

    //atualizando liberupag
    atualizaLiberaUPAG($siape);

    mensagem("Recebimento realizado com sucesso!", null, 1);
}
else if ($modo == "3")
{
    $siape = anti_injection($_GET["mat"]);

    excluiLiberaUPAG($siape);

    mensagem("Cancelamento da liberação realizado com sucesso!", "canliberupag.php");
}


## Base do formulário
#
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();



/*
 ************************************************
 *                                              *
 * FUNÇÕES DE APOIO                             *
 *                                              *
 ************************************************
 */

/*
 * Dados da liberação
 */
function dadosLiberacao( $mat )
{
    global $pagina_de_origem;

    // instancia a base de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setDestino($pagina_de_origem); // se houver algum erro redireciona para o destino indica

    $oDBase->query("
    SELECT
        lotdest, dtlibera, lotor
    FROM
        liberupag
    WHERE
        siape = :siape
        AND dtrecebe = '0000-00-00'
    ",
    array(
        array(':siape', $mat, PDO::PARAM_STR),
    ));
    $oLiberar = $oDBase->fetch_object();

    return $oLiberar;
}


/*
 * Obtém dados da uorg e upag
 * para saber são a mesma do usuário
 */
function dadosUnidade($unidade)
{
    global $pagina_de_origem;

    // instancia a base de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setDestino($pagina_de_origem); // se houver algum erro redireciona para o destino indica

    $oDBase->query("
    SELECT
        upag, area, cod_uorg
    FROM
        tabsetor
    WHERE
        codigo = :codigo
    ",
    array(
        array(':codigo', $unidade, PDO::PARAM_STR),
    ));
    $oSetor = $oDBase->fetch_object();

    return $oSetor;
}


/*
 * Atualiza o cadastro
 */
function atualizaServativ($siape, $area, $dtlibera, $lotdest, $uorg, $lotor)
{
    global $pagina_de_origem;

    // instancia a base de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setDestino($pagina_de_origem); // se houver algum erro redireciona para o destino indica

    $oDBase->setMensagem("Erro: na atualização do cadastro");
    $oDBase->query("
    UPDATE servativ
    SET
        area        = :area,
        dt_ing_lot  = :dt_ing_lot,
        cod_lot     = :cod_lot,
        cod_uorg    = :cod_uorg,
        cod_loc     = :cod_loc,
        dt_ing_loc  = :dt_ing_loc,
        cod_lot_ant = :cod_lot_ant,
        cod_loc_ant = :cod_loc_ant,
        dt_sai_lot  = :dt_sai_lot,
        dt_sai_loc  = :dt_sai_loc
    WHERE
        mat_siape = :siape
    ", array(
        array(':siape',       $siape,    PDO::PARAM_STR),
        array(':area',        $area,     PDO::PARAM_STR),
        array(':dt_ing_lot',  $dtlibera, PDO::PARAM_STR),
        array(':cod_lot',     $lotdest,  PDO::PARAM_STR),
        array(':cod_uorg',    $uorg,     PDO::PARAM_STR),
        array(':cod_loc',     $lotdest,  PDO::PARAM_STR),
        array(':dt_ing_loc',  $dtlibera, PDO::PARAM_STR),
        array(':cod_lot_ant', $lotor,    PDO::PARAM_STR),
        array(':cod_loc_ant', $lotor,    PDO::PARAM_STR),
        array(':dt_sai_lot',  $dtlibera, PDO::PARAM_STR),
        array(':dt_sai_loc',  $dtlibera, PDO::PARAM_STR),
    ));
}


/*
 * Registra em HISTORICO DE LOTACAO (HISTLOT)
 */
function registraHistoricoDeLotacao($siape, $lota, $novalota, $dtingn)
{
    global $pagina_de_origem;

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
            dt_sai_lot    = :dt_sai_lot,
            siape_alterou = :siape_alterou,
            data_alterou  = NOW()
        WHERE
            seq = :seq
        ",
        array(
            array( ':seq',           $id_seq,                 PDO::PARAM_STR ),
            array( ':dt_sai_lot',    $dtingn,                 PDO::PARAM_STR ),
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


/*
 * Atualiza o USUARIOS com dados da nova lotação
 */
function atualizaUsuarios($siape, $novalota, $upag)
{
    global $pagina_de_origem;

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
        upag   = :upag
    WHERE
        siape = :siape
    ",
    array(
        array( ':siape',       $siape,    PDO::PARAM_STR ),
        array( ':novalotacao', $novalota, PDO::PARAM_STR ),
        array( ':upag',        $upag,     PDO::PARAM_STR ),
    ));
}


/*
 * Inclui registro em LIBERUPAG
 */
function incluiLiberaUPAG($siape, $atuallota, $novalota, $dtingn)
{
    global $pagina_de_origem;

    $siape   = getNovaMatriculaBySiape($siape);
    $usuario = getNovaMatriculaBySiape($_SESSION['sMatricula']);
    
    // instancia a base de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setDestino($pagina_de_origem); // se houver algum erro redireciona para o destino indica
    $oDBase->setMensagem("Erro: na inclusão da LIBERAÇÃO");

    $oDBase->query("
    INSERT INTO
        liberupag
    SET
        siape          = :siape,
        lotor          = :lotor,
        lotdest        = :lotdest,
        dtlibera       = :dtlibera,
        siape_registro = :siape_registro,
        data_registro  = NOW()
    ",
    array(
        array(':siape',          $siape,     PDO::PARAM_STR),
        array(':lotor',          $atuallota, PDO::PARAM_STR),
        array(':lotdest',        $novalota,  PDO::PARAM_STR),
        array(':dtlibera',       $dtingn,    PDO::PARAM_STR),
        array(':siape_registro', $usuario,   PDO::PARAM_STR),
    ));
}


/*
 * Atualiza registro em LIBERUPAG
 */
function atualizaLiberaUPAG($siape)
{
    global $pagina_de_origem;

    $siape   = getNovaMatriculaBySiape($siape);
    $usuario = getNovaMatriculaBySiape($_SESSION['sMatricula']);

    // instancia a base de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setDestino($pagina_de_origem); // se há erro redireciona para origem
    $oDBase->setMensagem("Erro: na atualização de liberação de UPAG");
    $oDBase->query("
    UPDATE
        liberupag
    SET
        dtrecebe     = NOW(),
        siape_recebe = :siape_recebe,
        data_recebe  = NOW()
    WHERE
        siape = :siape
        AND dtrecebe = '0000-00-00'
    ", 
    array(
        array(':siape',        $siape,   PDO::PARAM_STR),
        array(':siape_recebe', $usuario, PDO::PARAM_STR),
    ));
}


/*
 * Exclui registro em LIBERUPAG
 */
function excluiLiberaUPAG($siape)
{
    global $pagina_de_origem;

    $siape   = getNovaMatriculaBySiape($siape);

    // instancia a base de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setDestino($pagina_de_origem); // se houver algum erro redireciona para o destino indica
    $oDBase->setMensagem("Erro: ao cancelar LIBERAÇÂO");
    $oDBase->query("
    DELETE FROM
        liberupag
    WHERE
        siape = :siape
        AND dtrecebe = '0000-00-00'
    ",
    array(
        array( ':siape', $siape, PDO::PARAM_STR),
    ));
}
