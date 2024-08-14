<?php

include_once("config.php");
include_once("Siape.php");

// Acentuação no response do WS para o INSERT
header('Content-Type: text/html; charset=UTF-8');

// Define limite duração do processo
ini_set('max_execution_time', 0); 

/**  @Class
* +--------------------------------------------------------------------+
* | @Class      AtualizaAfastamentosWsSiape                            |
* | @description   Importa os afastamentos do WS SIAPE, serviço        |
* |                consultaDadosAfastamentoHistorico e atualiza        |
* |                na tabela ponto do mês corrente                     |
* |                                                                    |
* | @author Weverson Pereira                                           |
* | @link sisref/_cron_12_atualiza_afastamentos_siape.php?org=54000    |
* | @version 0.2.0                                                     |
* +--------------------------------------------------------------------+
**/

class AtualizaAfastamentosWsSiape
{
	private $orgao;

	public function __construct ()
	{
		/* Recebe $_GET no formato AAAA, exemplo 2019 */
		if ($this->orgao = filter_input(INPUT_GET, 'org', FILTER_VALIDATE_INT)) {
			$this->PrintMessage("Parametro do orgao informado: ".$this->orgao);
		} elseif (strlen($this->orgao) <> 5) {
			$this->PrintMessage("Erro! Informe orgao no formato 99999 com 5 caracteres.");
		} else {
			$this->PrintMessage("Erro! Informe orgao no formato 99999.");
		}
	}

	public function executa()
	{
		// Chamada por órgão demonstrou melhor performance
		// $this->ListaServidoresPorOrgao($this->orgao);
		// $this->GravaDadosAfastamentoHistorico($this->orgao);
		$this->ConsultaDadosAfastamentoHistorico($this->orgao);
	}

	/*
	* Lista servidores por órgão
	*/
	public function ListaServidoresPorOrgao($orgao)
	{
		$oDBase = new DataBase();

		$sql = "
		SELECT DISTINCT `servativ`.`mat_siape`, `servativ`.`cpf`, LEFT(`servativ`.`mat_siape`, 5) AS `orgao_siape`
		FROM `servativ`
		WHERE LEFT(`servativ`.`mat_siape`, 5) = '{$orgao}';
		";
		$oDBase->query($sql);

		while ($row = $oDBase->fetch_object()) {
			$this->ImportaDadosAfastamentoHistorico($row->cpf, $row->orgao_siape);
			$num++;
		}
	}

	/*
	* Importa o servico consultaDadosAfastamentoHistorico do WS SIAPE
	*/
	public function ImportaDadosAfastamentoHistorico($cpf, $orgao)
	{
		// Iniciando a sessão
		if (session_status() !== PHP_SESSION_ACTIVE) {
			ob_start();
			session_start();
		}
		// Gravando valores dentro da sessão aberta
		$_SESSION['mes_inicial'] = date('m');
		$_SESSION['ano_final'] = date('Y');
		$_SESSION['mes_final'] = date('m');

		// RECUPERA O HISTORICO DE AFASTAMENTO
		$obj = new Siape();
		$dadosAfastamentoHistorico = $obj->buscarDadosAfastamentoHistorico($cpf, $orgao);
		$matricula = $dadosAfastamentoHistorico->ArrayDadosAfastamento->dadosAfastamentoPorMatricula->DadosAfastamentoPorMatricula->grMatricula;
		$ocorrencias = json_encode($dadosAfastamentoHistorico->ArrayDadosAfastamento->dadosAfastamentoPorMatricula->DadosAfastamentoPorMatricula->ocorrencias->DadosOcorrencias, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

		$oDBase = new DataBase();

		// Criar a tabela se não existir
		$sql = "
		CREATE TABLE IF NOT EXISTS `afastamentos_siape_json` (
			`siape` VARCHAR(12) NOT NULL DEFAULT '' COLLATE 'latin1_general_ci',
			`response_json` MEDIUMTEXT NOT NULL COLLATE 'utf8_unicode_ci',
			`data_importacao` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`siape`) USING BTREE
		)
		COLLATE='latin1_general_ci'
		ENGINE=InnoDB
		";
		$oDBase->query($sql);

		$sql = "REPLACE INTO `afastamentos_siape_json` (`siape`, `response_json`) VALUES ('{$matricula}','{$ocorrencias}');";
		$oDBase->query($sql);
	}

