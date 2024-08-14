<?php

// Inicia a sessão e carrega as funções de uso geral
include_once( "config.php" );
include_once( "class_ocorrencias_grupos.php" );
include_once( "src/controllers/DadosServidoresController.php" );
include_once( "src/controllers/TabBancoDeHorasCiclosController.php" );
include_once( "src/controllers/TabPontoAuxiliarController.php" );


/**
 * @class TabBancoDeHorasAcumulosModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : acumulos_horas
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - src/models
 * @Arquivo   - TabBancoDeHorasAcumulosModel.php
 *
 * @author Edinalvo Rosa
 */
class TabBancoDeHorasAcumulosModel
{
    /*
    * Atributos
    */
    /* @var OBJECT */ public $conexao = NULL;
    /* @var OBJECT */ public $objOcorrenciasGrupos = null;
    /* @var OBJECT */ public $objTabBancoDeHorasCiclosController = null;
    /* @var OBJECT */ public $objTabPontoAuxiliarController = null;
    /* @var OBJECT */ public $objDadosServidoresController = null;

    ## @constructor
    #+-----------------------------------------------------------------+
    # Construtor da classe
    #+-----------------------------------------------------------------+
    #
    function __construct()
    {
        # Faz conexão
        $this->conexao = new DataBase();

        // INSTANCIA GRUPOS DE OCORRENCIAS
        $this->objOcorrenciasGrupos               = new OcorrenciasGrupos();
        $this->objTabBancoDeHorasCiclosController = new TabBancoDeHorasCiclosController();
        $this->objTabPontoAuxiliarController      = new TabPontoAuxiliarController();
        $this->objDadosServidoresController       = new DadosServidoresController();
    }
    

    /**
     * @info Exibe quadro de saldo
     *
     * @param string      $mat
     * @param integer     $ciclo_id
     * @param date/string $dia
     * @return array (assoc)
     */
    public function SaldosBancoDeHorasCicloYear($mat = null, $ciclo_id = null, $anoparam = null, $so_ano_corrente = true)
    {
        if (is_null($mat) || (is_string($mat) && empty($mat)))
        {
            $mat = $_SESSION['sMatricula'];
        }

        // dados
        $mat         = getNovaMatriculaBySiape($mat);
        $where_ciclo = "";

        $paramns = array();
        $paramns[] = array( ":siape", $mat, PDO::PARAM_STR);

        if (!is_null($ciclo_id) || (!is_string($ciclo_id) && !empty($ciclo_id)))
        {
            $where_ciclo = " AND acumulos_horas.ciclo_id = :ciclo_id ";
            $paramns[]   = array(":ciclo_id", $ciclo_id, PDO::PARAM_STR);
        }
        else if (is_null($anoparam))
        {
            $ano = array();
            
            for ($x=date('Y'); $x >= 2009; $x--)
            {
                $ano[] = $x;
            };
        }
        else if (!is_null($anoparam))
        {
            $ano = array($anoparam);
        }

        //VALIDA SE AS DATAS DA AUTORIZAÇÃO ESTÃO DENTRO DO CICLO
        $resultado = array();
        
        foreach($ano AS $exercicio)
        {
            $where_ciclo  = " AND YEAR(ciclos.data_inicio) = :ano ";
            $where_ciclo .= " AND YEAR(autorizacoes_servidores.data_inicio) = :ano ";
            $paramns[]   = array(":ano", $exercicio, PDO::PARAM_STR);

            $query = "
            SELECT 
                DATE_FORMAT(ciclos.data_inicio,'%d/%m/%Y') AS data_inicio,
                DATE_FORMAT(ciclos.data_fim,'%d/%m/%Y')    AS data_fim,
                acumulos_horas.ciclo_id,
                SUM(acumulos_horas.horas)  AS horas,
                SUM(acumulos_horas.usufruto) AS usufruto,
                SUM(acumulos_horas.horas - acumulos_horas.usufruto) AS saldo
            FROM
                acumulos_horas
            LEFT JOIN
                autorizacoes_servidores ON acumulos_horas.siape = autorizacoes_servidores.siape
            LEFT JOIN
                ciclos ON acumulos_horas.ciclo_id = ciclos.id
            WHERE
		acumulos_horas.siape = :siape
		AND NOT ISNULL(autorizacoes_servidores.siape)
		AND ciclos.orgao = LEFT(:siape,5)
                " . $where_ciclo . "
            ORDER BY
                ciclos.data_inicio DESC
            ";

            // EXECUTA A QUERY
            $this->conexao->query($query, $paramns);
            
            if ($this->conexao->num_rows() > 0)
            {
                $resultado[] = $this->conexao->fetch_assoc();
            }
            
            if ($so_ano_corrente === true && (is_null($anoparam) || $anoparam == date('Y')))
            {
                break;
            }
        }

        return $resultado;
    }


