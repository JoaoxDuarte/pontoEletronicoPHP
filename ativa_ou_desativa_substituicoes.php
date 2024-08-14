<?php

include_once( 'inc/email_lib.php' );
include_once( 'config.php' );

// Define limite dura��o do processo
set_time_limit(108000);

/*
 * Calcula
 */
$verificaSubstituicoes = new VerificaSubstituicoesRegistradas();
$verificaSubstituicoes->executa();

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
        // Conex�o com o banco de dados SISREF
        $this->linkSISREF = new mysqli('10.120.2.5', 'sisref_app', 'SisReF2013app', 'sisref');
        if ($this->linkSISREF->connect_error)
        {
            comunicaErro("Falha na conex�o com Banco de Dados:<br> (" . $this->linkSISREF->connect_errno . ') ' . $this->linkSISREF->connect_error);
        }

    }

}

/**  @Class
 * +--------------------------------------------------------------------+
 * | @Class    : VerificaSubstituicoesRegistradas                       |
 * | @description : Verifica substitui��es registradas e ativa/desativa |
 * |                                                                    |
 * | Autor     : Edinalvo Rosa                                          |
 * +--------------------------------------------------------------------+
 * */
class VerificaSubstituicoesRegistradas extends ConexaoBD
{

    public function __construct()
    {
        // Conex�o com o banco de dados SISREF
        $this->conexao = new ConexaoBD();

    }

    public function executa()
    {
        // seleciona substitui��es vencidas,com situa��o A (ativa)
        $this->conexao->linkSISREF->setMensagem("Problemas no acesso a Tabela SUBSTITUI��O (E200040.".__LINE__.").");
        $dados = $this->conexao->linkSISREF->query("SELECT siape FROM substituicao WHERE situacao='A' AND DATE_FORMAT(NOW(),'%Y-%m-%d') > fim ");

        while ($oSubstituicao = $dados->fetch_object())
        {
            $siape = $oSubstituicao->siape;

            // verifica se o substituto efetivado � titular de fun��o e se � fun��o de unidade
            $this->conexao->linkSISREF->setMensagem("Problemas no acesso a Tabela OCUPANTES (E200041.".__LINE__.").");
            $result = $this->conexao->linkSISREF->query("SELECT mat_siape FROM ocupantes WHERE mat_siape='" . $siape . "' AND sit_ocup<>'S' AND resp_lot='S' ");
            $chefia = ($result->num_rows > 0 ? 'S' : 'N');

            // SERVATIV: Altera a indica��o do servidor ocupante de fun��o
            // USUARIOS: Altera a permiss�o de atua��o como chefia ou n�o
            // SUBSTITUICAO: Encerra (E) substitui��o
            $this->conexao->linkSISREF->setMensagem("Problemas no acesso a Tabela CADASTRO (E200042.".__LINE__.").");
            $this->conexao->linkSISREF->query("UPDATE servativ SET chefia='" . $chefia . "' WHERE mat_siape='" . $siape . "' ");

            $this->conexao->linkSISREF->setMensagem("Problemas no acesso a Tabela USU�RIOS (E200043.".__LINE__.").");
            $this->conexao->linkSISREF->query("UPDATE usuarios SET acesso=CONCAT(LEFT(acesso,1),'" . $chefia . "',RIGHT(acesso,LENGTH(acesso)-2)) WHERE siape='" . $siape . "' ");

            $this->conexao->linkSISREF->setMensagem("Problemas no acesso a Tabela SUBSTITUI��O (E200044.".__LINE__.").");
            $this->conexao->linkSISREF->query("UPDATE substituicao SET situacao='E' WHERE siape='" . $siape . "' AND situacao='A' AND DATE_FORMAT(NOW(),'%Y-%m-%d') > fim ");
        }

    }

}
