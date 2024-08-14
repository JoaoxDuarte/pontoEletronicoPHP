<?php

include_once( 'config.php' );


/**  @Class
* +------------------------------------------------------------------------+
* | @class : CalculoIsencaoPonto                                           |
* | @description : Atualiza as ccorrencias 44444 pendentes dos             |
* |                servidores com funcao isentos de ponto.                 |
* |                                                                        |
* | @author : Weverson Pereira                                             |
* | @link   : /sisref/_cron_9_calcula_isencao_ponto_44444.php?m=11&y=2019  |
* +------------------------------------------------------------------------+
**/
class CalculoIsencaoPonto
{
    private $mes;
    private $ano;

    public function __construct ()
    {
        /* Recebe $_GET no formato MMAAAA, exemplo 112019 */
        $options = array('options' => array('min_range' => 0, 'max_range' => 12 ));
        if (($this->mes = str_pad(filter_input(INPUT_GET, 'm', FILTER_VALIDATE_INT, $options), 2, '0', STR_PAD_LEFT))
            && ($this->ano = filter_input(INPUT_GET, 'y', FILTER_VALIDATE_INT))) {
            $this->PrintMessage( "Parametro do mes e ano informado: ".$this->mes.$this->ano);
        } else {
            $this->mes = date('m');
            $this->ano = date('Y');
            $this->PrintMessage( "Parametro do mes e ano corrente: {$this->mes}{$this->ano}." );
        }
    }

    public function executa()
    {
        $this->GravaHistorico44444();
        $this->IncluiOcorrencia44444();
        $this->AtualizaCampoOcorrencia44444();
        $this->AtualizaOcorrencia44444Cargo();
        $this->AtualizaOcorrencia44444Substituto();
    }

    /*
    * Historico de ocorrencias 44444 pendentes de atualizacao por ter funcao isenta de ponto
    */
    private function GravaHistorico44444()
    {
        $oDBase = new DataBase();

        $sql = "SHOW TABLES LIKE 'ponto{$this->mes}{$this->ano}' ";
        $oDBase->query( $sql );
        $num = $oDBase->num_rows();

        if ($num > 0)
        {
            try {
                $sql = "
                REPLACE INTO histponto{$this->mes}{$this->ano}
                (
                    histponto{$this->mes}{$this->ano}.dia,
                    histponto{$this->mes}{$this->ano}.siape,
                    histponto{$this->mes}{$this->ano}.entra,
                    histponto{$this->mes}{$this->ano}.intini,
                    histponto{$this->mes}{$this->ano}.intsai,
                    histponto{$this->mes}{$this->ano}.sai,
                    histponto{$this->mes}{$this->ano}.jornd,
                    histponto{$this->mes}{$this->ano}.jornp,
                    histponto{$this->mes}{$this->ano}.jorndif,
                    histponto{$this->mes}{$this->ano}.oco,
                    histponto{$this->mes}{$this->ano}.idreg,
                    histponto{$this->mes}{$this->ano}.ip,
                    histponto{$this->mes}{$this->ano}.ip2,
                    histponto{$this->mes}{$this->ano}.ip3,
                    histponto{$this->mes}{$this->ano}.ip4,
                    histponto{$this->mes}{$this->ano}.ipch,
                    histponto{$this->mes}{$this->ano}.iprh,
                    histponto{$this->mes}{$this->ano}.matchef,
                    histponto{$this->mes}{$this->ano}.siaperh,
                    histponto{$this->mes}{$this->ano}.diaalt,
                    histponto{$this->mes}{$this->ano}.horaalt,
                    histponto{$this->mes}{$this->ano}.siapealt,
                    histponto{$this->mes}{$this->ano}.ipalt,
                    histponto{$this->mes}{$this->ano}.idaltexc,
                    histponto{$this->mes}{$this->ano}.just,
                    histponto{$this->mes}{$this->ano}.justchef
                )
                    SELECT
                    ponto{$this->mes}{$this->ano}.dia,
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
                    '170001234567' AS siapealt,
                    '200.198.196.207' AS ipalt,
                    'I' AS idaltexc,
                    'Processamento de ocorrencias 44444' AS just,
                    'Corrigir as funcoes e cargos isentos de registro de ponto' AS justchef
                    FROM ocupantes
                    LEFT JOIN tabfunc
                        ON ocupantes.num_funcao = tabfunc.num_funcao
                    JOIN ponto{$this->mes}{$this->ano}
                        ON siape = ocupantes.MAT_SIAPE
                    JOIN isencao_ponto
                        ON isencao_ponto.codigo = tabfunc.COD_FUNCAO
                    WHERE tabfunc.resp_lot = 'S'
                      AND oco NOT IN ('44444','98989')
                    ORDER BY ocupantes.mat_siape
                    ";

                    $oDBase->query( $sql );

                    $num = $oDBase->num_rows();

                    if ($num > 0) {
                        $this->PrintMessage( "Tabela histponto{$this->mes}{$this->ano} gravado com sucesso!" );
                    } else if ($num == 0) {
                        $this->PrintMessage( "Nao ha registros de servidores para a tabela histponto{$this->mes}{$this->ano}!" );
                    } else {
                        $this->PrintMessage( "Erro ao executar o histórico de registros na tabela histponto{$this->mes}{$this->ano}." );
                    }

                } catch (Exception $e) {
                die('<pre>' . print_r($e->getMessage(), 1));
            }
        }
    }

