<?php
/**
 * Created by PhpStorm.
 * User: Ezequiel Lafuente
 * Date: 21/01/2019
 * Time: 18:27
 */

class WsSiape
{

    /**
     * Siape constructor.
     */
    public function __construct() {

        $this->config = include('config-wssiape.php');

        try{
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);
            $this->soap = new SoapClient('siape/siape-afastamentov1.wsdl', ['stream_context' => $context]);
        }
        catch(SoapFault $e){
            return $e->getMessage();
        }
    }


    public function consultarAfastamento($argument) {

        try{

            $client = new \SoapClient('siape/siape-afastamentov1.wsdl');

            return $client->consultarAfastamento(['arg0' => $argument]);
        }
        catch(\SoapFault $e){
            return $e->getMessage();
        }
    }

}