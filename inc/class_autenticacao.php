<?php

/* -------------------------------------------------------------------------*/
/*  Desenvolvedor: Edinalvo Josiel Pereira Rosa                             */
/* -------------------------------------------------------------------------*/

/*
 * Verifica se usuário e senha são válidos no LDAP
 */
class AcessoLDAP
{
    private $ds;
    private $info;
    private $bind;

    private $usuario;
    private $usuario_ind;

    public $erro;

    public function __construct()
    {
        $this->ds          = null;
        $this->bind        = null;
        $this->usuario     = null;
        $this->usuario_ind = null;

        $this->Connect();
    }

    // CONECTAR LDAP
    public function Connect()
    {
        try
        {
            $this->ds = ldap_connect("ldap://cnsldapdf.prevnet", 389); //correio.dataprev.gov.br
        }
        catch (Exception $e)
        {
            if (strpos(strtoupper($e->__toString()), 'INVALID CREDENTIALS') !== false)
            {
                $objInfraException->lancarValidacao('Usuário ou senha inválidos.');
            }

            throw $e;
        }

        ldap_set_option($this->ds, LDAP_OPT_PROTOCOL_VERSION, 3);

        if (!$this->ds)
        {
            return false;
        }

        $this->bind = ldap_bind($this->ds);
        if (!$this->bind)
        {
            return false;
        }

        return true;
    }


    // VALIDAR USUARIO LDAP POR SIAPE/E-MAIL/CPF
    public function ValidarUsuario($usuario='',$campo='')
    {
        switch ($campo)
        {
            case 'EMAIL':
                $resultado = $this->PesquisaEmail( $usuario );
                break;
            case 'SIAPE':
                $resultado = $this->PesquisaPorSiape( $usuario );
                break;
            case 'CPF':
                $resultado = $this->PesquisaPorCPF( $usuario );
                break;
            case 'UID':
                $resultado = $this->PesquisaPorUID( $usuario );
                break;
            default:
                // PRIMEIRO BUSCA PELO E-MAIL
                $resultado = $this->PesquisaEmail( $usuario );

                if ($resultado == false)
                {
                    // SEGUNDO BUSCA PELO SIAPE
                    $resultado = $this->PesquisaPorSiape( $usuario );

                    if ($resultado == false)
                    {
                        // TERCEIRO BUSCA PELO CPF
                        $resultado = $this->PesquisaPorCPF( $usuario );

                        if ($resultado == false)
                        {
                            // QUARTO BUSCA PELO UID
                            $resultado = $this->PesquisaPorUID( $usuario );
                        }
                    }
                }

                break;
        }

        return $resultado;
    }


    /*
     * PESQUISA USUARIO (VALIDA)
     */

    // PESQUISA POR SIAPE
    public function PesquisaPorSiape($usuario='')
    {
        return $this->PesquisaEmail($usuario,$campo='employeenumber');
    }

    // PESQUISA POR CPF
    public function PesquisaPorCPF($usuario='')
    {
        return $this->PesquisaEmail($usuario,$campo='cpf');
    }

    // PESQUISA POR UID
    public function PesquisaPorUID($usuario='')
    {
        return $this->PesquisaEmail($usuario,$campo='uid');
    }

    // PESQUISA POR ÓRGÃO
    public function PesquisaPorOrgao($usuario='')
    {
        return $this->PesquisaEmail($usuario,$campo='o');
    }

    // VALIDAR USUÁRIO
    public function PesquisaEmail($usuario=null,$campo='mail')
    {
        // VERIFICA PARAMETROS
        $this->usuario = ($usuario != null ? $usuario : ($this->usuario == null ? '' : $this->usuario));

        // BUSCA USUARIO
        $search = ldap_search($this->ds, "dc=gov,dc=br", "$campo=$usuario");

        $this->info = ldap_get_entries($this->ds, $search);

        // VERIFICA SE HÁ MAIS DE UM EMAIL CADASTRADO PARA O USUÁRIO
        $this->usuario_ind = $this->LogarVerificaEmailAtivo();

        return (!empty($this->info[$this->usuario_ind][$campo][0]));
    }


