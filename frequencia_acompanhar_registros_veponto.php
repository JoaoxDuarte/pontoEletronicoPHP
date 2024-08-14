<?php

include_once("config.php");
include_once("class_ocorrencias_grupos.php");

verifica_permissao("sAPS");

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    // Valores passados - encriptados
    $dados    = explode(":|:", base64_decode($dadosorigem));
    $mat      = $dados[0];
    $mes      = $dados[1];
    $ano      = $dados[2];
    $sLotacao = $dados[3];
    $cmd      = $dados[4];
    $so_ver   = $dados[5]; //($dados[5]==''?'nao':$dados[5]);

    if (date('m') == $mes && date('Y') == $ano)
    {
        $dia = date('j'); // dia do mês sem 0 à esquerda. Ex. 1 (primeiro dia)
    }
}

$sLotacao = (empty($sLotacao) ? $_SESSION["sLotacao"] : $sLotacao);
$magico   = $_SESSION["magico"];

switch ($cmd)
{
    case "2": $cmd = 1;
        break;
    case "3": $cmd = 3;
        break;
}

$mat = getNovaMatriculaBySiape($mat);

// dados voltar
$_SESSION['voltar_nivel_2'] = "frequencia_acompanhar_registros_veponto.php?dados=" . $dadosorigem;
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';
$_SESSION['voltar_nivel_5'] = '';

// instancia o banco de dados
$oDBase = new DataBase('PDO');

/* obtem dados dos servidores */
$oServidor   = DadosServidor($mat)->fetch_object();
$nome        = $oServidor->nome_serv;
$lot         = $oServidor->cod_lot;
$jnd         = $oServidor->jornada;
$chefe       = $oServidor->chefia;
$upg         = $oServidor->upag;
$dt_adm      = $oServidor->dt_adm;
$sitcad      = $oServidor->sigregjur;
$competencia = $mes . $ano;

if ($cmd == "1")
{
    $qlotacao = anti_injection($_REQUEST["sLotacao"]);
}
else
{
    $qlotacao = $_SESSION["sLotacao"];
}

$qlotacao = (empty($qlotacao) ? $sLotacao : $qlotacao);

$bRecursosHumanos   = ($_SESSION['sRH'] == "S");
$bRecursosHumanosSR = ($_SESSION['sRH'] == "S" && substr($_SESSION['sLotacao'], 2, 6) == '150000');
$bDiretoria         = ($_SESSION["sCAD"] == "S");
$bGestoresSISREF    = ($_SESSION["sSenhaI"] == "S");
$bAuditoria         = ($_SESSION['sAudCons'] == 'S' || $_SESSION["sLog"] == "S");
$bSuperintendente   = ($_SESSION['sAPS'] == 'S' && substr($_SESSION['sLotacao'], 2, 6) == '150000');
$bGerenteExecutivo  = ($_SESSION['sAPS'] == 'S' && substr($_SESSION['sLotacao'], 2, 1) == '0' && substr($_SESSION['sLotacao'], 5, 3) == '000');

