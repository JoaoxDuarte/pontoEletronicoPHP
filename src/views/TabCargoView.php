<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");


/**
 * @class TabCargoView
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : tabcargo
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - View
 * @Diret�rio - src/models
 * @Arquivo   - TabCargoView.php
 *
 * @author Edinalvo Rosa
 */
class TabCargoView extends formPadrao
{
    /*
    * Atributos
    */
    /* @var OBJECT */ public $conexao = NULL;

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
     * @info Exibe a lista de Isen��o de Ponto
     *
     * @param object $dados
     * @return HTML
     */
    public function showLista($retorno="")
    {
        $sLotacao        = $_SESSION["sLotacao"];
        $mensagemUsuario = $_SESSION["mensagem-usuario"];

        parent::setDataTables();
        parent::setSubTitulo( "Tabela de Cargo - Manuten��o" );

        // Topo do formul�rio
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML('1250px');

        $this->JavaScript($retorno);
        
        ?>
        <form name='form1' id='form1' method='POST' action='#' onsubmit='javascript:return false;'> 
            <input type="hidden" name="id" id="id">
                    
            <div class="row">
                <div class="col-md-12 text-left margin-25 margin-bottom-10">
                    <button type="button" class="btn btn-default btn_adicionar_cargo" style="padding-left:30px;padding-right:30px;">Adicionar</button>
                </div>
                            
                <table id="tabela_dados" class="display nowrap table-hover table table-striped table-bordered" role="grid" style="width:1050px;">
                    <thead>
                        <tr>
                            <th>A��es</th>
                            <th class="sorting" style="width:100px;">C�digo do Cargo</th>
                            <th class="sorting nowrap" style="width:550px;">Nome do Cargo</th>
                            <th class="sorting">Permite Banco de Horas</th>
                            <th class="sorting">Subs�dios</th>
                            <th class="sorting">Ativo</th>
                        </tr>
                    </thead>
                </table>
                            
            </div>
        </form>
        <?php
        
        // Base do formul�rio
        parent::exibeCorpoBaseHTML();
        parent::exibeBaseHTML();
    }


    /**
     * @info Fun��es Javascript
     *
     * @param void
     * @return script javascript
     */
    public function JavaScript($retorno="")
    {
        ?>
        <script>
            $(document).ready(function ()
            {
                $(".btn_adicionar_cargo").on('click', function () {
                    var oForm = $('#form1');
                    
                    showProcessandoAguarde();

                    oForm.attr('onsubmit', 'javascript:return true');
                    oForm.attr('action', 'tabcargo_incluir.php');
                    oForm.submit();
                });

                $(".btn_Alterar").click( function(event) {
                    var oForm   = jQuery('#form1');
                    var id      = $(this).attr('data-id');
                    
                    showProcessandoAguarde();

                    $('#id').val( id );
                    
                    oForm.attr('onsubmit', 'javascript:return true');
                    oForm.attr('action', 'tabcargo_alterar.php');
                    oForm.submit();
                });
                
                var tabela = $('#tabela_dados').DataTable({
                    "processing": true,
                    "ajax": {
                        "url": "tabcargo_lista.php",
                        "type": "POST"
                    },
                    "dom": '<"top"fl>rt<"bottom"ip><"clear">',
                    "scrollX": true,
                    "language": {
                        "url": "js/DataTables/i18n/pt_BR.json"
                    },
                    "stateSave": true,
                    "pagingType": "full_numbers",
                    "lengthMenu": [ [10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, "Todos"] ]
                });
            });
            
            function DeleteCargo(cargo)
            {
                (function($){ // recebendo como par�metro na vari�vel '$'
                    $(document).ready(function(){
                        bootbox.confirm({
                            locale: "br",
                            title: "Excluir Registro",
                            message: " Deseja realmente excluir este registro?",
                            buttons: {
                                confirm: {
                                    label: "<p style='padding:0px 15px 0px 15px;margin:0px;'>Sim</p>",
                                    className: 'btn-success'
                                },
                                cancel: {
                                    label: "<p style='padding:0px 15px 0px 15px;margin:0px;'>N�o</p>",
                                    className: 'btn-danger'
                                }
                            },
                            callback: function(result) {
                                    if (result){
                                        ExecutaExclusao(true,cargo);
                                    }
                                }
                        });
                
                        function ExecutaExclusao(apagar,dados)
                        {
                            if (apagar){

                                showProcessandoAguarde();

                                $.ajax({
                                    url: "tabcargo.php",
                                    type: "POST",
                                    data: "autorizacao=true&id=" + dados

                                }).done(function(resultado) {
                                    console.log(resultado);
                                    hideProcessandoAguarde();
                                    mostraMensagem( 'Exclu�do o cargo "' + dados + '" com sucesso.', 'success', 'tabcargo.php' );

                                }).fail(function(jqXHR, textStatus ) {
                                    console.log("Request failed: " + textStatus);
                                    hideProcessandoAguarde();

                                }).always(function() {
                                    console.log("completou");
                                    hideProcessandoAguarde();

                                });

                            }else{
                                event.preventDefault();
                            }
                        }
                    });
                })(jQuery);
            }
        </script>
        <?php
    }


