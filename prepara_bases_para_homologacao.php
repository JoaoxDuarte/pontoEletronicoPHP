<?php

/*
 * ****************************************************************
 *                                                               *
 *  Inicializa os bancos de dados para o início da homologação   *
 *  (SISREF), todo dia 1o. de cada mês às 3hs da manhã.          *
 *                                                               *
 *  Atualiza as seguintes tabela:                                *
 *    - TabValida;                                               *
 *    - ServAtiv;                                                *
 *                                                               *
 * ****************************************************************
 */

include_once( '../../sisref/inc/email_lib.php' );
include_once( '../../sisref/config.php' );

// Define limite duração do processo
set_time_limit(108000);

/*
 * ABRE para HOMOLOGAÇÃO
 */
$objAbreParaHomologacao = new AbreParaHomologacao();
$objAbreParaHomologacao->executa();

exit();

/**  @Class
 * +--------------------------------------------------------------------+
 * | @Class       : ConexaoBD                                           |
 * | @description : Conecta-se ao banco de dados                        |
 * | Autor     : Edinalvo Rosa                                          |
 * +--------------------------------------------------------------------+
 * */
class ConexaoBD
{

    public $linkSISREF;

    public function __construct()
    {
        // Conexão com o banco de dados SISREF
        $this->linkSISREF = new mysqli('10.120.2.5', 'sisref_app', 'SisReF2013app', 'sisref');
        if ($this->linkSISREF->connect_error)
        {
            comunicaErro("Falha na conexão com Banco de Dados:<br> (" . $this->linkSISREF->connect_errno . ') ' . $this->linkSISREF->connect_error);
        }

    }

}

/**  @Class
 * +--------------------------------------------------------------------+
 * | @Class    : AtualizaServativ                                       |
 * | @description : Atualiza o cadastro SISREF/SERVATIV a partir do     |
 * |                cadastro SIAPECAD/CADMES (SIAPE)                    |
 * | Autor     : Edinalvo Rosa                                          |
 * +--------------------------------------------------------------------+
 * */
class AbreParaHomologacao extends ConexaoBD
{

    private $conexao;

    public function __construct()
    {
        // Conexão com o banco de dados SISREF
        $this->conexao = new ConexaoBD();

    }

