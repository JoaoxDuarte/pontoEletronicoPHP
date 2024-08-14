<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH');

$destino = 'cadastro_alteracao.php';

// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();
$destino_erro = $sessao_navegacao->getPaginaAnterior();


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setSubTitulo("Alteração de Dados de Servidores ou Estagiários");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// dados passados por formulário
$tSiape      = soNumeros(anti_injection($_POST['tSiape']));

$wnome       = trata_aspas(trim(anti_injection($_POST['wnome'])));
$wnome_social       = trata_aspas(trim(anti_injection($_POST['wnome_social'])));
$Siapecad    = soNumeros(anti_injection($_POST['Siapecad']));
$idunica     = soNumeros(anti_injection($_POST['idunica']));
$Situacao    = (trim($_POST['Situacao']) == '' ? '00' : anti_injection($_POST['Situacao']));
$wcargo      = anti_injection($_POST['wcargo']);
$nivel       = strtoupper(trata_aspas(trim(anti_injection($_POST['nivel']))));
$Regjur      = anti_injection($_POST['Regjur']);

$wdatinss    = (empty(trim($_POST['wdatinss'])) ? "00/00/0000" : $_POST['wdatinss']);
$wdatinss    = conv_data($wdatinss);

$Jornada     = soNumeros(anti_injection($_POST['Jornada']));

$datjorn     = (empty(trim($_POST['datjorn'])) ? "00/00/0000" : $_POST['datjorn']);
$datjorn     = conv_data($datjorn);

$defvis      = anti_injection($_POST['defvis']);
$pis         = soNumeros(anti_injection($_POST['pis']));
$cpf         = soNumeros(anti_injection($_POST['cpf']));

$dtnasc      = (empty(trim($_POST['dtnasc'])) ? "00/00/0000" : $_POST['dtnasc']);
$dtnasc      = conv_data($dtnasc);

$wlota       = anti_injection($_POST['wlota']);

$datlot      = (empty(trim($_POST['datlot'])) ? "00/00/0000" : $_POST['datlot']);
$datlot      = conv_data($datlot);

$loca        = anti_injection($_POST['loca']);
$loca        = ($loca == '00000000' ? $wlota : $loca);

$datloca     = (empty(trim($_POST['datloca'])) || $_POST['datloca'] == '00/00/0000' ? $datlot : $_POST['datloca']);
$datloca     = conv_data($datloca);

$email       = trata_aspas(trim(anti_injection($_POST['email'])));

$nom         = trata_aspas(strtoupper(trim($wnome)));
$nom_social         = trata_aspas(strtoupper(trim($wnome_social)));

$horae       = anti_injection($_POST['horae']);
$processo    = trata_aspas(trim($_POST['processo']));
$mothe       = anti_injection($_POST['mothe']);

$dthe        = (empty(trim($_POST['dthe'])) ? '00/00/0000' : $_POST['dthe']);
$dthe        = conv_data($dthe);

$dthefim     = (empty(trim($_POST['dthefim'])) ? '00/00/0000' : $_POST['dthefim']);
$dthefim     = conv_data($dthefim);

$upg         = soNumeros(anti_injection($_POST['upg']));

$ip          = getIpReal(); //linha que captura o ip do usuario.
$sMatricula  = $_SESSION["sMatricula"]; //matrícula do usuario.
$permitehoras = anti_injection($_POST['limite-horas']);
$plantaoMedico = anti_injection($_POST['plantao-medico']);

$datdiplsp2  = '0000-00-00';
$datcarreira = '0000-00-00';
$datdiplsp3  = '0000-00-00';

$jornada_real = soNumeros(anti_injection($_POST['jornada_real']));
// grava em sessão para uso no retorno à página se houver erro
include_once( "cadastro_sessao_grava.php" );


## ############################### ##
##                                 ##
##            VALIDAÇÃO            ##
##                                 ##
## ############################### ##

