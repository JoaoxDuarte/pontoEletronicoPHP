<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * Responsável por gerenciar o fluxo de dados entre
 * a camada de modelo e a de visualização
 *
 *  TABELA : plantoes
 *       Suas descrições e características
 * 
 * @class TabPlantoesView
 *
 * @Camada    - View
 * @Diretório - inc/models
 * @Arquivo   - TabPlantoesView.php
 *
 * @author Edinalvo Rosa
 */
class TabPlantoesView extends formPadrao
{
    /*
    * Atributos
    */
    /* @var OBJECT */ public $conexao = null;

    ## @constructor
    #+-----------------------------------------------------------------+
    # Construtor da classe
    #+-----------------------------------------------------------------+
    #
    public function __construct()
    {
        parent::formPadrao();
                
    }

    /**
     * 
     * @param string $retorno Tipo do resultado
     * @param string $descricao Descrição do item
     * @return void
     */
    public function deleteView($retorno, $descricao)
    {
        switch($retorno)
        {
            case 'success':
                $tipo     = "success";
                $mensagem = "Excluído com sucesso o item " . $descricao . ".";
                break;

            case 'warning':
                $tipo     = "warning";
                $mensagem = "Há Servidor(a) registrado neste Plantão (" . $descricao . ").";
                break;

            case 'error':
                $tipo     = "danger";
                $mensagem = "NÃO houve sucesso na exclusão do item " . $descricao . ".";
                break;
        }

        retornaInformacao( $mensagem, $tipo);
    }

    
    /**
     * Monta <select> com tipos para isenção
     * 
     * @param array $opcoes
     * @param string/null $tipo
     * @return HTML
     */
    public function listaEscalas( $opcoes, $tipo = null )
    {
        $opcoes_tipo   = "";
        $inputs_hidden = "";
        
        foreach($opcoes AS $value)
        {
            $inputs_hidden .= "<input type='hidden' id='" 
                    . tratarHTML($value['id']) ."' name='"
                    . tratarHTML($value['id']) ."' value='"
                    . tratarHTML($value['trabalhar']) ."'>\n";
            $selected = (tratarHTML($value['id']) == tratarHTML($tipo) ? " selected" : "");
            $opcoes_tipo .= "
                <option value='" . tratarHTML($value['id']) ."'"
                    . tratarHTML($selected) . ">"
                    . tratarHTML($value['descricao']) . "
                </option>
            ";
        }

        ?>
        <select id="id_escala" name="id_escala" class="form-control select2-single">
            <?= $opcoes_tipo; ?>
        </select>
        <?php
        
        print $inputs_hidden;
    }
    
    
    /**
     * Formulário para inserção dos dados
     * 
     * @param object $dados   Registro selecionado
     * @param string $opcoes  Select com as escalas
     * @param string $form    Tipo do formulário (Incluir/Alterar)
     * @param string $retorno Resultado da ação de gravação dos  dados
     * @return string HTML
     */
    public function formularioCadastroPlantao( $dados, $opcoes, $form, $retorno )
    {
        $mensagemUsuario = $_SESSION["mensagem-usuario"];

        // cabecalho do HTML
        parent::setJS("js/jquery.mask.min.js");
        parent::setSubTitulo( "Configuração de Plantões - " . $form );
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML();
        
        $this->Javascript( $form, $retorno );

        ?>
        <div class="portlet-body form">

            <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

            <form id="form1" name="form1" method="POST" 
                  action="plantoes_configurar_<?= mb_strtolower($form); ?>.php" 
                  onsubmit="return false;">
                
                <input type='hidden' 
                       id="id_plantao" name='id_plantao' 
                       value='<?= tratarHTML($dados->id); ?>'>
                <input type='hidden' 
                       id='hora_inicial_antes' name='hora_inicial_antes' 
                       value='<?= tratarHTML($dados->hora_inicial); ?>'>
                <input type='hidden'
                       id='hora_final_antes'  name='hora_final_antes' 
                       value='<?= tratarHTML($dados->hora_final); ?>'>

                <div class="row col-md-offset-1">
                    <div class="col-md-8 margin-10">
                        <font class="ft_13_003">Selecione a escala:</font>
                        <?php $this->listaEscalas( $opcoes, $dados->id_escala ); ?>
                    </div>
                </div>

                <div class="row col-md-offset-1">
                    <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6 margin-10">
                        <font class="ft_13_003">Nome:</font>
                        &nbsp;<input type="text" 
                                     id="descricao" name="descricao" 
                                     class="form-control" 
                                     size="300" maxlength="300" 
                                     value="<?= tratarHTML($dados->descricao); ?>"
                                     style="width:500px;">
                    </div>
                </div>

                <div class="row col-md-offset-1">
                    <div class="col-lg-4 col-md-4 col-xs-4 col-sm-4 margin-10" style="width:125px;">
                        <font class="ft_13_003">Hora Inicial:</font>
                        &nbsp;<input type="text" 
                                     id="hora_inicial" name="hora_inicial" 
                                     class="form-control horas" 
                                     size="5" maxlength="5" 
                                     value="<?= tratarHTML($dados->hora_inicial); ?>">
                    </div>
                    <div class="col-lg-4 col-md-4 col-xs-4 col-sm-4 margin-10" style="width:125px;">
                        <font class="ft_13_003">Hora Final:</font>
                        &nbsp;<input type="text" 
                                     id="hora_final" name="hora_final" 
                                     class="form-control horas" 
                                     size="5" maxlength="5" 
                                     value="<?= tratarHTML($dados->hora_final); ?>">
                    </div>
                </div>

                <div class="row col-md-offset-1">
                    <div class="col-lg-4 col-md-4 col-xs-4 col-sm-4 margin-10">
                        <font class="ft_13_003">Ativo:</font>
                        <select id="ativo" name="ativo" class="form-control select2-single">
                            <option value='N'<?= ($dados->ativo != "S" ? " selected" : ""); ?>>N&atilde;o</option>
                            <option value='S'<?= ($dados->ativo == "S" || $form == 'Incluir' ? " selected" : ""); ?>>Sim</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <br>
                    <div class="form-group col-md-12 text-center">
                        <div class="col-md-2"></div>
                        <div class="col-md-2 col-xs-4 col-md-offset-2">
                            <a class="btn btn-success btn-block" id="btn-salvar" role="button">
                                <span class="glyphicon glyphicon-ok"></span> Salvar
                            </a>
                        </div>
                        <div class="col-md-2 col-xs-4">
                            <a class="btn btn-danger btn-block" id="btn-voltar"
                               href="javascript:window.location.replace('plantoes_configurar.php')" role="button">
                                <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                            </a>
                        </div>
                        <div class="col-md-2"></div>
                    </div>
                </div>
            </form>
        </div>
        <?php

        parent::exibeCorpoBaseHTML();
        parent::exibeBaseHTML();
    }


