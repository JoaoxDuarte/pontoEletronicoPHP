<?php
include_once("config.php");
include_once("hora_extra_autorizacao_funcoes.php");

verifica_permissao("sRH ou Chefia");

$tipo     = 'danger';
$mensagem = '';

/** Se não há POT, retorna erro **/
if( !isset($_POST['data_inicio']) )
{
    $mensagem = 'Dados não informados!';
    retornaInformacao($mensagem,$tipo);
}

$post['siape']                = anti_injection( $_POST['siape'] );
$post['setor']                = anti_injection($_POST['setor']);
$post['data_inicio']          = filter_input(INPUT_POST, 'data_inicio', FILTER_SANITIZE_STRING, FILTER_SANITIZE_MAGIC_QUOTES);
$post['data_fim']             = filter_input(INPUT_POST, 'data_fim', FILTER_SANITIZE_STRING, FILTER_SANITIZE_MAGIC_QUOTES);
$post['horas']                = anti_injection( $_POST['horas'] );
$post['documento']            = anti_injection( $_POST['documento'] );
$post['acrescimo_autorizado'] = anti_injection( $_POST['acrescimo_autorizado'] );
$post['id']                   = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

$post['documento'] = (is_null($post['documento']) ? '' : $post['documento']);

$data_teste_inicio = filter_input(INPUT_POST, 'data_inicio_anterior', FILTER_SANITIZE_STRING, FILTER_SANITIZE_MAGIC_QUOTES);
$data_teste_fim    = subtrai_dias_da_data($post['data_inicio'], 1);

$horas_destinadas_inicio = 0;


if ( !empty($data_teste_inicio) )
{
    $horas_destinadas_inicio = verificaHorasDestinadasParaHoraExtra($siape, $data_teste_inicio, $data_teste_fim);
}

$data_teste_inicio    = subtrai_dias_da_data($_POST['data_fim_anterior'], 1);
$data_teste_fim       = $post['data_fim'];
$horas_destinadas_fim = 0;

if ( !empty($data_teste_inicio) )
{
    $horas_destinadas_fim = verificaHorasDestinadasParaHoraExtra($siape, $data_teste_inicio, $data_teste_fim);
}

$ano_hora_extra = dataAno($post['data_inicio']); // date('Y');

// verifica se as datas já estão cadastradas
$datas_id = verificaSePeriodoHoraExtraJaCadastrado($post);

/* ***************************** *
 * DATA INCIAL                   *
 * ***************************** */

// Verifica se a data inicial é válida
if (validaData($post['data_inicio']) === false){
    $mensagem = 'Data Inicial é inválida!';
    retornaInformacao($mensagem,$tipo);
}

// Verifica se data inicial esta no ano corrente
if (dataAno($post['data_inicio']) > $ano_hora_extra){
    $mensagem = 'Data Inicial precisa estar dentro do ano corrente!';
    retornaInformacao($mensagem,$tipo);
}


// Verifica se data inicial já está cadastrada
// para este servidor, caso seja uma inclusão
if (($post['id'] == $datas_id->inicio_cadastrado) || (empty($post['id']) && $datas_id->inicio_cadastrado > 0))
{
    $mensagem = 'Data Inicial já cadastrada para este servidor, em outro período!';
    retornaInformacao($mensagem,$tipo);
}


/* ***************************** *
 * DATA FINAL                    *
 * ***************************** */

// Verifica se a data final é válida
if (validaData($post['data_fim']) === false){
    $mensagem = 'Data Final é inválida!';
    retornaInformacao($mensagem,$tipo);
}

// Verifica se data final esta no ano corrente
if (dataAno($post['data_fim']) > $ano_hora_extra){
    $mensagem = 'Data Final precisa estar dentro do ano corrente!';
    retornaInformacao($mensagem,$tipo);
}

// Verifica se data final já está cadastrada
// para este servidor, caso seja uma inclusão
if (($post['id'] == $datas_id->fim_cadastrado) || (empty($post['id']) && $datas_id->fim_cadastrado > 0))
{
    $mensagem = 'Data Final já cadastrada para este servidor, em outro período!';
    retornaInformacao($mensagem,$tipo);
}

// Verifica se a data inicial é maior que a data final
if (inverteData($post['data_inicio']) > inverteData($post['data_fim'])) {
    $mensagem = 'Data Inicial não pode ser maior que a Data Final!';
    retornaInformacao($mensagem,$tipo);
}

// se há registro de horas extras no ponto
if ($horas_destinadas_inicio > 0 && $horas_destinadas_fim > 0){
    $mensagem = 'Datas Inicial e Final NÃO podem ser alterada,<br>há registro de horas extras trabalhadas!';
    retornaInformacao($mensagem,$tipo);
}
else if ($horas_destinadas_inicio > 0){
    $mensagem = 'Data Inicial NÃO pode ser alterada,<br>há registro de horas extras trabalhadas!';
    retornaInformacao($mensagem,$tipo);
}
else if ($horas_destinadas_fim > 0){
    $mensagem = 'Data Final NÃO pode ser alterada,<br>há registro de horas extras trabalhadas!';
    retornaInformacao($mensagem,$tipo);
}

// Verifica as horas
if (time_to_sec($post['horas']) <= 0) {
    $mensagem = 'Hora(s) informada inválida!';
    retornaInformacao($mensagem,$tipo);
}

// diferença entre datas, inclusive as próprias datas
$mensagem = verificaHoraExtraNoPeriodo($post['siape'],$post['data_inicio'],$post['data_fim'],time_to_sec($post['horas']),$post['acrescimo_autorizado']);
if ($mensagem != "")
{
    retornaInformacao($mensagem,$tipo);
}

// Vefifica se documento informado quando acréscimo igual [S]im.
if (empty(trim($post['documento']))){
    $mensagem = 'Informe o documento!';
    retornaInformacao($mensagem,$tipo);
}


// registra em banco de dados
if (createUpdateAutorizacaoHoraExtra($post) == true)
{
    registraLog("Realizado registro de Autorização de Serviços Extraordinários para o servidor matrícula ".$matricula.".");
    $mensagem = 'Realizado registro de Autorização de Serviços Extraordinários!';
    retornaInformacao($mensagem,'success');
}
else
{
    $mensagem = 'Registro de Autorização de Serviços Extraordinários, NÃO realizado!';
    //$mensagem = 'Tempo da consulta inválido!';
    retornaInformacao($mensagem,$tipo);
}

exit();