    /**
     * @info Exibe quadro de saldo
     *
     * @param string      $mat
     * @param integer     $ciclo_id
     * @param date/string $dia
     * @return array (assoc)
     */
    public function DadosAcumuloHoras($mat = null, $ciclo_id = null, $dia = null)
    {
        if (is_null($mat) || (is_string($mat) && empty($mat)))
        {
            $mat = $_SESSION['sMatricula'];
        }

        // dados
        $mat     = getNovaMatriculaBySiape($mat);
        $paramns = array();

        if (!is_null($ciclo_id) || (!is_string($ciclo_id) && !empty($ciclo_id)))
        {
            $where_ciclo = " AND acumulos_horas.ciclo_id = :ciclo_id ";
            $paramns[]   = array(":ciclo_id", $ciclo_id, PDO::PARAM_STR);
        }

        if (is_null($dia) || !validaData($dia))
        {
            $data = date('Y-m-d');
        }
        else
        {
            $data = conv_data($dia);
        }

        //VALIDA SE AS DATAS DA AUTORIZAÇÃO ESTÃO DENTRO DO CICLO
        $query = "
            SELECT
                DATE_FORMAT(ciclos.data_fim, '%d/%m/%Y')    AS data_fim ,
                DATE_FORMAT(ciclos.data_inicio, '%d/%m/%Y') AS data_inicio,
                SUM(acumulos_horas.horas)                   AS horas,
                SUM(acumulos_horas.usufruto)                AS usufruto,
                SUM(acumulos_horas.horas - acumulos_horas.usufruto) AS saldo
            FROM
                acumulos_horas
            LEFT JOIN
                autorizacoes_servidores ON acumulos_horas.siape = autorizacoes_servidores.siape
            LEFT JOIN
                ciclos ON autorizacoes_servidores.ciclo_id = ciclos.id AND ciclos.orgao = LEFT(autorizacoes_servidores.siape,5)
            WHERE
		acumulos_horas.siape = :siape
		AND NOT ISNULL(autorizacoes_servidores.siape)
                " . $where_ciclo . "
		AND NOT ISNULL(ciclos.id)
                AND :dia BETWEEN autorizacoes_servidores.data_inicio
				AND autorizacoes_servidores.data_fim
        ";

        $paramns[] = array( ":siape", $mat,  PDO::PARAM_STR);
        $paramns[] = array( ":dia",   $data, PDO::PARAM_STR);

        // EXECUTA A QUERY
        $this->conexao->query($query, $paramns);

        return $this->conexao->fetch_assoc();
    }


    /**
     * @info Verifica se ocorrencia usofruto
     *
     *
     * @param object $dados
     * @return \stdClass|object
     */
    public function verificaSeOcorrenciaTipoUsufrutoBancoDeHoras( $dados = null )
    {
        $this->verificaSeOcorrenciaOrigemTipoUsufruto( $dados );
        return $this->verificaSeOcorrenciaDestinoTipoUsufruto( $dados );
    }


    /**
     * @info Verifica se ocorrencia origem é de usofruto
     *
     * @param object $dados
     * @return void|boolean
     */
    public function verificaSeOcorrenciaOrigemTipoUsufruto( $dados = null )
    {
        if (is_object($dados))
        {
            $mat        = getNovaMatriculaBySiape($dados->siape);
            $arquivo    = nomeTabelaFrequencia($dados->grupo, dataMes($dados->dia). dataAno($dados->dia));
            $sitcad     = $this->objDadosServidoresController->getSigRegJur($mat);
            $ocorrencia = $dados->ocor_origem;

            $codigoUsofrutoBancoDeHoras = $this->objOcorrenciasGrupos->CodigoBancoDeHorasDebitoPadrao( $sitcad );

            if ( !in_array($ocorrencia, $codigoUsofrutoBancoDeHoras) || $dados->grupo == 'historico_manutencao')
            {
                return false;
            }

            //Ciclo vigente do servidor
            $ciclo_id = $this->objTabBancoDeHorasCiclosController->getCicloBySiapeUsufruto( $mat, $dados->dia )['id'];

            // JORNADA SERVIDOR
            $jornadaSemanal = getJornadaServer( $mat ); // JORNADA SERVIDOR
            $jornadaDiaria  = formata_jornada_para_hhmm( $jornadaSemanal ); // CARGA DIÁRIA SERVIDOR

            // RECUPERA O REGISTRO DIÁRIO DO SERVIDOR
            $this->conexao->query("SELECT jornd FROM $arquivo WHERE dia = :dia AND siape = :siape", array(
                array(":dia",   conv_data($dados->dia), PDO::PARAM_STR),
                array(":siape", $mat,                   PDO::PARAM_STR))
            );

            $jornadaRealizada = $this->conexao->fetch_assoc()['jornd'];

            // RECUPERA O SALDO ATUAL DE USUFRUTO, E JÁ CALCULA O NOVO VALOR A SER SALVO
            $dadoacumulo = $this->selectAcumuloBancoDeHorasPorSiapeCiclo( $mat, $ciclo_id );

            $saldoAtualUsufruto      = intval($dadoacumulo['usufruto']);
            $result                  = parseHoursInSeconds($jornadaDiaria) - parseHoursInSeconds($jornadaRealizada);
            $saldoAtualizadoUsufruto = $saldoAtualUsufruto - $result;

            // DEVOLVE O SALDO DE USUFRUTO
            $this->updateAcumuloBancoDeHorasUsufruto( $mat, $ciclo_id, $saldoAtualizadoUsufruto );

            // ATUALIZA O HISTÓRICO DE MOVIMENTAÇÃO
            saveHistoricalOvertime( $mat, $ciclo_id, 0, -$result, $dados->dia);

            return true;
        }

        return false;
    }


