<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "src/models/TabBancoDeHorasCiclosModel.php" );
include_once( "src/views/TabBancoDeHorasCiclosView.php" );


/**
 * @class TabBancoDeHorasCiclosController
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : ciclos
 *       Suas descrições e características
 *
 * @Camada    - Controllers
 * @Diretório - src/controllers
 * @Arquivo   - TabBancoDeHorasCiclosController.php
 *
 */

class TabBancoDeHorasCiclosController
{
    /*
    * Atributos
    */
    public $tabBancoDeHorasCiclosModel = NULL;
    public $tabBancoDeHorasCiclosView  = NULL;

    public function __construct()
    {
        # instancia
        $this->tabBancoDeHorasCiclosModel = new TabBancoDeHorasCiclosModel();
        $this->tabBancoDeHorasCiclosView  = new TabBancoDeHorasCiclosView();
    }

    
    /**
     * @info Carrega o ID do ciclo corrente
     *
     * @param void
     * @return integer ID do ciclo atual
     */
    public function getCicloCurrent()
    {
        return $this->tabBancoDeHorasCiclosModel->getCicloCurrent();
    }
    
    
     /**
     * @info Inclui novo registro
     *
     * @param void
     * @return boolean TRUE sucesso
     */
    public function insert($alterar=null)
    {
        return $this->tabBancoDeHorasCiclosModel->insert($alterar);
    }


    /**
     * @info Inclui novo registro
     *
     * @param void
     * @return boolean TRUE sucesso
     */
    public function update($alterar=null)
    {
        return $this->tabBancoDeHorasCiclosModel->update($alterar);
    }


    /**
     * @info Apaga registro a partir do ID
     *
     * @param type $id
     * @return boolean TRUE sucesso
     */
    public function delete( $id=null )
    {
        $oBancoDeHorasCiclos = $this->tabBancoDeHorasCiclosModel->registrosBancoDeHorasCiclosPorID($id);
        $descricao = $oBancoDeHorasCiclos->fetch_object()->descricao;

        // efetua o "delete", se for o caso
        $retorno = $this->tabBancoDeHorasCiclosModel->delete( $id, $num_rows, $descricao );
        
        $this->tabBancoDeHorasCiclosView->deleteView($retorno, $descricao);
    }


    /*
     * @info Registros Ciclos - Banco de Horas
     *
     * @param void
     * @return object
     */
    public function registrosBancoDeHorasCiclos()
    {
        return $this->tabBancoDeHorasCiclosModel->registrosBancoDeHorasCiclos();
    }


    /*
     * @info Registros Ciclos - Banco de Horas
     *
     * @param void
     * @return object
     */
    public function registrosBancoDeHorasCiclosPorID($id=null)
    {
        return $this->tabBancoDeHorasCiclosModel->registrosBancoDeHorasCiclosPorID($id);
    }

    
    /**
     * @info Carrega dados do ciclo por matrícula, com
     *       autorizações por servidor, dentro do ciclo
     *
     * @param string $mat Matrícula do servidor
     * @return result
     */
    public function getCicloBySiape($mat=null)
    {
        return $this->tabBancoDeHorasCiclosModel->getCicloBySiape($mat);
    }


    /**
     * @info Carrega dados do ciclo por matrícula e ano
     *
     * @param string $mat Matrícula do servidor
     * @param string $ano Ano do ciclo
     * @return result
     */
    public function getCicloBySiapeYear($mat=null, $ano=null)
    {
        return $this->tabBancoDeHorasCiclosModel->getCicloBySiapeYear($mat, $ano);
    }
    

    /**
     * @info Carrega dados do ciclo por matrícula, com autorizações
     *       para usofruto pelo servidor, dentro do ciclo
     *
     * @param string $mat Matrícula do servidor
     * @return result
     */
    public function getCicloBySiapeUsufruto( $mat=null, $dia=null )
    {
        return $this->tabBancoDeHorasCiclosModel->getCicloBySiapeUsufruto( $mat, $dia );
    }    
    
    /**
     * @param $date_ini
     * @param $date_fim
     * @return string
     */
    public function validaRangeDates($date_ini, $date_fim, $id_ciclo = null)
    {
        return $this->tabBancoDeHorasCiclosModel->validaRangeDates( $date_ini, $date_fim, $id_ciclo );
    }
    
    
    /**
     * @info Exibe o formulário
     *
     * @param void
     * @return void
     */
    public function showFormularioLista($origem=null)
    {
        $dados = $this->tabBancoDeHorasCiclosModel->registrosBancoDeHorasCiclos();
        $this->tabBancoDeHorasCiclosView->showFormularioLista( $dados, $origem );
    }

} // END class TabBancoDeHorasCiclosController
