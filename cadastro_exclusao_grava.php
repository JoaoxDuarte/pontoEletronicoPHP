<?php

include_once( "config.php" );

verifica_permissao('sRH ou sTabServidor');

// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();
$destino_erro = $sessao_navegacao->getPaginaAnterior();

//$vUsuario = $sUsuario;
$vHoras = strftime("%H:%M:%S", time());
$vDatas = date("Y-m-d");

//recebe as variáveis do formulario
$tSiape             = anti_injection($_POST['tSiape']);
$codigo_de_exclusao = anti_injection($_POST['codocor']);
$wnome              = trata_aspas(strtoupper(trim(anti_injection($_POST['wnome']))));
$data_de_exclusao   = (empty(trim($_POST['Dataocor'])) ? '00/00/0000' : $_POST['Dataocor']);
$situacao_cadastral = anti_injection($_POST['sitcad']);

$sMatricula = $_SESSION['sMatricula'];

$tSiape = getNovaMatriculaBySiape($tSiape);

// $oDBase : será utilizado em todo o script
$oDBase = new DataBase('PDO');
$oDBase->setDestino( $destino_erro );

//converter datas para gravar
$data_de_exclusao  = conv_data($data_de_exclusao);
$servidor_excluido = 'S';

// grava em sessão
$_SESSION['cad_tSiape']   = $tSiape;
$_SESSION['cad_codocor']  = $codigo_de_exclusao;
$_SESSION['cad_wnome']    = $wnome;
$_SESSION['cad_Dataocor'] = $data_de_exclusao;

// define nova situacao funcional
$nova_situacao_cadastral = "";


// códigos de exclusão - aposentadoria
$codigo_aposentadoria = array();

$oDBase->setMensagem("Problemas no acesso a Tabela OCORRÊNCIAS SIAPE (E000130.".__LINE__.")");
$oDBase->query( "SELECT cod_ocorr FROM tabocorr WHERE UPPER(desc_ocorr) NOT LIKE 'INSTITUIDOR%' " );

while ($rows = $oDBase->fetch_object())
{
    $codigo_aposentadoria[] = $rows->cod_ocorr;
}

//$codigo_aposentadoria = array(
//    "01124", /* APOSENTADO - TCU 733/94                */
//    "02009", /* DISPENSA DE EMPREGO SEM JUSTA CAUSA    */
//    "02010", /* DISPENSA DE EMPREGO POR JUSTA CAUSA    */
//    "02017", /* DISPENSA DE EMPREGO A PEDIDO           */
//    "02031", /* APOSENTADORIA PELO INSS                */
//    "02032", /* APOSENTADORIA PELO TCU                 */
//    "02071", /* DISPENSADO POR SOLICITACAO/EMPRESA     */
//    "02074", /* APOSENTADORIA                          */
//    "02080", /* APOSENTADORIA POR INVALIDEZ TEMPORARIA */
//    "02124", /* TRANSF. INSTIT/BENEFIC. P/ OUTRO ORGAO */
//    "02129", /* APOSENTADORIA                          */
//    "02211", /* APOSENTADORIA POR INVALIDEZ            */
//    "05109", /* APOSENTADORIA COM VANT. ART. 193       */
//);


// códigos de exclusão - óbito
$codigo_instituidor = array();

$oDBase->setMensagem("Problemas no acesso a Tabela OCORRÊNCIAS SIAPE (E000130.".__LINE__.")");
$oDBase->query( "SELECT cod_ocorr FROM tabocorr WHERE UPPER(desc_ocorr) LIKE 'INSTITUIDOR%' " );

while ($rows = $oDBase->fetch_object())
{
    $codigo_instituidor[] = $rows->cod_ocorr;
}

//$codigo_instituidor = array(
//    '01120', /* INSTITUIDOR DE PENSAO          */
//    '01121', /* INSTITUIDOR DE PENSAO GRACIOSA */
//);

