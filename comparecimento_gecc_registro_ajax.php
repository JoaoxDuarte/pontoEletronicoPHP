<?php
include_once("config.php");
include_once("comparecimento_gecc_funcoes.php");

verifica_permissao("sRH ou Chefia");

$tipo = "danger";

// Valores passados - encriptados
$dadosorigem = $_POST['dados'];

if (empty($dadosorigem))
{
    $mensagem = 'Dados n�o informados!';
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

// verifica se as datas j� est�o cadastradas
$datas = verificaSePeriodoGECCJaCadastrado($siape, $start_date, $end_date, $start_hora, $end_hora);


/* ***************************** *
 * DATA INICIAL                  *
 * ***************************** */

// Verifica se a data inicial � v�lida
if (validaData($start_date) === false){
    $mensagem = 'Data Inicial � inv�lida!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se data inicial esta no ano corrente
if (dataAno($start_date) > $ano_gecc){
    $mensagem = 'Data Inicial precisa estar dentro do ano corrente!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se data inicial j� est� cadastrada
// para este servidor, caso seja uma inclus�o
if (empty($id) && $datas->inicio_cadastrado == 'S')
{
    $mensagem = 'Data Inicial j� cadastrada para este servidor, em outro per�odo!';
    retornaInformacao($mensagem,$tipo);
    exit();
}


/* ***************************** *
 * DATA FINAL                    *
 * ***************************** */

// Verifica se a data final � v�lida
if (validaData($end_date) === false){
    $mensagem = 'Data Final � inv�lida!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se data final esta no ano corrente
if (dataAno($end_date) > $ano_gecc){
    $mensagem = 'Data Final precisa estar dentro do ano corrente!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se data final j� est� cadastrada
// para este servidor, caso seja uma inclus�o
if (empty($id) && $datas->fim_cadastrado == 'S')
{
    $mensagem = 'Data Final j� cadastrada para este servidor, em outro per�odo!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se a data inicial � maior que a data final
if (inverteData($start_date) > inverteData($end_date)) {
    $mensagem = 'Data Inicial n�o pode ser maior que a Data Final!';
    retornaInformacao($mensagem,$tipo);
    exit();
}


/* ***************************** *
 * HOR�RIOS                      *
 * ***************************** */
$limites = configLimitesGECC();

// Verifica se o hor�rio inicial � v�lido
if (validaHoras($start_hora) == false) {
    $mensagem = 'Hor�rio Inicial � inv�lido!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se o hor�rio inicial est� no limite definido
if (time_to_sec($start_hora) < time_to_sec($limites['entrada'])){
    $mensagem = 'Hor�rio Inicial � menor que '.$limites['entrada'].'!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se o hor�rio final � v�lido
if (validaHoras($end_hora) == false) {
    $mensagem = 'Hor�rio Final � inv�lido!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se o hor�rio final est� no limite definido
if (time_to_sec($end_hora) > time_to_sec($limites['saida'])){
    $mensagem = 'Hor�rio Final � maior que '.$limites['saida'].'!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se o hor�rio inicial � v�lido
if (validaHoras($start_hora) == false) {
    $mensagem = 'Hor�rio Inicial � inv�lido!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se o hor�rio inicial � v�lido
if (time_to_sec($start_hora) >= time_to_sec($end_hora)){
    $mensagem = 'Hor�rio Inicial maior que Final!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Verifica se o hor�rio inicial � v�lido
if (empty(trim($documento))){
    $mensagem = 'Documento n�o informado!';
    retornaInformacao($mensagem,$tipo);
    exit();
}


/* ***************************** *
 * PER�ODOS                      *
 * ***************************** */

// verifica se datas j� constam em outros per�odos
$mensagem = verificaGECCNoPeriodo($siape,$start_date,$end_date,time_to_sec($horas),$acrescimo);
if ($mensagem != "")
{
    retornaInformacao($mensagem,$tipo);
    exit();
}

// Vefifica se o documento foi informado.
if ($acrescimo == "S" && (empty(trim($documento)) || strlen(trim($documento)) < 10)){
    $mensagem = 'Informe o documento, com no m�nimo 10 caracteres!';
    retornaInformacao($mensagem,$tipo);
    exit();
}

// registra em banco de dados
if (createAutorizacaoGECC($campos) == true)
{
    registraLog("Realizado registro de Gratifica��o por Encargo de Curso ou Concurso para o servidor matr�cula ".$matricula.".");
    $mensagem = 'Realizado registro de Gratifica��o por Encargo de Curso ou Concurso!';
    retornaInformacao($mensagem,'success');
}
else
{
    $mensagem = 'Registro de Gratifica��o por Encargo de Curso ou Concurso, N�O realizado!';
    //$mensagem = 'Tempo da consulta inv�lido!';
    retornaInformacao($mensagem,$tipo);
}

exit();
