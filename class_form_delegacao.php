<?php

include_once( "config.php" );

## @class
#+---------------------------------------------------------------------+
#|                                                                     |
#|                       FORMULÁRIO DELEGAÇÃO                          |
#|                                                                     |
#+---------------------------------------------------------------------+
#
class Delegacao extends formPadrao
{
    private $matricula; // string
    private $nome;      // string
    private $unidade;   // string
    private $oDBase; // object

    ##
    #  Métodos: Delegacao (construtor)
    ##
    function Delegacao()
    {
        parent::formPadrao();
        $this->oDBase = new DataBase('PDO');
    }

    ##
    #  Métodos: Matrícula
    ##
    function setMatricula($var = '')
    {
        $this->matricula = $var;
    }

    function getMatricula()
    {
        return $this->matricula;
    }

    ##
    #  Métodos: Nome
    ##
    function setNome($var = '')
    {
        $this->nome = $var;
    }

    function getNome()
    {
        return $this->nome;
    }

    ##
    #  Métodos: Unidade
    ##
    function setUnidade($var = '')
    {
        $this->unidade = $var;
    }

    function getUnidade()
    {
        return $this->unidade;
    }

    ##
    #  verifica se a lotação do servidor é a mesma do usuário logado,
    #  o sistema não permitirá o registro da delegação
    function delegacao_verifica_lotacao()
    {
        // pesquisa dados do servidor
        $this->oDBase->query('
        SELECT mat_siape, nome_serv, cod_lot
            FROM servativ
                WHERE mat_siape = :siape
        ',
        array(
            array( ':siape', $this->getMatricula(), PDO::PARAM_STR ),
        ));

        $oDados = $this->oDBase->fetch_object();

        $this->setNome($oDados->nome_serv);
        $this->setUnidade($oDados->cod_lot);

        // testa situação do usuário logado no sistema
        if ($_SESSION["sAPS"] == "S" || ($_SESSION['sRH'] == 'S' && $_SESSION['sLotacao'] == $_SESSION['upag']))
        {
            // se for chefe ou do RH (autorizacao) pode registrar a
            // delegação, com base em portaria emitida pela chefia imediata
        }
        else if ($oDados->cod_lot != $_SESSION['sLotacao'])
        {
            mensagem("Servidor não pertence a sua lotação!", null, 1);
        }
    }

    ##
    #  apenas titulares de função, e servidor do RH (autorizado)
    #  poderão delegar ou registrar delegação atribuida
    #
    function delegacao_verifica_permissao()
    {
        $this->oDBase->query('
        SELECT mat_siape
            FROM ocupantes
                WHERE mat_siape = :siape
                      AND sit_ocup = "T"
                      AND dt_fim = "0000-00-00"
        ',
        array(
            array( ':siape', $_SESSION['sMatricula'], PDO::PARAM_STR ),
        ));

        $nRowsOcupantes = $this->oDBase->num_rows();

        if ($nRowsOcupantes != 0 || ($_SESSION['sRH'] == 'S' && $_SESSION['sLotacao'] == $_SESSION['upag']))
        {
            // se for chefe não pode ter delegação
        }
        else
        {
            mensagem("Somente titulares de função podem realizar delegação de atribuições!", null, 1);
        }
    }

    ##
    #  se o servidor for titular ou substituto de alguma função,
    #  não poderá ser indicado para delegação de atribuição no SISREF
    #
    function delegacao_verifica_se_ocupante_de_funcao()
    {
        $this->oDBase->query('
        SELECT mat_siape
            FROM ocupantes
                WHERE mat_siape = :siape
                      AND sit_ocup = "S"
                      AND dt_fim = "0000-00-00"
        ',
        array(
            array( ':siape', $this->getMatricula(), PDO::PARAM_STR ),
        ));

        $nRowsOcupantes = $this->oDBase->num_rows();

        if ($nRowsOcupantes != 0)
        {
            mensagem("Não é permitido delegar homologação a servidor "
                   . "designado como titular ou substituto de função!",
                   null,
                   1
            );
        }
    }


