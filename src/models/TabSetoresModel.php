<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabSetoresModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : tabsetor
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - src/models
 * @Arquivo   - TabSetoresModel.php
 *
 */
class TabSetoresModel
{
    /*
     * Atributos
     */
    public $conexao;


    public function __construct()
    {
        # Faz conexão
        $this->conexao = new DataBase();
    }


    /**
     * @info Dados de uma unidade - por código
     *
     * @param string $setor Setor
     * @return object
     */
    public function dadosUnidadePorCodigo($setor=null)
    {
        // seleciona os registros para homologação
        $this->conexao->query("
        SELECT
            tabsetor.codigo, tabsetor.descricao, taborgao.denominacao, 
            taborgao.sigla, tabsetor.upag
        FROM
            tabsetor
        LEFT JOIN
            taborgao ON LEFT(tabsetor.codigo,5) = taborgao.codigo
        WHERE
            tabsetor.codigo = :codigo
            AND tabsetor.ativo = 'S'
        ",
        array(
            array( ':codigo', $setor, PDO::PARAM_STR ),
        ));

        return $this->conexao->fetch_object();
    }


    /**
     * @info Seleção dos registros por upag
     *
     * @param string $upag Código da UPAG
     * @return object
     */
    public function selecionaUnidadesPorUpag($upag=null)
    {
        $oDBase = new DataBase();
        
        // seleciona os registros para homologação
        $query = "
        SELECT
            tabsetor.codigo, tabsetor.descricao, taborgao.denominacao, 
            taborgao.sigla, tabsetor.upag
        FROM
            tabsetor
        LEFT JOIN
            taborgao ON LEFT(tabsetor.codigo,5) = taborgao.codigo
        WHERE
            tabsetor.upag = :upag
            AND tabsetor.ativo = 'S'
        ORDER BY
            tabsetor.descricao
        ";

        $params = array(
            array( ':upag', $upag, PDO::PARAM_STR )
        );
        
        $oDBase->query( $query, $params );
        
        return $oDBase;
    }



  /**
   * @info Alterar dados do setor
   *
   * @param string $var1 Chave de pesquisa
   * @param string $var2 Dados a pesquisar
   * @param string $groupby Campos a agrupar
   * @return object
   */
    public function updateSetor($dados, $codigo='')
    {
        $oDBase = new DataBase();
        
        $query = "
        UPDATE tabcargo
            SET codigo                        = :codigo,
                cod_uorg                      = :cod_uorg,
                descricao                     = :descricao,
                cod_uorg_pai                  = :cod_uorg_pai,
                uorg_pai                      = :uorg_pai,
                upag                          = :upag,
                ug                            = :ug,
                area                          = :area,
                inicio_atend                  = :inicio_atend,
                fim_atend                     = :fim_atend,
                ativo                         = :ativo,
                periodo_excecao               = :periodo_excecao,
                sigla                         = :sigla,
                cidade_lota                   = :cidade_lota,
                uf_lota                       = :uf_lota,
                codmun                        = :codmun,
                fuso_horario                  = :fuso_horario,
                horario_verao                 = :horario_verao,
                liberar_homologacao           = :liberar_homologacao
                    WHERE codigo = :codigo
        ";

        $paramns = array(
            array(":codigo",              $codigo,                       PDO::PARAM_STR),
            array(":cod_uorg",            $dados['cod_uorg'],            PDO::PARAM_STR),
            array(":descricao",           $dados['descricao'],           PDO::PARAM_STR),
            array(":cod_uorg_pai",        $dados['cod_uorg_pai'],        PDO::PARAM_STR),
            array(":uorg_pai",            $dados['uorg_pai'],            PDO::PARAM_STR),
            array(":upag",                $dados['upag'],                PDO::PARAM_STR),
            array(":ug",                  $dados['ug'],                  PDO::PARAM_STR),
            array(":area",                $dados['area'],                PDO::PARAM_STR),
            array(":inicio_atend",        $dados['inicio_atend'],        PDO::PARAM_STR),
            array(":fim_atend",           $dados['fim_atend'],           PDO::PARAM_STR),
            array(":ativo",               $dados['ativo'],               PDO::PARAM_STR),
            array(":periodo_excecao",     $dados['periodo_excecao'],     PDO::PARAM_STR),
            array(":sigla",               $dados['sigla'],               PDO::PARAM_STR),
            array(":cidade_lota",         $dados['cidade_lota'],         PDO::PARAM_STR),
            array(":uf_lota",             $dados['uf_lota'],             PDO::PARAM_STR),
            array(":codmun",              $dados['codmun'],              PDO::PARAM_STR),
            array(":fuso_horario",        $dados['fuso_horario'],        PDO::PARAM_STR),
            array(":horario_verao",       $dados['horario_verao'],       PDO::PARAM_STR),
            array(":liberar_homologacao", $dados['liberar_homologacao'], PDO::PARAM_STR),
        );

        $oDBase->query($query, $paramns);

        return $oDBase;
    }




  /**
   * @info Seleção dos registros
   *
   * @param string $var1 Chave de pesquisa
   * @param string $var2 Dados a pesquisar
   * @param string $groupby Campos a agrupar
   * @return object
   */
    public function cadastrarCargoFuncao($dados)
    {
        $oDBase = new DataBase('PDO');

        $query = ("INSERT INTO tabcargo (`COD_CARGO`,`DESC_CARGO`,`PERMITE_BANCO`,`SUBSIDIOS`) VALUES (:cargo , :nome , :permite, :subsidios)");

        $paramns = array(
            array(":cargo", $dados['COD_CARGO'], PDO::PARAM_STR),
            array(":nome", $dados['DESC_CARGO'], PDO::PARAM_STR),
            array(":permite", $dados['PERMITE_BANCO'], PDO::PARAM_STR),
            array(":subsidios", $dados['SUBSIDIOS'], PDO::PARAM_STR)
        );

        $oDBase->query($query, $paramns);

        return $oDBase;
    }
}
