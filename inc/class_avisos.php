<?php

$qtd               = explode('/', $_SERVER['PHP_SELF']);
$path_dots_slashes = str_repeat("../", count($qtd) - 3);

// Inicia a sessão e carrega as funções de uso geral
include_once( $path_dots_slashes . "config.php" );


/* ------------------------------------*\
  | Class de avisos e mensagens inciais|
  \*------------------------------------ */

class avisos
{

    private $limite;
    private $msgalerta;
    private $msgavisos;
    private $msghtml;
    private $tipo;
    private $largura;
    private $altura;
    private $dias_anteriores;

    //
    // Inicialização
    //
		function __construct($tp = 'S')
    {
        $this->initLimite();
        $this->initAlerta();
        $this->initAvisos();
        $this->setTipo($tp);
        $this->setDiasAnteriores();
        $this->getMensagem();

    }

    //
    // Métodos MENSAGEM
    //
		function getMensagem()
    {
        $sql = 'SELECT DATE_FORMAT(data_aviso,"%d-%m-%Y") AS dtaviso, mensagem, janela, janela_altura, alerta, ativo, DATE_FORMAT(data_expirar,"%Y%m%d") AS data_expirar, publico FROM avisos WHERE LTRIM(RTRIM(publico)) IN (' . $this->getTipo() . ') ORDER BY data_aviso DESC ';

        $oDBase  = new DataBase();
        $oDBase->query($sql);
        $tbnrows = $oDBase->num_rows();

        if ($tbnrows > 0)
        {

            $dthoje              = date('Ymd');
            $dt60dias_anteriores = inverteData(soma_dias_a_data(date('d/m/Y'), -$this->getDiasAnteriores()));
            $qtd_mensagem        = ($this->getLimite() == 0 ? 9999 : $this->getLimite());
            $lim_mensag          = 1;
            $lim_alerta          = 1;

            while (list($dtaviso, $txtaviso, $janela, $janela_altura, $alerta, $ativo, $data_expirar, $publico) = $oDBase->fetch_array())
            {
                $publico = ltrim(rtrim($publico));

                if ($alerta == 'S')
                {

                    if (($alerta == 'S') && ($ativo == 'S') && ($lim_alerta === 1) && ($data_expirar >= $dt60dias_anteriores))
                    {
                        $this->setLargura($janela);
                        $this->setAltura($janela_altura);
                        $this->setAlerta($txtaviso, $publico);
                        $lim_alerta = 0;
                    }

                    //if ($ativo == 'S' && $lim_mensag <= $qtd_mensagem && ($data_expirar >= $dt60dias_anteriores) && (substr_count($this->getTipo(),$publico) > 0))
                    if (($lim_mensag <= $qtd_mensagem && $data_expirar >= $dt60dias_anteriores) || $this->getDiasAnteriores() == 0)
                    {
                        $this->setAvisos($txtaviso, $publico);
                        $lim_mensag++;
                    }
                }
            }
        }

    }

    //
    // Métodos DIAS ANTERIORES
    //
		function setDiasAnteriores($var = 30)
    {
        $this->dias_anteriores = $var;

    }

    function getDiasAnteriores()
    {
        return $this->dias_anteriores;

    }

    //
    // Métodos LARGURA
    //
    // Atribui largura
    function setLargura($value = '495px')
    {
        $this->largura = $value;

    }

    // Pega os largura
    function getLargura()
    {
        return $this->largura;

    }

    //
    // Métodos ALTURA
    //
    // Atribui altura
    function setAltura($value = 'auto')
    {
        $this->altura = $value;

    }

    // Pega os altura
    function getAltura()
    {
        return $this->altura;

    }

    //
    // Métodos LIMITE
    //

		// Inicializa limite
    function initLimite()
    {
        $this->limite = 4;

    }

    // Atribui limite
    function setLimite($lmt = 4)
    {
        $this->limite = $lmt;

    }

    // Pega os limite
    function getLimite()
    {
        return $this->limite;

    }

    //
    // Métodos ALERTA
    //

