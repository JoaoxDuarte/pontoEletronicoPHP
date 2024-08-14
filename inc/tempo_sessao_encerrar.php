<?php

include( "../config.php" );

/* Inicia a sessão */
session_start();
destroi_sessao();

echo json_encode(array("hora" => '00:00', "tipo" => "warning", "destino" => 'login'));

exit();