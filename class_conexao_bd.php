<?php

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
    public $linkSRAR;

    public function __construct()
    {
        // Conexão com o banco de dados SISREF
        $this->linkSISREF = new mysqli('10.120.2.5', 'sisref_app', 'SisReF2013app', 'sisref');
        if ($this->linkSISREF->connect_error)
        {
            comunicaErro("Falha na conexão com Banco de Dados:<br> (" . $this->linkSISREF->connect_errno . ') ' . $this->linkSISREF->connect_error);
            exit();
        }
        else
        {
            // Conexão com o banco de dados SRAR
            $this->linkSRAR = new mysqli('10.120.3.23', 'sisref_srar', 'sisrefapp', 'srcr');
            if ($this->linkSRAR->connect_error)
            {
                comunicaErro("Falha na conexão com Banco de Dados:<br> (" . $this->linkSRAR->connect_errno . ') ' . $this->linkSRAR->connect_error);
                exit();
            }
        }

    }

}

