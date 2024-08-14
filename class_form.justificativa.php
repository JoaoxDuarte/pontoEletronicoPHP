<?php

// conexao ao banco de dados
// funcoes diversas
include_once( "config.php" );
include_once("class_ocorrencias_grupos.php");

## classe para montagem do formulario justificativa
#
class formJustificativa extends formPadrao
{
    private $action;
    private $onsubmit;
    private $siape;
    private $nome;
    private $lotacao;
    private $dia;
    private $ocorrencia;
    private $justificativa_servidor;
    private $justificativa_chefia;
    private $exige_justificativa_chefia;
    private $destino_avancar;
    private $destino_retorno;

    public function __construct()
    {
        parent::formPadrao();
        $this->initInputHidden();
        $this->setCSS(styleFormulario(_DIR_CSS_ . 'sisref_form_justificativa'));
        $this->setJS(_DIR_JS_ . 'phpjs.js');
    }

    public function setAction($value = '')
    {
        $this->action = $value;
    }

    public function setOnSubmit($value = '')
    {
        $this->onsubmit = $value;
    }

    public function setSiape($value = '')
    {
        $this->siape = $value;
    }

    public function setNome($value = '')
    {
        $this->nome = $value;
    }

    public function setLotacao($value = '')
    {
        $this->lotacao = $value;
    }

    public function setDia($value = '')
    {
        $this->dia = $value;
    }

    public function setOcorrencia($value = '')
    {
        $this->ocorrencia = $value;
    }

    public function setJustificativaServidor($value = '', $readonly = '')
    {
        $this->justificativa_servidor = array('value' => $value, 'readonly' => $readonly);
    }

    public function setJustificativaChefia($value = '', $readonly = '')
    {
        $this->justificativa_chefia = array('value' => $value, 'readonly' => $readonly);
    }

    public function setExigeJustificativaChefia($value = false)
    {
        $this->exige_justificativa_chefia = $value;
    }

    public function setDestinoAvancar($value = '')
    {
        $this->destino_avancar = $value;
    }

