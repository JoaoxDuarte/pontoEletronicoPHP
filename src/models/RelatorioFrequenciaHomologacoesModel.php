<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class RelatorioFrequenciaHomologacoesModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : homologados
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - src/models
 * @Arquivo   - RelatorioFrequenciaHomologacoesModel.php
 *
 * @author Edinalvo Rosa
 */
class RelatorioFrequenciaHomologacoesModel
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
     * @param string $ano  Ano da competência da homologação
     * @param string $mes  Mês da competência da homologação
     * @param string $upag UPAG da unidade do servidor/estagiário
     *
     * @info Total de servidores/estagiarios por UPAG
     */
    public function UnidadesTotalDeServidores($ano, $mes, $upag)
    {
        $compet = $ano . $mes;
        $dt_adm = (empty($compet) ? date('Ym') : $compet);

        $sql = "
        SELECT
            cad.cod_lot,
            und.descricao,
            und.cod_uorg_pai,
            und.uorg_pai,
            SUM(IF(IFNULL(homologados.homologado,'N') NOT IN ('V','S'),1,0)) AS homologados_nao,
            SUM(IF(IFNULL(homologados.homologado,'N') IN ('V','S'),1,0)) AS homologados_sim,
            SUM(IF(IFNULL(homologados.homologado,'N')='V',1,0)) AS homologados_visto,
            COUNT(*) AS total
        FROM
            servativ AS cad
        LEFT JOIN
            tabsetor AS und ON cad.cod_lot = und.codigo
        LEFT JOIN
            homologados ON (cad.mat_siape = homologados.mat_siape)
                           AND (homologados.compet = :compet)
        WHERE
            cad.excluido = 'N'
            AND cad.cod_sitcad NOT IN ('02','15','08')
            AND und.upag = :upag
            AND DATE_FORMAT(cad.dt_adm,'%Y%m') <= :dt_adm
        GROUP BY
            cad.cod_lot
        ";

        // instancia o banco de dados
        $this->conexao->query(
            $sql,
            array(
                array( ":compet", $compet, PDO::PARAM_STR ),
                array( ":upag",   $upag,   PDO::PARAM_STR ),
                array( ":dt_adm", $dt_adm, PDO::PARAM_STR ),
            )
        );

        return $this->conexao;
    }


    /*
     * @param string $ano  Ano da competência da homologação
     * @param string $mes  Mês da competência da homologação
     * @param string $upag UPAG da unidade do servidor/estagiário
     *
     * @info Lista as unidades e servidores para verificar se foram homologados
     */
    public function SituacaoHomologacaoPorMatricula( $siape = null, $dt_adm = null, $oco_exclu_dt = null, $compet = null )
    {
        $status = "HOMOLOGADO";
    
        if (is_null($compet))
        {
            $start = (is_null($dt_adm) ? '2009-10-01' : $dt_adm);
            $end   = ((is_null($oco_exclu_dt) || time_to_sec($oco_exclu_dt)) == 0
                            ? date('Y-m-d')
                            : $oco_exclu_dt);

            $meses = getDatesFromRange($start, $end, $format = 'Ym', $intervalo = 'P1M');
            array_pop( $meses );

            foreach ($meses as $compet)
            {
                $result       = verifica_se_mes_homologado($siape, $compet);
                $result_teste = tratarHTML( $result );

                if ($result_teste !== "HOMOLOGADO")
                {
                    $status = "PENDÊNCIA(S)";
                }
            }
        }
        else
        {
            $status = tratarHTML( verifica_se_mes_homologado($siape, $compet) );
        }

        return $status;
    }

} // END class RelatorioFrequenciaHomologacoesModel