    /**
     * @info Verifica se ocorrencia destino é de usofruto
     *
     * @param object $dados
     * @return \stdClass|object
     */
    public function verificaSeOcorrenciaDestinoTipoUsufruto( $dados = null )
    {
        $obj = new stdClass();
        $obj->notsave    = false;
        $obj->boolweek   = false;
        $obj->boolmonth  = false;
        $obj->diautil    = true;
        $obj->mensagem   = "";

        if (is_object($dados))
        {
            $mat        = getNovaMatriculaBySiape($dados->siape);
            $arquivo    = nomeTabelaFrequencia($dados->grupo, dataMes($dados->dia). dataAno($dados->dia));
            $sitcad     = $this->objDadosServidoresController->getSigRegJur($mat);
            $ocorrencia = $dados->ocor_destino;

            $codigoUsofrutoBancoDeHoras = $this->objOcorrenciasGrupos->CodigoBancoDeHorasDebitoPadrao( $sitcad );

            if ( !in_array($ocorrencia, $codigoUsofrutoBancoDeHoras) || $dados->grupo == 'historico_manutencao')
            {
                return $obj;
            }

            $obj->notsave = true;

            //Ciclo vigente do servidor
            $ciclo    = $this->objTabBancoDeHorasCiclosController->getCicloBySiapeUsufruto($mat);
            $ciclo_id = $ciclo['id'];

            // JORNADA SERVIDOR
            $jornadaSemanal = getJornadaServer($mat); // JORNADA SERVIDOR
            $jornadaDiaria  = formata_jornada_para_hhmm($jornadaSemanal); // CARGA DIÁRIA SERVIDOR

            // RECUPERA O REGISTRO DIÁRIO DO SERVIDOR
            $this->conexao->query("SELECT * FROM $arquivo WHERE dia = :dia AND siape = :siape", array(
                array(":dia",   conv_data($dados->dia), PDO::PARAM_STR),
                array(":siape", $mat,                   PDO::PARAM_STR)
            ));

            $jornadaRealizada = $this->conexao->fetch_assoc()['jornd'];

            // RECUPERA O SALDO ATUAL DE USUFRUTO
            $dadoacumulo = $this->selectAcumuloBancoDeHorasPorSiapeCiclo( $mat, $ciclo_id );

            $saldoDoServidor    = intval($dadoacumulo['saldo']);
            $saldoAtualUsufruto = intval($dadoacumulo['usufruto']);

            $result             = parseHoursInSeconds($jornadaDiaria) - parseHoursInSeconds($jornadaRealizada);

            $obj->boolweek      = $this->validateBalanceWeek($mat , $result, $dados->dia);
            $obj->boolmonth     = $this->validateBalanceMonth($mat , $result, $dados->dia);
            $obj->diautil       = (verifica_se_dia_nao_util($dados->dia, $dados->lot) == false);

            if (!$obj->boolweek)
            {
                $obj->mensagem = "Valor ultrapassa o limite de 24h permitidas de usufruto dentro da semana!";
            }

            if (!$obj->boolmonth)
            {
                $obj->mensagem = "Valor ultrapassa o limite de 40h permitidas de usufruto dentro do mês!";
            }

            if (!$obj->diautil)
            {
                $obj->mensagem = "Não é permitido lançar a ocorrência ".$ocorrencia.", em dia não útil!";
            }

            if ($obj->boolweek && $obj->boolmonth && $obj->diautil)
            {
                if ($saldoDoServidor >= $result)
                {
                    // CALCULA O NOVO VALOR A SER SALVO
                    $saldoAtualizadoUsufruto = $saldoAtualUsufruto + $result;

                    // DEVOLVE O SALDO DE USUFRUTO
                    $this->updateAcumuloBancoDeHorasUsufruto( $mat, $ciclo_id, $saldoAtualizadoUsufruto );

                    // ATUALIZA O HISTÓRICO DE MOVIMENTAÇÃO
                    saveHistoricalOvertime($mat, $ciclo_id, 0, $result, $dados->dia);
                    $obj->notsave = false;
                }
                else if (($saldoDoServidor > 0) && ($saldoDoServidor < $result))
                {
                    // CALCULA O NOVO VALOR A SER SALVO
                    $saldoAtualizadoUsufruto = $saldoAtualUsufruto + ($saldoDoServidor - $result);

                    // DEVOLVE O SALDO DE USUFRUTO
                    $this->updateAcumuloBancoDeHorasUsufruto( $mat, $ciclo_id, $saldoAtualizadoUsufruto );

                    // ATUALIZA O HISTÓRICO DE MOVIMENTAÇÃO
                    saveHistoricalOvertime($mat, $ciclo_id, 0, $result, $dados->dia);
                    $obj->notsave = false;
                }
                else
                {
                    $obj->notsave = false;
                    $obj->mensagem = "O servidor não possui horas suficientes acumuladas em Banco de Horas para utilizar!";
                }
            }
            else if ($obj->diautil)
            {
                $obj->notsave = false;
                $obj->mensagem = "O servidor não possui horas suficientes acumuladas em Banco de Horas para utilizar!";
            }
        }

        return $obj;
    }


