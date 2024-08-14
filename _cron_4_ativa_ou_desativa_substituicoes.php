<?php

/*
 ******************************************************************************
 *                                                                            *
 *  Verifica se h� substitui��es que n�o foram desativadas, pois � necess�rio *
 *  que o usu�rio realize login no dia seguinte para que a desativa��o seja   *
 *  executada. Assim, evitamos a perman�ncia de jornada incorreta em dias     *
 *  anteriores ao primeiro login ap�s o fim da substitui��o.                  *
 *                                                                            *
 ******************************************************************************
 */

include_once( 'config.php' );


/**  @Class
* +--------------------------------------------------------------------+
* | @Class    : VerificaSubstituicoesRegistradas                       |
* | @description : Verifica substitui��es vencidas e encerr�-as        |
* |                                                                    |
* | Autor     : Edinalvo Rosa                                          |
* +--------------------------------------------------------------------+
**/
class VerificaSubstituicoesRegistradas
{
	private $conexao; // Conex�o com o banco de dados
	private $conexao2; // Conex�o com o banco de dados

	public function __construct ()
	{
		// Conex�o com o banco de dados
		$this->conexao = new DataBase();
		$this->conexao2 = new DataBase();
	}

	public function executa()
	{
		// seleciona substitui��es vencidas,com situa��o "A" (ativa)
		$this->conexao->query( "SELECT siape FROM substituicao WHERE situacao='A' AND DATE_FORMAT(NOW(),'%Y-%m-%d') > fim " );
        $dados = $this->conexao;

		while ($oSubstituicao = $dados->fetch_object())
		{
			$siape = $oSubstituicao->siape;

			// verifica se o substituto efetivado � titular de fun��o e se � fun��o de unidade
			$result = $this->conexao2->query( "SELECT mat_siape FROM ocupantes WHERE mat_siape = :siape AND sit_ocup <> 'S' AND resp_lot = 'S' ", array(
                array(':siape', $siape, PDO::PARAM_STR)
            ));

            if ($result)
            {

                $dados2 = $this->conexao2;
    			$chefia = ($dados2->num_rows() > 0 ? 'S' : 'N');

    			// SERVATIV: Altera a indica��o do servidor ocupante de fun��o
    			// USUARIOS: Altera a permiss�o de atua��o como chefia ou n�o
    			// SUBSTITUICAO: Encerra (E) substitui��o
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
