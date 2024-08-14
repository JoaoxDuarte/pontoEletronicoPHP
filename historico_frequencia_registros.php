<?php

// Inicia a sessão e carrega as funções de uso geral
include_once( "config.php");
include_once("class_ocorrencias_grupos.php");
include_once("src/controllers/DadosServidoresController.php");
include_once("src/controllers/TabSetoresController.php");
include_once("src/controllers/TabPontoController.php");

// class formulario
include_once("class_form.frequencia.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH e sTabServidor');

if (!empty($_GET['importafastamentos']))
{
    $result = updateAfastamentosBySiape($_GET['siape'],$_GET['grupo'],$_GET['compet']);
    echo json_encode(array("success" => $result));
    die;
}

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    $dados = explode(":|:", base64_decode($dadosorigem));
    $ano_hoje = date('Y');
    $usuario = $dados[1];
    $siape = $dados[2];
    $mes = $dados[3];
    $ano = $dados[4];
    $siape_responsavel = $dados[5];
}


// instancia classes
$dadosServidoresController = new DadosServidoresController();
$oRegistrosFrequencia      = new TabPontoController();
$oGrupoOcorrencias         = new OcorrenciasGrupos();
$oRegistro                 = new stdClass();

// seleciona registros cadastro
$sigregjur = $dadosServidoresController->selecionaRegistroPorMatricula($siape);

// grupo de ocorrencias
$codigoRegistroParcialPadrao      = $oGrupoOcorrencias->CodigoRegistroParcialPadrao($sigregjur);
$codigoSemFrequenciaPadrao        = $oGrupoOcorrencias->CodigoSemFrequenciaPadrao($sigregjur);
$grupoOcorrenciasNegativasDebitos = $oGrupoOcorrencias->GrupoOcorrenciasNegativasDebitos($sigregjur);
$pagarEmFolha                     = $oGrupoOcorrencias->PagarEmFolha($sigregjur);
$codigosCredito                   = $oGrupoOcorrencias->CodigosCredito($sigregjur);
$codigosTrocaObrigatoria          = $oGrupoOcorrencias->CodigosTrocaObrigatoria($sigregjur);
$grupoOcorrenciasPassiveisDeAbono = $oGrupoOcorrencias->GrupoOcorrenciasPassiveisDeAbono($sigregjur);


// verificação dos dados
$registroParcialPadrao = implode(', ', $codigoRegistroParcialPadrao);
$semFrequenciaPadrao   = implode(', ', $codigoSemFrequenciaPadrao);
$trocaObrigatoria      = $codigosTrocaObrigatoria[0];


// nome do servidor do RH (usuário logado)
$oRegistro->servidor_rh      = $dadosServidoresController->getNomeServidor( $_SESSION['sMatricula'] );
$oRegistro->nome_responsavel = $dadosServidoresController->getNomeServidor( $siape_responsavel );
$anomes_admissao             = $dadosServidoresController->getDataAdmissaoDoServidor( $siape );

$acao_manutencao = "historico_manutencao";


// dados voltar
$_SESSION['voltar_nivel_1'] = $dadosorigem;
$_SESSION['voltar_nivel_2'] = '';
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';

$_SESSION['historico_manutencao'] = $dadosorigem;


// instancia o banco de dados
$oDBase = new DataBase();
$oDBase->setDestino('historico_frequencia.php');

// texto (observacao)
$oDBase->setMensagem("Falha na leitura da observação (histórico)");
$oDBase->query("SELECT observacao, siaperh, DATE_FORMAT(registrado_em,'%d/%m/%Y %H:%m') AS registrado_em FROM historico_observacoes WHERE siape = '" . $siape . "' AND compet = '" . $ano . $mes . "' ");
$oObservacao = $oDBase->fetch_object();

$observacao = trata_aspas($oObservacao->observacao);

// verifica se já existe o siape e
// data de registro no texto da observação
// mantemos o horario registrado antes
$autoria_registro = "- Alteração solicitada por: " . $oRegistro->nome_responsavel . " (" . $siape_responsavel . "), registro realizado por: " . $oRegistro->servidor_rh . " (" . $_SESSION['sMatricula'] . "). Em: " . date('d/m/Y, H:i:s');

if (substr_count(ltrim(rtrim($observacao)), $autoria_registro) == 0)
{
    $observacao = $observacao . $autoria_registro . ".\n";
}

// validacao dos campos
$validar = new valida();
$validar->setExibeMensagem(true);
$validar->setDestino('historico_frequencia.php');
$validar->siape($siape);   // se matrícula inválida retorna para destino
$validar->siaperh($siape); // o usuário não pode alterar sua própria frequência, retorna para destino
$validar->mes($mes); // se mes inválido retorna para destino
$validar->ano($ano); // se ano inválido retorna para destino
$validar->upagrh($_SESSION['upag']); // se upag diferente do usuario retorna para destino
// testa se o mes/ano informado é igual ao
// mes/ano corrente ou o imediatamente anterior
// e exibe mensagem de erro se igual
// Ex.: - Mês e ano atual: 10/2010
//      - Mês e ano imediatamente anterior: 09/2010
//      - Mês e ano informado: 10/2010
//      Exibe mensagem de erro, pois a competência informada é igual a competência corrente.
$oData = new trata_datasys();
$sMes = $oData->getMesHomologacao(); // mes da homologação
$sAno = $oData->getAnoHomologacao(); // ano da homologação
$sAnoMes = $sAno . $sMes;

$sCompInv = $ano . $mes;

if ($sCompInv < 200910 || $sCompInv >= $sAnoMes)
{
    $validar->setMensagem( "A inclusão de ocorrências no histórico deverá ser utilizada apenas para competências de 10/2009 em diante, limitando-se sempre ao mês anterior ao da homologação (hoje: " . $sMes . '/' . $sAno . ").", 'danger' );
}

