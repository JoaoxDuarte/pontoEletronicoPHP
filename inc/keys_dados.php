<?php
/* 
 * @author Edinalvo Rosa
 * @create 22/02/2019
 */

include_once( "../config.php" );
include_once( "../functions.php" );
//include_once( '../inc/MyCripty.class.php' );

$dados = base64_decode($_REQUEST["dados"]);

$result[] = array(
    'chave' => criptografa( $dados ),
);

$myData = array('dados' => $result);

print json_encode($myData);

exit();