if ($bDiretoria == true || $bGestoresSISREF == true || $bAuditoria == true || $bSuperintendente == true || $bGerenteExecutivo == true)
{
    // continua
}
elseif ($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N" && $lot != $qlotacao && $chefe == "N" && $magico < '3')
{
    mensagem("Não é permitido consultar/alterar servidor de outro setor!", $_SESSION['voltar_nivel_1']);
}
elseif ($_SESSION['sRH'] == "S" && $upg != $_SESSION['upag'])
{
    mensagem("Não é permitido consultar ponto de servidor de outra upag!", $_SESSION['voltar_nivel_1']);
}

$comp = $mes;
$year = $ano;

// mes e ano homologação
$competencia     = $mes . $ano; //$oData->getCompetHomologacao();
$nome_do_arquivo = 'ponto' . $competencia;

$mes_homologacao = true;

inserir_dias_sem_frequencia($mat, date('j'), $mes, $ano, $jornada, $sLotacao, $nome_do_arquivo, $dt_adm);


// carrrega dados do SIAPENET
updateAfastamentosBySiape($mat);

    
## DADOS DA FREQUÊNCIA
#
$oDBase = DadosFrequencia($mat, $mes, $ano);

$linhas = $oDBase->num_rows();

$ocorrencias_88888  = 0;
$ocorrencias_99999  = 0;
$ocorrencias_tracos = 0;

$registrosComparecimentoOcorrencia = array();

$umavez = true;

$atribuido = false;

while ($pm_partners = $oDBase->fetch_object())
{
    if ($atribuido == false)
    {
        $sNome                   = $pm_partners->nome;
        $horario_do_setor_inicio = $pm_partners->inicio_atend;
        $horario_do_setor_fim    = $pm_partners->fim_atend;
        $cmun                    = $pm_partners->codmun;
        $uorg                    = $pm_partners->cod_uorg;
        $upag                    = $pm_partners->upag;
        $anomes_admissao         = $pm_partners->dt_adm;
        $lotacao                 = $pm_partners->codigo;
        $lotacao_descricao       = $pm_partners->descricao;
        $orgao_sigla             = $pm_partners->sigla;
        $cod_sitcad              = $pm_partners->cod_sitcad;

        $oJornada            = new DefinirJornada();
        $oDBaseJH            = $oJornada->PesquisaJornadaHistorico($mat, '01/' . $mes . '/' . $ano);
        $oHorario            = $oDBaseJH->fetch_object();
        $entrada_no_servico  = $oHorario->entra_trab;
        $saida_para_o_almoco = $oHorario->ini_interv;
        $volta_do_almoco     = $oHorario->sai_interv;
        $saida_do_servico    = $oHorario->sai_trab;
        $jnd                 = $oHorario->jornada;


        ## ocorrências grupos
        $obj = new OcorrenciasGrupos();
        $grupoOcorrenciasNegativasDebitos = $obj->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios=true);
        $pagarEmFolha                     = $obj->PagarEmFolha($sitcad);
        $codigosCredito                   = $obj->CodigosCredito($sitcad,true); // true para carregar também o código de viagem
        $codigosTrocaObrigatoria          = $obj->CodigosTrocaObrigatoria($sitcad);

        $codigoSemFrequenciaPadrao        = $obj->CodigoSemFrequenciaPadrao($sitcad);
        $codigoRegistroParcialPadrao      = $obj->CodigoRegistroParcialPadrao($sitcad);


        $atribuido = true;
    }

    if (in_array($pm_partners->oco, $codigoRegistroParcialPadrao))
    {
      $ocorrencias_88888++;
    }

    if (in_array($pm_partners->oco, $codigoSemFrequenciaPadrao))
    {
      $ocorrencias_99999++;
    }

    if ($pm_partners->oco == $codigosTrocaObrigatoria[0]) // '-----'
    {
      $ocorrencias_tracos++;
    }


    ## Prepara os dados para exibir
    #
    if ($umavez == true)
    {
        $umavez       = false;
        $dia_nao_util = marca_dias_nao_util($mes, $ano, $pm_partners->codmun, $pm_partners->codigo);
    }
    $xdia       = $pm_partners->dia;
    $background = $dia_nao_util[$xdia][0];
    $color      = $dia_nao_util[$xdia][1];

    $font_i_color = "";
    $sinal        = '&nbsp;';
    $font_f_color = "";

    // elimina "/" e ":", depois define o tipo como inteiro
    // para garantir a resultado do teste a seguir
    $jornada_dif = alltrim(sonumeros($pm_partners->jorndif), '0');
    settype($jornada_dif, 'integer');

    if (!empty($jornada_dif) && in_array($pm_partners->oco,$grupoOcorrenciasNegativasDebitos))
    {
        $font_i_color = "<font color='red'>";
        $font_f_color = "</font>";
        $sinal        = "<font color='red'> - </font>";
    }
    else if (!empty($jornada_dif) && in_array($pm_partners->oco,$pagarEmFolha))
    {
        $font_i_color = "<font color='red'>(";
        $font_f_color = ")</font>";
        $sinal        = "";
    }
    else if (!empty($jornada_dif) && in_array($pm_partners->oco,$codigosCredito))
    {
        $sinal = " + ";
    }

    if (in_array($pm_partners->oco,$codigosTrocaObrigatoria))
    {
        $codigo_da_ocorrencia = "<font color='red'><b>" . $pm_partners->oco . "</b></font>";
    }
    else
    {
        $codigo_da_ocorrencia = $pm_partners->oco;
    }


    ## Ação: ALTERAR OCORRÊNCIA (VER JUSTIFICATIVA)
    #
    $justificativa_value = LinkAlterarOcorrenciaVerJustificativa($pm_partners, $codigo_da_ocorrencia);



    ## Ação: ALTERAR
    #
    // matricula, nome, dia, ocorrência, lotação,
    // identificacao do registrador, cmd, jornada,
    // so ver justificativa, situação cadastral e acompanhar registros
    $acao_alterar = LinkAlterarOcorrencia($mat, $sNome, $pm_partners, $lot, $jnd, $cod_sitcad);


    ## Ação: ABONAR
    #
    $acao_abonar = LinkAbonarOcorrencia($mat, $pm_partners, $cmd, $sitcad=null);


    ## Ação: EXCLUIR
    #
    $acao_excluir = LinkExcluirOcorrencia($mat, $pm_partners, $dia_nao_util, $xdia);


    $registrosComparecimentoOcorrencia[] = array(
        'background'           => $background,
        'color'                => $color,
        'dia-title'            => $dia_nao_util[$xdia][4],
        'dia-value'            => trim($dia_nao_util[$xdia][2]) . '&nbsp;' . trim($xdia . $dia_nao_util[$xdia][3]),
        'pm_partners'          => $pm_partners,
        'saldo'                => $sinal . ' ' . $font_i_color . $pm_partners->jorndif . $font_f_color,
        'justificativa-value'  => $justificativa_value,
        'acao-alterar'         => $acao_alterar,
        'acao-abonar'          => $acao_abonar,
        'acao-excluir'         => $acao_excluir,
        'codigo_da_ocorrencia' => $codigo_da_ocorrencia,
    );
}


