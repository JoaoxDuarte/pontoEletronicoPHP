<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "class_ocorrencias_grupos.php" );
include_once( "src/controllers/DadosServidoresController.php" );


/**
 * @class TabOcorrenciaModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : tabocfre
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - src/models
 * @Arquivo   - TabOcorrenciaModel.php
 *
 * @author Edinalvo Rosa
 */
class TabOcorrenciaModel
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
    public function padraoSQL($codigo=null, $semdDescricaoVazia=false, $soAtivo=false, $soGrupoOcorr=null)
    {
        if (is_null($codigo) || empty($codigo))
        {
            $where  = " WHERE TRUE ";
            $params = null;
        }
        else
        {
            $where  = " WHERE tabocfre.siapecad = :codigo ";
            $params = array(
                array( ':codigo', $codigo, PDO::PARAM_STR),
            );
        }

        $where .= (is_bool($semdDescricaoVazia) && $semdDescricaoVazia == true ? " AND TRIM(tabocfre.desc_ocorr) <> '' " : "");
        $where .= (is_bool($soAtivo)            && $soAtivo            == true ? " AND tabocfre.ativo = 'S' " : "");
        $where .= (!is_null($soGrupoOcorr)      && !empty($soGrupoOcorr)       ? " AND tabocfre.grupo_ocorrencia = '$soGrupoOcorr' " : "");
        
        $query = "
        SELECT
            tabocfre.siapecad,
            tabocfre.desc_ocorr,
            tabocfre.cod_ocorr,
            @num := @num + 1 AS seq,
            tabocfre.smap_ocorrencia,
            tabocfre.cod_siape,
            tabocfre.resp,
            tabocfre.aplic,
            tabocfre.implic,
            tabocfre.prazo,
            tabocfre.flegal,
            tabocfre.ativo,
            tabocfre.semrem,
            tabocfre.idsiapecad,
            tabocfre.grupo,
            tabocfre.tipo,
            tabocfre.situacao,
            tabocfre.justificativa,
            tabocfre.postergar_pagar_recesso,
            tabocfre.tratamento_debito,
            tabocfre.padrao,
            tabocfre.grupo_cadastral,
            tabocfre.agrupa_debito,
            tabocfre.grupo_ocorrencia,
            tabocfre.informar_horarios,
            tabocfre.vigencia_inicio,
            tabocfre.vigencia_fim
        FROM
            tabocfre, (SELECT @num := 0) as t 
        
        $where
        
        ORDER BY 
            tabocfre.ativo DESC, tabocfre.desc_ocorr
        ";

        $oDBase = new DataBase();
        $oDBase->setMensagem( "Problemas no acesso a Tabela OCORRÊNCIAS (E200019.".__LINE__.")." );
        $oDBase->query( $query, $params );

        return $oDBase;
    }

    /*
     * @info Registros Isenção de Ponto
     *
     * @param void
     * @return object
     */
    public function registros($codigo=null, $semdDescricaoVazia=false, $soAtivo=false, $soGrupoOcorr=null)
    {
        return $this->padraoSQL($codigo, $semdDescricaoVazia, $soAtivo, $soGrupoOcorr);
    }


    /*
     * @info Registros
     *
     * @param void
     * @return json
     */
    public function registrosRetornoAjax($codigo=null, $semdDescricaoVazia=true, $soAtivo=false)
    {
        $oDBase = $this->registros($codigo, $semdDescricaoVazia, $soAtivo);

        $result = array();

        while ($dados = $oDBase->fetch_object())
        {
        //$dados = $oDBase->fetch_object();
            $alterar    = "&nbsp;&nbsp;<a style=\"text-align:center;\" href=\"tabocorrencia_alterar.php?codigo=". tratarHTML($dados->siapecad) . "\"><span class=\"glyphicon glyphicon-pencil\" alt=\"Editar cargo\" title=\"Editar Registro\"></span></a>";
            $separador1  = ""; //&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            $excluir    = ""; //"<a class=\"text-center\" href=\"javascript:Delete('" . tratarHTML($dados->siapecad) . "');\"><span class=\"glyphicon glyphicon-trash\" alt=\"Excluir registro\" title=\"Excluir registro\"></span></a>";
            $separador2  = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            $visualizar = "<a style=\"text-align:center;\" href=\"tabocorrencia_visualizar.php?siapecad=". tratarHTML($dados->siapecad) . "\"><span class=\"glyphicon glyphicon-eye-open\" alt=\"Visualizar detalhes\" title=\"Editar Registro\"></span></a>&nbsp;&nbsp;";

            $result[] = array(
                $dados->seq,
                utf8_encode(
                    $alterar .
                    $separador1 .
                    $excluir .
                    $separador2 .
                    $visualizar
                ),
                $dados->siapecad,
                utf8_encode($dados->desc_ocorr),
                $dados->resp,
                utf8_encode($dados->cod_ocorr),
                ($dados->ativo != "S" ? utf8_encode("NÃO") : "SIM")
            );
        }
        
        $myData = array(
            'data' => $result);

        print json_encode($myData);
    }


    /*
     * @info Registros Isenção de Ponto
     *
     * @param void
     * @return object
     */
    public function registrosPorID($codigo=null)
    {
        return $this->padraoSQL($codigo);
    }


    /**
     * @info Inlcui novo registro
     *
     * @param array $var
     * @return boolean TRUE sucesso
     */
    public function insert($var=null)
    {
        $retorno = '';

        if ( !is_null($var) )
        {
            // verifica se já existe item igual
            $existe = $this->registrosPorID($var['siapecad']);

            if ($existe->num_rows() > 0)
            {
                $retorno = 'ja_existe';
            }
            else
            {
                $this->conexao->setMensagem( "Problemas no acesso a Tabela OCORRÊNCIAS (E200020.".__LINE__.")." );
                $this->conexao->query("
                INSERT INTO tabocfre
                    (tabocfre.siapecad, tabocfre.smap_ocorrencia, 
                     tabocfre.desc_ocorr, tabocfre.cod_ocorr, 
                     tabocfre.cod_siape, tabocfre.resp, tabocfre.aplic, 
                     tabocfre.implic, tabocfre.prazo, tabocfre.flegal, 
                     tabocfre.ativo, tabocfre.semrem, tabocfre.idsiapecad,
                     tabocfre.grupo, tabocfre.tipo, tabocfre.situacao,
                     tabocfre.justificativa, tabocfre.postergar_pagar_recesso,
                     tabocfre.tratamento_debito, tabocfre.padrao,
                     tabocfre.grupo_cadastral, tabocfre.agrupa_debito,
                     tabocfre.grupo_ocorrencia, tabocfre.informar_horarios,
                     tabocfre.vigencia_inicio, tabocfre.vigencia_fim)
                    VALUES
                    (:siapecad, :smap_ocorrencia, :desc_ocorr, :cod_ocorr, 
                     :cod_siape, :resp, :aplic, :implic, :prazo, :flegal, 
                     :ativo, :semrem, :idsiapecad, :grupo, :tipo, :situacao,
                     :justificativa, :postergar_pagar_recesso, 
                     :tratamento_debito, :padrao, :grupo_cadastral, 
                     :agrupa_debito, :grupo_ocorrencia, :informar_horarios,
                     :vigencia_inicio, :vigencia_fim)
                ",
                array(
                    array(":siapecad",                $var["siapecad"],                   PDO::PARAMT_STR),
                    array(":smap_ocorrencia",         $var["smap_ocorrencia"],            PDO::PARAMT_STR),
                    array(":desc_ocorr",              mb_strtoupper($var["desc_ocorr"]),  PDO::PARAMT_STR),
                    array(":cod_ocorr",               mb_strtoupper($var["cod_ocorr"]),   PDO::PARAMT_STR),
                    array(":cod_siape",               $var["cod_siape"],                  PDO::PARAMT_STR),
                    array(":resp",                    $var["resp"],                       PDO::PARAMT_STR),
                    array(":aplic",                   mb_strtoupper($var["aplic"]),       PDO::PARAMT_STR),
                    array(":implic",                  mb_strtoupper($var["implic"]),      PDO::PARAMT_STR),
                    array(":prazo",                   $var["prazo"],                      PDO::PARAMT_STR),
                    array(":flegal",                  $var["flegal"],                     PDO::PARAMT_STR),
                    array(":ativo",                   $var["ativo"],                      PDO::PARAMT_STR),
                    array(":semrem",                  $var["semrem"],                     PDO::PARAMT_STR),
                    array(":idsiapecad",              $var["idsiapecad"],                 PDO::PARAMT_STR),
                    array(":grupo",                   $var["grupo"],                      PDO::PARAMT_STR),
                    array(":tipo",                    $var["tipo"],                       PDO::PARAMT_STR),
                    array(":situacao",                $var["situacao"],                   PDO::PARAMT_STR),
                    array(":justificativa",           $var["justificativa"],              PDO::PARAMT_STR),
                    array(":postergar_pagar_recesso", $var["postergar_pagar_recesso"],    PDO::PARAMT_STR), 
                    array(":tratamento_debito",       $var["tratamento_debito"],          PDO::PARAMT_STR),
                    array(":padrao",                  $var["padrao"],                     PDO::PARAMT_STR),
                    array(":grupo_cadastral",         $var["grupo_cadastral"],            PDO::PARAMT_STR),
                    array(":agrupa_debito",           $var["agrupa_debito"],              PDO::PARAMT_STR),
                    array(":grupo_ocorrencia",        $var["grupo_ocorrencia"],           PDO::PARAMT_STR),
                    array(":informar_horarios",       $var["informar_horarios"],          PDO::PARAMT_STR),
                    array(":vigencia_inicio",         conv_data($var["vigencia_inicio"]), PDO::PARAMT_STR),
                    array(":vigencia_fim",            conv_data($var["vigencia_fim"]),    PDO::PARAMT_STR),        
                ));

                $affected_rows = ($this->conexao->affected_rows() > 0);

                if ($affected_rows)
                {
                    registraLog("Ocorrência (".$var['siapecad'].") registrado com sucesso!");
                    $retorno = 'gravou';
                }
                else
                {
                    registraLog("Ocorrência (".$var['siapecad'].") NÃO foi registrado!");
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
            // verifica se já existe item igual
            $existe = $this->registrosPorID($var['siapecad']);

            if ($existe->num_rows() == 0)
            {
                $retorno = 'nao_existe';
            }
            else
            {
                $this->conexao->setMensagem( "Problemas no acesso a Tabela OCORRÊNCIAS (E200021.".__LINE__.")." );
                $this->conexao->query("
                UPDATE tabocfre
                    SET
                        tabocfre.smap_ocorrencia         = :smap_ocorrencia,
                        tabocfre.desc_ocorr              = :desc_ocorr,
                        tabocfre.cod_ocorr               = :cod_ocorr,
                        tabocfre.cod_siape               = :cod_siape,
                        tabocfre.resp                    = :resp,
                        tabocfre.aplic                   = :aplic,
                        tabocfre.implic                  = :implic,
                        tabocfre.prazo                   = :prazo,
                        tabocfre.flegal                  = :flegal,
                        tabocfre.ativo                   = :ativo,
                        tabocfre.semrem                  = :semrem,
                        tabocfre.idsiapecad              = :idsiapecad,
                        tabocfre.grupo                   = :grupo,
                        tabocfre.tipo                    = :tipo,
                        tabocfre.situacao                = :situacao,
                        tabocfre.justificativa           = :justificativa,
                        tabocfre.postergar_pagar_recesso = :postergar_pagar_recesso,
                        tabocfre.tratamento_debito       = :tratamento_debito,
                        tabocfre.padrao                  = :padrao,
                        tabocfre.grupo_cadastral         = :grupo_cadastral,
                        tabocfre.agrupa_debito           = :agrupa_debito,
                        tabocfre.grupo_ocorrencia        = :grupo_ocorrencia,
                        tabocfre.informar_horarios       = :informar_horarios,
                        tabocfre.vigencia_inicio         = :vigencia_inicio,
                        tabocfre.vigencia_fim            = :vigencia_fim
                            WHERE tabocfre.siapecad = :siapecad
                ",
                array(
                    array(":siapecad",                $var["siapecad"],                   PDO::PARAMT_STR),
                    array(":smap_ocorrencia",         $var["smap_ocorrencia"],            PDO::PARAMT_STR),
                    array(":desc_ocorr",              mb_strtoupper($var["desc_ocorr"]),  PDO::PARAMT_STR),
                    array(":cod_ocorr",               mb_strtoupper($var["cod_ocorr"]),   PDO::PARAMT_STR),
                    array(":cod_siape",               $var["cod_siape"],                  PDO::PARAMT_STR),
                    array(":resp",                    $var["resp"],                       PDO::PARAMT_STR),
                    array(":aplic",                   mb_strtoupper($var["aplic"]),       PDO::PARAMT_STR),
                    array(":implic",                  mb_strtoupper($var["implic"]),      PDO::PARAMT_STR),
                    array(":prazo",                   $var["prazo"],                      PDO::PARAMT_STR),
                    array(":flegal",                  $var["flegal"],                     PDO::PARAMT_STR),
                    array(":ativo",                   $var["ativo"],                      PDO::PARAMT_STR),
                    array(":semrem",                  $var["semrem"],                     PDO::PARAMT_STR),
                    array(":idsiapecad",              $var["idsiapecad"],                 PDO::PARAMT_STR),
                    array(":grupo",                   $var["grupo"],                      PDO::PARAMT_STR),
                    array(":tipo",                    $var["tipo"],                       PDO::PARAMT_STR),
                    array(":situacao",                $var["situacao"],                   PDO::PARAMT_STR),
                    array(":justificativa",           $var["justificativa"],              PDO::PARAMT_STR),
                    array(":postergar_pagar_recesso", $var["postergar_pagar_recesso"],    PDO::PARAMT_STR), 
                    array(":tratamento_debito",       $var["tratamento_debito"],          PDO::PARAMT_STR),
                    array(":padrao",                  $var["padrao"],                     PDO::PARAMT_STR),
                    array(":grupo_cadastral",         $var["grupo_cadastral"],            PDO::PARAMT_STR),
                    array(":agrupa_debito",           $var["agrupa_debito"],              PDO::PARAMT_STR),
                    array(":grupo_ocorrencia",        $var["grupo_ocorrencia"],           PDO::PARAMT_STR),
                    array(":informar_horarios",       $var["informar_horarios"],          PDO::PARAMT_STR),
                    array(":vigencia_inicio",         conv_data($var["vigencia_inicio"]), PDO::PARAMT_STR),
                    array(":vigencia_fim",            conv_data($var["vigencia_fim"]),    PDO::PARAMT_STR),        
                ));

                $affected_rows = ($this->conexao->affected_rows() > 0);

                if ($affected_rows)
                {
                    registraLog("Alterado com sucesso (".$var['siapecad'].")!");
                    $retorno = 'gravou';
                }
                else
                {
                    registraLog("Alteração NÃO foi realizada (".$var['siapecad'].")!");
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
        $resultado = "error";

        if ($num_rows > 0)
        {
            $resultado = "warning";
        }
        else
        {
            $query = "
            UPDATE tabocfre
                SET tabocfre.ativo = 'N'
                    WHERE tabocfre.siapecad = :siapecad
            ";

            $params = array(
                array(":siapecad", $id, PDO::PARAMT_STR),
            );

            $this->conexao->setMensagem( "Problemas no acesso a Tabela OCORRÊNCIAS (E200022.".__LINE__.")." );
            $this->conexao->query( $query, $params );

            if ($this->conexao->affected_rows() > 0)
            {
                $resultado = "success";
                registraLog("Deletou a ocorrência ".$id);
            }
        }

        return $resultado;
    }


    /*
     * @info Carrega os dados de campos ENUM
     *
     * @param string $tabela    Nome da tabela no banco de dados
     * @param string $campo     Nome do campo ENUM na tabela
     *
     * @return array
     *
     * @author Edinalvo Rosa
     */
    public function carregaCampoEnum( $tabela=null, $campo=null )
    {
        $retorno = array();
        
        if ( !is_null($tabela) && !is_null($campo) )
        {
            $this->conexao->setMensagem( "Problemas no acesso a Tabela OCORRÊNCIAS (E200023.".__LINE__.")." );
            $this->conexao->query( "DESCRIBE $tabela " );

            while ($lista = $this->conexao->fetch_assoc())
            {
                if ($lista['Field'] == $campo)
                {
                    $retorno = explode(',',str_replace("'",'',str_replace(')','',str_replace('enum(','',$lista['Type']))));
                }
            }
        }
        
        return $retorno;
    }

    public function dadosCampoENUM( $tabela=null, $campo=null )
    {
        return carregaCampoEnum($tabela, $campo);
    }

    
    /**
     * @info Monta listbox com códigos de ocorrência
     *
     * @param  string  $valor       Valor para marcarcomo selecionado     
     * @param  integer $tamdescr    Largura do list box                   
     * @param  boolean $imprimir    Retornará como texto ou exibirá       
     * @param  boolean $por_periodo Indica se a ocorrencia eh por periodo 
     * @param  boolean $historico   Indica se a será exibida no histórico 
     * @param  string  $onchange    Função javascript para troca de opção ou seleção
     * @param  string  $grupo       Grupo de origem (acaompanhar/homologar/...)
     * @return string HTML
     */
    public function carregaSelectOcorrencias($valor = '', $por_periodo = false, $historico = false, $grupo='', $siape = '')
    {
        // instancia class
        $oDadosServidores = new DadosServidoresController();
        $sitcad = $oDadosServidores->getSigRegJur($siape);

        ## ocorrências grupos
        $obj = new OcorrenciasGrupos();
        $codigoFrequenciaNormalPadrao   = $obj->CodigoFrequenciaNormalPadrao($sitcad);
        $codigoBancoDeHorasDebitoPadrao = $obj->CodigoBancoDeHorasDebitoPadrao($sitcad);
        $codigoOcorrenciasViagem        = $obj->GrupoOcorrenciasViagem($sitcad);

        $grupo_situacao = (isset($sitcad) && ($sitcad == '66' || $sitcad == 'ETG') ? '"Ambos","Estagiario"' : '"Ambos","Servidor"');

        if ($por_periodo == true)
        {
            $codigo_por_periodo = ' AND oco.informar_horarios = "N" '
                . ($_SESSION['sRH'] == 'S' ? ' OR oco.siapecad IN ("'.$codigoFrequenciaNormalPadrao[0].'","'.$codigoOcorrenciasViagem[0].'") ' : '');
        }
        else if ($grupo == 'historico_manutencao')
        {
            $codigo_por_periodo = ' AND oco.siapecad NOT IN ("'.$codigoBancoDeHorasDebitoPadrao[0].'") ';
            $valor              = 'X';
        }
        else
        {
            $codigo_por_periodo = '';
        }

        if (($_SESSION['sAPS'] == 'N' && $_SESSION['sRH'] == 'S') || $historico == true)
        {
            $grupo_atuacao = '"CH","AB","RH"';
        }
        else
        {
            $grupo_atuacao = '"CH","AB"';
        }

        $sql = '
        SELECT
            oco.siapecad, oco.desc_ocorr, oco.cod_ocorr
        FROM
            tabocfre AS oco
        WHERE
            (oco.resp IN (' . $grupo_atuacao . ')
            AND oco.ativo = "S"
            AND oco.grupo IN (' . $grupo_situacao . ') '
            . $codigo_por_periodo . ')
            OR oco.siapecad = "' . $valor . '"
            OR oco.siapecad = "-----"
        ORDER BY
            oco.desc_ocorr
        ';

        $this->conexao->setMensagem( "Problemas no acesso a Tabela OCORRÊNCIAS (E200022.".__LINE__.")." );
        $this->conexao->query( $sql );
        
        return $this->conexao;
    }
    
} // END class TabOcorrenciaModel
