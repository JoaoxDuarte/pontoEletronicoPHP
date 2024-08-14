<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");
include_once( "src/models/TabHomologadosModel.php" );
include_once( "src/views/TabHomologadosView.php" );
include_once( "src/controllers/DadosServidoresController.php" );


/**
 * @class TabHomologadosController
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : homologados
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - Controllers
 * @Diret�rio - src/controllers
 * @Arquivo   - TabHomologadosController.php
 *
 */

class TabHomologadosController
{
    /*
    * Atributos
    */
    public $tabHomologadosModel       = NULL;
    public $tabHomologadosView        = NULL;
    public $dadosServidoresController = NULL;

    public function __construct()
    {
        # instancia
        $this->tabHomologadosModel       = new TabHomologadosModel();
        $this->tabHomologadosView        = new TabHomologadosView();
        $this->dadosServidoresController = new DadosServidoresController();
    }


    /*
     * @info verifica se homologado
     *
     * @param string $siape    Matr�cula SIAPE
     * @param string $mes      M�s da frequ�ncia
     * @param string $ano      Ano da frequ�ncia
     * @param string $destino  P�gina de destino
     * @param string $retorno  P�gina de retorno
     * @return string "HOMOLOGADO" ou "N�O HOMOLOGADO"
     */
    public function retornaSeHomologado( $siape = null, $mes = null, $ano = null, $destino = null, $retorno = null )
    {
        return $this->tabHomologadosModel->retornaSeHomologado( $siape, $mes, $ano, $destino, $retorno);
    }

    
    /*
     * @info verifica se homologado
     *
     * @param string $siape    Matr�cula SIAPE
     * @param string $mes      M�s da frequ�ncia
     * @param string $ano      Ano da frequ�ncia
     * @param string $homologado_siape  Matr�cula SIAPE do homologador
     * @return void
     */
    public function registraHomologacao( $siape = null, $mes = null, $ano = null, $homologador_siape = null, $lotacao = null )
    {
        $this->tabHomologadosModel->registraHomologacao( $siape, $mes, $ano, $homologador_siape, $lotacao );
    }

} // END class TabHomologadosController
