<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH ou Chefia');

// pega o nome do arquivo origem
$pagina_de_origem = pagina_de_origem();

//Recebe  a opção da página anterior
//Recebendo os dados a serem incluidos
$vDatas     = date("Y-m-d");
$modo       = anti_injection($_REQUEST['modo']);
$num_funcao = anti_injection($_REQUEST['num_funcao']);
$funcao     = anti_injection($_REQUEST['funcao']);
$lota       = anti_injection($_REQUEST['lota']);
$sigla      = anti_injection($_REQUEST['sigla']);
$uorg       = anti_injection($_REQUEST['uorg']);
$ugpai      = anti_injection($_REQUEST['pai']);
$sit2        = anti_injection($_REQUEST['ocupacao']);
$nome       = anti_injection($_REQUEST['nome']);
$lotat      = anti_injection($_REQUEST['lotat']);
$dinglota   = (empty($_REQUEST['dinglota']) ? '0000-00-00' : conv_data($_REQUEST['dinglota']));

$matricula = anti_injection($_REQUEST['matricula']);
$matricula = getNovaMatriculaBySiape($matricula);

$area       = anti_injection($_REQUEST['area']);
$publicacao = anti_injection($_REQUEST['publicacao']);

$inicio = (empty($_REQUEST['inicio']) ? '0000-00-00' : conv_data($_REQUEST['inicio']));

$Ndoc1 = anti_injection($_REQUEST['Ndoc1']);
$Ndoc2 = anti_injection($_REQUEST['Ndoc2']);
$Nnum1 = anti_injection($_REQUEST['Nnum1']);
$Nnum2 = anti_injection($_REQUEST['Nnum2']);

$Ndata1 = (empty($_REQUEST['Ndata1']) ? '0000-00-00' : conv_data($_REQUEST['Ndata1']));
$Ndata2 = (empty($_REQUEST['Ndata2']) ? '0000-00-00' : conv_data($_REQUEST['Ndata2']));

// grava dados em sessao
$_SESSION['sGravaFuncaoSiape']      = $matricula;
$_SESSION['sGravaFuncaoNumFuncao']  = $num_funcao;
$_SESSION['sGravaFuncaoSituacao']   = $sit2;

$_SESSION['sGravaFuncaoFuncao']     = $funcao;
$_SESSION['sGravaFuncaoLota']       = $lota;
$_SESSION['sGravaFuncaoSigla']      = $sigla;
$_SESSION['sGravaFuncaoUorg']       = $uorg;
$_SESSION['sGravaFuncaoPai']        = $ugpai;
$_SESSION['sGravaFuncaoNome']       = $nome;
$_SESSION['sGravaFuncaoLotat']      = $lotat;
$_SESSION['sGravaFuncaoDingLota']   = $dinglota;
$_SESSION['sGravaFuncaoArea']       = $area;
$_SESSION['sGravaFuncaoPublicacao'] = $publicacao;
$_SESSION['sGravaFuncaoInicio']     = $inicio;
$_SESSION['sGravaFuncaoNdoc1']      = $Ndoc1;
$_SESSION['sGravaFuncaoNdoc2']      = $Ndoc2;
$_SESSION['sGravaFuncaoNnum1']      = $Nnum1;
$_SESSION['sGravaFuncaoNnum2']      = $Nnum2;
$_SESSION['sGravaFuncaoNdata1']     = $Ndata1;
$_SESSION['sGravaFuncaoNdata2']     = $Ndata2;

$_SESSION['grava_inclui_funcao'] = true;

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// banco de dados
$oDBase = new DataBase('PDO');

// verifica se a funcao eh responsavel por um setor
$oDBase->query("SELECT resp_lot FROM tabfunc WHERE ativo ='S' AND num_funcao = :funcao ",
    array(
        array(":funcao", $num_funcao, PDO::PARAM_STR),
    ));
$oFuncao  = $oDBase->fetch_object();
$resp_lot = $oFuncao->resp_lot;


/* ----------------------------------*\
  |                                    |
  \*---------------------------------- */
