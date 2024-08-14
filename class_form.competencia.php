<?php
// @package class
include_once( "config.php" );
include_once( 'class_form.telas.php' );

## @class
#+-------------------------------+
#| Formulario Competencia |
#+-------------------------------+

#
class formCompetencia extends formTelas
{

    var $MesNome;    // string
    var $MesTitulo;  // string
    var $MesOnkeyup; // string
    var $MesPosicao_titulo;     // string
    var $AnoNome;    // string
    var $AnoTitulo;  // string
    var $AnoOnkeyup; // string
    var $AnoPosicao_titulo;     // string
    var $CompetenciaDestino; // string
    var $CompetenciaValidar; // string
    var $soMes; // boolean
    var $soAno; // boolean
    var $soPeriodo; // boolean

    var $observacao_topo; // string
    var $observacao_base; // string

    ## @constructor
    #+----------------------------+
    #| Construtor da classe       |
    #+----------------------------+

    #
    function formCompetencia()
    {
        parent::formPadrao();
        //
        // mes
        $this->setMesNome('mes');
        $this->setMesTitulo('M&ecirc;s:');
        $this->setMesOnkeyup('javascript:ve(this.value);');
        $this->setMesPosicao_titulo('topo');
        //
        // ano
        $this->setAnoNome('ano');
        $this->setAnoTitulo('Ano:');
        $this->setMesOnkeyup('javascript:ve(this.value);');
        $this->setAnoPosicao_titulo('topo');

    }

    ## @metodo
    #+----------------------------+
    #| Define/Le os atributos     |
    #+----------------------------+
    #
    //
    // Observações (topo/base)
    //
    function setObservacaoTopo($string = "")
    {
        $this->observacao_topo = $string;

    }

    function getObservacaoTopo()
    {
        return $this->observacao_topo;

    }

    function setObservacaoBase($string = "")
    {
        $this->observacao_base = $string;

    }

    function getObservacaoBase()
    {
        return $this->observacao_base;

    }


		//
		// MES: nome da variavel, titulo do campo, funcao, posicao do titulo
    //
		function setMesNome($string = "")
    {
        $this->MesNome = $string;

    }

    function getMesNome()
    {
        return $this->MesNome;

    }

    function setMesTitulo($string = "")
    {
        $this->MesTitulo = $string;

    }

    function getMesTitulo()
    {
        return $this->MesTitulo;

    }

    function setMesOnkeyup($string = "")
    {
        $this->MesOnkeyup = $string;

    }

    function getMesOnkeyup()
    {
        return $this->MesOnkeyup;

    }

    function setMesPosicao_titulo($string = "")
    {
        $this->MesPosicao_titulo = $string;

    }

    function getMesPosicao_titulo()
    {
        return $this->MesPosicao_titulo;

    }

    //
    // ANO: nome da variavel, titulo do campo, funcao, posicao do titulo
    //
    function setAnoNome($string = "")
    {
        $this->AnoNome = $string;

    }

    function getAnoNome()
    {
        return $this->AnoNome;

    }

    function setAnoTitulo($string = "")
    {
        $this->AnoTitulo = $string;

    }

    function getAnoTitulo()
    {
        return $this->AnoTitulo;

    }

    function setAnoOnkeyup($string = "")
    {
        $this->AnoOnkeyup = $string;

    }

    function getAnoOnkeyup()
    {
        return $this->AnoOnkeyup;

    }

    function setAnoPosicao_titulo($string = "")
    {
        $this->AnoPosicao_titulo = $string;

    }

    function getAnoPosicao_titulo()
    {
        return $this->AnoPosicao_titulo;

    }

    function setSoMes($string = false)
    {
        $this->soMes = $string;

    }

    function getSoMes()
    {
        return $this->soMes;

    }

    function setSoAno($string = false)
    {
        $this->soAno = $string;

    }

    function getSoAno()
    {
        return $this->soAno;

    }

    function setSoPeriodo($string = false)
    {
        $this->soPeriodo = $string;

    }

    function getSoPeriodo()
    {
        return $this->soPeriodo;

    }

    // Destino
    function setCompetenciaDestino($destino = "")
    {
        $this->CompetenciaDestino = $destino;

    }

    function getCompetenciaDestino()
    {
        return $this->CompetenciaDestino;

    }

    // Funcao de validacao
    function setCompetenciaValidar($validar = "")
    {
        $this->CompetenciaValidar = $validar;

    }

    function getCompetenciaValidar()
    {
        return $this->CompetenciaValidar;

    }

    // texto com informações sobre os campos
    public function setAjuda()
    {
        parent::setHTML("<div style='text-align:right;width:90%;margin:25px;font-size:9px;border:0px;'>");
        parent::setHTML("   <fieldset style='border:1px solid white;text-align:left;'>");
        parent::setHTML("       <legend style='font-size:12px;padding:0px;margin:0px;'><b>&nbsp;Informações&nbsp;</b></legend>");
        parent::setHTML("       <p style='padding:1px;padding-top:5px;margin:0px;font-size:12px;'>" . $this->getObservacaoBase() . "</p>");
        parent::setHTML("   </fieldset>");
        parent::setHTML("</div>");
        $this->setObservacaoBase( '' );
    }

    
    ## @metodo
    #+----------------------------+
    #| Exibe o formulário         |
    #+----------------------------+

