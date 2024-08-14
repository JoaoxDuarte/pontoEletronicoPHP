<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabIsencaoPontoModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : tabisencao_ponto
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - inc/models
 * @Arquivo   - TabIsencaoPontoModel.php
 *
 * @author Edinalvo Rosa
 */
class TabIsencaoPontoModel
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
     * @info Registros Isenção de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosIsencaoDePonto()
    {
        $query = "
        SELECT id, codigo, texto, tipo, tabela, obrigatorio_isencao, ativo
            FROM isencao_ponto
                ORDER BY tipo, codigo
        ";

        $this->conexao->query( $query );

        return $this->conexao;
    }


    /*
     * @info Registros Isenção de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosIsencaoDePontoPorID($id=null)
    {
        $query = "
        SELECT id, codigo, texto, tipo, tabela, obrigatorio_isencao, ativo
            FROM isencao_ponto
                WHERE id = :id
                    ORDER BY tipo, codigo
        ";
        
        $param = array(
            array( ':id', $id, PDO::PARAM_INT),
        );

        $this->conexao->query( $query, $param );

        return $this->conexao;
    }


    /*
     * @info Registros Isenção de Ponto por código
     *
     * @param void
     * @return object
     */
    public function registrosIsencaoDePontoPorCodigo($codigo=null)
    {
        $query = "
        SELECT id, codigo, texto, tipo, tabela, obrigatorio_isencao, ativo
            FROM isencao_ponto
                WHERE codigo = :codigo
                    ORDER BY tipo, codigo
        ";
        
        $param = array(
            array( ':codigo', $codigo, PDO::PARAM_STR),
        );

        $this->conexao->query( $query, $param );

        return $this->conexao;
    }

    
    /**
     * @info Tabela que corresponde ao tipo de isenção
     * 
     * @param sgtring $tipo
     * @return string
     */
    public function tabelaParaTipo($tipo=null)
    {
        $tipo_escolhido = (substr($tipo,0,3) == "DAS" ? "DAS" : $tipo);
        
        switch ($tipo_escolhido)
        {
            case "Função":
            case "DAS":
                $tabela = "tabfunc";
                break;
            
            case "Cargo":
                $tabela = "tabcargo";
                break;
            
            case "Regime":
                $tabela = "tabregime";
                break;
            
            case "Situação Funcional":
                $tabela = "tabsitcad";
                break;
            
            default:
                $tabela = "";
                break;
        }
        
        return $tabela;
    }

    /**
     * @info Inlcui novo registro de isenção de ponto
     *
     * @param array $var
     * @return boolean TRUE sucesso
     */
    public function insert($var=null)
    {
        $retorno = '';
       
        if ( !is_null($var) )
        {
            $tabela = $this->tabelaParaTipo($var['tipo']);
            
            // verifica se já existe item igual
            $isencao = $this->registrosIsencaoDePontoPorCodigo($var['codigo']);
        
            if ($isencao->num_rows() > 0)
            {
                $retorno = 'ja_existe';
            }
            else
            {
                $this->conexao->query("
                INSERT INTO isencao_ponto
                    (codigo,texto,tipo,tabela,obrigatorio_isencao,ativo)
                        VALUES (:codigo, :texto, :tipo, :tabela, :obrigatorio_isencao, :ativo)
                ",
                array(
                    array(":codigo", mb_strtoupper($var['codigo']), PDO::PARAM_STR),
                    array(":texto",  mb_strtoupper($var['texto']),  PDO::PARAM_STR),
                    array(":tipo",   $var['tipo'],   PDO::PARAM_STR),
                    array(":tabela", $tabela,        PDO::PARAM_STR),
                    array(":obrigatorio_isencao", $var['obrigatorio_isencao'], PDO::PARAM_STR),
                    array(":ativo",  $var['ativo'],  PDO::PARAM_STR)
                ));

                $affected_rows = ($this->conexao->affected_rows() > 0);

                if ($affected_rows)
                {
                    registraLog("Item de Isenção de Ponto (".$var['codigo'].") registrado com sucesso!");
                    $retorno = 'gravou';
                }
                else
                {
                    registraLog("Item de Isenção de Ponto (".$var['codigo'].") NÃO foi registrado!");
                    $retorno = 'nao_gravou';
                }
            }
        }
        
        return $retorno;
    }

    /**
     * @info Inlcui novo registro de isenção de ponto
     *
     * @param array $var
     * @return boolean TRUE sucesso
     */
    public function update($var=null)
    {
        $retorno = '';
       
        if ( !is_null($var) )
        {
            $tabela = $this->tabelaParaTipo($var['tipo']);

            // verifica se já existe item igual
            $isencao = $this->registrosIsencaoDePontoPorID($var['id']);
        
            if ($isencao->num_rows() == 0)
            {
                $retorno = 'nao_existe';
            }
            else
            {
                $this->conexao->query("
                UPDATE isencao_ponto
                    SET codigo = :codigo, 
                        texto  = :texto, 
                        tipo   = :tipo, 
                        tabela = :tabela, 
                        obrigatorio_isencao = :obrigatorio_isencao,
                        ativo  = :ativo
                            WHERE id = :id
                ",
                array(
                    array(":id",     $var['id'], PDO::PARAM_STR),
                    array(":codigo", mb_strtoupper($var['codigo']), PDO::PARAM_STR),
                    array(":texto",  mb_strtoupper($var['texto']),  PDO::PARAM_STR),
                    array(":tipo",   $var['tipo'],   PDO::PARAM_STR),
                    array(":tabela", $tabela,        PDO::PARAM_STR),
                    array(":obrigatorio_isencao", $var['obrigatorio_isencao'], PDO::PARAM_STR),
                    array(":ativo",  $var['ativo'],  PDO::PARAM_STR),
                ));

                $affected_rows = ($this->conexao->affected_rows() > 0);

                if ($affected_rows)
                {
                    registraLog("Alterado com sucesso (".$var['codigo'].")!");
                    $retorno = 'gravou';
                }
                else
                {
                    registraLog("Alteração NÃO foi realizada (".$var['codigo'].")!");
                    $retorno = 'nao_gravou';
                }
            }
        }
        
        return $retorno;
    }


    /**
     *
     * @param type $id
     * @return boolean TRUE sucesso
     */
    public function delete( $id=null )
    {
        $query = "
        DELETE FROM isencao_ponto
            WHERE id = :id
        ";

        $params =  array(
            array(":id", $id, PDO::PARAM_INT),
        );

        $this->conexao->query( $query, $params );

        $affected_rows = ($this->conexao->affected_rows() > 0);

        if ($affected_rows)
        {
            registraLog("Excluído o registro ".$id);
            $_SESSION["mensagem-usuario"] = "Excluído o registro ".$id;
        }

        return $affected_rows;
    }


    /**
     * @info Lista tipos de grupos para indicar Isenção de Ponto
     *
     * @param void
     * @return array
     */
    public function tipoParaIsencao()
    {
        return enumExplode('isencao_ponto','tipo');
    }


    /**
     * @info Lista tipos de grupos para indicar Isenção de Ponto
     *
     * @param void
     * @return array
     */
    public function listaTabelas()
    {
        //$this->conexao->query( "SHOW TABLES WHERE LEFT(tables_in_sisref,3) = 'tab'" );
        //return $this->conexao->fetch_array();

        return array(
            'tabcargo'  => 'Cargos',
            'tabfunc'   => 'Funções',
            'tabsetor'  => 'Unidades',
            'tabsitcad' => 'Situação Funcional'
        );
    }

} // END class TabIsencaoPontoModel
