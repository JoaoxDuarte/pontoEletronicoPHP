<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

/**
 * @class PesquisaChaveEscolhaModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : servativ
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - inc/models
 * @Arquivo   - PesquisaChaveEscolhaModel.php
 *
 * @author Edinalvo Rosa
 */
class PesquisaChaveEscolhaModel
{
  /*
   * Atributos
   */
  /* @var OBJECT */
  public $conexao = NULL;
  
  private $chave;
  private $escolha;
  private $primeira_vez;
  private $var1;
  private $var2;
  private $id;
  private $delete;
  private $nome;

  /**
   * @method Construtor
   */
  function __construct()
  {
    # Faz conexão
    $this->conexao = new DataBase();
    $this->argumentosGet();
  }

  /**
   * @info Carrega contéudo de "chave"
   *
   * @param void
   * @return string
   */
  public function getChave()
  {
    return $this->chave;
  }

  /**
   * @info Carrega contéudo de "escolha"
   *
   * @param void
   * @return string
   */
  public function getEscolha()
  {
    return $this->escolha;
  }

  /**
   * @info Carrega contéudo de "var1"
   *
   * @param void
   * @return string
   */
  public function getChaveVar1()
  {
    return $this->var1;
  }

  /**
   * @info Carrega contéudo de "var2"
   *
   * @param void
   * @return string
   */
  public function getEscolhaVar2()
  {
    return $this->var2;
  }

  /**
   * @info Carrega acesso "primeira vez"
   *
   * @param void
   * @return boolean
   */
  public function getPrimeiraVez()
  {
    return $this->primeira_vez;
  }

  /**
   * @info Carrega dados passados por
   *       $_GET/$_POST/$_REQUEST
   *
   * @param void
   * @return void
   */
  public function argumentosGet()
  {
    $this->primeira_vez = (!isset($_REQUEST['primeira_vez']) );
    $this->chave        = anti_injection($_REQUEST["chave"]);
    $this->escolha      = anti_injection(str_replace('"', '', $_REQUEST["escolha"]));
    $this->delete       = "";
    $this->id           = "";
    $this->nome         = "";
  }

  /**
   * @info Utilizada para carregar o SQL gerado para 
   *       impressao sua alimentação será via ajax 
   *       (jquery.js), a chamada encontra-se no sorttable.js
   * 
   * @param void
   */
  public function dadosParaPesquisa()
  {
    if (($this->primeira_vez == false) && ($this->chave == "") && ($this->escolha != 'todos'))
    {
      $this->var1 = $_SESSION['sChaveCriterioExtra']["chave"];
      $this->var2 = $_SESSION['sChaveCriterioExtra']["escolha"];
    }
    else
    {
      $_SESSION['sSQLPesquisaExtra']   = "";
      $_SESSION['sChaveCriterioExtra'] = "";

      $this->var1 = $this->chave;
      $this->var2 = $this->escolha;
    }
  }


  /*
   * @info Seleciona registros com base no campo e dados informados
   *
   * @param string $var1    Campo de pesquisar
   * @param string $var2    Valor a pesquisar
   * @param string $groupby Campo a agrupar
   * @return  object  Resultado da pesquisa
   *
   * @author Edinalvo Rosa
   */
  function pesquisaChaveEscolha($var1 = '', $var2 = '', $groupby = "siape")
  {
    if ($groupby !== "siape")
    {
      $_SESSION['sChaveCriterioExtra'] = array("chave" => $var1, "escolha" => $var2);
    }

    $query = "
    SELECT
      servativ.mat_siape       AS matricula,
      servativ.mat_siape       AS siape,
      servativ.nome_serv       AS nome,
      servativ.cod_lot         AS setor,
      servativ.horae           AS horae,
      servativ.motivo          AS motivo,
      servativ.limite_horas    AS limite_horas,
      tabcargo.PERMITE_BANCO   AS permite_banco,
      tabsetor.periodo_excecao AS excecao
    FROM
      servativ
    LEFT JOIN
      tabsetor ON servativ.cod_lot = tabsetor.codigo
    LEFT JOIN
      tabcargo ON servativ.cod_cargo = tabcargo.cod_cargo
    WHERE
      servativ.cod_sitcad NOT IN ('02','08','15','66')
      AND servativ.excluido = 'N'
      AND servativ.mat_siape <> :usuario
    ";

    $params   = array();
    $params[] = array(":usuario", $_SESSION['sMatricula'], PDO::PARAM_STR);

    if ($_SESSION["sLog"] != "S")
    {
      $query   .= " AND tabsetor.upag = :upag ";
      $params[] = array(":upag", $_SESSION['upag'], PDO::PARAM_STR);
    }

    if ($_SESSION["sRH"] != "S" && $_SESSION["sAPS"] == "S")
    {
      $query   .= " AND servativ.cod_lot = :lotacao ";
      $params[] = array(":lotacao", $_SESSION['sLotacao'], PDO::PARAM_STR);
    }

    switch ($var2)
    {
      case "siape":
        $novamatricula = getNovaMatriculaBySiape($var1);
        $query        .= " AND servativ.mat_siape = :siape ";
        $params[]      = array(":siape", $novamatricula, PDO::PARAM_STR);
        break;

      case "nome":
        $query   .= " AND servativ.nome_serv LIKE :nome ";
        $params[] = array(":nome", "%$var1%", PDO::PARAM_STR);
        break;

      case "cargo":
        $query   .= "
                  AND (servativ.cod_cargo = :cargo
                  OR tabcargo.desc_cargo LIKE :descricao)
              ";
        $params[] = array(":cargo", $var1, PDO::PARAM_STR);
        $params[] = array(":descricao", "%$var1%", PDO::PARAM_STR);
        break;

      case "lotacao":
        // uso de ":unidade" para não conflitar com ":lotacao" usada acima
        $query   .= " AND servativ.cod_lot LIKE :unidade ";
        $params[] = array(":unidade", "%$var1%", PDO::PARAM_STR);
        break;
    }

    $_SESSION['sSQLPesquisa'] = $query;

    if ($groupby == "siape")
    {
      $query .= "GROUP BY servativ.mat_siape ";
    }

    if ($var2 == "lotacao")
    {
      $query .= "ORDER BY servativ.cod_lot ";
    }
    else
    {
      $query .= "ORDER BY servativ.nome_serv ";
    }

    $this->conexao->query($query, $params);

    return $this->conexao;
  }
}
