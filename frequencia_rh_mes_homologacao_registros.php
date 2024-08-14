<?php

include_once( "config.php" );
include_once( "class_form.frequencia.php" );
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao('sRH');

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    $dados      = explode(":|:", base64_decode($dadosorigem));
    $mat        = $dados[0];
    $lot        = $dados[1];
    $jnd        = $dados[2];
    $cod_sitcad = $dados[3];
}

// dados voltar
$_SESSION['voltar_nivel_1'] = $dadosorigem;
$_SESSION['voltar_nivel_2'] = '';
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';

$_SESSION['rh_mes_homologacao_registros'] = $dadosorigem;

// competencia
$data        = new trata_datasys();
$dia         = '01';
$ano         = $data->getAnoHomologacao();
$mes         = $data->getMesHomologacao();
$competencia = $data->getCompetHomologacao(); // mes e ano, ex.: 032010

$comp = $mes;
$year = $ano;

// mes e ano homologação
$competencia     = $mes . $ano; //$oData->getCompetHomologacao();
$nome_do_arquivo = 'ponto' . $competencia;

$mes_homologacao = true;

// pega data de admissão para uso
// em inserir_dias_sem_frequencia
$dt_adm = getDataAdmissaoDoServidor($mat);

$dias_no_mes = numero_dias_do_mes($mes, $ano);

inserir_dias_sem_frequencia($mat, $dias_no_mes, $mes, $ano, $jornada, $lot, $nome_do_arquivo, $dt_adm);


$ocorrencias_88888  = 0;
$ocorrencias_99999  = 0;
$ocorrencias_tracos = 0;

$registrosComparecimentoOcorrencia = array();

$umavez = true;

$atribuido = false;


// seleciona registros da frequência e dados do servidor
$oDBase = selecionaRegistrosFrequenciaDoServidor($mat, $competencia);

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
        $sitcad                  = $pm_partners->sigregjur;

        $oJornada            = new DefinirJornada();
        $oDBaseJH            = $oJornada->PesquisaJornadaHistorico($mat, '01/' . $mes . '/' . $ano);
        $oHorario            = $oDBaseJH->fetch_object();
        $entrada_no_servico  = $oHorario->entra_trab;
        $saida_para_o_almoco = $oHorario->ini_interv;
        $volta_do_almoco     = $oHorario->sai_interv;
        $saida_do_servico    = $oHorario->sai_trab;
        $jnd                 = $oHorario->jornada;


        // instancia grupo de ocorrencia
        $objOcorr = new OcorrenciasGrupos();

        $codigoRegistroParcialPadrao = $objOcorr->CodigoRegistroParcialPadrao($sitcad);
        $codigoSemFrequenciaPadrao   = $objOcorr->CodigoSemFrequenciaPadrao($sitcad);

        $codigosCompensaveis     = $objOcorr->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios = true);
        $PagarEmFolha            = $objOcorr->PagarEmFolha($sitcad);
        $CodigosCredito          = $objOcorr->CodigosCredito($sitcad);
        $codigosTrocaObrigatoria = $objOcorr->CodigosTrocaObrigatoria($sitcad);
        $codigosPassisveisDeAbono = $objOcorr->GrupoOcorrenciasPassiveisDeAbono($sitcad);


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
        $dia_nao_util = marca_dias_nao_util(
            $mes,
            $ano,
            $pm_partners->codmun,
            $pm_partners->codigo
        );
    }

    $xdia = $pm_partners->dia;

    $codigo_da_ocorrencia = preparaOcorrenciaParaExibir($pm_partners);

    // matricula, dia, situação cadastral, cmd,
    // so ver justificativa e homologacao
    $justificativa = base64_encode(
        $mat . ":|:" .
        $pm_partners->dia . ':|:' .
        $cod_sitcad . ":|:2:|:rh_mes_homologacao"
    );

    ## Ação: ALTERAR OCORRÊNCIA (VER JUSTIFICATIVA)
    #
    $justificativa_value = indicaSeHaJustificativaRegistrada($pm_partners, $codigo_da_ocorrencia);


    ## Ação: ALTERAR
    #
    $acao_alterar = acaoLinkFrequenciaAlterar($mat,$pm_partners);


    ## Ação: ABONAR
    #
    $acao_abonar = acaoLinkAbonar($pm_partners, $justificativa);


    ## Ação: EXCLUIR
    #
    $acao_excluir = acaoLinkExcluir($mat, $pm_partners, $dia_nao_util[$xdia][2]);


    // Registros tratados para exibição
    $registrosComparecimentoOcorrencia[] = array(
        'background'          => $dia_nao_util[$xdia][0],
        'color'               => $dia_nao_util[$xdia][1],
        'dia-title'           => $dia_nao_util[$xdia][4],
        'dia-value'           => trim($dia_nao_util[$xdia][2])
                                    . '&nbsp;'
                                    . trim($xdia . $dia_nao_util[$xdia][3]
                                 ),
        'pm_partners'         => $pm_partners,
        'saldo'               => preparaSaldoParaExibir($pm_partners),
        'justificativa-value' => $justificativa_value,
        'acao-alterar'        => $acao_alterar,
        'acao-abonar'         => $acao_abonar,
        'acao-excluir'        => $acao_excluir
    );
}


