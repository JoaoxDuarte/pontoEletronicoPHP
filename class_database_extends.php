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
 * | $method : setSiape()        Matr�cula siape do servidor     |
 * |           getSiape()                                        |
 * | $method : setMes()          M�s de compet�ncia              |
 * |           getMes()                                          |
 * | $method : setAno()          Ano de compet�ncia              |
 * |           getAno()                                          |
 * | $method : setDestino()      P�gina de destino/retorno       |
 * |           getDestino()                                      |
 * | $method : Select()          Seleciona os dados              |
 * | $method : Delete()          Apaga registros                 |
 * | @usage  : $oobFreqANO = new OperaRegistroFRQANO;            |
 * |           $oData->query('SELECT ...');                      |
 * |                                                             |
 * | @dependence : DataBase()    (class_database.php)            |
 * |                                                             |
 * +-------------------------------------------------------------+
 * |   Conven��es:                                               |
 * |      [] -> indicam parametros obrigat�rios                  |
 * |      <> -> indicam parametros                               |
 * +-------------------------------------------------------------+
 * */
class OperaRegistroFRQANO extends DataBase
{

    public $ano;                // string - ano de compet�ncia
    public $mes;                // string - m�s de compet�ncia
    public $siape;              // string - matr�cula siape do servidor
    public $lotacao;            // string - lota��o do servidor
    public $destino;            // string - destino em caso de erro
    public $numero_dias_do_mes; // string - n�mero de dias no m�s

    ## contructor

    #
    public function __constructor()
    {
        parent::DataBase('PDO'); // instancia do banco de dados
        $this->setMensagem("Falha na exclus�o do codigo 199!");

    }

    ## matr�cula siape

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

    ## c�digo da lota��o do servidor

    #
    function setLotacao($valor = '')
    {
        $this->lotacao = $valor;

    }

    function getLotacao()
    {
        return $this->lotacao;

    }

    ## n�mero de dias no m�s

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
        $this->setMensagem("Falha na exclus�o do codigo 199!");
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
        $this->setMensagem("Falha no registro da frequ�ncia!");
        $this->query("
            INSERT INTO frq$ano
                (compet, mat_siape, dia_ini, dia_fim, cod_ocorr, cod_lot)
            VALUES
                ('$ano$mes','$siape','01','$numero_dias_do_mes','000','$lotacao')
        ");

    }

}
