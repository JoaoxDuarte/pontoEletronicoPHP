<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "class_ocorrencias_grupos.php" );


/**
 * @class TabBancoDeHorasCiclosModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : ciclos
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - src/models
 * @Arquivo   - TabBancoDeHorasCiclosModel.php
 *
 * @author Edinalvo Rosa
 */
class TabBancoDeHorasCiclosModel extends formPadrao
{
    /*
    * Atributos
    */
    /* @var OBJECT */ public $conexao = NULL;
    /* @var OBJECT */ public $objOcorrenciasGrupos = null;


    ## @constructor
    #+-----------------------------------------------------------------+
    # Construtor da classe
    #+-----------------------------------------------------------------+
    #
    function __construct()
    {
        # Faz conexão
        $this->conexao = new DataBase();

        // INSTANCIA GRUPOS DE OCORRENCIAS
        $this->objOcorrenciasGrupos = new OcorrenciasGrupos();
    }

    
    /**
     * @info Carrega o ID do ciclo corrente
     *
     * @param void
     * @return integer ID do ciclo atual
     */
    public function getCicloCurrent()
    {
        $orgao = getOrgaoByUorg();
        
        $oDBase = new DataBase();
        $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E200024.".__LINE__.").");

        $oDBase->query( "
            SELECT ciclos.id 
                FROM ciclos
                    WHERE
                        orgao = :orgao 
                        AND CURDATE() BETWEEN ciclos.data_inicio 
                                          AND ciclos.data_fim
        ", array(
            array( ':orgao', $orgao, PDO::PARAM_STR )
        ));

        return $oDBase->fetch_assoc()['id'];
    }


    /*
     * @info Registros Ciclos - Banco de Horas
     *
     * @param void
     * @return object
     */
    public function registrosBancoDeHorasCiclos()
    {
        $oDBase = new DataBase();
        $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E200025.".__LINE__.").");

        $oDBase->query( "
            SELECT id, data_inicio, data_fim, orgao
                FROM ciclos 
                    WHERE SUBSTR(ciclos.orgao,1,5) = :orgao 
                        ORDER BY ciclos.id DESC
        ",
        array(
            array(":orgao", getOrgaoByUorg($_SESSION['sLotacao']), PDO::PARAM_STR)
        ));

        return $oDBase;
    }

    
    /**
     * @info Valida range entre datas
     * 
     * @param $date_ini
     * @param $date_fim
     * @return string
     */
    public function validaRangeDates($date_ini, $date_fim, $id_ciclo = null)
    {
        // FORMATA AS DATAS VINDAS DO AJAX
        $dateiniformated = conv_data($date_ini);
        $datefimformated = conv_data($date_fim);

        // ESSA QUERY FAZ AS SEGUINTES VALIDAÇÕES,
        // NÃO PERMITE QUE SEJA CADASTRADO NENHUM NOVO CICLO
        // ONDE AS DATAS BATAM COM ALGUMA JÁ EXISTENTE NO BANCO,
        // PARA O ÓRGÃO DO USUÁRIO LOGADO.
        $query = "
        SELECT *
            FROM ciclos
                WHERE
                    (LEFT(ciclos.orgao,5) = :orgao
                    AND ((:data_inicio BETWEEN ciclos.data_inicio AND ciclos.data_fim)
                    OR (:data_fim BETWEEN ciclos.data_inicio AND ciclos.data_fim)
                    OR (((:data_inicio < ciclos.data_inicio)
                        AND (:data_fim > ciclos.data_fim)))))
        ";

        // CASO EXISTA ID, EH ADICIONADO MAIS ALGUNS PARAMETROS
        if (!empty($id_ciclo))
        {
            $query .= " AND ciclos.id <> :id";
            $paramns = array(
                array(":orgao",       getOrgaoByUorg( $_SESSION['sLotacao'] ), PDO::PARAM_STR),
                array(":data_inicio", $dateiniformated, PDO::PARAM_STR),
                array(":data_fim",    $datefimformated, PDO::PARAM_STR),
                array(":id",          $id_ciclo,        PDO::PARAM_INT)
            );
        }
        else
        {
            $paramns = array(
                array(":orgao",       getOrgaoByUorg( $_SESSION['sLotacao'] ), PDO::PARAM_STR),
                array(":data_inicio", $dateiniformated, PDO::PARAM_STR),
                array(":data_fim",    $datefimformated, PDO::PARAM_STR));
        }
        //var_dump($paramns);
        //fimDie(__LINE__, $query, false, __FILE__ . '<br>' . __FUNCTION__ . ' ' . __CLASS__ . ' ' . __METHOD__);
        
        // EXECUTA A QUERY
        $oDBase = new DataBase();
        $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E200026.".__LINE__.").");

        $oDBase->query($query, $paramns);

        // CASO EXISTA REGISTROS EH RETORNADO FALSE O QUE POSSIBILITA O CADASTRO
        if (empty($oDBase->fetch_assoc()))
        {
            return json_encode(array("success" => true, "bloqueia_cadastro" => false));
        }
        
        return json_encode(array("success" => true, "bloqueia_cadastro" => true));
    }

    
    /*
     * @info Gravar os dados do Ciclo - Banco de Horas
     * 
     * @param void
     * @return void
     */
    public function Gravar()
    {
        $start_date = $_POST['data_inicio']; // Data Inicial sem formatações
        $end_date   = $_POST['data_final']; // Data Final sem formatações
        $lotacao    = $_POST['lota']; // Lotação

        // Formatação das datas
        $dateiniformated = conv_data($start_date);
        $datefimformated = conv_data($end_date);

        $bool = true; // Variável de controle

        // Verifica se a data inicial foi informada
        if(empty($start_date) AND $bool)
        {
            setMensagemUsuario('Data Inicial não informada!','danger');
            $bool = false;
        }

        // Verifica se a data final foi informada
        if(empty($end_date) AND $bool)
        {
            setMensagemUsuario('Data Final não informada!','danger');
            $bool = false;
        }

        // Verifica se as datas não são de anos diferentes
        if (validateYears($start_date , $end_date) AND $bool)
        {
            setMensagemUsuario('O Ciclo precisa estar dentro de um mesmo ano!','danger');
            $bool = false;
        }

        // Verifica se a data inicial é maior que a data final-
        if ((strtotime($dateiniformated) > strtotime($datefimformated)) AND $bool)
        {
            setMensagemUsuario('Data inicial não pode ser maior que a final!','danger');
            $bool = false;
        }

        // Vefifica se o range de datas selecionado é válido.
        $result = json_decode($this->validaRangeDates($start_date, $end_date));
        
        if($result->bloqueia_cadastro AND $bool)
        {
            setMensagemUsuario(' Já existe ciclo cadastrado dentro desse período!','danger');
            $bool = false;
        }


        if($bool) 
        {
            $this->gravar_ciclo();
            registraLog("Cadastro de ciclos");
            replaceLink("tabvalida.php?aba=qui");
        }
    }

    
    
    /**
     * @param $post
     */
    public function gravar_ciclo()
    {
        $oDBase = new DataBase();
        $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E200027.".__LINE__.").");

        $result = $oDBase->query( "
            INSERT INTO ciclos (data_inicio, data_fim, orgao)
                VALUES (:data_inicio, :data_fim, :orgao)
        ",
        array(
            array(":data_inicio", conv_data($_POST['data_inicio']), PDO::PARAM_STR),
            array(":data_fim",    conv_data($_POST['data_final']),  PDO::PARAM_STR),
            array(":orgao",       getOrgaoByUorg($_POST['lota']),   PDO::PARAM_STR),
        ));

        return $result;
    }

    
    /**
     * @param $post
     */
    public function update_ciclo($post)
    {
        $oDBase = new DataBase();
        $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E200028.".__LINE__.").");

        $result = $oDBase->query("
            UPDATE ciclos
                SET data_fim = :data_fim
                    WHERE id = :id
        ",
        array(
            array(":data_fim", conv_data($post['data_final']), PDO::PARAM_STR),
            array(":id",       $post['id'],                    PDO::PARAM_INT),
        ));

        return $result;
    }
    

    /*
     * @info Registros Ciclos - Banco de Horas
     *
     * @param void
     * @return object
     */
    public function registrosBancoDeHorasCiclosPorID($id)
    {
        $oDBase = new DataBase();
        $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E200029.".__LINE__.").");

        $oDBase->query( "
        SELECT id, data_inicio, data_fim, orgao
            FROM ciclos
                WHERE id = :id
        ", 
        array(
            array( ':id', $id, PDO::PARAM_INT),
        ));

        return $oDBase;
    }


    /*
     * @info Registros Ciclos - Banco de Horas
     *
     * @param void
     * @return object
     */
    public function registrosBancoDeHorasCiclosPorOrgao($orgao)
    {
        $oDBase = new DataBase();
        $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E200030.".__LINE__.").");

        $oDBase->query( "
        SELECT id, data_inicio, data_fim, orgao
            FROM ciclos
                WHERE orgao = :orgao
                    ORDER BY data_inicio, data_fim
        ", 
        array(
            array( ':orgao', $orgao, PDO::PARAM_STR),
        ));

        return $oDBase;
    }

    /**
     * @info Inlcui novo registro de Ciclos - Banco de Horas
     *
     * @param array $var
     * @return boolean TRUE sucesso
     */
    public function insert($var)
    {
        $retorno = '';

        // verifica se já existe item igual
        $dados = $this->registrosBancoDeHorasCiclosPorOrgao($var['orgao']);

        if ($dados->num_rows() > 0)
        {
            $retorno = 'ja_existe';
        }
        else
        {
            $oDBase = new DataBase();
            $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E200031.".__LINE__.").");
    
            $oDBase->query("
                INSERT INTO ciclos (data_inicio, data_fim, orgao)
                    VALUES (:data_inicio, :data_fim, :orgao)
            ",
            array(
                array(":data_inicio", conv_data($var['data_inicio']), PDO::PARAM_STR),
                array(":data_fim",    conv_data($var['data_fim']),    PDO::PARAM_STR),
                array(":orgao",       $var['orgao'],                  PDO::PARAM_STR),
            ));

            $affected_rows = ($oDBase->affected_rows() > 0);

            if ($affected_rows)
            {
                //$this->historico(0,'I'); // Inclusão realizada
                registraLog("Ciclo de ".$var['data_inicio']." a ".$var['data_fim']." (".$var['orgao'].") registrado com sucesso!");
                $retorno = 'gravou';
            }
            else
            {
                registraLog("Ciclo de ".$var['data_inicio']." a ".$var['data_fim']." (".$var['orgao'].") NÃO foi registrado!");
                $retorno = 'nao_gravou';
            }
        }
        return $retorno;
    }

    /**
     * @info Inlcui novo registro de Ciclos - Banco de Horas
     *
     * @param array $var
     * @return boolean TRUE sucesso
     */
    public function update($var)
    {
        $retorno = '';

        // verifica se já existe item igual
        $dados = $this->registrosBancoDeHorasCiclosPorID($var['id']);

        if ($dados->num_rows() == 0)
        {
            $retorno = 'nao_existe';
        }
        else
        {
            $this->historico($var['id'],'A'); // Alteração realizada

            $oDBase = new DataBase();
            $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E200032.".__LINE__.").");

            $oDBase->query("
                UPDATE ciclos
                    SET 
                        data_inicio = :data_inicio,
                        data_fim    = :data_fim,
                        orgao       = :orgao
                            WHERE id = :id
            ",
            array(
                array(":id",          $var['id'],          PDO::PARAM_INT),
                array(":data_inicio", $var['data_inicio'], PDO::PARAM_STR),
                array(":data_fim",    $var['data_fim'],    PDO::PARAM_STR),
                array(":orgao",       $var['orgao'],       PDO::PARAM_STR),
            ));

            $affected_rows = ($oDBase->affected_rows() > 0);

            if ($affected_rows)
            {
                registraLog("Alterado Ciclo para ".$var['data_inicio']." a ".$var['data_fim']." (".$var['orgao'].")!");
                $retorno = 'gravou';
            }
            else
            {
                registraLog("Alteração NÃO foi realizada!");
                $retorno = 'nao_gravou';
            }
        }

        return $retorno;
    }


    /**
     * @info Desabilita o registro, ativo = 'N'
     *
     * @param type $id
     * @return boolean TRUE sucesso
     */
    public function delete( $id )
    {
        $resultado = "error";
        
        if ($num_rows > 0)
        {
            $resultado = "warning";
        }
        else
        {
            $oDBase = new DataBase();
            $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E200033.".__LINE__.").");
    
            $oDBase->query("
                DELETE ciclos
                    WHERE id = :id
            ",  
            array(
                array(":id", $id, PDO::PARAM_INT),
            ));

            $affected_rows = ($oDBase->affected_rows() > 0);

            if ($affected_rows)
            {
                $resultado = "success";
                registraLog("Deletou o registro da escala de ID ".$id." (".$descricao.")");
                $this->historico($id,'E');
            }
        }

        return $resultado;
    }

    /**
     * @info Histórico
     *
     * @param array $var
     * @return void
     */
    public function historico($id,$oper)
    {
        $oDBase = new DataBase();
        $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E200034.".__LINE__.").");

        $oDBase->query("
            INSERT INTO ciclos_historico
                SELECT 
                    0, id, data_inicio, data_fim, 
                    orgao, :oper, :operador_siape, NOW()
                        FROM ciclos
                            WHERE ciclos.id = :id
        ",
        array(
            array(":id",   $id,   PDO::PARAM_INT),
            array(":oper", $oper, PDO::PARAM_STR),
            array(":operador_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
        ));
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
        if (is_null($mat) || (is_string($mat) && empty($mat)))
        {
            $mat = $_SESSION['sMatricula'];
        }
        
        if (is_null($ano) || empty($mat))
        {
            $ano = date('Y');
        }
        
        $mat = getNovaMatriculaBySiape($mat);

        // EXECUTA A QUERY
        $oDBase = new DataBase();
        $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E200045.".__LINE__.").");

        $oDBase->query( "
            SELECT 
                ciclos.id,
		DATE_FORMAT(ciclos.data_inicio,'%d/%m/%Y') AS data_inicio,
		DATE_FORMAT(ciclos.data_fim,'%d/%m/%Y')    AS data_fim
            FROM
                ciclos
            LEFT JOIN
                autorizacoes_servidores ON ciclos.id = autorizacoes_servidores.ciclo_id AND YEAR(autorizacoes_servidores.data_inicio) = :ano
            WHERE
		autorizacoes_servidores.siape = :siape
		AND YEAR(ciclos.data_inicio) = :ano
        ",
        array(
            array( ":siape", $mat, PDO::PARAM_STR),
            array( ":ano",   $ano, PDO::PARAM_STR)
        ));

        return $oDBase->fetch_assoc();
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
        if (is_null($mat) || (is_string($mat) && empty($mat)))
        {
            $mat = $_SESSION['sMatricula'];
        }
        
        $mat = getNovaMatriculaBySiape($mat);

        // EXECUTA A QUERY
        $oDBase = new DataBase();
        $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E200035.".__LINE__.").");
        
        $oDBase->query( "
            SELECT
                ciclos.id , ciclos.data_inicio , ciclos.data_fim
            FROM ciclos
            JOIN autorizacoes_servidores ON ciclos.id = autorizacoes_servidores.ciclo_id
            WHERE
                autorizacoes_servidores.siape = :siape
                AND CURDATE() BETWEEN autorizacoes_servidores.data_inicio
                                  AND autorizacoes_servidores.data_fim
        ",
        array(
            array(":siape", $mat, PDO::PARAM_STR)
        ));

        return $oDBase->fetch_assoc();
    }


    /**
     * @info Carrega dados do ciclo por matrícula, com autorizações
     *       para usofruto pelo servidor, dentro do ciclo
     *
     * @param string $mat Matrícula do servidor
     * @return result
     */
    public function getCicloBySiapeUsufruto($mat=null, $dia=null)
    {
        if (is_null($mat) || (is_string($mat) && empty($mat)))
        {
            $mat = $_SESSION['sMatricula'];
        }

        $mat = getNovaMatriculaBySiape($mat);

        if (is_null($dia) || (is_string($dia) && empty($dia)))
        {
            $dia = date('Y-m-d');
        }

        // VALIDA SE AS DATAS DA autorização ESTÃO DENTRO DO CICLO
        // EXECUTA A QUERY
        $oDBase = new DataBase();
        $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E200036.".__LINE__.").");

        $oDBase->query( "
        SELECT
            ciclos.id , ciclos.data_inicio , ciclos.data_fim
        FROM 
            ciclos
        JOIN 
            autorizacoes_servidores_usufruto ON ciclos.id = autorizacoes_servidores_usufruto.ciclo_id
        WHERE
            autorizacoes_servidores_usufruto.siape = :siape
            AND (:dia BETWEEN autorizacoes_servidores_usufruto.data_inicio
                          AND autorizacoes_servidores_usufruto.data_fim)
        ",
        array(
            array( ":siape", $mat,            PDO::PARAM_STR),
            array( ":dia",   conv_data($dia), PDO::PARAM_STR)
        ));

        return $oDBase->fetch_assoc();
    }

} // END class TabBancoDeHorasCiclosModel