    /**
     * Inclui os códigos javascript de uso geral e especificos
     * 
     * @param string $form Tipo do formulário (Inclusão/Alteração)
     * @param string $retorno Resultado da gravação dos dados
     * @return string JAVASCRIPT
     */
    public function Javascript( $form, $retorno )
    {
        switch ($form)
        {
            case 'Incluir':
                $this->JavascriptIncluir( $retorno );
                break;
            
            case 'Alterar':
                $this->JavascriptAlterar( $retorno );
                break;
        }

        $this->JavascriptGeral();
    }
    
    
    /**
     * Inclui os códigos javascript para o form de inclusão
     * 
     * @param string $retorno Resultado da gravação dos dados
     * @return string JAVASCRIPT
     */
    public function JavascriptIncluir( $retorno )
    {
        ?>
        <script>
            $(document).ready(function () {
                <?php if ($retorno == "ja_existe"): ?>
                    mostraMensagem('Plantão já consta do Cadastro!', 'danger', "plantoes_configurar_incluir.php");
                <?php endif; ?>

                <?php if ($retorno == "gravou"): ?>
                    mostraMensagem('Inclusão realizada com sucesso!', 'success', "plantoes_configurar.php");
                <?php endif; ?>

                <?php if ($retorno == "nao_gravou"): ?>
                    mostraMensagem('Inclusão NÃO foi realizada!', 'danger', "plantoes_configurar.php");
                <?php endif; ?>
            });
        </script>
        <?php
    }
    
    
    /**
     * Inclui os códigos javascript para o form de alteração
     * 
     * @param string $retorno Resultado da gravação dos dados
     * @return string JAVASCRIPT
     */
    public function JavascriptAlterar( $retorno )
    {
        ?>
        <script>
            $(document).ready(function () {
                <?php if ($retorno == "ja_existe"): ?>
                    mostraMensagem('Plantão já consta do Cadastro!!', 'danger', "plantoes_configurar_alterar.php");
                <?php endif; ?>

                <?php if ($retorno == "nao_existe"): ?>
                    mostraMensagem('Item não consta da Configuração de Plantões!', 'danger', "plantoes_configurar.php");
                <?php endif; ?>

                <?php if ($retorno == "gravou"): ?>
                    mostraMensagem('Alteração realizada com sucesso!', 'success', "plantoes_configurar.php");
                <?php endif; ?>

                <?php if ($retorno == "nao_gravou"): ?>
                    mostraMensagem('Alteração NÃO foi realizada!', 'danger', "plantoes_configurar.php");
                <?php endif; ?>
            });
        </script>
        <?php
    }
    
    
    /**
     * Inclui os códigos javascript de uso geral
     * 
     * @param void
     * @return string JAVASCRIPT
     */
    public function JavascriptGeral()
    {
        ?>
        <script>
            $(document).ready(function () {
                $('#btn-salvar').on('click', function () {
                    if(!validateForm()){
                        return false;
                    }

                    $('#form1').attr('onsubmit', 'javascript:return true;');
                    $('#form1').submit();

                    return false;
                });

                $('#hora_inicial').on('keyup', function ()
                {
                    var ini    = $(this).val();
                    var fim    = $('#hora_final').val();
                    var escala = $('#id_escala').val();
                    var horas  = $('#'+escala).val();

                    if (ini.length >= 5 && fim.length >= 5)
                    {
                        if (($(this).val() == '00:00') || verificaHora($(this)))
                        {
                            verificaDiferenca(ini, fim, horas);
                        }
                        else
                        {
                            mostraMensagem("Hora Inicial inválida!", "warning");
                            return false;
                        }
                    }
                });

                $('#hora_final').on('keyup', function ()
                {
                    var ini = $('#hora_inicial').val();
                    var fim = $(this).val();
                    var escala = $('#id_escala').val();
                    var horas  = $('#'+escala).val();

                    if (ini.length >= 5 && fim.length >= 5)
                    {
                        if (($(this).val() == '00:00') || verificaHora($(this)))
                        {
                            verificaDiferenca(ini, fim, horas);
                        }
                        else
                        {
                            mostraMensagem("Hora Final inválida!", "warning");
                            return false;
                        }
                    }
                });
            });

            /**
             * @info Verifica limite de plantão com base na escala escolhida
             * 
             * @param {string/time} ini
             * @param {string/time} fim
             * @param {string/time} horas
             * @returns {boolean}
             */
            function verificaDiferenca(ini, fim, horas)
            {
                var segs_ini   = time_to_sec(ini);
                var segs_fim   = time_to_sec(fim);
                var segs_horas = time_to_sec(horas+':00');
                var segs_24hs  = time_to_sec('24:00');
                var tmp        = 0;
                var tmp0       = 0;

                if (segs_fim > segs_ini)
                {
                    tmp = (segs_fim - segs_ini);
                }
                else
                {
                    tmp0 = (segs_24hs - segs_ini);
                    tmp  = (tmp0 + segs_fim);
                }

                if (tmp > segs_horas)
                {
                    mostraMensagem("Horas do Plantão maior que "+horas+"!", "warning");
                    return false;
                }

                if (tmp < segs_horas)
                {
                    mostraMensagem("Horas do Plantão menor que "+horas+"!", "warning");
                    return false;
                }

                return true;
            }

            function validateForm() {

                var hora_inicial = $("[name='hora_inicial']").val();
                var hora_final   = $("[name='hora_final']").val();
                var descricao    = $("[name='descricao']").val();

                if(hora_inicial != "00:00" && parseInt(hora_inicial, 10) == 0){
                    mostraMensagem('Hora Inicial é obrigatória!', 'warning');
                    return false;
                }

                if(hora_final != "00:00" && parseInt(hora_final, 10) == 0){
                    mostraMensagem('Hora Final é obrigatória!', 'warning');
                    return false;
                }

                if(descricao == ""){
                    mostraMensagem('Nome do Plantão é obrigatória!', 'warning');
                    return false;
                }

                return true;
            }

            $('.horas').mask('00:00');
        </script>
        <?php
    }

} // END class TabPlantoesView
