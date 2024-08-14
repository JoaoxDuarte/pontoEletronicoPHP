<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once("class_ocorrencias_grupos.php");
include_once("src/controllers/DadosServidoresController.php");


/**
 * @class TabPontoAuxiliarModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : pontoMMAAAA_auxiliar
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - src/models
 * @Arquivo   - TabPontoAuxiliarModel.php
 *
 * @author Edinalvo Rosa
 */
class TabPontoAuxiliarModel extends formPadrao
{
    /*
    * Atributos
    */
    /* @var OBJECT */ public $conexao = NULL;
    /* @var OBJECT */ public $objOcorrenciasGrupos;
    /* @var OBJECT */ public $objDadosServidoresController;

    
    ## @constructor
    #+-----------------------------------------------------------------+
    # Construtor da classe
    #+-----------------------------------------------------------------+
    #
    function __construct()
    {
        # Faz conexão
        $this->conexao = new DataBase();
        
        $this->objOcorrenciasGrupos         = new OcorrenciasGrupos();
        $this->objDadosServidoresController = new DadosServidoresController();
    }


    /**
     * @param  string  $siape  Matrícula do servidor
     * @param  string  $dia    Dia do registro
     * @return resource  Resultado da seleção
     */
    public function selecionarDadosPontoAuxiliar( $siape=null, $dia=null, $oco=null )
    {
        if (is_null($siape) || is_null($dia) || is_null($oco))
        {
            return false;
        }
        
        // Matricula no padrao orgao+siape
        $matricula = getNovaMatriculaBySiape($siape);
        $comp      = dataMes($dia) . dataAno($dia);

        $sql = "
        SELECT dia, siape, entra, intini, intsai, sai, jornd, jornp, jorndif, oco
            FROM ponto".$comp."_auxiliar
                WHERE 
                    dia = :dia
                    AND siape = :siape
                    AND oco = :oco
        ";

        $params = array(
            array(":dia",   conv_data($dia), PDO::PARAM_STR),
            array(":siape", $matricula,      PDO::PARAM_STR),
            array(":oco",   $oco,            PDO::PARAM_STR),
        );

        $this->conexao->query( $sql, $params );

        return $this->conexao;
    }

    
    /**
     * @info Incluir frequencia auxiliar
     * 
     * @param  array    $dados
     *      array(
     *          'dia'            Data da ocorrência
     *          'matricula'      Matrícula SIAPE
     *          'registro_ip'    IP da máquina do registro
     *          'registro_siape' Matrícula SIAPE de quem registrou
     * 
     *          'hora_ini'       Hora inicial da ausência
     *          'hora_fim'       Hora final da ausência
     *          'tempo_consulta' Tempo decorrido (hora_fim - hora_ini)
     *          'deslocamento'   Tempo de deslocamento,
     *          'setor'          Unidade do servidor
     *          'idreg'          Quem registrou Chefia/RH/Servidor
     *          'oco'            Código da ocorrência
     *      );
     * @param  integer  $saldo  
     * @return boolean  TRUE sucesso, FALSE erro
     */
    public function incluirPontoAuxiliarBancoDeHoras($paramsDados, $saldo = 0)
    {
        $dados = (object) $paramsDados;

        // Matricula no padrao orgao+siape
        $matricula  = getNovaMatriculaBySiape($dados->matricula);
        $sitcad     = $this->objDadosServidoresController->getSigRegJur( $matricula );
        $ocorrencia = $this->objOcorrenciasGrupos->CodigoBancoDeHorasDebitoPadrao($sitcad)[0];
        $dia        = conv_data($dados->dia);

        $return = false;

        $comp = dataMes($dia) . dataAno($dia);

        $ipalt    = getIpReal();
        $idaltexc = 'I';
        $siapealt = $dados->registro_siape;

        switch ($dados->idreg)
        {
            case 'S':
                $ipch     = '';
                $iprh     = '';
                $matchef  = '';
                $siaperh  = '';
                break;
    
            case 'R':
            case 'H':
                $ipch     = '';
                $iprh     = $dados->registro_ip;
                $matchef  = '';
                $siaperh  = $dados->registro_siape;
                break;

            case 'C':
                $ipch     = $dados->registro_ip;
                $iprh     = '';
                $matchef  = $dados->registro_siape;
                $siaperh  = '';
                break;
        }

        $dados->ipch    = $ipch;
        $dados->iprh    = $iprh;
        $dados->matchef = $ipch;
        $dados->siaperh = $iprh;
        
        // inclui no ponto auxiliar
        $this->conexao->setMensagem("Problemas no acesso a Tabela FREQUÊNCIA (E200038.".__LINE__.").");
        $this->conexao->query( "
        INSERT ponto".$comp."_auxiliar
            (dia, siape, entra, intini, intsai, sai, jornd, jornp, jorndif, oco, just, seq, idreg, ip, ip2, ip3, ip4, justchef, ipch, iprh, matchef, siaperh)
            VALUES
            (:dia, :siape, :entra, '00:00:00', '00:00:00', :sai, :jornd, '00:00', '00:00', :oco, '', '', :idreg, '', '', '', '', '', :ipch, :iprh, :matchef, :siaperh)
        ", array(
            array(":dia",     conv_data($dia),        PDO::PARAM_STR),
            array(":siape",   $matricula,             PDO::PARAM_STR),
            array(":entra",   $dados->hora_ini,       PDO::PARAM_STR),
            array(":sai",     $dados->hora_fim,       PDO::PARAM_STR),
            array(":jornd",   $dados->tempo_consulta, PDO::PARAM_STR),
            array(":oco",     $ocorrencia,            PDO::PARAM_STR),
            array(":idreg",   $dados->idreg,          PDO::PARAM_STR),
            array(":ipch",    $ipch,                  PDO::PARAM_STR),
            array(":iprh",    $iprh,                  PDO::PARAM_STR),
            array(":matchef", $matchef,               PDO::PARAM_STR),
            array(":siaperh", $siaperh,               PDO::PARAM_STR),
        ));
        $linhas_afetadas = $this->conexao->affected_rows();

