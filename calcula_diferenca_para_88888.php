<?php

/*
 * ****************************************************************
 *                                                               *
 *  Cálculo da diferença devida, para registros com código 88888 *
 *  No dia seguinte ao do registro da frequência                 *
 *                                                               *
 * ****************************************************************
 */

include_once( 'inc/email_lib.php' );
include_once( 'config.php' );
include_once( "class_ocorrencias_grupos.php" );

// Define limite duração do processo
set_time_limit(108000);


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
 * | @Class    : CalculoDiferenca88888                                  |
 * | @description : Apura horas trabalhadas em dia com registro parcial |
 * |                (88888), registra diferença calculada (negativa) em |
 * |                dia anterior e não alteradas pela chefia imediata   |
 * |                                                                    |
 * | Autor     : Edinalvo Rosa                                          |
 * +--------------------------------------------------------------------+
 * */
class CalculoDiferenca88888 extends ConexaoBD
{

    private $conexao;
    private $dataSys;
    private $data;
    private $mes;
    private $ano;

    public $codigoRegistroParcialPadrao;

    public function __construct()
    {
        // Conexão com o banco de dados SISREF
        $this->conexao = new ConexaoBD();
        $this->dataSys = new DateTimeSys();

        $this->data = conv_data($this->dataSys->subDia());
        $this->mes  = dataMes($this->data);
        $this->ano  = dataAno($this->data);

        ## ocorrências grupos
        $obj = new OcorrenciasGrupos();
        $this->codigoRegistroParcialPadrao = $obj->CodigoRegistroParcialPadrao($sitcad);
    }

    public function executa()
    {
        if ($this->GravaHistorico88888())
        {
            $this->CalculaDiferenca88888();
            $this->AtualizaCampoDiferenca88888();
            $this->RegistraNoHistoricoPontoDiferenca88888();
            $this->AlteraPontoDiferenca88888();
        }

    }

    /*
     * OCORRENCIAS 88888 PENDENTES, PARA REGISTRO NO HISTORICO
     * ANTES DA ATUALIZAÇÃO DO PONTOmmAAAA APÓS CÁLCULO DA
     * DIFERENÇA EXISTENTE, NOS CASOS DE SALDOS NEGATIVOS
     */

    private function GravaHistorico88888()
    {
        $sql    = "SHOW TABLES LIKE 'ponto" . $this->mes . $this->ano . "' ";
        $result = $this->conexao->linkSISREF->query($sql);
        if ($result->num_rows > 0)
        {
            $sql = "CREATE TABLE IF NOT EXISTS _ocorrencias_88888_para_historico_antes_do_calculo_" . $this->ano
              . "
            SELECT pto.*
            FROM ponto" . $this->mes . $this->ano . " AS pto
            LEFT JOIN servativ AS cad ON pto.siape = cad.mat_siape
            WHERE pto.oco IN (" . implode(',', $this->codigoRegistroParcialPadrao) . ")
                  AND pto.dia='" . $this->data . "'
                  AND cad.excluido = 'N'
                  AND cad.cod_sitcad NOT IN ('02','08','15')
            ORDER BY pto.siape ";
            $this->conexao->linkSISREF->query($sql);

            $sql = "SHOW INDEX FROM _ocorrencias_88888_para_historico_antes_do_calculo_" . $this->ano . " ";
            $result2 = $this->conexao->linkSISREF->query($sql);
            if ($result2->num_rows == 0)
            {
                $sql = "ALTER TABLE _ocorrencias_88888_para_historico_antes_do_calculo_" . $this->ano . " ADD INDEX siape (siape), ADD INDEX dia (dia), ADD INDEX oco (oco) ";
                $this->conexao->linkSISREF->query($sql);
            }
        }

        return ($result->num_rows > 0);

    }

    /*
     * OCORRENCIAS 88888 PENDENTES
     * - CÁLCULO DAS DIFERENÇAS EXISTENTES
     */