// verificação dos dados
$registroParcialPadrao = implode(', ', $codigoRegistroParcialPadrao);
$semFrequenciaPadrao   = implode(', ', $codigoSemFrequenciaPadrao);
$trocaObrigatoria      = $codigosTrocaObrigatoria[0];

$avisos_mensagens = "<tr><td colspan='5'>";

//$avisos_mensagens .= "<div align='center' style='font-size: 10pt;'><font color='red'><b>ATENÇÃO:</b></font> Opção <b>Excluir</b> habilitada para os dias do final de semana e feriados.</div>";

if ($ocorrencias_88888 > 0 || $ocorrencias_99999 > 0 || $ocorrencias_tracos > 0)
{
    $avisos_mensagens .= "<div class='text-center' style='color:red;'><b>ATENÇÃO:</b> ";
    if ($ocorrencias_88888 > 0 && $ocorrencias_99999 > 0 && $ocorrencias_tracos > 0)
    {
        $avisos_mensagens .= "»» Há ocorrência(s) com código " . $registroParcialPadrao . ", " . $semFrequenciaPadrao . " e \"" . $trocaObrigatoria . "\" na ficha do servidor ««";
    }
    else if ($ocorrencias_88888 > 0 && $ocorrencias_99999 > 0)
    {
        $avisos_mensagens .= "»» Há ocorrência(s) com código " . $registroParcialPadrao . " e " . $semFrequenciaPadrao . " na ficha do servidor ««";
    }
    else if ($ocorrencias_88888 > 0 && $ocorrencias_tracos > 0)
    {
        $avisos_mensagens .= "»» Há ocorrência(s) com código ". $registroParcialPadrao . " e \"" . $trocaObrigatoria . "\" na ficha do servidor ««";
    }
    else if ($ocorrencias_99999 > 0 && $ocorrencias_tracos > 0)
    {
        $avisos_mensagens .= "»» Há ocorrência(s) com código " . $semFrequenciaPadrao . " e \"" . $trocaObrigatoria . "\" na ficha do servidor ««";
    }
    else if ($ocorrencias_tracos > 0)
    {
        $avisos_mensagens .= "»» Há ocorrência(s) com código \"" . $trocaObrigatoria . "\" na ficha do servidor ««";
    }
    else if ($ocorrencias_88888 > 0)
    {
        $avisos_mensagens .= "»» Há ocorrência(s) com código " .$registroParcialPadrao . " na ficha do servidor ««";
    }
    else if ($ocorrencias_99999 > 0)
    {
        $avisos_mensagens .= "»» Há ocorrência(s) com código " .$semFrequenciaPadrao . " na ficha do servidor ««";

    }
    $avisos_mensagens .= "</div>";
} // fim do if
$avisos_mensagens .= "</td></tr>";

$destino_voltar = ($_SESSION["sLancarExcessao"] == "S" ? $_SESSION['voltar_nivel_1'] : $_SESSION['voltar_nivel_0']);