// grupos situacao cadastral
$cargos_niveis = array(
    'NA', /* Nível Auxiliar      */
    'NI', /* Nível Intermediário */
    'NM', /* Nível Médio         */
    'NS'  /* Nível Superior      */
);

$situacao_sem_cargo = array(
    '03', /* REQUISITADO          */
    '04', /* NOMEADO CARGO COMIS. */
    '05', /* SEM VINCULO          */
    '06', /* TABELISTA(ESP/EMERG) */
    '07', /* NATUREZA ESPECIAL    */
    '12', /* CONTRATO TEMPORARIO  */
    '14', /* REQ.DE OUTROS ORGAOS */
    '16', /* REQ. MILITAR P/PR    */
    '17', /* APOS. TCU 733/94     */
    /*'18', EXERC DESCENT CARREI */
    /*'19', LOTACAO PROVISORIA   */
    '46', /* ????                 */
    '66'  /* ESTAGIÁRIO           */
);

// mensagem
$mensagem = "";

// class valida
$validar = new valida();
$validar->setDestino( $destino_erro );
$validar->setExibeMensagem( false );

$tSiape = getNovaMatriculaBySiape($tSiape);


## NOME DO SERVIDOR / ESTAGIÁRIO
if (strlen(trim($wnome)) == 0)
{
    $validar->setMensagem('- O NOME é obrigatório!\\n');
}

## MATRÍCULA SIAPE
$validar->siape( $tSiape );


## MATRÍCULA SIAPECAD
if (strlen(trim($Siapecad)) != 8 || $Siapecad == '00000000')
{
    $validar->setMensagem("- É obrigatório informar o SIAPECAD com 8 números!\\n");
}

## IDENTIFICAÇÃO ÚNICA
if (strlen(trim($idunica)) != 9 || $idunica == '000000000')
{
    $validar->setMensagem("- É obrigatório informar a identificação única com 9 números!\\n");
}

## SITUAÇÃO FUNCIONAL
if ($Situacao == '00')
{
    $validar->setMensagem("- A situação funcional deve ser informada!\\n");
}

## EMAIL
if (!filter_var($email, FILTER_VALIDATE_EMAIL))
{
    $validar->setMensagem("- O E-mail não é válido!\\n");
}

## CARGO EFTIVO
if (!in_array($Situacao,$situacao_sem_cargo) && $wcargo == '000000')
{
    $validar->setMensagem("- O Cargo é obrigatório!\\n");
}
else if (in_array($Situacao,$situacao_sem_cargo) && $wcargo != '000000')
{
    $validar->setMensagem("- Situação cadastral do servidor impede alteração de cargo!\\n");
}

## NÍVEL (Para servidor preencher: NA, NI, NM ou NS, para estagiário NM ou NS)
if (!empty(trim($nivel)))
{
    if ($Situacao == '66' && (trim($nivel) != 'NS' && trim($nivel) != 'NM'))
    {
        $validar->setMensagem("- O nível deve ser informado (Estagiário: NS/NM)!\\n");
    }
    else if (!in_array($Situacao,$situacao_sem_cargo) && !in_array($nivel,$cargos_niveis))
    {
        $validar->setMensagem("- O nível deve ser informado (Servidor: NS/NM/NI/NA)!\\n");
    }
}

## REGIME JURÍDICO
if ($Regjur == '0')
{
    $validar->setMensagem("- O regime jurídico é obrigatório!\\n");
}

## DATA DE INGRESSO NO ÓRGÃO
$validar->data( $wdatinss, "A data de ingresso no Órgão deve ser informada!" );

## JORNADA
if (strlen(trim($Jornada)) != 2 || $Jornada == '00')
{
    $validar->setMensagem("- A jornada é obrigatório!\\n");
}


