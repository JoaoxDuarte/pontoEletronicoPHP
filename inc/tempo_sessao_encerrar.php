<?php

include( "../config.php" );

/* Inicia a sess�o */
session_start();
destroi_sessao();

echo json_encode(array("hora" => '00:00', "tipo" => "warning", "destino" => 'login'));

exit();