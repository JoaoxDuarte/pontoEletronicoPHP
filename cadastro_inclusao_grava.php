<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH');

$destino = 'cadastro_inclusao.php';

// dados
$wnome        = anti_injection($_POST['wnome']);
$wnome_social = anti_injection($_POST['wnome_social']);
$tSiape       = soNumeros(anti_injection($_POST['tSiape']));
$Siapecad     = soNumeros(anti_injection($_POST['Siapecad']));
$idunica      = soNumeros(anti_injection($_POST['idunica']));
$Situacao     = (trim($_POST['Situacao']) == '' ? '00' : anti_injection($_POST['Situacao']));
$wcargo       = anti_injection($_POST['wcargo']);
$nivel        = strtoupper(trata_aspas(anti_injection($_POST['nivel'])));
$Regjur       = anti_injection($_POST['Regjur']);

$wdatinss     = (empty(trim($_POST['wdatinss'])) ? "00/00/0000" : $_POST['wdatinss']);
$wdatinss     = conv_data($wdatinss);

$Jornada       = soNumeros(anti_injection($_POST['Jornada']));
$Jornada_cargo = soNumeros(anti_injection($_POST['Jornada_cargo']));
$datjorn       = (empty(trim($_POST['datjorn'])) ? "00/00/0000" : $_POST['datjorn']);
$datjorn       = conv_data($datjorn);

$defvis        = anti_injection($_POST['defvis']);
$pis           = soNumeros(anti_injection($_POST['pis']));
$cpf           = soNumeros(anti_injection($_POST['cpf']));

$dtnasc        = (empty(trim($_POST['dtnasc'])) ? "00/00/0000" : $_POST['dtnasc']);
$dtnasc        = conv_data($dtnasc);

$wlota         = soNumeros(anti_injection($_POST['wlota']));

$datlot        = (empty(trim($_POST['datlot'])) ? "00/00/0000" : $_POST['datlot']);
$datlot        = conv_data($datlot);

$loca          = soNumeros(anti_injection($_POST['loca']));
$loca          = ($loca == '00000000000000' ? $wlota : $loca);

$datloca       = (empty(trim($_POST['datloca'])) || $_POST['datloca'] == '00/00/0000' ? $datlot : $_POST['datloca']);
$datloca       = conv_data($datloca);

$email         = trata_aspas(trim(anti_injection($_POST['email'])));

$nom           = trata_aspas(strtoupper(trim($wnome)));
$permitehoras  = anti_injection($_POST['limite-horas']);
$plantaomedico  = anti_injection($_POST['plantao-medico']);


$datdiplsp2    = '0000-00-00';
$datcarreira   = '0000-00-00';
$datdiplsp3    = '0000-00-00';

// grava em sessão para uso no retorno à página se houver erro
include_once('cadastro_sessao_grava.php');



## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setSubTitulo("Inclusão de Servidores e Estagiários");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();



## ############################### ##
##                                 ##
##            VALIDAÇÃO            ##
##                                 ##
## ############################### ##


validacaoDosCamposPost();



## ############################### ##
##                                 ##
##            GRAVAÇÃO             ##
##                                 ##
## ############################### ##


$tSiape = getNovaMatriculaBySiape($tSiape);


