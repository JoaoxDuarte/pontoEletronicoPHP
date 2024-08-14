<?php
/**
 * Created by PhpStorm.
 * User: Ezequiel Lafuente
 * Date: 19/11/2018
 * Time: 15:39
 */

require_once 'config-sigac.php';

class Sigac {


    /**
     * Tipo de resposta solicitada
     */
    const RESPONSE_TYPE = 'code';

    /**
     * Essa rota chama o login do SIGAC
     */
    const ROUTE_AUTH = "/auth/oauth/authorize?";

    /**
     * Essa rota serve para buscar o token do usuário
     */
    const ROUTE_TOKEN = "/auth/oauth/token";

    /**
     * Essa rota serve para validar o token do usuário
     */
    const ROUTE_VALIDATE_TOKEN = "/auth/oauth/check_token";


    /**
     * Sigac constructor.
     */
    public function __construct() {
        $this->config = include('config-sigac.php');
    }

    /**
     * @param $cpf
     * @return string
     * @chamada Essa deve ser a primeira chamada
     * @info Esse método chama a interface do login com o SIGAC, e se porventura o login for realizado com sucesso, a API retornará para o sistema do SISREF
     */
    public function callLoginSIGAC($type) {

        try{

            $link_url = $this->config['sigac_url'] . self::ROUTE_AUTH
                . 'response_type=' . self::RESPONSE_TYPE
                . '&redirect_uri=' . $this->getRedirect($type)
                . '&client_id=' . $this->config['client_id'];

            return $link_url;
        }
        catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * @param $code
     * @return mixed|string
     * @info Retorna as informações do usuário em questão
     */


    public function getTokenAccess($code , $type){
        try{

            $headers = [
                'Authorization: Basic ' . base64_encode($this->config['client_id'] . ":" . $this->config['client_secret']),
                'Content-Type:application/x-www-form-urlencoded',
            ];

            $body = [
                'grant_type' => urlencode('authorization_code'),
                'code' => urlencode($code),
                'redirect_uri' => $this->getRedirect($type)
            ];

            foreach ($body as $key => $value) {
                $dados .= $key . '=' . $value . '&';
            }

            rtrim($dados, '&');

            $ch = curl_init();

            $link_url = $this->config['sigac_url'] . self::ROUTE_TOKEN;

            curl_setopt($ch, CURLOPT_URL, $link_url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dados);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $output = curl_exec($ch);

            curl_close($ch);

            return json_decode($output, true);
        }
        catch(\Exception $e){
            return $e->getMessage();
        }
    }


    /**
     * @param $token
     * @return mixed|string
     * @info Realiza a validação do token gerado anteriormente
     */
    public function validateTokenAccess($token){
        try{

            $headers = [
                'Authorization: Basic ' . base64_encode($this->config['client_id'] . ":" . $this->config['client_secret']),
                'Content-Type:application/x-www-form-urlencoded',
            ];

            $body = [
                'token' => urlencode($token)
            ];

            foreach ($body as $key => $value) {
                $dados .= $key . '=' . $value . '&';
            }

            rtrim($dados, '&');

            $ch = curl_init();

            $link_url = $this->config['sigac_url'] . self::ROUTE_VALIDATE_TOKEN;

            curl_setopt($ch, CURLOPT_URL, $link_url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dados);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $output = curl_exec($ch);

            curl_close($ch);

            return json_decode($output, true);
        }
        catch(\Exception $e){
            return $e->getMessage();
        }
    }


    /**
     * @param $type
     * @return mixed
     * @info Recupera a url de callback
     */
    private function getRedirect($type){

        switch ($type){
            CASE "SERVIDOR":
                return $this->config['redirect_uri_servidor'];
            CASE "CHEFIA":
                return $this->config['redirect_uri_chefia'];
            CASE "RH":
                return $this->config['redirect_uri_rh'];
            default:
                return $this->config['redirect_uri_rh'];
        }
    }
}