        $return = ($linhas_afetadas > 0);

        // inclui no historico do ponto auxiliar
        $this->conexao->setMensagem("Problemas no acesso a Tabela FREQUÊNCIA (E200039.".__LINE__.").");
        $this->conexao->query( "
        INSERT histponto".$comp."_auxiliar
            (dia, siape, entra, intini, intsai, sai, jornd, jornp, jorndif, 
             oco, just, idreg, ip, ip2, ip3, ip4, justchef, ipch, iprh, 
             matchef, siaperh, diaalt, horaalt, siapealt, ipalt, idaltexc) 
            VALUES
            (:dia, :siape, :entra, '00:00:00', '00:00:00', :sai, :jornd, 
             '00:00', '00:00', :oco, '', :idreg, '', '', '', '', '', :ipch, 
             :iprh, :matchef, :siaperh, DATE_FORMAT(NOW(),'%Y-%m-%d'), 
             DATE_FORMAT(NOW(),'%H:%i:%s'), :siapealt, :ipalt, :idaltexc) 
        ", array(
            array(":dia",      conv_data($dia),        PDO::PARAM_STR),
            array(":siape",    $matricula,             PDO::PARAM_STR),
            array(":entra",    $dados->hora_ini,       PDO::PARAM_STR),
            array(":sai",      $dados->hora_fim,       PDO::PARAM_STR),
            array(":jornd",    $dados->tempo_consulta, PDO::PARAM_STR),
            array(":oco",      $ocorrencia,            PDO::PARAM_STR),
            array(":idreg",    $dados->idreg,          PDO::PARAM_STR),
            array(":ipch",     $ipch,                  PDO::PARAM_STR),
            array(":iprh",     $iprh,                  PDO::PARAM_STR),
            array(":matchef",  $matchef,               PDO::PARAM_STR),
            array(":siaperh",  $siaperh,               PDO::PARAM_STR),
            array(":siapealt", $siapealt,              PDO::PARAM_STR),
            array(":ipalt",    $ipalt,                 PDO::PARAM_STR),
            array(":idaltexc", $idaltexc,              PDO::PARAM_STR),
        ));

        // limite de horas consulta medica
        $limite_consulta = $this->horariosLimite();
        $secLimiteHoras  = time_to_sec($limite_consulta['limite_consulta']);

        if (is_integer($saldo))
        {
            $secSaldo = $saldo;
        }
        else
        {
            $secSaldo = time_to_sec($saldo);
        }

        if ($secSaldo > $secLimiteHoras)
        {
            $secSaldo = ($secSaldo - $secLimiteHoras);
        }

        if ($dados->idreg != 'S')
        {
            AlterarPontoPrincipal( $dados, $por_exclusao = false, $secSaldo );
        }

        return $return;
    }

    
    /**
     * @info Incluir frequencia auxiliar
     * 
     * @param  array    $paramsPonto
     *      array(
     *          'dia'            Data da ocorrência
     *          'matricula'      Matrícula SIAPE
     *          'hora_ini'       Hora inicial da ausência
     *          'hora_fim'       Hora final da ausência
     *          'tempo_consulta' Tempo decorrido (hora_fim - hora_ini)
     *          'deslocamento'   Tempo de deslocamento,
     *          'setor'          Unidade do servidor
     *          'idreg'          Quem registrou Chefia/RH/Servidor
     *          'oco'            Código da ocorrência
     *          'registro_ip'    IP da máquina do registro
     *          'registro_siape' Matrícula SIAPE de quem registrou
     *      );
     * @param  integer  $saldo  
     * @return boolean  TRUE sucesso, FALSE erro
     */
    function incluirPontoAuxiliarGECC($paramsPonto,$saldo=0)
    {
        // Matricula no padrao orgao+siape
        $matricula = getNovaMatriculaBySiape($paramsPonto['matricula']);

        $return = false;

        $comp = dataMes($paramsPonto['dia']) . dataAno($paramsPonto['dia']);

        $dia      = conv_data($paramsPonto['dia']);
        $ipalt    = getIpReal();
        $idaltexc = 'I';
        $siapealt = $paramsPonto['registro_siape'];

        if ($paramsPonto['idreg'] == 'C')
        {
            $ipch     = $paramsPonto['registro_ip'];
            $iprh     = '';
            $matchef  = $paramsPonto['registro_siape'];
            $siaperh  = '';
        }
        else
        {
            $ipch     = '';
            $iprh     = $paramsPonto['registro_ip'];
            $matchef  = '';
            $siaperh  = $paramsPonto['registro_siape'];
        }

        $paramsPonto['ipch']    = $ipch;
        $paramsPonto['iprh']    = $iprh;
        $paramsPonto['matchef'] = $ipch;
        $paramsPonto['siaperh'] = $iprh;

        $oDBase = new DataBase('PDO');
        $oDBase2 = new DataBase('PDO');

        // inclui no ponto auxiliar
        $sql = "
        INSERT ponto".$comp."_auxiliar
            (dia, siape, entra, intini, intsai, sai, jornd, jornp, jorndif, oco, just, seq, idreg, ip, ip2, ip3, ip4, justchef, ipch, iprh, matchef, siaperh)
            VALUES
            (:dia, :siape, :entra, '00:00:00', '00:00:00', :sai, :jornd, '00:00', '00:00', :oco, '', :seq, :idreg, '', '', '', '', '', :ipch, :iprh, :matchef, :siaperh)
        ";

        $params = array(
            array(":dia",     conv_data($dia),                PDO::PARAM_STR),
            array(":siape",   $matricula,                     PDO::PARAM_STR),
            array(":entra",   $paramsPonto['hora_ini'],       PDO::PARAM_STR),
            array(":sai",     $paramsPonto['hora_fim'],       PDO::PARAM_STR),
            array(":jornd",   $paramsPonto['tempo_consulta'], PDO::PARAM_STR),
            array(":oco",     $paramsPonto['oco'],            PDO::PARAM_STR),
            array(":seq",     '',                             PDO::PARAM_STR),
            array(":idreg",   $paramsPonto['idreg'],          PDO::PARAM_STR),
            array(":ipch",    $ipch,                          PDO::PARAM_STR),
            array(":iprh",    $iprh,                          PDO::PARAM_STR),
            array(":matchef", $matchef,                       PDO::PARAM_STR),
            array(":siaperh", $siaperh,                       PDO::PARAM_STR),
        );

        // inclusão
        $oDBase->query( $sql, $params );
        $linhas_afetadas = $oDBase->affected_rows();

        $return = ($linhas_afetadas > 0);

        // inclui no historico do ponto auxiliar
        $sql = "
        INSERT histponto".$comp."_auxiliar
            (dia, siape, entra, intini, intsai, sai, jornd, jornp, jorndif, 
             oco, just, idreg, ip, ip2, ip3, ip4, justchef, ipch, iprh, 
             matchef, siaperh, diaalt, horaalt, siapealt, ipalt, idaltexc) 
            VALUES
            (:dia, :siape, :entra, '00:00:00', '00:00:00', :sai, :jornd, 
             '00:00', '00:00', :oco, '', :idreg, '', '', '', '', '', :ipch, 
             :iprh, :matchef, :siaperh, DATE_FORMAT(NOW(),'%Y-%m-%d'), 
             DATE_FORMAT(NOW(),'%H:%i:%s'), :siapealt, :ipalt, :idaltexc) 
        ";

        $params = array(
            array(":dia",      conv_data($dia),                 PDO::PARAM_STR),
            array(":siape",    $matricula,                      PDO::PARAM_STR),
            array(":entra",    $paramsPonto['hora_ini'],        PDO::PARAM_STR),
            array(":sai",      $paramsPonto['hora_fim'],        PDO::PARAM_STR),
            array(":jornd",    $paramsPonto['tempo_consulta'],  PDO::PARAM_STR),
            array(":oco",      $paramsPonto['oco'],             PDO::PARAM_STR),
            array(":idreg",    $paramsPonto['idreg'],           PDO::PARAM_STR),
            array(":ipch",     $ipch,                           PDO::PARAM_STR),
            array(":iprh",     $iprh,                           PDO::PARAM_STR),
            array(":matchef",  $matchef,                        PDO::PARAM_STR),
            array(":siaperh",  $siaperh,                        PDO::PARAM_STR),
            array(":siapealt", $siapealt,                       PDO::PARAM_STR),
            array(":ipalt",    $ipalt,                          PDO::PARAM_STR),
            array(":idaltexc", $idaltexc,                       PDO::PARAM_STR),
        );


        // limite de horas consulta medica
        $limite_consulta = horariosLimite();
        $secLimiteHoras  = time_to_sec($limite_consulta['limite_consulta']);

        $secSaldo = time_to_sec($saldo);

        $oDBase2->query( $sql, $params );

        if ($secSaldo > $secLimiteHoras)
        {
            $secSaldo = ($secSaldo - $secLimiteHoras);
        }

        AlterarPontoPrincipal( $dados, $por_exclusao = false, $secSaldo );

        return $return;
    }

    
    /**
     * @param  array    $paramsPonto = array(
     *          'dia'            Data da ocorrência
     *          'matricula'      Matrícula SIAPE
     *          'hora_ini'       Hora inicial da ausência
     *          'hora_fim'       Hora final da ausência
     *          'tempo_consulta' Tempo decorrido (hora_fim - hora_ini)
     *          'deslocamento'   Tempo de deslocamento,
     *          'setor'          Unidade do servidor
     *          'idreg'          Quem registrou Chefia/RH/Servidor
     *          'oco'            Código da ocorrência
     *          'registro_ip'    IP da máquina do registro
     *          'registro_siape' Matrícula SIAPE de quem registrou
     *          'ipch'           IP da chefia, se for a chefia quem registra
     *          'iprh'           IP do RH, se for o RH quem registra
     *          'matchef'        Matrícula da chefia, se for a chefia quem registra
     *          'siaperh'        Matrícula do servidor do RH, se for o RH quem registra
     *      );
     *         boolean $por_exclusao
     * @return boolean  TRUE sucesso, FALSE erro
     */
    public function AlterarPontoPrincipal( $paramsPonto=null, $por_exclusao=false, $limite_horas=0 )
    {
        if ((is_null($paramsPonto)  || !is_array($paramsPonto)) || 
            (is_null($por_exclusao) || !is_bool($por_exclusao)) ||
            (is_null($limite_horas) || !is_int($limite_horas)))
        {
            return false;
        }
        
        // Matricula no padrao orgao+siape
        $matricula = getNovaMatriculaBySiape( $paramsPonto['matricula'] );
        $sitcad    = $this->objDadosServidoresController->getSigRegJur( $paramsPonto['matricula'] );

        $dia        = conv_data($paramsPonto['dia']);
        $comp       = dataMes($dia) . dataAno($dia);
        $novo_saldo = NULL;
        $jornada    = $this->JornadaDoServidor($matricula,$_SESSION['uorg'],$dia);

        $sql = "
            SELECT 
                dia, siape, entra, intini, intsai, sai, jornd, jornp, jorndif, oco
            FROM 
                ponto" . $comp . "
            WHERE
                dia = :dia
                AND siape = :siape
        ";

        $params = array(
            array(":dia",   $dia,       PDO::PARAM_STR),
            array(":siape", $matricula, PDO::PARAM_STR),
        );

        // inclusão
        $this->conexao->query( $sql, $params );

        if ($this->conexao->num_rows() > 0)
        {
            $CodigoDiferenca                 = $this->objOcorrenciasGrupos->SaldoDiferencasMultiocorrencias($sitcad,$temp=true);
            $CodigoHoraExtra                 = $this->objOcorrenciasGrupos->CodigoHoraExtraPadrao($sitcad);
            $CodigoCredito                   = $this->objOcorrenciasGrupos->CodigoCreditoPadrao($sitcad);
            $CodigoCredito                   = $this->objOcorrenciasGrupos->CodigoCreditoPadrao($sitcad);
            
            $CodigoConsultaMedicaPadrao      = $this->objOcorrenciasGrupos->CodigoConsultaMedicaPadrao($sitcad);
            $CodigoBancoDeHorasCreditoPadrao = $this->objOcorrenciasGrupos->CodigoBancoDeHorasCreditoPadrao($sitcad);
            $CodigoBancoDeHorasDebitoPadrao  = $this->objOcorrenciasGrupos->CodigoBancoDeHorasDebitoPadrao($sitcad);

            $SaldoNegativo                   = $this->objOcorrenciasGrupos->SaldoNegativo();
            $SaldoPositivo                   = $this->objOcorrenciasGrupos->SaldoPositivo();

            $ponto = $this->conexao->fetch_object();

            $secTempoConsulta = time_to_sec($paramsPonto['tempo_consulta']);
            
            if ($secTempoConsulta > time_to_sec($ponto->jorndif) && !in_array($ponto->oco,$SaldoPositivo))
            {
                $secTempoConsulta = $limite_horas;
            }
            
            $secJornDiferenca = time_to_sec($ponto->jorndif);
            $secJornada       = time_to_sec(formata_jornada_para_hhmm($jornada));

            // ocorrências com diferença
            if (in_array($ponto->oco,$CodigoDiferenca))
            {
                if ($por_exclusao == true)
                {
                    $novo_saldo9 = ($secJornDiferenca + $secTempoConsulta);
                    $novo_saldo8 = (in_array($ponto->oco,$CodigoHoraExtra)
                                    && $novo_saldo9 > time_to_sec('02:00')
                                    ? time_to_sec('02:00') : $novo_saldo9);
                    $novo_saldo  = (in_array($ponto->oco,$CodigoCredito)
                                    && $novo_saldo8 > time_to_sec('02:00')
                                    ? time_to_sec('02:00') : $novo_saldo8);
                }
                else
                {
                    $novo_saldo = ($secJornDiferenca - $secTempoConsulta);
                    if ($novo_saldo < 0)
                    {
                        $novo_saldo = 0;
                    }
                }

                $novo_saldo = ($novo_saldo > $secJornada ? $secJornada : $novo_saldo);
            }

            if (!is_null($novo_saldo))
            {
                sleep(1);

                $this->gravar_historico_ponto($matricula, $dia, $oper = 'A');

                $novo_saldo = ($novo_saldo > 0 ? $novo_saldo : 0);

                $sql = "
                    UPDATE 
                        ponto".$comp."
                    SET
                        jorndif = '" . sec_to_time($novo_saldo,'hh:mm') . "'
                    WHERE
                        dia = :dia
                        AND siape = :siape
                ";

                $this->conexao->query( $sql, $params );
            }
        }
    }

    /**
     * 
     * @param string      $siape
     * @param string      $lotacao
     * @param string/date $dia
     * @return bool/string
     */
    public function JornadaDoServidor( $siape=null, $lotacao=null, $dia=null)
    {
        if (is_null($siape) || is_null($lotacao) || is_null($dia))
        {
            return false;
        }
        
        // verifica autorizacao
        $oJornadaTE = new DefinirJornada();
        $oJornadaTE->setSiape($siape);
        $oJornadaTE->setLotacao($lotacao);
        $oJornadaTE->setData($dia);
        $oJornadaTE->setChefiaAtiva();
        $oJornadaTE->estabelecerJornada();

        return $oJornadaTE->getJornada();
    }

    
    /**
     * @param array  $dados
     * @param string $oco
     * @return void
     */
    public function deletePontoAuxiliar( $dados=null, $oco=null)
    {
        if ((is_null($dados) || !is_array($dados)) ||
            (is_null($oco) || !is_string($oco) || empty($oco)))
        {
            return false;
        }
        
        /*
        $params = array(
             0    array(":dia",            $oDados->dia,            PDO::PARAM_STR),
             1    array(":siape",          $oDados->siape,          PDO::PARAM_STR),
             2    array(":hora_ini",       $oDados->hora_ini,       PDO::PARAM_STR),
             3    array(":hora_fim",       $oDados->hora_fim,       PDO::PARAM_STR),
             4    array(":tempo_consulta", $oDados->tempo_consulta, PDO::PARAM_STR),
             5    array(":deslocamento",   $oDados->deslocamento,   PDO::PARAM_STR),
             6    array(":setor",          $oDados->setor,          PDO::PARAM_STR),
             7    array(":idreg",          $idreg,                  PDO::PARAM_STR),
             8    array(":acao",           'E',                     PDO::PARAM_STR),
             9    array(":registro_ip",    getIpReal(),             PDO::PARAM_STR),
            10    array(":registro_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
        );
        */

        // Matricula no padrao orgao+siape
        $matricula = getNovaMatriculaBySiape($dados[1][1]);

        // dados
        $comp     = dataMes($dados[0][1]) . dataAno($dados[0][1]);
        $dia      = conv_data($dados[0][1]);
        $ipalt    = getIpReal();
        $idaltexc = $dados[8][1];
        $siapealt = $dados[10][1];

        // grava os dados anteriores
        // no historico do ponto
        $this->gravarHistoricoPontoAuxiliar( $matricula, $dia, $oco, 'E' );

        // alterar principal
        $paramsPonto = array(
            'dia'            => $dia,
            'matricula'      => $matricula,
            'tempo_consulta' => $dados[4][1],
            'setor'          => $dados[6][1],
            'idreg'          => $dados[7][1],
            'oco'            => $oco,
            'registro_ip'    => $dados[9][1],
            'registro_siape' => $dados[10][1],
            'ipch'           => $dados[9][1],
            'iprh'           => '',
            'matchef'        => $dados[10][1],
            'siaperh'        => '',
        );

        $this->AlterarPontoPrincipal( $paramsPonto, $por_exclusao=true );

        // apaga o registro
        $this->conexao->query("
            DELETE FROM 
                ponto".$comp."_auxiliar
            WHERE
                siape = :siape
                AND dia = :dia
                AND oco = :oco
        ",
        array(
            array(":siape", $matricula,      PDO::PARAM_STR),
            array(":dia",   conv_data($dia), PDO::PARAM_STR),
            array(":oco",   $oco,            PDO::PARAM_STR),
        ));
    }


    /**  @Function
     *
     * @param  string  $matricula  Matricula siape
     * @param  string> $diac       Data invertida (aaaa-mm-dd)
     * @param  string> $oco        Código da ocorrência
     * @param  string> $oper       Operação executada (alteração,etc)
     * @return : void
     * @usage  : gravarHistoricoPontoAuxiliar('9999999','2011-11-10','xxxxx','X');                                                |
     * @author  Edinalvo Rosa
     *
     * @info  Registra em histórico os dados do ponto auxiliar antes da alteração
     */
    public function gravarHistoricoPontoAuxiliar( $matricula=null, $dia=null, $oco=null, $oper='A')
    {
        if (is_null($matricula) || is_null($dia) || is_null($oco))
        {
            return false;
        }
        
        // Matricula no padrao orgao+siape
        $matricula = getNovaMatriculaBySiape($matricula);

        // dados do usuario cadastrador
        $sUsuario = $_SESSION["sMatricula"];

        //linha que captura o ip do usuario.
        $ip = getIpReal();

        // competencia referente a data indicada
        $comp = dataMes($dia) . dataAno($dia);

        // mensagem em caso de erro no acesso aa tabela
        $this->conexao->setMensagem( "Falha no registro do histórico de frequência!" );

        // grava os dados anteriores
        // no historico do ponto
        $this->conexao->query("
            INSERT INTO 
                histponto".$comp."_auxiliar
            SELECT
                dia, siape, entra, intini, intsai, sai, jornd, jornp, 
                jorndif, oco, idreg, ip, ip2, ip3, ip4, 
                IFNULL(ipch,'') AS ipch, 
                IFNULL(iprh,'') AS iprh,
                matchef, siaperh, 
                DATE_FORMAT(NOW(),'%Y-%m-%d') AS diaalt,
                DATE_FORMAT(NOW(),'%H:%i:%s') AS horaalt, 
                :usuario, :ip, :oper, just, justchef
            FROM
                ponto" . $comp . "_auxiliar
            WHERE
                dia = :dia
                AND siape = :siape
                AND oco = :oco
        ",
        array(
            array(':siape',   $matricula, PDO::PARAM_STR),
            array(':dia',     $dia,       PDO::PARAM_STR),
            array(':usuario', $sUsuario,  PDO::PARAM_STR),
            array(':ip',      $ip,        PDO::PARAM_STR),
            array(':oper',    $oper,      PDO::PARAM_STR),
            array(":oco",     $oco,       PDO::PARAM_STR),
        ));
    }


    /**  @Function
     *
     * @param  string  $siape    Matrícula do servidor
     * @param  string  $dia      Data do registro da frequência
     * @param  string  $unidade  Unidade do servidor
     * @return void
     * @author Edinalvo Rosa
     * @info   Ajusta saldo da frequencia se houver registro de
     *         consulta médica ou GECC
     */
    public function AjustaSaldoFrequenciaSeConsultaMedicaRegistrada( $siape=null, $dia=null, $unidade=NULL )
    {
        if (is_null($siape) || is_null($dia) || is_null($unidade))
        {
            return false;
        }
        
        $sql = "
            SELECT 
                tempo_consulta
            FROM 
                compareceu_consulta_medica
            WHERE
                siape = :siape
                AND dia = :dia
        ";

        $params = array(
            array(":dia",   $dia,   PDO::PARAM_STR),
            array(":siape", $siape, PDO::PARAM_STR),
        );

        // inclusão
        $this->conexao->query( $sql, $params );

        if ($this->conexao->num_rows() > 0)
        {
            $horarios_limite = $this->horariosLimite($unidade);

            $tempo_consulta = $this->conexao->fetch_object()->tempo_consulta;

            $paramsPonto = array(
                'dia'            => $dia,
                'matricula'      => $siape,
                'tempo_consulta' => $tempo_consulta,
            );

            $this->AlterarPontoPrincipal( $paramsPonto, false, time_to_sec($horarios_limite['horas_excedentes']) );
        }
    }


    /**  @Function
     * +-------------------------------------------------------------+
     * | @function    : horariosLimite                               |
     * | @description : Carrega limites                              |
     * | @param  : <string>  - $unidade                              |
     * | @return : void                                              |
     * | @author : Edinalvo Rosa                                     |
     * +-------------------------------------------------------------+
     * */
    public function horariosLimite( $unidade=NULL )
    {
        $unidade = (is_null($unidade) ? $_SESSION['sLotacao'] : $unidade);

        $horarios_limite = array(
            'entrada_orgao'    => '05:00',
            'saida_orgao'      => '22:00',
            'entrada_unidade'  => '07:00',
            'saida_unidade'    => '19:00',
            'limite_consulta'  => '44:00',
            'horas_excedentes' => '02:00',
        );
    
        // seleciona limite de horário inicial e final do Órgão/Sistema
        $this->conexao->query( "
            SELECT 
                campo, minutos
            FROM 
                config_basico
        " );

        if ($this->conexao->num_rows() > 0)
        {
            while ($horarios = $this->conexao->fetch_object())
            {
                switch ($horarios->campo)
                {
                    case 'limite_hora_entrada_inss':
                        $horarios_limite['entrada_orgao'] = $horarios->minutos;
                        break;
                
                    case 'limite_hora_saida_inss':
                        $horarios_limite['saida_orgao'] = $horarios->minutos;
                        break;
                
                    case 'limite_horas_excedentes_por_dia':
                        $horarios_limite['horas_excedentes'] = $horarios->minutos;
                        break;
                
                    case 'limite_horas_anual_consulta_medica':
                        $horarios_limite['limite_consulta'] = $horarios->minutos;
                        break;
                
                    default:
                        $horarios_limite[$horarios->campo] = $horarios->minutos;
                        break;
                }
            }
        }

        // seleciona limite de horário inicial e final da Unidade
        $this->conexao->query( "SELECT inicio_atend, fim_atend FROM tabsetor WHERE codigo = '".$unidade."' " );

        if ($this->conexao->num_rows() > 0)
        {
            $horario = $this->conexao->fetch_object();
            $horarios_limite['entrada_unidade']  = $horario->inicio_atend;
            $horarios_limite['saida_unidade']    = $horario->fim_atend;
        }
    
        return $horarios_limite;
    }

} // END class TabPontoAuxiliarModel
