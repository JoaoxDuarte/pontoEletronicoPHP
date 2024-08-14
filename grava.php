<?php

include_once( "config.php" );

// dados pessoais
$modo = anti_injection($_REQUEST['modo']);

$sMesi = $_SESSION['sMesi'];
$sMesf = $_SESSION['sMesf'];

// instancia o BD
$oDBase = new DataBase('PDO');


/* -------------------------------------------------------*\
  |                                                         |
  |     MODO 5                                              |
  |     - grava prazos, datas -                             |
  |                                                         |
  \*------------------------------------------------------- */
if ($modo == "5")
{
    $comp1 = anti_injection($_REQUEST['comp1']);
    $comp2 = anti_injection($_REQUEST['comp2']);

    $mesi    = substr($comp1, 0, 2) . substr($comp1, 3, 4); // competencia referente as datas/períodos
    $mesf    = substr($comp2, 0, 2) . substr($comp2, 3, 4);
    $rhi     = conv_data($_REQUEST['rhi']); // recursos humanos - verificacao - inicio
    $rhf     = conv_data($_REQUEST['rhf']); // recursos humanos - verificacao - fim
    $apsi    = conv_data($_REQUEST['apsi']); // homologacao - chefias - inicio
    $apsf    = conv_data($_REQUEST['apsf']); // homologacao - chefias - fim
    $gbnini  = conv_data($_REQUEST['gbnini']);
    $gbninf  = conv_data($_REQUEST['gbninf']);
    $outchei = conv_data($_REQUEST['outchei']);
    $outchef = conv_data($_REQUEST['outchef']);
    $rmi     = conv_data($_REQUEST['rmi']);
    $rmf     = conv_data($_REQUEST['rmf']);
    $cadi    = conv_data($_REQUEST['cadi']);
    $cadf    = conv_data($_REQUEST['cadf']);
    $id      = anti_injection($_REQUEST['id']);

    // atualiza as datas/períodos
    $oDBase->setMensagem("Erro:");
    $oDBase->query("
    UPDATE tabvalida 
        SET rhi  = :rhi, 
            rhf  = :rhf, 
            apsi = :apsi, 
            apsf = :apsf 
                WHERE id = :id
    ",
    array(
        array( ':rhi',  $rhi,  PDO::PARAM_STR ),
        array( ':rhf',  $rhf,  PDO::PARAM_STR ),
        array( ':apsi', $apsi, PDO::PARAM_STR ),
        array( ':apsf', $apsf, PDO::PARAM_STR ),
        array( ':id',   $id,   PDO::PARAM_STR ),
    ));
    

    if ($oDBase->affected_rows() > 0)
    {
        registraLog("O usuário $sMatricula alterou a tabela de validação para o meses $mesi a $mesf "); // grava o LOG
        retornaInformacao("Alteração realizada com sucesso!", 'danger');
    }
    else
    {
        retornaInformacao("Não houve sucesso na Alteração dos dados!", 'danger');
    }
}

/* -------------------------------------------------------*\
  |                                                         |
  |     MODO 6                                              |
  |     - reinicia a senha para a data de nascimento        |
  |                                                         |
  \*------------------------------------------------------- */
elseif ($modo == "6")
{
    $lSiape = anti_injection($_REQUEST['lSiape']);

    $oDBase->query("UPDATE usuarios SET senha='e10adc3949ba59' WHERE siape='$lSiape' ");

    // grava o LOG
    registraLog("O usuário $sMatricula reiniciou a senha do servidor Siape $lSiape "); // grava o LOG
    // fim do LOG

    mensagem("Senha reiniciada com sucesso!", null, 1);
}

/* -------------------------------------------------------*\
  |                                                         |
  |     MODO 10                                             |
  |     - tabela de lotação                                 |
  |                                                         |
  \*------------------------------------------------------- */
elseif ($modo == "10")
{
    mensagem("Falta concluir o módulo de gravacao de inclusão de TABLOT");
}

/* -------------------------------------------------------*\
  |                                                         |
  |     MODO 11                                             |
  |     - inclusão de código na tabela TABOCFRE             |
  |                                                         |
  \*------------------------------------------------------- */
elseif ($modo == "11")
{
    $siapecad = anti_injection($_REQUEST['siapecad']);
    $desc     = anti_injection($_REQUEST['desc']);
    $sirh     = anti_injection($_REQUEST['sirh']);
    $siape    = anti_injection($_REQUEST['siape']);
    $resp     = anti_injection($_REQUEST['resp']);
    $aplic    = anti_injection($_REQUEST['aplic']);
    $implic   = anti_injection($_REQUEST['implic']);
    $prazo    = anti_injection($_REQUEST['prazo']);
    $flegal   = anti_injection($_REQUEST['flegal']);

    //testa se o codigo ja existe
    $oDBase->query("SELECT * FROM tabocfre WHERE siapecad='$siapecad' AND ativo='S' ");

    if ($oDBase->num_rows() == 0)
    {
        $oDBase->query("INSERT INTO tabocfre (siapecad, desc_ocorr, cod_ocorr, cod_siape, resp, aplic, implic, prazo, flegal, ativo) VALUES ('$siapecad', '$desc', '$sirh', '$siape', '$resp', '$aplic', '$implic', '$prazo', '$flegal', 'S') ");

        registraLog("O usuário $sMatricula incluiu o código de ocorrencia $siapecad "); // grava o LOG

        mensagem("Código incluído com sucesso!", "tabocfrei.php");
    }
    else
    {
        mensagem("Codigo de ocorrência já existe!", "tabocfrei.php");
    }
}

/* -------------------------------------------------------*\
  |                                                         |
  |     MODO 12                                             |
  |     - alteração de código na tabela TABOCFRE            |
  |                                                         |
  \*------------------------------------------------------- */
elseif ($modo == "12")
{
    $siapecad   = anti_injection($_REQUEST['siapecad']);
    $sDescricao = anti_injection($_REQUEST['sDescricao']);
    $sirh       = anti_injection($_REQUEST['sirh']);
    $siape      = anti_injection($_REQUEST['siape']);
    $resp       = anti_injection($_REQUEST['resp']);
    $aplic      = anti_injection($_REQUEST['aplic']);
    $implic     = anti_injection($_REQUEST['implic']);
    $prazo      = anti_injection($_REQUEST['prazo']);
    $flegal     = anti_injection($_REQUEST['flegal']);
    $sAtivo     = anti_injection($_REQUEST['sAtivo']);

    $oDBase->query("UPDATE tabocfre SET desc_ocorr='$sDescricao', cod_ocorr='$sirh', cod_siape='$siape', resp='$resp', aplic='$aplic', implic='$implic', prazo='$prazo', flegal='$flegal', ativo='$sAtivo' WHERE siapecad='$siapecad' ");

    registraLog("O usuário $sMatricula alterou o código de ocorrencia $siapecad "); // grava o LOG

    mensagem("Código alterado com sucesso!", "tabocfre.php?modo=&var2=$siapecad&var1=siapecad");
}