if ($modo == "1")
{

    // validacao dos campos
    $validacao = new valida();
    $validacao->setExibeMensagem(false);
    $validacao->setDestino($_SESSION['sHOrigem_2']);
    $validacao->setVoltar(0);

    if (!validaData($inicio))
    {
        $validacao->setMensagem("- Data de início do exercício Inválida!\\n");
    }

    if (strlen(alltrim($Nnum1)) == 0)
    {
        $validacao->setMensagem("- O numero da portaria!\\n");
    }

    if (!validaData($Ndata1))
    {
        $validacao->setMensagem("- A data da portaria é Inválida!!\\n");
    }

    if ($publicacao == '00')
    {
        $validacao->setMensagem("- Selecionar o meio de publicação!\\n");
    }

    if (strlen(alltrim($Nnum2)) == 0)
    {
        $validacao->setMensagem("- O número do meio de publicação!\\n");
    }

    if (!validaData($Ndata2))
    {
        $validacao->setMensagem("- A data da publicação é Inválida!!\\n");
    }

    $validacao->exibeMensagem(); // exibe mensagem de erro se houver

    // instancia o banco de dados
    $oDBase->setDestino($pagina_de_origem);

    $resp = 0;

    $oDBase->query("
    INSERT INTO ocupantes
        (id, MAT_SIAPE, NOME_SERV, SIT_OCUP, NUM_FUNCAO, RESP_LOT, 
        COD_DOC1, NUM_DOC1, DT_DOC1, 
        COD_DOC2, NUM_DOC2, DT_DOC2, 
        COD_DOC3, NUM_DOC3, DT_DOC3, 
        COD_DOC4, NUM_DOC4, DT_DOC4, 
        DT_ALTERA, DT_INICIO, DT_FIM, 
        COD_SERV, DT_ATUAL, DECIR, DTDECIR)
    VALUES
        (0, :MAT_SIAPE, :NOME_SERV, :SIT_OCUP, :NUM_FUNCAO, :RESP_LOT, 
        :COD_DOC1, :NUM_DOC1, :DT_DOC1, 
        :COD_DOC2, :NUM_DOC2, :DT_DOC2, 
        :COD_DOC3, :NUM_DOC3, :DT_DOC3, 
        :COD_DOC4, :NUM_DOC4, :DT_DOC4, 
        :DT_ALTERA, :DT_INICIO, :DT_FIM, 
        :COD_SERV, :DT_ATUAL, :DECIR, :DTDECIR)
    ",
    array(
        array( ':MAT_SIAPE',  $matricula,   PDO::PARAM_STR ),
        array( ':NOME_SERV',  $nome,        PDO::PARAM_STR ),
        array( ':SIT_OCUP',   $sit2,        PDO::PARAM_STR ),
        array( ':NUM_FUNCAO', $num_funcao,  PDO::PARAM_STR ),
        array( ':RESP_LOT',   $resp_lot,    PDO::PARAM_STR ),
        array( ':COD_DOC1',   'PT',         PDO::PARAM_STR ),
        array( ':NUM_DOC1',   $Nnum1,       PDO::PARAM_STR ),
        array( ':DT_DOC1',    $Ndata1,      PDO::PARAM_STR ),
        array( ':COD_DOC2',   $publicacao,  PDO::PARAM_STR ),
        array( ':NUM_DOC2',   $Nnum2,       PDO::PARAM_STR ),
        array( ':DT_DOC2',    $Ndata2,      PDO::PARAM_STR ),
        array( ':COD_DOC3',   '',           PDO::PARAM_STR ),
        array( ':NUM_DOC3',   '',           PDO::PARAM_STR ),
        array( ':DT_DOC3',    '0000-00-00', PDO::PARAM_STR ),
        array( ':COD_DOC4',   '',           PDO::PARAM_STR ),
        array( ':NUM_DOC4',   '',           PDO::PARAM_STR ),
        array( ':DT_DOC4',    '0000-00-00', PDO::PARAM_STR ),
        array( ':DT_ALTERA',  '0000-00-00', PDO::PARAM_STR ),
        array( ':DT_INICIO',  $inicio,      PDO::PARAM_STR ),
        array( ':DT_FIM',     '0000-00-00', PDO::PARAM_STR ),
        array( ':COD_SERV',   '00000000',   PDO::PARAM_STR ),
        array( ':DT_ATUAL',   '0000-00-00', PDO::PARAM_STR ),
        array( ':DECIR',      'N',          PDO::PARAM_STR ),
        array( ':DTDECIR',    $vDatas,      PDO::PARAM_STR ),
    ));
    $resp = $oDBase->affected_rows();


    $oDBase->query("
    INSERT INTO historic
        (mat_siape, nome_serv, sit_ocup, num_funcao, resp_lot, 
        cod_doc1, num_doc1, dt_doc1, 
        cod_doc2, num_doc2, dt_doc2, 
        cod_doc3, num_doc3, dt_doc3, 
        cod_doc4, num_doc4, dt_doc4, 
        dt_altera, dt_inicio, dt_fim, 
        cod_serv, dt_atual, decir, dtdecir, 
        siape_registro, data_registro)
    VALUES
        (:mat_siape, :nome_serv, :sit_ocup, :num_funcao, :resp_lot, 
        :cod_doc1, :num_doc1, :dt_doc1, 
        :cod_doc2, :num_doc2, :dt_doc2, 
        :cod_doc3, :num_doc3, :dt_doc3, 
        :cod_doc4, :num_doc4, :dt_doc4, 
        :dt_altera, :dt_inicio, :dt_fim, 
        :cod_serv, :dt_atual, :decir, :dtdecir, 
        :siape_registro, NOW())
    ",
    array(
        array( ':mat_siape',      $matricula,   PDO::PARAM_STR ),
        array( ':nome_serv',      $nome,        PDO::PARAM_STR ),
        array( ':sit_ocup',       $sit2,        PDO::PARAM_STR ),
        array( ':num_funcao',     $num_funcao,  PDO::PARAM_STR ),
        array( ':resp_lot',       $resp_lot,    PDO::PARAM_STR ),
        array( ':cod_doc1',       'PT',         PDO::PARAM_STR ),
        array( ':num_doc1',       $Nnum1,       PDO::PARAM_STR ),
        array( ':dt_doc1',        $Ndata1,      PDO::PARAM_STR ),
        array( ':cod_doc2',       $publicacao,  PDO::PARAM_STR ),
        array( ':num_doc2',       $Nnum2,       PDO::PARAM_STR ),
        array( ':dt_doc2',        $Ndata2,      PDO::PARAM_STR ),
        array( ':cod_doc3',       '',           PDO::PARAM_STR ),
        array( ':num_doc3',       '',           PDO::PARAM_STR ),
        array( ':dt_doc3',        '0000-00-00', PDO::PARAM_STR ),
        array( ':cod_doc4',       '',           PDO::PARAM_STR ),
        array( ':num_doc4',       '',           PDO::PARAM_STR ),
        array( ':dt_doc4',        '0000-00-00', PDO::PARAM_STR ),
        array( ':dt_altera',      '0000-00-00', PDO::PARAM_STR ),
        array( ':dt_inicio',      $inicio,      PDO::PARAM_STR ),
        array( ':dt_fim',         '0000-00-00', PDO::PARAM_STR ),
        array( ':cod_serv',       '',           PDO::PARAM_STR ),
        array( ':dt_atual',       '0000-00-00', PDO::PARAM_STR ),
        array( ':decir',          '',           PDO::PARAM_STR ),
        array( ':dtdecir',        '0000-00-00', PDO::PARAM_STR ),
        array( ':siape_registro', $_SESSION['sMatricula'], PDO::PARAM_STR ),
    ));

    //atualizar lotacao e historico quando nomear e exonerar de funcao.
    //identifica uorg pai
    $oDBase->query("
    SELECT
        codigo, descricao, cod_uorg, uorg_pai, upag
    FROM
        tabsetor
    WHERE
        codigo = :codigo
    ",
    array(
        array(":codigo", $lota, PDO::PARAM_STR),
    ));
    $oSetor = $oDBase->fetch_object();

    //recupera dia anterior a nomeação
    $dia    = dataDia($inicio) - 1;
    $mes    = dataMes($inicio);
    $ano    = dataAno($inicio);
    $data   = mktime(0, 0, 0, $mes, $dia, $ano);
    $sailot = date('Y-m-d', $data);

    // dados do usuário
    $oDBase->query("
    SELECT
        IFNULL(portaria,'')            AS portaria,
        IFNULL(datapt,'0000-00-00')    AS datapt,
        IFNULL(ptfim,'')               AS ptfim,
        IFNULL(dtfim,'0000-00-00')     AS dtfim,
        IFNULL(acesso,'NSSNNNNNNNNNN') AS acesso
    FROM
        usuarios
    WHERE
        siape = :siape
    ",
    array(
        array(":siape", $matricula, PDO::PARAM_STR),
    ));
    $oUsuarios = $oDBase->fetch_object();

    if ($oUsuarios->datapt != '0000-00-00' && $oUsuarios->dtfim == '0000-00-00')
    {
        $usu_ptfim = $Nnum1;
        $usu_dtfim = conv_data($Ndata1);
        $sAcesso   = $oUsuarios->acesso;
        $nMagico   = 0;
    }
    else
    {
        $usu_ptfim = $oUsuarios->ptfim;
        $usu_dtfim = $oUsuarios->dtfim;
        $sAcesso   = $oUsuarios->acesso;
        $nMagico   = 0;
    }

    //atualizando servativ
    $oDBase->setMensagem("Erro na atualizacao da lotacao, verifique o modulo apropriado!");

    if (($resp_lot == "S" || $resp_lot == "N") && $sit2 != "S")
    {
        $oDBase->query("
        UPDATE
            servativ
        SET
            chefia      = :resp_lot,
            area        = :area,
            cod_lot     = :cod_lot,
            dt_ing_lot  = :dt_ing_lot,
            cod_uorg    = :cod_uorg,
            cod_lot_ant = :cod_lot_ant,
            dt_sai_lot  = :dt_sai_lot,
            dt_sai_loc  = :dt_sai_loc,
            cod_loc     = :cod_loc,
            dt_ing_loc  = :dt_ing_loc
        WHERE
            mat_siape = :siape
        ",
        array(
            array(":resp_lot",    $resp_lot,  PDO::PARAM_STR),
            array(":area",        $area,      PDO::PARAM_STR),
            array(":cod_lot",     $lota,      PDO::PARAM_STR),
            array(":dt_ing_lot",  $inicio,    PDO::PARAM_STR),
            array(":cod_uorg",    $uorg,      PDO::PARAM_STR),
            array(":cod_lot_ant", $lotat,     PDO::PARAM_STR),
            array(":dt_sai_lot",  $sailot,    PDO::PARAM_STR),
            array(":dt_sai_loc",  $sailot,    PDO::PARAM_STR),
            array(":cod_loc",     $lota,      PDO::PARAM_STR),
            array(":dt_ing_loc",  $inicio,    PDO::PARAM_STR),
            array(":siape",       $matricula, PDO::PARAM_STR),
        ));

        $oDBase->setMensagem("Erro na atualizacao das permissões do ocupante designado!");

        if ($resp_lot == "S")
        {
            // ocupantes de função DAS-4, DAS-5 e DAS-6 sem dispensados de registrar frequência
            // atribuindo 1 ao campo 'magico' informamos ao aplicativo a dispensa de registro
            $nMagico = (substr_count("DAS1014_DAS1015_DAS1016_DAS1024", $sigla) > 0 ? '1' : '0');

            // permissoes de chefia
            $sAcesso = ($oSetor->codigo == $oSetor->upag ? 'SSSNNNNNNSNNN' : 'NSSNNNNNNNNNN' );
        }
    }

    if ($resp > 0) //($resp == 1 && $resp2 ==1)
    {
        //atualizando usuarios
        $oDBase->query("
        UPDATE
            usuarios
        SET
            setor  = :setor,
            acesso = :acesso,
            magico = :magico,
            ptfim  = :ptfim,
            dtfim  = :dtfim
        WHERE
            siape = :siape
        ",
        array(
            array( ":siape",  $matricula, PDO::PARAM_STR ),
            array( ":acesso", $sAcesso,   PDO::PARAM_STR ),
            array( ":setor",  $lota,      PDO::PARAM_STR ),
            array( ":ptfim",  $usu_ptfim, PDO::PARAM_STR ),
            array( ":dtfim",  $usu_dtfim, PDO::PARAM_STR ),
            array( ":magico", $nMagico,   PDO::PARAM_STR ),
        ));

        //atualizando historico de lotacao
        $oDBase->setMensagem("Erro na atualizacao de histlot!");

        $oDBase->query("
        UPDATE
            histlot
        SET
            dt_sai_lot = :dt_sai_lot,
            dt_sai_loc = :dt_sai_loc
        WHERE
            siape = :siape
            AND cod_lot = :cod_lot
            AND dt_ing_lot = :dt_ing_lot
        ",
        array(
            array(":dt_sai_lot", $sailot,    PDO::PARAM_STR),
            array(":dt_sai_loc", $sailot,    PDO::PARAM_STR),
            array(":siape",      $matricula, PDO::PARAM_STR),
            array(":cod_lot",    $lotat,     PDO::PARAM_STR),
            array(":dt_ing_lot", $dinglota,  PDO::PARAM_STR),
        ));

        $oDBase->query("
        INSERT INTO histlot
            (siape, cod_lot, dt_ing_lot, dt_sai_lot, cod_loc, dt_ing_loc, dt_sai_loc, cod_uorg, cod_uorg_loc, siape_registro, data_registro, siape_alterou, data_alterou, seq)
        VALUES
            (:siape, :cod_lot, :dt_ing_lot, :dt_sai_lot, :cod_loc, :dt_ing_loc, :dt_sai_loc, :cod_uorg, :cod_uorg_loc, :siape_registro, NOW(), :siape_alterou, :data_alterou, 0)
        ",
        array(
            array( ':siape',          $matricula,   PDO::PARAM_STR),
            array( ':cod_lot',        $lota,        PDO::PARAM_STR),
            array( ':dt_ing_lot',     $inicio,      PDO::PARAM_STR),
            array( ':dt_sai_lot',     '0000-00-00', PDO::PARAM_STR),
            array( ':cod_loc',        $lota,        PDO::PARAM_STR),
            array( ':dt_ing_loc',     $inicio,      PDO::PARAM_STR),
            array( ':dt_sai_loc',     '0000-00-00', PDO::PARAM_STR),
            array( ':cod_uorg',       $lota,        PDO::PARAM_STR),
            array( ':cod_uorg_loc',   $lota,        PDO::PARAM_STR),
            array( ':siape_registro', $_SESSION['sMatricula'], PDO::PARAM_STR),
            array( ':siape_alterou',  '',           PDO::PARAM_STR),
            array( ':data_alterou',   '0000-00-00', PDO::PARAM_STR),
        ));

        // grava dados gravados em sessao
        unset($_SESSION['sGravaFuncaoSiape']);
        unset($_SESSION['sGravaFuncaoNumFuncao']);
        unset($_SESSION['sGravaFuncaoSituacao']);

        unset($_SESSION['sGravaFuncaoNovaFuncao']);
        unset($_SESSION['sGravaFuncaoIdSubs']);
        unset($_SESSION['sGravaFuncaoMatricula']);
        unset($_SESSION['sGravaFuncaoOcupacao']);

        unset($_SESSION['sGravaFuncaoFuncao']);
        unset($_SESSION['sGravaFuncaoLota']);
        unset($_SESSION['sGravaFuncaoSigla']);
        unset($_SESSION['sGravaFuncaoUorg']);
        unset($_SESSION['sGravaFuncaoPai']);
        unset($_SESSION['sGravaFuncaoNome']);
        unset($_SESSION['sGravaFuncaoLotat']);
        unset($_SESSION['sGravaFuncaoDingLota']);
        unset($_SESSION['sGravaFuncaoArea']);
        unset($_SESSION['sGravaFuncaoPublicacao']);
        unset($_SESSION['sGravaFuncaoInicio']);
        unset($_SESSION['sGravaFuncaoNdoc1']);
        unset($_SESSION['sGravaFuncaoNdoc2']);
        unset($_SESSION['sGravaFuncaoNnum1']);
        unset($_SESSION['sGravaFuncaoNnum2']);
        unset($_SESSION['sGravaFuncaoNdata1']);
        unset($_SESSION['sGravaFuncaoNdata2']);

        mensagem("Dados gravados com sucesso!", "incfuncserv.php");
    }
    else
    {
        mensagem("Não foi possível concluir a inclusão da função!", $pagina_de_origem);
    }
}

