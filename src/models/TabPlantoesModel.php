<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabPlantoesModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : plantoes
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - inc/models
 * @Arquivo   - TabPlantoesModel.php
 *
 * @author Edinalvo Rosa
 */
class TabPlantoesModel
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


    public function registrosPlantoesServidoresPorId( $id )
    {
        $query = "
        SELECT id, id_plantao, siape, compensar, fora_do_horario, ativo,
               registro_siape, registro_data
                    FROM plantoes_servidores
                        WHERE id_plantao = :id
                              AND ativo = 'S'
        ";

        $param = array(
            array( ':id', $id, PDO::PARAM_INT),
        );

        $this->conexao->query( $query, $param );

        return $this->conexao;
    }


    /*
     * @info Registros Plantoes
     *
     * @param void
     * @return object
     */
    public function registrosPlantoes()
    {
        $query = "
        SELECT plantoes.id, plantoes.id_escala, plantoes.descricao,
               plantoes.hora_inicial, plantoes.hora_final, plantoes.uorg,
               plantoes.data_criacao, plantoes.data_encerramento,
               plantoes.ativo, escalas.descricao AS escala_descricao,
               CONCAT(escalas.trabalhar,' x ',escalas.folgar) AS escala_sigla
            FROM plantoes
                LEFT JOIN escalas ON plantoes.id_escala = escalas.id
                    WHERE plantoes.uorg = :uorg
                          AND plantoes.ativo = 'S'
        ";

        $param = array(
            array( ':uorg', $_SESSION['sLotacao'], PDO::PARAM_STR),
        );

        $this->conexao->query( $query, $param );

        return $this->conexao;
    }


    /*
     * @info Registros Plantoes
     *
     * @param void
     * @return object
     */
    public function registrosPlantoesPorID($id)
    {
        $query = "
        SELECT id, id_escala, descricao, hora_inicial, hora_final, uorg,
               data_criacao, data_encerramento, ativo
            FROM plantoes
                WHERE id = :id
                      AND ativo = 'S'
                        ORDER BY id_escala
        ";

        $param = array(
            array( ':id', $id, PDO::PARAM_INT),
        );

        $this->conexao->query( $query, $param );

        return $this->conexao;
    }


    /*
     * @info Registros Plantoes por escala
     *
     * @param integer
     * @return object
     */
    public function registrosPlantoesPorEscala($id)
    {
        $query = "
        SELECT descricao
            FROM plantoes
                WHERE id_escala = :id
                      AND ativo = 'S'
        ";

        $param = array(
            array( ':id', $id, PDO::PARAM_INT),
        );

        $this->conexao->query( $query, $param );

        return $this->conexao;
    }


    /*
     * @info Registros Plantoes por escala, horas trabalhar,
     *       horas folgar e uorg
     *
     * @param void
     * @return object
     */
    public function registrosPlantoesPorGrupo($escala, $hora_inicial, $hora_final)
    {
        $hora_inicial = strpadHora( $hora_inicial );
        $hora_final   = strpadHora( $hora_final );

        $query = "
        SELECT id, id_escala, descricao, hora_inicial, hora_final, uorg,
               data_criacao, data_encerramento, ativo, registro_siape,
               registro_data
            FROM plantoes
                WHERE id_escala = :id_escala
                      AND hora_inicial = :hora_inicial
                      AND hora_final = :hora_final
                      AND uorg = :uorg
                      AND ativo = 'S'
                    ORDER BY id
        ";

        $param = array(
            array( ':id_escala',    $escala,       PDO::PARAM_INT),
            array( ':hora_inicial', $hora_inicial, PDO::PARAM_STR),
            array( ':hora_final',   $hora_final,   PDO::PARAM_STR),
            array( ':uorg', $_SESSION['sLotacao'], PDO::PARAM_STR),
        );

        $this->conexao->query( $query, $param );

        return $this->conexao;
    }

    /**
     * @info Inclui novo registro de Plantoes
     *
     * @param array $var
     * @return boolean TRUE sucesso
     */
    public function insert($var)
    {
        $retorno = '';

        $hora_inicial = strpadHora( $var['hora_inicial'] );
        $hora_final   = strpadHora( $var['hora_final'] );

        // verifica se já existe item igual
        $dados = $this->registrosPlantoesPorGrupo($var['id_escala'], $hora_inicial, $hora_final);

        if ($dados->num_rows() > 0)
        {
            $retorno = 'ja_existe';
        }
        else
        {
            $this->conexao->query("
            INSERT INTO plantoes
                (id_escala, descricao, hora_inicial, hora_final, uorg, ativo,
                data_criacao, data_encerramento, registro_siape, registro_data)
                    VALUES (:id_escala, :descricao, :hora_inicial, :hora_final,
                            :uorg, :ativo, NOW(), '0000-00-00 00:00:00',
                            :registro_siape, NOW())
            ",
            array(
                array(":id_escala",      $var['id_escala'],       PDO::PARAM_INT),
                array(":descricao",      $var['descricao'],       PDO::PARAM_STR),
                array(":hora_inicial",   $hora_inicial,           PDO::PARAM_STR),
                array(":hora_final",     $hora_final,             PDO::PARAM_STR),
                array(":uorg",           $_SESSION['sLotacao'],   PDO::PARAM_STR),
                array(":ativo",          $var['ativo'],           PDO::PARAM_STR),
                array(":registro_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
            ));

            $affected_rows = ($this->conexao->affected_rows() > 0);

            if ($affected_rows)
            {
                //$this->historico(0,'I'); // Inclusão realizada
                registraLog("Item de Plantão (".$var['descricao'].") registrado com sucesso!");
                $retorno = 'gravou';
            }
            else
            {
                registraLog("Item de Plantão (".$var['descricao'].") NÃO foi registrado!");
                $retorno = 'nao_gravou';
            }
        }
        return $retorno;
    }

    /**
     * @info Atualiza registro de Plantão
     *
     * @param array $var
     * @return string
     */
    public function update($var)
    {
        $retorno = '';

        $hora_inicial = strpadHora( $var['hora_inicial'] );
        $hora_final   = strpadHora( $var['hora_final'] );

        $hora_inicial_antes = strpadHora( $var['hora_inicial_antes'] );
        $hora_final_antes   = strpadHora( $var['hora_final_antes'] );

        // verifica se já existe item igual
        $dados2 = $this->registrosPlantoesPorGrupo(
            $var['id_escala'], $hora_inicial, $hora_final
        );

        if ($dados2->num_rows() > 0 &&
            ($hora_inicial != $hora_inicial_antes || $hora_final != $hora_final_antes))
        {
            return 'ja_existe';
        }


        // verifica se existe o item
        $dados = $this->registrosPlantoesPorID($var['id']);

        if ($dados->num_rows() == 0)
        {
            return 'nao_existe';
        }

        $this->conexao->query("
        UPDATE plantoes
            SET id_escala      = :id_escala,
                descricao      = :descricao,
                hora_inicial   = :hora_inicial,
                hora_final     = :hora_final,
                ativo          = :ativo,
                registro_siape = :registro_siape,
                registro_data  = NOW()
                    WHERE id = :id
        ",
        array(
            array(":id",           $var['id'],        PDO::PARAM_INT),
            array(":id_escala",    $var['id_escala'], PDO::PARAM_INT),
            array(":descricao",    $var['descricao'], PDO::PARAM_STR),
            array(":hora_inicial", $hora_inicial,     PDO::PARAM_STR),
            array(":hora_final",   $hora_final,       PDO::PARAM_STR),
            array(":ativo",        $var['ativo'],     PDO::PARAM_STR),
            array(":registro_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
        ));

        $affected_rows = ($this->conexao->affected_rows() > 0);

        if ($affected_rows)
        {
            registraLog("Alterado com sucesso (".$var['descricao'].")!");
            $retorno = 'gravou';
        }
        else
        {
            registraLog("Alteração NÃO foi realizada (".$var['descricao'].")!");
            $retorno = 'nao_gravou';
        }

        return $retorno;
    }


    /**
     * @info Desabilita o registro, ativo = 'N'
     *
     * @param type $id
     * @return boolean TRUE sucesso
     */
    public function delete( $id, $num_rows )
    {
        $resultado = "error";

        if ($num_rows > 0)
        {
            $resultado = "warning";
        }
        else
        {
            $query = "
            UPDATE plantoes
                SET ativo             = :ativo,
                    registro_siape    = :registro_siape,
                    registro_data     = NOW(),
                    data_encerramento = NOW()
                        WHERE id = :id
            ";

            $params = array(
                array(":id",        $id, PDO::PARAM_INT),
                array(":ativo",     'N', PDO::PARAM_STR),
                array(":registro_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
            );

            $this->conexao->query( $query, $params );

            $affected_rows = ($this->conexao->affected_rows() > 0);

            if ($affected_rows)
            {
                $resultado = "success";
                registraLog("Deletou o registro ".$id);
                $this->historico($id,'E'); // Alteração realizada
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
        $this->conexao->query("
        INSERT INTO plantoes_historico
            SELECT 0, id, id_escala, descricao, hora_inicial, hora_final, uorg,
                   data_criacao, data_encerramento, ativo, registro_siape,
                   registro_data, :oper, :operador_siape, NOW()
                        FROM plantoes
                            WHERE plantoes.id = :id
        ",
        array(
            array(":id",   $id,   PDO::PARAM_INT),
            array(":oper", $oper, PDO::PARAM_STR),
            array(":operador_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
        ));
    }


    /**
     * @info Carrega os dados de Escalas
     *
     * @param void
     * @return string HTML
     */
    public function listaEscalas($oDBase)
    {
        $array = array();

        while ($rows = $oDBase->fetch_object())
        {
            /*
            $descricao = $rows->trabalhar
                         . 'x' . $rows->folgar
                         . ' - ' . $rows->descricao
                         . ' (' . $rows->jornada . ')';
             */

            $descricao = $rows->descricao . ' (' . $rows->jornada . ')';

            $array[] = array(
                'id'        => $rows->id,
                'descricao' => $descricao,
                'trabalhar' => $rows->trabalhar,
                'folgar'    => $rows->folgar,
            );
        }

        return $array;
    }

} // END class TabPlantoesModel