    public function setDestinoRetorno($value = '')
    {
        $this->destino_retorno = $value;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getOnSubmit()
    {
        return $this->onsubmit;
    }

    public function getSiape()
    {
        return $this->siape;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function getLotacao()
    {
        return $this->lotacao;
    }

    public function getDia()
    {
        return $this->dia;
    }

    public function getOcorrencia()
    {
        return $this->ocorrencia;
    }

    public function getJustificativaServidor($key = 'value')
    {
        return $this->justificativa_servidor[$key];
    }

    public function getJustificativaChefia($key = 'value')
    {
        return $this->justificativa_chefia[$key];
    }

    public function getExigeJustificativaChefia()
    {
        return $this->exige_justificativa_chefia;
    }

    public function getDestinoAvancar()
    {
        return $this->destino_avancar;
    }

    public function getDestinoRetorno()
    {
        return $this->destino_retorno;
    }

    public function showForm()
    {
        $oDBase = selecionaServidor( $this->getSiape() );
        $sitcad = $oDBase->fetch_object()->sigregjur;

        ## ocorrências grupos
        $obj = new OcorrenciasGrupos();
        $grupoOcorrenciasNegativasDebitos = $obj->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios=true);


        $onSubmit        = 'javascript:' . $this->getOnSubmit();
        $destino_avancar = $this->getDestinoAvancar();

        if ($this->getExigeJustificativaChefia() == true)
        {
            $this->setExigeJustificativaChefia( in_array($this->getOcorrencia(), $grupoOcorrenciasNegativasDebitos) );
        }

        ## Topo do formulário
        #
	    $this->exibeTopoHTML();
        $this->exibeCorpoTopoHTML();

        ?>
        <form method="POST"
              action="<?= $this->getAction(); ?>"
              <?= ($onSubmit == '' ? '' : ' onsubmit="' . $onSubmit . '"'); ?>
              id="form1"
              name="form1">
            <?= $this->getInputHidden(); ?>
            <div id='form-bloco'>
                <div id='form-grupo'>
                    <div id='form-siape-tit' class='form-ft-tit form-border'>SIAPE</div>
                    <div id='form-nome-tit' class='form-ft-tit form-bTRB'>NOME</div>
                    <div id='form-unidade-tit' class='form-ft-tit form-bTRB'>LOTAÇÃO</div>
                </div>
                <div id='form-grupo'>
                    <div id='form-siape' class='form-bRBL'>
                        <input type="text" id="siape" name="siape" class='centro' value="<?= tratarHTML($this->getSiape()); ?>" size="7" readonly>
                    </div>
                    <div id='form-nome' class='form-bRB'>
                        <input type="text" id="nome" name="nome" class='Caixa' value="<?= tratarHTML($this->getNome()); ?>" size="60" readonly>
                    </div>
                    <div id='form-unidade' class='form-bRB'>
                        <input type="text" id="lotacao" name="lotacao" class='centro' value="<?= tratarHTML($this->getLotacao()); ?>" size="8" readonly>
                    </div>
                </div>
                <div id='form-grupo'>
                    <div id='form-dia-tit' class='form-ft-tit form-bRBL'>DATA</div>
                </div>
                <div id='form-grupo'>
                    <div id='form-dia' class='form-bRBL'>
                        <input type="text" id="dia" name="dia" class='centro' value="<?= tratarHTML($this->getDia()); ?>" size="10" readonly>
                    </div>
                </div>
                <?php

                if ($this->getExigeJustificativaChefia() == false)
                {
                    ?>
                    <div id='form-grupo'>
                        <div id='form-oco-tit' class='form-ft-tit form-bRBL'>Código da Ocorrência</div>
                        <div id='form-just-tit' class='form-ft-tit form-bRB'>JUSTIFICATIVA DO SERVIDOR</div>
                    </div>
                    <div id='form-grupo'>
                        <div id='form-oco' class='form-bRBL'>
                            <input type="text" id="oco" name="oco" class='centro' value="<?= tratarHTML($this->getOcorrencia()); ?>" size="10" readonly>
                        </div>
                        <div id='form-just' class='form-bRB'>
                            <textarea id="textarea" name='just' cols='80' rows='5' <?= tratarHTML($this->getJustificativaServidor('readonly')); ?>><?= tratarHTML($this->getJustificativaServidor('value')); ?></textarea>
                        </div>
                    </div>
                    <?php
                }
                else
                {
                    ?>
                    <div id='form-grupo'>
                        <div id='form-oco-tit' class='form-ft-tit form-bRBL'>Código da Ocorrência</div>
                        <div id='form-just-tit' class='form-ft-tit form-bRB'>JUSTIFICATIVA DO SERVIDOR</div>
                    </div>
                    <div id='form-grupo'>
                        <div id='form-oco-2' class='form-bRBL'>
                            <input type="text" id="oco" name="oco" class='centro' value="<?= tratarHTML($this->getOcorrencia()); ?>" size="10" readonly>
                        </div>
                        <div id='form-grupo-2'>
                            <div id='form-just' class='form-bRB'>
                                <textarea id="textarea" name='just' cols='80' rows='5' <?= tratarHTML($this->getJustificativaServidor('readonly')); ?>><?= tratarHTML($this->getJustificativaServidor('value')); ?></textarea>
                            </div>
                            <div id='form-just-tit' class='form-ft-tit form-bRB'>JUSTIFICATIVA DA CHEFIA<!-- <b style='font-size:9px;color:red;'>&nbsp;(campo obrigatório)</b> //--></div>
                            <div id='form-justchef' class='form-bRB'>
                                <textarea id="justchef" name='justchef' cols='80' rows='4' <?= ($this->getExigeJustificativaChefia() == true ? '' : tratarHTML($this->getJustificativaChefia('readonly'))); ?>><?= tratarHTML($this->getJustificativaChefia('value')); ?></textarea>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>

            <div id='form-bloco-2'>
                <div id='form-grupo'>
                    <div id='form-botao-alterar'>
                        <?php

                        if ($destino_avancar != '')
                        {
                            echo ($this->getJustificativaServidor('value') == '' ?
                                botaoDuplo('Não Há Justificativa do Servidor.<br>Continuar Alteração ?', $destino_avancar) :
                                botaoDuplo('Continuar', $destino_avancar)
                            );
                        }

                        ?>
                    </div>
                    <div id='form-botao-voltar'>
                        <?= botaoDuplo('Voltar', $this->getDestinoRetorno()); ?>
                    </div>
                </div>
            </div>

        </form>
        <?php

        ## Base do formulário
        #
        $this->exibeCorpoBaseHTML();
        $this->exibeBaseHTML();
    }
}
