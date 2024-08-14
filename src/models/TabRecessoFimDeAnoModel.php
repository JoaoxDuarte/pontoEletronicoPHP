<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "class_ocorrencias_grupos.php" );


/**
 * @class TabRecessoFimDeAnoModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : tabrecesso_fimdeano
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - src/models
 * @Arquivo   - TabRecessoFimDeAnoModel.php
 *
 * @author Edinalvo Rosa
 */
class TabRecessoFimDeAnoModel extends formPadrao
{
    /*
    * Atributos
    */
    /* @var OBJECT */ public $conexao              = NULL;
    /* @var OBJECT */ public $objOcorrenciasGrupos = NULL;

    ## @constructor
    #+-----------------------------------------------------------------+
    # Construtor da classe
    #+-----------------------------------------------------------------+
    #
    function __construct()
    {
        # Faz conexão
        $this->conexao              = new DataBase();
        $this->objOcorrenciasGrupos = new OcorrenciasGrupos();
    }

    
    /**
     * @info Lista dados do período
     *
     * @param object $obj Ano Inicial
     * @return result
     */
    public function dadosPeriodoRecesso( $periodo = null )
    {
        $filtro = "";
        $limit  = "";
        $params = null;

        if ( !is_null($periodo) && !empty($period) )
        {
            $filtro = " AND periodo = :periodo ";
            $params = array(
                array( ':periodo', $periodo, PDO::PARAM_STR )
            );
        }
        else
        {
            $limit = " LIMIT 1";
        }

        $query = "
        SELECT
            id,
            periodo,
            recesso_inicio,
            recesso_fim,
            recesso_inicio_compensacao,
            recesso_fim_compensacao,
            ativo,
            (TIMESTAMPDIFF(MONTH,recesso_inicio_compensacao,recesso_fim_compensacao)+1) AS meses_compensacao
        FROM
            tabrecesso_fimdeano
        WHERE
            TRUE
            " . $filtro . "
        ORDER BY 
            periodo DESC
        " . $limit;
        
        $this->conexao->query( $query, $params );

        return $this->conexao;
    }

    
    /**
     * @info Carrega registros
     *
     * @param string $siape Matrícula
     * @param string $periodo Período do recesso. Ex.2018/2019
     * @return array
     */
    public function registrosRecessoFrequencia( $siape = null, $dados = null )
    {
        // códigos de ocorrências
        $codigoCreditoRecessoPadrao = $this->objOcorrenciasGrupos->CodigoCreditoRecessoPadrao( $sitcad );
        $codigoDebitoRecessoPadrao  = $this->objOcorrenciasGrupos->CodigoDebitoRecessoPadrao( $sitcad );
        $grupoOcorrenciasViagem     = $this->objOcorrenciasGrupos->GrupoOcorrenciasViagem( $sitcad );

        $codigos_creditos = implode(",", $codigoCreditoRecessoPadrao) . "," .
                            implode(",", $grupoOcorrenciasViagem);
        $codigos_debitos  = implode(",", $codigoDebitoRecessoPadrao);
        
        $total_credito  = 0;
        $total_debito   = 0;
        $total_subtotal = 0;
        $dados_recesso  = array();
        $recesso        = $dados->fetch_object();
        
        if (is_string($siape) && !empty($siape) && is_object($recesso))
        {
            $compet = dataMes($recesso->recesso_inicio_compensacao) . dataAno($recesso->recesso_inicio_compensacao);
            $data   = $recesso->recesso_inicio_compensacao;

            for ($x = 1; $x <= $recesso->meses_compensacao; $x++)
            { 
                $this->conexao->query("
                    SELECT
                        servativ.mat_siape         AS siape, 
                        servativ.nome_serv         AS nome, 
                        DATE_FORMAT(a.dia,'%m/%Y') AS compet, 
                        TIME_FORMAT(SEC_TO_TIME(SUM(IF(a.oco IN (" . $codigos_creditos . "),TIME_TO_SEC(a.jorndif),0))),'%H:%i')    AS creditos, 
                        TIME_FORMAT(SEC_TO_TIME(SUM(IF(a.oco IN (" . $codigos_debitos  . "),TIME_TO_SEC(a.jorndif),0))*-1),'%H:%i') AS debitos, 
                        TIME_FORMAT(SEC_TO_TIME((SUM(IF(a.oco IN (" . $codigos_creditos . "),TIME_TO_SEC(a.jorndif),0))) 
                                                 - SUM(IF(a.oco IN (" . $codigos_debitos . "),TIME_TO_SEC(a.jorndif),0))),'%H:%i')  AS subtotal,
                        DATE_FORMAT(ADDDATE('".$data."', INTERVAL 1 MONTH),'%m%Y') AS proxima_compet,
                        ADDDATE('".$data."', INTERVAL 1 MONTH)                     AS proximo_mes
                    FROM
                        servativ
                    LEFT JOIN    
                        ponto" . $compet . " AS a ON servativ.mat_siape = a.siape
                    WHERE 
                        servativ.mat_siape = :siape
                        AND a.oco IN (" . implode( ',', $codigoDebitoRecessoPadrao )  . "," . implode( ',', $codigoCreditoRecessoPadrao ) . ")
                        AND (a.dia >= :recesso_inicio_compensacao AND a.dia <= :recesso_fim_compensacao)
                    GROUP BY
                        servativ.mat_siape;
                    ", array(
                        array( ':siape',                      $siape,                               PDO::PARAM_STR ),
                        array( ':recesso_inicio_compensacao', $recesso->recesso_inicio_compensacao, PDO::PARAM_STR ),
                        array( ':recesso_fim_compensacao',    $recesso->recesso_fim_compensacao,    PDO::PARAM_STR ),
                    ));   

                if ($this->conexao && $this->conexao->num_rows() > 0)
                {
                    $quadro = $this->conexao->fetch_object();

                    $dados_recesso[] = array(
                        'siape'    => $quadro->siape,
                        'nome'     => $quadro->nome,
                        'periodo'  => $recesso->periodo,
                        'compet'   => $quadro->compet,
                        'credito'  => $quadro->creditos,
                        'debito'   => $quadro->debitos,
                        'subtotal' => $quadro->subtotal
                    );

                    $compet = $quadro->proxima_compet;
                    $data   = (empty($quadro->proximo_mes) ? $data : $quadro->proximo_mes);
    
                    $total_credito  += time_to_sec($quadro->creditos);
                    $total_debito   += time_to_sec($quadro->debitos);
                    $total_subtotal += time_to_sec($quadro->subtotal);
                }
                else
                {
                    $this->conexao->query("
                        SELECT
                            DATE_FORMAT('".$data."','%m/%Y')                           AS compet,
                            DATE_FORMAT(ADDDATE('".$data."', INTERVAL 1 MONTH),'%m%Y') AS proxima_compet,
                            ADDDATE('".$data."', INTERVAL 1 MONTH)                     AS proximo_mes
                        ");

                    $quadro = $this->conexao->fetch_object();

                    $dados_recesso[] = array(
                        'siape'    => $quadro->siape,
                        'nome'     => $quadro->nome,
                        'periodo'  => $recesso->periodo,
                        'compet'   => $quadro->compet,
                        'credito'  => '00:00',
                        'debito'   => '00:00',
                        'subtotal' => '00:00'
                    );
                    
                    $compet = $quadro->proxima_compet;
                    $data   = $quadro->proximo_mes;
                }
            }

            $this->conexao->query("
                SELECT 
                    TIME_FORMAT(SEC_TO_TIME(:credito),'%H:%i')                        AS creditos, 
                    TIME_FORMAT(SEC_TO_TIME(:debito),'%H:%i')                         AS debitos,
                    TIME_FORMAT(SEC_TO_TIME(:credito - :debito),'%H:%i') AS subtotal
            ", array(
                array( ':credito',  $total_credito,  PDO::PARAM_STR),
                array( ':debito',   $total_debito,   PDO::PARAM_STR)
            ));

            $totais = $this->conexao->fetch_object();

            $dados_recesso[] = array(
                'siape'    => $quadro->siape,
                'nome'     => $quadro->nome,
                'compet'   => 'Total',
                'credito'  => $totais->creditos,
                'debito'   => $totais->debitos,
                'subtotal' => $totais->subtotal
            );
            
            
        }
        
        return $dados_recesso;
    }

} // END class TabRecessoFimDeAnoModel
