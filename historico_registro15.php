<?php

// Inicia a sessão e carrega as funções de uso geral
include_once( "config.php" );
include_once( "class_form.frequencia.php" );
include_once( "class_ocorrencias_grupos.php" );

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH e sTabServidor');

// testa se o acesso é válido
if (empty($_SESSION['sHOrigem_2']))
{
    mensagem('Acesso inválido!', 'historico_frequencia.php');
}

// grava em sessao dados do script atual
//$_SESSION['sHOrigem_1'] : historico_frequencia.php
//$_SESSION['sHOrigem_2'] : historico_regfreq3.php
//$_SESSION['sHOrigem_3'] : historico_registro8.php
$_SESSION['sHOrigem_4'] = ($_SESSION['sHOrigem_4'] == "" ? $_SERVER['REQUEST_URI'] : $_SESSION['sHOrigem_4']);

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];
$dadosorigem = (empty($dadosorigem) ? $_SESSION['sDadosC'] : $dadosorigem);

if (empty($dadosorigem))
{
    // le dados passados por formulario
    $siape = anti_injection($_REQUEST['mat']);
    $dia   = $_REQUEST['dia'];
    $cmd   = anti_injection($_REQUEST['cmd']);
    $ocor  = anti_injection($_REQUEST['ocor']);
    $mes   = substr($dia, 3, 2); // mes
    $ano   = substr($dia, 6, 4); // ano
}
else
{
    // Valores passados - encriptados
    $dados      = explode(":|:", base64_decode($dadosorigem));
    $dia        = $dados[0];
    $nome       = $dados[1];
    $ocor_antes = $dados[2];

    $ocor    = anti_injection($_REQUEST['ocor']);
    $modoreg = anti_injection($_REQUEST['modoreg']);
    $cmd     = '1';

    $siape = $_SESSION['sHSiape'];
    //$mes = $_SESSION['sHMes']; // mes
    //$ano = $_SESSION['sHAno']; // ano
    $mes   = substr($dia, 3, 2); // mes
    $ano   = substr($dia, 6, 4); // ano
}


$oDBase = selecionaServidor($mat);
$sitcad = $oDBase->fetch_object()->sigregjur;

// ocorrências grupos
$obj = new OcorrenciasGrupos();
$correnciaLimiteDias = $obj->OcorrenciaLimiteDias($sitcad);

// ocorrencias com limites de dias, e este limite
$arrayMsg = "";
foreach($correnciaLimiteDias[0] as $val)
{
  $arrayMsg .= "limiteDias['".$val."'] = ".$correnciaLimiteDias[1][$val].';'.chr(13)."".chr(10);
}



// validacao dos campos
$validacao = new valida();
$validacao->setExibeMensagem(true);
$validacao->setDestino($_SESSION['sHOrigem_4']);
$validacao->siape($siape);   // se matrícula inválida retorna para destino
$validacao->siaperh($siape); // o usuário não pode alterar sua própria frequência, retorna para destino
$validacao->mes($mes); // se mes inválido retorna para destino
$validacao->ano($ano); // se ano inválido retorna para destino
$validacao->upagrh($_SESSION['upag']); // se upag diferente do usuario retorna para destino

$sub_titulo  = "Registro de Ocorr&ecirc;ncia por Per&iacute;odo";
$form_action = "historico_gravaregfreq2.php?modo=4b";
$form_submit = "return testa(2)";

// Grava em sessão dados para o proximo script.
$_SESSION['sHIdReg'] = 'R';
$_SESSION['sHCmd']   = '1';
$_SESSION['sDadosC'] = base64_encode($dia . ":|:" . $nome . ":|:" . $ocor . ":|:" . $modoreg);

// Grava em sessão dados e nomes dos scripts origem para retornar.
$_SESSION['sDadosD'] = base64_encode($dia . ":|:" . $nome . ":|:" . $ocor . ":|:" . $modoreg);

$nome_do_arquivo = $_SESSION['sHArquivo']; // nome do arquivo de trabalho, neste caso, o temporario
$upag            = $_SESSION['upag']; // Código da UPAG do usuario logado

// instancia o formulario
$oForm           = new formFrequencia;
$oForm->setJS("
<script>
  var limiteDias = new Array();
  " . $arrayMsg . "
</script>
");
$oForm->setJS("historico_registro15.js");
$oForm->setHistoryGo(0);
$oForm->setSeparador(0);

// Registra em sessao
$oForm->setOrigem($_SESSION['sHOrigem_1'], 1);
$oForm->setOrigem($_SESSION['sHOrigem_2'], 2);
$oForm->setOrigem($_SESSION['sHOrigem_3'], 3);
$oForm->setOrigem($_SESSION['sHOrigem_4'], 4);

// dados registrados em sessao (via ajax)
$oForm->setAnoHoje($ano_hoje); // ano (data atual)
$oForm->setUsuario($usuario);  // matricula do usuario
$oForm->setSiape($siape); // matricula do servidor que se deseja alterar a frequencia
$oForm->setMes($mes); // mes que se deseja alterar a frequencia
$oForm->setAno($ano); // ano que se deseja alterar a frequencia
$oForm->setData($dia); // data da frequencia
$oForm->setNomeDoArquivo($nome_do_arquivo); // nome do arquivo de trabalho, neste caso, o temporario
$oForm->setCodigoOcorrencia($ocor); // codigo de ocorrencia
$oForm->setTipoOperacao($cmd);

$oForm->setInputHidden('correnciaLimiteDias', implode(',', $correnciaLimiteDias));

$oForm->setSubTitulo($sub_titulo);
$oForm->setFormAction($form_action);
$oForm->setFormSubmit($form_submit);

//$oForm->validaParametros( 0 );

$oForm->exibeFormRegistro15();