// situação cadastral
if (substr($codigo_de_exclusao, 0, 2) == '05' || in_array($codigo_de_exclusao,$codigo_aposentadoria))
{
    $nova_situacao_cadastral = '02';
}
elseif (in_array($codigo_de_exclusao,$codigo_instituidor))
{
    $nova_situacao_cadastral = '15';
}
else
{
    $nova_situacao_cadastral = $situacao_cadastral;
}

//$situacao_cadastral = (empty($nova_situacao_cadastral) ? $situacao_cadastral : $nova_situacao_cadastral);

$affected_rows = 0;

// ATUALIZA O CADASTRO - registra no cadastro a nova situacao funcional
$affected_rows = updateServativPorID($tSiape, $nova_situacao_cadastral, $servidor_excluido, $codigo_de_exclusao, $data_de_exclusao);


if ($affected_rows > 0)
{
    // VERIFICA SE OCUPANTE DE FUNÇÕES - dados da função (se ocupante)
    $ocupante_rows = getOcupantesPorID($tSiape);

    if ($ocupante_rows > 0)
    {
        // ATUALIZA DADOS - OCUPANTE DE FUNÇÕES - HISTÓRICO
        updateHistoricoOcupantesPorID($tSiape, $data_de_exclusao, $situacao_cadastral);

        // INSERIR DADOS -  OCUPANTE DE FUNÇÕES - HISTÓRICO
        insertHistoricoOcupantesPorID($tSiape, $data_de_exclusao, $situacao_cadastral);

        // ATUALIZA DADOS - OCUPANTE DE FUNÇÕES - HISTÓRICO 
        deleteOcupantesPorID($tSiape);
    }
    
    // GRAVA EXCLUSAO SERVATIV
    insertExclusaoPorID($tSiape, $situacao_cadastral, $codigo_de_exclusao, $data_de_exclusao);

    //  DESATIVA USUÁRIOS - desativa usuario trocando a senha atual
    desativaUsuariosPorID($tSiape);

    // grava em sessão
    unset($_SESSION['cad_tSiape']);
    unset($_SESSION['cad_codocor']);
    unset($_SESSION['cad_wnome']);
    unset($_SESSION['cad_Dataocor']);

    //grava o LOG
    registraLog("excluiu o servidor $wnome");

    // limpa siape do servidor
    // para que o teste de erro de upag
    // possa funcionar corretamente;
    $_SESSION['sExc_Matricula_Siape'] = "";

    $mensagem = "Servidor excluído com sucesso!";
    $destino  = 'cadastro_exclusao.php';
}
else
{
    $mensagem = "Erro na exclusão do Servidor/Estagiário!";
    $destino  = $destino_erro;
}



## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCSS("css/select2.min.css");
$oForm->setCSS("css/select2-bootstrap.css");
$oForm->setCSS("js/bootstrap-datepicker/css/bootstrap-datepicker3.standalone.min.css");

$oForm->setJS( "js/select2.full.js");
$oForm->setJS( 'js/bootstrap-datepicker/js/bootstrap-datepicker.min.js');
$oForm->setJS( 'js/bootstrap-datepicker/locales/bootstrap-datepicker.pt-BR.min.js');

$oForm->setJS( "cadastro_exclusao_formulario.js" );

$oForm->setSubTitulo("Exclus&atilde;o de Servidores/Estagi&aacute;rios");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// MENSAGEM
mensagem( $mensagem, $destino);


// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();




/* **************************************************************************
 *
 * FUNÇÕES COMPLEMENTARES
 *
 * **************************************************************************
 */

/**
 * @info ATUALIZA O CADASTRO
 *       registra no cadastro a nova situacao funcional
 *
 * @param string $tSiape
 * @param string $nova_situacao_cadastral
 * @param string $servidor_excluido
 * @param string $codigo_de_exclusao
 * @param string $data_de_exclusao
 * @return interger
 */
