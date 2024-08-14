<?php

require_once 'config-siape.php';

class Siape {

    /**
     * Siape constructor.
     */
    public function __construct() {

        $this->config = include('config-siape.php');

        try{
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);
            $this->soap = new SoapClient($this->config['wsdl'], ['stream_context' => $context]);
        }
        catch(\SoapFault $e){
            //mensagem( "Problema no acesso ao WSDL SIAPE (WS-25)<br>".$e->getMessage() );
            return false; //$e->getMessage();
        }
    }

    /**
     * @param $cpf
     * @return string
     */
    public function buscarDadosPessoais($cpf , $orgao = null) {

        try{
            return $this->soap->consultaDadosPessoais(
                $this->config['siglaSistema'],
                $this->config['nomeSistema'],
                $this->config['senha'],
                $cpf,
                $orgao, // ex: 20213
                'b',// $this->config['parmExistPag'],
                $this->config['parmTipoVinculo']
            );
        }
        catch(\SoapFault $e){
            //mensagem( "Problema no acesso ao WSDL SIAPE (WS-48)<br>".$e->getMessage() );
            return false; //$e->getMessage();
        }
    }

    public function buscarDadosAfastamentoHistorico($cpf, $orgao = null){

        try{

            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);

            $client = new SoapClient($this->config['wsdl'], ['stream_context' => $context]);

            $args = [
                'siglaSistema' =>  $this->config['siglaSistema'],
                'nomeSistema' =>  $this->config['nomeSistema'],
                'senha' =>  $this->config['senha'],
                'cpf' =>  $cpf,
                'codOrgao' => $orgao,
                'parmExistPag' => $this->config['parmExistPag'],
                'parmTipoVinculo' =>  $this->config['parmTipoVinculo'],
                'anoInicial' => date("Y",strtotime("-5 year")),
                'mesInicial' => $_SESSION['mes_inicial'],
                'anoFinal' => $_SESSION['ano_final'],
                'mesFinal' => $_SESSION['mes_final']
            ];

            return call_user_func_array([$client, 'ConsultaDadosAfastamentoHistorico'], $args);

        }catch(\SoapFault $e){
            //mensagem( "Problema no acesso ao WSDL SIAPE (WS-84)<br>".$e->getMessage() );
            return false; //$e->getMessage();
        }
    }


    /**
     * @param $cpf
     * @return string
     */
    public function buscarDadosFuncionais($cpf, $orgao = null) {

        if(empty($orgao))
            $orgao = $this->config['codOrgao'];

        try {
            return $this->soap->consultaDadosFuncionais(
                $this->config['siglaSistema'],
                $this->config['nomeSistema'],
                $this->config['senha'],
                $cpf,
                $orgao,//ex: 20213
                'b', //$this->config['parmExistPag'],
                $this->config['parmTipoVinculo']
            );
        }
        catch(\SoapFault $e){
            //mensagem( "Problema no acesso ao WSDL SIAPE (WS-111)<br>".$e->getMessage() );
            return false; //$e->getMessage();
        }
    }

    /**
     * @param $cpf
     * @return string
     */
    public function buscarDadosAfastamento($cpf, $orgao = null) {

        try{
            return $this->soap->ConsultaDadosAfastamento(
                $this->config['siglaSistema'],
                $this->config['nomeSistema'],
                $this->config['senha'],
                $cpf,
                $orgao,//ex: 20213
                //$this->config['codOrgao'],
                $this->config['parmExistPag'],
                $this->config['parmTipoVinculo']
            );
        }
        catch(\SoapFault $e){
            //mensagem( "Problema no acesso ao WSDL SIAPE (WS-135)<br>".$e->getMessage() );
            return false; //$e->getMessage();
        }
    }


    /**
     * @param $cpf
     * @return string
     */
    public function ConsultaDadosBancarios($cpf, $orgao = null) {

        try{
            return $this->soap->ConsultaDadosBancarios(
                $this->config['siglaSistema'],
                $this->config['nomeSistema'],
                $this->config['senha'],
                $cpf,
                $orgao,//ex: 20213
                //$this->config['codOrgao'],
                $this->config['parmExistPag'],
                $this->config['parmTipoVinculo']
            );
        }
        catch(\SoapFault $e){
            //mensagem( "Problema no acesso ao WSDL SIAPE (WS-160)<br>".$e->getMessage() );
            return false; //$e->getMessage();
        }
    }


    /**
     * Lista os uorgs do siape por órgão
     *
     * @param integer $cpf
     * @param integer $orgao
     * @param integer|null $uorg
     * @return string
     */
    public function listarUorgs($cpf, $orgao, $uorg = null){
        try{
            return $this->soap->listarUorgs(
                $this->config['siglaSistema'],
                $this->config['nomeSistema'],
                $this->config['senha'],
                $cpf,
                $orgao,
                $uorg
            );
        }
        catch(\SoapFault $e){
            return $e->getMessage();
        }
    }

    /**
     * Lista os servidores port órgão e uorg
     *
     * @param integer $cpf
     * @param integer $orgao
     * @param integer $uorg
     * @return string
     */
    public function listaServidores($cpf, $orgao, $uorg){
        try{
            return $this->soap->listaServidores(
                $this->config['siglaSistema'],
                $this->config['nomeSistema'],
                $this->config['senha'],
                $cpf,
                $orgao,
                $uorg
            );
        }
        catch(\SoapFault $e){
            return $e->getMessage();
        }
    }

    
}

