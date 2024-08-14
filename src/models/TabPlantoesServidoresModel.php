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
class TabPlantoesServidoresModel
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
               CONCAT(escalas.trabalhar,'x',escalas.folgar) AS escala_sigla
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


    public function gravar()
    {
      $id              = $_POST['id'];
      $id_plantao      = $_POST['id_plantao'];
      $fora_do_horario = $_POST['fora_do_horario'];
      $compensar       = $_POST['compensar'];

      $retorno = array(
        'retorno'  => '',
        'mensagem' => '',
      );

      foreach ($id as $key => $value)
      {
        if (empty($value) && !empty($id_plantao[$key]))
        {
          $retorno = $this->insert( $key, $id_plantao[$key], $fora_do_horario[$key], $compensar[$key] );
        }
        else if ( !empty($value) )
        {
          $retorno = $this->update( $value, $key, $id_plantao[$key], $fora_do_horario[$key], $compensar[$key] );
        }
      }

      return $retorno;
    }

    /**
     * @info Inclui novo registro de Plantonistas
     *
     * @param string $siape           Matrícula siape do servidor
     * @param string $id_plantao      ID do plantão
     * @param string $fora_do_horario Indica se pode registrar antes ou depois
     *                                do horário de funcionamento da unidades
     * @param string $compensar       Indica se pode compensar horas devidas
     * @return boolean TRUE sucesso
     */
    public function insert($siape, $id_plantao, $fora_do_horario, $compensar)
    {
      $retorno = array(
        'retorno'  => 'gravou',
        'mensagem' => "Servidor plantonista registrado com sucesso!",
      );

      $siape = strtr($siape, array("'" => "", '"' => ''));

      // id_plantao         int(10)
      // siape              varchar(12)
      // compensar          char(1)
      // fora_do_horario    char(1)
      // uorg               varchar(14)
      // ativo              char(1)
      // data_criacao       datetime
      // data_encerramento  datetime
      // registro_siape     varchar(12)
      // registro_data      datetime

      $this->conexao->query("
      INSERT INTO plantoes_servidores
        (id, id_plantao, siape, compensar, fora_do_horario, uorg,
         data_criacao, data_encerramento, ativo, registro_siape, registro_data)
      VALUES (0, :id_plantao, :siape, :compensar, :fora_do_horario, :uorg,
              NOW(), '0000-00-00 00:00:00', :ativo, :registro_siape, NOW())
      ",
      array(
        array(":id_plantao",      $id_plantao,            PDO::PARAM_INT),
        array(":siape",           $siape,                 PDO::PARAM_STR),
        array(":compensar",       $compensar,             PDO::PARAM_STR),
        array(":fora_do_horario", $fora_do_horario,       PDO::PARAM_STR),
        array(":uorg",            $_SESSION['sLotacao'],  PDO::PARAM_STR),
        array(":ativo",           "S",                    PDO::PARAM_STR),
        array(":registro_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
      ));

      $affected_rows = ($this->conexao->affected_rows() > 0);

      if ($affected_rows)
      {
        //$this->historico(0,'I'); // Inclusão realizada
        registraLog("Plantonista (".$siape.") registrado com sucesso!");
        $retorno = array(
          'retorno'  => 'gravou',
          'mensagem' => "Plantonista registrado com sucesso!",
        );
      }
      else
      {
        registraLog("Servidor Plantonista (".$siape.") NÃO foi registrado!");
        $retorno = array(
          'retorno'  => 'nao gravou',
          'mensagem' => "Plantonista NÃO foi registrado!",
        );
      }

      return $retorno;
    }


    /**
     * @info Inclui novo registro de Plantonistas
     *
     * @param integer $id              ID do registro do plantonista
     * @param string  $siape           Matrícula siape do servidor
     * @param string  $id_plantao      ID do plantão
     * @param string  $fora_do_horario Indica se pode registrar antes ou depois
     *                                do horário de funcionamento da unidades
     * @param string $compensar       Indica se pode compensar horas devidas
     * @return boolean TRUE sucesso
     */
    public function update($id, $siape, $id_plantao, $fora_do_horario, $compensar)
    {
      $retorno = '';
      $ativo   = ($id_plantao == 0 ? 'N' : 'S');

      $this->insertHistorico($id, $siape, 'A', $ativo);

      $this->conexao->query("
      UPDATE plantoes_servidores
        SET
          id_plantao        = :id_plantao,
          compensar         = :compensar,
          fora_do_horario   = :fora_do_horario,
          ativo             = :ativo,
          data_encerramento = IF(:ativo = 'N', NOW(), '0000-00-00 00:00:00'),
          registro_siape    = :registro_siape,
          registro_data     = NOW()
            WHERE id = :id
      ",
      array(
        array(":id",              $id,                     PDO::PARAM_INT),
        array(":id_plantao",      $id_plantao,             PDO::PARAM_INT),
        array(":compensar",       $compensar,              PDO::PARAM_STR),
        array(":fora_do_horario", $fora_do_horario,        PDO::PARAM_STR),
        array(":ativo",           $ativo,                  PDO::PARAM_STR),
        array(":registro_siape",  $_SESSION['sMatricula'], PDO::PARAM_STR),
      ));

      $affected_rows = ($this->conexao->affected_rows() > 0);

      if ($affected_rows)
      {
        registraLog("Alterado com sucesso (".$siape.")!");
        $retorno = array(
          'retorno'  => 'gravou',
          'mensagem' => "Alterado com sucesso!",
        );
      }
      else
      {
        registraLog("Alteração NÃO foi realizada (".$siape.")!");
        $retorno = array(
          'retorno'  => 'nao_gravou',
          'mensagem' => "Alteração NÃO foi realizada!",
        );
      }

      return $retorno;
    }

    /**
     * @info Inclui novo registro no histórico de Plantonistas
     *
     * @param string $id    ID do plantonista
     * @param string $siape Matrícula siape do servidor
     * @param string $oper  Indica se pode registrar antes ou depois
     *                      do horário de funcionamento da unidades
     * @param string $ativo
     * @return string Indica situação da operação
     */
    public function insertHistorico($id, $siape, $oper, $ativo)
    {
      $siape = strtr($siape, array("'" => "", '"' => ''));

      $this->conexao->query("
      INSERT INTO plantoes_servidores_historico
        SELECT
          0,
          plantoes_servidores.id,
          plantoes_servidores.id_plantao,
          plantoes_servidores.siape,
          plantoes_servidores.compensar,
          plantoes_servidores.fora_do_horario,
          plantoes_servidores.uorg,
          plantoes_servidores.data_criacao,
          IF(plantoes_servidores.ativo = :ativo,
            plantoes_servidores.data_encerramento, NOW()) AS data_encerramento,
          plantoes_servidores.ativo,
          plantoes_servidores.registro_siape,
          plantoes_servidores.registro_data,
          :oper,
          :siape,
          NOW()
        FROM
          plantoes_servidores
        WHERE
          plantoes_servidores.id = :id
      ",
      array(
        array(":id",    $id,    PDO::PARAM_INT),
        array(":siape", $siape, PDO::PARAM_STR),
        array(":oper",  $oper,  PDO::PARAM_STR),
        array(":ativo", $ativo, PDO::PARAM_STR),
      ));

      $affected_rows = ($this->conexao->affected_rows() > 0);

      if ($affected_rows)
      {
        //$this->historico(0,'I'); // Inclusão realizada
        registraLog("Servidor histórico Plantonista (".$siape.") registrado com sucesso!");
        $retorno = array(
          'retorno'  => 'gravou',
          'mensagem' => "Servidor histórico Plantonista registrado com sucesso!",
        );
      }
      else
      {
        registraLog("Servidor histórico Plantonista (".$siape.") NÃO foi registrado!");
        $retorno = array(
          'retorno'  => 'nao_gravou',
          'mensagem' => "Servidor histórico Plantonista NÃO foi registrado com sucesso!",
        );
      }

      return $retorno;
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
     * @info Registros de servidordes Plantoes
     *
     * @param void
     * @return object
     */
    public function registrosPlantoesServidores($id=null)
    {
        $query = "
        SELECT servativ.mat_siape AS siape,
            servativ.nome_serv AS nome,
            servativ.jornada,
            IFNULL(plantoes_servidores.id,'') AS id,
            IFNULL(plantoes_servidores.id_plantao,'') AS id_plantao,
            IFNULL(plantoes_servidores.compensar,'') AS compensar,
            IFNULL(plantoes_servidores.fora_do_horario,'') AS fora_do_horario,
            IFNULL(CONCAT(escalas.trabalhar,' x ',escalas.folgar),'') AS escalas,
            IFNULL(escalas.jornada,'') AS escalas_jornada
        FROM 
            servativ
        LEFT JOIN 
            plantoes_servidores ON servativ.mat_siape = plantoes_servidores.siape
                                   AND plantoes_servidores.ativo = 'S'
                                   AND plantoes_servidores.uorg = servativ.cod_lot
        LEFT JOIN 
            plantoes ON plantoes_servidores.id_plantao = plantoes.id
                        AND plantoes.ativo = 'S'
        LEFT JOIN 
            escalas ON plantoes.id_escala = escalas.id
                       AND escalas.ativo = 'S'
        WHERE 
            servativ.cod_lot = :uorg
        ";

        if (is_null($id))
        {
            $param = array(
                array( ':uorg', $_SESSION['sLotacao'], PDO::PARAM_STR),
            );
        }
        else
        {
            $query .= "
            AND plantoes_servidores.id = :id
            ";
            $param = array(
                array( ':id',   $id, PDO::PARAM_INT),
                array( ':uorg', $_SESSION['sLotacao'], PDO::PARAM_STR),
            );
        }

        $query .= "
            ORDER BY servativ.nome_serv
        ";
        
        $this->conexao->query( $query, $param );

        return $this->conexao;
    }


    /**
     * @info Carrega os dados de plantões
     *
     * @param void
     * @return string HTML
     */
    public function listaPlantoes($oDBase)
    {
        $array = array();
        $array[] = array(
          'id'           => 0,
          'descricao'    => "Sem Plantão",
          'escala_sigla' => "",
        );

        while ($rows = $oDBase->fetch_object())
        {
            $array[] = array(
                'id'           => $rows->id,
                'descricao'    => $rows->descricao,
                'escala_sigla' => $rows->escala_sigla,
            );
        }

        return $array;
    }

} // END class TabPlantoesServidoresModel