/* ----------------------------------*\
  |                                    |
  \*---------------------------------- */
if ($modo == "2")
{
    $vDatas = date("Y-m-d");

    //Recebe  a opção da página anterior
    //Recebendo os dados a serem incluidos
    $matricula = getNovaMatriculaBySiape(anti_injection($_REQUEST['matricula']));
    $Nfuncao   = anti_injection($_REQUEST['Nfuncao']);
    $funcao    = anti_injection($_REQUEST['funcao']);
    $Edoc1     = anti_injection($_REQUEST['Edoc1']);
    $Edoc2     = anti_injection($_REQUEST['Edoc2']);

    $Edata3    = (empty($_REQUEST['Edata1']) ? '0000-00-00' : conv_data($_REQUEST['Edata1']));
    $Edata4    = (empty($_REQUEST['Edata2']) ? '0000-00-00' : conv_data($_REQUEST['Edata2']));

    $fim = (empty($_REQUEST['dt_fim']) ? '0000-00-00' : conv_data($_REQUEST['dt_fim']));

    $Enum1 = anti_injection($_REQUEST['Enum1']);
    $Enum2 = anti_injection($_REQUEST['Enum2']);

    ///*
    // atualiza na tabela de historico
    $historic = "
    UPDATE
        historic
    SET
        dt_fim = '$fim',
        cod_doc3 = '$Edoc1',
        cod_doc4 = '$Edoc2',
        num_doc3 = '$Enum1',
        num_doc4 = '$Enum2',
        dt_doc3 = '$Edata3',
        dt_doc4 = '$Edata4',
        siape_registro = '".$_SESSION['sMatricula']."',
        data_registro = NOW()
    WHERE
        mat_siape = '$matricula'
        AND num_funcao = '$Nfuncao'
        AND sit_ocup = '$sit2'
        AND dt_fim = '0000-00-00'";
    $oDBase->query($historic);
    $resp     = $oDBase->affected_rows();

    $oDBase->query("
    INSERT INTO historic
        (mat_siape, nome_serv, sit_ocup, num_funcao, resp_lot, cod_doc1, num_doc1, dt_doc1, cod_doc2, num_doc2, dt_doc2, cod_doc3, num_doc3, dt_doc3, cod_doc4, num_doc4, dt_doc4, dt_altera, dt_inicio, dt_fim, cod_serv, dt_atual, decir, dtdecir, siape_registro, data_registro)
    VALUES
        (:mat_siape, :nome_serv, :sit_ocup, :num_funcao, :resp_lot, :cod_doc1, :num_doc1, :dt_doc1, :cod_doc2, :num_doc2, :dt_doc2, :cod_doc3, :num_doc3, :dt_doc3, :cod_doc4, :num_doc4, :dt_doc4, :dt_altera, :dt_inicio, :dt_fim, :cod_serv, :dt_atual, :decir, :dtdecir, :siape_registro, NOW())
    ",
    array(
        array( ':mat_siape',      $matricula,   PDO::PARAM_STR ),
        array( ':nome_serv',      $nome,        PDO::PARAM_STR ),
        array( ':sit_ocup',       $sit2,        PDO::PARAM_STR ),
        array( ':num_funcao',     $num_funcao,  PDO::PARAM_STR ),
        array( ':resp_lot',       $resp_lot,    PDO::PARAM_STR ),
        array( ':cod_doc1',       'PT',         PDO::PARAM_STR ),
        array( ':num_doc1',       $Nnum1,       PDO::PARAM_STR ),
        array( ':dt_doc1',        $Ndata1,      PDO::PARAM_STR ),
        array( ':cod_doc2',       $publicacao,  PDO::PARAM_STR ),
        array( ':num_doc2',       $Nnum2,       PDO::PARAM_STR ),
        array( ':dt_doc2',        $Ndata2,      PDO::PARAM_STR ),
        array( ':cod_doc3',       '',           PDO::PARAM_STR ),
        array( ':num_doc3',       '',           PDO::PARAM_STR ),
        array( ':dt_doc3',        '0000-00-00', PDO::PARAM_STR ),
        array( ':cod_doc4',       '',           PDO::PARAM_STR ),
        array( ':num_doc4',       '',           PDO::PARAM_STR ),
        array( ':dt_doc4',        '0000-00-00', PDO::PARAM_STR ),
        array( ':dt_altera',      '0000-00-00', PDO::PARAM_STR ),
        array( ':dt_inicio',      $inicio,      PDO::PARAM_STR ),
        array( ':dt_fim',         '0000-00-00', PDO::PARAM_STR ),
        array( ':cod_serv',       '',           PDO::PARAM_STR ),
        array( ':dt_atual',       '0000-00-00', PDO::PARAM_STR ),
        array( ':decir',          '',           PDO::PARAM_STR ),
        array( ':dtdecir',        '0000-00-00', PDO::PARAM_STR ),
        array( ':siape_registro', $_SESSION['sMatricula'], PDO::PARAM_STR ),
    ));

    // deleta da tabela atual
    $atual = "DELETE FROM ocupantes WHERE mat_siape = '$matricula' AND num_funcao = '$Nfuncao' AND sit_ocup = '$sit2' AND dt_fim = '0000-00-00'";
    $oDBase->query($atual);
    $resp2 = $oDBase->affected_rows();

    if ($resp == 1 && $resp2 == 1)
    {
        mensagem("Dados gravados com sucesso!", $pagina_de_origem, 1);
    }
    else
    {
        mensagem("Não foi possível concluir a inclusão da função!", $pagina_de_origem, 1);
    }
}

