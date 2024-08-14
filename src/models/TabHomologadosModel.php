<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabHomologadosModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : homologados
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - src/models
 * @Arquivo   - TabHomologadosModel.php
 *
 * @author Edinalvo Rosa
 */
class TabHomologadosModel
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
        $status  = '<font color=red><b>NÃO HOMOLOGADO</b></font>';

        // testa parametros
        $mes = (is_null($mes) || empty($mes) ? date('m') : $mes );
        $ano = (is_null($ano) || empty($ano) ? date('Y') : $ano );
        
        if ( !is_null($siape) && !empty($siape))
        {
            // dados
            $comp       = $ano . $mes;
            $sMatricula = $siape;

            // conexao com a base de dados
            if ( !is_null($destino) && !empty($destino))
            {
                $this->conexao->setDestino( $destino );
            }
            
            if ( !is_null($retorno) && !empty($retorno))
            {
                $this->conexao->setVoltar( $retorno );
            }
            
            $this->conexao->query("
                SELECT 
                    IF(IFNULL(homologados.homologado,'N') NOT IN ('V','S'),'N','S') AS homologado 
                FROM 
                    homologados 
                WHERE 
                    mat_siape= :siape 
                    AND compet= :compet 
                ", array(
                    array(':siape', $siape, PDO::PARAM_STR),
                    array(':compet', $comp, PDO::PARAM_STR)
                )
            );

            $oStatus = $this->conexao->fetch_object();

            if ($oStatus->homologado == "S")
            {
                $status = "<b>HOMOLOGADO</b>";
            }
        }
        
        return $status;
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
        $comp_inverte = $ano . $mes;
        
        if (is_null($homologador_siape) || empty($homologador_siape))
        {
            $homologador_siape = $_SESSION['sMatricula'];
        }
        
        $this->conexao->setMensagem('Falha no registro da homologação!');
        $this->conexao->query("SELECT homologado FROM homologados WHERE mat_siape = :siape AND compet = :compet ", array(
            array(':siape',  $siape,        PDO::PARAM_STR),
            array(':compet', $comp_inverte, PDO::PARAM_STR),
        ));
        $linhas = $this->conexao->num_rows();

        if ($linhas == 0)
        {
            $this->conexao->query('INSERT INTO homologados SET compet = :compet, mat_siape = :siape, homologado = "S", homologado_siape = :siape_homologador, homologado_data = NOW(), desomologado_motivo = "" ', array(
                array(':siape',             $siape,             PDO::PARAM_STR),
                array(':compet',            $comp_inverte,      PDO::PARAM_STR),
                array(':siape_homologador', $homologador_siape, PDO::PARAM_STR),
            ));
        }
        else
        {
            $this->conexao->query("UPDATE homologados SET homologado = 'S', homologado_siape = :siape_homologador, homologado_data = NOW() WHERE compet = :compet AND mat_siape = :siape ", array(
                array(':siape',             $siape,             PDO::PARAM_STR),
                array(':compet',            $comp_inverte,      PDO::PARAM_STR),
                array(':siape_homologador', $homologador_siape, PDO::PARAM_STR),
            ));
        }

        $this->conexao->query("
            INSERT homologados_historico 
            SELECT 
                0, compet, mat_siape, homologado, homologado_siape, 
                homologado_data, desomologado_motivo, desomologado_siape, 
                desomologado_data, cod_lot, verificado_siape, verificado_data, 
                registrado_por, registrado_em, '".($linhas == 0 ? 'I' : 'A')."',
                :operador, NOW()
            FROM 
                homologados 
            WHERE 
                compet = :compet
                AND mat_siape = :siape
        ", array(
            array( ':compet',   $comp_inverte, PDO::PARAM_STR ),
            array( ':siape',    $siape,        PDO::PARAM_STR ),
            array( ':operador', $_SESSION['sMatricula'], PDO::PARAM_STR ),
        ));

        // registrando homologação - compatibilidade
        $this->conexao->query('UPDATE servativ SET freqh = "S" WHERE mat_siape = :siape ', array(
            array(':siape', $siape, PDO::PARAM_STR),
        ));

        $this->conexao->query('UPDATE tabsetor SET tfreq = "S", dfreq = "N" WHERE codigo = :codigo ', array(
            array(':codigo', $lotacao, PDO::PARAM_STR),
        ));

        $this->conexao->query('UPDATE usuarios SET recalculo = "S", refaz_frqano = "S" WHERE siape = :siape ', array(
            array(':siape', $siape, PDO::PARAM_STR),
        ));
    }


    /**
     * @info Inclui novo registro
     *
     * @param array $var
     * @return boolean TRUE sucesso
     */
    public function insert($var=null)
    {
        $retorno = '';

        return $retorno;
    }

    /**
     * @info Atualiza registro
     *
     * @param array $var
     * @return boolean TRUE sucesso
     */
    public function update($var=null)
    {
        $retorno = '';

        return $retorno;
    }


    /**
     *
     * @param string $siape
     * @param string $dia
     * @return boolean TRUE sucesso
     */
    public function delete( $siape=null, $dia=null )
    {
        $siape = (is_null($siape) || empty($siape) ? $_SESSION['sMatricula'] : getNovaMatriculaBySiape($siape));
        $dia   = (is_null($dia)   || empty($dia)   ? date('d/m/Y') : $dia);

        $resultado = "error";

        return $resultado;
    }

} // END class TabHomologadosModel
