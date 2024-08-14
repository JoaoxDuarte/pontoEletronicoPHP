<?php

$qtd               = explode('/', $_SERVER['PHP_SELF']);
$path_dots_slashes = str_repeat("../", (count($qtd) > 3 ? (count($qtd) - 3) : 0));

// Inicia a sessão e carrega as funções de uso geral
include_once( $path_dots_slashes . "config.php");

include_once( 'class_form.telas.php' );

## @class
#+-------------------------------+
#| Formulario Siape, Competencia |
#+-------------------------------+
#

class formSiapeCompetencia extends formTelas
{

    private $SiapeNome;               // string
    private $SiapeTitulo;             // string
    private $SiapeOnkeyup;            // string
    private $SiapeResponsavelNome;    // string
    private $SiapeResponsavelTitulo;  // string
    private $SiapeResponsavelOnkeyup; // string
    private $MesNome;                 // string
    private $MesTitulo;               // string
    private $MesOnkeyup;              // string
    private $MesPosicao_titulo;       // string
    private $AnoNome;                 // string
    private $AnoTitulo;               // string
    private $AnoOnkeyup;              // string
    private $AnoPosicao_titulo;       // string
    private $SiapeCompetenciaDestino; // string
    private $SiapeCompetenciaValidar; // string
    private $soMes; // boolean
    private $soAno; // boolean

    ## @constructor
    #+----------------------------+
    #| Construtor da classe       |
    #+----------------------------+

    #
    public function __construct()
    {
        parent::formTelas();
        // siape
        $this->setSiapeNome('pSiape');
        $this->setSiapeTitulo('Matr&iacute;cula');
        $this->setSiapeOnkeyup('javascript:ve(this.value);');
        $this->setSiapePosicao_titulo('topo');
        //
        // mes
        $this->setMesNome('mes3');
        $this->setMesTitulo('M&ecirc;s');
        $this->setMesOnkeyup('javascript:ve(this.value);');
        $this->setMesPosicao_titulo('topo');
        //
        // ano
        $this->setAnoNome('ano3');
        $this->setAnoTitulo('Ano');
        $this->setAnoOnkeyup('javascript:ve(this.value);');
        $this->setAnoPosicao_titulo('topo');
        //
        // indica se módulo histórico
        // para soliictar matrícula do
        // responsável pela solicitação
        $this->setSiapeResponsavelNome("");

    }

    ## @metodo
    #+----------------------------+
    #| Define/Le os atributos     |
    #+----------------------------+
    #
    //
    // SIAPE DO RESPONSÁVEL: nome da variavel, titulo do campo, funcao, posicao do titulo
    //
    public function setSiapeResponsavelNome($string = "")
    {
        $this->SiapeResponsavelNome = $string;

    }

    public function getSiapeResponsavelNome()
    {
        return $this->SiapeResponsavelNome;

    }

    public function setSiapeResponsavelTitulo($string = "")
    {
        $this->SiapeResponsavelTitulo = $string;

    }

    public function getSiapeResponsavelTitulo()
    {
        return $this->SiapeResponsavelTitulo;

    }

    public function setSiapeResponsavelOnkeyup($string = "")
    {
        $this->SiapeResponsavelOnkeyup = $string;

    }

    public function getSiapeResponsavelOnkeyup()
    {
        return $this->SiapeResponsavelOnkeyup;

    }

    public function setSiapeResponsavelPosicao_titulo($string = "")
    {
        $this->SiapeResponsavelPosicao_titulo = $string;

    }

    public function getSiapeResponsavelPosicao_titulo()
    {
        return $this->SiapeResponsavelPosicao_titulo;

    }

    //
    // SIAPE: nome da variavel, titulo do campo, funcao, posicao do titulo
    //
    public function setSiapeNome($string = "")
    {
        $this->SiapeNome = $string;

    }

    public function getSiapeNome()
    {
        return $this->SiapeNome;

    }

    public function setSiapeTitulo($string = "")
    {
        $this->SiapeTitulo = $string;

    }

    public function getSiapeTitulo()
    {
        return $this->SiapeTitulo;

    }

    public function setSiapeOnkeyup($string = "")
    {
        $this->SiapeOnkeyup = $string;

    }

    public function getSiapeOnkeyup()
    {
        return $this->SiapeOnkeyup;

    }

    public function setSiapePosicao_titulo($string = "")
    {
        $this->SiapePosicao_titulo = $string;

    }

    public function getSiapePosicao_titulo()
    {
        return $this->SiapePosicao_titulo;

    }

    //
    // MES: nome da variavel, titulo do campo, funcao, posicao do titulo
    //
    public function setMesNome($string = "")
    {
        $this->MesNome = $string;

    }

    public function getMesNome()
    {
        return $this->MesNome;

    }

    public function setMesTitulo($string = "")
    {
        $this->MesTitulo = $string;

    }

    public function getMesTitulo()
    {
        return $this->MesTitulo;

    }