/* ----------------------------------*\
  |                                    |
  \*---------------------------------- */
if ($modo == "3")
{
    $data2 = (empty($_REQUEST['data']) ? '0000-00-00' : conv_data($_REQUEST['data']));
    $sql   = "UPDATE ocupantes SET decir = 'S', dtdecir = '$data2' WHERE mat_siape = '$matricula' AND sit_ocup = 'T'";
    $oDBase->query($sql);
    mensagem("Dados de entrega de declaração gravados com sucesso!", $pagina_de_origem, 1);
}

/* ----------------------------------*\
  |                                    |
  \*---------------------------------- */
if ($modo == "4")
{
    //Recebe  a opção da página anterior
    //Recebendo os dados a serem incluidos
    $matricula   = getNovaMatriculaBySiape(anti_injection($_REQUEST['matricula']));
    $nome        = anti_injection($_REQUEST['nome']);
    $num_funcao  = anti_injection($_REQUEST['novafuncao']);
    $sit2        = anti_injection($_REQUEST['ocupacao']);
    $Nnum1       = anti_injection($_REQUEST['Nnum1']);
    $Nnum2       = anti_injection($_REQUEST['Nnum2']);
    $Nnum3       = anti_injection($_REQUEST['Nnum3']);
    $Nnum4       = anti_injection($_REQUEST['Nnum4']);
    $publicacao  = anti_injection($_REQUEST['publicacao']);
    $publicacao2 = anti_injection($_REQUEST['publicacao2']);
    $respon      = anti_injection($_REQUEST['respon']);

    $inicio = (empty($_REQUEST['inicio']) ? '0000-00-00' : conv_data($_REQUEST['inicio']));
    $fim    = (empty($_REQUEST['fim']) ? '0000-00-00' : conv_data($_REQUEST['fim']));

    $Ndata1 = (empty($_REQUEST['Ndata1']) ? '0000-00-00' : conv_data($_REQUEST['Ndata1']));
    $Ndata2 = (empty($_REQUEST['Ndata2']) ? '0000-00-00' : conv_data($_REQUEST['Ndata2']));
    $Ndata3 = (empty($_REQUEST['Ndata3']) ? '0000-00-00' : conv_data($_REQUEST['Ndata3']));
    $Ndata4 = (empty($_REQUEST['Ndata4']) ? '0000-00-00' : conv_data($_REQUEST['Ndata4']));

    //Implementar busca para saber se o periodo ja foi inserido
    $busca = "SELECT * FROM historic WHERE dt_inicio <= '$fim' AND dt_fim >= '$inicio' AND mat_siape='$matricula' AND sit_ocup = '$sit2' ";
    $oDBase->query($busca);
    $rows  = $oDBase->num_rows();

    //Só insere o histórico se não tiver um lançado
    if ($rows == 0)
    {
        $oDBase->query("
        INSERT INTO historic
            (mat_siape, nome_serv, sit_ocup, num_funcao, resp_lot, cod_doc1, num_doc1, dt_doc1, cod_doc2, num_doc2, dt_doc2, cod_doc3, num_doc3, dt_doc3, cod_doc4, num_doc4, dt_doc4, dt_altera, dt_inicio, dt_f    im, cod_serv, dt_atual, decir, dtdecir, siape_registro, data_registro)
        VALUES
            (:mat_siape, :nome_serv, :sit_ocup, :num_funcao, :resp_lot, :cod_doc1, :num_doc1, :dt_doc1, :cod_doc2, :num_doc2, :dt_doc2, :cod_doc3, :num_doc3, :dt_doc3, :cod_doc4, :num_doc4, :dt_doc4, :dt_alte    ra, :dt_inicio, :dt_fim, :cod_serv, :dt_atual, :decir, :dtdecir, :siape_registro, NOW())
        ",
        array(
            array( ':mat_siape',      $matricula,   PDO::PARAM_STR ),
            array( ':nome_serv',      $nome,        PDO::PARAM_STR ),
            array( ':sit_ocup',       $sit2,        PDO::PARAM_STR ),
            array( ':num_funcao',     $num_funcao,  PDO::PARAM_STR ),
            array( ':resp_lot',       $respon,      PDO::PARAM_STR ),
            array( ':cod_doc1',       'PT',         PDO::PARAM_STR ),
            array( ':num_doc1',       $Nnum1,       PDO::PARAM_STR ),
            array( ':dt_doc1',        $Ndata1,      PDO::PARAM_STR ),
            array( ':cod_doc2',       $publicacao,  PDO::PARAM_STR ),
            array( ':num_doc2',       $Nnum2,       PDO::PARAM_STR ),
            array( ':dt_doc2',        $Ndata2,      PDO::PARAM_STR ),
            array( ':cod_doc3',       'PT',         PDO::PARAM_STR ),
            array( ':num_doc3',       $Nnum3 ,      PDO::PARAM_STR ),
            array( ':dt_doc3',        $Ndata3,      PDO::PARAM_STR ),
            array( ':cod_doc4',       $publicacao2, PDO::PARAM_STR ),
            array( ':num_doc4',       $Nnum4,       PDO::PARAM_STR ),
            array( ':dt_doc4',        $Ndata4,      PDO::PARAM_STR ),
            array( ':dt_altera',      $altera,      PDO::PARAM_STR ),
            array( ':dt_inicio',      $inicio,      PDO::PARAM_STR ),
            array( ':dt_fim',         $fim,         PDO::PARAM_STR ),
            array( ':cod_serv',       '00000000',   PDO::PARAM_STR ),
            array( ':dt_atual',       '0000-00-00', PDO::PARAM_STR ),
            array( ':decir',          '',           PDO::PARAM_STR ),
            array( ':dtdecir',        '0000-00-00', PDO::PARAM_STR ),
            array( ':siape_registro', $_SESSION['sMatricula'], PDO::PARAM_STR ),
        ));

        $resp2 = $oDBase->affected_rows();

        mensagem("Dados do histórico gravados com sucesso!", null, 2);
    }
    else
    {

        mensagem("Servidor já ocupa função nesse período!", "manhistfunc.php?matricula=$matricula&numfuncao=$num_funcao&inicio=$dtini&fim=$dtfim&ocupacao=$sit2&num1=$Nnum1&num2=$Nnum2&num3=$Nnum3&num4=$Nnum4&publicacao=$publicacao&publicacao2=$publicacao2&respon=$respon&data1=$data1&data2=$data2&data3=$data3&data4=$data4", 2);
    }
}