    /**
     * @info Verifica condições para usufruto Banco de Horas
     *
     * @param object  $dados
     *          $dados->siape            // string  : matrícula do servidor
     *          $dados->dia              // string  : data da ocorrência
     *          $dados->grupo            // string  : grupo/módulo (acompanhar,hsitórico,etc)
     *          $dados->tipoUsufruto     // string  : tipo do usufruto (parcial,total)
     *          $dados->ocorrencia       // string  : código de ocorrência
     *          $dados->debitoPadrao     // string  : código débito padrão
     *          $dados->diferenca        // string  : diferença no dia
     *          $dados->jornp            // string  : jornada prevista no dia
     *          $dados->jornd            // string  : jornada realizada no dia
     *          $dados->jornadaPrevista  // string  : jornada prevista (cadastro/jornada histórico)
     *          $dados->jornadaRealizada // string  : jornada realizada (cadastro/jornada histórico)
     *          $dados->idreg            // string  : indica quem registrou (S)servidor, (C)hefia, etc
     *          $dados->registro_ip      // string  : IP da máquina
     *          $dados->registro_siape   // string  : Matrícula do operador
     * @return object
     *          $retorno["ocorrencia"]       // string  : ocorrencia destinação
     *          $retorno["horasNegativas"]   // boolean : Horas negativas (true)
     *          $retorno["mensagemNegativa"] // astring : mensagem, se hora negativa
     *          $retorno["diferenca"]        // diferença restante após usufruto Banco de Horas
     *          $retorno["bool"]             // Resultado das validações do limite semanal e mensal
     *          $retorno["horasUsadaUsufruto"] // Horas utilizadas do Banco de Horas
     *
     * @param object $dados
     * @return \stdClass|object
     */
    public function verificaCondicoesUsufrutoBancoDeHoras( $dados )
    {
        // dados
        $dados->siape        = getNovaMatriculaBySiape($dados->siape);
        $dados->tipoUsufruto = (is_null($dados->tipoUsufruto) || empty($dados->tipoUsufruto) ? "total" : $dados->tipoUsufruto);

        // resultado - retorno
        $retorno = array();
        $retorno["ocorrencia"]         = null;
        $retorno["horasNegativas"]     = false;
        $retorno["mensagemNegativa"]   = "";
        $retorno["diferenca"]          = null;
        $retorno["bool"]               = "";
        $retorno["horasUsadaUsufruto"] = 0;

        // códigos de ocorrência
        $sitcad                       = $this->objDadosServidoresController->getSigRegJur( $dados->siape );
        $codigoDebitoPadrao           = $this->objOcorrenciasGrupos->CodigoDebitoPadrao( $sitcad );
        $codigoFrequenciaNormalPadrao = $this->objOcorrenciasGrupos->CodigoFrequenciaNormalPadrao( $sitcad );

        $bool = $this->verifyExistsAutorization($dados->siape, $dados->tipoUsufruto, $dados->dia);

        // VALIDAÇÃO SEMANA
        if($bool === true && $dados->ocorrencia == $dados->debitoPadrao)
        {
            $bool = $this->validateBalanceWeek($dados->siape , parseHoursInSeconds($dados->diferenca), $dados->dia);
            $retorno["horasNegativas"]   = true;
            $retorno["mensagemNegativa"] = "Horas superam limite MÁXIMO de usufruto de Banco de Horas por SEMANA.";
        }

        // VALIDAÇÃO MÊS
        if($bool === true && $dados->ocorrencia == $dados->debitoPadrao)
        {
            $bool = $this->validateBalanceMonth($dados->siape , parseHoursInSeconds($dados->diferenca));
            $retorno["horasNegativas"]   = true;
            $retorno["mensagemNegativa"] = "Horas extrapolam limite MÁXIMO de usufruto de Banco de Horas por MÊS.";
        }

        if ($bool === true && $dados->ocorrencia == $dados->debitoPadrao)
        {
            $timejornadaPrevista = parseHoursInSeconds( $dados->jornadaPrevista );
            $timejornadaFeita    = parseHoursInSeconds( $dados->jornadaRealizada );

            // CASO A JORNADA DIÁRIA SEJA MENOR QUE A PREVISTA
            if ($timejornadaPrevista > $timejornadaFeita)
            {
                $ciclo_id = $this->objTabBancoDeHorasCiclosController->getCicloBySiapeUsufruto($dados->siape);

                // RECUPERA O SALDO DE HORAS e
                // SALDO ATUAL DE USUFRUTO DO SERVIDOR
                $dadoacumulo = $this->selectAcumuloBancoDeHorasPorSiapeCiclo( $dados->siape, $ciclo_id['id'] );

                // CALCULA O NOVO VALOR A SER SALVO
                $saldoDoServidor         = intval($dadoacumulo['saldo']);
                $saldoAtualUsufruto      = intval($dadoacumulo['usufruto']);
                $saldoAtualizadoUsufruto = $saldoAtualUsufruto + parseHoursInSeconds($dados->diferenca);

                // CASO O SALDO DE HORAS NO BANCO SEJA MAIOR OU IGUAL A NECESSIDADE
                if ($saldoDoServidor >= parseHoursInSeconds($dados->diferenca))
                {
                    // ATUALIZA A TABELA DE BANCO DE HORAS
                    $this->updateAcumuloBancoDeHorasUsufruto( $dados->siape, $ciclo_id['id'], $saldoAtualizadoUsufruto );

                    // SALVA A MOVIMENTAÇÃO
                    saveHistoricalOvertime($dados->siape , $ciclo_id['id'], 0, parseHoursInSeconds($dados->diferenca));

                    $paramsPonto = array(
                        'dia'            => $dados->dia,
                        'matricula'      => $dados->siape,
                        'hora_ini'       => '00:00',
                        'hora_fim'       => '00:00',
                        'tempo_consulta' => $dados->diferenca,
                        'deslocamento'   => 0,
                        'setor'          => $setor,
                        'idreg'          => $dados->idreg,
                        'oco'            => $dados->ocorrencia,
                        'registro_ip'    => $dados->registro_ip,
                        'registro_siape' => $dados->registro_siape,
                    );

                    $this->objTabPontoAuxiliarController->incluirPontoAuxiliarBancoDeHoras( $paramsPonto, $dados->diferenca );

                    $retorno["ocorrencia"]         = $codigoFrequenciaNormalPadrao[0]; //$codigoDebitoPadrao[0]; //$dados->debitoPadrao;
                    $retorno["horasNegativas"]     = true;
                    $retorno["mensagemNegativa"]   = "As horas negativas do dia foram deduzidas do Banco de Horas visto estar autorizado o usufruto";
                    $retorno["diferenca"]          = "00:00";
                    $retorno["bool"]               = "";
                    $retorno["horasUsadaUsufruto"] = (is_integer($dados->diferenca) ? $dados->diferenca : time_to_sec($dados->diferenca));
                }

                // CASO O SALDO DE HORAS NO BANCO SEJA MENOR QUE A NECESSIDADE
                else if (($saldoDoServidor > 0) && ($saldoDoServidor < parseHoursInSeconds($dados->diferenca)))
                {
                    $diferenca = parseHoursInSeconds($dados->diferenca) - $saldoDoServidor;

                    // ATUALIZA A TABELA DE BANCO DE HORAS - DEDUZ O SALDO ATUAL, ZERANDO USUFRUTO
                    $this->updateAcumuloBancoDeHorasUsufruto( $dados->siape, $ciclo_id['id'], $saldoDoServidor );

                    // SALVA A MOVIMENTAÇÃO
                    saveHistoricalOvertime($dados->siape , $ciclo_id['id'], 0, $saldoDoServidor);

                    $paramsPonto = array(
                        'dia'            => $dados->dia,
                        'matricula'      => $dados->siape,
                        'hora_ini'       => '00:00',
                        'hora_fim'       => '00:00',
                        'tempo_consulta' => sec_to_time($saldoDoServidor, 'hh:mm'),
                        'deslocamento'   => 0,
                        'setor'          => $setor,
                        'idreg'          => $dados->idreg,
                        'oco'            => $dados->ocorrencia,
                        'registro_ip'    => $dados->registro_ip,
                        'registro_siape' => $dados->registro_siape,
                    );

                    $this->objTabPontoAuxiliarController->incluirPontoAuxiliarBancoDeHoras( $paramsPonto, $saldoDoServidor );

                    $retorno["ocorrencia"]         = $codigoDebitoPadrao[0]; //$dados->debitoPadrao;
                    $retorno["horasNegativas"]     = true;
                    $retorno["mensagemNegativa"]   = "Parte das horas negativas do dia foram deduzidas do Banco de Horas visto estar autorizado o usufruto";
                    $retorno["diferenca"]          = $diferenca;
                    $retorno["bool"]               = "";
                    $retorno["horasUsadaUsufruto"] = (is_integer($saldoDoServidor) ? $saldoDoServidor : time_to_sec($saldoDoServidor));
                }
            }
        }

        return (object) $retorno;
    }


