<?php

/*
 * ****************************************************************
 *                                                                *
 *  Cria tabelas necessárias para o novo ano:                     *
 *       feriados_<ano>                                           *
 *                                                                *
 * ****************************************************************
 */

// conexão BD
include_once "class_conexao_bd.php";

// Define limite duração do processo
set_time_limit(108000);

/**  @Class
 * +--------------------------------------------------------------------+
 * | @Class    : CriaTabelasFrequenciaBasica                            |
 * | @description : Verifica se existem tabelas necessárias para ano    |
 * |                corrente                                            |
 * |                                                                    |
 * | Autor     : Edinalvo Rosa                                          |
 * +--------------------------------------------------------------------+
 * */
class CriaTabelasFrequenciaBasica extends ConexaoBD
{

    private $mes;
    private $ano;

    public function __construct()
    {
        // Conexão com o banco de dados SISREF
        $this->conexao = new ConexaoBD();
        $this->mes     = date('m');
        $this->ano     = date('Y');

    }

    /*
     *  SETTER's
     */
    public function setMes($value='')
    {
        $this->mes = (empty($value) ? date('m') : substr('00'.$mes,-2) );
    }

    public function setAno($value='')
    {
        $this->ano = (empty($value) ? date('Y') : substr('0000'.$ano,-4) );
    }

    /*
     *  GETTER's
     */
    public function getMes()
    {
        return $this->mes;
    }

    public function getAno()
    {
        return $this->ano;
    }


    /*
     *  FERIADOS_<ANO>
     *  Datas de feriados nacionais, estaduais e municipais
     */
    public function Feriados()
    {
        $sql = "CREATE TABLE IF NOT EXISTS feriados_" . $this->ano . " LIKE feriados_2018 ";
        $this->conexao->linkSISREF->query( $sql );
    }

    /*
     *  FRQ_<ANO>
     *  Agrupa ocorrências iguais, registradas em datas seguidas
     *  Ex.: 00172 de 01 a 05/10/2009
     *       33333 de 06 a 06/10/2009
     *       00169 de 07 a 30/10/2009
     */
    public function OcorrenciasAgrupadas()
    {
        $sql = "CREATE TABLE IF NOT EXISTS frq" . $this->ano . " LIKE frq2018 ";
        $this->conexao->linkSISREF->query( $sql );
    }

    /*
     *  Frequencia
     *  Tabelas para registro das frequências dos servidores/estagiários
     */
    public function Frequencia()
    {
        for ($x = 1; $x <= 12; $x++)
        {
            $mes = substr("00" . $x, 2);

            // PONTO<MESANO>
            $sql = "CREATE TABLE IF NOT EXISTS ponto" . $mes . $this->ano . " LIKE ponto122018 ";
            $this->conexao->linkSISREF->query($sql);

            // PONTO<MESANO>_auxiliar
            $sql = "CREATE TABLE IF NOT EXISTS ponto" . $mes . $this->ano . "_auxiliar LIKE ponto122018_auxiliar ";
            $this->conexao->linkSISREF->query($sql);

            // HISTPONTO<MESANO>
            $sql = "CREATE TABLE IF NOT EXISTS histponto" . $mes . $this->ano . " LIKE histponto122018 ";
            $this->conexao->linkSISREF->query($sql);

            // HISTPONTO<MESANO>
            $sql = "CREATE TABLE IF NOT EXISTS histponto" . $mes . $this->ano . "_auxiliar LIKE histponto122018_auxiliar ";
            $this->conexao->linkSISREF->query($sql);
        }
    }

}

/*
 * Cria tabelas
 */
$TabelasFrequencia = new CriaTabelasFrequenciaBasica();
$TabelasFrequencia->Feriados();
$TabelasFrequencia->OcorrenciasAgrupadas();
$TabelasFrequencia->Frequencia();

exit();
