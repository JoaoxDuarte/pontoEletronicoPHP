<?php
/**
 * +-------------------------------------------------------------+
 * | @description : Autorização de Trabalho em Dia Não Útil      |
 * |                - Seleção de unidade                         |
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
$form_caminho    = "Frequência » Visualizar » Autorização de Trabalho";
$form_sub_titulo = "Autorização de Trabalho em Dia Não Útil";
?>
<script src='<?= _DIR_JS_; ?>phpjs.js' type='text/javascript'></script>
<script src='autorizacao_trabalho_dia_nao_util_entra.js' type='text/javascript'></script>
<?php
include_once( "frequencia_entra_formulario.php" );