trocaParametroREQUEST_URI("sLotacao", $qlotacao);
$_SESSION["sVePonto"]     = $_SERVER['REQUEST_URI'];
$caminho_modulo_utilizado = 'Frequência » Acompanhar » Registro de comparecimento';

$paginaDestino = $_SESSION['sPaginaRetorno_sucesso'];

$titulo_pagina = 'Acompanhar Registro de Comparecimento';
$title         = _SISTEMA_SIGLA_ . ' | ' . $titulo_pagina;

$acao_manutencao = "acompanhar";


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setJS( _DIR_JS_ . 'phpjs.js' );

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML($width='1540px;');

include_once "html/form-frequencia-manutencao.php";

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();


/* ***************************************************** *
 *                                                       *
 *                 FUNÇÕES COMPLEMENTARES                *
 *                                                       *
 * ***************************************************** *
 */

/*
 * @param  string  $siape  Matrícula do servidor
 * @return  resource  $oDBase  Seleção realizada
 *
 * @info  Recupera dados do servidor
 */
function DadosServidor($siape)
{
    $oDBase = new DataBase('PDO');
    $oDBase->query("
    SELECT
        servativ.nome_serv, servativ.entra_trab, servativ.ini_interv,
        servativ.sai_interv, servativ.sai_trab, servativ.cod_lot,
        servativ.chefia, servativ.jornada,
        DATE_FORMAT(servativ.dt_adm,'%d/%m/%Y') AS dt_adm,
        tabsetor.upag
            FROM servativ
                LEFT JOIN tabsetor ON servativ.cod_lot = tabsetor.codigo
                    WHERE mat_siape = :siape ", array(
    array(':siape', $siape, PDO::PARAM_STR),
    ));

    return $oDBase;
}

/*
 * @param  string  $siape  Matrícula do servidor
 * @param  string  $mes    Mês da competência
 * @param  string  $ano    Ano da competência
 * @return  resource  $oDBase  Seleção realizada
 *
 * @info  Recupera dados da frequência do servidor
 */
function DadosFrequencia($siape, $mes, $ano)
{
    $tabela = "ponto".$mes.$ano;

    $oDBase = new DataBase('PDO');
    $oDBase->query("
    SELECT
        cad.nome_serv AS nome, und.inicio_atend, und.fim_atend, und.codmun,
        und.cod_uorg, und.upag, pto.entra,
        DATE_FORMAT(pto.dia,'%d/%m/%Y') AS dia,
        pto.intini, pto.intsai, pto.sai, pto.jornd, pto.jornp, pto.jorndif,
        pto.oco, pto.just, pto.justchef, pto.idreg, oco.desc_ocorr AS dcod,
        und.codmun, und.codigo, pto.idreg, pto.ip, pto.matchef, pto.siaperh,
        DATE_FORMAT(cad.dt_adm,'%d/%m/%Y') AS dt_adm, und.descricao,
        taborgao.denominacao, taborgao.sigla, cad.cod_sitcad, cad.sigregjur
    FROM
        $tabela AS pto
    LEFT JOIN
        tabocfre AS oco ON pto.oco = oco.siapecad
    LEFT JOIN
        servativ AS cad ON pto.siape = cad.mat_siape
    LEFT JOIN
        tabsetor AS und ON cad.cod_lot = und.codigo
    LEFT JOIN
        taborgao ON LEFT(und.codigo,5) = taborgao.codigo
    WHERE
        pto.siape = :siape
        AND dia <> '0000-00-00'
    ORDER BY
        pto.dia
    ", array(
        array(':siape', $siape, PDO::PARAM_STR)
    ));

    return $oDBase;
}


/*
 * @param  string  $pm_partners             Array com dados do servidor/frequência
 * @param  string  $codigo_da_ocorrencia    Código da ocorrência a alterar
 * @return  string  Link para alterar ocorrência e ver justificativa do servidor
 *
 * @info  Link para alterar ocorrência e ver justificativa do servidor
 */