function updateServativPorID($mat = null, $nova_situacao_cadastral = null, $servidor_excluido = null, $codigo_de_exclusao = null, $data_de_exclusao = null)
{
    $oDBase = new DataBase();
    $oDBase->setMensagem("Erro na atualização da Tabela SERVATIV (E000130.".__LINE__.")");
    $oDBase->query("
    UPDATE
        servativ
    SET
        cod_serv             = :cod_serv,
        cod_sitcad           = :cod_sitcad,
        excluido             = :excluido,
        reg_obito_dt         = '0000-00-00',
        oco_exclu_oco        = :oco_exclu_oco,
        oco_exclu_dt         = :oco_exclu_dt,
        oco_exclu_dl_cod     = '',
        oco_exclu_dl_num     = '',
        oco_exclu_dl_dt_publ = '0000-00-00'
    WHERE
        mat_siape = :siape
    ",
    array(
        array( ':siape',         $mat,                         PDO::PARAM_STR ),
        array( ':cod_serv',      $nova_situacao_cadastral,     PDO::PARAM_STR ),
        array( ':cod_sitcad',    $nova_situacao_cadastral,     PDO::PARAM_STR ),
        array( ':excluido',      $servidor_excluido,           PDO::PARAM_STR ),
        array( ':oco_exclu_oco', $codigo_de_exclusao,          PDO::PARAM_STR ),
        array( ':oco_exclu_dt',  conv_data($data_de_exclusao), PDO::PARAM_STR ),
    ));

    $affected_rows = $oDBase->affected_rows();

    return $affected_rows;
}


/**
 * @info VERIFICA SE OCUPANTE DE FUNÇÕES
 *
 * @param string $mat
 * @return integer
 */
function getOcupantesPorID($mat = null)
{
    $oDBase = new DataBase();
    $oDBase->setMensagem("Problema no acesso a Tabela OCUPANTES DE FUNÇÃO (E000130.".__LINE__.")");
    $oDBase->query( "
    SELECT
        id,
        SIT_OCUP,
        NUM_FUNCAO
    FROM
        ocupantes
    WHERE
        mat_siape = :mat_siape
        AND dt_fim = '0000-00-00'
    ", array(
        array( ':mat_siape', $mat, PDO::PARAM_STR )
    ));

    $affected_rows = $oDBase->affected_rows();

    return $affected_rows;
}


/**
 * @info ATUALIZA DADOS : OCUPANTE DE FUNÇÕES - HISTÓRICO
 *
 * @param string $mat
 * @param string $data_de_exclusao
 * @param string $situacao_cadastral
 * @return integer
 */
function updateHistoricoOcupantesPorID($mat = null, $data_de_exclusao = null, $situacao_cadastral = null)
{
    $oDBase = new DataBase();
    $oDBase->setMensagem("Erro na atualização da Tabela de HISTÓRICO FUNÇÕES (E000130.".__LINE__.")");
    $oDBase->query( "
    UPDATE historic
    SET
        dt_fim         = :dt_fim,
        cod_serv       = :cod_serv,
        siape_registro = :usuario
    WHERE
        mat_siape = :mat_siape
        AND dt_fim = '0000-00-00'
    ", array(
        array( ':mat_siape', $mat,                    PDO::PARAM_STR ),
        array( ':dt_fim',    $data_de_exclusao,       PDO::PARAM_STR ),
        array( ':usuario',   $_SESSION['sMatricula'], PDO::PARAM_STR ),
        array( ':cod_serv',  $situacao_cadastral,     PDO::PARAM_STR )
    ));

    $affected_rows = $oDBase->affected_rows();

    return $affected_rows;
}


/**
 * @info INSERIR DADOS : OCUPANTE DE FUNÇÕES - HISTÓRICO
 *
 * @param string $mat
 * @param string $data_de_exclusao
 * @param string $situacao_cadastral
 * @return integer
 */