    private function CalculaDiferenca88888()
    {
        $sql = "
		CREATE TABLE IF NOT EXISTS _ocorrencias_88888_calculo_diferenca_" . $this->ano . "
		SELECT
			pto.dia, pto.siape, pto.entra, pto.intini, pto.intsai, pto.sai, pto.jornd, pto.jornp, pto.jorndif, pto.oco,
			SEC_TO_TIME(
			(
				IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,
					'00:00:00',
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
					'00:00:00',
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
					'00:00:00',
					IF((TIME_TO_SEC(intsai)<>0 AND TIME_TO_SEC(sai)=0) OR TIME_TO_SEC(sai)=0,
						TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)),
						(TIME_TO_SEC(LEFT(sai,5))-TIME_TO_SEC(LEFT(intsai,5)) + TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)))
					)
				)
				-
				IF(IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,TIME_TO_SEC(LEFT(jornp,5)),IF((TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0) OR TIME_TO_SEC(sai)=0,TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)),TIME_TO_SEC(LEFT(sai,5))-TIME_TO_SEC(LEFT(intsai,5)) + TIME_TO_SEC(LEFT(intini,5))-TIME_TO_SEC(LEFT(entra,5)))) > TIME_TO_SEC('07:01'), IF(TIME_TO_SEC(intini)=0 AND TIME_TO_SEC(intsai)=0 AND TIME_TO_SEC(sai)=0,0,TIME_TO_SEC('03:00')),TIME_TO_SEC('00:00'))
			) - TIME_TO_SEC(jornp)),8),5) AS jorn_dif

			FROM ponto" . $this->mes . $this->ano . " AS pto
      LEFT JOIN servativ AS cad ON pto.siape = cad.mat_siape
      WHERE pto.oco IN (" . implode(',', $this->codigoRegistroParcialPadrao) . ")
            AND pto.dia='" . $this->data . "'
            AND cad.excluido = 'N'
            AND cad.cod_sitcad NOT IN ('02','08','15')
      ORDER BY pto.siape
		";
        $this->conexao->linkSISREF->query($sql);

        $sql    = "SHOW INDEX FROM _ocorrencias_88888_calculo_diferenca_" . $this->ano . " ";
        $result = $this->conexao->linkSISREF->query($sql);
        if ($result->num_rows == 0)
        {
            $sql = "ALTER TABLE _ocorrencias_88888_calculo_diferenca_" . $this->ano . " ADD INDEX siape (siape), ADD INDEX dia (dia), ADD INDEX oco (oco) ";
            $this->conexao->linkSISREF->query($sql);
        }

    }

    /*
     * OCORRENCIAS 88888 PENDENTES ATUALIZAÇÃO DO JORNDIF COM O
     * RESULTADO DO CÁLCULO DAS DIFERENÇAS NEGATIVAS EXISTENTES
     */

    private function AtualizaCampoDiferenca88888()
    {
        $sql = "
		UPDATE _ocorrencias_88888_calculo_diferenca_" . $this->ano . " AS pto
		SET
			pto.jorndif = pto.jorn_dif
		WHERE
			pto.oco IN (" . implode(',', $this->codigoRegistroParcialPadrao) . ")
      AND pto.sinal = '-'
      AND pto.dia='" . $this->data . "'
		";
        $this->conexao->linkSISREF->query($sql);

    }

    /*
     * REGISTRO NO HISTORICO DO PONTO DA FREQUENCIA QUE SOFRERÃO
     * ALTERAÇÃO COM A ATUALIZAÇÃO DO JORNDIF DO PONTO COM O
     * RESULTADO DO CÁLCULO DAS DIFERENÇAS NEGATIVAS EXISTENTES
     */

    private function RegistraNoHistoricoPontoDiferenca88888()
    {
        $sql = "
		INSERT histponto" . $this->mes . $this->ano . "
		SELECT
			pto.dia, pto.siape, pto.entra, pto.intini, pto.intsai, pto.sai, pto.jornd, pto.jornp, pto.jorndif, pto.oco, pto.idreg, pto.ip, pto.ip2, pto.ip3, pto.ip4, pto.ipch, pto.iprh, pto.matchef, pto.siaperh, DATE_FORMAT(NOW(),'%Y-%m-%d') AS diaalt, DATE_FORMAT(NOW(),'%H:%i:%s') AS horaalt, 'XXXXXXX' AS siapealt, '' AS ipalt, 'A' AS idaltexc, pto.just, pto.justchef
		FROM _ocorrencias_88888_para_historico_antes_do_calculo_" . $this->ano . " AS pto
    LEFT JOIN _ocorrencias_88888_calculo_diferenca_" . $this->ano . " AS calc ON pto.siape = calc.siape AND pto.dia=calc.dia
    WHERE
      pto.oco IN (" . implode(',', $this->codigoRegistroParcialPadrao) . ")
      AND calc.sinal = '-'
      AND pto.dia='" . $this->data . "'
    ORDER BY pto.dia
		";
        $this->conexao->linkSISREF->query($sql);

    }

    /*
     * OCORRENCIAS 88888 PENDENTES ATUALIZAÇÃO DO JORNDIF COM O
     * RESULTADO DO CÁLCULO DAS DIFERENÇAS NEGATIVAS EXISTENTES
     */

    private function AlteraPontoDiferenca88888()
    {
        $sql = "
		UPDATE ponto" . $this->mes . $this->ano . " AS pto
		LEFT JOIN _ocorrencias_88888_calculo_diferenca_" . $this->ano . " AS dif ON pto.siape = dif.siape AND  pto.dia = dif.dia
		SET
			pto.jorndif = dif.jorn_dif
		WHERE
      pto.oco IN (" . implode(',', $this->codigoRegistroParcialPadrao) . ")
      AND dif.sinal = '-'
      AND pto.dia='" . $this->data . "';
		";
        $this->conexao->linkSISREF->query($sql);

    }

}


/*
 * Calcula
 */
$calculaDiferenca88888 = new CalculoDiferenca88888();
$calculaDiferenca88888->executa();

exit();