/* ----------------------------------*\
  |                                    |
  \*---------------------------------- */
if ($modo == "5")
{
    $vDatas = date("Y-m-d");

    //Recebe  a opção da página anterior
    //Recebendo os dados a serem alterados
    $matricula  = getNovaMatriculaBySiape(anti_injection($_REQUEST['matricula']));
    $num_funcao = anti_injection($_REQUEST['numfunc']);
    $sit2        = anti_injection($_REQUEST['ocupacao']);
    $Nnum1      = anti_injection($_REQUEST['Nnum1']);
    $Nnum2      = anti_injection($_REQUEST['Nnum2']);
    $publicacao = anti_injection($_REQUEST['publicacao']);
    $respon     = anti_injection($_REQUEST['respon']);

    $inicio = (empty($_REQUEST['inicio']) ? '0000-00-00' : conv_data($_REQUEST['inicio']));
    $fim    = (empty($_REQUEST['fim']) ? '0000-00-00' : conv_data($_REQUEST['fim']));

    $Ndata1 = (empty($_REQUEST['Ndata1']) ? '0000-00-00' : conv_data($_REQUEST['Ndata1']));
    $Ndata2 = (empty($_REQUEST['Ndata2']) ? '0000-00-00' : conv_data($_REQUEST['Ndata2']));

    // atualiza tabela de ocupantes
    $sql = "UPDATE ocupantes set sit_ocup = '$sit2', resp_lot = '$respon', num_doc1 = '$Nnum1', num_doc2 = '$Nnum2', dt_doc1 = '$Ndata1', dt_doc2 = '$Ndata2', dt_inicio = '$inicio', dt_altera = '$vDatas',  cod_doc2 = '$publicacao' WHERE mat_siape = '$matricula' AND num_funcao = '$num_funcao' AND dt_fim = '0000-00-00' ";
    $oDBase->query($sql);

    // atualiza na tabela de historico
    $historic = "UPDATE historic set  sit_ocup = '$sit2', resp_lot = '$respon', num_doc1 = '$Nnum1', num_doc2 = '$Nnum2', dt_doc1 = '$Ndata1', dt_doc2 = '$Ndata2', dt_inicio = '$inicio', dt_altera = '$vDatas',  cod_doc2 = '$publicacao', siape_registro = '".$_SESSION['sMatricula']."', data_registro = NOW() WHERE mat_siape = '$matricula' AND num_funcao = '$num_funcao' AND dt_fim = '0000-00-00' ";
    $oDBase->query($historic);

    mensagem("Alteração gravada com sucesso!", null, 2);
}

/* -------------------------------------------------------------------------*\
  |                                                                           |
  |   MODO: 6                                                                 |
  |                                                                           |
  |   REGISTRA A EXONERAÇÃO/DESTITUIÇÃO DO OCUPANTE ATUAL DA FUNÇÃO           |
  |                                                                           |
  |  * Testa se os dados para a exoneração/destituição foram informados       |
  |  * BD ocupantes: Apaga o registro do ocupante                             |
  |  * BD historic : Altera os dados do histórico                             |
  |  * BD servativ : Atualiza registra que o servidor já não é chefe de setor |
  |  * BD usuarios : Altera as permissões, retirando a de chefia              |
  |                                                                           |
  |  **> Alterado em 22/12/2011                                               |
  |                                                                           |
  \*------------------------------------------------------------------------- */