    public function setMesOnkeyup($string = "")
    {
        $this->MesOnkeyup = $string;

    }

    public function getMesOnkeyup()
    {
        return $this->MesOnkeyup;

    }

    public function setMesPosicao_titulo($string = "")
    {
        $this->MesPosicao_titulo = $string;

    }

    public function getMesPosicao_titulo()
    {
        return $this->MesPosicao_titulo;

    }

    public function setSoMes($string = false)
    {
        $this->soMes = $string;

    }

    public function getSoMes()
    {
        return $this->soMes;

    }

    public function setSoAno($string = false)
    {
        $this->soAno = $string;

    }

    public function getSoAno()
    {
        return $this->soAno;

    }

    //
    // ANO: nome da variavel, titulo do campo, funcao, posicao do titulo
    //
    public function setAnoNome($string = "")
    {
        $this->AnoNome = $string;

    }

    public function getAnoNome()
    {
        return $this->AnoNome;

    }

    public function setAnoTitulo($string = "")
    {
        $this->AnoTitulo = $string;

    }

    public function getAnoTitulo()
    {
        return $this->AnoTitulo;

    }

    public function setAnoOnkeyup($string = "")
    {
        $this->AnoOnkeyup = $string;

    }

    public function getAnoOnkeyup()
    {
        return $this->AnoOnkeyup;

    }

    public function setAnoPosicao_titulo($string = "")
    {
        $this->AnoPosicao_titulo = $string;

    }

    public function getAnoPosicao_titulo()
    {
        return $this->AnoPosicao_titulo;

    }

    // Destino
    public function setSiapeCompetenciaDestino($destino = "")
    {
        $this->SiapeCompetenciaDestino = $destino;

    }

    public function getSiapeCompetenciaDestino()
    {
        return $this->SiapeCompetenciaDestino;

    }

    // Funcao de validacao
    public function setSiapeCompetenciaValidar($validar = "")
    {
        $this->SiapeCompetenciaValidar = $validar;

    }

    public function getSiapeCompetenciaValidar()
    {
        return $this->SiapeCompetenciaValidar;

    }

    // texto com informações sobre os campos
    public function setAjuda()
    {
        $this->setHTML("<div style='text-align:right;width:90%;margin:25px;font-size:9px;border:0px;'>");
        $this->setHTML("<fieldset style='border:1px solid white;text-align:left;'><legend style='font-size:12px;padding:0px;margin:0px;'><b>&nbsp;Informações&nbsp;</b></legend>");
        $this->setHTML("<p style='padding:1px;margin:0px;'><b>" . ($this->getSiapeTitulo() == '' ? 'Matrícula' : $this->getSiapeTitulo()) . "&nbsp;:&nbsp;</b><b></b>Matrícula do servidor/estagiário.</p>");

        if ($this->getSoAno() == true && $this->getSoMes() == true)
        {

        }
        else
        {
            if (($this->getMesTitulo() != '' || $this->getSoMes() == true) && $this->getSoAno() == false)
            {
                $this->setHTML("<p style='padding:1px;margin:0px;'><b>" . ($this->getMesTitulo() == '' ? 'Mês' : $this->getMesTitulo()) . "&nbsp;:&nbsp;</b>Mês da frequência.</p>");
            }

            if ($this->getAnoTitulo() != '' || $this->getSoAno() == true)
            {
                $this->setHTML("<p style='padding:1px;margin:0px;'><b>" . ($this->getAnoTitulo() == '' ? 'Ano' : $this->getAnoTitulo()) . "&nbsp;:&nbsp;</b>Ano da frequência.</p>");
            }

            if (($this->getMesTitulo() == '' && $this->getAnoTitulo() == '') && $this->getSoMes() == false && $this->getSoAno() == false)
            {
                $this->setHTML("<p style='padding:1px;margin:0px;'><b>Competência&nbsp;:&nbsp;</b>Mês e Ano da frequência.</p>");
            }
        }

        if ($this->getSiapeResponsavelTitulo() != '')
        {
            $this->setHTML("<p style='padding:1px;margin:0px;'><b>" . ($this->getSiapeResponsavelTitulo() == '' ? 'Responsável pela Homologação' : $this->getSiapeResponsavelTitulo()) . "&nbsp;:&nbsp;</b>Matrícula do solicitante das alterações;</p>");
        }
        
        
        if ($this->getObservacaoBase() != '')
        {
            $this->setHTML("<p style='padding:1px;padding-top:5px;margin:0px;font-weight:bold;font-size:12px;'>" . $this->getObservacaoBase() . "</p>");
        }
        $this->setObservacaoBase( '' );
        
        $this->setHTML("</fieldset>");
        $this->setHTML("</div>");
    }

    ## @metodo
    #+----------------------------+
    #| Exibe o formulário         |
    #+----------------------------+