    // VERIFICA SE HÁ MAIS DE UM EMAIL CADASTRADO PARA O USUÁRIO
    // RETORNA INDICE DO EMAIL ATIVO QUE CONSTA DO VETOR "INFO"
    public function LogarVerificaEmailAtivo()
    {
        $this->usuario_ind = 0;
        for ($x=0; $x < count($this->info); $x++)
        {
            if ($this->getActive($x) === 'active')
            {
                $this->usuario_ind = $x;
                break;
            }
        }

        return $this->usuario_ind;
    }


    public function getInfo($usuario=null)
    {
        if ($this->info == null)
        {
            $this->PesquisaPorSiape($usuario);
        }

        return $this->info;
    }

    public function getActive($ind=null)
    {
        return $this->getDadosEmail($ind,$campo='accountstatus');
    }

    public function getSiape($ind=null)
    {
        return $this->getDadosEmail($ind,$campo='employeenumber');
    }

    public function getCpf($ind=null)
    {
        return $this->getDadosEmail($ind,$campo='cpf');
    }

    public function getUID($ind=null)
    {
        return $this->getDadosEmail($ind,$campo='uid');
    }

    public function getMail($ind=null)
    {
        return $this->getDadosEmail($ind,$campo='mail');
    }

    public function getOrgao($ind=null)
    {
        return $this->getDadosEmail($ind,$campo='o');
    }

    public function getNome($ind=null)
    {
        return $this->getDadosEmail($ind,$campo='cn');
    }

    public function getUnidade($ind=null)
    {
        return preg_replace( "/\D/", "", $this->getDadosEmail($ind,$campo='businesscategory') );
    }

    public function getDN($ind=null)
    {
        $usuario_ind = ($ind == null ? $this->usuario_ind : $ind);
        return $this->info[$usuario_ind]['dn'];
    }

    public function getRGUF($ind=null)
    {
        $usuario_ind = ($ind == null ? $this->usuario_ind : $ind);
        return $this->getDadosEmail($ind,$campo='rguf');
    }

    public function getPrimeiroNome($ind=null)
    {
        $usuario_ind = ($ind == null ? $this->usuario_ind : $ind);
        return $this->getDadosEmail($ind,$campo='givenname');
    }

    public function getDadosEmail($ind=null,$campo='employeenumber')
    {
        $usuario_ind = ($ind == null ? $this->usuario_ind : $ind);
        return $this->info[$usuario_ind][$campo][0];
    }

    public function PrintDadosEmail($usuario_ind=null,$campo=null)
    {
        $usuario_ind = ($usuario_ind == null ? $this->usuario_ind : $usuario_ind);
        print '<pre>';
        print_r( $info );
        print '</pre>';
    }


    //VERIFICA USUARIO E SENHA DE REDE
    public function Login($formusuario=null, $formsenha=null, $campo='')
    {
        $resultado = false;

        if ($this->ValidarUsuario($formusuario, $campo) == true)
        {
            $this->bind = ldap_bind($this->ds, $this->getDN(), $formsenha);

            if (!$this->bind || !isset($this->bind))
            {
                $resultado = false;
            }
            else
            {
                $resultado = true;
            }
        }

        return $resultado;
    }

    public function Close()
    {
        ldap_close($this->ds);
    }
}


/*
 * Verifica se usuário e senha são válidos no SISREF
 */
class AcessoSISREF
{
    //VERIFICA USUARIO E SENHA - SISREF
    public function Login($siape='', $senha='')
    {
        $resultado = file_get_contents( "http://10.120.2.20/sisref/autenticacao.php?siape=$siape&senha=$senha" );
        return ($resultado == 1); // 1 : sucesso; Diferente de um usuário inválido;
    }
}

?>
