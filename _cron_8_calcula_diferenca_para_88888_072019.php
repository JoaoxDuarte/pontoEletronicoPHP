<?php

/*
 ******************************************************************************
 *                                                                            *
 *  Calculo da diferença devida, para registros com código 88888              *
 *  de acordo com o mes e ano do registro da frequência                       *
 *                                                                            *
 ******************************************************************************
 */

include_once( 'config.php' );


/**  @Class
* +--------------------------------------------------------------------------+
* | @Class    : CalculoDiferenca88888                                        |
* | @description : Apura horas trabalhadas em dia com registro parcial       |
* |                (88888), registra diferença calculada (negativa) em       |
* |                dia anterior e não alteradas pela chefia imediata         |
* |                                                                          |
* | @author Edinalvo Rosa e Weverson Pereira                                 |
* | @link /sisref/_cron_8_calcula_diferenca_para_88888_072019.php?m=8&y=2019 |
* +--------------------------------------------------------------------------+
**/
class CalculoDiferenca88888
{
    private $mes;
    private $ano;

    public function __construct ()
    {
        /* Recebe $_GET no formato MMAAAA, exemplo 082019 */
        $options = array('options' => array('min_range' => 0, 'max_range' => 12 ));
        if (($this->mes = str_pad(filter_input(INPUT_GET, 'm', FILTER_VALIDATE_INT, $options), 2, '0', STR_PAD_LEFT))
            && ($this->ano = filter_input(INPUT_GET, 'y', FILTER_VALIDATE_INT))) {
            $this->PrintMessage( "Parametro do mes e ano informado: ".$this->mes.$this->ano);
        } else {
            $this->PrintMessage( "Erro nos parametros." );
        }
    }

    public function executa()
    {
        $this->GravaHistorico88888();
        $this->CalculaDiferenca88888();
        $this->AtualizaCampoDiferenca88888();
        $this->RegistraNoHistoricoPontoDiferenca88888();
        $this->AlteraPontoDiferenca88888();
    }


    /*
     * OCORRENCIAS 88888 PENDENTES, PARA REGISTRO NO HISTORICO
     * ANTES DA ATUALIZACAO DO PONTOmmAAAA APOS CALCULO DA
     * DIFERENCA EXISTENTE, NOS CASOS DE SALDOS NEGATIVOS
    */
    private function GravaHistorico88888()
    {
        $oDBase = new DataBase();

        $sql = "SHOW TABLES LIKE 'ponto".$this->mes.$this->ano."' ";
        $oDBase->query( $sql );

        $num = $oDBase->num_rows();

        if ($num > 0)
        {
            $sql = "REPLACE INTO _ocorrencias_88888_para_historico_antes_do_calculo_".$this->ano." (
                        dia, siape, entra, intini, intsai, sai, jornd, jornp, jorndif, oco, just,seq,
                        idreg, ip, ip2, ip3, ip4, justchef, ipch, iprh, matchef, siaperh
                    )
                    SELECT pto.* FROM ponto".$this->mes.$this->ano." AS pto LEFT JOIN servativ AS cad ON pto.siape = cad.mat_siape WHERE pto.oco = '88888' 
                    AND DATE_FORMAT(pto.dia,'%m%Y') ='".$this->mes.$this->ano."' AND cad.excluido = 'N' AND cad.cod_sitcad NOT IN ('02','08','15','18') 
                    ORDER BY pto.siape ";
            $oDBase->query( $sql );
        }

        $num = $oDBase->num_rows();

