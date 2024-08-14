<?php

include_once( "config.php" );
include_once( "class_form.frequencia.php" );
include_once( "class_ocorrencias_grupos.php" );
include_once( "src/controllers/DadosServidoresController.php" );
include_once( "src/controllers/TabSetoresController.php" );
include_once( "src/controllers/TabPontoController.php" );

verifica_permissao('sAPS');

// instancia classes
$oDadosServidores     = new DadosServidoresController();
$oRegistrosFrequencia = new TabPontoController();
$oGrupoOcorrencias    = new OcorrenciasGrupos();

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

// competencia
$data        = new trata_datasys();
$dia         = '01';
$ano         = $data->getAnoHomologacao();
$mes         = $data->getMesHomologacao();
$competencia = $data->getCompetHomologacao(); // mes e ano, ex.: 032010

$comp = $mes;
$year = $ano;

// mes e ano homologa��o
$competencia     = $mes . $ano; //$oData->getCompetHomologacao();
$nome_do_arquivo = 'ponto' . $competencia;

$mes_homologacao = true;

// Dados do servidor
// seleciona registros cadastro
$pm_partners = $oDadosServidores->selecionaRegistroPorMatricula($mat);
$sNome        = $pm_partners->nome;
$horario_do_setor_inicio = $pm_partners->inicio_atend;
$horario_do_setor_fim    = $pm_partners->fim_atend;
$codigo_do_municipio     = $pm_partners->codmun;
$uorg                    = $pm_partners->cod_uorg;
$upag                    = $pm_partners->upag;
$anomes_admissao         = $pm_partners->dt_adm;
$lotacao                 = $pm_partners->codigo;
$lotacao_descricao       = $pm_partners->descricao;
$orgao_sigla             = $pm_partners->sigla;
$cod_sitcad              = $pm_partners->cod_sitcad;
$sigregjur               = $pm_partners->sigregjur;

// jornada do servidor
$oJornada            = new DefinirJornada();
$oDBaseJH            = $oJornada->PesquisaJornadaHistorico($mat, '01/' . $mes . '/' . $ano);
$oHorario            = $oDBaseJH->fetch_object();
$entrada_no_servico  = $oHorario->entra_trab;
$saida_para_o_almoco = $oHorario->ini_interv;
$volta_do_almoco     = $oHorario->sai_interv;
$saida_do_servico    = $oHorario->sai_trab;
$jnd                 = $oHorario->jornada;

// grupo de ocorrencias
$codigoRegistroParcialPadrao      = $oGrupoOcorrencias->CodigoRegistroParcialPadrao($sigregjur);
$codigoSemFrequenciaPadrao        = $oGrupoOcorrencias->CodigoSemFrequenciaPadrao($sigregjur);
$grupoOcorrenciasNegativasDebitos = $oGrupoOcorrencias->GrupoOcorrenciasNegativasDebitos($sigregjur);
$pagarEmFolha                     = $oGrupoOcorrencias->PagarEmFolha($sigregjur);
$codigosCredito                   = $oGrupoOcorrencias->CodigosCredito($sigregjur);
$codigosTrocaObrigatoria          = $oGrupoOcorrencias->CodigosTrocaObrigatoria($sigregjur);
$grupoOcorrenciasPassiveisDeAbono = $oGrupoOcorrencias->GrupoOcorrenciasPassiveisDeAbono($sigregjur);

$dias_no_mes = numero_dias_do_mes($mes, $ano);

inserir_dias_sem_frequencia($mat, $dias_no_mes, $mes, $ano, $jornada, $lot, $nome_do_arquivo, $anomes_admissao);

$ocorrencias_88888  = 0;
$ocorrencias_99999  = 0;
$ocorrencias_tracos = 0;

$registrosComparecimentoOcorrencia = array();

$umavez = true;

$atribuido = false;

// seleciona registros da frequ�ncia e dados do servidor
$oDBase = $oRegistrosFrequencia->selecionaRegistrosFrequenciaDoServidor($mat, $competencia);

