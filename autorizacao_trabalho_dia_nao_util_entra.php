<?php
/**
 * +-------------------------------------------------------------+
 * | @description : Autoriza��o de Trabalho em Dia N�o �til      |
 * |                - Sele��o de unidade                         |
 * |                                                             |
 * | @author  : Carlos Augusto                                   |
 * | @version : Edinalvo Rosa                                    |
 * +-------------------------------------------------------------+
 * */
// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("sAPS");

// dados para o formulario
$form_destino    = array("autorizacao_trabalho_dia_nao_util.php", "");
$form_caminho    = "Frequ�ncia � Visualizar � Autoriza��o de Trabalho";
$form_sub_titulo = "Autoriza��o de Trabalho em Dia N�o �til";
?>
<script src='<?= _DIR_JS_; ?>phpjs.js' type='text/javascript'></script>
<script src='autorizacao_trabalho_dia_nao_util_entra.js' type='text/javascript'></script>
<?php
include_once( "frequencia_entra_formulario.php" );