    ##
    #  formulario para informar o dados para o registro delegação
    #
    function delegacao_formulario_de_registro($modo='9')
    {
        ?>
        <div class="portlet-body form">

            <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

            <form method="POST" action="#" id="form1" name="form1" onSubmit="javascript:return false;">
                <input type='hidden' name='modo'    value='<?= $modo; ?>'>
                <input type='hidden' name='siape'   value='<?= tratarHTML($this->getMatricula()); ?>'>
                <input type='hidden' name='nome'    value='<?= tratarHTML($this->getNome()); ?>'>
                <input type='hidden' name='lotacao' value='<?= tratarHTML($this->getUnidade()); ?>'>
                <input type="hidden" name="dados"   value="<?= tratarHTML($dadosorigem); ?>" >

                <div class="row">
                    <div class="col-md-2">
                        <p><b>ÓRGÃO: </b><?= tratarHTML(getOrgaoMaisSigla( $_SESSION['sLotacao'] )); ?></p>
                    </div>
                    <div class="col-md-7">
                        <p><b>UORG: </b><?= tratarHTML(getUorgMaisDescricao( $_SESSION['sLotacao'] )); ?></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-7 col-md-7 col-xs-7 col-sm-7 margin-30">
                        <label>Servidor:</label>
                        <input type="text" class="form-control" value="<?= tratarHTML(removeOrgaoMatricula($this->getMatricula())) . " - " . tratarHTML($this->getNome()); ?>" readonly/>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-7 col-md-7 col-xs-7 col-sm-7 margin-10">
                        <label>Unidade:</label>
                        <input type="text" class="form-control" value="<?= tratarHTML(getUorgMaisDescricao( $this->getUnidade() )); ?>" readonly/>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-7 col-md-7 col-xs-7 col-sm-7 margin-10">
                        <label>Portaria de <?= ($modo == '9' ? "delegação:" : "cancelamento:"); ?></label>
                        <input name="portaria" type="text" class="form-control" id="portaria" value='' size="70" maxlength="70" >
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-2 col-md-2 col-xs-2 col-sm-2 margin-10" id="dt-container">
                        <label class='control-label'>Data:</label>
                        <div class='input-group date'>
                            <input type='text' id="datapt" name="datapt"
                                   placeholder="dd/mm/aaaa"
                                   class="form-control" style="background-color:transparent;width:110px;"/>
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>
                </div>

                <div class="row">
                    <br>
                    <div class="form-group col-md-12 text-center">
                        <div class="col-md-2"></div>
                        <div class="col-md-2 col-xs-4 col-md-offset-2">
                            <a class="btn btn-success btn-block" id="btn-salvar-registro">
                                <span class="glyphicon glyphicon-ok"></span> Salvar
                            </a>
                        </div>
                        <div class="col-md-2 col-xs-4">
                            <a class="btn btn-danger btn-block" id="btn-voltar" href="javascript:window.location.replace('/sisref/<?= $_SESSION['voltar_nivel_2']; ?>')" role="button">
                                <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                            </a>
                        </div>
                        <div class="col-md-2"></div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }

    function exibeFormulario($modo='9')
    {
        // se lotação do servidor for diferente da lotação do usuário
        // logado, o sistema não permitirá o registro da delegação
        $this->delegacao_verifica_lotacao();

        // apenas titulares de função, e servidor do RH (autorizado)
        // poderão registrar delegação atribuida
        $this->delegacao_verifica_permissao();

        // servidor titular ou substituto de alguma função, não
        // pode ser indicado para delegação de atribuição no SISREF
        $this->delegacao_verifica_se_ocupante_de_funcao();


        // Topo do formulário
        $this->exibeTopoHTML();
        $this->exibeCorpoTopoHTML();


        // formulário
        $this->delegacao_formulario_de_registro($modo);


        // Base do formulário
        $this->exibeCorpoBaseHTML();
        $this->exibeBaseHTML();
    }
}
