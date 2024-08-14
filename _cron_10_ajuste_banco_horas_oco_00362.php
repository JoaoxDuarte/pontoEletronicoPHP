<?php

include_once( 'config.php' );


/**  @Class
* +--------------------------------------------------------------------+
* | @Class      CalculoBancoHoras                                      |
* | @description   Calcula o banco de horas das ocorrências 00362      |
* |                autorizadas para acumulo e 00356 para usufruto      |
* |                de horas                                            |
* |                                                                    |
* | @author Weverson Pereira                                           |
* | @link /sisref/_cron_10_ajuste_banco_horas_oco_00362.php?m=8&y=2019 |
* | @version 0.3.0                                                     |
* +--------------------------------------------------------------------+
**/
class CalculoBancoHoras
{
    private $mes;
    private $ano;
    private $months;

    public function __construct ()
    {
        /* Recebe $_GET no formato MMAAAA, exemplo 012020 */
        $options = array('options' => array('min_range' => 0, 'max_range' => 12 ));
        if (($this->mes = str_pad(filter_input(INPUT_GET, 'm', FILTER_VALIDATE_INT, $options), 2, '0', STR_PAD_LEFT))
            && ($this->ano = filter_input(INPUT_GET, 'y', FILTER_VALIDATE_INT))) {
            $this->PrintMessage( "Parametro do mes e ano informado: {$this->mes}{$this->ano}.");
        } else {
            $this->mes = date('m');
            $this->ano = date('Y');
            $this->PrintMessage( "Parametro do mes e ano corrente: {$this->mes}{$this->ano}." );
        }

		/* Loop nas tabelas ponto do ano setado */
		$this->months = array(
                        "01" => "Jan", "02" => "Feb", "03" => "Mar", "04" => "Apr",
                        "05" => "May", "06" => "Jun", "07" => "Jul", "08" => "Aug",
                        "09" => "Sep", "10" => "Oct", "11" => "Nov", "12" => "Dec",
                    );
    }
    
    public function execute()
    {
        $this->GravaHistoricoPonto();
        $this->AtualizaServidoresSemAutorizacao();
        $this->CalculoDiferencaJornadaPonto();
        $this->HistoricoMovimentacoesAcumulo();
        $this->CalculoBancoHorasAcumulo();
        $this->CalculoBancoHorasUsufruto();
    }
    
