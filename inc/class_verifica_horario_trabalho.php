<?php

include_once( "config.php" );

//header( 'Content-Type: text/html;  charset=UTF-8',true);

/*
 * Verificar se os dados informados de hor�rio de trabalho
 * como in�cio, intervalo do almo�o e fim do expediente
 * est�o dentro das regras de neg�cios estabelecidas
 *
 */
class CalculaHoras
{

    /*
     * Atributos
     */
    private $oito_horas_secs = 28800; // 8 horas = 28.800 segundos
    private $intervalo_minimo;  // Intervalo m�nimo para almo�o/descanso
    private $intervalo_apurado; // Intervalo apurado dos dados do intervalo do almo�o/descanso
    private $entrada;           // hor�rio de entrada no servi�o
    private $intervalo_inicio;  // hor�rio de in�cio do intervalo para almo�o/descanso
    private $intervalo_fim;     // hor�rio de t�rmino do intervalo para almo�o/descanso
    private $saida;             // fim do expediente
    private $jornada_semanal; // jornada semanal de trabalho, em horas (inteiro). Ex: 40
    private $jornada_diaria;  // jornada diaria de trabalho (formato hh:mm). Ex: 08:00
    private $jornada_realizada; // Autoriza ou n�o o servidor/estagi�rio compensar horas devidas
    private $compensacao     = null; // Autoriza ou n�o o servidor/estagi�rio compensar horas devidas
    private $db; // instancia da class de banco de dados


    /*
     * M�todo construtor/inicial da classe
     */
    function __construct()
    {
        // instancia o BD
        $this->db = new DataBase();

        //$this->setIntervaloMinimo('01:00'); // 1 hora de almo�o (m�nimo)
        //$this->setJornada('40');            // Em horas di�rias ou semanais (40hs ou 08:00hs)
        //$this->setEntrada('08:00');         // 8 horas, in�cio do expediente
        //$this->setIntervaloInicio('12:00'); // 12 horas come�a o intervalo
        //$this->setIntervaloFim('13:00');    // 13 horas termina o intervalo
        //$this->setSaida('17:00');           // 17 horas, fim do expediente
    }

   
    /*
     * Tempo m�nimo de intervalo para o almo�o/descanso
     */
    public function setIntervaloMinimo($value = '01:00')
    {
        $this->intervalo_minimo = $value;
    }

    public function getIntervaloMinimo()
    {
        return $this->intervalo_minimo;
    }

    public function getIntervaloMinimoSecs()
    {
        return time_to_sec($this->intervalo_minimo);
    }

    
    /*
     * In�cio do expediente
     */
    public function setEntrada($value = '08:00')
    {
        $this->entrada = $value;
    }

    public function getEntrada()
    {
        return $this->entrada;
    }

    public function getEntradaSecs()
    {
        return time_to_sec($this->entrada);
    }

    
    /*
     * In�cio do intervalo para almo�o/descanso
     */
    public function setIntervaloInicio($value = '12:00')
    {
        $this->intervalo_inicio = $value;
    }

    public function getIntervaloInicio()
    {
        return $this->intervalo_inicio;
    }

    public function getIntervaloInicioSecs()
    {
        return time_to_sec($this->intervalo_inicio);
    }

    
    /*
     * Fim do intervalo para almo�o/descanso
     */
    public function setIntervaloFim($value = '13:00')
    {
        $this->intervalo_fim = $value;
    }

    public function getIntervaloFim()
    {
        return $this->intervalo_fim;
    }

    public function getIntervaloFimSecs()
    {
        return time_to_sec($this->intervalo_fim);
    }

    
    /*
     * Fim do expediente
     */
    public function setSaida($value = '08:00')
    {
        $this->saida = $value;
    }

    public function getSaida()
    {
        return $this->saida;
    }

    public function getSaidaSecs()
    {
        return time_to_sec($this->saida);
    }


    /*
     * Jornada definida para o servidor/estagi�rio
     * - Igual a Jornada Diaria, mas atualiza tamb�m a Jornada Semanal
     */
    public function setJornada($value = '08:00')
    {
        $this->setJornadaDiaria($value);
        $this->setJornadaSemanal($value);
    }

    public function getJornada()
    {
        return $this->getJornadaDiaria();
    }

    public function getJornadaSecs()
    {
        return $this->getJornadaDiariaSecs();
    }


    /*
     * Jornada di�ria definida para o servidor/estagi�rio
     */
    public function setJornadaDiaria($value = '08:00')
    {
        $this->jornada_diaria = formata_jornada_para_hhmm($value);
    }

