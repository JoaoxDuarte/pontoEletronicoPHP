<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once("class_definir.jornada.php");
include_once("class_ocorrencias_grupos.php");
//include_once("hora_extra_autorizacao_funcoes.php");


/**
 * @class HoraExtraModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : plantoes
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - inc/models
 * @Arquivo   - HoraExtraModel.php
 *
 * @author Edinalvo Rosa
 */
class HoraExtraModel
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
   * @info Carrega dados passados por
   *       $_GET/$_POST/$_REQUEST
   *
   * @param void
   * @return void
   */
  public function argumentosGet()
  {
    $this->delete = "";
    $this->id     = "";
    $this->nome   = "";

    $dadosorigem = $_REQUEST['dados'];
    
    if (!empty($dadosorigem))
    {
      $dados = base64_decode($dadosorigem);
      parse_str($dados, $dados_get);

      $this->delete = $dados_get['delete'];
      $this->id     = $dados_get['id'];
      $this->nome   = $dados_get['nome'];

      if ($this->delete == 'sim')
      {
        $this->deleteAutorizacaoHoraExtra();
      }
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
  public function pesquisaChaveEscolha($var1 = '', $var2 = '', $groupby = "siape")
  {
    if ($groupby !== "siape")
    {
      $_SESSION['sChaveCriterioExtra'] = array("chave" => $var1, "escolha" => $var2);
    }

    $query = "
    SELECT
      IFNULL(autorizacoes_hora_extra.id,0) AS id,
      servativ.mat_siape       AS matricula,
      servativ.mat_siape       AS siape,
      servativ.nome_serv       AS nome,
      servativ.cod_lot         AS setor,
      servativ.horae           AS horae,
      servativ.motivo          AS motivo,
      servativ.limite_horas    AS limite_horas,
      tabcargo.PERMITE_BANCO   AS permite_banco,
      tabsetor.periodo_excecao AS excecao,
      autorizacoes_hora_extra.data_inicio,
      autorizacoes_hora_extra.data_fim,
      CONCAT(
        DATE_FORMAT(autorizacoes_hora_extra.data_inicio,'%d/%m/%Y'),
        ' a ',
        DATE_FORMAT(autorizacoes_hora_extra.data_fim,'%d/%m/%Y')
      ) AS periodo,
      autorizacoes_hora_extra.horas
    FROM
      servativ
    LEFT JOIN
      autorizacoes_hora_extra ON servativ.mat_siape = autorizacoes_hora_extra.siape
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

    if ($groupby !== 'siape')
    {
      $query .= "
        AND NOT ISNULL(autorizacoes_hora_extra.data_inicio)
        AND NOT ISNULL(autorizacoes_hora_extra.data_fim)
      ";
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
      $query .= "ORDER BY servativ.nome_serv, autorizacoes_hora_extra.data_inicio DESC ";
    }

    $this->conexao->query($query, $params);

    return $this->conexao;
  }

  /**
   * @info Cria uma nova autorização para o servidor ou atualiza a existente
   *
   * @param $post
   * @return bool|int|null|PDOStatement|resource
   */
  function createUpdateAutorizacaoHoraExtra($post)
  {
    $return = false;

    $post['siape'] = $this->getNovaMatriculaBySiape($post['siape']);

    // CONSULTA PARA IDENTIFICAR SE JÁ EXISTE AUTORIZAÇÃO PARA O SERVIDOR
    $query = "
    SELECT
      siape
    FROM 
      autorizacoes_hora_extra
    WHERE 
      autorizacoes_hora_extra.id = :id
    ";
    
    $params = array(
      array(":id", $post['id'], PDO::PARAM_STR),
    );

    $this->conexao->query($query, $params);

    // SE EXISTIR SERA ATUALIZADO COM OS NOVOS VALORES, CASO CONTRÁRIO SERA CRIADO UM NOVO
    if ($this->conexao->num_rows() > 0)
    {
      $numrows = $this->updateAutorizacaoHoraExtra($post);
    }
    else
    {
      $numrows = $this->insertAutorizacaoHoraExtra($post);
    }

    $return = ($numrows > 0);

    return $return;
  }

  /**
   * @info INSERT Autorização Serviço Extraordinário
   *
   * @param array $post
   */
  function insertAutorizacaoHoraExtra($post)
  {
    $query = "
    INSERT INTO autorizacoes_hora_extra
    SET
      siape                = :siape,
      setor                = :setor,
      data_inicio          = :data_inicio,
      data_fim             = :data_fim,
      horas                = :horas,
      documento            = :documento,
      acrescimo_autorizado = :acrescimo_autorizado,
      registrado_por       = :registrado_por,
      registrado_data      = NOW()
    ";

    $params = array(
      array(":siape",                $post['siape'],                  PDO::PARAM_STR),
      array(":setor",                $post['setor'],                  PDO::PARAM_STR),
      array(":data_inicio",          conv_data($post['data_inicio']), PDO::PARAM_STR),
      array(":data_fim",             conv_data($post['data_fim']),    PDO::PARAM_STR),
      array(":horas",                $post['horas'],                  PDO::PARAM_STR),
      array(":documento",            $post['documento'],              PDO::PARAM_STR),
      array(":acrescimo_autorizado", $post['acrescimo_autorizado'],   PDO::PARAM_STR),
      array(":registrado_por",       $_SESSION['sMatricula'],         PDO::PARAM_STR),
    );

    $this->conexao->query($query, $params);
    registraLog("Inclusão de autorização de serviço(s) estraordinário(s) (Horas Extras)");

    return $this->conexao->affected_rows();
  }

  /**
   * @info UPDATE Autorização Serviço Extraordinário
   *
   *
   * @param array $post
   */
  function updateAutorizacaoHoraExtra($post)
  {
    $this->gravar_historico_hora_extra($post['id'], 'A');

    $query = "
    UPDATE autorizacoes_hora_extra
    SET 
      autorizacoes_hora_extra.data_inicio          = :data_inicio,
      autorizacoes_hora_extra.data_fim             = :data_fim,
      autorizacoes_hora_extra.horas                = :horas,
      autorizacoes_hora_extra.documento            = :documento,
      autorizacoes_hora_extra.acrescimo_autorizado = :acrescimo_autorizado
      autorizacoes_hora_extra.registrado_por       = :registrado_por
    WHERE 
      id = :id
    ";

    $params = array(
      array(":id",                   $post['id'],                     PDO::PARAM_STR),
      array(":data_inicio",          conv_data($post['data_inicio']), PDO::PARAM_STR),
      array(":data_fim",             conv_data($post['data_fim']),    PDO::PARAM_STR),
      array(":horas",                $post['horas'],                  PDO::PARAM_STR),
      array(":documento",            $post['documento'],              PDO::PARAM_STR),
      array(":acrescimo_autorizado", $post['acrescimo_autorizado'],   PDO::PARAM_STR),
      array(":registrado_por",       $_SESSION['sMatricula'],         PDO::PARAM_STR),
    );

    $this->conexao->query($query, $params);
    registraLog("Alteração da autorização de serviço(s) estraordinário(s) (Horas Extras)");

    return $this->conexao->affected_rows();
  }

  /**
   * @param integer $id   Número do registro
   * @param string  $nome Nome do servidor
   * @return void
   */
  public function deleteAutorizacaoHoraExtra()
  {
    // CONSULTA PARA IDENTIFICAR SE JÁ EXISTE AUTORIZAÇÃO PARA O SERVIDOR
    $query  = "
    SELECT
      siape
    FROM 
      autorizacoes_hora_extra
    WHERE 
      autorizacoes_hora_extra.id = :id
    ";
    
    $params = array(
      array(":id", $this->id, PDO::PARAM_STR),
    );

    $this->conexao->query($query, $params);

    // SE EXISTIR SERA DELETADO
    if ($this->conexao->num_rows() > 0)
    {
      $this->gravar_historico_hora_extra($this->id, 'E');

      // apaga o registro
      $this->conexao->query("
      DELETE FROM autorizacoes_hora_extra
      WHERE 
        autorizacoes_hora_extra.id = :id
      ",
      array(
        array(":id", $this->id, PDO::PARAM_INT),
      ));

      // registra em log a ação
      registraLog("Deletado registro de período de serviço extraordinário de " . $nome);
    }
  }

  function gravar_historico_hora_extra($id, $oper = 'A')
  {
    // inclusão historico
    $this->conexao->query("
    INSERT autorizacoes_hora_extra_historico
      SELECT 
        0, 
        autorizacoes_hora_extra.siape,
        autorizacoes_hora_extra.setor,
        autorizacoes_hora_extra.data_inicio,
        autorizacoes_hora_extra.data_fim,
        autorizacoes_hora_extra.horas,
        autorizacoes_hora_extra.documento,
        autorizacoes_hora_extra.acrescimo_autorizado,
        autorizacoes_hora_extra.registrado_por,
        autorizacoes_hora_extra.registrado_data,
        autorizacoes_hora_extra.homologado_por,
        autorizacoes_hora_extra.homologado_data, :idreg, :siape, NOW()
      FROM  
        autorizacoes_hora_extra
      WHERE 
        autorizacoes_hora_extra.id = :id
      ", 
      array(
        array(":id",    $id,                     PDO::PARAM_STR),
        array(":siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
        array(":idreg", $oper,                   PDO::PARAM_STR),
      )
    );
  }

  /**
   * @param $siape
   * @param $start_date
   * @param $end_date
   * @return bool  Retorna FALSE se data já cadastrada para o servidor.
   * @info Verifica período de hora extra
   */
  function verificaSePeriodoHoraExtraJaCadastrado($siape, $start_date, $end_date)
  {
    $siape = getNovaMatriculaBySiape($siape);

    $this->conexao->query("
    SELECT
      IFNULL((SELECT
                id
              FROM
                autorizacoes_hora_extra
              WHERE
                autorizacoes_hora_extra.siape = :siape
                AND (:start_date >= autorizacoes_hora_extra.data_inicio
                AND :start_date <= autorizacoes_hora_extra.data_fim)),0) AS inicio_cadastrado,
      IFNULL((SELECT
                siape
              FROM
                autorizacoes_hora_extra
              WHERE
                autorizacoes_hora_extra.siape = :siape
                AND (:end_date >= autorizacoes_hora_extra.data_inicio
                AND :end_date <= autorizacoes_hora_extra.data_fim)),0) AS fim_cadastrado
      ",
      array(
        array(":siape",      $siape,                 PDO::PARAM_STR),
        array(":start_date", conv_data($start_date), PDO::PARAM_STR),
        array(":end_date",   conv_data($end_date),   PDO::PARAM_STR),
      )
    );

    return $this->conexao->fetch_object();
  }

  /**
   * @param $siape
   * @param $dia
   * @return boolean  TRUE há autorização hora extra
   * @info Verifica se autorizado hora extra
   */
  function verificaSeHaAutorizacaoHoraExtra($siape, $dia)
  {
    $matricula = getNovaMatriculaBySiape($siape);

    $this->conexao->query("
    SELECT
      siape
    FROM
      autorizacoes_hora_extra
    WHERE
      autorizacoes_hora_extra.siape = :siape
      AND (:dia >= autorizacoes_hora_extra.data_inicio
           AND :dia <= autorizacoes_hora_extra.data_fim)
    ",
    array(
      array(":siape", $matricula,      PDO::PARAM_STR),
      array(":dia",   conv_data($dia), PDO::PARAM_STR),
    ));

    return ($this->conexao->num_rows() > 0);
  }

  /**
   * @param void
   * @return array  Com campos como chave e seus valores limite
   * @info Carrega limites de hora extra
   */
  function configLimitesHoraExtra()
  {
    $array = array();

    $this->conexao->query("
    SELECT
      campo, minutos
    FROM 
      config_basico
    WHERE 
      grupo = 'hora_extra'
    ");

    while ($rows = $this->conexao->fetch_object())
    {
      $array[$rows->campo] = $rows->minutos;
    }

    return $array;
  }

  /**
   * @param  float
   * @return  float  Horas extras acumuladas no exercício
   * @info Carrega limites de hora extra
   */
  public function acumuladoHoraExtraNoAno($siape, $ano = NULL, $retorna = 'sec')
  {
    $siape = getNovaMatriculaBySiape($siape);

    $ano = (is_null($ano) ? date('Y') : $ano);

    $horas_acumuladas = 0;

    $query = "
    SELECT
      SUM(TIME_TO_SEC(horas)) AS segundos,
      IF(LENGTH(SEC_TO_TIME(SUM(TIME_TO_SEC(horas)))) > 8,
        SUBSTR(SEC_TO_TIME(SUM(TIME_TO_SEC(horas))),1,6),
        SUBSTR(SEC_TO_TIME(SUM(TIME_TO_SEC(horas))),1,5)) AS hhmm
    FROM
      autorizacoes_hora_extra
    WHERE
      siape = :siape
      AND YEAR(data_inicio) = :ano
    ";

    $params = array(
      array(':siape', $siape, PDO::PARAM_STR),
      array(':ano',   $ano,   PDO::PARAM_STR),
    );

    $this->conexao->query($query, $params);

    $dados = $this->conexao->fetch_object();

    if ($retorna == 'sec')
    {
      $horas_acumuladas = $dados->segundos;
    }
    else
    {
      $horas_acumuladas = $dados->hhmm;
    }

    return $horas_acumuladas;
  }

  function verificaHoraExtraNoPeriodo($siape, $start_date, $end_date, $horas, $acrescimo)
  {
    $siape = getNovaMatriculaBySiape($siape);

    $mensagem = "";

    $limites              = configLimitesHoraExtra();
    $maximo_dia           = time_to_sec($limites['limite_diario_hora_extra']);
    $maximo_mes           = time_to_sec($limites['limite_mensal_hora_extra']);
    $maximo_ano           = time_to_sec($limites['limite_anual_hora_extra']);
    $maximo_ano_acrescimo = time_to_sec($limites['limite_anual_acrescimo_hora_extra']);

    $maximo_ano_com_acrescimo = ($maximo_ano + $maximo_ano_acrescimo);

    $dias_no_periodo  = (dif_data(conv_data($start_date), conv_data($end_date)) + 1);
    $meses_no_periodo = ((dataMes($end_date) - dataMes($start_date)) + 1);

    // divide horas informadas por dias no período, para
    // verificar se excedem as 2 horas permitidas por dia
    $horas_por_dias = ($horas / $dias_no_periodo);

    $horas_acumuladas = $this->acumuladoHoraExtraNoAno($siape, dataAno($start_date), $retorna          = 'sec');

    $horas_totais = $horas + $horas_acumuladas;

    // testa limites horas no ano
    if (($horas > $maximo_ano_com_acrescimo) || ($horas_totais > $maximo_ano_com_acrescimo))
    {
      $mensagem .= (empty($mensagem) ? "" : "<br>")
        . "Horas informadas/acumuladas acima do limite máximo de "
        . sec_to_time($maximo_ano_com_acrescimo, 'hh:mm')
        . ' (' . sec_to_time($maximo_ano, 'hh:mm')
        . ' + '
        . sec_to_time($maximo_ano_acrescimo, 'hh:mm')
        . ')';
    }
    else if (($horas > $maximo_ano || $horas_totais > $maximo_ano) && $acrescimo == 'N')
    {
      $mensagem .= (empty($mensagem) ? "" : "<br>")
        . "Horas informadas/acumuladas acima do limite máximo anual de "
        . sec_to_time($maximo_ano, 'hh:mm');
    }
    else if ($dias_no_periodo == 1 && $horas > $maximo_dia)
    {
      $mensagem .= (empty($mensagem) ? "" : "<br>")
        . "Horas informadas/acumuladas acima do limite diário ("
        . sec_to_time($maximo_dia, 'hh:mm')
        . ')';
    }
    else if ($meses_no_periodo == 1 && $horas > $maximo_mes)
    {
      $mensagem .= (empty($mensagem) ? "" : "<br>")
        . "Horas informadas/acumuladas acima do limite mensal ("
        . sec_to_time($maximo_mes, 'hh:mm') . ')';
    }
    else if ($horas_por_dias > $maximo_dia)
    {
      $mensagem .= (empty($mensagem) ? "" : "<br>")
        . "Horas informadas, para o período, excedem o limite diário ("
        . sec_to_time($maximo_dia, 'hh:mm')
        . ')';
    }

    return $mensagem;
  }

  public function verificaSePodeEditarPeriodo($siape, $start_date, $end_date, $horas = 0)
  {
    $horas_realizadas = $this->verificaHorasDestinadasParaHoraExtra($siape, $start_date, $end_date);

    // informação de retorno
    $info = ['blocked' => false, 'titulo' => 'Permitido'];

    /*
      if ((inverteData($start_date) <= date('Ymd')))
      {
      $mensagem = 'Data Inicial não pode ser alterada!';
      $info = ['blocked' => true, 'titulo' => $mensagem];
      }

      // Verifica se já passou a data de início ou fim do período
      if ((inverteData($end_date) <= date('Ymd')))
      {
      $mensagem = 'Data Final não pode ser alterada!';
      $info = ['blocked' => true, 'titulo' => $mensagem];
      }

      // Verifica se já passou a data de início ou fim do período
      if ((inverteData($start_date) <= date('Ymd')) && (inverteData($end_date) <= date('Ymd')))
      {
      $mensagem = 'Período não pode ser alterado!';
      $info = ['blocked' => true, 'titulo' => $mensagem];
      }
     */

    // Verifica se há registro de hora extra no ponto
    //if ((inverteData($start_date) <= date('Ymd')) && (inverteData($end_date) <= date('Ymd')))
    if ($horas_realizadas >= $horas)
    {
      $mensagem = 'Período não pode ser alterado!';
      $info     = ['blocked' => true, 'titulo' => $mensagem];
    }

    return $info;
  }

  public function verificaHorasDestinadasParaHoraExtra($siape, $start_date, $end_date)
  {
    $horas = 0;

    $obj = new OcorrenciasGrupos();
    $oco = $obj->CodigoHoraExtraPadrao()[0];

    $params = array(
      array(":siape",       $siape,                 PDO::PARAM_STR),
      array(":data_inicio", conv_data($start_date), PDO::PARAM_STR),
      array(":data_fim",    conv_data($end_date),   PDO::PARAM_STR),
      array(":oco",         $oco,                   PDO::PARAM_STR),
    );

    $ano     = dataAno($start_date);
    $mes_ini = dataMes($start_date);
    $mes_fim = dataMes($end_date);

    for ($mes = $mes_ini; $mes <= $mes_fim; $mes++)
    {
      $siape = getNovaMatriculaBySiape($siape);

      $query = "
      SELECT
        IF(ISNULL(SEC_TO_TIME(SUM(TIME_TO_SEC(jorndif)))),
          0,
          SEC_TO_TIME(SUM(TIME_TO_SEC(jorndif)))) AS horas
      FROM 
        ponto" . str_pad($mes, 2, "0", STR_PAD_LEFT) . $ano . "
      WHERE 
        siape = :siape
        AND (dia >= :data_inicio AND dia <= :data_fim)
        AND oco = :oco ";

      $this->conexao->query($query, $params);

      $horas += $this->conexao->fetch_object()->horas;
    }

    return $horas;
  }

  public function CarregaRegistrosHoraExtra($id)
  {
    $query = "
    SELECT
      autorizacoes_hora_extra.siape,
      servativ.nome_serv AS nome,
      IF(autorizacoes_hora_extra.setor = '',
        servativ.cod_lot,
        autorizacoes_hora_extra.setor) AS setor,
      autorizacoes_hora_extra.data_inicio,
      autorizacoes_hora_extra.data_fim,
      autorizacoes_hora_extra.horas,
      autorizacoes_hora_extra.documento,
      autorizacoes_hora_extra.acrescimo_autorizado
    FROM 
      autorizacoes_hora_extra
    LEFT JOIN 
      servativ ON autorizacoes_hora_extra.siape = servativ.mat_siape
    WHERE 
      autorizacoes_hora_extra.id = :id
    ";
    
    $params = array(
      array(":id", $id, PDO::PARAM_STR),
    );

    $this->conexao->query($query, $params);

    // SE EXISTIR SERA DELETADO
    if ($this->conexao->num_rows() > 0)
    {
      return $this->conexao->fetch_object();
    }

    return NULL;
  }
}