    /* Passo 1: Gravar os dados antes da modificacao no historico */
    private function GravaHistoricoPonto()
    {
        try 
        {
            $oDBase = new DataBase();

            $sql .= "REPLACE INTO histponto{$this->mes}{$this->ano}
                        (`dia`,`siape`,`entra`,`intini`,`intsai`,`sai`,`jornd`,`jornp`,`jorndif`,`oco`,`idreg`,
                        `ip`,`ip2`,`ip3`,`ip4`,`ipch`,`iprh`,`matchef`,`siaperh`,`diaalt`,`horaalt`,`siapealt`,
                        `ipalt`,`idaltexc`,`just`,`justchef`)
                        SELECT ponto{$this->mes}{$this->ano}.dia,
                        ponto{$this->mes}{$this->ano}.siape,
                        ponto{$this->mes}{$this->ano}.entra,
                        ponto{$this->mes}{$this->ano}.intini,
                        ponto{$this->mes}{$this->ano}.intsai,
                        ponto{$this->mes}{$this->ano}.sai,
                        ponto{$this->mes}{$this->ano}.jornd,
                        ponto{$this->mes}{$this->ano}.jornp,
                        ponto{$this->mes}{$this->ano}.jorndif,
                        ponto{$this->mes}{$this->ano}.oco,
                        ponto{$this->mes}{$this->ano}.idreg,
                        ponto{$this->mes}{$this->ano}.ip,
                        ponto{$this->mes}{$this->ano}.ip2,
                        ponto{$this->mes}{$this->ano}.ip3,
                        ponto{$this->mes}{$this->ano}.ip4,
                        ponto{$this->mes}{$this->ano}.ipch,
                        ponto{$this->mes}{$this->ano}.iprh,
                        ponto{$this->mes}{$this->ano}.matchef,
                        ponto{$this->mes}{$this->ano}.siaperh,
                        current_date() AS diaalt,
                        current_time() AS horaalt,
                        NULL AS siapealt,
                        NULL AS ipalt,
                        'A' AS idaltexc,
                        ponto{$this->mes}{$this->ano}.just,
                        ponto{$this->mes}{$this->ano}.justchef
                    FROM autorizacoes_servidores
                    RIGHT JOIN ponto{$this->mes}{$this->ano}
                        ON autorizacoes_servidores.siape = ponto{$this->mes}{$this->ano}.siape
                    JOIN servativ
                        ON servativ.mat_siape = ponto{$this->mes}{$this->ano}.siape
                    WHERE oco = '00362'
                      AND autorizacoes_servidores.siape IS NOT NULL
                      AND autorizacoes_servidores.data_fim > current_date
                      AND ponto{$this->mes}{$this->ano}.justchef IS NULL
                      AND ponto{$this->mes}{$this->ano}.matchef IS NULL;";

            $oDBase->query( $sql );

            $num = $oDBase->num_rows();

            if ($num > 0) {
                $this->PrintMessage( "Registros de autorizacoes de servidores da tabela histponto incluidos com sucesso!" );
            } else if ($num == 0) {
                $this->PrintMessage( "Nao ha registros com ocorrencia 00362 sem autorizacao da chefia na tabela ponto para o historico!" );
            } else {
                $this->PrintMessage( "Erro ao executar a inclusao de registros na tabela histponto." );
            }

        } catch (Exception $e) {
            die('<pre>' . print_r($e->getMessage(), 1));
        }
    }

    /* Passo 2: Corrigir as ocorrencias oco 00362 para 33333 dos servidores que estavam sem autorização para acumulo de horas */
    private function AtualizaServidoresSemAutorizacao()
    {
        try {
            $oDBase = new DataBase();

            $sql = "
                SELECT COUNT(0) AS contador
                FROM ponto{$this->mes}{$this->ano}
                WHERE ponto{$this->mes}{$this->ano}.oco = '00362'
                  AND jorndif = '00:00'
                ";

            $oDBase->query( $sql );

            $row = $oDBase->fetch_object();

            if($row->contador > 0){
                $sql = "
                UPDATE ponto{$this->mes}{$this->ano} JOIN (
                SELECT 
                    ponto{$this->mes}{$this->ano}.dia, 
                    ponto{$this->mes}{$this->ano}.siape, 
                    ponto{$this->mes}{$this->ano}.jornd, 
                    ponto{$this->mes}{$this->ano}.jornp, 
                    ponto{$this->mes}{$this->ano}.jorndif, 
                    ponto{$this->mes}{$this->ano}.oco
                    FROM ponto{$this->mes}{$this->ano} WHERE oco = '00362' AND jorndif = '00:00'
                ) AS tmp_ponto_oco_00362
                 ON ponto{$this->mes}{$this->ano}.siape   = tmp_ponto_oco_00362.siape
                AND ponto{$this->mes}{$this->ano}.dia     = tmp_ponto_oco_00362.dia
                SET ponto{$this->mes}{$this->ano}.jorndif = LEFT(TIMEDIFF(ponto{$this->mes}{$this->ano}.jornd,ponto{$this->mes}{$this->ano}.jornp),5),
                    ponto{$this->mes}{$this->ano}.oco     = IF(TIME_TO_SEC(TIMEDIFF(ponto{$this->mes}{$this->ano}.jornd,ponto{$this->mes}{$this->ano}.jornp))<=0, '00000', '00362');
                ";

                $oDBase->query( $sql );

                $this->PrintMessage( "Servidores sem autorizacao para acumulo de horas corrigidos para ocorrencia 33333 com sucesso!" );
            } else {
                $this->PrintMessage( "Nao ha registro de servidores sem autorizacao na tabela ponto!" );
            }

        } catch (Exception $e) {
            die('<pre>' . print_r($e->getMessage(), 1));
        }
    }