// verificação dos dados
$registroParcialPadrao = implode(', ', $codigoRegistroParcialPadrao);
$semFrequenciaPadrao   = implode(', ', $codigoSemFrequenciaPadrao);
$trocaObrigatoria      = $codigosTrocaObrigatoria[0];

$avisos_mensagens = "<tr><td colspan='5'>";

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
        $avisos_mensagens .= "»» Há ocorrência(s) com código " . $registroParcialPadrao . " e \"" . $trocaObrigatoria . "\" na ficha do servidor ««";
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


## classe para montagem do formulario padrao
#
$oForm         = new formPadrao();

$titulo_pagina = 'Manutenção Frequência - Mês em Homologação';
$title         = _SISTEMA_SIGLA_ . ' | ' . $titulo_pagina;

$acao_manutencao = "rh_mes_homologacao";

// ordena tabela
$css = array();

$javascript   = array();
$javascript[] = _DIR_JS_ . 'phpjs.js';
$javascript[] = 'frequencia_rh_mes_homologacao_registros.js';

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML($width='1540px;');

include_once "html/form-frequencia-manutencao.php";

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

exit();



/* ************************************************ *
 *                                                  *
 *              FUNÇÕES COMPLEMENTARES              *
 *                                                  *
 * ************************************************ */

/* @info  Pega data de admissão para uso
 *        em inserir_dias_sem_frequencia
 *
 * @param  string  $siape  Matrícula do servidor/estagiário
 * @return  string  Data da admissão
 *
 * @author Edinalvo Rosa
 */
function getDataAdmissaoDoServidor($siape)
{
    $siape = getNovaMatriculaBySiape($siape);

    $oDBase = new DataBase();

    $oDBase->query("
    SELECT DATE_FORMAT(cad.dt_adm,'%d/%m/%Y') AS dt_adm
	FROM servativ AS cad
            WHERE cad.mat_siape = :siape
    ",
    array(
        array(':siape', $siape, PDO::PARAM_STR)
    ));

    return $oDBase->fetch_object()->dt_adm;
}


/* @info  Seeleciona os registros de frequência do
 *        mês em homologação e ourros dados do servidor
 *
 * @param  string  $siape  Matrícula do servidor/estagiário
 * @return  object  Dados da frequência e outros
 *
 * @author Edinalvo Rosa
 */