    /*
    * Gera lista de dias para a insercao dass ocorrencias 44444 pendentes de
    * atualizacao por ter funcao isenta de ponto
    */
    private function ListaDiasMesOcorrencia44444($matricula)
    {
        try {
            $oDBase = new DataBase();

            $interval = new DateInterval('P1D');
            $first    = DateTime::createFromFormat('d/m/Y', "01/{$this->mes}/{$this->ano}");
            $interval = DateInterval::createFromDateString('1 day');
            $last     = DateTime::createFromFormat('d/m/Y', "31/{$this->mes}/{$this->ano}");

            $period = new DatePeriod($first, $interval, $last);

            $fields[] = "(`dia`,`siape`,`entra`,`intini`,`intsai`,`sai`,`jornd`,`jornp`,`jorndif`,`oco`,`just`,`seq`,`idreg`,`ip`,`ip2`,`ip3`,`ip4`,`justchef`,`ipch`,`iprh`,`matchef`,`siaperh`)";

            $sql = "REPLACE INTO `ponto{$this->mes}{$this->ano}`";
            $sql .= implode(PHP_EOL.', ', $fields).' VALUES ';
            foreach ($period as $date) {
                $sql .= $values[] = "('{$date->format('Y-m-d')}','{$matricula}','00:00:00','00:00:00','00:00:00','00:00:00','00:00','00:00','00:00','44444',NULL,'00','X','','','','',NULL,'','',NULL,NULL),";
            }

            $oDBase->query( substr( trim($sql), 0, -1) );

        } catch (Exception $e) {
            die('<pre>' . print_r($e->getMessage(), 1));
        }
    }