        if ($num > 0)
        {
            $this->PrintMessage( "Sucesso GravaHistorico88888()." );
        }
        else if ($num == 0)
        {
            $this->PrintMessage( "Sem registro para GravaHistorico88888()." );
        }
        else
        {
            $this->PrintMessage( "Erro ao executar GravaHistorico88888()." );
        }
    }


    /*
     * OCORRENCIAS 88888 PENDENTES
     * - CALCULO DAS DIFERENCAS EXISTENTES
    */
    private function CalculaDiferenca88888()
    {
        $oDBase = new DataBase();

        $sql = "SHOW TABLES LIKE 'ponto".$this->mes.$this->ano."' ";
        $oDBase->query( $sql );

        $num = $oDBase->num_rows();

        if ($num > 0)
        {
            $sql = "REPLACE INTO _ocorrencias_88888_calculo_diferenca_".$this->ano." (
                        dia, siape, entra, intini, intsai, sai, jornd, jornp,
                        jorndif, oco, jorn_d, jorn_p, sinal, jorn_dif
                    )
                    SELECT
                    pto.dia, pto.siape, pto.entra, pto.intini, pto.intsai, pto.sai, pto.jornd, pto.jornp, pto.jorndif, pto.oco,
                    SEC_TO_TIME(
                    (
                        IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,
                            TIME_TO_SEC('00:00:00'),
                            IF((TIME_TO_SEC(intsai)<>0 AND TIME_TO_SEC(sai)=0) OR TIME_TO_SEC(sai)=0,
                                TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)),
                                (TIME_TO_SEC(LEFT(sai,5))-TIME_TO_SEC(LEFT(intsai,5)) + TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)))
                            )
                        )
                        -
                        IF(IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,TIME_TO_SEC(LEFT(jornp,5)),IF((TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0) OR TIME_TO_SEC(sai)=0,TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)),TIME_TO_SEC(LEFT(sai,5))-TIME_TO_SEC(LEFT(intsai,5)) + TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)))) > TIME_TO_SEC('07:01'), IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,0,TIME_TO_SEC('03:00')),TIME_TO_SEC('00:00')))
                    ) AS jorn_d,
        
                    jornp AS jorn_p,
        
                    IF(LEFT(SEC_TO_TIME(
                    (
                        IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,
                            TIME_TO_SEC('00:00:00'),
                            IF((TIME_TO_SEC(intsai)<>0 AND TIME_TO_SEC(sai)=0) OR TIME_TO_SEC(sai)=0,
                                TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)),
                                (TIME_TO_SEC(LEFT(sai,5))-TIME_TO_SEC(LEFT(intsai,5)) + TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)))
                            )
                        )
                        -
                        IF(IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,TIME_TO_SEC(LEFT(jornp,5)),IF((TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0) OR TIME_TO_SEC(sai)=0,TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)),TIME_TO_SEC(LEFT(sai,5))-TIME_TO_SEC(LEFT(intsai,5)) + TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)))) > TIME_TO_SEC('07:01'), IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,0,TIME_TO_SEC('03:00')),TIME_TO_SEC('00:00'))
                    ) - TIME_TO_SEC(jornp)),1)='-','-','+') AS sinal,
        
                    LEFT(RIGHT(SEC_TO_TIME(
                    (
                        IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,
                            TIME_TO_SEC('00:00:00'),
                            IF((TIME_TO_SEC(intsai)<>0 AND TIME_TO_SEC(sai)=0) OR TIME_TO_SEC(sai)=0,
                                TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)),
                                (TIME_TO_SEC(LEFT(sai,5))-TIME_TO_SEC(LEFT(intsai,5)) + TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)))
                            )
                        )
                        -
                        IF(IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,TIME_TO_SEC(LEFT(jornp,5)),IF((TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0) OR TIME_TO_SEC(sai)=0,TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)),TIME_TO_SEC(LEFT(sai,5))-TIME_TO_SEC(LEFT(intsai,5)) + TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)))) > TIME_TO_SEC('07:01'), IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,0,TIME_TO_SEC('03:00')),TIME_TO_SEC('00:00'))
                    ) - TIME_TO_SEC(jornp)),8),5) AS jorn_dif
        
                    FROM ponto".$this->mes.$this->ano." AS pto LEFT JOIN servativ AS cad ON pto.siape = cad.mat_siape WHERE pto.oco = '88888' 
                    AND cad.excluido = 'N' AND cad.cod_sitcad NOT IN ('02','08','15','18') ORDER BY pto.siape
                ";
            $oDBase->query( $sql );
        }

        $num = $oDBase->num_rows();

        if ($num > 0)
        {
            $this->PrintMessage( "Sucesso CalculaDiferenca88888()." );
        }
        else if ($num == 0)
        {
            $this->PrintMessage( "Sem registro para CalculaDiferenca88888()." );
        }
        else
        {
            $this->PrintMessage( "Erro ao executar CalculaDiferenca88888()." );
        }
    }

    /*
     * OCORRENCIAS 88888 PENDENTES ATUALIZACAO DO JORNDIF COM O
     * RESULTADO DO CALCULO DAS DIFEREN?AS NEGATIVAS EXISTENTES
    */
    private function AtualizaCampoDiferenca88888()
    {
        $oDBase = new DataBase();

        $sql = "
        UPDATE _ocorrencias_88888_calculo_diferenca_".$this->ano." AS pto
        SET
            pto.jornd = LEFT(pto.jorn_d,5),
            pto.jorndif = pto.jorn_dif
        WHERE pto.oco = '88888' 
          AND pto.sinal = '-';
        ";
        $oDBase->query( $sql );

        $num = $oDBase->num_rows();

        if ($num > 0)
        {
            $this->PrintMessage( "Sucesso AtualizaCampoDiferenca88888()." );
        }
        else if ($num == 0)
        {
            $this->PrintMessage( "Sem registro para AtualizaCampoDiferenca88888()." );
        }
        else
        {
            $this->PrintMessage( "Erro ao executar AtualizaCampoDiferenca88888()." );
        }
    }


    /*
     * REGISTRO NO HISTORICO DO PONTO DA FREQUENCIA QUE SOFRERAO
     * ALTERACAO COM A ATUALIZACAO DO JORNDIF DO PONTO COM O
     * RESULTADO DO CALCULO DAS DIFERENCAS NEGATIVAS EXISTENTES
    */
    private function RegistraNoHistoricoPontoDiferenca88888()
    {
        $oDBase = new DataBase();

        $sql = "
        REPLACE INTO histponto".$this->mes.$this->ano." (
            dia, siape, entra, intini, intsai, sai,  jornd, jornp, jorndif, oco, idreg,
            ip, ip2, ip3, ip4, ipch, iprh,  matchef, siaperh, diaalt, horaalt,
            siapealt, ipalt, idaltexc, just, justchef
        )
        SELECT
            pto.dia, pto.siape, pto.entra, pto.intini, pto.intsai, pto.sai, pto.jornd, pto.jornp, pto.jorndif, pto.oco, pto.idreg, pto.ip, pto.ip2, pto.ip3, pto.ip4, pto.ipch, pto.iprh, pto.matchef, pto.siaperh, DATE_FORMAT(NOW(),'%Y-%m-%d') AS diaalt, DATE_FORMAT(NOW(),'%H:%i:%s') AS horaalt, 'XXXXXXX' AS siapealt, '' AS ipalt, 'A' AS idaltexc, pto.just, pto.justchef
        FROM _ocorrencias_88888_para_historico_antes_do_calculo_".$this->ano." AS pto 
        LEFT JOIN _ocorrencias_88888_calculo_diferenca_".$this->ano." AS calc 
        ON pto.siape = calc.siape AND pto.dia=calc.dia 
        WHERE  pto.oco = '88888' 
        AND calc.sinal = '-'
        ORDER BY pto.dia
        ";

        $oDBase->query( $sql );

        $num = $oDBase->num_rows();

        if ($num > 0)
        {
            $this->PrintMessage( "Sucesso RegistraNoHistoricoPontoDiferenca88888()." );
        }
        else if ($num == 0)
        {
            $this->PrintMessage( "Sem registro para RegistraNoHistoricoPontoDiferenca88888()." );
        }
        else
        {
            $this->PrintMessage( "Erro ao executar RegistraNoHistoricoPontoDiferenca88888()." );
        }        
    }


    /*
     * OCORRENCIAS 88888 PENDENTES ATUALIZACAO DO JORNDIF COM O
     * RESULTADO DO CALCULO DAS DIFERENCAS NEGATIVAS EXISTENTES
    */
    private function AlteraPontoDiferenca88888()
    {
        $oDBase = new DataBase();

        $sql = "
        UPDATE ponto".$this->mes.$this->ano." AS pto
        LEFT JOIN _ocorrencias_88888_calculo_diferenca_".$this->ano." AS dif ON pto.siape = dif.siape AND  pto.dia = dif.dia
        SET
            pto.jornd = LEFT(dif.jorn_d,5),
            pto.jorndif = dif.jorn_dif
        WHERE pto.oco = '88888' AND dif.sinal = '-';
        ";

        $oDBase->query( $sql );
        
        $num = $oDBase->num_rows();

        if ($num > 0)
        {
            $this->PrintMessage( "Sucesso AlteraPontoDiferenca88888()." );
        }
        else if ($num == 0)
        {
            $this->PrintMessage( "Sem registro para AlteraPontoDiferenca88888()." );
        }
        else
        {
            $this->PrintMessage( "Erro ao executar AlteraPontoDiferenca88888()." );
        }            
    }


    /* Print nas messagems de respostas dos metodos para log */
    private function PrintMessage($msg)
    {
        print "[" . date("Y-m-d H:i:s:u") . "] " . $msg."</br>".PHP_EOL;
    }
}



/*
 * Calcula
 */
$calculaDiferenca88888 = new CalculoDiferenca88888();
$calculaDiferenca88888->executa();

exit();
