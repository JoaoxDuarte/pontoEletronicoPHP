<?php

/*
 ******************************************************************************
 *                                                                            *
 *  Apaga tabelas temporarias utilizadas para atualizar as tabelas dos pontos *
 *  em cada competencia                                                       *
 *                                                                            *
 ******************************************************************************
 */

include_once( 'config.php' );


/**  @Class
* +--------------------------------------------------------------------+
* | @Class    : ApagaTabelasHistoricoTemp                              |
* | @description : Apaga tabelas temporarias utilizadas para atualizar |
* |                as tabelas dos pontos em cada competencia           |
* | Autor     : Edinalvo Rosa                                          |
* | Corrigido em 11/02/2020 por Weverson                               |
* +--------------------------------------------------------------------+
**/
class ApagaTabelasHistoricoTemp
{
	public function __construct ()
	{

	}

	public function executa()
	{
		// Leitura do banco de dados
		// Apaga todos os arquivos "historico_temp_???????"
		// e todos os arquivos "registro_especial_tmp_???????"
		$tb_nome_tmp = array('historico_temp_','registro_especial_tmp_');

		$oDBase = new DataBase();
		$oDBase2 = new DataBase();

        foreach ($tb_nome_tmp AS $tb_nome)
        {
    		$sql = '
    		SELECT
    			table_name
    		FROM
    			information_schema.tables
    		WHERE
    			table_name LIKE "'.$tb_nome.'%"
    		';

			$oDBase->query( $sql );

            $num = $oDBase->num_rows();

            if ($num > 0) {
				while ($row = $oDBase->fetch_object()) {
					if (substr_count($row->table_name,$tb_nome) > 0) {
						$sql = 'DROP TABLE ' . $row->table_name;
						$oDBase2->query( $sql );
						$this->PrintMessage("Tabela temporaria {$row->table_name} excluida com sucesso!");
					}
				}
            } else if ($num == 0) {
                $this->PrintMessage( "Nao ha tabelas temporarias {$tb_nome} para exclusao." );
            } else {
                $this->PrintMessage( "Erro ao executar a exclusao de tabelas temporarias {$tb_nome}." );
			}
        }
	}

	/* Print nas messagems de respostas dos metodos para log */
	private function PrintMessage($msg)
	{
		print "[" . date("Y-m-d H:i:s:u") . "] " . $msg."</br>";
	}
}


/*
 * APAGA TODOS OS ARQUIVOS "historico_temp_???????"
 */
$objApagaTabelasHistoricoTemp = new ApagaTabelasHistoricoTemp();
$objApagaTabelasHistoricoTemp->executa();

exit();
