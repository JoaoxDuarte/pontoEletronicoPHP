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
// Comentado temporariamente por não sabermos, de antemão, os IPs da aplicação
//include_once('ilegal_grava.php');

/* --------------------------------------------*\
  |   - registra frequência como homologada      |
  |                                              |
  |  Alteração: 08/07/2013                       |
  |             07/08/2013                       |
  \*-------------------------------------------- */


$mes = dataMes($dia);
$ano = dataAno($dia);

// instancia o banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setDestino($_SESSION['voltar_nivel_1']);
$oDBase->setMensagem("Falha no registro da verificação de cedidos/descentralizados!");

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
registraLog(" verificou a Homologação da matrícula " . $siape, "", "", "Verificar Homologações");

mensagem("Verificação de homologação realizada com sucesso!");
?>
<script>
    window.parent.closeIFrame();
</script>
