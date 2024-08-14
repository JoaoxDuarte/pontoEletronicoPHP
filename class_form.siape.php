<?php

// @package class
include_once("class_formpadrao.php");

## @class
#+-------------------------------+
#| Formulario Siape              |
#+-------------------------------+
#

class formSiape extends formPadrao
{

    var $SiapeDestino;
    var $SiapeRetorno;
    var $SiapeValidar;
    var $SiapeUsuario;
    var $SiapeMes;
    var $SiapeAno;
    var $SiapeYear;
    var $SiapeCmd;
    var $SiapeCaixa;
    var $SiapeCaixaBorda;
    var $SiapeCaixaWidth;
    var $SiapeNome;
    var $SiapeTitulo;
    var $SiapeTituloClass;

    ## @constructor
    #+----------------------------+
    #| Construtor da classe       |
    #+----------------------------+
    #
    function formSiape()
    {
        parent::formPadrao();
        parent::setSiapeOnkeyup('javascript:ve(this.value);');
        parent::setSiapePosicao_titulo('topo');
        $this->setSiapeCaixa('149');
        $this->setSiapeCaixaBorda('1');
        $this->setSiapeCaixaWidth('11%');

        // siape
        $this->initSiapeNome();
        $this->setSiapeNome();
        $this->initSiapeTitulo();
        $this->setSiapeTitulo();
        $this->initSiapeTituloClass();
        $this->setSiapeTituloClass();
        $this->initSiapeOnkeyup();
        $this->setSiapeOnkeyup();
        $this->initSiapeClass();
        $this->setSiapeClass();
        $this->initSiapePosicao_titulo();
        $this->setSiapePosicao_titulo();
        $this->setSiapeRetorno(null);

        // mes
        $this->initMesNome();
        $this->setMesNome();
        $this->initMesTitulo();
        $this->setMesTitulo();
        $this->initMesOnkeyup();
        $this->setMesOnkeyup();
        $this->initMesClass();
        $this->setMesClass();
        $this->initMesPosicao_titulo();
        $this->setMesPosicao_titulo();

        // ano
        $this->initAnoNome();
        $this->setAnoNome();
        $this->initAnoTitulo();
        $this->setAnoTitulo();
        $this->initAnoOnkeyup();
        $this->setAnoOnkeyup();
        $this->initAnoClass();
        $this->setAnoClass();
        $this->initAnoPosicao_titulo();
        $this->setAnoPosicao_titulo();
    }

    ## @metodo
    #+----------------------------+
    #| formSiape                  |
    #+----------------------------+
    #
    function setSiapeNome($valor = 'pSiape')
    {
        $this->SiapeNome = $valor;
    }

    function setSiapeTitulo($valor = 'Matr&iacute;cula:')
    {
        $this->SiapeTitulo = $valor;
    }

    function setSiapeTituloClass($valor = 'ft_13_001')
    {
        $this->SiapeTituloClass = $valor;
    }

    function setSiapeDestino($destino = "")
    {
        $this->SiapeDestino = $destino;
    }

    function getSiapeDestino()
    {
        return $this->SiapeDestino;
    }

    function setSiapeRetorno($voltar = null)
    {
        $this->SiapeRetorno = $voltar;
    }

    function getSiapeRetorno()
    {
        return $this->SiapeRetorno;
    }

    function setSiapeValidar($validar = "")
    {
        $this->SiapeValidar = $validar;
    }

    function getSiapeValidar()
    {
        return $this->SiapeValidar;
    }

    function setSiapeUsuario($usuario = "")
    {
        $this->SiapeUsuario = $usuario;
    }

    function getSiapeUsuario()
    {
        return $this->SiapeUsuario;
    }

    function setSiapeAno($ano = "")
    {
        $this->SiapeAno = $ano;
    }

    function getSiapeAno()
    {
        return $this->SiapeAno;
    }

    function setSiapeMes($mes = "")
    {
        $this->SiapeMes = $mes;
    }

    function getSiapeMes()
    {
        return $this->SiapeMes;
    }

    function setSiapeYear($year = "")
    {
        $this->SiapeYear = $year;
    }

    function getSiapeYear()
    {
        return $this->SiapeYear;
    }

    function setSiapeCmd($cmd = "")
    {
        $this->SiapeCmd = $cmd;
    }

    function getSiapeCmd()
    {
        return $this->SiapeCmd;
    }