//## JORNADA
//if (strlen(trim($Jornada)) == 2 || $Jornada != '00')
//{
//    if($jornada_real != $Jornada){
//       // var_dump($mothe);die;
//        $sErro   = '';
//        if($dthe=='00/00/0000'){
//            $sErro   .= "- Data de início é obrigatório!\\n";
//            //$validar->setMensagem("- Data de início é obrigatório!\\n");
//        }
//        if($mothe=='00' || $mothe==''){
//            $sErro   .="- Motivo é obrigatório!\\n";
//            //$validar->setMensagem("- Motivo é obrigatório!\\n");
//        }
//    }
//    if ($sErro != '')
//    {
//        $sErro = str_ireplace(',', ' e ', $sErro);
//        // $validar->setMensagem("Horário Especial - Falta informar:\\n" . $sErro . "");
//        $validar->setMensagem($sErro);
//    }
//
//
//}

## DATA DE INGRESSO NA JORNADA
$validar->data( $datjorn, "A data de início da jornada é obrigatório!" );

## PIS-PASEP
$pis_invalido = array(
    '00000000000', '11111111111', '22222222222', '33333333333', '22222222222',
    '55555555555', '66666666666', '77777777777', '88888888888', '99999999999'
);
if (strlen(trim($pis)) != 11 || in_array($pis, $pis_invalido))
{
    $validar->setMensagem("- O PIS não é válido!\\n");
}

## CPF
if (validaCPF($cpf) == false)
{
    $validar->setMensagem("- O CPF não é válido!\\n");
}

## DATA DE NASCIMENTO
$validar->data( $dtnasc, "A data de nascimento é obrigatório!" );

## LOTAÇÃO
/*
if ($wlota == '00000000')
{
    $validar->setMensagem("- A LOTAÇÃO é obrigatória!\\n");
}

## DATA DE INGRESSO NA LOTAÇÃO
$validar->data( $datlot, "A data de ingresso na lotação deve ser informada!" );
*/

## HORÁRIO ESPECIAL
$horae = ($horae == '0' ? 'N' : $horae);

if ($horae == 'S' || (strlen(trim($processo)) > 0 && intval($processo,10) != 0)
   || $mothe != '00' || intval($dthe,10) != 0 || $jornada_real != $Jornada)
{

    $arrayMotivo = explode(':', $mothe);

    // - Se um dos campos de horário especial for preenchido,
    //   outros serão obrigatórios, exceto data de fim;
    // - Se o motivo for Amamentação (A) ou Estudante (E),
    //   a data de fim é obrigatória
    $sErro   = '';
    $sErro  .= (strlen(trim($processo)) == 0 ? ' - Processo é obrigatório!\\n' : '');

    $sErro  .= (validaData($dthe) == false ? ' - Data de início é obrigatório!\\n' : '');
    //$sErro  .= (($mothe == 'E' || $mothe == 'A') && validaData($dthefim) == false ? ' - Data de fim' : '');
    if($jornada_real != $Jornada){
        if (strlen(trim($Jornada)) == 2 || $Jornada != '00')
        {
            if($dthe=='00/00/0000'){
                $sErro   .= "- Data de início é obrigatório!\\n";
                //$validar->setMensagem("- Data de início é obrigatório!\\n");
            }
            if($mothe=='00' || $mothe==''){
                $sErro   .="- Motivo é obrigatório!\\n";
                //$validar->setMensagem("- Motivo é obrigatório!\\n");
            }
        }
    }
    else{
        $sErro  .= ($mothe == '00' ? ' - Motivo é obrigatório!\\n' : '');
    }





    if ($sErro != '')
    {
        $sErro = str_ireplace(',', ' e ', $sErro);
       // $validar->setMensagem("Horário Especial - Falta informar:\\n" . $sErro . "");
        $validar->setMensagem("" . $sErro . "");
    }
    else if (strlen($processo) > 0 && $mothe != '00' && (validaData($dthe) == false))
    {
        $horae = 'S';
    }
}
else
{
    $processo = '';
    $mothe   = '';
    $dthe     = '0000-00-00';
    $dthefim  = '0000-00-00';
}

// Exibe mensagem(ns) de erro, se houver
$validar->exibeMensagem();


## ############################### ##
##                                 ##
##            GRAVAÇÃO             ##
##                                 ##
## ############################### ##