function LinkAlterarOcorrenciaVerJustificativa($pm_partners, $codigo_da_ocorrencia)
{
    // indica se há justificativa registrada
    if ($pm_partners->just != "")
    {
        $justificativa_value = "<img border= '0' src='imagem/arrow.gif' width='7' height='7' align='absmiddle'>";
        $justificativa_value .= "&nbsp;&nbsp;<a href=\"javascript:verJustificativa('" . preparaTextArea($pm_partners->just) . "');\" title=\"" . $pm_partners->dcod . "\n" . $pm_partners->just . "\" alt=\"" . $pm_partners->dcod . "\n" . preparaTextArea($pm_partners->just) . "\" style='color: #404040;'>" . $codigo_da_ocorrencia . "</a>";
    }
    else
    {
        $justificativa_value = "<div title=\"" . $pm_partners->dcod . "\" alt=\"" . $pm_partners->dcod . "\" style='color: #404040;'><img border= '0' src='imagem/ativar_off.gif' width='7' height='7' align='absmiddle'>&nbsp;&nbsp;" . $codigo_da_ocorrencia . "</div>";
    }

    return $justificativa_value;
}

/*
 * @param  string  $siape       Matrícula do servidor
 * @param  string  $nome        Nome do servidor
 * @param  string  $pm_partners Array com dados do servidor/frequência
 * @param  string  $setor       Unidade de lotação do servidor
 * @param  string  $jornada     Jornada do servidor
 * @param  string  $sitcad      Situação cadastral do servidor
 * @return  string  Link para alterar ocorrência
 *
 * @info  Link para alterar ocorrência
 */
function LinkAlterarOcorrencia($siape, $nome, $pm_partners, $setor, $jornada, $sitcad)
{
    $frequencia_alterar = base64_encode($siape . ':|:' . $nome . ':|:' . $pm_partners->dia . ':|:' . $pm_partners->oco . ':|:' . $setor . ':|:' . $pm_partners->idreg . ':|:2:|:' . $jornada . ':|:' . $sitcad . ':|:acompanhar_ve_ponto');

    $acao_alterar = "<a href=\"javascript:window.location.replace('frequencia_alterar.php?dados=" . $frequencia_alterar . "');\" style='color: #404040;'>Alterar</a>";

    return $acao_alterar;
}

/*
 * @param  string  $siape       Matrícula do servidor
 * @param  string  $pm_partners Array com dados do servidor/frequência
 * @param  string  $cmd         Define qual a ação adotar no momento da gravação
 * @param  string  $sitcad      Situação cadastral do servidor
 * @return  string  Link para abonar ocorrência
 *
 * @info  Link para abonar ocorrência
 */
function LinkAbonarOcorrencia($siape, $pm_partners, $cmd, $sitcad=null)
{
    $obj = new OcorrenciasGrupos();
    $ocoPassiveisDeAbono = $obj->GrupoOcorrenciasPassiveisDeAbono($sitcad);

    // matricula, dia, situação c adastral, cmd,
    // so ver justificativa e acompanhar
    $justificativa = base64_encode($siape . ":|:" . $pm_partners->dia . ':|:' . $cmd . ':|:nao:|:acompanhar_ve_ponto');

    if (in_array($pm_partners->oco,$ocoPassiveisDeAbono))
    {
        $acao_abonar = "<a href=\"javascript:window.location.replace('frequencia_justificativa_abono.php?dados=" . $justificativa . "');\" style='color: #404040;'>Abonar</a>";
    }
    else
    {
        $acao_abonar = "<a href='javascript:return false;' style='cursor: none; text-decoration: none; color: #f8f8f8;' disabled>Abonar</a>";
    }

    return $acao_abonar;
}

/*
 * @param  string  $siape         Matrícula do servidor
 * @param  string  $pm_partners   Array com dados do servidor/frequência
 * @param  string  $dia_nao_util  Array com informações do dia (feriado,etc)
 * @return  string  Link para excluir ocorrência
 *
 * @info  Link para excluir ocorrência
 */
function LinkExcluirOcorrencia($siape, $pm_partners, $dia_nao_util, $xdia=0)
{
    if ((strtr($dia_nao_util[$xdia][2], array('<font color=red><b>' => '', ' </b></font>' => '')) != '') || $xdia >= date('d/m/Y'))
    {
        $acao_excluir = "<a href=\"javascript:window.location.replace('frequencia_excluir.php?dados=" . base64_encode($siape . ":|:" . $pm_partners->dia . ':|:acompanhar_ve_ponto') . "');\" style='color: #404040;'>Excluir</a>";
    }
    else
    {
        $acao_excluir = "<a href='javascript:void(0);' style='cursor: none; text-decoration: none; color: #f8f8f8;' disabled>Excluir</a>";
    }

    return $acao_excluir;
}