    public function getJornadaDiaria()
    {
        return $this->jornada_diaria;
    }

    public function getJornadaDiariaSecs()
    {
        return time_to_sec(formata_jornada_para_hhmm($this->jornada_diaria));
    }


    /*
     * Jornada semanal definida para o servidor/estagi�rio
     */
    public function setJornadaSemanal($value = '40')
    {
        $this->jornada_semanal = jornada_diaria_para_semanal($value);
    }

    public function getJornadaSemanal()
    {
        return $this->jornada_semanal;
    }

    public function getJornadaSemanalSecs()
    {
        return ($this->jornada_semanal * 60 * 60);
    }


    /*
     * Autoriza ou n�o compensa��o de horas devidas
     */
    public function setCompensacao($value = '00')
    {
        $this->compensacao = $value;
    }

    public function getCompensacao()
    {
        return $this->compensacao;
    }


    /*
     * Verifica se os hor�rios informados est�o de acordo com as regras
     * - Jornada de 8hs com intervalo de 1hs, verifica se as horas
     *   calculadas fecham com a jornada prevista.
     * - Jornada inferior a 8hs, sem intervalo, verifica se as horas
     *   calculadas fecham com a jornada prevista.
     * - Se informar intervalo ser� utilizado no c�lculo para apurar se
     *   est� de acordo com a jornada prevista.
     */
    public function verificaHorarioDeTrabalho($exibe_mensagem = true)
    {
        // Verifica informa��es de hor�rio
        // ===============================
        $jornada_diaria_secs   = $this->getJornadaSecs();
        //$intervalo_minimo    = $this->getIntervaloMinimoSecs();

        // limite de horario de entrada e saida do �rg�o
        $limites_inss          = horariosLimiteINSS();
        $limite_entrada_minima = time_to_sec($limites_inss['entrada']['horario']); // registra entrada a partir deste hor�rio, ex.: 6:30
        $limite_entrada_maxima = time_to_sec($limites_inss['saida']['horario']); // registra sa�da at� este hor�rio, ex.: 22:00
        $limite_entrada        = $limite_entrada_maxima - ($jornada_diaria_secs + ($jornada_diaria_secs == $this->oito_horas_secs ? $this->getIntervaloMinimoSecs() : 0)); // limita o hor�rio m�nimo para defini��o de sa�da, ex.: 22:00 - 06:00 = 16:00
        $intervalo_minimo      = time_to_sec($limites_inss['limite_duracao_minima_almoco']['horario']);

        $msg_erro = null;
        $result   = array();

        $entrada_len          = strlen(trim($this->getEntrada()));
        $saida_len            = strlen(trim($this->getSaida()));
        $intervalo_inicio_len = strlen(trim($this->getIntervaloInicio()));
        $intervalo_inicio_len = strlen(trim($this->getIntervaloFim()));

        $horainicio      = $this->getEntradaSecs();
        $horapausa       = $this->getIntervaloInicioSecs();
        $horacontinuacao = $this->getIntervaloFimSecs();
        $horafinal       = $this->getSaidaSecs();
        ;

        $intervalo_apurado = (($horapausa <= 0 && $horacontinuacao > 0) || ($horapausa > 0 && $horacontinuacao <= 0) || $horapausa > $horacontinuacao ? $intervalo_minimo : ($horacontinuacao - $horapausa));

        $jornada = $horafinal - $horainicio - (($jornada_diaria_secs == $this->oito_horas_secs) && $intervalo_apurado < $intervalo_minimo ? $intervalo_minimo : $intervalo_apurado);

        $this->jornada_realizada = $jornada;

        if ($horainicio <= 0)
        {
            $msg_erro .= "- Entrada � obrigat�ria no formato hh:mm!\\n";
        }

        if ($horafinal <= 0)
        {
            $msg_erro .= "- Fim do expediente � obrigat�rio no formato hh:mm!\\n";
        }

        if ($horainicio < $limite_entrada_minima)
        {
            $msg_erro .= '- Entrada menor que ' . sec_to_time($limite_entrada_minima, 'hh:mm') . ' horas!\\n';
        }
        else if ($horainicio > $limite_entrada)
        {
            $msg_erro .= '- Jornada ' . ($this->getJornada()) . 'hs, entrada superior a ' . sec_to_time($limite_entrada, 'hh:mm') . ' horas!\\n   Limite m�ximo de fim do expediente ' . sec_to_time($limite_entrada_maxima, 'hh:mm') . ' horas\\n';
        }


        if (($horainicio > $horapausa && $horapausa > 0) || ($horainicio > $horacontinuacao && $horacontinuacao > 0) || $horainicio > $horafinal)
        {
            $msg_erro .= "- Entrada n�o pode ser maior que os demais hor�rios!\\n";
        }
        else if ($jornada_diaria_secs < $this->oito_horas_secs && ($horainicio > $horafinal))
        {
            $msg_erro .= "- Entrada n�o pode ser maior que hora do fim do expediente!\\n";
        }
        else if ($jornada_diaria_secs == $this->oito_horas_secs && ($horapausa <= 0))
        {
            $msg_erro .= "- In�cio do intervalo � obrigat�rio para jornada de 08:00!\\n";
        }
        else if ($jornada_diaria_secs == $this->oito_horas_secs && ($horacontinuacao <= 0))
        {
            $msg_erro .= "- Retorno do intervalo � obrigat�rio para jornada de 08:00!\\n";
        }
        else if ($horapausa > 0 && ($horacontinuacao <= 0))
        {
            $msg_erro .= "- Retorno do intervalo n�o informado!\\n";
        }
        else if ($horapausa > $horacontinuacao && $horapausa > $horafinal)
        {
            $msg_erro .= "- In�cio do intervalo deve ser menor que retorno do intervalo e fim do expediente!\\n";
        }
        else if ($horapausa > $horacontinuacao)
        {
            $msg_erro .= "- In�cio do intervalo deve ser menor que retorno do intervalo!\\n";
        }
        else if ($horapausa > $horafinal)
        {
            $msg_erro .= "- In�cio do intervalo deve ser menor que fim do expediente!\\n";
        }
        else if ($horacontinuacao > $horafinal)
        {
            $msg_erro .= "- Retorno do intervalo deve ser menor que fim do expediente!\\n";
        }
        else if ($jornada_diaria_secs == $this->oito_horas_secs && $intervalo_apurado < $intervalo_minimo)
        {
            $msg_erro .= "- Intervalo deve ser igual ou maior que " . sec_to_time($intervalo_minimo, 'hh:mm') . " hora(s)!\\n";
        }
        else if ($jornada > $jornada_diaria_secs)
        {
            $msg_erro .= "- Horas calculadas (" . sec_to_time(($jornada), 'hh:mm') . ") maior que a jornada legal (" . sec_to_time($jornada_diaria_secs, 'hh:mm') . ")!\\n";
            //$msg_erro .= '\tEntrada\t----Almo�o----\tSa�da\tHoras\\n';
            //$msg_erro .= '\t'.left($this->getEntrada(),5).'\t'.left($this->getIntervaloInicio(),5).'\t'.left($this->getIntervaloFim(),5).'\t'.left($this->getSaida(),5).'\t'.sec_to_time($horarios_tempo_total).' \\n';
        }
        else if ($jornada < $jornada_diaria_secs)
        {
            $msg_erro .= "- Horas calculadas (" . sec_to_time(($jornada), 'hh:mm') . ") menor que a jornada legal (" . sec_to_time($jornada_diaria_secs, 'hh:mm') . ")!\\n";
            //$msg_erro .= '\tEntrada\t----Almo�o----\tSa�da\tHoras\\n';
            //$msg_erro .= '\t'.left($this->getEntrada(),5).'\t'.left($this->getIntervaloInicio(),5).'\t'.left($this->getIntervaloFim(),5).'\t'.left($this->getSaida(),5).'\t'.sec_to_time($horarios_tempo_total).' \\n';
        }


        // Verifica autoriza��o para compensar
        //====================================
        if ($this->getCompensacao() == null || $this->getCompensacao() == '00' || $this->getCompensacao() == '9')
        {
            $msg_erro .= "- Autorizar ou n�o compensa��o de horas devidas!\\n";
        }

        if ($msg_erro != null)
        {
            if ($exibe_mensagem == true)
            {
                mensagem($msg_erro);
                return false;
            }
            else
            {
                return $msg_erro;
            }
        }
        else
        {
            return true;
        }
    }

    
    /*
     * Verifica se os hor�rios informados est�o de acordo com as regras
     * - Jornada realizada de 8hs com intervalo de 1hs, verifica se as horas
     *   calculadas fecham com a jornada prevista.
     * - Jornada realizada inferior a 8hs, sem intervalo, verifica se as horas
     *   calculadas fecham 7hs, se maior, deduz 3 horas.
     */
    public function calculaJornadaRealizada()
    {
        //
    }
}