if ($modo == "6")
{

    //Recebendo os dados a serem excluidos
    $matricula  = getNovaMatriculaBySiape(anti_injection($_REQUEST['matricula']));
    $num_funcao = anti_injection($_REQUEST['num_funcao']);
    $sit2        = anti_injection($_REQUEST['ocupacao']);
    $inicio     = (empty($_REQUEST['inicio']) ? '0000-00-00' : conv_data($_REQUEST['inicio']));

    $Nnum3      = anti_injection($_REQUEST['Nnum3']);
    $Ndata3     = (empty($_REQUEST['Ndata3']) ? '0000-00-00' : conv_data($_REQUEST['Ndata3']));
    $publicacao = anti_injection($_REQUEST['publicacao']);
    $Nnum4      = anti_injection($_REQUEST['Nnum4']);
    $Ndata4     = (empty($_REQUEST['Ndata4']) ? '0000-00-00' : conv_data($_REQUEST['Ndata4']));
    $fim        = (empty($_REQUEST['fim']) ? '0000-00-00' : conv_data($_REQUEST['fim']));

    // grava dados gravados em sessao
    $_SESSION['sGravaFuncaoSiape']     = $matricula;
    $_SESSION['sGravaFuncaoNumFuncao'] = $num_funcao;
    $_SESSION['sGravaFuncaoExclusao']  = $exclusao;
    $_SESSION['sGravaFuncaoOcupacao']  = $sit2;

    $_SESSION['sGravaFuncaoNnum3']      = $Nnum3;
    $_SESSION['sGravaFuncaoNdata3']     = $Ndata3;
    $_SESSION['sGravaFuncaopublicacao'] = $publicacao;
    $_SESSION['sGravaFuncaoNnum4']      = $Nnum4;
    $_SESSION['sGravaFuncaoNdata4']     = $Ndata4;
    $_SESSION['sGravaFuncaofim']        = $fim;

    // validacao dos campos
    $validacao = new valida();
    $validacao->setExibeMensagem(false);
    $validacao->setDestino($_SESSION['sHOrigem_3']);
    $validacao->setVoltar(0);

    // valida os dados enviados
    if (strlen(trim($Nnum3)) == 0)
    {
        $validacao->setMensagem("- O numero da portaria!\\n");
    }
    if (validaData($Ndata3) === false)
    {
        $validacao->setMensagem("- A data da portaria no formato dd/mm/aaaa!\\n");
    }
    if ($publicacao == '00')
    {
        $validacao->setMensagem("- Selecionar o meio de publicação!\\n");
    }
    if (strlen(trim($Nnum4)) == 0)
    {
        $validacao->setMensagem("- O número do meio de publicação!\\n");
    }
    if (validaData($Ndata4) === false)
    {
        $validacao->setMensagem("- A data da publicação no formato dd/mm/aaaa!\\n");
    }
    if (validaData($fim) === false)
    {
        $validacao->setMensagem("- A data de fim do exercício da função!\\n");
    }

    $validacao->exibeMensagem(); // exibe mensagem de erro se houver


    // apaga o registro do ocupante
    $oDBase->setDestino($_SESSION['sHOrigem_3']);
    $oDBase->query("
    DELETE FROM
        ocupantes
    WHERE
        mat_siape = :siape
        AND num_funcao = :num_funcao
        AND dt_fim = '0000-00-00'
        AND dt_inicio = :dt_inicio
    ",
    array(
        array( ':siape',      $matricula,  PDO::PARAM_STR ),
        array( ':num_funcao', $num_funcao, PDO::PARAM_STR ),
        array( ':dt_inicio',  $inicio,     PDO::PARAM_STR ),
    ));
    $nLinhasAfetadas += $oDBase->affected_rows();

    // altera os dados do histórico
    $oDBase->query("
    UPDATE
        historic
    SET
        cod_doc3       = :cod_doc3,
        num_doc3       = :num_doc3,
        dt_doc3        = :dt_doc3,
        cod_doc4       = :cod_doc4,
        num_doc4       = :num_doc4,
        dt_doc4        = :dt_doc4,
        dt_fim         = :dt_fim,
        siape_registro = :siape_registro,
        data_registro  = NOW()
    WHERE
        mat_siape = :siape
        AND num_funcao = :num_funcao
        AND dt_inicio = :dt_inicio
        AND sit_ocup = :sit_ocup
    ",
    array(
        array( ':siape',      $matricula,  PDO::PARAM_STR ),
        array( ':num_funcao', $num_funcao, PDO::PARAM_STR ),
        array( ':dt_inicio',  $inicio,     PDO::PARAM_STR ),
        array( ':sit_ocup',   $sit2,       PDO::PARAM_STR ),
        array( ':cod_doc3',   'PT',        PDO::PARAM_STR ),
        array( ':num_doc3',   $Nnum3,      PDO::PARAM_STR ),
        array( ':dt_doc3',    $Ndata3,     PDO::PARAM_STR ),
        array( ':cod_doc4',   $publicacao, PDO::PARAM_STR ),
        array( ':num_doc4',   $Nnum4,      PDO::PARAM_STR ),
        array( ':dt_doc4',    $Ndata4,     PDO::PARAM_STR ),
        array( ':dt_fim',     $fim,        PDO::PARAM_STR ),
        array( ':siape_registro', $_SESSION['sMatricula'], PDO::PARAM_STR ),
    ));
    $nLinhasAfetadas += $oDBase->affected_rows();


    // atualizando base servativ
    $oDBase->query("UPDATE servativ SET chefia = :chefia WHERE mat_siape = :siape ",
    array(
        array( ':siape',  $matricula,  PDO::PARAM_STR ),
        array( ':chefia', 'N',         PDO::PARAM_STR ),
    ));
    $nLinhasAfetadas += $oDBase->affected_rows();

    // altera as permissões do usuário
    $oDBase->query("UPDATE usuarios SET setor = :setor, acesso = :acesso WHERE siape = :siape ",
    array(
        array( ':siape',  $matricula,      PDO::PARAM_STR ),
        array( ':setor',  $lota,           PDO::PARAM_STR ),
        array( ':acesso', 'NNSNNNNNNNNNN', PDO::PARAM_STR ),
    ));
    $nLinhasAfetadas += $oDBase->affected_rows();

    if ($nLinhasAfetadas > 0)
    {
        // grava dados gravados em sessao
        unset($_SESSION['sGravaFuncaoSiape']);
        unset($_SESSION['sGravaFuncaoNumFuncao']);
        unset($_SESSION['sGravaFuncaoExclusao']);
        unset($_SESSION['sGravaFuncaoOcupacao']);

        unset($_SESSION['sGravaFuncaoNnum3']);
        unset($_SESSION['sGravaFuncaoNdata3']);
        unset($_SESSION['sGravaFuncaopublicacao']);
        unset($_SESSION['sGravaFuncaoNnum4']);
        unset($_SESSION['sGravaFuncaoNdata4']);
        unset($_SESSION['sGravaFuncaofim']);

        mensagem("Vacância gravada com sucesso!", $_SESSION['sHOrigem_1']);
    }
    else
    {
        mensagem("Falha no registro da Vacância!", $_SESSION['sHOrigem_3']);
    }
}