function insertHistoricoOcupantesPorID($mat = null, $data_de_exclusao = null, $situacao_cadastral = null)
{
    $oDBase = new DataBase();
    $oDBase->setMensagem("Erro na atualização da Tabela de HISTÓRICO FUNÇÕES (E000130.".__LINE__.")");
    $oDBase->query( "
    INSERT INTO historic
    SELECT
        mat_siape, nome_serv, sit_ocup, num_funcao, resp_lot, cod_doc1,
        num_doc1, dt_doc1, cod_doc2, num_doc2, dt_doc2, cod_doc3, num_doc3,
        dt_doc3, cod_doc4, num_doc4, dt_doc4, dt_altera, dt_inicio, :dt_fim,
        :cod_serv, dt_atual, decir, dtdecir, :usuario, NOW()
    FROM
        ocupantes
    WHERE
        mat_siape = :mat_siape
        AND dt_fim = '0000-00-00'
    ", array(
        array( ':mat_siape', $mat,                    PDO::PARAM_STR ),
        array( ':dt_fim',    $data_de_exclusao,       PDO::PARAM_STR ),
        array( ':usuario',   $_SESSION['sMatricula'], PDO::PARAM_STR ),
        array( ':cod_serv',  $situacao_cadastral,     PDO::PARAM_STR )
    ));

    $affected_rows = $oDBase->affected_rows();

    return $affected_rows;
}


/**
 * @info APAGAR DADOS : OCUPANTE DE FUNÇÕES
 *
 * @param string $mat
 * @return integer
 */
function deleteOcupantesPorID($mat = null)
{
    $oDBase = new DataBase();
    $oDBase->setMensagem("Erro na atualização da Tabela de OCUPANTES DE FUNÇÃO (E000130.".__LINE__.")");
    $oDBase->query( "
    DELETE FROM ocupantes
    WHERE
        mat_siape = :mat_siape
        AND dt_fim = '0000-00-00'
    ", array(
        array( ':mat_siape', $mat, PDO::PARAM_STR ),
    ));

    $affected_rows = $oDBase->affected_rows();

    return $affected_rows;
}


/**
 * @info Registra exclusão do registro no EXCLUS
 * 
 * @param string $mat
 * @param string $situacao_cadastral
 * @param string $codigo_de_exclusao
 * @param string $data_de_exclusao
 * @return integer
 */
function insertExclusaoPorID($mat = null, $situacao_cadastral = null, $codigo_de_exclusao = null, $data_de_exclusao = null)
{
    $oDBase = new DataBase();
    $oDBase->setMensagem("Erro na exclusão da Tabela de SERVATIV (matrícula ".$mat.") (E000130.".__LINE__.")");
    $oDBase->query("
    INSERT INTO
        exclus
    SET
        siape     = :siape,
        cod_serv  = :cod_serv,
        cod_ocorr = :oco_exclu_oco,
        dt_ocorr  = :oco_exclu_dt,
        tp_doc    = '',
        num_doc   = '',
        dt_doc    = '0000-00-00',
        cartorio  = '',
        dt_obito  = '0000-00-00',
        reg_obito = '',
        fol_obito = '',
        liv_obito = '',
        cod_orgao = '',
        dt_exped  = :oco_exclu_dt
    ",
    array(
        array( ':siape',         $mat,                PDO::PARAM_STR ),
        array( ':cod_serv',      $situacao_cadastral, PDO::PARAM_STR ),
        array( ':oco_exclu_oco', $codigo_de_exclusao, PDO::PARAM_STR ),
        array( ':oco_exclu_dt',  $data_de_exclusao,   PDO::PARAM_STR ),
    ));

    $affected_rows = $oDBase->affected_rows();

    return $affected_rows;
}


/**
 * @info Desativa usuários trocando a senha atual
 * 
 * @param string $mat
 * @return integer
 */
function desativaUsuariosPorID($mat)
{
    $oDBase = new DataBase();
    $oDBase->setMensagem("Erro na atualização da Tabela USUÁRIOS (E000130.".__LINE__.")");
    $oDBase->query("
    UPDATE
        usuarios
    SET
        senha = 'e11abc3849bc57'
    WHERE
        siape = :siape
    ",
    array(
        array( ':siape', $mat, PDO::PARAM_STR ),
    ));

    $affected_rows = $oDBase->affected_rows();

    return $affected_rows;
}
