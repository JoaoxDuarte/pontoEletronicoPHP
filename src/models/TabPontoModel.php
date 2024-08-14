<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabPontoModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : pontoMMAAAA
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - src/models
 * @Arquivo   - TabPontoModel.php
 *
 * @author Edinalvo Rosa
 */
class TabPontoModel
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
     * @info Registros frequência por servidor e dia
     *
     * @param string $siape
     * @param string $dia
     * @return object
     */
    public function registrosPorSiapeDia($siape=null, $dia=null, $nome_do_arquivo=null)
    {
        $siape = (is_null($siape) || empty($siape) ? $_SESSION['sMatricula'] : getNovaMatriculaBySiape($siape));
        $dia   = (is_null($dia)   || empty($dia)   ? date('d/m/Y') : $dia);

        $comp            = dataMes($dia) . dataAno($dia);
        $nome_do_arquivo = (is_null($nome_do_arquivo) || empty($nome_do_arquivo) ? "ponto" . $comp : $nome_do_arquivo);

        $oDBase = new DataBase();
        $oDBase->setMensagem("Falha no registro do PONTO".$comp." (E200050.".__LINE__.")");

        $oDBase->query("
        SELECT
            pto.dia,
            pto.siape,
            pto.entra,
            pto.intini,
            pto.intsai,
            pto.sai,
            pto.jornd,
            pto.jornp,
            pto.jorndif,
            pto.oco,
            pto.just,
            pto.seq,
            pto.idreg,
            pto.ip,
            pto.ip2,
            pto.ip3,
            pto.ip4,
            pto.justchef,
            pto.ipch,
            pto.iprh,
            IFNULL(pto.matchef,'') AS matchef, 
            IFNULL(pto.siaperh,'') AS siaperh,
            tabocfre.desc_ocorr,
            tabocfre.desc_ocorr AS dcod, 
            tabsetor.codmun, 
            tabsetor.codigo, 
            servativ.cod_sitcad,
            servativ.sigregjur,
            servativ.cod_lot,
            servativ.nome_serv AS nome
        FROM
            " . $nome_do_arquivo . " AS pto
        LEFT JOIN
            tabocfre ON pto.oco = tabocfre.siapecad
        LEFT JOIN
            servativ ON pto.siape = servativ.mat_siape
        LEFT JOIN
            tabsetor ON servativ.cod_lot = tabsetor.codigo
        WHERE
            pto.siape = :siape
            AND pto.dia = :dia
            AND DAY(pto.dia) <> 0
            AND MONTH(pto.dia) <> 0
            AND YEAR(pto.dia) <> 0
        ORDER BY
            pto.siape, pto.dia
        ", array(
            array(":dia",   conv_data($dia), PDO::PARAM_STR),
            array(":siape", $siape,          PDO::PARAM_STR)
        ));

        return $oDBase;
    }


    /*
     * @info Registros frequência por servidor
     *
     * @param string $siape
     * @param string $compet
     * @return object
     */
    public function registrosPorID($siape = null, $compet = null, $nome_do_arquivo=null)
    {
        $siape           = (is_null($siape)  || empty($siape)  ? $_SESSION['sMatricula'] : getNovaMatriculaBySiape($siape));
        $comp            = (is_null($compet) || empty($compet) ? date('mY')              : $compet);
        $nome_do_arquivo = (is_null($nome_do_arquivo) || empty($nome_do_arquivo) ? "ponto" . $comp : $nome_do_arquivo);

        $oDBase = new DataBase();
        $oDBase->setMensagem("Falha no registro do PONTO".$comp." (E200051.".__LINE__.")");
        
        $oDBase->query("
        SELECT
            pto.siape,
            DATE_FORMAT(pto.dia,'%d/%m/%Y') AS dia,
            pto.entra,
            pto.intini,
            pto.intsai,
            pto.sai,
            pto.jornd,
            pto.jornp,
            pto.jorndif,
            pto.oco,
            pto.just,
            pto.seq,
            pto.idreg,
            pto.ip,
            pto.ip2,
            pto.ip3,
            pto.ip4,
            pto.justchef,
            pto.ipch,
            pto.iprh,
            IFNULL(pto.matchef,'') AS matchef, 
            IFNULL(pto.siaperh,'') AS siaperh,
            tabocfre.desc_ocorr,
            tabocfre.desc_ocorr AS dcod, 
            tabsetor.codmun, 
            tabsetor.codigo, 
            servativ.cod_sitcad,
            servativ.sigregjur,
            servativ.cod_lot,
            servativ.nome_serv AS nome
        FROM
            " . $nome_do_arquivo . " AS pto
        LEFT JOIN
            tabocfre ON pto.oco = tabocfre.siapecad
        LEFT JOIN
            servativ ON pto.siape = servativ.mat_siape
        LEFT JOIN
            tabsetor ON servativ.cod_lot = tabsetor.codigo
        WHERE
            pto.siape = :siape
            AND DAY(pto.dia) <> 0
            AND MONTH(pto.dia) <> 0
            AND YEAR(pto.dia) <> 0
        ORDER BY
            pto.siape, pto.dia
        ", array(
            array(":siape", $siape, PDO::PARAM_STR)
        ));

        return $oDBase;
    }


    /*
     * @info Registros frequência por servidor
     *
     * @param string $siape
     * @return object
     */
    public function registrosPorSiapeHistorico($siape=null)
    {
        $siape   = (is_null($siape) || empty($siape) ? $_SESSION['sMatricula'] : getNovaMatriculaBySiape($siape));
        $arquivo = $_SESSION['sHArquivoTemp'];

        $oDBase = new DataBase();
        $oDBase->setMensagem("Falha no registro do PONTO".$comp." (E200052.".__LINE__.")");
        
        $oDBase->query("
        SELECT
            pto.siape,
            DATE_FORMAT(pto.dia,'%d/%m/%Y') AS dia,
            pto.entra,
            pto.intini,
            pto.intsai,
            pto.sai,
            pto.jornd,
            pto.jornp,
            pto.jorndif,
            pto.oco,
            pto.just,
            pto.seq,
            pto.idreg,
            pto.ip,
            pto.ip2,
            pto.ip3,
            pto.ip4,
            pto.justchef,
            pto.ipch,
            pto.iprh,
            pto.matchef,
            pto.siaperh,
            tabocfre.desc_ocorr,
            tabocfre.desc_ocorr AS dcod, 
            tabsetor.codmun, 
            tabsetor.codigo, 
            servativ.cod_sitcad,
            servativ.sigregjur,
            servativ.cod_lot,
            servativ.nome_serv AS nome
        FROM
            " . $arquivo . " AS pto
        LEFT JOIN
            tabocfre ON pto.oco = tabocfre.siapecad
        LEFT JOIN
            servativ ON pto.siape = servativ.mat_siape
        LEFT JOIN
            tabsetor ON servativ.cod_lot = tabsetor.codigo
        WHERE
            pto.siape = :siape
            AND DAY(pto.dia) <> 0
            AND MONTH(pto.dia) <> 0
            AND YEAR(pto.dia) <> 0
        ORDER BY
            pto.siape, pto.dia
        ", array(
            array(":siape", $siape, PDO::PARAM_STR)
        ));

        return $oDBase;
    }


    /* @info  Seeleciona os registros de frequência do
     *        mês desejado e ourros dados do servidor
     *
     * @param  string  $siape Matrícula do servidor/estagiário
     * @param  string  $comp  Mês e ano desejado
     * @return  object  Dados da frequência e outros
     *
     * @author Edinalvo Rosa
     */
    public function selecionaRegistrosFrequenciaDoServidor($siape = null, $compet = null, $nome_do_arquivo=null)
    {
        return $this->registrosPorID($siape, $compet, $nome_do_arquivo );
    }


    /**
     * @info Inclui novo registro
     *
     * @param array $var
     * @return boolean TRUE sucesso
     */
    public function insert($var=null, $nome_do_arquivo=null)
    {
        $retorno = 'nao_gravou';

        if ( is_array($var) )
        {
            $comp = dataMes($var['dia']) . dataAno($var['dia']);
            $nome_do_arquivo = (is_null($nome_do_arquivo) || empty($nome_do_arquivo) ? "ponto" . $comp : $nome_do_arquivo);

            // verifica se já existe item igual
            $existe = $this->registrosPorSiapeDia($var['siape'], $var['dia'], $nome_do_arquivo);

            if ($existe->num_rows() > 0)
            {
                $retorno = 'ja_existe';
            }
            else
            {
                $oDBase = new DataBase();
                $oDBase->setMensagem("Falha no registro do PONTO".$comp." (E200053.".__LINE__.")");

                $oDBase->query("
                INSERT INTO " . $nome_do_arquivo . "
                    (dia, siape, entra, intini, intsai, sai, jornd, jornp, 
                     jorndif, oco, just, seq, idreg, ip, ip2, ip3, ip4, 
                     justchef, ipch, iprh, matchef, siaperh)
                    VALUES
                    (:dia, :siape, :entra, :intini, :intsai, :sai, :jornd, 
                     :jornp, :jorndif, :oco, :just, :seq, :idreg, :ip, :ip2, 
                     :ip3, :ip4, :justchef, :ipch, :iprh, :matchef, :siaperh)
                ",
                array(
                    array( ':dia',      $var['dia'],      PDO::PARAM_STR ),
                    array( ':siape',    $var['siape'],    PDO::PARAM_STR ),
                    array( ':entra',    $var['entra'],    PDO::PARAM_STR ),
                    array( ':intini',   $var['intini'],   PDO::PARAM_STR ),
                    array( ':intsai',   $var['intsai'],   PDO::PARAM_STR ),
                    array( ':sai',      $var['sai'],      PDO::PARAM_STR ),
                    array( ':jornd',    $var['jornd'],    PDO::PARAM_STR ),
                    array( ':jornp',    $var['jornp'],    PDO::PARAM_STR ),
                    array( ':jorndif',  $var['jorndif'],  PDO::PARAM_STR ),
                    array( ':oco',      $var['oco'],      PDO::PARAM_STR ),
                    array( ':just',     $var['just'],     PDO::PARAM_STR ),
                    array( ':seq',      '00',             PDO::PARAM_STR ),
                    array( ':idreg',    $var['idreg'],    PDO::PARAM_STR ),
                    array( ':ip',       $var['ip'],       PDO::PARAM_STR ),
                    array( ':ip2',      $var['ip2'],      PDO::PARAM_STR ),
                    array( ':ip3',      $var['ip3'],      PDO::PARAM_STR ),
                    array( ':ip4',      $var['ip4'],      PDO::PARAM_STR ),
                    array( ':justchef', $var['justchef'], PDO::PARAM_STR ),
                    array( ':ipch',     $var['ipch'],     PDO::PARAM_STR ),
                    array( ':iprh',     $var['iprh'],     PDO::PARAM_STR ),
                    array( ':matchef',  $var['matchef'],  PDO::PARAM_STR ),
                    array( ':siaperh',  $var['siaperh'],  PDO::PARAM_STR ),
                ));

                $affected_rows = ($oDBase->affected_rows() > 0);
                                
                if ($affected_rows)
                {
                    $this->insertHistorico($var, $oper='I');
                    
                    registraLog("Frequência (".$var['siape'].") registrada com sucesso!");
                    $retorno = 'gravou';
                }
                else
                {
                    registraLog("Frequência (".$var['siape'].") NÃO foi registrada!");
                    $retorno = 'nao_gravou';
                }
            }
        }

        return $retorno;
    }

    /**
     * @info Atualiza registro
     *
     * @param array $var
     * @return boolean TRUE sucesso
     */
    public function update($var=null, $nome_do_arquivo=null)
    {
        $retorno = 'nao_gravou';

        if ( !is_null($var) )
        {
            $comp            = dataMes($var['dia']) . dataAno($var['dia']);
            $nome_do_arquivo = (is_null($nome_do_arquivo) || empty($nome_do_arquivo) ? "ponto" . $comp : $nome_do_arquivo);

            // verifica se já existe item igual
            $existe = $this->registrosPorID($var['siape'], $comp);

            if ($existe->num_rows() == 0)
            {
                $retorno = 'nao_existe';
            }
            else
            {
                $this->insertHistorico($var, $oper='A');
                
                $oDBase = new DataBase();
                $oDBase->setMensagem("Falha no registro do PONTO".$comp." (E200054.".__LINE__.")");

                $oDBase->query("
                UPDATE " . $nome_do_arquivo . "
                    SET
                        entra    = :entra,
                        intini   = :intini,
                        intsai   = :intsai,
                        sai      = :sai,
                        jornd    = :jornd,
                        jornp    = :jornp,
                        jorndif  = :jorndif,
                        oco      = :oco,
                        just     = :just,
                        seq      = :seq,
                        idreg    = :idreg,
                        ip       = :ip,
                        ip2      = :ip2,
                        ip3      = :ip3,
                        ip4      = :ip4,
                        justchef = :justchef,
                        ipch     = :ipch,
                        iprh     = :iprh,
                        matchef  = :matchef,
                        siaperh  = :siaperh
                WHERE
                    siape = :siape
                    AND dia = :dia
                ",
                array(
                    array( ':dia',      $var['dia'],      PDO::PARAM_STR ),
                    array( ':siape',    $var['siape'],    PDO::PARAM_STR ),
                    array( ':entra',    $var['entra'],    PDO::PARAM_STR ),
                    array( ':intini',   $var['intini'],   PDO::PARAM_STR ),
                    array( ':intsai',   $var['intsai'],   PDO::PARAM_STR ),
                    array( ':sai',      $var['sai'],      PDO::PARAM_STR ),
                    array( ':jornd',    $var['jornd'],    PDO::PARAM_STR ),
                    array( ':jornp',    $var['jornp'],    PDO::PARAM_STR ),
                    array( ':jorndif',  $var['jorndif'],  PDO::PARAM_STR ),
                    array( ':oco',      $var['oco'],      PDO::PARAM_STR ),
                    array( ':just',     $var['just'],     PDO::PARAM_STR ),
                    array( ':seq',      $var['seq'],      PDO::PARAM_STR ),
                    array( ':idreg',    $var['idreg'],    PDO::PARAM_STR ),
                    array( ':ip',       $var['ip'],       PDO::PARAM_STR ),
                    array( ':ip2',      $var['ip2'],      PDO::PARAM_STR ),
                    array( ':ip3',      $var['ip3'],      PDO::PARAM_STR ),
                    array( ':ip4',      $var['ip4'],      PDO::PARAM_STR ),
                    array( ':justchef', $var['justchef'], PDO::PARAM_STR ),
                    array( ':ipch',     $var['ipch'],     PDO::PARAM_STR ),
                    array( ':iprh',     $var['iprh'],     PDO::PARAM_STR ),
                    array( ':matchef',  $var['matchef'],  PDO::PARAM_STR ),
                    array( ':siaperh',  $var['siaperh'],  PDO::PARAM_STR ),
                ));

                $affected_rows = ($oDBase->affected_rows() > 0);

                if ($affected_rows)
                {
                    registraLog("Alterada com sucesso, frequência (".$var['siape'].")!");
                    $retorno = 'gravou';
                }
                else
                {
                    registraLog("Frequência (".$var['siape'].") NÃO foi alterada!");
                    $retorno = 'nao_gravou';
                }
            }
        }

        return $retorno;
    }


    /**
     *
     * @param string $siape
     * @param string $dia
     * @return boolean TRUE sucesso
     */
    public function delete( $siape=null, $dia=null )
    {
        $siape = (is_null($siape) || empty($siape) ? $_SESSION['sMatricula'] : getNovaMatriculaBySiape($siape));
        $dia   = (is_null($dia)   || empty($dia)   ? date('d/m/Y') : $dia);

        $resultado = "error";

        if ( !is_null($var) )
        {
            $comp = dataMes($var['dia']) . dataAno($var['dia']);

            // verifica se já existe item igual
            $existe = $this->registrosPorID($var['siape'], $comp);

            if ($existe->num_rows() == 0)
            {
                $resultado = "warning";
            }
            else
            {
                $this->insertHistorico($var, $oper='E');

                $oDBase = new DataBase();
                $oDBase->setMensagem("Falha no registro do PONTO".$comp." (E200055.".__LINE__.")");

                $oDBase->query("
                DELETE FROM ponto" . $comp . "
                WHERE
                    dia = :dia
                    AND siape = :siape
                ", array(
                    array(":dia",   conv_data($dia), PDO::PARAM_STR),
                    array(":siape", $siape,          PDO::PARAM_STR)
                ));

                if ($oDBase->affected_rows() > 0)
                {
                    $resultado = "success";
                    registraLog("Deletou a frequência ( " . $var['siape'] . ", " . databarra($var['dia']) . ")!");
                }
            }
        }

        return $resultado;
    }


    /**
     * @info Inclui novo registro
     *
     * @param array $var
     * @param string $oper
     * @return boolean TRUE sucesso
     */
    public function insertHistorico($var=null, $oper='I')
    {
        $retorno = false;

        if ( is_array($var) )
        {
            $comp = dataMes($var['dia']) . dataAno($var['dia']);

            //sleep(1);

            // instancia o banco de dados
            $oDBase = new DataBase();
            $oDBase->setMensagem("Falha no registro do HISTÓRICO PONTO".$comp." (E200056.".__LINE__.").");

            // grava os dados anteriores no historico do ponto
            $oDBase->query("
            INSERT INTO histponto$comp
                SELECT
                    dia, siape, entra, intini, intsai, sai, jornd, jornp, 
                    jorndif, oco, idreg, ip, ip2, ip3, ip4, 
                    IFNULL(ipch,'') AS ipch, IFNULL(iprh,'') AS iprh, 
                    matchef, siaperh, DATE_FORMAT(NOW(),'%Y-%m-%d'),
                    DATE_FORMAT(NOW(),'%H:%i:%s'), :usuario, :ip, :oper,
                    just, justchef
                FROM
                    ponto$comp
                WHERE
                    dia = :dia
                    AND siape = :siape
            ",
            array(
                array(':dia',     $var['dia'],            PDO::PARAM_STR),
                array(':siape',   $var['siape'],          PDO::PARAM_STR),
                array(':usuario', $SESSION['sMatricula'], PDO::PARAM_STR),
                array(':ip',      getIpReal(),            PDO::PARAM_STR),
                array(':oper',    $oper,                  PDO::PARAM_STR),
            ));

            $affected_rows = ($oDBase->affected_rows() > 0);

            if ($affected_rows)
            {
                registraLog("Histórico (log) Frequência (".$var['siape'].") registrada com sucesso!");
                $retorno = true;
            }
            else
            {
                registraLog("Histórico (log) Frequência (".$var['siape'].") NÃO foi registrada!");
                $retorno = false;
            }
        }

        return $retorno;
    }
} // END class TabPontoModel