function selecionaRegistrosFrequenciaDoServidor($siape,$competencia)
{
    $oDBase = new DataBase();

    $oDBase->query("
    SELECT
        servativ.nome_serv AS nome,
        tabsetor.inicio_atend,
        tabsetor.fim_atend,
        tabsetor.codmun,
        tabsetor.cod_uorg,
        tabsetor.upag,
        pto.entra,
        DATE_FORMAT(pto.dia,'%d/%m/%Y') AS dia,
        pto.intini,
        pto.intsai,
        pto.sai,
        pto.jornd,
        pto.jornp,
        pto.jorndif,
        pto.oco,
        pto.just,
        pto.justchef,
        pto.idreg,
        tabocfre.desc_ocorr AS dcod,
        tabsetor.codmun,
        tabsetor.codigo,
        pto.idreg,
        pto.ip,
        pto.matchef,
        pto.siaperh,
        DATE_FORMAT(servativ.dt_adm,'%d/%m/%Y') AS dt_adm,
        tabsetor.descricao,
        taborgao.denominacao,
        taborgao.sigla,
        servativ.cod_sitcad,
        servativ.sigregjur
    FROM
        ponto$competencia AS pto
    LEFT JOIN
        tabocfre ON pto.oco = tabocfre.siapecad
    LEFT JOIN
        servativ ON pto.siape = servativ.mat_siape
    LEFT JOIN
        tabsetor ON servativ.cod_lot = tabsetor.codigo
    LEFT JOIN
        taborgao ON LEFT(tabsetor.codigo,5) = taborgao.codigo
    WHERE
        pto.siape = :siape
        AND pto.dia <> '0000-00-00'
    ORDER BY
        pto.dia
    ", array(
        array(':siape', $siape, PDO::PARAM_STR)
    ));

    return $oDBase;
}


/* @info Prepara o saldo de horas do dia para exibir
 *
 * @param  object  $pm_partners  Dados da frequência do s ervidor
 * @return  string  Ocorrência com padrãoes de cor e tipo de fonte
 *
 * @author Edinalvo Rosa
 */
function preparaSaldoParaExibir($pm_partners)
{
    global $codigosCompensaveis, $PagarEmFolha, $CodigosCredito;

    // define tipo, cor de fonte para algumas ocorrências
    $font_i_color = "";
    $sinal        = '&nbsp;';
    $font_f_color = "";

    // elimina "/" e ":", depois define o tipo como inteiro
    // para garantir a resultado do teste a seguir
    $jornada_dif = alltrim(sonumeros($pm_partners->jorndif), '0');
    settype($jornada_dif, 'integer');

    if (!empty($jornada_dif) && in_array($pm_partners->oco,$codigosCompensaveis))
    {
        $font_i_color = "<font color='red'>";
        $font_f_color = "</font>";
        $sinal        = "<font color='red'> - </font>";
    }
    else if (!empty($jornada_dif) && in_array($pm_partners->oco, $PagarEmFolha))
    {
        $font_i_color = "<font color='red'>(";
        $font_f_color = ")</font>";
        $sinal        = "";
    }
    else if (!empty($jornada_dif) && in_array($pm_partners->oco, $CodigosCredito))
    {
        $sinal = " + ";
    }

    return $sinal . ' ' . $font_i_color . $pm_partners->jorndif . $font_f_color;
}


/* @info Prepara o código da ocorrênciao para exibir
 *
 * @param  object  $pm_partners  Dados da frequência do servidor
 * @return  string  Ocorrência com padrãoes de cor e tipo de fonte
 *
 * @author Edinalvo Rosa
 */
function preparaOcorrenciaParaExibir($pm_partners)
{
    global $codigosTrocaObrigatoria;

    if (in_array($pm_partners->oco, $codigosTrocaObrigatoria))
    {
        $codigo_da_ocorrencia = "<font color='red'><b>" . $pm_partners->oco . "</b></font>";
    }
    else
    {
        $codigo_da_ocorrencia = $pm_partners->oco;
    }

    return $codigo_da_ocorrencia;
}


/* @info Retorna link para ver justificativa se houver
 *
 * @param  object  $pm_partners  Dados da frequência do servidor
 * @param  string  $codigo_da_ocorrencia
 *                 Retorna o link com a jsutificativa
 *                 ou somente o código da ocorrência
 * @return  string  Link para ver justificativa
 *
 * @author Edinalvo Rosa
 */