    /**
     * @info Exibe formul�rio para inclus�o de Isen��o de Ponto
     *
     * @param string $retorno Informa��o do resultado da grava��o
     * @param array  $opcoes  Tipo a que pertence a Isen��o
     * @return HTML
     */
    public function showFormularioIncluirCargo( $retorno=null )
    {
        $sLotacao        = $_SESSION["sLotacao"];
        $mensagemUsuario = $_SESSION["mensagem-usuario"];

        parent::setJSSelect2();
        parent::setSubTitulo( "Tabela de Cargo - Inclus�o" );

        // Topo do formul�rio
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML();

        $this->formularioJavaScript( 'Inclus�o', $retorno, 'tabcargo_incluir.php' );
        $this->formularioHtml();
    }


    /**
     * @info Exibe formul�rio para altera��o de Isen��o de Ponto
     *
     * @param string $retorno Informa��o do resultado da grava��o
     * @param array  $opcoes  Tipo a que pertence a Isen��o
     * @return HTML
     */
    public function showFormularioAlterarCargo( $dados=null, $retorno=null )
    {
        $sLotacao        = $_SESSION["sLotacao"];
        $mensagemUsuario = $_SESSION["mensagem-usuario"];

        parent::setJSSelect2();
        parent::setSubTitulo( "Tabela de Cargo - Altera��o" );

        // Topo do formul�rio
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML();

        $this->formularioJavaScript( 'Altera��o', $retorno, 'tabcargo_alterar.php' );
        $this->formularioHtml( "Altera��o", $dados->fetch_object() );
    }
    
    
    /**
     * @info Formul�rio para manuten��o de Isen��o de Ponto
     *
     * @param string $dados  Dados de isen��o de ponto
     * @return HTML
     */
    public function formularioHtml( $acao=null, $dados=null )
    {
        if (is_null($dados))
        {
            $dados = new stdClass();
            $dados->codigo    = "";
            $dados->nome      = "";
            $dados->permite   = "";
            $dados->subsidios = "S";
            
            $inclusao = true;
        }
        else
        {
            $inclusao = false;
        }
        
        ?>
        <div class="portlet-body form">

            <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

            <form id="form1" name="form1" method="POST" action="#" onsubmit="javascript:retrun false;">
                <input type="hidden" value="<?= $dados->COD_CARGO; ?>" name="id">
                
                <div class="row">
                    <div class="col-lg-2 col-md-2 col-xs-2 col-sm-2 margin-10">
                        <font class="ft_13_003">C�digo:</font>
                        &nbsp;<input type="text"
                                     id="codigo" name="codigo"
                                     class="form-control uppercase"
                                     size="6" maxlength="6"
                                     value="<?= $dados->COD_CARGO; ?>"
                                     <?= ($acao=='Altera��o' ? ' readonly' : ''); ?>>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-4 col-xs-4 col-sm-4 margin-10">
                        <font class="ft_13_003">Descri��o:</font>
                        &nbsp;<input type="text"
                                     id="nome" name="nome"
                                     class="form-control uppercase"
                                     size="42" maxlength="42"
                                     value="<?= $dados->DESC_CARGO; ?>" style="width:400px;">
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3 margin-10">
                        <font class="ft_13_003">Permite banco de horas?</font>
                        <select id="permite" name="permite" class="form-control select2-single">
                            <option value='N�O' <?= ($dados->PERMITE_BANCO != 'SIM' ? ' selected' : ''); ?>>N&atilde;o</option>
                            <option value='SIM' <?= ($dados->PERMITE_BANCO == 'SIM' ? ' selected' : ''); ?>>Sim</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3 margin-10">
                        <font class="ft_13_003">Subs�dios?</font>
                        <select id="subsidios" name="subsidios" class="form-control select2-single">
                            <option value='N�O' <?= ($dados->SUBSIDIOS != 'SIM' ? ' selected' : ''); ?>>N&atilde;o</option>
                            <option value='SIM' <?= ($dados->SUBSIDIOS == 'SIM' ? ' selected' : ''); ?>>Sim</option>
                        </select>
                    </div>
                </div>

                <?php if ($inclusao == true): ?>

                <input type='hidden' id="ativo" name="ativo" value="S">
                
                <?php else: ?>

                <div class="row">
                    <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3 margin-10">
                        <font class="ft_13_003">Ativo?</font>
                        <select id="ativo" name="ativo" class="form-control select2-single">
                            <option value='N�O' <?= ($dados->ativo != 'S' ? ' selected' : ''); ?>>N&atilde;o</option>
                            <option value='SIM' <?= ($dados->ativo == 'S' ? ' selected' : ''); ?>>Sim</option>
                        </select>
                    </div>
                </div>
                
                <?php endif; ?>

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
                               href="javascript:window.location.replace('tabcargo.php')" role="button">
                                <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                            </a>
                        </div>
                        <div class="col-md-2"></div>
                    </div>
                </div>
            </form>
        </div>
        <?php
        
        // Base do formul�rio
        parent::exibeCorpoBaseHTML();
        parent::exibeBaseHTML();
    }