    /**
     * @param string $matricula
     * @param string $tipoUsufruto
     * @param date/string $dia
     *
     * @return bool
     */
    public function verifyExistsAutorization($matricula, $tipoUsufruto = 'total', $dia = null)
    {
        $matricula = getNovaMatriculaBySiape($matricula);
        $data      = (is_null($dia) || empty($dia) || !validaData($dia) ? date('Y-m-d') : conv_data($dia));

        $query = "
            SELECT * FROM autorizacoes_servidores_usufruto
                WHERE autorizacoes_servidores_usufruto.siape = :siape
                      AND autorizacoes_servidores_usufruto.tipo_autorizacao = :tipo
                      AND :dia BETWEEN autorizacoes_servidores_usufruto.data_inicio
                                   AND autorizacoes_servidores_usufruto.data_fim
        ";

        $this->conexao->query($query, array(
            array(":siape", $matricula,    PDO::PARAM_STR),
            array(":tipo",  $tipoUsufruto, PDO::PARAM_STR),
            array(":dia",   $data,         PDO::PARAM_STR)
        ));
        
        $retorno = ($this->conexao->num_rows() > 0 ? true : false);
        
        return $retorno;
    }


    /**
     * @info Validação de usufruto dentro do mês corrente
     *
     * @param type $matricula
     * @param type $diferenca
     * @return boolean|bool
     */
    public function validateBalanceMonth($matricula = null, $diferenca = null)
    {
        if (is_null($matricula) || is_null($diferenca))
        {
            return false;
        }

        $matricula = getNovaMatriculaBySiape($matricula);

        $diasmes = getMonthDates();
        $uteis   = [];

        foreach ($diasmes as $index => $date)
        {
            if (!verifica_se_dia_nao_util($date, $_SESSION['sLotacao']))
            {
                array_push($uteis, $date);
            }
        }

        $limite_usufruto_mensal_banco_de_horas = grupoOcorrencias('limite_usufruto_mensal_banco_de_horas');
        $limite_banco_de_horas_usufruto_mensal = time_to_sec($limite_usufruto_mensal_banco_de_horas['limite_usufruto_mensal_banco_de_horas']['horario']);

        $saldo_usufruto = $this->getUsufrutoPorSiapePeriodo( $matricula, $uteis[0], end($uteis) );

        if (($saldo_usufruto + $diferenca) > $limite_banco_de_horas_usufruto_mensal)
        {
            return false;
        }

        return true;
    }


