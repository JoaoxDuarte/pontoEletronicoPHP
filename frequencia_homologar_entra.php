<?php
/**
 * +-------------------------------------------------------------+
 * | @description : Homologação dos Registros de Frequência      |
 * |                realizados por servidores/estagiários        |
 * |                                                             |
 * | @author  : Carlos Augusto                                   |
 * | @version : Edinalvo Rosa                                    |
 * +-------------------------------------------------------------+
 * */
// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("sAPS");


if (!empty($_GET['importafastamentos']))
{
    $result = updateAfastamentosBySiape($_GET['siape']);
    echo json_encode(array("success" => $result));
    die;
}


// dados para o formulario
$form_destino = array("frequencia_homologar.php", base64_encode("2:|:" . $_SESSION['sLotacao'])); // cmd=2
$form_caminho = "Homologar";

include_once( "frequencia_entra_formulario.php" );

?>
<script type='text/javascript' src='<?= _DIR_JS_; ?>phpjs.js'></script>
<script type='text/javascript' src='frequencia_homologar_entra.js'></script>
<?php

exit();