/* -------------------------------------------------------------------------*\
  |                                                                           |
  |   MODO: 7                                                                 |
  |                                                                           |
  |   REGISTRA A EFETIVAÇÃO DA SUBSTITUIÇÃO                                   |
  |                                                                           |
  |  * Testa se os dados para a efetivação já foram informados                |
  |  * BD substituicao : Verifica se o período já foi inserido                |
  |  *                   se não, inclui o registro                            |
  |  * BD servativ : Busca o email do servidor                                |
  |                                                                           |
  |  **> Alterado em 11/02/2012                                               |
  |      Incluida a desativação de períodos passados                          |
  |                                                                           |
  |                                                                           |
  \*------------------------------------------------------------------------- */
if ($modo == "7")
{
    //Recebe  a opção da página anterior
    //Recebendo os dados de substituição a serem incluidos
    $lot = $_SESSION['sLotacao'];

    $matricula  = getNovaMatriculaBySiape(anti_injection($_REQUEST['siape']));
    $lotserv    = anti_injection($_REQUEST['lotat']);
    $num_funcao = anti_injection($_REQUEST['num_funcao']);
    $sigla      = anti_injection($_REQUEST['sigla']);
    $ugpai      = anti_injection($_REQUEST['pai']);
    $Ndata1     = conv_data($_REQUEST['Ndata1']);
    $Ndata2     = conv_data($_REQUEST['Ndata2']);
    $motivo     = anti_injection($_REQUEST['motivo']);
    
    $data_hoje = date('Y-m-d');
    $nextdate  = somadiasadata((validaData($Ndata1) === false ? date('Y-m-d') : $Ndata1), 60); // Adiciona 60 dias

    $_SESSION['sMatriculaSubstitutoEfetivar'] = $matricula;
    //$pagina_de_origem .= "?matricula=$matricula&upag=" . $_SESSION['upag'];


    $mensagem = "";


    // validacao dos campos
    $validacao = new valida();
    $validacao->setExibeMensagem(false);
    $validacao->setDestino($pagina_de_origem);
    $validacao->setVoltar(0);

    // valida os dados enviados
    if (validaData($Ndata1) === false)
    {
        $mensagem .= ".Data de Início inválida!\\n"; //$validacao->setMensagem(".Data de Início inválida!\\n");
    }
    if (validaData($Ndata2) === false)
    {
        $mensagem .= ".Data Fim inválida!\\n"; //$validacao->setMensagem(".Data Fim inválida!\\n");
    }
    if ((trim($Ndata1) < $data_hoje))
    { /* $mensagem .= "O início do período é anterior a data de hoje!!\\n"; //$validacao->setMensagem( 'O início do período é anterior a data de hoje!!\\n' ); */
    }
    if ($Ndata1 > $Ndata2)
    {
        $mensagem .= ".Por favor informe Data de início menor que data fim!\\n"; //$validacao->setMensagem('.Por favor informe Data de início menor que data fim!\\n');
    }
    if ($motivo === '0')
    {
        $mensagem .= ".Por favor selecione o motivo!\\n"; //$validacao->setMensagem(".Por favor selecione o motivo!\\n");
    }
    if ($Ndata2 > $nextdate)
    {
        $mensagem .= ".O período de substituição não pode ser superior a 60 dias!\\n"; //$validacao->setMensagem(".O período de substituição não pode ser superior a 60 dias!\\n");
    }

    $validacao->exibeMensagem(); // exibe mensagem de erro se houver

    // instancia o banco de dados
    $oDBase->setDestino($pagina_de_origem);

    if ($mensagem == "")
    {
        //Implementar busca para saber se o periodo ja foi inserido
        $oDBase->setMensagem("Pesquisa de registro falhou");
        $oDBase->query("SELECT siape FROM substituicao WHERE inicio <= :inicio AND fim >= :fim AND siape = :siape AND numfunc = :numfunc ", array(
            array( ':inicio',  $Ndata2, PDO::PARAM_STR ),
            array( ':fim',     $Ndata1, PDO::PARAM_STR ),
            array( ':siape',   $matricula, PDO::PARAM_STR ),
            array( ':numfunc', $num_funcao, PDO::PARAM_STR ),
        ));
        $rows = $oDBase->num_rows();

        //Só insere o período se não tiver um lançado
        if ($rows > 0)
        {
            $mensagem = "Período (parte/total) já consta no banco de dados, verifique!";
        }
        else
        {
            $mensagem = "Falha no registro da Efetivação da Substituição!";
            $oDBase->setMensagem("Encerramento substituição(ões) anterior(es), falhou.");

            // desativa períodos anteriores
            $oDBase->query("UPDATE substituicao SET situacao='E' WHERE siape = :siape ", array(
                array( ':siape', $matricula, PDO::PARAM_STR ),
            ));

            $oDBase->setMensagem("Efetivação da substituição, falhou.");
    
            $oDBase->query("
            INSERT INTO substituicao
                (id, siape, numfunc, upai, sigla, inicio, fim, motivo, situacao, siape_registro, data_registro)
            VALUES
                (0, :siape, :numfunc, :upai, :sigla, :inicio, :fim, :motivo, :situacao, :siape_registro, NOW())
            ",
            array(
                array( ':siape',          $matricula,  PDO::PARAM_STR ),
                array( ':numfunc',        $num_funcao, PDO::PARAM_STR ),
                array( ':upai',           $ugpai,      PDO::PARAM_STR ),
                array( ':sigla',          $sigla,      PDO::PARAM_STR ),
                array( ':inicio',         $Ndata1,     PDO::PARAM_STR ),
                array( ':fim',            $Ndata2,     PDO::PARAM_STR ),
                array( ':motivo',         $motivo,     PDO::PARAM_STR ),
                array( ':situacao',       'A',         PDO::PARAM_STR ),
                array( ':siape_registro', $_SESSION['sMatricula'], PDO::PARAM_STR ),
            ));

            $nLinhasAfetadas = $oDBase->affected_rows();

            if ($nLinhasAfetadas > 0)
            {
                //obtendo email do substituto
                //
		        $oDBase->setMensagem("Tabela de servidores inexistente");
                $oDBase->query("SELECT email FROM servativ WHERE mat_siape = '$matricula' ");
                $emailsubs = $oDBase->fetch_object()->email;
                $ini       = databarra($Ndata1);
                $fim       = databarra($Ndata2);

                $count = enviarEmail($emailsubsl, 'INFORMA EFETIVA SUBSTITUIÇÃO', "<br><br><big>Senhor Substituto,<br>Informamos que foi lançado no SISREF período de efetiva substituição, ficando a seu cargo as providências de controle de frequência no período de $ini a $fim.<br> Atenciosamente,<BR> Equipe SISREF.</big><br><br>");

                $mensagem         = "Substituição gravada com sucesso!";
                $pagina_de_origem = "subsfuncinf.php";
            }
        }
    }

    if ($mensagem != "")
    {
        setMensagemUsuario( $mensagem, 'danger'); //mensagem($mensagem, $pagina_de_origem);
    }

    replaceLink($pagina_de_origem);
}

/* ----------------------------------*\
  |                                    |
  |                                    |
  |                                    |
  \*---------------------------------- */