    /**
     * @info Validação de usufruto dentro da semana corrente
     *
     * @param type $matricula
     * @param type $diferenca
     * @return boolean|bool
     */
    public function validateBalanceWeek($matricula = null, $diferenca = null, $dia = null)
    {
        if (is_null($matricula) || is_null($diferenca))
        {
            return false;
        }

        $matricula = getNovaMatriculaBySiape($matricula);

        $diassemana = getWeekDates( $dia );
        $uteis = [];

        foreach ($diassemana as $index => $date)
        {
            if ( !verifica_se_dia_nao_util($date, $_SESSION['sLotacao']) )
            {
                array_push($uteis, $date);
            }
        }

        $limite_usufruto_semanal_banco_de_horas = grupoOcorrencias('limite_usufruto_semanal_banco_de_horas');
        $limite_banco_de_horas_usufruto_semanal = time_to_sec($limite_usufruto_semanal_banco_de_horas['limite_usufruto_semanal_banco_de_horas']['horario']);

        $saldo_usufruto = $this->getUsufrutoPorSiapePeriodo( $matricula, $uteis[0], end($uteis) );

        if (($saldo_usufruto + $diferenca) > $limite_banco_de_horas_usufruto_semanal)
        {
            return false;
        }

        return true;
    }


    /**
     * @info Seleciona dados de acumulo de banco de horas
     *
     * @param string      $mat Matrícula SIAPE
     * @param string/date $dia Data da ocorrência
     * @return false/array
     */
    public function selectAcumuloBancoDeHorasPorSiapeDia( $mat=null, $dia=null )
    {
        if (is_null($mat) || is_null($dia))
        {
            return false;
        }

        $mat = getNovaMatriculaBySiape($mat);

        $this->conexao->query("
            SELECT
                IFNULL(SUM(acumulos_horas.horas)
                    - SUM(acumulos_horas.usufruto),0)  AS saldo,
                IFNULL(SUM(acumulos_horas.horas),0)    AS horas,
                IFNULL(SUM(acumulos_horas.usufruto),0) AS usufruto
            FROM
                acumulos_horas
            LEFT JOIN autorizacoes_servidores_usufruto ON acumulos_horas.id = autorizacoes_servidores_usufruto.siape
            LEFT JOIN ciclos                           ON autorizacoes_servidores_usufruto.ciclo_id = ciclos.id
            WHERE
                acumulos_horas.siape = :siape
                AND (:dia BETWEEN autorizacoes_servidores_usufruto.data_inicio
                              AND autorizacoes_servidores_usufruto.data_fim)
        ",
        array(
            array(":siape", $mat,            PDO::PARAM_STR),
            array(":dia",   conv_data($dia), PDO::PARAM_INT)
        ));

        return $this->conexao->fetch_assoc();
    }


