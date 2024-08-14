<?php

/*
 ******************************************************************************
 *                                                                            *
 *  Inserir código 99999 (Sem frequência) para dias sem registro              *
 *                                                                            *
 ******************************************************************************
 */

include_once( 'config.php' );

/* 120 segundos = 2 minutos */
// set_time_limit(120);

/**  @Class
* +--------------------------------------------------------------------+
* | @Class    : InserirDiasSemFrequencia                               |
* | @description : Insere código 99999 em dias sem  frequencia         |
* |                                                                    |
* | Autor     : Edinalvo Rosa                                          |
* | Modificado: Edinalvo Rosa e Weverson Pereira                       |
* | Data: 06/09/2019                                                   |
* +--------------------------------------------------------------------+
**/
class InserirDiasSemFrequencia
{
    private $conexao; // Conexão com o banco de dados
    // private $conexao2; // Conexão com o banco de dados

    public function __construct ()
    {
        // Conexão com o banco de dados
        // $this->conexao = new DataBase();
        // $this->conexao2 = new DataBase();
    }

    public function executa()
    {
        // seleciona substituições vencidas,com situação "A" (ativa)
        // $this->conexao->query( "
        // SELECT
            // usuarios.siape, servativ.cod_lot, servativ.dt_adm, servativ.oco_exclu_dt
        // FROM
            // usuarios
        // LEFT JOIN
            // servativ ON usuarios.siape = servativ.mat_siape
        // " );

        $oDBase = new DataBase();

        $sql = "SELECT
                    usuarios.siape, servativ.cod_lot, servativ.dt_adm, servativ.oco_exclu_dt
                FROM
                    usuarios
                LEFT JOIN
                    servativ ON usuarios.siape = servativ.mat_siape";
	
         echo 'Iniciando consulta...'; 
	 print('iniciando...'); 
	$oDBase->query( $sql );
	
	 
        // $dados = $this->conexao;

        while ($oDados = $oDBase->fetch_object())
        {
            inserir_dias_sem_frequencia(
                $oDados->siape,
                $dia     = date('d'),
                $mes     = '',
                $ano     = '',
                $jornada = '',
                $oDados->cod_lot,
                $nome_do_arquivo = '',
                $oDados->dt_adm,
                $oDados->oco_exclu_dt
            );
        }
// die('<pre>' . print_r($oDados, 1));
    }
}


/*
 * Calcula
 */
echo 'inicinando';
print('inicio');
$verificaSubstituicoes = new InserirDiasSemFrequencia();
$verificaSubstituicoes->executa();

exit();