while ($pm_partners = $oDBase->fetch_object())
{
    if (in_array($pm_partners->oco, $codigoRegistroParcialPadrao))
    {
        $ocorrencias_88888++;
    }

    if (in_array($pm_partners->oco, $codigoSemFrequenciaPadrao))
    {
        $ocorrencias_99999++;
    }

    if ($pm_partners->oco == $codigosTrocaObrigatoria[0])
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

    // matricula, dia, situa��o cadastral, cmd,
    // so ver justificativa e homologar
    $justificativa = base64_encode(
        $mat . ":|:" .
        $pm_partners->dia . ':|:' .
        $cod_sitcad . ":|:2:|::|:homologar"
    );

    ## A��o: ALTERAR OCORR�NCIA (VER JUSTIFICATIVA)
    #
    $justificativa_value = indicaSeHJustificativaRegistrada($pm_partners, $codigo_da_ocorrencia);

    ## A��o: ALTERAR
    #
    $acao_alterar = acaoLinkFrequenciaAlterar($mat,$pm_partners);


    ## A��o: ABONAR
    #
    $acao_abonar = acaoLinkAbonar($pm_partners, $justificativa);


    ## A��o: EXCLUIR
    #
    $acao_excluir = acaoLinkExcluir($mat, $pm_partners, $dia_nao_util[$xdia][2]);


    // Registros tratados para exibi��o
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


// verifica��o dos dados
$registroParcialPadrao = implode(', ', $codigoRegistroParcialPadrao);
$semFrequenciaPadrao   = implode(', ', $codigoSemFrequenciaPadrao);
$trocaObrigatoria      = $codigosTrocaObrigatoria[0];

$avisos_mensagens = "<tr><td colspan='5'>";

if ($ocorrencias_88888 > 0 || $ocorrencias_99999 > 0 || $ocorrencias_tracos > 0)
{
    $avisos_mensagens .= "<div class='text-center' style='color:red;'><b>ATEN��O:</b> ";

    if ($ocorrencias_88888 > 0 && $ocorrencias_99999 > 0 && $ocorrencias_tracos > 0)
    {
        $avisos_mensagens .= "�� H� ocorr�ncia(s) com c�digo " . $registroParcialPadrao . ", " . $semFrequenciaPadrao . " e \"" . $trocaObrigatoria . "\" na ficha do servidor ��";
    }
    else if ($ocorrencias_88888 > 0 && $ocorrencias_99999 > 0)
    {
        $avisos_mensagens .= "�� H� ocorr�ncia(s) com c�digo " . $registroParcialPadrao . " e " . $semFrequenciaPadrao . " na ficha do servidor ��";
    }
    else if ($ocorrencias_88888 > 0 && $ocorrencias_tracos > 0)
    {
        $avisos_mensagens .= "�� H� ocorr�ncia(s) com c�digo " . $registroParcialPadrao . " e \"" . $trocaObrigatoria . "\" na ficha do servidor ��";
    }
    else if ($ocorrencias_99999 > 0 && $ocorrencias_tracos > 0)
    {
        $avisos_mensagens .= "�� H� ocorr�ncia(s) com c�digo " . $semFrequenciaPadrao . " e \"" . $trocaObrigatoria . "\" na ficha do servidor ��";
    }
    else if ($ocorrencias_tracos > 0)
    {
        $avisos_mensagens .= "�� H� ocorr�ncia(s) com c�digo \"" . $trocaObrigatoria . "\" na ficha do servidor ��";
    }
    else if ($ocorrencias_88888 > 0)
    {
        $avisos_mensagens .= "�� H� ocorr�ncia(s) com c�digo " . $registroParcialPadrao . " na ficha do servidor ��";
    }
    else if ($ocorrencias_99999 > 0)
    {
        $avisos_mensagens .= "�� H� ocorr�ncia(s) com c�digo " . $semFrequenciaPadrao . " na ficha do servidor ��";
    }

    $avisos_mensagens .= "</div>";
} // fim do if

$avisos_mensagens .= "</td></tr>";

$destino_voltar = ($_SESSION["sLancarExcessao"] == "S" ? $_SESSION['voltar_nivel_1'] : $_SESSION['voltar_nivel_0']);


## classe para montagem do formulario padrao
#
$oForm         = new formPadrao();
$titulo_pagina = 'Homologa��o de Registro de Frequ�ncia do M�s Anterior';
$title         = _SISTEMA_SIGLA_ . ' | ' . $titulo_pagina;

$acao_manutencao = "homologar";

// ordena tabela
$css = array();

$javascript   = array();
$javascript[] = _DIR_JS_ . 'phpjs.js';
$javascript[] = 'frequencia_homologar_registros.js?v.0.0.0.0.2';

// Topo do formul�rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML($width='1540px;');

include_once "html/form-frequencia-manutencao.php";

// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

exit();



/* ************************************************ *
 *                                                  *
 *              FUN��ES COMPLEMENTARES              *
 *                                                  *
 * ************************************************ */

/* @info Prepara o saldo de horas do dia para exibir
 *
 * @param  object  $pm_partners  Dados da frequ�ncia do s ervidor
 * @return  string  Ocorr�ncia com padr�oes de cor e tipo de fonte
 *
 * @author Edinalvo Rosa
 */
function preparaSaldoParaExibir($pm_partners)
{
    global $grupoOcorrenciasNegativasDebitos, $pagarEmFolha, $codigosCredito;

    // define tipo, cor de fonte para algumas ocorr�ncias
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
    else if (!empty($jornada_dif) && in_array($pm_partners->oco, $pagarEmFolha))
    {
        $font_i_color = "<font color='red'>(";
        $font_f_color = ")</font>";
        $sinal        = "";
    }
    else if (!empty($jornada_dif) && in_array($pm_partners->oco, $codigosCredito))
    {
        $sinal = " + ";
    }

    return $sinal . ' ' . $font_i_color . $pm_partners->jorndif . $font_f_color;
}


/* @info Prepara o c�digo da ocorr�nciao para exibir
 *
 * @param  object  $pm_partners  Dados da frequ�ncia do servidor
 * @return  string  Ocorr�ncia com padr�oes de cor e tipo de fonte
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
 * @param  object  $pm_partners  Dados da frequ�ncia do servidor
 * @param  string  $codigo_da_ocorrencia
 *                 Retorna o link com a jsutificativa
 *                 ou somente o c�digo da ocorr�ncia
 * @return  string  Link para ver justificativa
 *
 * @author Edinalvo Rosa
 */
function indicaSeHJustificativaRegistrada($pm_partners, $codigo_da_ocorrencia)
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


/* @info Retorna link para Altera��o
 *
 * @param  string  $siape        Matr�cula do servidor
 * @param  object  $pm_partners  Dados da frequ�ncia do servidor
 * @return  string  Link para altera��o
 *
 * @author Edinalvo Rosa
 */
function acaoLinkFrequenciaAlterar($siape, $pm_partners)
{
    global $sNome, $lot, $jnd, $cod_sitcad;

    // matricula, nome, dia, ocorr�ncia, lota��o,
    // identificacao do registrador, cmd, jornada,
    // so ver justificativa, situa��o cadastral e homologar registros
    $frequencia_alterar = base64_encode($siape . ':|:' . $sNome . ':|:' . $pm_partners->dia . ':|:' . $pm_partners->oco . ':|:' . $lot . ':|:' . $pm_partners->idreg . ':|:2:|:' . $jnd . ':|:' . $cod_sitcad . ':|:homologar_registros');

    $acao_alterar = "<a href=\"javascript:window.location.replace('frequencia_alterar.php?dados=" . $frequencia_alterar . "');\" style='color: #404040;'>Alterar</a>";

    return $acao_alterar;
}


/* @info Retorna link para Abonar
 *
 * @param  object  $pm_partners    Dados da frequ�ncia do servidor
 * @param  string  $justificativa  Justificativa do servidor
 * @return  string  Link para abonar ocorr�ncia
 *
 * @author Edinalvo Rosa
 */
function acaoLinkAbonar($pm_partners, $justificativa)
{
    global $grupoOcorrenciasPassiveisDeAbono;

    if (in_array($pm_partners->oco, $grupoOcorrenciasPassiveisDeAbono))
    {
        $acao_abonar = "<a href=\"javascript:window.location.replace('frequencia_justificativa_abono.php?dados=" . $justificativa . "');\" style='color: #404040;'>Abonar</a>";
    }
    else
    {
        $acao_abonar = "<a href='javascript:return false;' style='cursor: none; text-decoration: none; color: #f8f8f8;' disabled>Abonar</a>";
    }

    return $acao_abonar;
}


/* @info Retorna link para Exclus�o
 *
 * @param  string  $siape         Matr�cula do servidor
 * @param  object  $pm_partners   Dados da frequ�ncia do servidor
 * @param  string  $dia_nao_util  Dia para exibir
 * @return  string  Link para exclus�o
 *
 * @author Edinalvo Rosa
 */
function acaoLinkExcluir($siape, $pm_partners, $dia_nao_util)
{
    $dia = strtr($dia_nao_util, array('<font color=red><b>' => '', ' </b></font>' => ''));

    if ($dia != '')
    {
        $acao_excluir = "<a href=\"javascript:window.location.replace("
            . "'frequencia_excluir.php?dados="
            . base64_encode($siape . ":|:" . $pm_partners->dia . ':|:homologar')
            . "');\" style='color: #404040;'>Excluir</a>";
    }
    else
    {
        $acao_excluir = "<a href='javascript:void(0);' "
            . "style='cursor: none; text-decoration: none; "
            . "color: #f8f8f8;' disabled>Excluir</a>";

    }

    return $acao_excluir;
}
