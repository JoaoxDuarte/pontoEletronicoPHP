<?php

/**
 * Executa operações com tabela ocorrência
 *
 * @version 1.0
 * @author Edinalvo Rosa
 */

// Inicializa a sessão (session_start)
// funcoes de uso geral
include_once( "config.php" );


class OcorrenciasGrupos
{

    /**
     * Atributos
     *
     */
    private $oDBase; // @var object Funções Banco de Dados

    /**
     * Construtora
     *
     * @param void
     *
     * @return void
     */
    public function __construct()
    {
        $this->oDBase = new DataBase('PDO');
    }

    
    /**
     * Verifica o regime
     *
     * @param string/null  $sigla  Indica o regime a que pertence a sigla/código
     *
     * @return string  $regime  Regime cadastral
     */
    public function GrupoRegime($sitcad = null)
    {
        switch ($sitcad)
        {
            case 'CLT': $grupo = "'Todos','CLT'";
                break;
            case 'ETG': $grupo = "'Todos','ETG'";
                break;
            case 'RJU': $grupo = "'Todos','RJU','RJU/EST";
                break;
            case 'EST': $grupo = "'Todos','EST','RJU/EST";
                break;
            default: $grupo = "";
                break;
        }

        return $grupo;
    }

    
    /**
     * Verifica o grupo cadastral
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return string  $grupo  Grupo cadastral
     */
    public function GrupoCadastral($sitcad = null)
    {
        switch ($sitcad)
        {
            case 'CLT': $grupo = "'Todos','CLT'";
                break;
            case 'ETG': $grupo = "'Todos','ETG'";
                break;
            case 'RJU': $grupo = "'Todos','RJU','RJU/EST";
                break;
            case 'EST': $grupo = "'Todos','EST','RJU/EST'";
                break;
            default: $grupo = "";
                break;
        }

        if (!empty($grupo))
        {
            $grupo = " AND grupo_cadastral IN (" . $grupo . ") ";
        }

        return $grupo;
    }

    
    /**
     * Executa query padrão
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     * @param string       $where   Filtro (where) para o
     *                              código SQL a ser executado.
     *
     * @return array  $array  Resultado da seleção
     */
    public function ExecutaPadraoSQL($sitcad = null, $where = "", $resp = '', $dia = null, $exibe_sql = false)
    {
        $dia = (is_null($dia) ? date('Y-m-d') : $dia);

        switch ($resp)
        {
            case 'C':
            case 'CH':
                $resp = 'CH';
                break;
            case 'R':
            case 'RH':
                $resp = 'RH';
                break;
        }

        $array = array();

        $where .= $this->GrupoCadastral($sitcad);

        $sql = "
        SELECT
            siapecad
        FROM
            tabocfre
        WHERE
            ('" . $dia . "' >= vigencia_inicio AND '" . $dia . "' <= IF(vigencia_fim='0000-00-00','9999-99-99',vigencia_fim))
            " . (empty($resp) ? "" : " AND resp IN ('" . $resp . "','AB')") . "
            " . $where . "
        ORDER BY
            IF(grupo_cadastral='CLT',1,2),siapecad
        ";

        if ($exibe_sql == true)
        {
            //fimDie(__LINE__, $sql, false, __FILE__ . '<br>' . __FUNCTION__ . ' ' . __CLASS__ . ' ' . __METHOD__);
        }
        
        $this->oDBase->setMensagem("Problemas no acesso a Tabela OCORRÊNCIAS (E300034.".__LINE__.").");
        $this->oDBase->query($sql);

        while ($codigo = $this->oDBase->fetch_object())
        {
            $array[] = $codigo->siapecad;
        }

        return $array;
    }

    
    /**
     * Código de ocorrências de uso exclusivo do sistema
     *
     * @param string/null  $sitcad          Indica o grupo a que pertence o código
     * @param boolean      $exige_horarios  Indica que exige informar os horários
     *
     * @return array  código de ocorrências de uso exclusivo
     */
    public function CodigosUsoExclusivoSistema($sitcad = null, $exige_horarios = false)
    {
        return $this->ExecutaPadraoSQL(
            $sitcad, "
            AND resp = 'SI' 
            " . ($exige_horarios == false ? "AND informar_horarios = 'N'" : "")
        );

        return $array;
    }

    
    /**
     * Código de ocorrência de registro parcial - padrão
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  código de ocorrência de registro parcial
     */
    public function CodigoRegistroParcialPadrao($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND padrao IN ('Registro Parcial')
            "
        );
    }

    
    /**
     * Código de ocorrência de crédito - padrão
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  código de ocorrência crédito
     */
    public function CodigoCreditoPadrao($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND padrao IN ('Credito')
            "
        );
    }

    
    /**
     * Código de ocorrência de dédito - padrão
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  código de ocorrência dédito
     */
    public function CodigoDebitoPadrao($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND padrao IN ('Debito')
            "
        );
    }

    
    /**
     * Código de ocorrência de crédito compensação do recesso - padrão
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  código de ocorrência crédito
     */
    public function CodigoCreditoRecessoPadrao($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND padrao IN ('Recesso Credito')
            "
        );
    }

    
    /**
     * Código de ocorrência de dédito por uso do recesso- padrão
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  código de ocorrência dédito
     */
    public function CodigoDebitoRecessoPadrao($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND padrao IN ('Recesso Debito')
            "
        );
    }

    
    /**
     * Código de ocorrência de frequência normal - padrão
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  código de ocorrência frequência normal
     */
    public function CodigoFrequenciaNormalPadrao($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND padrao IN ('Frequencia Normal')
            "
        );
    }

    
    /**
     * Código de ocorrência de sem frequência - padrão
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  código de ocorrência sem frequência
     */
    public function CodigoSemFrequenciaPadrao($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND padrao IN ('Sem Frequencia')
            "
        );
    }

    
    /**
     * Código de ocorrência de abono - padrão
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  código de ocorrência de abono
     */
    public function CodigoAbonoPadrao($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND padrao IN ('Abono')
            "
        );
    }

    
    /**
     * Código de ocorrência de hora extra - padrão
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  código de ocorrência de hora extra
     */
    public function CodigoHoraExtraPadrao($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND padrao IN ('Hora Extra')
            "
        );
    }

    
    /**
     * Código de ocorrência de instrutoria/tutoria débito - padrão
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  Código de instrutoria/tutoria
     */
    public function CodigoDebitoInstrutoriaPadrao($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND padrao IN ('Instrutoria Debito')
            "
        );
    }

    
    /**
     * Código de ocorrência de instrutoria/tutoria débito - padrão
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  Código de instrutoria/tutoria
     */
    public function CodigoCreditoInstrutoriaPadrao($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND padrao IN ('Instrutoria Credito')
            "
        );
    }

    
    /**
     * Código de ocorrência de banco de horas crébito - padrão
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  Código de banco de horas crédito
     */
    public function CodigoBancoDeHorasCreditoPadrao($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND padrao IN ('Banco de Horas Credito')
            "
        );
    }

    
    /**
     * Código de ocorrência de banco de horas débito - padrão
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  Código de banco de horas débito
     */
    public function CodigoBancoDeHorasDebitoPadrao($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND padrao IN ('Banco de Horas Debito')
            "
        );
    }

    
    /**
     * Código de ocorrência de consulta médica - padrão
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  Código de consulta médica
     */
    public function CodigoConsultaMedicaPadrao($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND padrao IN ('Consulta Medica')
            "
        );
    }

    
    /**
     * Código de ocorrência de serviço externo - padrão
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  Código de serviço externo
     */
    public function CodigoServicoExternoPadrao($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND grupo_ocorrencia IN ('Servico Externo')
            "
        );
    }

    
    /**
     * Código de ocorrência Sem Vínculo - padrão
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  código de ocorrência Sem Vínculo
     */
    public function CodigoSemVinculoPadrao($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND padrao IN ('Sem Vínculo')
            "
        );
    }

    
    /**
     * Código de ocorrências compensaveis
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  código de ocorrências compensaveis
     */
    public function CodigosDebitosExigeHorario($sitcad = null)
    {
        return $this->CodigosCompensaveis($sitcad, $exige_horarios = true);
    }

    
    /**
     * Código de ocorrências compensaveis
     *
     * @param string/null  $sitcad          Indica o grupo a que pertence o código
     * @param boolean      $exige_horarios  Indica que exige informar os horários
     *
     * @return array  código de ocorrências compensaveis
     */
    public function CodigosCompensaveis($sitcad = null, $exige_horarios = false)
    {
        if (is_bool($exige_horarios))
        {
            $filtro = ($exige_horarios == false ? " AND informar_horarios = 'N' " : " AND resp <> 'SI' AND informar_horarios = 'S' ");
        }

        $array = $this->ExecutaPadraoSQL(
                $sitcad, "
            AND tratamento_debito IN ('Compensavel')
            " . $filtro
        );

        return $array;
    }

    
    /**
     * Código de ocorrências agrupados em um código de desconto
     *
     * @param string/null  $sitcad          Indica o grupo a que pertence o código
     *
     * @return array  código de ocorrências agrupados para desconto
     */
    public function CodigosAgrupadosParaDesconto($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND agrupa_debito IN (" . implode(',', $this->CodigoDebitoPadrao($sitcad)) . ")
        "
        );
    }

    
    /**
     * Código de ocorrências Licença com remuneração
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  zerar saldo
     */
    public function CodigosLicencaComRemuneracao($sitcad = null)
    {
        $array = array();

        $array = $this->ExecutaPadraoSQL(
                $sitcad, "
            AND tipo IN ('todos_zerados')
            AND grupo_ocorrencia IN ('Licenca com remuneracao')
            "
        );

        return $array;
    }

    
    /**
     * Código de ocorrências zerar saldo
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  zerar saldo
     */
    public function CodigosDiaJaRemuneradoSaldoZerado($sitcad = null)
    {
        $array = array();

        $array = $this->ExecutaPadraoSQL(
                $sitcad, "
            AND grupo IN ('Servidor')
            AND tipo IN ('diferenca_zerada')
            AND grupo_ocorrencia IN ('Dia já remunerado')
            "
        );

        return $array;
    }

    
    /**
     * Código de ocorrências COVID19
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array
     */
    public function CodigosCOVID19($sitcad = null)
    {
        $array = array();

        $array = $this->ExecutaPadraoSQL(
                $sitcad, "
            AND grupo_ocorrencia IN ('COVID19')
            "
        );

        return $array;
    }

    
    /**
     * Código de ocorrências que exigem informação de horário
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  Códigos que exigem informação de horários
     */
    public function CodigosExigemHorarios($sitcad = null)
    {
        $array = array();

        $array = $this->ExecutaPadraoSQL(
                $sitcad, "
            AND informar_horarios = 'S'
            "
        );

        return $array;
    }


    /**
     * Código de ocorrências Diferença positiva no Saldo
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     * @param boolean      $temp    true = inclui o código de viagem em objeto de serviço
     *
     * @return array  Códigos com Diferença no Saldo
     */
    public function CodigosCredito($sitcad = null, $temp = false)
    {
        $array1 = $this->SaldoPositivo($sitcad, $temp);
        $array2 = $this->CodigosDiaJaRemuneradoSaldoZerado($sitcad);
        $array3 = $this->CodigoFrequenciaNormalPadrao($sitcad);

        $array = array_merge($array1, $array2, $array3);

        sort($array);

        return $array;
    }

    
    /**
     * Código de ocorrências excluídas dos sem remuneração
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  $array  excluídas dos sem remuneração
     */
    public function ExcluirDosSemRemuneracao($sitcad = null, $exige_horarios = false)
    {
        $array1 = $this->CodigoFrequenciaNormalPadrao($sitcad);
        $array = array_merge($array1, $this->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios));
        //$array  = array_merge( $array2, $this->CodigoAbonoPadrao($sitcad) );
        sort($array);

        return $array;
    }

    
    /**
     * Código de ocorrências zerar horarios
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     * @param boolean      $temp    false = inclui o código de viagem em objeto de serviço
     *
     * @return array  zerar horarios
     */
    public function HorariosZerados($sitcad = null, $temp = false)
    {
        $codigo_viagem = "'" . implode("','", $this->GrupoOcorrenciasViagem($sitcad)) . "'";

        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND (grupo NOT IN ('Estagiario')
            AND tipo IN ('todos_zerados'))
            " . ($temp == false ? "" : " AND siapecad NOT IN (" . $codigo_viagem . ") ") . "
            "
        );
    }

    
    /**
     * Código de ocorrências zerar saldo
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  zerar saldo
     */
    public function SaldoZerado($sitcad = null)
    {
        $array = array();

        $array = $this->ExecutaPadraoSQL(
                $sitcad, "
            AND grupo NOT IN ('Estagiario')
            AND tipo IN ('diferenca_zerada')
            "
        );

        $array = array_merge($array, $this->CodigoAbonoPadrao($sitcad));
        sort($array);

        return $array;
    }

    
    /**
     * Código de ocorrências Diferença positiva no Saldo
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     * @param boolean      $temp    true = inclui o código de viagem em objeto de serviço
     *
     * @return array  Códigos com Diferença no Saldo
     */
    public function SaldoPositivo($sitcad = null, $temp = false)
    {
        $codigo_viagem = "'" . implode("','", $this->GrupoOcorrenciasViagem($sitcad)) . "'";

        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND tipo IN ('diferenca_positiva')
            " . ($temp == false ? "" : " OR siapecad IN (" . $codigo_viagem . ") ") . "
            "
        );
    }

    
    /**
     * Código de ocorrências Diferença negativa no Saldo
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  Códigos com Diferença no Saldo
     */
    public function CodigosJornadaNegativa($sitcad = null)
    {
        $array = array();

        $array = $this->ExecutaPadraoSQL(
                $sitcad, "
            AND (grupo NOT IN ('Estagiario')
                AND tipo IN ('jornada_negativa'))
            "
        );

        $array1 = array_merge($array, $this->CodigoRegistroParcialPadrao($sitcad));
        $array = array_merge($array1, $this->CodigoSemFrequenciaPadrao($sitcad));
        sort($array);

        return $array;
    }

    
    /**
     * Código de ocorrências Diferença negativa no Saldo
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  Códigos com Diferença no Saldo
     */
    public function CodigosDebito($sitcad = null)
    {
        return $this->SaldoNegativo($sitcad);
    }

    
    /**
     * Código de ocorrências Falta Justificada
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  $array  Códigos Falta Justificada
     */
    public function FaltaJustificadaPadrao($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND padrao IN ('Falta Justificada')
            "
        );
    }

    
    /**
     * Código de ocorrências Diferença negativa no Saldo
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  $array  Códigos com Diferença no Saldo
     */
    public function SaldoNegativo($sitcad = null)
    {
        $array = array();

        $array = $this->ExecutaPadraoSQL(
                $sitcad, "
            AND (grupo NOT IN ('Estagiario')
            AND ((tipo IN ('diferenca_negativa'))
                OR (tipo IN ('jornada_negativa') AND tratamento_debito = 'Compensavel')))
            "
        );

        $array1 = array_merge($array, $this->CodigoRegistroParcialPadrao($sitcad));
        $array = array_merge($array1, $this->CodigoSemFrequenciaPadrao($sitcad));
        sort($array);

        return $array;
    }

    
    /**
     * Código de ocorrências Diferença no Saldo (positiva ou negativa)
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     * @param boolean      $temp    true = inclui o código de viagem em objeto de serviço
     *
     * @return array  Diferença no Saldo (positiva ou negativa)
     */
    public function SaldoDiferenca($sitcad = null, $temp = true)
    {
        $codigo_viagem = "'" . implode("','", $this->GrupoOcorrenciasViagem($sitcad)) . "'";

        return $this->ExecutaPadraoSQL(
            $sitcad, "
            AND (grupo NOT IN ('Estagiario')
            AND tipo IN ('diferenca_negativa','diferenca_positiva'))
        " . ($temp == false ? "" : " OR siapecad IN (" . $codigo_viagem . ") ") . "
        "
        );
    }

    
    /**
     * Código de ocorrências Diferença no Saldo
     * (positiva, negativa, jornada_negativa AND Compensavel)
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     * @param boolean      $temp    true = inclui o código de viagem em objeto de serviço
     *
     * @return array  $array  Diferença no Saldo (positiva ou negativa)
     */
    public function SaldoDiferencasMultiocorrencias($sitcad = null, $temp = true)
    {
        $array = array();

        $codigo_viagem = "'" . implode("','", $this->GrupoOcorrenciasViagem($sitcad)) . "'";

        $array = $this->ExecutaPadraoSQL(
                $sitcad, "
            AND (grupo NOT IN ('Estagiario')
            AND ((tipo IN ('diferenca_negativa','diferenca_positiva'))
                OR (tipo IN ('jornada_negativa') AND tratamento_debito = 'Compensavel')))
            " . ($temp == false ? "" : " OR siapecad IN (" . $codigo_viagem . ") ") . "
            "
        );

        $array = array_merge($array, $this->CodigoRegistroParcialPadrao($sitcad));
        $array = array_merge($array, $this->CodigoSemFrequenciaPadrao($sitcad));
        sort($array);

        return $array;
    }

    
    /**
     * Código de ocorrências Estagiários
     *
     * @param void
     *
     * @return array  Estagiarios
     */
    public function Estagiarios()
    {
        return $this->ExecutaPadraoSQL(
            $sitcad, "
            AND grupo IN ('Ambos','Estagiarios')
            AND siapecad <> '-----'
            "
        );
    }

    
    /**
     * Código de ocorrências indefinidos
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     * @param boolean      $temp    true = inclui o código de viagem em objeto de serviço
     *
     * @return array  Ocorrências indefinidas
     */
    public function CodigosTrocaObrigatoria($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND tipo IN ('indefinida')
            OR siapecad IN ('-----','99999')
            "
        );
    }

    
    /**
     * Código de ocorrências indefinidos
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     * @param boolean      $temp    true = inclui o código de viagem em objeto de serviço
     *
     * @return array  Ocorrências indefinidas
     */
    public function CodigosIndefinida($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
                        $sitcad, "
            AND tipo IN ('indefinida')
            "
        );
    }

    
    /**
     * Código de ocorrências jornada negativa
     *
     * @param void
     *
     * @return array  Estagiários - reduz jornada pela metade
     */
    public function EstagiariosJornadaNegativa()
    {
        return $this->ExecutaPadraoSQL(
            $sitcad, "
            AND grupo IN ('Estagiario')
            AND tipo IN ('jornada_negativa')
            "
        );
    }

    
    /**
     * Código de ocorrências diferença negativa
     *
     * @param void
     *
     * @return array  Estagiários - reduz jornada pela metade
     */
    public function EstagiariosDiferencaNegativa()
    {
        return $this->ExecutaPadraoSQL(
            $sitcad, "
            AND grupo IN ('Estagiario')
            AND tipo IN ('diferenca_negativa')
            "
        );
    }

    
    /**
     * Código de ocorrências diferença positiva
     *
     * @param void
     *
     * @return array  Estagiários - reduz jornada pela metade
     */
    public function EstagiariosDiferencaPositiva()
    {
        return $this->ExecutaPadraoSQL(
            $sitcad, "
            AND grupo IN ('Estagiario')
            AND tipo IN ('diferenca_positiva')
            "
        );
    }

    
    /**
     * Código de ocorrências Estagiários - reduz jornada pela metade
     *
     * @param void
     *
     * @return array  Estagiarios - reduz jornada pela metade
     */
    public function EstagiariosReduzMetade($tipo = 'todos')
    {
        $array = array();

        switch ($tipo)
        {
            case 'credito':
                $array[] = "32929";
                break;

            case 'debito':
                $array[] = "12929";
                break;

            case 'zerado':
                $array[] = "02929";
                break;

            default:
                $array = array("02929", "12929", "32929");
                break;
        }

        return $array;
    }

    
    /**
     * Código de ocorrências Estagiários - reduz jornada pela metade
     *
     * @param void
     *
     * @return array  Estagiários - reduz jornada pela metade
     */
    public function EstagiariosReduzMetadeDebito()
    {
        return array("02929", "12929", "32929");
    }

    
    /**
     * Código de ocorrências Pagar em folha - não pode compensar com horas
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  Pagar em folha - não pode compensar com horas
     */
    public function PagarEmFolha($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
            $sitcad, "
            AND tratamento_debito IN ('Desconto Imediato')
            AND tipo IN ('jornada_negativa')
            "
        );
    }

    
    /**
     * Código de ocorrências Programas de Gestão INSS
     *
     * @param string/null  $sigla  Indica o programa participante
     *
     * @return array  Código programa
     */
    public function ProgramaDeGestaoINSS($sitcad = null, $sigla = null, $debito = null)
    {
        switch ($sigla)
        {
            case 'PGSP':
                if (is_null($debito))
                {
                    return array('90300', '90302', '90372');
                }
                else if ($debito == false)
                {
                    return array('90302');
                }
                else
                {
                    return array('90372');
                }
                break;

            case 'CEAP':
                return array('90301');
                break;
        }
    }


    /**
     * Código de ocorrências Eventos Esportivos - Olimpíadas e Copas do Mundo
     *
     * @param void
     *
     * @return array  $array  Eventos Esportivos - Olimpíadas e Copas do Mundo
     */
    public function EventosEsportivos($todos = null)
    {
        $array = array();

        $this->oDBase->setMensagem("Problemas no acesso a Tabela OCORRÊNCIAS (E300035.".__LINE__.").");
        $this->oDBase->query("
            SELECT
                codigo_debito  AS debito,
                codigo_credito AS credito
            FROM
                tabfacultativo172
            GROUP BY
                codigo_debito, codigo_credito
            ORDER BY
                codigo_debito, codigo_credito
        ");

        while ($codigo = $this->oDBase->fetch_object())
        {
            if (is_null($todos) || $todos == 'debito')
            {
                $array[] = $codigo->debito;
            }

            if (is_null($todos) || $todos == 'credito')
            {
                $array[] = $codigo->credito;
            }
        }

        return $array;
    }


    /**
     * Código de ocorrências Eventos Esportivos - Olimpíadas e Copas do Mundo
     *
     * @param void
     *
     * @return array  $array  Eventos Esportivos - Olimpíadas e Copas do Mundo
     */
    public function EventosEsportivosDebitoPorData($data)
    {
        $array = array();

        //Implementar busca para saber se é dia da copa
        $this->oDBase->setMensagem("Problemas no acesso a Tabela FACULTATIVO (E300041.".__LINE__.").");
        $this->oDBase->query( "
            SELECT
                codigo_debito
            FROM
                tabfacultativo172
            WHERE
                dia = :dia
                AND ativo = 'S'
        ",
        array(
            array( ':dia', conv_data($data), PDO::PARAM_STR ),
        ));

        if ($this->oDBase->num_rows() == 0)
        {
            $array[] = $this->oDBase->fetch_object()->codigo_debito;
        }

        return $array;
    }


    /**
     * Grupo de Código de ocorrências
     *
     * @param string/null  $ocor    Código de ocorrência a verificar grupo
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  $array  Grupo de Código de ocorrências
     */
    public function GrupoOcorrencia($ocor = null, $sitcad = null)
    {
        $grupo = $this->GrupoCadastral($sitcad);

        $this->oDBase->setMensagem("Problemas no acesso a Tabela OCORRÊNCIAS (E300036.".__LINE__.").");
        $this->oDBase->query("
        SELECT
            CASE
                WHEN (grupo NOT IN ('Estagiario') AND tipo IN ('todos_zerados'))                           THEN 'horarios_zerados'
                WHEN (grupo NOT IN ('Estagiario') AND tipo IN ('diferenca_negativa','diferenca_positiva')) THEN 'diferenca_de_jornada'
                WHEN (grupo NOT IN ('Estagiario') AND tipo IN ('diferenca_zerada'))                        THEN 'diferenca_zerada'
                WHEN (tratamento_debito IN ('Desconto Imediato') AND tipo IN ('jornada_negativa'))         THEN 'jornada_negativa'
                WHEN (grupo IN ('Ambos','Estagiario'))                                                     THEN 'ocorrencia_estagiario'
                WHEN (grupo IN ('Sistema'))                                                                THEN 'ocorrencia_sistema'
                WHEN (tratamento_debito IN ('Compensavel') AND grupo NOT IN ('Estagiario','Sistema'))      THEN 'ocorrencia_compensavel'
                ELSE 'Indefinido'
            END AS grupo_ocorrencia,
            siapecad, desc_ocorr, resp, ativo, semrem, grupo, tipo, tratamento_debito
        FROM tabocfre
        WHERE
            ativo = 'S'
            AND siapecad = '" . $ocor . "'
            " . $grupo . "
        ORDER BY siapecad
        ");

        return $this->oDBase->fetch_object()->grupo_ocorrencia;
    }

    
    /**
     * Código de ocorrências - Abandono de cargo
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return object  $this->oDBase  Código de ocorrências - Por período
     */
    public function OcorrenciasAbandonoDeCargo($sitcad = null)
    {
        return $this->ExecutaPadraoSQL(
            $sitcad, "
            AND grupo_ocorrencia = 'Abandono de Cargo'
            "
        );
    }

    
    /**
     * Código de ocorrências que exigem informar horários
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     * @param string       $resp    Indica o grupo responsável pelo registro
     *                              da ocorrência, se RH (recursos humanos),
     *                              ou CH (chefia), ou AB (ambos)
     *
     * @return object  $this->oDBase  Código de ocorrências
     */
    public function OcorrenciasExigeHorarios($sitcad = null, $resp = '')
    {
        $where = "
            AND informar_horarios = 'S'
            AND tratamento_debito IN ('Compensavel')
        ";

        return $this->ExecutaPadraoSQL($sitcad, $where, $resp);
    }

    
    /**
     * Código de ocorrências que exigem justificativa
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     * @param string       $resp    Indica o grupo responsável pelo registro
     *                              da ocorrência, se RH (recursos humanos),
     *                              ou CH (chefia), ou AB (ambos)
     *
     * @return object  $this->oDBase  Código de ocorrências
     */
    public function OcorrenciasExigeJustificativa($sitcad = null, $resp = '')
    {
        $where = "
            AND justificativa = 'S'
        ";

        return $this->ExecutaPadraoSQL($sitcad, $where, $resp);
    }

    
    /**
     * Código de ocorrências - Por período
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     * @param string       $resp    Indica o grupo responsável pelo registro
     *                              da ocorrência, se RH (recursos humanos),
     *                              ou CH (chefia), ou AB (ambos)
     *
     * @return object  $this->oDBase  Código de ocorrências - Por período
     */
    public function OcorrenciasPorPeriodo($sitcad = null, $resp = '')
    {
        $abono = $this->CodigoAbonoPadrao($sitcad);

        $grupo = $this->GrupoCadastral($sitcad);

        //grava os dados anteriores
        $this->oDBase->setMensagem("Problemas no acesso a Tabela OCORRÊNCIAS (E300037.".__LINE__.").");
        $this->oDBase->query("
            SELECT
                siapecad, desc_ocorr, cod_ocorr
            FROM
                tabocfre
            WHERE
                ativo = 'S'
                AND siapecad = '-----'
                OR (informar_horarios = 'N'
                    AND justificativa = 'N'
                    AND siapecad NOT IN ('" . implode(',', $abono) . "'))
                " . (empty($resp) ? "" : " AND resp IN ('" . $resp . "','AB')") . "
                " . $grupo . "
            ORDER BY
            desc_ocorr
        ");

        return $this->oDBase;
    }

    
    /**
     * Código de ocorrências - Sistema Indisponivel
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return object  $this->oDBase  Código de ocorrências - Por período
     */
    public function OcorrenciasSistemaIndisponivel($sitcad = null)
    {
        $grupo = $this->GrupoCadastral($sitcad);

        $where = "
            AND desc_ocorr = 'Sistema Indisponivel'
        ";

        return $this->ExecutaPadraoSQL($sitcad, $where);
    }

    
    /**
     * Código de ocorrências com saldo negativo, débitos
     *
     * @param string/null  $sitcad          Indica a situação funcional
     * @param boolean      $exige_horarios  Indica que exige informar os horários
     *
     * @return array  Código de ocorrências com saldo negativo, débitos
     */
    public function GrupoOcorrenciasNegativasDebitos($sitcad = null, $exige_horarios = true)
    {
        $vetor1 = $this->CodigosCompensaveis($sitcad = null, $exige_horarios);
        $vetor2 = $this->EventosEsportivos($todos = 'debito');

        $array = array_merge($vetor1, $vetor2);

        asort($array);

        return $array;
    }

    
    /**
     * Código de ocorrências que podem ser abonadas
     *
     * @param string/null  $sitcad  Indica a situação funcional
     *
     * @return array  Código de ocorrências com saldo negativo, débitos
     */
    public function GrupoOcorrenciasPassiveisDeAbono($sitcad = null, $exige_horarios = null)
    {
        $array = array();

        $array = $this->ExecutaPadraoSQL(
                $sitcad, "
            AND abonavel = 'S'
            "
        );

        return $array;
    }

    
    /**
     * Código de ocorrências viagem a serviço
     *
     * @param string/null  $sitcad  Indica a situação funcional
     *
     * @return array  Código de ocorrências de viagem a serviço
     */
    public function GrupoOcorrenciasViagem($sitcad = null)
    {
        $array = array();

        $array = $this->ExecutaPadraoSQL(
                $sitcad, "
            AND grupo_ocorrencia IN ('Viagem a Servico')
            "
        );

        return $array;
    }

    
    /**
     * Código de ocorrências férias
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  Código de ocorrências
     */
    public function GrupoOcorrenciasFerias($sitcad = null, $resp = '')
    {
        $where = "
            AND cod_ocorr LIKE '%FERIAS%'
        ";

        return $this->ExecutaPadraoSQL($sitcad, $where, $resp);
    }

    
    /**
     * Código de ocorrências com limites de dias
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  Código de ocorrências
     */
    public function OcorrenciaLimiteDias($sitcad = null)
    {
        $array1 = array();
        $array2 = array();

        $grupo = $this->GrupoCadastral($sitcad);

        //grava os dados anteriores
        $this->oDBase->setMensagem("Problemas no acesso a Tabela OCORRÊNCIAS (E300038.".__LINE__.").");
        $this->oDBase->query("
            SELECT
              siapecad, CAST(IFNULL(prazo,0) AS UNSIGNED INTEGER) AS prazo
            FROM
              tabocfre
            WHERE
              ativo = 'S'
              AND CAST(IFNULL(prazo,0) AS UNSIGNED INTEGER) > 0
              AND grupo_ocorrencia IN ('Afastamento','Licenca')
              " . $grupo . "
            ORDER BY
                desc_ocorr
        ");

        while ($codigo = $this->oDBase->fetch_object())
        {
            $array1[] = $codigo->siapecad;
            $array2[$codigo->siapecad] = $codigo->prazo;
        }

        return array($array1, $array2);
    }

    
    /**
     * Código de ocorrências indefinidas
     * - Registro parcial,
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     * @return array  Ocorrências indefinidas
     */
    public function Indefinida($sitcad = null)
    {
        $array = $this->CodigoRegistroParcialPadrao($sitcad);

        return $array[0];
    }

    
    /**
     * Código de ocorrências todos zerados
     * - Todos os horários e jornadas ficam zeradas
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     * @return array  Ocorrências
     */
    public function TodosZerados($sitcad = null)
    {
        $array = array();

        $array = $this->ExecutaPadraoSQL(
            $sitcad, "
            AND tipo IN ('todos_zerados')
            "
        );

        return $array;
    }

    
    /**
     * Código de ocorrências jornada negativa
     * - Os valores são zerados e
     *   registra jornada negativa do servidor
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     * @return array  Ocorrências
     */
    public function JornadaNegativa($sitcad = null)
    {
        $array = array();

        $array = $this->ExecutaPadraoSQL(
            $sitcad, "
            AND tipo IN ('jornada_negativa')
            "
        );

        return $array;
    }

    
    /**
     * Código de ocorrências com diferença zerada
     * - A diferença da jornada será negativa
     *   jornada_realizada = jornada_prevista/jornada_realizada;
     *   jornada_diferenca = "00:00";
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     * @return array  Ocorrências
     */
    public function DiferencaZerada($sitcad = null)
    {
        $array = array();

        $array = $this->ExecutaPadraoSQL(
            $sitcad, "
            AND tipo IN ('diferenca_zerada')
            "
        );

        return $array;
    }

    
    /**
     * Código de ocorrências com diferença positiva
     *   jornada_realizada = jornada_prevista/jornada_realizada;
     *   jornada_diferenca = "00:00";
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  Ocorrências
     */
    public function DiferencaPositiva($sitcad = null)
    {
        $array = array();

        $array = $this->ExecutaPadraoSQL(
            $sitcad, "
            AND tipo IN ('diferenca_positiva')
            "
        );

        return $array;
    }

    
    /**
     * Código de ocorrências com diferença negativa
     *
     * @param string/null  $sitcad  Indica o grupo a que pertence o código
     *
     * @return array  Ocorrências
     */
    public function DiferencaNegativa($sitcad = null)
    {
        $array = array();

        $array = $this->ExecutaPadraoSQL(
            $sitcad, "
            AND tipo IN ('diferenca_negativa')
            "
        );

        return $array;
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
    public function CodigoSelectOcorrenciasPorPeriodo($sitcad = null)
    {
        $array = array();

        $array = $this->ExecutaPadraoSQL(
            $sitcad, "
            AND tipo IN ('diferenca_negativa')
            "
        );

        return $array;

        $grupo_situacao = (isset($sitcad) && ($sitcad == '66' || $sitcad == 'ETG') ? '"Ambos","Estagiario"' : '"Ambos","Servidor"');

        if ($por_periodo == true)
        {
            $codigo_por_periodo = ' AND oco.informar_horarios = "N" '
                . ($_SESSION['sRH'] == 'S' ? ' OR oco.siapecad="'.$codigoFrequenciaNormalPadrao[0].'"' : '');
        }
        else if ($grupo == 'historico_manutencao')
        {
            $codigo_por_periodo = ' AND oco.siapecad <> "'.$codigoBancoDeHorasDebitoPadrao[0].'" ';
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

        $html = '';
        $html .= '<SELECT id="ocor" name="ocor" size="1" class="form-control select2-single" title="Selecione a ocorrência!" ' . ($onchange == '' ? '' : 'onChange="' . $onchange . '"') . '>';
        $html .= montaSelect($valor, $sql, $tamdescr, false);
        $html .= '</SELECT>';
        if ($imprimir == true)
        {
            echo $html;
        }
        else
        {
            return $html;
        }
    }

}