$mensagem = $validar->getMensagem();
if ( !empty($mensagem) )
{
    setMensagemUsuario( $validar->getMensagem(), 'danger' );
    replaceLink('historico_frequencia.php');
}


## instancia classe frequencia
# inserir feriados e
# carregar dados do servidor
#
$oForm = new formFrequencia;
$oForm->setOrigem("historico_frequencia.php", 1);
//$oForm->setLotacao( $lot );     // lotação do servidor que se deseja alterar a frequencia
$oForm->setSiape($siape);       // matricula do servidor que se deseja alterar a frequencia
$oForm->setDia('01');         // dia que se inicia a verificação para incluir frequencia
$oForm->setMes($mes); // mes que se deseja incluir frequencia
$oForm->setAno($ano); // ano que se deseja incluir frequencia
$oForm->setNomeDoArquivo($_SESSION['sHArquivoTemp']); // nome do arquivo de trabalho
$oForm->loadDadosServidor();
$oForm->loadDadosSetor();
$oForm->pontoFacultativo();
$oForm->verificaSeDiaUtil();
$oForm->inserirDiasSemFrequencia(true);

$sNome   = $oForm->getNome();
$lot     = $oForm->getLotacao();
$lotacao = $oForm->getLotacao();
$entra   = $oForm->getCadastroEntrada();
$iniin   = $oForm->getCadastroInicioIntervalo();
$fimin   = $oForm->getCadastroFimIntervalo();
$sai     = $oForm->getCadastroSaida();
$jnd     = $oForm->getJornada();

$iniat   = $oForm->getInicioAtendimento();
$fimat   = $oForm->getFimAtendimento();
$cmun    = $oForm->getCodigoMunicipio();

$uorg    = $oForm->getSetorUorg();
$upag    = $oForm->getSetorUpag();

$umavez = true;


$oDBase = $oRegistrosFrequencia->registrosPorSiapeHistorico($siape);
$linhas = $oDBase->num_rows();

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

    if (in_array($pm_partners->oco, $codigosTrocaObrigatoria))
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
    // so ver justificativa 
    $justificativa = base64_encode(
        $siape . ":|:" .
        $pm_partners->dia . ':|:' .
        $cod_sitcad . ":|:2:|::|:" . $acao_manutencao
    );

    ## Ação: ALTERAR OCORRÊNCIA (VER JUSTIFICATIVA)
    #
    $justificativa_value = indicaSeHJustificativaRegistrada($pm_partners, $codigo_da_ocorrencia);

    ## Ação: ALTERAR
    #
    $acao_alterar = acaoLinkFrequenciaAlterar($siape,$pm_partners);


    ## Ação: ABONAR
    #
    $acao_abonar = acaoLinkAbonar($pm_partners, $justificativa);


    ## Ação: EXCLUIR
    #
    $acao_excluir = acaoLinkExcluir($siape, $pm_partners, $dia_nao_util[$xdia][2]);


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
        $avisos_mensagens .= "»» Há ocorrência(s) com código " . $registroParcialPadrao . " na ficha do servidor ««";
    }
    else if ($ocorrencias_99999 > 0)
    {
        $avisos_mensagens .= "»» Há ocorrência(s) com código " . $semFrequenciaPadrao . " na ficha do servidor ««";
    }

    $avisos_mensagens .= "</div>";
} // fim do if

$avisos_mensagens .= "</td></tr>";

$destino_voltar = ($_SESSION["sLancarExcessao"] == "S" ? $_SESSION['voltar_nivel_1'] : $_SESSION['voltar_nivel_0']);


## classe para montagem do formulario padrao
#
$oForm         = new formPadrao();
$titulo_pagina = "Hist&oacute;rico - Manuten&ccedil;&atilde;o de Ocorr&ecirc;ncia";
$title         = _SISTEMA_SIGLA_ . ' | ' . $titulo_pagina;

// ordena tabela
$css = array();

$javascript   = array();
$javascript[] = _DIR_JS_ . 'phpjs.js';
$javascript[] = 'historico_frequencia_registros.js?v.0.0.0.1.23';

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

/* @info Prepara o saldo de horas do dia para exibir
 *
 * @param  object  $pm_partners  Dados da frequência do s ervidor
 * @return  string  Ocorrência com padrãoes de cor e tipo de fonte
 *
 * @author Edinalvo Rosa
 */
function preparaSaldoParaExibir($pm_partners)
{
    global $grupoOcorrenciasNegativasDebitos, $pagarEmFolha, $codigosCredito;

    // define tipo, cor de fonte para algumas ocorrências
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
    global $sNome, $lot, $jnd, $cod_sitcad, $acao_manutencao;

    // matricula, nome, dia, ocorrência, lotação,
    // identificacao do registrador, cmd, jornada,
    // so ver justificativa, situação cadastral e registros
    $frequencia_alterar = base64_encode($siape . ':|:' . $sNome . ':|:' . $pm_partners->dia . ':|:' . $pm_partners->oco . ':|:' . $lot . ':|:' . $pm_partners->idreg . ':|:2:|:' . $jnd . ':|:' . $cod_sitcad . ':|:' . $acao_manutencao);

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
    global $acao_manutencao;
    
    $dia = strtr($dia_nao_util, array('<font color=red><b>' => '', ' </b></font>' => ''));

    if ($dia != '')
    {
        $acao_excluir = "<a href=\"javascript:window.location.replace("
            . "'frequencia_excluir.php?dados="
            . base64_encode($siape . ":|:" . $pm_partners->dia . ':|:'.$acao_manutencao)
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
?>