    #
    public function exibeForm()
    {
        $this->exibeTopoHTML();
        $this->exibeCorpoTopoHTML();

        $this->initHTML();
        $this->setFormAction($this->getSiapeCompetenciaDestino());
        $this->setFormOnSubmit($this->getSiapeCompetenciaValidar());
        $this->setHTML($this->getAbreForm());

        // campos hidden
        $this->setInputHidden('an', date('Y'));
        $this->setHTML($this->getInputHidden());

        $this->setHTML("<div valign='middle' class='col-md-12 text-center'>");
        $this->setHTML("<div valign='middle' class='col-md-3 col-lg-offset-4 text-center'>");
        $this->setHTML("<table class='table table-striped table-condensed table-bordered text-center'>");
        $this->setHTML("<tr>");

        // SIAPE
        $this->setHTML("<td class='text-center col-md-3'>");
        $this->setCampoSiape();
        $this->setInputID($this->getSiapeNome());
        $this->setInputTipoCampo('siape');
        $this->setInputTitulo($this->getSiapeTitulo());
        $this->setInputOnkeyup($this->getSiapeOnkeyup());
        $this->setInputPosicao_titulo($this->getSiapePosicao_titulo());
        $this->setInputTitle('');
        $this->setInputValue('');
        $this->setInputReadOnly(false);
        $this->setHTML($this->campoSiape());
        $this->setHTML("</td>");

        // Mês/Ano
        if ($this->getSoAno() == false || $this->getSoMes() == false)
        {
            $this->setHTML("<td class='text-center col-md-4'>");
            $this->setHTML("<table border='0' cellpadding='2' cellspacing='0' valign='middle'>");
        }

        $MesTitulo = $this->getMesTitulo();
        $AnoTitulo = $this->getAnoTitulo();

        if (empty($MesTitulo) || empty($AnoTitulo))
        {
            $this->setHTML("<tr><td align='center'" . ($this->getSoMes() == true ? ">Mês" : ($this->getSoAno() == true ? ">Ano" : " colspan='2'>Competência")) . "</td></tr>");
        }

        $this->setHTML("<tr>");

        // Mês
        if ($this->getSoAno() == false)
        {
            $this->setHTML("<td style='width:50px;'>");
            $this->setCampoMes();
            $this->setInputID($this->getMesNome());
            $this->setInputTipoCampo('mes');
            $this->setInputTitulo($this->getMesTitulo());
            $this->setInputOnkeyup($this->getMesOnkeyup());
            $this->setInputPosicao_titulo($this->getMesPosicao_titulo());
            $this->setInputTitle('');
            $this->setInputValue('');
            $this->setHTML($this->campoMes());
            $this->setHTML("</td>");
        }

        // Ano
        if ($this->getSoMes() == false)
        {
            $this->setHTML("<td style='width:80px;'>");
            $this->setCampoAno();
            $this->setInputID($this->getAnoNome());
            $this->setInputTipoCampo('ano');
            $this->setInputTitulo($this->getAnoTitulo());
            $this->setInputOnkeyup($this->getAnoOnkeyup());
            $this->setInputPosicao_titulo($this->getAnoPosicao_titulo());
            $this->setInputTitle('');
            $this->setInputValue('');
            $this->setHTML($this->campoAno());
            $this->setHTML("</td>");
        }

        $this->setHTML("</tr></table></td></tr>");

        // Módulo Histórico - Responsável pela solicitação
        if ($this->getSiapeResponsavelNome() != '')
        {
            $this->setHTML("<tr>");
            $this->setHTML("<td width='118' height='42' colspan='3' style='text-align: center;'>");
            $this->setCampoSiape();
            $this->setInputID($this->getSiapeResponsavelNome());
            $this->setInputTipoCampo('siape');
            $this->setInputTitulo($this->getSiapeResponsavelTitulo());
            $this->setInputOnkeyup($this->getSiapeResponsavelOnkeyup());
            $this->setInputPosicao_titulo($this->getSiapeResponsavelPosicao_titulo());
            $this->setInputTitle('');
            $this->setInputValue('');
            $this->setInputReadOnly(false);
            $this->setHTML($this->campoSiape());
            $this->setHTML("</td>");
            $this->setHTML("</tr>");
        }

        $this->setHTML("</table>");
        $this->setHTML("</div>");

        $this->setHTML('<div class="form-group col-md-8 text-center"><div class="col-md-7 col-md-offset-6 margin-10"><div class="col-md-6 text-right"><button class="btn btn-success btn-primary" type="submit" id="btn-continuar-mixer"><span class="glyphicon glyphicon-ok"></span> Continuar </button></div></div></div>');
        $this->setHTML("</div>");
        $this->setHTML("<div>");

        $this->setAjuda();

        $this->setHTML("</div>");

        $this->setHTML($this->getFechaForm());

        print $this->getHTML();

        $this->exibeCorpoBaseHTML();
        $this->exibeBaseHTML();
    }

}