    /**
     * @info Seleciona dados de acumulo de banco de horas
     *
     * @param string  $mat Matrícula SIAPE
     * @param integer $ciclo_id ID do ciclo de acumulo/usufruto
     * @return false/array  ('usufruto','saldo')
     */
    public function selectAcumuloBancoDeHorasPorSiapeCiclo( $mat, $ciclo_id )
    {
        if (is_null($mat) || is_null($ciclo_id))
        {
            return false;
        }

        $mat = getNovaMatriculaBySiape($mat);

        $this->conexao->query("
            SELECT
                IFNULL(SUM(acumulos_horas.horas)
                    - SUM(acumulos_horas.usufruto),0)  AS saldo,
                IFNULL(SUM(acumulos_horas.horas),0)    AS horas,
                IFNULL(SUM(acumulos_horas.usufruto),0) AS usufruto
            FROM
                acumulos_horas
            WHERE
                siape = :siape
                AND ciclo_id = :ciclo_id
        ",
        array(
            array(":siape",    $mat,      PDO::PARAM_STR),
            array(":ciclo_id", $ciclo_id, PDO::PARAM_INT)
        ));

        return $this->conexao->fetch_assoc();
    }


    /**
     * @info Atualiza os saldos de acumulo de banco de horas
     *
     * @param string  $mat                      Matrícula SIAPE
     * @param integer $ciclo_id                 ID do ciclo de acumulo/usufruto
     * @param integer $saldoAtualizadoUsufruto  VAlor para atualizar banco de horas
     * @return integer/false
     */
    public function updateAcumuloBancoDeHorasUsufruto( $mat = null, $ciclo_id = null , $saldoAtualizadoUsufruto = 0 )
    {
        if (is_null($mat) || is_null($ciclo_id))
        {
            return false;
        }

        $mat = getNovaMatriculaBySiape($mat);

        $this->conexao->query("
            UPDATE
                acumulos_horas
            SET
                usufruto = :usufruto
            WHERE
                siape = :siape
                AND ciclo_id = :ciclo_id
        ",
        array(
            array(":siape",    $mat,                     PDO::PARAM_STR),
            array(":usufruto", $saldoAtualizadoUsufruto, PDO::PARAM_INT),
            array(":ciclo_id", $ciclo_id,                PDO::PARAM_INT)
        ));

        return $this->conexao->affected_rows();
    }


    /**
     * @info Atualiza os saldos de acumulo de banco de horas
     * 
     * @param string  $mat               Matrícula SIAPE
     * @param integer $ciclo_id          ID do ciclo de acumulo/usufruto
     * @param string  $tipo_solicitacao  Tipo da autorização usufruto (parcial/total)
     * @param date    $dateiniformated   Data de início
     * @param date    $datefimformated   Data de término
     * @return boolean
     */
    public function insertAutorizacoesServidoresUsufruto( $mat=null, $ciclo_id=null, $tipo_solicitacao=null, $dateiniformated=null, $datefimformated=null )
    {
        if (is_null($mat) || is_null($ciclo_id))
        {
            return false;
        }

        $siape = getNovaMatriculaBySiape($mat);

        $this->conexao->setMensagem("Problemas no acesso a Tabela AUTORIZACOES USUFRUTO (Parcial) (E200046.".__LINE__.").");
        $this->conexao->query("
            INSERT INTO
                autorizacoes_servidores_usufruto
                (siape, ciclo_id, data_inicio, data_fim, tipo_autorizacao)
                VALUES
                (:siape, :ciclo_id, :data_inicio, :data_fim, :tipo_autorizacao)
        ", array(
            array(":siape",            $siape,            PDO::PARAM_STR),
            array(":ciclo_id",         $ciclo_id,         PDO::PARAM_INT),
            array(":tipo_autorizacao", $tipo_solicitacao, PDO::PARAM_STR),
            array(":data_inicio",      $dateiniformated,  PDO::PARAM_STR),
            array(":data_fim",         $datefimformated,  PDO::PARAM_STR)
        ));