function indicaSeHaJustificativaRegistrada($pm_partners, $codigo_da_ocorrencia)
{
    if ($pm_partners->just != "")
    {
        $justificativa_value = "
        <img border= '0' src='imagem/arrow.gif' width='7' height='7' align='absmiddle'>
        &nbsp;&nbsp;<a href=\"javascript:verJustificativa('"
        . preparaTextArea($pm_partners->just)
        . "');\" title=\""
        . $pm_partners->dcod . "\n"
        . $pm_partners->just . "\" alt=\""
        . $pm_partners->dcod . "\n"
        . preparaTextArea($pm_partners->just)
        . "\" style='color: #404040;'>"
        . $codigo_da_ocorrencia . "</a>";
    }
    else
    {
        $justificativa_value = "
        <div title=\"" . $pm_partners->dcod . "\" alt=\""
        . $pm_partners->dcod
        . "\" style='color: #404040;'>
        <img border= '0' src='imagem/ativar_off.gif' width='7' height='7'
        align='absmiddle'>&nbsp;&nbsp;" . $codigo_da_ocorrencia . "</div>";
    }

    return $justificativa_value;
}


/* @info Retorna link para Alteração
 *
 * @param  string  $siape        Matrícula do servidor
 * @param  object  $pm_partners  Dados da frequência do servidor
 * @return  string  Link para alteração
 *
 * @author Edinalvo Rosa
 */
function acaoLinkFrequenciaAlterar($siape, $pm_partners)
{
    global $sNome, $lot, $jnd, $cod_sitcad;

    // matricula, nome, dia, ocorrência, lotação,
    // identificacao do registrador, cmd, jornada,
    // so ver justificativa, situação cadastral e homologacao registros
    $frequencia_alterar = base64_encode($siape . ':|:' . $sNome . ':|:' . $pm_partners->dia . ':|:' . $pm_partners->oco . ':|:' . $lot . ':|:' . $pm_partners->idreg . ':|:2:|:' . $jnd . ':|:' . $cod_sitcad . ':|:rh_mes_homologacao');

    $acao_alterar = "<a href=\"javascript:window.location.replace('frequencia_alterar.php?dados=" . $frequencia_alterar . "');\" style='color: #404040;'>Alterar</a>";

    return $acao_alterar;
}


/* @info Retorna link para Abonar
 *
 * @param  object  $pm_partners    Dados da frequência do servidor
 * @param  string  $justificativa  Justificativa do servidor
 * @return  string  Link para abonar ocorrência
 *
 * @author Edinalvo Rosa
 */
function acaoLinkAbonar($pm_partners, $justificativa)
{
    global $codigosPassisveisDeAbono;

    if (in_array($pm_partners->oco, $codigosPassisveisDeAbono))
    {
        $acao_abonar = "<a href=\"javascript:window.location.replace('frequencia_justificativa_abono.php?dados=" . $justificativa . "');\" style='color: #404040;'>Abonar</a>";
    }
    else
    {
        $acao_abonar = "<a href='javascript:return false;' style='cursor: none; text-decoration: none; color: #f8f8f8;' disabled>Abonar</a>";
    }

    return $acao_abonar;
}


/* @info Retorna link para Exclusão
 *
 * @param  string  $siape         Matrícula do servidor
 * @param  object  $pm_partners   Dados da frequência do servidor
 * @param  string  $dia_nao_util  Dia para exibir
 * @return  string  Link para exclusão
 *
 * @author Edinalvo Rosa
 */
function acaoLinkExcluir($siape, $pm_partners, $dia_nao_util)
{
    $dia = strtr($dia_nao_util, array('<font color=red><b>' => '', ' </b></font>' => ''));

//    if ($dia != '')
//    {
        $acao_excluir = "<a href=\"javascript:window.location.replace("
            . "'frequencia_excluir.php?dados="
            . base64_encode($siape . ":|:" . $pm_partners->dia . ':|:rh_mes_homologacao')
            . "');\" style='color: #404040;'>Excluir</a>";
//    }
//    else
//    {
//        $acao_excluir = "<a href='javascript:void(0);' "
//            . "style='cursor: none; text-decoration: none; "
//            . "color: #f8f8f8;' disabled>Excluir</a>";
//    }

    return $acao_excluir;
}
