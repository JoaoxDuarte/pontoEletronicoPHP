<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "src/models/TabHomologadosModel.php" );
include_once( "src/views/TabHomologadosView.php" );
include_once( "src/controllers/DadosServidoresController.php" );


/**
 * @class TabHomologadosController
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : homologados
 *       Suas descrições e características
 *
 * @Camada    - Controllers
 * @Diretório - src/controllers
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
     * @param string $siape    Matrícula SIAPE
     * @param string $mes      Mês da frequência
     * @param string $ano      Ano da frequência
     * @param string $destino  Página de destino
     * @param string $retorno  Página de retorno
     * @return string "HOMOLOGADO" ou "NÃO HOMOLOGADO"
     */
    public function retornaSeHomologado( $siape = null, $mes = null, $ano = null, $destino = null, $retorno = null )
    {
        return $this->tabHomologadosModel->retornaSeHomologado( $siape, $mes, $ano, $destino, $retorno);
    }

    
    /*
     * @info verifica se homologado
     *
     * @param string $siape    Matrícula SIAPE
     * @param string $mes      Mês da frequência
     * @param string $ano      Ano da frequência
     * @param string $homologado_siape  Matrícula SIAPE do homologador
     * @return void
     */
    public function registraHomologacao( $siape = null, $mes = null, $ano = null, $homologador_siape = null, $lotacao = null )
    {
        $this->tabHomologadosModel->registraHomologacao( $siape, $mes, $ano, $homologador_siape, $lotacao );
    }

} // END class TabHomologadosController