if ($modo == "8")
{
    // voltar(1, $pagina_de_origem);
    // Recebe  a opção da página anterior
    // Recebendo os dados a serem excluidos

    $matricula = getNovaMatriculaBySiape(anti_injection($_REQUEST['matricula']));
    $sit2      = anti_injection($_REQUEST['sit']);

    // instancia o banco de dados
    $oDBase->setDestino($pagina_de_origem);

    //obtem dados da função
    $oDBase->query("SELECT * FROM ocupantes WHERE mat_siape='$matricula' AND sit_ocup='$sit2' AND dt_fim='0000-00-00' ");
    $rows = $oDBase->num_rows();

    if ($rows == 0)
    {
        $mensagem = "Servidor não ocupa função!";
    }
    else
    {
        $oRes1   = $oDBase->fetch_object();
        $numfunc = $oRes1->num_funcao;
        $inicio  = $oRes1->dt_inicio;
        $fim     = $oRes1->dt_fim;
        $resp    = $oRes1->resp_lot;

        //obtem lotaçao do servidor
        $oDBase->query("SELECT cod_lot_ant FROM servativ WHERE mat_siape='$matricula' ");
        $lota = $oDBase->fetch_object()->cod_lot_ant;

        //excluindo servidor de ocupantes
        $oDBase->query("DELETE FROM ocupantes WHERE mat_siape='$matricula' AND sit_ocup='$sit2' ");
        $nLinhasAfetadas = $oDBase->affected_rows();
        if ($nLinhasAfetadas == 0)
        {
            $mensagem = "Falha na Exclusão do ocupante de função!";
        }
        else
        {
            // altera permissão do usuário
            $oDBase->setMensagem("Erro: na atualização de usuários!");
            $oDBase->query("UPDATE usuarios SET setor='$lota', acesso='" . (substr($lota, 5, 1) == 7 || substr($lota, 2, 1) == 7 ? "SNN" : "NNS") . "NNNNNNNNNN' WHERE siape='$matricula' ");
            $nLinhasAfetadas = $oDBase->affected_rows();
            if ($nLinhasAfetadas > 0)
            {
                $mensagem = "Exclusão gravada com sucesso!";

                // excluindo dados de historico
                $oDBase->query("DELETE FROM historic WHERE num_funcao='$numfunc' AND mat_siape='$matricula' AND sit_ocup='$sit2' AND dt_inicio='$inicio' AND dt_fim='$fim' ");
                $nLinhasAfetadas = $oDBase->affected_rows();
                if ($nLinhasAfetadas > 0)
                {
                    // atualizando base servativ
                    $oDBase->setMensagem("Erro: na atualização do cadastro!");
                    $oDBase->query("UPDATE servativ SET chefia='N', cod_lot='$lota' WHERE mat_siape='$matricula' ");
                    $nLinhasAfetadas = $oDBase->affected_rows();
                    if ($nLinhasAfetadas > 0)
                    {
                        // atualizando historico de lotacao
                        $oDBase->setMensagem("Erro: na atualização de histlot!");
                        $oDBase->query("DELETE FROM histlot WHERE siape = '$matricula' AND dt_ing_lot='$inicio' AND dt_sai_lot='$fim' ");
                    }
                }
            }
        }
    }

    if ($mensagem != "")
    {
        mensagem($mensagem, $pagina_de_origem);
    }
    else
    {
        voltar(1, $pagina_de_origem);
    }
}

/* ------------------------------------------*\
  | Modo: 9 - Delegação de atribuição          |
  | Modo: 10 - Cancela delegação de atribuição |
  \*------------------------------------------ */
if ($modo == "9" || $modo == "10")
{

    // Recebe os dados da página anterior
    $matricula     = getNovaMatriculaBySiape(anti_injection($_REQUEST['siape']));
    $portaria      = anti_injection($_REQUEST['portaria']);
    $datapt        = $_REQUEST['datapt'];
    $data_portaria = conv_data($datapt);
    $lota          = $_SESSION['sLotacao'];

    // validacao dos campos
    $validacao = new valida();
    $validacao->setExibeMensagem(false);
    $validacao->setDestino($pagina_de_origem);
    $validacao->setVoltar(0);

    if (strlen(alltrim($portaria)) == 0)
    {
        $validacao->setMensagem(".O numero da portaria!\\n");
    }
    if (strlen(alltrim($datapt)) < 10)
    {
        $validacao->setMensagem(".A data da portaria no formato dd/mm/aaaa!\\n");
    }
    if (validaData($datapt) == false)
    {
        $validacao->setMensagem(".Data inválida!\\n");
    }

    $validacao->exibeMensagem(); // exibe mensagem de erro se houver

    // permissoes - acesso
    $oDBase->query("SELECT acesso FROM usuarios WHERE siape='$matricula' ");
    $oAcesso = $oDBase->fetch_object();
    $sAcesso = $oAcesso->acesso;

    // tipo da ação - delegação
    $aMensagem = array();
    switch ($modo)
    {
        case '9':
            // Modo: 9 - Delegação de atribuição
            $sAcesso[1]   = 'S';
            $sql          = "UPDATE usuarios SET acesso = '" . $sAcesso . "', magico='2', portaria='$portaria', datapt='$data_portaria', ptfim='', dtfim='0000-00-00' WHERE siape='$matricula' ";
            $aMensagem[0] = "Delegação efetuada com sucesso!";
            $aMensagem[1] = "Falha na Delegação!";
            break;
        case '10':
        // Modo: 10 - Cancela delegação de atribuição
        default:
            // Cancela delegação de atribuição
            $sAcesso[1]   = 'N';
            // verifica se o servidor ocupa função para
            // não alterar as permissões de chefia
            $oDBase->query("SELECT a.mat_siape, a.sit_ocup, IFNULL(b.situacao,'') AS situacao FROM ocupantes AS a LEFT JOIN substituicao AS b ON a.mat_siape=b.siape AND b.situacao='A' WHERE a.mat_siape='$matricula' AND (a.sit_ocup='T' OR (a.sit_ocup='S' AND IFNULL(b.situacao,'')='A')) AND a.dt_fim='0000-00-00' ");
            $nRows        = $oDBase->num_rows();
            if ($nRows > 0)
            {
                $sql = "UPDATE usuarios SET magico='0', ptfim='$portaria', dtfim='$data_portaria' WHERE siape='$matricula' ";
            }
            else
            {
                $sql = "UPDATE usuarios SET acesso = '" . $sAcesso . "', magico='0', ptfim='$portaria', dtfim='$data_portaria' WHERE siape='$matricula' ";
            }
            $aMensagem[0] = "Cancelamento de Delegação efetuado com sucesso!";
            $aMensagem[1] = "Falha no Cancelamento da Delegação!";
            break;
    }

    // instancia o banco de dados
    $oDBase->setDestino($pagina_de_origem);
    $oDBase->setMensagem("Erro: na atualização do usuario!");
    $oDBase->query($sql);

    if ($oDBase->affected_rows() > 0)
    {
        $nMsg             = 0;
        $pagina_de_origem = $_SESSION['sHOrigem_1'];
    }
    else
    {
        $nMsg = 1;
    }

    if (count($aMensagem) > 0)
    {
        mensagem($aMensagem[$nMsg], $pagina_de_origem);
    }
    else
    {
        voltar(1, $pagina_de_origem);
    }
}

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
