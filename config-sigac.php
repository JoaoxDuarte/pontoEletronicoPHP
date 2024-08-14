<?php
/**
 * Created by PhpStorm.
 * User: Ezequiel Lafuente
 * Date: 19/11/2018
 * Time: 15:40
 */

include_once("config.php");

return [
    'client_id' => getParamentroSigac(1)['valor'],
    'client_secret' => getParamentroSigac(2)['valor'],
    'redirect_uri_servidor' => getParamentroSigac(3)['valor'],
    'redirect_uri_chefia' => getParamentroSigac(4)['valor'],
    'redirect_uri_rh' => getParamentroSigac(5)['valor'],
    'sigac_url' => getParamentroSigac(6)['valor']
];