    function setSiapeCaixa($cmd = "149")
    {
        $this->SiapeCaixa = $cmd;
    }

    function getSiapeCaixa()
    {
        return $this->SiapeCaixa;
    }

    function setSiapeCaixaBorda($cmd = "1")
    {
        $this->SiapeCaixaBorda = $cmd;
    }

    function getSiapeCaixaBorda()
    {
        return $this->SiapeCaixaBorda;
    }

    function setSiapeCaixaWidth($cmd = "11%")
    {
        $this->SiapeCaixaLargura = $cmd;
    }

    function getSiapeCaixaWidth()
    {
        return $this->SiapeCaixaLargura;
    }

    function setSiapeLargura($cmd = "800")
    {
        parent::setLargura($cmd);
    }

    function getSiapeLargura()
    {
        return parent::getLargura();
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

        parent::setSiapeNome($this->SiapeNome);
        parent::setSiapeTitulo($this->SiapeTitulo);
        parent::setSiapeTituloClass($this->SiapeTituloClass);

		?>
        <script>
            $(document).ready(function ()
            {
                $("#btn-continuar").on('click', function () 
                {
                    validar();
                });    
            });
            
            function validar() {
                if ($('#pSiape').val().length == 0)
                {
                    $('#pSiape').focus();
                    mostraMensagem('É obrigatório informar a matrícula!', 'warning');
                    return false;
                }
                else
                {
                    // mensagem processando
                    showProcessando();
                    $('#form1').submit();
                }
            }
        </script>

        <form id="form1" name="form1" method="post" action="<?= tratarHTML($this->getSiapeDestino()); ?>" onSubmit="return validar()">
            <?= parent::getInputHidden(); ?>
            <input type='hidden' id='an'  name='an'  value='<?= tratarHTML($this->getSiapeAno()); ?>'>
            <input type='hidden' id='mes' name='mes' value='<?= tratarHTML($this->getSiapeMes()); ?>'>
            <input type='hidden' id='ano' name='ano' value='<?= tratarHTML($this->getSiapeYear()); ?>'>
            <input type='hidden' id='cmd' name='cmd' value='<?= tratarHTML($this->getSiapeCmd()); ?>'>
            <input type="hidden" id="usu" name="usu" value="<?= tratarHTML($this->getSiapeUsuario); ?>">

            <div valign='middle' class='col-md-12 text-center'>
                <div valign='middle' class='col-md-3 col-lg-offset-4 text-center'>
                    <table class='table table-striped table-condensed <?= ($this->getSiapeCaixaBorda() == "1" ? "table-bordered" : ""); ?> text-center'>
                        <tr>
                            <td>
                                <?php

                                if ($this->SiapeTitulo != "")
                                {
                                    if (parent::getSiapePosicao_titulo() == 'topo')
                                    {
                                        ?>
                                        &nbsp;<?= $this->SiapeTitulo; ?><br>&nbsp;
                                        <div style="text-align:center">
                                        <?php parent::inputSiape(0, true); ?>
                                        </div>
                                        <?php
                                    }
                                    else
                                    {
                                        ?>
                                        <div class='row' style='padding-top:10px;'>
                                            <div class='col-md-4' style="padding-top:7px;">
                                                &nbsp;<?= tratarHTML($this->SiapeTitulo); ?><br>&nbsp;
                                            </div>
                                            <div class='text-center col-lg-8'>
                                                <?php parent::inputSiape(0, true); ?>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="form-group col-md-8 text-center">
                    <div class="col-md-8 col-md-offset-6 margin-10">
                        <div class="col-md-7 text-center">
                            <a class="btn btn-success btn-primary" id="btn-continuar">
                                <span class="glyphicon glyphicon-ok"></span> Continuar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div style='text-align:right;width:90%;margin:25px;font-size:9px;border:0px;'>
                    <fieldset style='border:1px solid white;text-align:left;'>
                        <legend style='font-size:12px;padding:0px;margin:0px;'><b>&nbsp;Informações&nbsp;</b></legend>
                        <p style='padding:1px;margin:0px;'>
                            <b>Matrícula SIAPE&nbsp;:&nbsp;</b><b></b>Matrícula do servidor/estagiário;
                        </p>
                    </fieldset>
                </div>
            </div>
        </form>
        <?php

        parent::exibeCorpoBaseHTML();
        parent::exibeBaseHTML();
    }
}