    #
    function exibeForm()
    {        
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML();

        parent::initHTML();
        parent::setFormAction($this->getCompetenciaDestino());
        parent::setFormOnSubmit($this->getCompetenciaValidar());
        parent::setHTML(parent::getAbreForm());
        
        // campos hidden
        parent::setInputHidden('an', date('Y'));
        parent::setHTML(parent::getInputHidden());

        parent::setHTML("<div class='col-md-12 text-center margin-25'>\n");
        parent::setHTML("    <div valign='middle' class='col-md-2 col-lg-offset-4 text-center'>\n");
        parent::setHTML("        <table class='table table-striped table-condensed table-bordered text-center'>\n");
        parent::setHTML("            <tr>\n");
        parent::setHTML("                <td>\n");
        parent::setHTML("                    <div class='col-md-2 text-center' id='dt-container' style='padding:0px;'>\n");
        parent::setHTML("                        <label class='control-label text-center'>Competência:</label>\n");
        parent::setHTML("                        <div class='input-group date text-center'>\n");
        parent::setHTML("                            <input type='text' id='competencia' name='competencia' size='10' maxlength='10' style='background-color:transparent;width:105px;' class='form-control' autocomplete='off'><span class='input-group-addon'><i class='glyphicon glyphicon-calendar'></i></span>\n");
        parent::setHTML("                        </div>\n");
        parent::setHTML("                    </div>\n");
        parent::setHTML("                </td>\n");
        parent::setHTML("            </tr>\n");
        parent::setHTML("        </table>\n");
        parent::setHTML("    </div>\n");
        parent::setHTML("</div>\n");

        parent::setHTML("<div class='form-group col-md-12 text-center'>\n");
        parent::setHTML("   <div class='col-md-3 col-md-offset-4 margin-10'>\n");
        parent::setHTML("       <div class='col-md-6 text-right'>\n");
        parent::setHTML("           <button class='btn btn-success btn-primary' type='submit' id='btn-continuar'>\n");
        parent::setHTML("           <span class='glyphicon glyphicon-ok'></span> Continuar </button>\n");
        parent::setHTML("       </div>\n");
        parent::setHTML("   </div>\n");
        parent::setHTML("</div>\n");

        parent::setHTML("<div class='form-group col-md-12 margin-25 text-center'>\n");
        parent::setHTML("</div>\n");

        parent::setHTML(parent::getFechaForm());
        
        $this->setAjuda();

        print parent::getHTML();

        parent::exibeCorpoBaseHTML();
        parent::exibeBaseHTML();
   }

    
    public function exibeFormPeriodoRecesso()
    {
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML();

        $obsTopo = $this->getObservacaoTopo();
        $obsBase = $this->getObservacaoBase();

        parent::setObservacaoTopo('');
        $this->setObservacaoTopo('');
        parent::setObservacaoBase('');
        $this->setObservacaoBase('');

        ?>
        <form id='form1' name='form1' method='post' action='<?= $this->getCompetenciaDestino(); ?>' onSubmit='<?= $this->getCompetenciaValidar(); ?>' valign='middle'>

            <?= parent::getInputHidden(); ?>
            <input type='hidden' id='an' name='an' value='<?= date('Y'); ?>'>

            <div valign='middle' class='col-md-12 text-center' style='padding-top:40px;'>
                <div valign='middle' class='col-md-3 col-lg-offset-4 text-center'>
                    <table class='table table-striped table-condensed table-bordered text-center'>
                        <tr>
                            <td class='text-center col-md-3'>
                                <font class='<?= $this->getInputTituloClass(); ?>'>
                                    &nbsp;Período:&nbsp;<br>
                                    &nbsp;<select id='<?= $this->getAnoNome(); ?>' name='<?= $this->getAnoNome(); ?>' class='form-control'>
                                    <?php

                                    $mesAtual = date('m');
                                    $anoFinal = date('Y');
                                    $anoFinal = (substr_count('11_12', $mesAtual) > 0 ? ($anoFinal + 1) : $anoFinal);

                                    for ($ano = $anoFinal; $ano > 2018; $ano--)
                                    {
                                        ?>
                                        <option value='<?= ($ano - 1) . " / " . $ano; ?>' <?= ($ano == date('Y') ? 'selected' : ''); ?>><?= ($ano - 1) . " / " . $ano; ?></option>
                                        <?php
                                    }

                                    ?>
                                    </select>
                                </font>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="form-group col-md-8 text-center">
                <div class="col-md-7 col-md-offset-6 margin-10">
                    <div class="col-md-6 text-right">
                        <button class="btn btn-success btn-primary" type="submit" id="btn-continuar-mixer"><span class="glyphicon glyphicon-ok"></span> Continuar </button>
                    </div>
                </div>
            </div>

        </form>

        <div class="form-group col-md-12 text-left" style='text-align:right;width:90%;margin:25px;font-size:9px;border:0px;'>
            <fieldset style='border:1px solid white;text-align:left;'>
                <legend style='font-size:12px;padding:0px;margin:0px;;text-align:left'><b>&nbsp;Informações&nbsp;</b></legend>
                <p style='padding:1px;margin:0px;text-align:left;'>
                    <?= strtr($obsTopo,array('<center>'=>'','</center>'=>'')); ?>
                    <br>
                    <?= strtr($obsBase,array('<center>'=>'','</center>'=>'')); ?>
                </p>
            </fieldset>
        </div>
        <?php

        parent::exibeCorpoBaseHTML();
        parent::exibeBaseHTML();
    }
}