    /**
     * @info Fun��es Javascript - Inclus�o de Registro
     *
     * @param string $retorno
     * @param string $destino
     * @return script javascript
     */
    public function FormularioJavaScript( $acao='Inclus�o', $retorno=null, $destino=null)
    {
        ?>
        <script>
            $(document).ready(function () {

                // Set the "bootstrap" theme as the default theme for all Select2
                // widgets.
                //
                // @see https://github.com/select2/select2/issues/2927
                $.fn.select2.defaults.set("theme", "bootstrap");

                var placeholder = "Selecione uma Op��o";

                $(".select2-single").select2({
                    placeholder: placeholder,
                    width: null,
                    containerCssClass: ':all:'
                });

                // TESTE DO FORMUL�RIO INCLUS�O
                <?php if ($retorno == "ja_existe"): ?>
                    mostraMensagem('Cargo j� cadastrado!', 'danger', "tabcargo.php");
                <?php endif; ?>

                // TESTE DO FORMUL�RIO ALTERA��O
                <?php if ($retorno == "nao_existe"): ?>
                    mostraMensagem('Cargo n�o encontrado!', 'danger', "tabcargo.php");
                <?php endif; ?>

                <?php if ($retorno == "gravou"): ?>
                    mostraMensagem('<?= $acao; ?> realizada com sucesso!', 'success', "tabcargo.php");
                <?php endif; ?>

                <?php if ($retorno == "nao_gravou"): ?>
                    mostraMensagem('<?= $acao; ?> N�O foi realizada!', 'danger', "tabcargo.php");
                <?php endif; ?>


                $('#btn-salvar').on('click', function () {
                    if(!validateForm()){
                        return false;
                    }
                    
                    var oForm = jQuery('#form1');

                    oForm.attr('onsubmit', 'javascript:return true');
                    oForm.attr('action', '<?= $destino; ?>');
                    oForm.submit();

                    return false;
                });
            });

            function validateForm() {

                var codigo = $("[name='codigo']").val();
                var texto  = $("[name='nome']").val();

                // Verifica se c�digo (sigla) foi informado
                if (codigo == "") {
                    mostraMensagem('C�digo � obrigat�rio!', 'warning');
                    return false;
                }

                if(texto == ""){
                    mostraMensagem('Texto descri��o � obrigat�rio!', 'warning');
                    return false;
                }

                return true;
            }
        </script>
        <?php
    }

    /**
     * 
     * @param string $retorno Tipo do resultado
     * @param string $descricao Descri��o do item
     * @return void
     */
    public function deleteView($retorno, $descricao)
    {
        switch($retorno)
        {
            case 'success':
                $tipo     = "success";
                $mensagem = "Exclu�do com sucesso o cargo " . $descricao . ".";
                break;

            case 'warning':
                $tipo     = "warning";
                $mensagem = "Exclus�o N�O realizada, h� Servidor(a) registrado com Cargo (" . $descricao . ").";
                break;

            case 'error':
                $tipo     = "danger";
                $mensagem = "N�O houve sucesso na exclus�o do cargo " . $descricao . ".";
                break;
        }

        retornaInformacao( $mensagem, $tipo);
    }

} // END class TabCargoView