// instancia banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setMensagem("Erro no acesso ao CADASTRO");
$oDBase->setDestino( $destino_erro );

//Rotina para guardar dados a serem alterados no histórico
$oDBase->query("
SELECT
    defvis, jornada, dt_ing_jorn, entra_trab, sai_trab, ini_interv,
    sai_interv, horae, processo, motivo, dthe, dthefim, autchef, bhoras,
    cod_lot
FROM
    servativ
WHERE
    mat_siape = :siape
",
array(
    array( ':siape', $tSiape, PDO::PARAM_STR ),
));

if ($oDBase->num_rows() == 0)
{
    mensagem("Matricula não cadastrada na base!", $destino, 1);
}
else
{
    $oServidor = $oDBase->fetch_object();
    $hdefvis   = $oServidor->defvis;
    $hjornada  = $oServidor->jornada;
    $hentra    = $oServidor->entra_trab;
    $hsai      = $oServidor->sai_trab;
    $hintini   = $oServidor->ini_interv;
    $hintsai   = $oServidor->sai_interv;
    $hhorae    = $oServidor->horae;
    $hprocesso = $oServidor->processo;
    $hmotivo   = $oServidor->motivo;
    $hdthe     = $oServidor->dthe;
    $hdthefim  = $oServidor->dthefim;
    $hautchef  = $oServidor->autchef;
    $hbhoras   = $oServidor->bhoras;

    $lotacao                  = $oServidor->cod_lot;
    $data_ingresso_na_jornada = $oServidor->dt_ing_jorn;

    // data e hora atual para gravação no historico do cadastro
    $vHoras = strftime("%H:%M:%S", time());
    $vDatas = date("Y-m-d");

    // grava historico
    $oDBase->setMensagem("Erro na gravação do histórico!");
    $oDBase->query("
    INSERT INTO histcad
    SET
        mat_siape  = :mat_siape,
        defvis     = :defvis,
        jornada    = :jornada,
        entra_trab = :entra_trab,
        sai_trab   = :sai_trab,
        ini_interv = :ini_interv,
        sai_interv = :sai_interv,
        horae      = :horae,
        processo   = :processo,
        motivo     = :motivo,
        dthe       = :dthe,
        dthefim    = :dthefim,
        autchef    = :autchef,
        bhoras     = :bhoras,
        dataalt    = :dataalt,
        horaalt    = :horaalt,
        siapealt   = :siapealt,
        ipalt      = :ipalt
    ",
    array(
        array(':mat_siape',  $tSiape,     PDO::PARAM_STR ),
        array(':defvis',     $hdefvis,    PDO::PARAM_STR ),
        array(':jornada',    $hjornada,   PDO::PARAM_STR ),
        array(':entra_trab', $hentra,     PDO::PARAM_STR ),
        array(':sai_trab',   $hsai,       PDO::PARAM_STR ),
        array(':ini_interv', $hintini,    PDO::PARAM_STR ),
        array(':sai_interv', $hintsai,    PDO::PARAM_STR ),
        array(':horae',      $hhorae,     PDO::PARAM_STR ),
        array(':processo',   $hprocesso,  PDO::PARAM_STR ),
        array(':motivo',     $hmotivo,    PDO::PARAM_STR ),
        array(':dthe',       $hdthe,      PDO::PARAM_STR ),
        array(':dthefim',    $hdthefim,   PDO::PARAM_STR ),
        array(':autchef',    $hautchef,   PDO::PARAM_STR ),
        array(':bhoras',     $hbhoras,    PDO::PARAM_STR ),
        array(':dataalt',    $vDatas,     PDO::PARAM_STR ),
        array(':horaalt',    $vHoras,     PDO::PARAM_STR ),
        array(':siapealt',   $sMatricula, PDO::PARAM_STR ),
        array(':ipalt',      $ip,         PDO::PARAM_STR ),
    ));


    // grava as alterações no banco
    $oDBase->setMensagem("Erro na gravação da alteração no Cadastro!");
    $oDBase->query("
    UPDATE
        servativ
    SET
        nome_serv    = :nome_serv,
        nome_social    = :nome_social,
        email        = :email,
        mat_siapecad = :mat_siapecad,
        ident_unica  = :ident_unica,
        dt_nasc      = :dt_nasc,
        defvis       = :defvis,
        cpf          = :cpf,
        pis_pasep    = :pis_pasep,
        cod_sitcad   = :cod_sitcad,
        reg_jur_at   = :reg_jur_at,
        f_ing_org    = :f_ing_org,
        dt_adm       = :dt_adm,
        dipl_inss    = :dipl_inss,
        n_sip_inss   = :n_sip_inss,
        dt_dip_ins   = :dt_dip_ins,
        cod_cargo    = :cod_cargo,
        dt_ing_car   = :dt_ing_car,
        f_ing_car    = :f_ing_car,
        dipl_car     = :dipl_car,
        n_sip_car    = :n_sip_car,
        dt_dip_car   = :dt_dip_car,
        nivel        = :nivel,
        cod_classe   = :cod_classe,
        cod_padrao   = :cod_padrao,
        jornada      = :jornada,
        dt_ing_jorn  = :dt_ing_jorn,
        horae        = :horae,
        processo     = :processo,
        motivo       = :motivo,
        dthe         = :dthe,
        dthefim      = :dthefim,
        limite_horas = :limite_horas,
        plantao_medico = :plantao_medico
    WHERE
        mat_siape = :siape
    ",
    array(
        array(':siape',        $tSiape,         PDO::PARAM_STR ),
        array(':nome_serv',    $nom,            PDO::PARAM_STR ),
        array(':nome_social',  $nom_social,   PDO::PARAM_STR ),
        array(':email',        $email,          PDO::PARAM_STR ),
        array(':mat_siapecad', $Siapecad,       PDO::PARAM_STR ),
        array(':ident_unica',  $idunica,        PDO::PARAM_STR ),
        array(':dt_nasc',      $dtnasc,         PDO::PARAM_STR ),
        array(':defvis',       $defvis,         PDO::PARAM_STR ),
        array(':cpf',          $cpf,            PDO::PARAM_STR ),
        array(':pis_pasep',    $pis,            PDO::PARAM_STR ),
        array(':cod_sitcad',   $Situacao,       PDO::PARAM_STR ),
        array(':reg_jur_at',   $Regjur,         PDO::PARAM_STR ),
        array(':f_ing_org',    $fingorg,        PDO::PARAM_STR ),
        array(':dt_adm',       $wdatinss,       PDO::PARAM_STR ),
        array(':dipl_inss',    $diplinss,       PDO::PARAM_STR ),
        array(':n_sip_inss',   $ndiplsp2,       PDO::PARAM_STR ),
        array(':dt_dip_ins',   $datdiplsp2,     PDO::PARAM_STR ),
        array(':cod_cargo',    $wcargo,         PDO::PARAM_STR ),
        array(':dt_ing_car',   $datcarreira,    PDO::PARAM_STR ),
        array(':f_ing_car',    $formicr,        PDO::PARAM_STR ),
        array(':dipl_car',     $diplcr,         PDO::PARAM_STR ),
        array(':n_sip_car',    $ndiplsp3,       PDO::PARAM_STR ),
        array(':dt_dip_car',   $datdiplsp3,     PDO::PARAM_STR ),
        array(':nivel',        $nivel,          PDO::PARAM_STR ),
        array(':cod_classe',   $classe,         PDO::PARAM_STR ),
        array(':cod_padrao',   $padrao,         PDO::PARAM_STR ),
        array(':jornada',      $Jornada,        PDO::PARAM_STR ),
        array(':dt_ing_jorn',  $datjorn,        PDO::PARAM_STR ),
        array(':horae',        $horae,          PDO::PARAM_STR ),
        array(':processo',     $processo,       PDO::PARAM_STR ),
        array(':motivo',       $mothe,          PDO::PARAM_STR ),
        array(':dthe',         $dthe,           PDO::PARAM_STR ),
        array(':dthefim',      $dthefim,        PDO::PARAM_STR ),
        array(':limite_horas', $permitehoras,   PDO::PARAM_STR ),
        array(':plantao_medico', $plantaoMedico,   PDO::PARAM_STR ),
    ));


    /* Atualiza usuario */
    $oDBase->setMensagem("Erro no acesso ao cadastro de Usuário!");
    $oDBase->query("SELECT siape FROM usuarios WHERE siape = :siape", array(
        array(':siape', $tSiape, PDO::PARAM_STR),
    ));

    if ($oDBase->num_rows() == 0)
    {
        /* lendo dados do setor */
        $oDBase->query("SELECT upag FROM tabsetor WHERE codigo = :codigo", array(
            array(':codigo', $wlota, PDO::PARAM_STR),
        ));
        $oSetor = $oDBase->fetch_object();
        $upag   = $oSetor->upag;

        /* gravando dados de usuario  */
        $dn     = dataDia($dtnasc) . dataMes($dtnasc) . dataAno($dtnasc);
        $ssenha = substr(md5($dn), 0, 14);

        $oDBase->query("
        INSERT INTO usuarios
        SET
            siape  = :siape,
            nome   = :nome,
            acesso = :acesso,
            setor  = :setor,
            senha  = :senha,
            prazo  = :prazo,
            upag   = :upag,
            defvis = :defvis
        ",
        array(
            array(':siape',  $tSiape,         PDO::PARAM_STR),
            array(':nome',   $nom,            PDO::PARAM_STR),
            array(':acesso', 'NNSNNNNNNNNNN', PDO::PARAM_STR),
            array(':setor',  $wlota,          PDO::PARAM_STR),
            array(':senha',  $ssenha,         PDO::PARAM_STR),
            array(':prazo',  '1',             PDO::PARAM_STR),
            array(':upag',   $upag,           PDO::PARAM_STR),
            array(':defvis', $defvis,         PDO::PARAM_STR),
        ));
    }
    else
    {
        $oDBase->query("
        UPDATE usuarios
        SET
            nome   = :nome,
            defvis = :defvis
        WHERE
            siape = :siape
        ",
        array(
            array( ':nome',   $nom,  PDO::PARAM_STR ),
            array( ':defvis', $defvis, PDO::PARAM_STR ),
            array( ':siape',  $tSiape,  PDO::PARAM_STR ),
        ));
    }

    // registra no historico de jornada
    // se houver alteração da jornada
    // do servidor ou estagiário
    if ($Jornada != $hjornada || $datjorn != $data_ingresso_na_jornada)
    {
        // le jornada historico
        $oJornada = new DefinirJornada();
        $oJornada->setSiape( $tSiape );
        $oJornada->leDadosServidor();
        $oJornada->leSupervisao( $datjorn );
        $oJornada->processo_hespecial = $processo;
        $oJornada->motivo_hespecial   = $mothe;

        // le jornada historico
        $oHoras      = $oJornada->PesquisaUltimosHorariosJornada($tSiape, $hjornada);
        //$entrada     = $oHoras->entra_trab;
        //$saidaAlmoco = $oHoras->ini_interv;
        //$voltaAlmoco = $oHoras->sai_interv;
        //$saida       = $oHoras->sai_trab;

        //if (($_SESSION['ss_jor'] != $Jornada) || ($_SESSION['ss_datjorn'] != anti_injection($_REQUEST['datjorn'])))
        //{
            // grava jornada e horarios no jornada historico
        //    $oJornada->inserirJornadaHistorico($tSiape, $lotacao, $Jornada, $datjorn, $entrada, $saidaAlmoco, $voltaAlmoco, $saida);
        //}
    }

    // apaga variaveis de sessao
    include_once( "cadastro_sessao_elimina.php" );

    //grava o LOG
    registraLog("alterou os dados do servidor $nom");

    // mensagem
    mensagem("Dados gravados com sucesso!", "cadastro_alteracao.php", 1);
}

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