    /* Listar e  inserir os servidores isentos de ponto que não estão na tabela ponto */
    private function IncluiOcorrencia44444()
    {
        try {
            $oDBase = new DataBase();

            $sql = "
            SELECT DISTINCT servativ.mat_siape
            FROM servativ
            JOIN isencao_ponto
            ON isencao_ponto.codigo = servativ.cod_cargo
            WHERE servativ.mat_siape NOT IN ( SELECT DISTINCT ponto{$this->mes}{$this->ano}.siape FROM ponto{$this->mes}{$this->ano} );
            ";

            $oDBase->query( $sql );

            while ($row = $oDBase->fetch_object())
            {
                $this->ListaDiasMesOcorrencia44444( $row->mat_siape );
                $num++;
            }

            if ($num > 0) {
                $this->PrintMessage( "Servidores isentos de registro de ponto incluidos com sucesso!" );
            } else if ($num == 0) {
                $this->PrintMessage( "Servidores isentos de registro de ponto sem inclusoes!" );
            } else {
                $this->PrintMessage( "Erro ao executar a inclusao dos servidores isentos de registro de ponto!" );
            }

        } catch (Exception $e) {
            die('<pre>' . print_r($e->getMessage(), 1));
        }
    }
    /*
     * Atualiza as ocorrencias dos servidores que estao pendentes por ter funcao
     * e sao isento de registro de ponto [Funcao e DAS 4, 5 e 6 (ou correlatas)]
    */
    private function AtualizaCampoOcorrencia44444()
    {
        try {
            $oDBase = new DataBase();

            $sql = "SET SQL_SAFE_UPDATES = 0, FOREIGN_KEY_CHECKS = 0;
                    UPDATE ponto{$this->mes}{$this->ano}
                    JOIN (
                        SELECT DISTINCT
                        ocupantes.mat_siape AS siape,
                        ocupantes.nome_serv,
                        IF(ocupantes.sit_ocup=\"S\",\"SUBSTITUTO\",\"TITULAR\") AS ocupacao,
                        isencao_ponto.codigo,
                        CASE isencao_ponto.codigo
                        WHEN 'DAS1014' THEN '44456'
                        WHEN 'FPE1014' THEN '44456'
                        ELSE '44444' END oco,
                        isencao_ponto.tipo,
                        tabfunc.cod_lot,
                        tabfunc.desc_func
                        FROM ocupantes
                        LEFT JOIN tabfunc
                            ON ocupantes.num_funcao = tabfunc.num_funcao
                        JOIN ponto{$this->mes}{$this->ano}
                            ON ponto{$this->mes}{$this->ano}.siape = ocupantes.MAT_SIAPE
                        JOIN isencao_ponto
                            ON isencao_ponto.codigo = tabfunc.COD_FUNCAO
                        WHERE tabfunc.resp_lot = \"S\"
                          AND ponto{$this->mes}{$this->ano}.oco NOT IN ('44444','98989')
                        ORDER BY ocupantes.mat_siape
                    ) AS tmp_funcao
                        ON ponto{$this->mes}{$this->ano}.siape = tmp_funcao.siape
                    SET
                        ponto{$this->mes}{$this->ano}.entra='00:00:00',
                        ponto{$this->mes}{$this->ano}.intini='00:00:00',
                        ponto{$this->mes}{$this->ano}.intsai='00:00:00',
                        ponto{$this->mes}{$this->ano}.sai='00:00:00',
                        ponto{$this->mes}{$this->ano}.jornd='00:00',
                        ponto{$this->mes}{$this->ano}.jornp='00:00',
                        ponto{$this->mes}{$this->ano}.jorndif='00:00',
                        ponto{$this->mes}{$this->ano}.oco='44444',
                        ponto{$this->mes}{$this->ano}.just=NULL,
                        ponto{$this->mes}{$this->ano}.seq='00',
                        ponto{$this->mes}{$this->ano}.idreg='X',
                        ponto{$this->mes}{$this->ano}.ip='',
                        ponto{$this->mes}{$this->ano}.ip2='',
                        ponto{$this->mes}{$this->ano}.ip3='',
                        ponto{$this->mes}{$this->ano}.ip4='',
                        ponto{$this->mes}{$this->ano}.justchef=NULL,
                        ponto{$this->mes}{$this->ano}.ipch='',
                        ponto{$this->mes}{$this->ano}.iprh='',
                        ponto{$this->mes}{$this->ano}.matchef=NULL,
                        ponto{$this->mes}{$this->ano}.siaperh=NULL
                    WHERE ponto{$this->mes}{$this->ano}.oco <> tmp_funcao.oco;
                    SET SQL_SAFE_UPDATES = 1, FOREIGN_KEY_CHECKS = 1;
            ";

            $oDBase->query( $sql );

            $num = $oDBase->num_rows();

            if ($num > 0) {
                $this->PrintMessage( "Servidores isentos de registro de ponto com o tipo [Funcao e DAS 4, 5 e 6 (ou correlatas)] atualizados com sucesso!" );
            } else if ($num == 0) {
                $this->PrintMessage( "Servidores isentos de registro de ponto com o tipo [Funcao e DAS 4, 5 e 6 (ou correlatas)] sem alteracoes!" );
            } else {
                $this->PrintMessage( "Erro ao executar os servidores isentos de registro de ponto com o tipo [Funcao e DAS 4, 5 e 6 (ou correlatas)]!" );
            }

        } catch (Exception $e) {
            die('<pre>' . print_r($e->getMessage(), 1));
        }
    }

    /*
     * Atualiza as ocorrencias dos servidores que estao pendentes por ter funcao
     * e sao isento de registro de ponto por cargo
    */
    private function AtualizaOcorrencia44444Cargo()
    {
        try {
            $oDBase = new DataBase();

            $sql = "SET SQL_SAFE_UPDATES = 0, FOREIGN_KEY_CHECKS = 0;
                    UPDATE ponto{$this->mes}{$this->ano}
                    JOIN (
                        SELECT DISTINCT
                        ponto{$this->mes}{$this->ano}.siape,
                            servativ.nome_serv,
                            servativ.cod_cargo,
                            isencao_ponto.codigo,
                            isencao_ponto.texto
                        FROM ponto{$this->mes}{$this->ano}
                        JOIN servativ
                            ON ponto{$this->mes}{$this->ano}.siape = servativ.mat_siape
                        JOIN isencao_ponto
                            ON isencao_ponto.codigo = servativ.cod_cargo
                        WHERE isencao_ponto.tipo = 'Cargo'
                          AND ponto{$this->mes}{$this->ano}.oco NOT IN ('44444','98989')
                    ) AS tmp_cargo
                        ON ponto{$this->mes}{$this->ano}.siape = tmp_cargo.siape
                    SET
                        ponto{$this->mes}{$this->ano}.entra='00:00:00',
                        ponto{$this->mes}{$this->ano}.intini='00:00:00',
                        ponto{$this->mes}{$this->ano}.intsai='00:00:00',
                        ponto{$this->mes}{$this->ano}.sai='00:00:00',
                        ponto{$this->mes}{$this->ano}.jornd='00:00',
                        ponto{$this->mes}{$this->ano}.jornp='00:00',
                        ponto{$this->mes}{$this->ano}.jorndif='00:00',
                        ponto{$this->mes}{$this->ano}.oco='44444',
                        ponto{$this->mes}{$this->ano}.just=NULL,
                        ponto{$this->mes}{$this->ano}.seq='00',
                        ponto{$this->mes}{$this->ano}.idreg='X',
                        ponto{$this->mes}{$this->ano}.ip='',
                        ponto{$this->mes}{$this->ano}.ip2='',
                        ponto{$this->mes}{$this->ano}.ip3='',
                        ponto{$this->mes}{$this->ano}.ip4='',
                        ponto{$this->mes}{$this->ano}.justchef=NULL,
                        ponto{$this->mes}{$this->ano}.ipch='',
                        ponto{$this->mes}{$this->ano}.iprh='',
                        ponto{$this->mes}{$this->ano}.matchef=NULL,
                        ponto{$this->mes}{$this->ano}.siaperh=NULL;
                    SET SQL_SAFE_UPDATES = 1, FOREIGN_KEY_CHECKS = 1;
            ";

            $oDBase->query( $sql );

            $num = $oDBase->num_rows();

            if ($num > 0) {
                $this->PrintMessage( "Servidores isentos de registro de ponto com o tipo [Cargo] atualizados com sucesso!" );
            } else if ($num == 0) {
                $this->PrintMessage( "Servidores isentos de registro de ponto com o tipo [Cargo] sem alteracoes!" );
            } else {
                $this->PrintMessage( "Erro ao executar os servidores isentos de registro de ponto com o tipo [Cargo]!" );
            }
        } catch (Exception $e) {
            die('<pre>' . print_r($e->getMessage(), 1));
        }
    }

    /*
     * Atualiza as ocorrencias dos servidores que estao pendentes por ter funcao
     * e sao isento de registro de ponto por substituir [Funcao e DAS 4, 5 e 6 (ou correlatas)]
    */
    private function AtualizaOcorrencia44444Substituto()
    {
        try {
            $oDBase = new DataBase();

            $sql = "SET SQL_SAFE_UPDATES = 0, FOREIGN_KEY_CHECKS = 0;
                    UPDATE ponto{$this->mes}{$this->ano}
                    JOIN (
                        SELECT CASE substituicao.sigla
                                    WHEN 'DAS1014' THEN '44456'
                                    WHEN 'FPE1014' THEN '44456'
                                    ELSE '44444'
                                END oco,
                                substituicao.sigla,
                                substituicao.siape,
                                servativ.nome_serv,
                                substituicao.upai,
                                substituicao.inicio,
                                substituicao.fim
                         FROM substituicao
                         LEFT JOIN tabmotivo_substituicao
                             ON substituicao.motivo = tabmotivo_substituicao.codigo
                         LEFT JOIN servativ
                             ON substituicao.siape = servativ.mat_siape
                         LEFT JOIN tabfunc
                             ON substituicao.numfunc = tabfunc.num_funcao
                         LEFT JOIN tabsetor
                             ON servativ.cod_lot = tabsetor.codigo
                         JOIN isencao_ponto
                             ON isencao_ponto.codigo = substituicao.sigla
                         WHERE isencao_ponto.tipo LIKE 'Fun%o'
                           AND substituicao.situacao <> 'C'
                           AND inicio BETWEEN '{$this->ano}-{$this->mes}-01' and '{$this->ano}-{$this->mes}-31'
                         ORDER BY servativ.nome_serv
                    ) AS tmp_substituto
                        ON ponto{$this->mes}{$this->ano}.siape = tmp_substituto.siape
                    SET
                        ponto{$this->mes}{$this->ano}.entra='00:00:00',
                        ponto{$this->mes}{$this->ano}.intini='00:00:00',
                        ponto{$this->mes}{$this->ano}.intsai='00:00:00',
                        ponto{$this->mes}{$this->ano}.sai='00:00:00',
                        ponto{$this->mes}{$this->ano}.jornd='00:00',
                        ponto{$this->mes}{$this->ano}.jornp='00:00',
                        ponto{$this->mes}{$this->ano}.jorndif='00:00',
                        ponto{$this->mes}{$this->ano}.oco=tmp_substituto.oco,
                        ponto{$this->mes}{$this->ano}.just=NULL,
                        ponto{$this->mes}{$this->ano}.seq='00',
                        ponto{$this->mes}{$this->ano}.idreg='X',
                        ponto{$this->mes}{$this->ano}.ip='',
                        ponto{$this->mes}{$this->ano}.ip2='',
                        ponto{$this->mes}{$this->ano}.ip3='',
                        ponto{$this->mes}{$this->ano}.ip4='',
                        ponto{$this->mes}{$this->ano}.justchef=NULL,
                        ponto{$this->mes}{$this->ano}.ipch='',
                        ponto{$this->mes}{$this->ano}.iprh='',
                        ponto{$this->mes}{$this->ano}.matchef=NULL,
                        ponto{$this->mes}{$this->ano}.siaperh=NULL;
                    SET SQL_SAFE_UPDATES = 1, FOREIGN_KEY_CHECKS = 1;
            ";

            $oDBase->query( $sql );

            $num = $oDBase->num_rows();

            if ($num > 0) {
                $this->PrintMessage( "Servidores isentos de registro de ponto com o tipo [Substituto] atualizados com sucesso!" );
            } else if ($num == 0) {
                $this->PrintMessage( "Servidores isentos de registro de ponto com o tipo [Substituto] sem alteracoes!" );
            } else {
                $this->PrintMessage( "Erro ao executar os servidores isentos de registro de ponto com o tipo [Substituto]!" );
            }
        } catch (Exception $e) {
            die('<pre>' . print_r($e->getMessage(), 1));
        }
    }

    /* Print nas mensagens de respostas dos metodos para log */
    public function PrintMessage($msg)
    {
        print "[" . date("Y-m-d H:i:s:u") . "] " . $msg ."</br>" . PHP_EOL;
    }

}


/*
 * Calcula
 */
$CalculoIsencaoPonto = new CalculoIsencaoPonto();
$CalculoIsencaoPonto->executa();

exit();

