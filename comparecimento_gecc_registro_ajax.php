<?php
include_once("config.php");
include_once("comparecimento_gecc_funcoes.php");

verifica_permissao("sRH ou Chefia");

$tipo = "danger";

// Valores passados - encriptados
$dadosorigem = $_POST['dados'];

if (empty($dadosorigem))
{
    $mensagem = 'Dados não informados!';
    retornaInformacao($mensagem,$tipo);
    exit();
}
else
{
    $dados  = descriptografa($dadosorigem);
    $campos = args2array($dados);

    $siape      = $campos['siape'];
    $id         = $campos['id'];
    $setor      = $campos['setor'];
    $start_date = $campos['data_ini'];
    $end_date   = $campos['data_fim'];
    $start_hora = $campos['hora_ini'];
    $end_hora   = $campos['hora_fim'];
    $horas      = $campos['horas'];
    $documento  = $campos['documento'];
    $acrescimo  = $campos['acrescimo_autorizado'];
}

$ano_gecc = dataAno($start_date); // date('Y');

// verifica se as datas já estão cadastradas
$datas = verificaSePeriodoGECCJaCadastrado($siape, $start_date, $end_date, $start_hora, $end_hora);


/* ***************************** *
 * DATA INICIAL                  *
 * ***************************** */

// Verifica se a data inicial é válida
if (validaData($start_date) === false){
    $mensagem = 'Data Inicial é inválida!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se data inicial esta no ano corrente
if (dataAno($start_date) > $ano_gecc){
    $mensagem = 'Data Inicial precisa estar dentro do ano corrente!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se data inicial já está cadastrada
// para este servidor, caso seja uma inclusão
if (empty($id) && $datas->inicio_cadastrado == 'S')
{
    $mensagem = 'Data Inicial já cadastrada para este servidor, em outro período!';
    retornaInformacao($mensagem,$tipo);
    exit();
}


/* ***************************** *
 * DATA FINAL                    *
 * ***************************** */

// Verifica se a data final é válida
if (validaData($end_date) === false){
    $mensagem = 'Data Final é inválida!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se data final esta no ano corrente
if (dataAno($end_date) > $ano_gecc){
    $mensagem = 'Data Final precisa estar dentro do ano corrente!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se data final já está cadastrada
// para este servidor, caso seja uma inclusão
if (empty($id) && $datas->fim_cadastrado == 'S')
{
    $mensagem = 'Data Final já cadastrada para este servidor, em outro período!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se a data inicial é maior que a data final
if (inverteData($start_date) > inverteData($end_date)) {
    $mensagem = 'Data Inicial não pode ser maior que a Data Final!';
    retornaInformacao($mensagem,$tipo);
    exit();
}


/* ***************************** *
 * HORÁRIOS                      *
 * ***************************** */
$limites = configLimitesGECC();

// Verifica se o horário inicial é válido
if (validaHoras($start_hora) == false) {
    $mensagem = 'Horário Inicial é inválido!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se o horário inicial está no limite definido
if (time_to_sec($start_hora) < time_to_sec($limites['entrada'])){
    $mensagem = 'Horário Inicial é menor que '.$limites['entrada'].'!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se o horário final é válido
if (validaHoras($end_hora) == false) {
    $mensagem = 'Horário Final é inválido!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se o horário final está no limite definido
if (time_to_sec($end_hora) > time_to_sec($limites['saida'])){
    $mensagem = 'Horário Final é maior que '.$limites['saida'].'!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se o horário inicial é válido
if (validaHoras($start_hora) == false) {
    $mensagem = 'Horário Inicial é inválido!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se o horário inicial é válido
if (time_to_sec($start_hora) >= time_to_sec($end_hora)){
    $mensagem = 'Horário Inicial maior que Final!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se o horário inicial é válido
if (empty(trim($documento))){
    $mensagem = 'Documento não informado!';
    retornaInformacao($mensagem,$tipo);
    exit();
}


/* ***************************** *
 * PERÍODOS                      *
 * ***************************** */

// verifica se datas já constam em outros períodos
$mensagem = verificaGECCNoPeriodo($siape,$start_date,$end_date,time_to_sec($horas),$acrescimo);
if ($mensagem != "")
{
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Vefifica se o documento foi informado.
if ($acrescimo == "S" && (empty(trim($documento)) || strlen(trim($documento)) < 10)){
    $mensagem = 'Informe o documento, com no mínimo 10 caracteres!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// registra em banco de dados
if (createAutorizacaoGECC($campos) == true)
{
    registraLog("Realizado registro de Gratificação por Encargo de Curso ou Concurso para o servidor matrícula ".$matricula.".");
    $mensagem = 'Realizado registro de Gratificação por Encargo de Curso ou Concurso!';
    retornaInformacao($mensagem,'success');
}
else
{
    $mensagem = 'Registro de Gratificação por Encargo de Curso ou Concurso, NÃO realizado!';
    //$mensagem = 'Tempo da consulta inválido!';
    retornaInformacao($mensagem,$tipo);
}

exit();
