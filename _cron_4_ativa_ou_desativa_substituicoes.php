<?php

/*
 ******************************************************************************
 *                                                                            *
 *  Verifica se há substituições que não foram desativadas, pois é necessário *
 *  que o usuário realize login no dia seguinte para que a desativação seja   *
 *  executada. Assim, evitamos a permanência de jornada incorreta em dias     *
 *  anteriores ao primeiro login após o fim da substituição.                  *
 *                                                                            *
 ******************************************************************************
 */

include_once( 'config.php' );


/**  @Class
* +--------------------------------------------------------------------+
* | @Class    : VerificaSubstituicoesRegistradas                       |
* | @description : Verifica substituições vencidas e encerrá-as        |
* |                                                                    |
* | Autor     : Edinalvo Rosa                                          |
* +--------------------------------------------------------------------+
**/
class VerificaSubstituicoesRegistradas
{
	private $conexao; // Conexão com o banco de dados
	private $conexao2; // Conexão com o banco de dados

	public function __construct ()
	{
		// Conexão com o banco de dados
		$this->conexao = new DataBase();
		$this->conexao2 = new DataBase();
	}

	public function executa()
	{
		// seleciona substituições vencidas,com situação "A" (ativa)
		$this->conexao->query( "SELECT siape FROM substituicao WHERE situacao='A' AND DATE_FORMAT(NOW(),'%Y-%m-%d') > fim " );
        $dados = $this->conexao;

		while ($oSubstituicao = $dados->fetch_object())
		{
			$siape = $oSubstituicao->siape;

			// verifica se o substituto efetivado é titular de função e se é função de unidade
			$result = $this->conexao2->query( "SELECT mat_siape FROM ocupantes WHERE mat_siape = :siape AND sit_ocup <> 'S' AND resp_lot = 'S' ", array(
                array(':siape', $siape, PDO::PARAM_STR)
            ));

            if ($result)
            {

                $dados2 = $this->conexao2;
    			$chefia = ($dados2->num_rows() > 0 ? 'S' : 'N');

    			// SERVATIV: Altera a indicação do servidor ocupante de função
    			// USUARIOS: Altera a permissão de atuação como chefia ou não
    			// SUBSTITUICAO: Encerra (E) substituição
    			$this->conexao2->query( "UPDATE servativ SET chefia = :chefia WHERE mat_siape = :siape ", array(
                    array(':siape',  $siape,  PDO::PARAM_STR),
                    array(':chefia', $chefia, PDO::PARAM_STR)
                ));

    			$this->conexao2->query( "UPDATE usuarios SET acesso = CONCAT(LEFT(acesso,1),'N',RIGHT(acesso,LENGTH(acesso)-2)) WHERE siape = :siape ", array(
                    array(':siape',  $siape,  PDO::PARAM_STR)
                ));

    			$this->conexao2->query( "UPDATE substituicao SET situacao = 'E' WHERE siape = :siape AND situacao = 'A' AND DATE_FORMAT(NOW(),'%Y-%m-%d') > fim ", array(
                    array(':siape',  $siape,  PDO::PARAM_STR)
                ));
            }
        }
	}
}


/*
 * Calcula
 */
$verificaSubstituicoes = new VerificaSubstituicoesRegistradas();
$verificaSubstituicoes->executa();

exit();