		// Inicializa alertas
    function initAlerta()
    {
        $this->msgalerta = array();

    }

    // Atribui alertas
    function setAlerta($msg = '', $publico = '')
    {
        if (!empty($msg))
        {
            $this->msgalerta[] = array($msg, $publico);
        }

    }

    // Pega uma mensagem de alerta
    function getAlerta($ind = '')
    {
        if (empty($ind))
        {
            return '';
        }
        else
        {
            return $this->msgalerta[$ind][0];
        }

    }

    // Exibe os alertas
    function sayAlerta()
    {
        $tp    = $this->getTipo();
        $this->getMensagem();
        $msg   = $this->msgalerta;
        // $msg[$i][0] => mensagem
        // $msg[$i][1] => tipo do público alvo da mensagem
        $texto = '';
        for ($i = 0; $i < count($msg); $i++)
        {
            if ($msg[$i][1] == $tp || $msg[$i][1] == 'T')
            { // && $msg[$i][2] >= date('Ymd')) {
                $texto .= $msg[$i][0] . '<br><br>';
            }
        }
        return $texto;

    }

    //
    // Métodos AVISOS
    //

		// Inicializa avisos
    function initAvisos()
    {
        $this->msgavisos = array();

    }

    // Atribui avisos
    function setAvisos($msg = '', $publico = '')
    {
        if (!empty($msg))
        {
            $this->msgavisos[] = array($msg, $publico);
        }

    }

    // Pega os avisos
    function getAvisos($ind = 0)
    {
        $this->getMensagem();
        $msg = $this->msgavisos;
        $tp  = $this->getTipo();
        $lmt = $this->getLimite();
        $lmt = ($lmt == 0 ? (count($msg) - 1) : ($lmt - 1));
        if (soNumeros($ind) == '')
        {
            //$lmt = $ind;
        }

        // $msg[$i][1] => tipo do público alvo da mensagem
        // $msg[$i][2] => data limite para exibir
        $texto = '';
        for ($i = $ind; $i <= $lmt; $i++)
        {
            if (substr_count($tp, ($msg[$i][1] == '' ? 'xx' : $msg[$i][1])) > 0)
            {
                $texto .= "<div style='width:300px;background-color:#e2e2e2;'>&nbsp;</div>";
                $texto .= "<div style='width:300px;text-align:justify;'>" . $msg[$i][0] . "</div><br>";
            }
        }
        $texto = str_replace("\\t", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", trim($texto));
        $texto = str_replace("\\n", "<br>", trim($texto));
        $texto = str_replace("width: 450px", "width: 290px", $texto);
        $texto = str_replace("<br><br>", "<br>", $texto);
        $texto = str_replace("<br>", "<br>", $texto);

        print $texto;

    }

    // Exibe os avisos
    function sayAvisos($ind = 0)
    {
        print $this->getAvisos($ind);

    }

    //
    // Métodos TIPO
    //

		// Inicializa tipo
    function initTipo()
    {
        $this->tipo = "";

    }

    // Atribui tipo
    //
		// T: Todos
    // C: Chefia
    // R: Recursos Humanos
    // A: Chefia e Recursos Humanos
    // S: Servidores/Estagiários
    //
  function setTipo($tp = 'S')
    {
        $outros = "";
        if ($_SESSION['sAPS'] == 'S' || $_SESSION['sRH'] == 'S')
        {
            $outros = ",'A'";
            $outros .= ($_SESSION['sRH'] == 'S' ? ",'R'" : "");
            $outros .= ($_SESSION['sAPS'] == 'S' ? ",'C'" : "");
        }
        switch ($tp)
        {
            case 'R': $this->tipo = "'T','C','R','A'";
                break;
            case 'C': $this->tipo = "'T','C','A'";
                break;
            case 'S': $this->tipo = "'T','S'" . $outros;
                break;
            case 'T': $this->tipo = "'T'" . $outros;
                break;
            default: $this->tipo = $tp;
                break;
        }

    }

    // Pega o tipo
    function getTipo()
    {
        return $this->tipo;

    }

}