// verifica se matrícula já cadastrada
if (verificaSeMatriculaCadastrada($tSiape) === false &&
   verificaSeCPFCadastrado($cpf) === false)
{
    // instancia banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setMensagem("Erro no acesso ao CADASTRO");
    $oDBase->setDestino($destino);

        $oDBase->query("
        INSERT INTO servativ
        SET
            mat_siape    = :mat_siape,
            mat_siapecad = :mat_siapecad,
            ident_unica  = :ident_unica,
            nome_serv    = :nome_serv,
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
            cod_lot      = :cod_lot,
            cod_loc      = :cod_loc,
            dt_ing_lot   = :dt_ing_lot,
            dt_ing_loc   = :dt_ing_loc,
            area         = :area,
            jornada      = :jornada,
            dt_ing_jorn  = :dt_ing_jorn,
            email        = :email,
            freqh        = :freqh,
            jornada_cargo= :jornada_cargo,
            nome_social = :nome_social,
            limite_horas = :limite_horas,
            plantao_medico = :plantao_medico
        ",
        array(
            array(':mat_siape',    $tSiape,      PDO::PARAM_STR),
            array(':mat_siapecad', $Siapecad,    PDO::PARAM_STR),
            array(':ident_unica',  $idunica,     PDO::PARAM_STR),
            array(':nome_serv',    $nom,         PDO::PARAM_STR),
            array(':dt_nasc',      $dtnasc,      PDO::PARAM_STR),
            array(':defvis',       $defvis,      PDO::PARAM_STR),
            array(':cpf',          $cpf,         PDO::PARAM_STR),
            array(':pis_pasep',    $pis,         PDO::PARAM_STR),
            array(':cod_sitcad',   $Situacao,    PDO::PARAM_STR),
            array(':reg_jur_at',   $Regjur,      PDO::PARAM_STR),
            array(':f_ing_org',    $fingorg,     PDO::PARAM_STR),
            array(':dt_adm',       $wdatinss,    PDO::PARAM_STR),
            array(':dipl_inss',    $diplinss,    PDO::PARAM_STR),
            array(':n_sip_inss',   $ndiplsp2,    PDO::PARAM_STR),
            array(':dt_dip_ins',   $datdiplsp2,  PDO::PARAM_STR),
            array(':cod_cargo',    $wcargo,      PDO::PARAM_STR),
            array(':dt_ing_car',   $datcarreira, PDO::PARAM_STR),
            array(':f_ing_car',    $formicr,     PDO::PARAM_STR),
            array(':dipl_car',     $diplcr,      PDO::PARAM_STR),
            array(':n_sip_car',    $ndiplsp3,    PDO::PARAM_STR),
            array(':dt_dip_car',   $datdiplsp3,  PDO::PARAM_STR),
            array(':nivel',        $nivel,       PDO::PARAM_STR),
            array(':cod_classe',   $classe,      PDO::PARAM_STR),
            array(':cod_padrao',   $padrao,      PDO::PARAM_STR),
            array(':cod_lot',      $wlota,       PDO::PARAM_STR),
            array(':cod_loc',      $loca,        PDO::PARAM_STR),
            array(':dt_ing_lot',   $datlot,      PDO::PARAM_STR),
            array(':dt_ing_loc',   $datloca,     PDO::PARAM_STR),
            array(':area',         $area,        PDO::PARAM_STR),
            array(':jornada',      $Jornada,     PDO::PARAM_STR),
            array(':dt_ing_jorn',  $datjorn,     PDO::PARAM_STR),
            array(':email',        $email,       PDO::PARAM_STR),
            array(':freqh',        'N',          PDO::PARAM_STR),
            array(':jornada_cargo', $Jornada_cargo,          PDO::PARAM_STR),
            array(':nome_social', $wnome_social,PDO::PARAM_STR),
            array(':limite_horas', $permitehoras,PDO::PARAM_STR),
            array(':plantao_medico', $plantaomedico,PDO::PARAM_STR)
        ));




        /* lendo dados do setor */
        $oDBase->query("SELECT upag FROM tabsetor WHERE codigo = :codigo", array(
            array(':codigo', $wlota, PDO::PARAM_STR),
        ));
        $oSetor = $oDBase->fetch_object();
        $upag   = $oSetor->upag;

        /* gravando dados de usuario  */
        $dn     = dataDia($dtnasc) . dataMes($dtnasc) . dataAno($dtnasc);
        $ssenha = substr(md5($dn), 0, 14);

        $oDBase->setMensagem("Erro no acesso ao cadastro de Usuário!");
        $oDBase->query("SELECT siape FROM usuarios WHERE siape = :siape", array(
            array(':siape', $tSiape, PDO::PARAM_STR),
        ));

        if ($oDBase->num_rows() == 0)
        {
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
                array(':siape',  $tSiape,   PDO::PARAM_STR),
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
                acesso = :acesso,
                setor  = :setor,
                senha  = :senha,
                prazo  = :prazo,
                upag   = :upag,
                defvis = :defvis

            WHERE
                siape = :siape
            ", array(
                array(':siape',  $tSiape,         PDO::PARAM_STR),
                array(':nome',   $nom,            PDO::PARAM_STR),
                array(':acesso', 'NNSNNNNNNNNNN', PDO::PARAM_STR),
                array(':setor',  $wlota,          PDO::PARAM_STR),
                array(':senha',  $ssenha,         PDO::PARAM_STR),
                array(':prazo', '1',              PDO::PARAM_STR),
                array(':upag',   $upag,           PDO::PARAM_STR),
                array(':defvis', $defvis,         PDO::PARAM_STR),
            ));
        }

        // registra no historico de jornada
        // se houver alteração da jornada
        // do servidor ou estagiário
        // le jornada historico
        $oJornada = new DefinirJornada();
        $oJornada->setSiape( $tSiape );
        $oJornada->leDadosServidor();
        $oJornada->leSupervisao( $datjorn );

        // apaga estes atributos da sessão
        include_once('cadastro_sessao_elimina.php');

        //grava o LOG
        registraLog("incluiu o servidor $nom");

        // mensagem
        mensagem("Dados gravados com sucesso!", $destino);
    }


// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();



/*
 ************************************************
 *                                              *
 *            FUNÇÕES COMPLEMENTARES            *
 *                                              *
 ************************************************
 */

/*
 * @info Valida os dados passados por formulário
 *
 * @param  void
 * $return  void
 */
function validacaoDosCamposPost()
{
    global $wnome, $wnome_social, $tSiape, $Siapecad, $idunica, $Situacao,
        $wcargo, $nivel, $Regjur, $wdatinss, $wdatinss, $Jornada,
        $Jornada_cargo, $datjorn, $datjorn, $defvis, $pis, $cpf, $dtnasc,
        $dtnasc, $wlota, $datlot, $datlot, $loca, $datloca, $email, $nom,
        $permitehoras, $datdiplsp2, $datcarreira, $datdiplsp3, $destino;

    // grupos situacao cadastral
    $cargos_niveis = array(
        'NA', /* Nível Auxiliar      */
        'NI', /* Nível Intermediário */
        'NM', /* Nível Médio         */
        'NS'  /* Nível Superior      */
    );

    $oDBase = new DataBase();
    $oDBase->query("
    SELECT
        codsitcad
    FROM
        tabsitcad
    WHERE
        carg_obrig = 'N'
    ");
    
    $situacao_sem_cargo = array();

    while ($rows = $oDBase->fetch_object())
    {
        $situacao_sem_cargo[] = $rows->codsitcad;
    }


    // class valida
    $validar = new valida();
    $validar->setDestino( $destino );
    $validar->setExibeMensagem( false );


    ## NOME DO SERVIDOR / ESTAGIÁRIO
    if (strlen(trim($wnome)) == 0)
    {
        $validar->setMensagem('- O NOME é obrigatório!<br>');
    }

    ## MATRÍCULA SIAPE
    $validar->siape( $tSiape );

    ## MATRÍCULA SIAPECAD
    if (strlen(trim($Siapecad)) != 8 || $Siapecad == '00000000')
    {
        $validar->setMensagem("- É obrigatório informar o SIAPECAD com 8 números!<br>");
    }

    ## IDENTIFICAÇÃO ÚNICA
    if (strlen(trim($idunica)) != 9 || $idunica == '000000000')
    {
        $validar->setMensagem("- É obrigatório informar a identificação única com 9 números!<br>");
    }

    ## SITUAÇÃO FUNCIONAL
    if ($Situacao == '00')
    {
        $validar->setMensagem("- A situação funcional deve ser informada!<br>");
    }

    ## EMAIL
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    {
        $validar->setMensagem("- O E-mail não é válido!<br>");
    }

    ## CARGO EFTIVO
    if (!in_array($Situacao,$situacao_sem_cargo) && $wcargo == '000000')
    {
        $validar->setMensagem("- O Cargo é obrigatório!<br>");
    }
    else if (in_array($Situacao,$situacao_sem_cargo) && $wcargo != '000000')
    {
        $validar->setMensagem("- Situação cadastral do servidor impede alteração de cargo!<br>");
    }

    ## NÍVEL (Para servidor preencher: NA, NI, NM ou NS, para estagiário NM ou NS)
    if (!empty(trim($nivel)))
    {
        if ($Situacao == '66' && (trim($nivel) != 'NS' && trim($nivel) != 'NM'))
        {
            $validar->setMensagem("- O nível deve ser informado (Estagiário: NS/NM)!<br>");
        }
        else if (!in_array($Situacao,$situacao_sem_cargo) && !in_array($nivel,$cargos_niveis))
        {
            $validar->setMensagem("- O nível deve ser informado (Servidor: NS/NM/NI/NA)!<br>");
        }
    }

    ## REGIME JURÍDICO
    if ($Regjur == '0')
    {
        $validar->setMensagem("- O regime jurídico é obrigatório!<br>");
    }

    ## DATA DE INGRESSO NO ÓRGÃO
    $validar->data( $wdatinss, "A data de ingresso no Órgão deve ser informada!" );

    /*## JORNADA
    if (strlen(trim($Jornada)) != 2 || $Jornada == '00')
    {
        $validar->setMensagem("- A jornada é obrigatória!<br>");
    }*/

    ## DATA DE INGRESSO NA JORNADA
    $validar->data( $datjorn, "A data de início da jornada é obrigatória!" );

    ## PIS-PASEP
    $pis_invalido = array(
        '00000000000', '11111111111', '22222222222', '33333333333', '22222222222',
        '55555555555', '66666666666', '77777777777', '88888888888', '99999999999'
    );
    if (strlen(trim($pis)) != 11 || in_array($pis, $pis_invalido))
    {
        $validar->setMensagem("- O PIS não é válido!<br>");
    }

    ## CPF
    if (validaCPF($cpf) == false)
    {
        $validar->setMensagem("- O CPF não é válido!<br>");
    }

    ## DATA DE NASCIMENTO
    $validar->data( $dtnasc, "A data de nascimento é obrigatória!" );

    ## LOTAÇÃO
    if ($wlota == '00000000000000')
    {
        $validar->setMensagem("- A LOTAÇÃO é obrigatória!<br>");
    }

    ## DATA DE INGRESSO NA LOTAÇÃO
    $validar->data( $datlot, "A data de ingresso na lotação deve ser informada!" );

    // Exibe mensagem(ns) de erro, se houver
    if ($validar->getMensagem() != "")
    {
        $validar->exibeMensagem();
        die();
    }
}


/*
 * @info Verifica se a matrícula já está cadastrada
 *
 * @param  string $siape  Matrícula do servidor
 * $return  boolean  False: não cadastrada
 */
function verificaSeMatriculaCadastrada($siape)
{
    global $destino;

    // instancia banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setMensagem("Erro no acesso ao CADASTRO");
    $oDBase->setDestino($destino);

    $oDBase->query("
    SELECT
        cad.mat_siape,
        (SELECT und2.descricao FROM tabsetor AS und2 WHERE LEFT(und2.sigla,3) = 'GEX' AND usu.upag = und2.upag LIMIT 1) AS gerencia,
        (SELECT IF(IFNULL(cad2.cpf,'')='','N','S') FROM servativ AS cad2 WHERE cad.cpf = cad2.cpf LIMIT 1) AS cpf_repetido,
        (SELECT IF(IFNULL(cad2.pis_pasep,'')='','N','S') FROM servativ AS cad2 WHERE cad.pis_pasep = cad2.pis_pasep LIMIT 1) AS pis_repetido
    FROM servativ AS cad
    LEFT JOIN usuarios AS usu ON cad.mat_siape = usu.siape
    WHERE
        cad.mat_siape = :siape
    ",
    array(
        array(':siape', $siape, PDO::PARAM_STR)
    ));

    if ($oDBase->num_rows() > 0)
    {
        mensagem("Matricula já consta na base!", $destino, 1);
        return true;
    }

    return false;
}

/*
 * @info Verifica se o CPF já está cadastrado
 *
 * @param  string $cpf  CPF do servidor
 * $return  boolean  False: não cadastrado
 */
function verificaSeCPFCadastrado($cpf)
{
    global $destino;

    // instancia banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setMensagem("Erro no acesso ao CADASTRO");
    $oDBase->setDestino($destino);

    // verifica se matrícula já cadastrada
    $oDBase->query("
    SELECT
        cad.cpf,
        cad.nome_serv AS nome,
        cad.cod_lot
    FROM servativ AS cad
    LEFT JOIN usuarios AS usu ON cad.mat_siape = usu.siape
    LEFT JOIN tabcargo ON cad.cod_cargo = tabcargo.COD_CARGO
    WHERE
        cad.cpf = :cpf AND NOT tabcargo.DESC_CARGO LIKE '%medico%'
    ", array(
        array(':cpf', $cpf, PDO::PARAM_STR),
    ));

    if ($oDBase->num_rows() > 0)
    {
        while ($dados = $oDBase->fetch_object())
        {
            $info  = $dados->nome . '<br>';
            $info .= getOrgaoMaisSigla($dados->cod_lot) . '<br>';
            $info .= getUorgMaisDescricao($dados->cod_lot) . '<br>';
        }

        mensagem("CPF já consta na base!<br> " . $info, $destino, 1);
        return true;
    }

    return false;
}