    public function executa()
    {
        $dia = date('j'); // dia do mês atual
        $mes = date('n'); // mês atual, sem zero à esquerda

        $ultimo_dia = 1; //date('t'); // últimodia do mês atual
        // cria as tabelas do ponto, histórico, feriados e frq
        if ($dia == $ultimo_dia)
        {
            $ano          = date('Y');
            $ano_anterior = date('Y') - 1;

            for ($x = 1; $x <= 12; $x++)
            {
                $mes = substr('00' . $x, -2);

                $sql = "CREATE TABLE IF NOT EXISTS histponto" . $mes . $ano . " ( PRIMARY KEY(dia,siape,diaalt,horaalt),KEY siape (siape), KEY dia (dia)) ENGINE=INNODB CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COLLATE = latin1_general_ci COMMENT = 'Histórico de frequência' SELECT dia, siape, entra, intini, intsai, sai, jornd, jornp, jorndif, oco, idreg, ip, ip2, ip3, ip4, ipch, iprh, matchef, siaperh, diaalt, horaalt, siapealt, ipalt, idaltexc, just, justchef FROM histponto12" . $ano_anterior . " WHERE 1 = 0 ";
                $this->conexao->linkSISREF->query($sql);

                $sql = "CREATE TABLE IF NOT EXISTS ponto" . $mes . $ano . " ( PRIMARY KEY(dia,siape),KEY siape (siape), KEY dia (dia), KEY oco (oco), KEY matchef (matchef), KEY siaperh (siaperh), KEY jorndif (jorndif)) ENGINE=INNODB CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COLLATE = latin1_general_ci COMMENT = '' SELECT dia, siape, entra, intini, intsai, sai, jornd, jornp, jorndif, oco, just, seq, idreg, ip, ip2, ip3, ip4, justchef, ipch, iprh, matchef, siaperh FROM ponto12" . $ano_anterior . " WHERE 1 = 0 ";
                $this->conexao->linkSISREF->query($sql);

                $sql = "CREATE TABLE IF NOT EXISTS feriados_" . $ano . " ( PRIMARY KEY(id),KEY dialot (dia, mes, lot), KEY dia (dia), KEY mes (mes), KEY descr (`desc`), KEY tipo (tipo), KEY codmun (codmun), KEY lot (lot), KEY data_feriado (data_feriado), KEY `data` (data_feriado)) ENGINE=INNODB CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COLLATE = latin1_general_ci COMMENT = 'Feriados Nacionais, Estaduais e Municipais' SELECT id, dia, mes, `desc`, lot, tipo, codmun, data_feriado, base_legal FROM feriados_" . $ano_anterior . " WHERE 1 = 0 ";
                $this->conexao->linkSISREF->query($sql);

                $sql = "CREATE TABLE IF NOT EXISTS frq" . $ano . " ( KEY siape (mat_siape), KEY compet (compet), KEY ocorrencia (cod_ocorr)) ENGINE=INNODB CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COLLATE = latin1_general_ci COMMENT = 'Ficha de Frequência - Resumo Anual' SELECT compet, dia_ini, dia_fim, cod_ocorr, mat_siape, cod_lot, dias, horas, minutos FROM frq" . $ano_anterior . " WHERE 1 = 0 ";
                $this->conexao->linkSISREF->query($sql);

                //$sql = "REVOKE ALL ON `sisref`.`histponto".$mes.$ano."` FROM 'auditoria'@'%' ";
                $sql = "GRANT SELECT ON `sisref`.`histponto" . $mes . $ano . "` TO 'auditoria'@'%' ";
                $this->conexao->linkSISREF->query($sql);

                //$sql = "REVOKE ALL ON `sisref`.`ponto".$mes.$ano."` FROM 'auditoria'@'%' ";
                $sql = "GRANT SELECT ON `sisref`.`ponto" . $mes . $ano . "` TO 'auditoria'@'%' ";
                $this->conexao->linkSISREF->query($sql);
            }

            //$sql = "REVOKE ALL ON `sisref`.`feriados_".$ano."` FROM 'auditoria'@'%' ";
            $sql = "GRANT SELECT ON `sisref`.`feriados_" . $ano . "` TO 'auditoria'@'%' ";
            $this->conexao->linkSISREF->query($sql);

            //$sql = "REVOKE ALL ON `sisref`.`frq".$ano."` FROM 'auditoria'@'%' ";
            $sql = "GRANT SELECT ON `sisref`.`frq" . $ano . "` TO 'auditoria'@'%' ";
            $this->conexao->linkSISREF->query($sql);
        }

        // abre para homologação
        if ($dia == 1)
        {
            $sysano = date('Y'); // ano atual do sistema
            $sysmes = date('n'); // mes atual do sistema

            $anoAnterior = ($sysmes == 1 ? ($sysano - 1) : $sysano);
            $mesAnterior = ($sysmes == 1 ? '12' : substr('00' . ($sysmes - 1), -2));
            $mes_ano     = $mesAnterior . $anoAnterior;

            $sql = 'UPDATE tabvalida SET ativo = "N" '; // desativa todos os meses
            $this->conexao->linkSISREF->query($sql);

            $sql = 'UPDATE tabvalida SET ativo = "S" WHERE compi = "' . $mes_ano . '" '; // ativa o mes corrente
            $this->conexao->linkSISREF->query($sql);

            $sql = 'UPDATE tabsetor SET tfreq = "N", dfreq = "N" WHERE ativo = "S" '; // registra N para tfreq e dfreq
            $this->conexao->linkSISREF->query($sql);

            $sql = 'UPDATE servativ SET freqh = "N", motidev = "" '; // registra N freqh limpa motidev
            $this->conexao->linkSISREF->query($sql);
        }

    }

}