    /* Passo 3: Atualizar o campo jorndif onde não é calculada a diferença entre jornd e jornp nas tabelas ponto */
    private function CalculoDiferencaJornadaPonto()
    {
        try {
            $oDBase = new DataBase();

            $sql = "
                    SELECT COUNT(0) AS contador
                    FROM autorizacoes_servidores
                    RIGHT JOIN ponto{$this->mes}{$this->ano}
                    ON autorizacoes_servidores.siape = ponto{$this->mes}{$this->ano}.siape
                    JOIN servativ
                    ON servativ.mat_siape = ponto{$this->mes}{$this->ano}.siape
                    WHERE oco = '00362'
                      AND autorizacoes_servidores.siape IS NULL
                      AND autorizacoes_servidores.data_fim > current_date
                      AND ponto{$this->mes}{$this->ano}.justchef IS NULL
                      AND ponto{$this->mes}{$this->ano}.matchef IS NULL
            ";
            $oDBase->query( $sql );

            $row = $oDBase->fetch_object();

            if($row->contador > 0){
                $sql = "UPDATE ponto{$this->mes}{$this->ano}
                        JOIN (
                            SELECT
                            ponto{$this->mes}{$this->ano}.dia,
                            ponto{$this->mes}{$this->ano}.siape,
                            '33333' atualiza_ocorrencia
                            FROM autorizacoes_servidores
                            RIGHT JOIN ponto{$this->mes}{$this->ano}
                            ON autorizacoes_servidores.siape = ponto{$this->mes}{$this->ano}.siape
                            JOIN servativ
                            ON servativ.mat_siape = ponto{$this->mes}{$this->ano}.siape
                            WHERE oco = '00362'
                            AND autorizacoes_servidores.siape IS NULL
                            AND autorizacoes_servidores.data_fim > current_date
                            AND ponto{$this->mes}{$this->ano}.justchef IS NULL
                            AND ponto{$this->mes}{$this->ano}.matchef IS NULL
                        ) AS tmp_oco_00362
                        ON ponto{$this->mes}{$this->ano}.siape = tmp_oco_00362.siape
                        AND ponto{$this->mes}{$this->ano}.dia = tmp_oco_00362.dia
                        SET ponto{$this->mes}{$this->ano}.oco = tmp_oco_00362.atualiza_ocorrencia;";

                $oDBase->query( $sql );

                $this->PrintMessage( "Servidores com oco 00362 e jorndif 00:00 recalculados com sucesso!" );
            } else {
                $this->PrintMessage( "Nao ha registro de servidores com oco 00362 e jorndif 00:00 na tabela ponto!" );
            }

        } catch (Exception $e) {
            die('<pre>' . print_r($e->getMessage(), 1));
        }
    }

    /* Passo 4: Atualizar os historico antes do calculo de acumulos_horas a partir daS tabelas pontoMMAAAA (Ex. ponto072019) */
    private function HistoricoMovimentacoesAcumulo()
    {
        try {
            $oDBase = new DataBase();

            $sql = "REPLACE INTO historico_movimentacoes_acumulos(id, siape, acumulo, usufruto, data_movimentacao, ciclo_id) ";
            $sql .= "SELECT acumulos_horas.id,
                        acumulos_horas.siape,
                        acumulos_horas.horas,
                        acumulos_horas.usufruto,
                        current_date() data_movimentacao,
                        acumulos_horas.ciclo_id
                    FROM acumulos_horas
                    WHERE EXISTS (
                        SELECT siape
                        FROM autorizacoes_servidores
                        WHERE LEFT(data_inicio, 4) = '{$this->ano}'
                    );
                    ";

            $oDBase->query( $sql );

            $num = $oDBase->num_rows();

            if ($num > 0) {
                $this->PrintMessage( "Inclusao na tabela historico_movimentacoes_acumulos com sucesso!" );
            } else if ($num == 0) {
                $this->PrintMessage( "Nao ha registro de servidores para inclusao na historico_movimentacoes_acumulos!" );
            } else {
                $this->PrintMessage( "Erro ao executar a inclusao na historico_movimentacoes_acumulos." );
            }

        } catch (Exception $e) {
            die('<pre>' . print_r($e->getMessage(), 1));
        }
    }