        return $this->conexao->affected_rows();
    }


    /**
     * @info Atualiza os saldos de acumulo de banco de horas - Histórico
     *
     * @param string  $mat                      Matrícula SIAPE
     * @param integer $ciclo_id                 ID do ciclo de acumulo/usufruto
     * @param integer $saldoAtualizadoUsufruto  VAlor para atualizar banco de horas
     * @return integer/false
     */
    public function insertAutorizacoesServidoresUsufrutoHistorico( $mat=null, $ciclo_id=null, $dateiniformated=null, $datefimformated=null, $tipo_solicitacao=null  )
    {
        if (is_null($mat) || is_null($ciclo_id))
        {
            return false;
        }

        $siape = getNovaMatriculaBySiape($mat);

        $this->conexao->setMensagem("Problemas no acesso a Tabela AUTORIZACOES USUFRUTO - HISTÓRICO (Parcial) (E200047.".__LINE__.").");
        $this->conexao->query("
            INSERT INTO
                autorizacoes_servidores_usufruto_historico
                (id, siape, ciclo_id, data_inicio, data_fim,
                 tipo_autorizacao, acao, acao_siape, acao_data)
                VALUES
                (0, :siape, :ciclo_id, :data_inicio, :data_fim,
                :tipo_autorizacao, 'I', :acao_siape, NOW())
        ", array(
            array(":siape",            $siape,                  PDO::PARAM_STR),
            array(":ciclo_id",         $ciclo_id,               PDO::PARAM_INT),
            array(":data_inicio",      $dateiniformated,        PDO::PARAM_STR),
            array(":data_fim",         $datefimformated,        PDO::PARAM_STR),
            array(":tipo_autorizacao", $tipo_solicitacao,       PDO::PARAM_STR),
            array(":acao_siape",       $_SESSION['sMatricula'], PDO::PARAM_STR),
        ));

        return $this->conexao->affected_rows();
    }

    /**
     * @info Apura o saldo de usufruto dentro da semana corrente
     *
     * @param string $mat Matrícula SIAPE
     * @param string $inicio Data inicial
     * @param string $fim Data final
     * @return boolean
     */
    public function saldoHistoricoMovimentacoesAcumulosBancoDeHorasPorSiapePeriodo( $mat = null, $inicio = null, $fim = null )
    {
        if (is_null($mat) || is_null($inicio) || is_null($fim))
        {
            return false;
        }

        $mat = getNovaMatriculaBySiape($mat);

        $this->conexao->query("
            SELECT
                (SUM(historico_movimentacoes_acumulos.acumulo)
                 - SUM(historico_movimentacoes_acumulos.usufruto)) AS saldo,
                SUM(historico_movimentacoes_acumulos.acumulo)      AS horas,
                SUM(historico_movimentacoes_acumulos.usufruto)     AS usufruto
            FROM
                historico_movimentacoes_acumulos
            WHERE
                historico_movimentacoes_acumulos.siape = :siape
                AND historico_movimentacoes_acumulos.data_movimentacao >= (:inicio)
                AND historico_movimentacoes_acumulos.data_movimentacao <= (:fim)
        ",
        array(
            array(":siape",  $mat,    PDO::PARAM_STR),
            array(":inicio", $inicio, PDO::PARAM_STR),
            array(":fim",    $fim,    PDO::PARAM_STR),
        ));

        $totais = $this->conexao->fetch_assoc();

        $totais['saldo']    = (empty($totais['saldo'])    ? 0 : $totais['saldo']);
        $totais['horas']    = (empty($totais['horas'])    ? 0 : $totais['horas']);
        $totais['usufruto'] = (empty($totais['usufruto']) ? 0 : $totais['usufruto']);

        return $totais;
    }


    /**
     * @info Apura o saldo de usufruto por período
     *
     * @param string $mat Matrícula SIAPE
     * @param string $inicio Data inicial
     * @param string $fim Data final
     * @return boolean
     */
    public function getUsufrutoPorSiapePeriodo( $mat = null, $inicio = null, $fim = null )
    {
        $totais = $this->saldoHistoricoMovimentacoesAcumulosBancoDeHorasPorSiapePeriodo( $mat, $inicio, $fim );
        return $totais['usufruto'];
    }


    /**
     * @info Apura o saldo de horas acumuladas por período
     *
     * @param string $mat Matrícula SIAPE
     * @param string $inicio Data inicial
     * @param string $fim Data final
     * @return boolean
     */
    public function getAcumuloHorasPorSiapePeriodo( $mat = null, $inicio = null, $fim = null )
    {
        $totais = $this->saldoHistoricoMovimentacoesAcumulosBancoDeHorasPorSiapePeriodo( $mat, $inicio, $fim );
        return $totais['horas'];
    }


    /**
     * @info Apura o saldo de usufruto por período
     *
     * @param string $mat Matrícula SIAPE
     * @param string $inicio Data inicial
     * @param string $fim Data final
     * @return boolean
     */
    public function getSaldoBancoDeHorasPorSiapePeriodo( $mat = null, $inicio = null, $fim = null )
    {
        $saldos = $this->saldoHistoricoMovimentacoesAcumulosBancoDeHorasPorSiapePeriodo( $mat, $inicio, $fim );
        return $saldos['saldo'];
    }

} // END class TabBancoDeHorasAcumulosModel
