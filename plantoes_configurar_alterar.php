<?php
include_once("config.php");
include_once( "src/controllers/TabPlantoesController.php" );

verifica_permissao("plantoes");

$oPlantao = new TabPlantoesController();

$retorno = "";

if(!empty($_POST)){

    $alterar['id']           = $_POST['id_plantao'];
    $alterar['id_escala']    = $_POST['id_escala'];
    $alterar['descricao']    = $_POST['descricao'];
    $alterar['hora_inicial'] = $_POST['hora_inicial'];
    $alterar['hora_final']   = $_POST['hora_final'];
    $alterar['ativo']        = $_POST['ativo'];

    $alterar['hora_inicial_antes'] = $_POST['hora_inicial_antes'];
    $alterar['hora_final_antes']   = $_POST['hora_final_antes'];

    $retorno = $oPlantao->update($alterar);
}

$oPlantao->formularioCadastroPlantao( "Alterar", $retorno);

exit;