    /* Passo 5: Atualizar com o calculo de acumulos_horas a partir da tabela pontoMMAAAA (Ex. ponto082019) 
    * O limite maximo de horas acumuladas e de 100:00 convertido em 360000 segundos
    * A ocorrencia é a 00362
    */
    private function CalculoBancoHorasAcumulo()
    {
        try {
            $oDBase = new DataBase();
			
			/* Inserir o registro de acumulo se não existir */
			$sql = "
            INSERT INTO acumulos_horas(siape,horas,usufruto,ciclo_id)
            SELECT DISTINCT
               autorizacoes_servidores.siape,
               0 horas,
               0 usufruto,
               autorizacoes_servidores.ciclo_id
              FROM acumulos_horas
            RIGHT JOIN autorizacoes_servidores
                ON acumulos_horas.siape = autorizacoes_servidores.siape
                AND acumulos_horas.ciclo_id = autorizacoes_servidores.ciclo_id
            RIGHT JOIN ciclos ON autorizacoes_servidores.ciclo_id = ciclos.id
            WHERE ISNULL(acumulos_horas.ciclo_id)
              AND YEAR(ciclos.data_inicio) = '{$this->ano}'
            GROUP BY autorizacoes_servidores.siape;
            ";

            $oDBase->query( $sql );

            /* Zerar os saldos para o novo calculo */
            $sql = "
            UPDATE acumulos_horas
            JOIN (
                SELECT DISTINCT
                   autorizacoes_servidores.siape,
                   0 horas,
                   0 usufruto,
                   autorizacoes_servidores.ciclo_id
                  FROM acumulos_horas
                RIGHT JOIN autorizacoes_servidores
                    ON acumulos_horas.siape = autorizacoes_servidores.siape
                    AND acumulos_horas.ciclo_id = autorizacoes_servidores.ciclo_id
                RIGHT JOIN ciclos ON autorizacoes_servidores.ciclo_id = ciclos.id
                WHERE YEAR(ciclos.data_inicio) = '{$this->ano}'
                GROUP BY autorizacoes_servidores.siape
            ) AS tmp_acumulos_horas
             ON acumulos_horas.siape    = tmp_acumulos_horas.siape
			AND acumulos_horas.ciclo_id    = tmp_acumulos_horas.ciclo_id
            SET acumulos_horas.horas    = tmp_acumulos_horas.horas,
                acumulos_horas.usufruto = tmp_acumulos_horas.usufruto;
            ";

            $oDBase->query( $sql );

            /* Atualizar os saldos com o novo calculo */
            $sql = "
            UPDATE acumulos_horas
            JOIN (
                SELECT
                acumulos_horas.id,
                ponto_oco_00362.siape,
                FLOOR(SUM(ponto_oco_00362.horas)) AS horas,
                autorizacoes_servidores.ciclo_id
                FROM (";
                     foreach($this->months as $key => $values) {
                     $sql .= "SELECT siape, oco, SUM(TIME_TO_SEC(jorndif)) AS horas FROM ponto{$key}{$this->ano} WHERE oco = '00362' GROUP BY siape";
                         if ($key < 12)
                            $sql.=" UNION ";
                     }
                 $sql.=") AS ponto_oco_00362
                JOIN acumulos_horas
                    ON ponto_oco_00362.siape = acumulos_horas.siape
                JOIN autorizacoes_servidores
                    ON acumulos_horas.siape = autorizacoes_servidores.siape
                    AND acumulos_horas.ciclo_id = autorizacoes_servidores.ciclo_id
                LEFT JOIN ciclos ON acumulos_horas.ciclo_id = ciclos.id
                WHERE NOT ISNULL(acumulos_horas.siape)
                AND NOT ISNULL(autorizacoes_servidores.ciclo_id)
                AND YEAR(ciclos.data_inicio) = '{$this->ano}'
                GROUP BY ponto_oco_00362.siape
                HAVING SUM(ponto_oco_00362.horas) <= 360000
            ) AS tmp_acumulos_horas
             ON acumulos_horas.siape    = tmp_acumulos_horas.siape
            AND acumulos_horas.ciclo_id = tmp_acumulos_horas.ciclo_id
            SET acumulos_horas.horas    = tmp_acumulos_horas.horas;
            ";

            $oDBase->query( $sql );

            $num = $oDBase->num_rows();

            if ($num == 0) {
                $this->PrintMessage( "Ocorrencia 00362 de acumulo de horas atualizada com sucesso!" );
            } else {
                $this->PrintMessage( "Erro ao executar a inclusao de acumulo de horas na tabela acumulos_horas." );
            }

        } catch (Exception $e) {
            die('<pre>' . print_r($e->getMessage(), 1));
        }
    }
    
    /* Passo 6: Atualizar o campo usufruto de acumulos_horas a partir da tabela pontoMMAAAA (Ex. ponto082019) 
    * A ocorrencia é a 00356
    */
    private function CalculoBancoHorasUsufruto()
    {
        try {
            $oDBase = new DataBase();

            $sql = "
                UPDATE acumulos_horas
                JOIN (
                    SELECT
                    acumulos_horas.id,
                    ponto_oco_00356.siape,
                    FLOOR(SUM(ponto_oco_00356.usufruto)) AS usufruto,
                    autorizacoes_servidores.ciclo_id
                    FROM (";
                         foreach($this->months as $key => $values) {
                         $sql .= "SELECT siape, oco, SUM(TIME_TO_SEC(jorndif)) AS usufruto FROM ponto{$key}{$this->ano} WHERE oco = '00356' GROUP BY siape";
                             if ($key < 12)
                                $sql.=" UNION ";
                         }
                     $sql.=") AS ponto_oco_00356
                    JOIN acumulos_horas
                        ON ponto_oco_00356.siape = acumulos_horas.siape
                    JOIN autorizacoes_servidores
                        ON acumulos_horas.siape = autorizacoes_servidores.siape
                        AND acumulos_horas.ciclo_id = autorizacoes_servidores.ciclo_id
                    LEFT JOIN ciclos ON acumulos_horas.ciclo_id = ciclos.id
                    WHERE NOT ISNULL(acumulos_horas.siape)
                    AND NOT ISNULL(autorizacoes_servidores.ciclo_id)
                    AND YEAR(ciclos.data_inicio) = '{$this->ano}'
                    GROUP BY ponto_oco_00356.siape
                    HAVING SUM(ponto_oco_00356.usufruto) <= 360000
                ) AS tmp_acumulos_horas
                 ON acumulos_horas.siape    = tmp_acumulos_horas.siape
                AND acumulos_horas.ciclo_id = tmp_acumulos_horas.ciclo_id
                SET acumulos_horas.usufruto = tmp_acumulos_horas.usufruto;
                ";

            $oDBase->query( $sql );

            $this->PrintMessage( "Ocorrencia 00356 de usufruto da tabela acumulos_horas atualizada com sucesso!" );

        } catch (Exception $e) {
            die('<pre>' . print_r($e->getMessage(), 1));
        }
    }

    /* Print nas messagems de respostas dos metodos para log */
    private function PrintMessage($msg)
    {
        print "[" . date("Y-m-d H:i:s:u") . "] " . $msg."</br>";
    }

}


/*
 * CalculoBancoHoras ocorrência 00362
 */

$CalculoBancoHoras = new CalculoBancoHoras();
$CalculoBancoHoras->execute();

exit();