	/*
	* Grava os dados importados do servico consultaDadosAfastamentoHistorico do WS SIAPE
	*/
	public function GravaDadosAfastamentoHistorico($orgao)
	{
		$oDBase = new DataBase();
		$oDBase2 = new DataBase();

		// Cria a tabela se não existir
		$sql = "
		CREATE TABLE IF NOT EXISTS `afastamentos_siape` (
			`siape` VARCHAR(12) NOT NULL DEFAULT '' COLLATE 'latin1_general_ci',
			`cod_diploma_afastamento` VARCHAR(5) NULL DEFAULT '' COLLATE 'latin1_general_ci',
			`cod_ocorrencia` VARCHAR(5) NOT NULL DEFAULT '' COLLATE 'latin1_general_ci',
			`data_fim` VARCHAR(8) NOT NULL COLLATE 'latin1_general_ci',
			`data_ini` VARCHAR(8) NOT NULL COLLATE 'latin1_general_ci',
			`data_publicacao_afastamento` VARCHAR(8) NULL DEFAULT NULL COLLATE 'latin1_general_ci',
			`desc_diploma_afastamento` VARCHAR(100) NULL DEFAULT NULL COLLATE 'latin1_general_ci',
			`desc_ocorrencia` VARCHAR(100) NOT NULL COLLATE 'latin1_general_ci',
			`numero_diploma_afastamento` VARCHAR(100) NULL DEFAULT NULL COLLATE 'latin1_general_ci',
			`data_importacao` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			UNIQUE INDEX `ix_afastamentos_siape` (`siape`, `cod_ocorrencia`, `data_fim`) USING BTREE
		)
		COMMENT='Tabela de importacao do WSSIAPE afastamentos'
		COLLATE='latin1_general_ci'
		ENGINE=InnoDB;
		";
		$oDBase->query($sql);

		// Padroniza o JSON que eventualmente venha sem os colchetes do response
		$sql = "
		UPDATE `afastamentos_siape_json`
		SET `response_json` = CONCAT('[', `response_json`, ']')
		WHERE `response_json` NOT LIKE '%[%'
		AND `response_json` <> 'null';
		";
		$oDBase->query($sql);

		$sql = "
		SELECT * FROM `afastamentos_siape_json`
		WHERE LEFT(`afastamentos_siape_json`.`siape`, 5) = '{$orgao}'
		AND `response_json` <> 'null';
		";
		$oDBase->query($sql);

		while ($row = $oDBase->fetch_object()) {
			$matricula = $row->siape;
			$jsonData = json_decode($row->response_json, true);

			// Delimita o JSON em campos e constrói o INSERT
			// Expressão regular na $key para fazer o CamelCase para snake_case
			foreach ($jsonData as $id=>$row) {
				$insert = array();
				foreach ($row as $key=>$value) {
					$insert[addslashes(ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $key )), '_'))] = addslashes($value);
				}
				$fields = '`' . implode('`,`', array_keys($insert)) . '`';
				$values = '"' . implode('","', array_values($insert)) . '"';
				$sql = "REPLACE INTO `afastamentos_siape` (`siape`, {$fields}) VALUES ('".$matricula."', {$values});";
				$oDBase2->query($sql);
			}
			$this->PrintMessage( "Importação e carga de afastamentos da matrícula {$matricula} executada com sucesso!" );
			$num++;
		}
	}

	/* 
	* Consulta a tabela de afastamento para os lançamentos da tabela ponto
	*/
	public function ConsultaDadosAfastamentoHistorico($orgao)
	{
		$oDBase = new DataBase();
		// $orgao = substr($matricula, 0, 5);

		// Consulta as ocorrências importadas do WS para atualizar na tabela ponto
		$sql = "
		SELECT DISTINCT 
		`siape`,
		`cod_ocorrencia`,
		(STR_TO_DATE(DATE_FORMAT(CONCAT(RIGHT(`data_fim`,4),SUBSTRING(`data_fim`,3,2),LEFT(`data_fim`,2)),'%Y-%m-%d'),'%Y-%m-%d')) AS `data_fim`
		FROM `dbpro_11310_sisref`.`afastamentos_siape` 
		WHERE LEFT(`siape`, 5) = '{$orgao}'
		";

		$oDBase->query($sql);

		while ($row = $oDBase->fetch_object())
		{
			$this->AtualizaTabelaPonto($row->siape, $row->cod_ocorrencia, $row->data_fim);
			$num++;
		}
	}

	/*
	* Altera a tabela de ponto com os lançamentos da tabela afastamentos
	*/
	public function AtualizaTabelaPonto($matricula, $cod_ocorrencia, $data_fim)
	{
		$oDBase = new DataBase();

		// Gera a lista com os dias para inclusão/correção
		$start    = (new DateTime($data_fim))->modify('first day of this month');
		$end      = (new DateTime($data_fim))->modify('+1 day');
		$interval = DateInterval::createFromDateString('1 day');
		$period   = new DatePeriod($start, $interval, $end);

		$fields[] = "(`dia`,`siape`,`entra`,`intini`,`intsai`,`sai`,`jornd`,`jornp`,`jorndif`,`oco`,`just`,`seq`,`idreg`,`ip`,`ip2`,`ip3`,`ip4`,`justchef`,`ipch`,`iprh`,`matchef`,`siaperh`)";

		// $sql = "REPLACE INTO `ponto".date('m').date('Y')."`";
		$sql = "REPLACE INTO `ponto06".date('Y')."`";
		$sql .= implode(PHP_EOL.', ', $fields).' VALUES ';
		foreach ($period as $date) {
			$sql .= $values[] = "('{$date->format('Y-m-d')}','{$matricula}','00:00:00','00:00:00','00:00:00','00:00:00','00:00','00:00','00:00','{$cod_ocorrencia}',NULL,'00','W','','','','',NULL,'','',NULL,NULL),";
		}
echo $sql;
		$oDBase->query( substr( trim($sql), 0, -1) );
	}

	/*
	* Print nas mensagens de respostas dos metodos para log
	*/
	public function PrintMessage($msg)
	{
		print "[" . date("Y-m-d H:i:s.u") . "] " . $msg ."</br>" . PHP_EOL;
	}
}


/*
 * Calcula
 */
$AtualizaAfastamentosWsSiape = new AtualizaAfastamentosWsSiape();
$AtualizaAfastamentosWsSiape->executa();

// exit();
