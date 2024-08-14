<?php
include_once("config.php");
include_once( "src/controllers/TabPlantoesController.php" );

verifica_permissao("plantoes");

$oPlantao = new TabPlantoesController();

$retorno = "";

if(!empty($_POST)){

    $incluir['id']           = $_POST['id_plantao'];
    $incluir['id_escala']    = $_POST['id_escala'];
    $incluir['descricao']    = $_POST['descricao'];
    $incluir['hora_inicial'] = $_POST['hora_inicial'];
    $incluir['hora_final']   = $_POST['hora_final'];
    $incluir['ativo']        = $_POST['ativo'];

    $incluir['hora_inicial_antes'] = $_POST['hora_inicial_antes'];
    $incluir['hora_final_antes']   = $_POST['hora_final_antes'];

    $retorno = $oPlantao->insert($incluir);
}

$oPlantao->formularioCadastroPlantao( "Incluir", $retorno);

exit;
