<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabBancoDeCompensacoesModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : banco_de_horas
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - src/models
 * @Arquivo   - TabBancoDeCompensacoesModel.php
 *
 * @author Edinalvo Rosa
 */
class TabBancoDeCompensacoesModel extends formPadrao
{
    /*
    * Atributos
    */
    /* @var OBJECT */ public $conexao = NULL;

    ## @constructor
    #+-----------------------------------------------------------------+
    # Construtor da classe
    #+-----------------------------------------------------------------+
    #
    function __construct()
    {
        # Faz conexão
        $this->conexao = new DataBase();
    }


    /**
     * @info Exibe quadro de saldo
     *
     * @param string $siape
     * @return void
     */
    public function DadosAcumuloHoras($mat = null, $dia = null)
    {
        if (empty($mat) || is_null($mat))
        {
            $mat = $_SESSION['sMatricula'];
        }

        if (empty($dia) || is_null($dia))
        {
            $dia = date('d/m/Y');
        }

        $mat = getNovaMatriculaBySiape($mat);

        //VALIDA SE AS DATAS DA AUTORIZAÇÃO ESTÃO DENTRO DO CICLO
        $query = "
            SELECT
                DATE_FORMAT(ciclos.data_fim, '%d/%m/%Y') AS data_fim ,
                DATE_FORMAT(ciclos.data_inicio, '%d/%m/%Y') AS data_inicio,
                acumulos_horas.horas,
                acumulos_horas.usufruto,
                (SUM(acumulos_horas.horas) - SUM(acumulos_horas.usufruto)) AS saldo
            FROM 
                autorizacoes_servidores
            JOIN 
                ciclos ON autorizacoes_servidores.ciclo_id = ciclos.id
            LEFT JOIN 
                acumulos_horas ON autorizacoes_servidores.siape = acumulos_horas.siape
            WHERE 
                CURDATE() BETWEEN autorizacoes_servidores.data_inicio AND autorizacoes_servidores.data_fim 
                AND autorizacoes_servidores.siape = :siape
        ";

        $paramns = array(
            array(":siape", $mat, PDO::PARAM_STR)
        );

        // EXECUTA A QUERY
        $this->conexao->query($query, $paramns);
        
        return $this->conexao->fetch_assoc();
    }

} // END class TabBancoDeCompensacoesModel
