<?php
// conexao ao banco de dados
// funcoes diversas
include_once("config.php");

// class formulario
include_once("class_form.frequencia.php");

verifica_permissao('sRH');

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    // Valores passados - encriptados
    $dados  = explode(":|:", base64_decode($dadosorigem));
    $siape  = $dados[0];
    $dia    = $dados[1];
    $cmd    = $dados[2];
    $so_ver = $dados[3];
}

//
// VERIFICA SE O ACESSO EH VIA ENTRADA.PHP
//
// Comentado temporariamente por n�o sabermos, de antem�o, os IPs da aplica��o
//include_once('ilegal_grava.php');

/* --------------------------------------------*\
  |   - registra frequ�ncia como homologada      |
  |                                              |
  |  Altera��o: 08/07/2013                       |
  |             07/08/2013                       |
  \*-------------------------------------------- */


$mes = dataMes($dia);
$ano = dataAno($dia);

// instancia o banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setDestino($_SESSION['voltar_nivel_1']);
$oDBase->setMensagem("Falha no registro da verifica��o de cedidos/descentralizados!");

// atualiza cadastro do servidor
$oDBase->query("UPDATE servativ SET freqh = 'V' WHERE mat_siape = :mat_siape", array(
    array( ':mat_siape', $siape, PDO::PARAM_STR )
));

// atualiza homologados
$oDBase->query("UPDATE homologados SET homologado='V' WHERE compet = :compet AND mat_siape = :mat_siape ", array(
    array( ':mat_siape', $siape, PDO::PARAM_STR ),
    array( ':compet',  $ano . $mes  , PDO::PARAM_STR)
));

// grava o LOG
registraLog(" verificou a Homologa��o da matr�cula " . $siape, "", "", "Verificar Homologa��es");

mensagem("Verifica��o de homologa��o realizada com sucesso!");
?>
<script>
    window.parent.closeIFrame();
</script>
