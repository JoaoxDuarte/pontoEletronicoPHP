<?php

/**  @Class
 * +-------------------------------------------------------------+
 * | @class       : OperaRegistroFRQANO                          |
 * | @description : CRUD do banco de dados frq<ano>              |
 * | @copyright   : (C) 2012 INSS                                |
 * | @license     :                                              |
 * | @author      : Edinalvo Rosa                                |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : void                                              |
 * | $method : OperaRegistroFRQANO   Construtor                  |
 * | $method : setSiape()        Matrícula siape do servidor     |
 * |           getSiape()                                        |
 * | $method : setMes()          Mês de competência              |
 * |           getMes()                                          |
 * | $method : setAno()          Ano de competência              |
 * |           getAno()                                          |
 * | $method : setDestino()      Página de destino/retorno       |
 * |           getDestino()                                      |
 * | $method : Select()          Seleciona os dados              |
 * | $method : Delete()          Apaga registros                 |
 * | @usage  : $oobFreqANO = new OperaRegistroFRQANO;            |
 * |           $oData->query('SELECT ...');                      |
 * |                                                             |
 * | @dependence : DataBase()    (class_database.php)            |
 * |                                                             |
 * +-------------------------------------------------------------+
 * |   Convenções:                                               |
 * |      [] -> indicam parametros obrigatórios                  |
 * |      <> -> indicam parametros                               |
 * +-------------------------------------------------------------+
 * */
class OperaRegistroFRQANO extends DataBase
{

    public $ano;                // string - ano de competência
    public $mes;                // string - mês de competência
    public $siape;              // string - matrícula siape do servidor
    public $lotacao;            // string - lotação do servidor
    public $destino;            // string - destino em caso de erro
    public $numero_dias_do_mes; // string - número de dias no mês

    ## contructor

    #
    public function __constructor()
    {
        parent::DataBase('PDO'); // instancia do banco de dados
        $this->setMensagem("Falha na exclusão do codigo 199!");

    }

    ## matrícula siape

    #
    function setSiape($valor = '')
    {
        $this->siape = $valor;

    }

    function getSiape()
    {
        return $this->siape;

    }

    ## ano

    #
    function setAno($valor = '')
    {
        $this->ano = $valor;

    }

    function getAno()
    {
        return $this->ano;

    }

    ## mes

    #
    function setMes($valor = '')
    {
        $this->mes = $valor;

    }

    function getMes()
    {
        return $this->mes;

    }

    ## destino em caso de erro

    #
    function setDestino($valor = '')
    {
        $this->destino = $valor;

    }

    function getDestino()
    {
        return $this->destino;

    }

    ## código da lotação do servidor

    #
    function setLotacao($valor = '')
    {
        $this->lotacao = $valor;

    }

    function getLotacao()
    {
        return $this->lotacao;

    }

    ## número de dias no mês

    #
    function setNumeroDiasDoMes($valor = '')
    {
        $this->numero_dias_do_mes = $valor;

    }

    function getNumeroDiasDoMes()
    {
        return $this->numero_dias_do_mes;

    }

    ## Seleciona registro(s)

    #
    function SelectMesSiape()
    {
        $siape = $this->getSiape();
        $ano   = $this->getAno();
        $mes   = $this->getMes();

        $this->setDestino($this->getDestino());
        $this->setMensagem("Pesquisa de registro falhou!");
        $this->query("
            SELECT
                compet
            FROM
                frq$ano
            WHERE
                compet = '$ano$mes'
                AND mat_siape = '$siape'
                AND cod_ocorr != '199'
        ");

    }

    ## Seleciona registro(s)

    #
    function SelectDiasMesSiape()
    {
        $siape              = $this->getSiape();
        $ano                = $this->getAno();
        $mes                = $this->getMes();
        $numero_dias_do_mes = $this->getNumeroDiasDoMes();

        $this->setDestino($this->getDestino());
        $this->setMensagem("Pesquisa de registro falhou!");
        $this->query("
            SELECT
                compet
            FROM
                frq$ano
            WHERE
            compet = '$ano$mes'
                AND mat_siape = '$siape'
                AND cod_ocorr != '199'
                AND dia_ini = '01'
                AND dia_fim = '$numero_dias_do_mes'
        ");

    }

    ## Apaga registro

    #
    function DeleteMesSiape()
    {
        $siape = $this->getSiape();
        $ano   = $this->getAno();
        $mes   = $this->getMes();

        $this->setDestino($this->getDestino());
        $this->setMensagem("Falha na exclusão do codigo 199!");
        $this->query("
            DELETE FROM
                frq$ano
            WHERE
                compet = '$ano$mes'
                AND cod_ocorr = '199'
                AND mat_siape = '$siape'
        ");

    }

    ## Inserir registro

    #
    function InsertMesSiape()
    {
        $siape              = $this->getSiape();
        $ano                = $this->getAno();
        $mes                = $this->getMes();
        $lotacao            = $this->getLotacao();
        $numero_dias_do_mes = $this->getNumeroDiasDoMes();

        $this->setDestino($this->getDestino());
        $this->setMensagem("Falha no registro da frequência!");
        $this->query("
            INSERT INTO frq$ano
                (compet, mat_siape, dia_ini, dia_fim, cod_ocorr, cod_lot)
            VALUES
                ('$ano$mes','$siape','01','$numero_dias_do_mes','000','$lotacao')
        ");

    }

}
