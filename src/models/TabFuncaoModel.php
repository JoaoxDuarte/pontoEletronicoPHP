<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabFuncaoModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : tabcargo
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - src/models
 * @Arquivo   - TabFuncaoModel.php
 *
 * @author Edinalvo Rosa
 */
class TabFuncaoModel
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
     * @info SQL Padrão
     *
     * @param $cargo Código do cargo (opcional)
     * @return object
     */
    public function padraoSQL($id=null,$acao='',$where=null,$param=null)
    {
        $oDBase = new DataBase();
        
        $filtro = array();
        $params = array();

        $filtro[] = "upag = :upag";
        $params[] = array( ':upag', $_SESSION['upag'], PDO::PARAM_STR);
        
        if ( !is_null($id) && !empty($id))
        {
            $filtro[] = "tabfunc.id = :id";
            $params[] = array( ':id', $id, PDO::PARAM_STR);
        }

        if ( is_array($where) )
        {
            $filtro = array_merge($filtro, $where);
        }

        if ( is_array($param) )
        {
            $params = array_merge($params, $param);
        }
        
        $query = "
        SELECT
            tabfunc.NUM_FUNCAO, tabfunc.COD_FUNCAO, 
            IF(ISNULL(tabfuncao.desc_funcao),
                tabfunc.DESC_FUNC,tabfuncao.desc_funcao) AS DESC_FUNC,
            tabfunc.COD_LOT, tabfunc.UPAG, tabfunc.SIT_PAG, tabfunc.INDSUBS,
            tabfunc.RESP_LOT, tabfunc.ATIVO, tabfunc.id
        FROM tabfunc
        LEFT JOIN tabfuncao ON tabfunc.COD_FUNCAO = tabfuncao.cod_funcao
        WHERE
            " . implode( " AND ", $filtro) . "
        ORDER BY tabfunc.ativo DESC, tabfunc.DESC_FUNC
        ";

        $oDBase->setMensagem("Problemas no acesso a Tabela FUNÇÕES (E200057.".__LINE__.").");
        $oDBase->query( $query, $params );

        return $oDBase;
    }


    /*
     * @info Registros
     *
     * @param void
     * @return object
     */
    public function registros()
    {
        return $this->padraoSQL();
    }


    /*
     * @info Registros
     *
     * @param void
     * @return json
     */
    public function registrosRetornoAjax()
    {
        $oDBase = $this->registros();

        $result = array();

        while ($dados = $oDBase->fetch_object())
        {
            $alterar   = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a style=\"text-align:center;\" href=\"javascript:funcaoAlterar('". tratarHTML($dados->id) . "');\"><span class=\"glyphicon glyphicon-pencil\" alt=\"Editar cargo\" title=\"Editar cargo\"></span></a>";
            $separador = ""; //&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            $excluir   = ""; //"<a class=\"text-center\" href=\"javascript:Delete('" . tratarHTML($dados->id) . "');\"><span class=\"glyphicon glyphicon-trash\" alt=\"Excluir cargo\" title=\"Excluir cargo\"></span></a>";

            $result[] = array(
                utf8_encode(
                    $alterar .
                    $separador .
                    $excluir
                ),
                $dados->NUM_FUNCAO,
                $dados->COD_FUNCAO,
                utf8_encode($dados->DESC_FUNC),
                $dados->COD_LOT,
                $dados->UPAG,
                utf8_encode(($dados->INDSUBS  != "S" ? "NÃO" : "SIM")),
                utf8_encode(($dados->RESP_LOT != "S" ? "NÃO" : "SIM")),
                utf8_encode(($dados->ATIVO    != "S" ? "NÃO" : "SIM")),
            );
        }

        $myData = array(
            'data' => $result);

        print json_encode($myData);
    }


    /*
     * @info Registros
     *
     * @param integer $id
     * @param string $acao
     * @return resource
     */
    public function registrosPorID($id=null,$acao='')
    {
        return $this->padraoSQL($id,$acao);
    }


    /*
     * @info Registros
     *
     * @param integer $id
     * @param string $acao
     * @return resource
     */
    public function registrosPorFuncao($var=null, $id_func=null)
    {
        $where = array();
        $param = array();

        $where[] = "tabfunc.cod_funcao = :cod_funcao";
        $where[] = "tabfunc.cod_lot = :cod_lot";
        $where[] = "tabfunc.resp_lot = :resp_lot";

        $param[] = array( ':cod_funcao', $var['COD_FUNCAO'], PDO::PARAM_STR);
        $param[] = array( ':cod_lot',    $var['COD_LOT'],    PDO::PARAM_STR);
        $param[] = array( ':resp_lot',   $var['RESP_LOT'],   PDO::PARAM_STR);
        
        if ( !is_null($id_func) )
        {
            $where[] = "tabfunc.id <> :id";
            $param[] = array( ':id', $id_func, PDO::PARAM_STR);
        }
        
        return $this->padraoSQL($id,$acao,$where,$param);
    }


    /*
     * @info Registros Funções Siape
     *
     * @param string $cod
     * @return object
     */
    public function dadosFuncaoResponsavelUnidade($dados=null)
    {
        $oDBase = new DataBase();
        
        $query = "
            SELECT
                tabfunc.NUM_FUNCAO, tabfunc.COD_FUNCAO, 
                IF(ISNULL(tabfuncao.desc_funcao),
                    tabfunc.DESC_FUNC,
                    tabfuncao.desc_funcao) AS DESC_FUNC, 
                tabfunc.DESC_FUNC,
                tabfunc.COD_LOT, tabfunc.UPAG, tabfunc.SIT_PAG, 
                tabfunc.INDSUBS, tabfunc.RESP_LOT, tabfunc.ATIVO, 
                tabfunc.id
            FROM 
                tabfunc
            LEFT JOIN 
                tabfuncao ON tabfunc.COD_FUNCAO = tabfuncao.cod_funcao
            WHERE 
                tabfunc.upag = :upag
                AND tabfunc.COD_FUNCAO = :cod_funcao
                AND tabfunc.COD_LOT = :cod_lot
                AND tabfunc.RESP_LOT = 'S'
            ORDER BY 
                tabfunc.ativo DESC, tabfunc.DESC_FUNC
        ";

        $params = array(
            array( ':upag',       $_SESSION['upag'],    PDO::PARAM_STR),
            array( ':cod_funcao', $dados['COD_FUNCAO'], PDO::PARAM_STR),
            array( ':cod_lot',    $dados['COD_LOT'],    PDO::PARAM_STR),
            array( ':resp_lot',   'S',                  PDO::PARAM_STR),
        );
        
        $oDBase->setMensagem("Problemas no acesso a Tabela FUNÇÕES (E200058.".__LINE__.").");
        $oDBase->query( $query, $params );
        
        return $oDBase;
    }


    /*
     * @info Registros Funções Responsavel
     *
     * @param string $cod
     * @return object
     */
    public function dadosFuncoesSiapeCodigo($cod=null)
    {
        $oDBase = new DataBase();
        
        $where = "";
        
        if ( !is_null($cod) && !empty($cod) )
        {
            $where  = " WHERE tabfuncao.cod_funcao = :cod_funcao ";
            $params = array(
                array( ':cod_funcao', $cod, PDO::PARAM_STR),
            );
        }

        $query = "
            SELECT 
                id_funcao, cod_funcao, desc_funcao 
            FROM 
                tabfuncao
            $where
            GROUP BY
                cod_funcao
            ORDER BY
                cod_funcao, desc_funcao
        ";

        $oDBase->setMensagem("Problemas no acesso a Tabela FUNÇÕES (E200059.".__LINE__.").");
        $oDBase->query( $query, $params );
        
        return $oDBase;
    }


    /**
     * @info Lista códigos de funções (SIAPE)
     *
     * @param void
     * 
     * @return object Códigos das funções e dewscrição
     */
    public function CarregaCodigoFuncoesSiape($id=null)
    {
        $oDBase = new DataBase();
        
        $where = "";
        $params = null;
        
        if ( !is_null($id) && !empty($id) )
        {
            $where  = " WHERE tabfuncao.id_funcao = :id ";
            $params = array(
                array( ':id', $id, PDO::PARAM_STR),
            );
        }

        $query = "
            SELECT 
                id_funcao, UPPER(cod_funcao) AS cod_funcao, desc_funcao 
            FROM 
                tabfuncao
            $where
            ORDER BY
                cod_funcao, desc_funcao
        ";

        $oDBase->setMensagem("Problemas no acesso a Tabela FUNÇÕES (E200060.".__LINE__.").");
        $oDBase->query( $query, $params );
        
        return $oDBase;
    }


    /**
     * @info Inlcui novo registro
     *
     * @param array $var
     * @return boolean TRUE sucesso
     */
    public function insert($var=null)
    {
        $oDBase = new DataBase();

        $retorno = '';

        if ( !is_null($var) )
        {
            $existe = $this->registrosPorFuncao($var);
            
            if ($existe->num_rows() > 0)
            {
                $retorno = 'ja_existe';
            }
            else if (!empty($var['id'])) 
            {
                // verifica se já existe item igual
                $existe = $this->registrosPorID($var['id'],'inclusao');

                if ($existe->num_rows() > 0)
                {
                    $retorno = 'ja_existe';
                }
            }
            else 
            {
                $descricao = $this->dadosFuncoesSiapeCodigo( $var['COD_FUNCAO'] )->fetch_object()->desc_funcao;
                
                $oDBase->setMensagem("Problemas no acesso a Tabela FUNÇÕES (E200061.".__LINE__.").");
                $oDBase->query("
                INSERT INTO tabfunc
                    (NUM_FUNCAO, COD_FUNCAO, DESC_FUNC, COD_LOT, cod_uorg,
                     UPAG, SIT_PAG, INDSUBS, RESP_LOT, ATIVO, TIPO,
                     registro_siape, registro_data, id)
                    VALUES
                    (:NUM_FUNCAO, :COD_FUNCAO, :DESC_FUNC, :COD_LOT, :cod_uorg,
                     :UPAG, :SIT_PAG, :INDSUBS, :RESP_LOT, :ATIVO, :TIPO,
                     :registro_siape, NOW(), 0)
                ",
                array(
                    array(":NUM_FUNCAO",     $var['NUM_FUNCAO'],      PDO::PARAM_STR),
                    array(":COD_FUNCAO",     $var['COD_FUNCAO'],      PDO::PARAM_STR),
                    array(":DESC_FUNC",      mb_strtoupper(str_to_utf8($descricao)), PDO::PARAM_STR),
                    array(":COD_LOT",        $var['COD_LOT'],         PDO::PARAM_STR),
                    array(":cod_uorg",       $var['COD_LOT'],         PDO::PARAM_STR),
                    array(":UPAG",           $var['UPAG'],            PDO::PARAM_STR),
                    array(":SIT_PAG",        "N",                     PDO::PARAM_STR),
                    array(":INDSUBS",        $var['INDSUBS'],         PDO::PARAM_STR),
                    array(":RESP_LOT",       $var['RESP_LOT'],        PDO::PARAM_STR),
                    array(":ATIVO",          'S',                     PDO::PARAM_STR),
                    array(":TIPO",           "T",                     PDO::PARAM_STR),
                    array(":registro_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
                ));

                $affected_rows = ($oDBase->affected_rows() > 0);

                if ($affected_rows)
                {
                    $oDBase->query( "SELECT LAST_INSERT_ID() AS id" );
                    $id = $oDBase->fetch_object()->id;

                    $oDBase->setMensagem("Problemas no acesso a Tabela FUNÇÕES (E200061.".__LINE__.").");
                    $oDBase->query("
                    UPDATE tabfunc
                        SET
                            tabfunc.NUM_FUNCAO = RIGHT(CONCAT('00000',:NUM_FUNCAO),5)
                    WHERE
                        tabfunc.id = :id
                    ORDER BY
                        tabfunc.NUM_FUNCAO
                    ",
                    array(
                        array(":NUM_FUNCAO", $id, PDO::PARAM_STR),
                        array(":id",         $id, PDO::PARAM_INT),
                    ));

                    registraLog("Função (".$var['NUM_FUNCAO'].") registrada com sucesso!");
                    $retorno = 'gravou';
                }
                else
                {
                    registraLog("Função(".$var['NUM_FUNCAO'].") NÃO foi registrada!");
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
        $oDBase = new DataBase();

        if (is_null($var)) 
        {
            return ""; 
        }
        
        // verifica se já existe item igual
        $existe = $this->registrosPorID($var['id']);

        if ($existe->num_rows() == 0)
        {
            return 'nao_existe'; 
        }
        
        // verifica se já existe outra função como os mesmos dados
        $existe = $this->registrosPorFuncao($var, $var['id']);
            
        if ($existe->num_rows() > 0)
        {
            return 'ja_existe';
        }
                
        $oDBase->setMensagem("Problemas no acesso a Tabela FUNÇÕES (E200062.".__LINE__.").");
        $oDBase->query("
        UPDATE tabfunc
        SET
            COD_FUNCAO     = :COD_FUNCAO,
            DESC_FUNC      = :DESC_FUNC,
            COD_LOT        = :COD_LOT,
            cod_uorg       = :cod_uorg,
            UPAG           = :UPAG,
            SIT_PAG        = :SIT_PAG,
            INDSUBS        = :INDSUBS,
            RESP_LOT       = :RESP_LOT,
            ATIVO          = :ATIVO,
            TIPO           = :TIPO,
            registro_siape = :registro_siape,
            registro_data  = NOW()
        WHERE tabfunc.id = :id
        ", array(
            array(":id",             $var['id'],              PDO::PARAM_INT),
            array(":COD_FUNCAO",     $var['COD_FUNCAO'],      PDO::PARAM_STR),
            array(":DESC_FUNC",      mb_strtoupper($var['DESC_FUNC']), PDO::PARAM_STR),
            array(":COD_LOT",        $var['COD_LOT'],         PDO::PARAM_STR),
            array(":cod_uorg",       $var['COD_LOT'],         PDO::PARAM_STR),
            array(":UPAG",           $var['UPAG'],            PDO::PARAM_STR),
            array(":SIT_PAG",        "N",                     PDO::PARAM_STR),
            array(":INDSUBS",        $var['INDSUBS'],         PDO::PARAM_STR),
            array(":RESP_LOT",       $var['RESP_LOT'],        PDO::PARAM_STR),
            array(":ATIVO",          $var['ATIVO'],           PDO::PARAM_STR),
            array(":TIPO",           $var['TIPO'],            PDO::PARAM_STR),
            array(":registro_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
        ));

        $affected_rows = ($oDBase->affected_rows() > 0);

        if ($affected_rows)
        {
            registraLog("Alterado com sucesso (".$var['id'].")!");
            $retorno = 'gravou';
        }
        else
        {
            registraLog("Alteração NÃO foi realizada (".$var['id'].")!");
            $retorno = 'nao_gravou';
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
        $oDBase = new DataBase();
        
        $resultado = "error";

        if ($num_rows > 0)
        {
            $resultado = "warning";
        }
        else
        {
            $query = "
            UPDATE tabfunc
                SET ativo          = :ativo,
                    registro_siape = :registro_siape,
                    registro_data  = NOW()
                        WHERE tabfunc.id = :codigo
            ";

            $params = array(
                array(":codigo",         $id, PDO::PARAM_STR),
                array(":ativo",          'N', PDO::PARAM_STR),
                array(":registro_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
            );

            $oDBase->setMensagem("Problemas no acesso a Tabela FUNÇÕES (E200063.".__LINE__.").");
            $oDBase->query( $query, $params );

            if ($oDBase->affected_rows() > 0)
            {
                $resultado = "success";
                registraLog("Deletou a função ".$id);
            }
        }

        return $resultado;
    }

} // END class TabFuncaoModel
