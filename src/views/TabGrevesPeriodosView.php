<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabGrevesPeriodosView
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : greves_periodos
 *       Suas descrições e características
 *
 * @Camada    - View
 * @Diretório - src/models
 * @Arquivo   - TabGrevesPeriodosView.php
 *
 * @author Edinalvo Rosa
 */
class TabGrevesPeriodosView extends formPadrao
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
     * @info Exibe a lista de Isenção de Ponto
     *
     * @param object $dados
     * @return HTML
     */
    public function showLista($retorno="")
    {
        $sLotacao        = $_SESSION["sLotacao"];
        $mensagemUsuario = $_SESSION["mensagem-usuario"];

        parent::setDataTables();
        parent::setSubTitulo( "Tabela de Greves - Manutenção" );

        // Topo do formulário
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML('1250px');

        $this->JavaScript($retorno);
        
        ?>
        <form name='form1' id='form1' method='POST' action='#' onsubmit='javascript:return false;'> 
            <input type="hidden" name="id" id="id">
                    
            <div class="row">
                <div class="col-md-12 text-left margin-25 margin-bottom-10">
                    <button type="button" class="btn btn-default btn_Adicionar" style="padding-left:30px;padding-right:30px;">Adicionar</button>
                </div>
                            
                <table id="tabela_dados" class="display nowrap table-hover table table-striped table-bordered" role="grid" style="width:1050px;">
                    <thead>
                        <tr>
                            <th>Ações</th>
                            <th class="sorting" style="width:100px;">ID</th>
                            <th class="sorting nowrap" style="width:550px;">Descrição</th>
                            <th class="sorting">Início</th>
                            <th class="sorting">Fim</th>
                            <th class="sorting">Ativo</th>
                        </tr>
                    </thead>
                </table>
                            
            </div>
        </form>
        <?php
        
        // Base do formulário
        parent::exibeCorpoBaseHTML();
        parent::exibeBaseHTML();
    }


    /**
     * @info Funções Javascript
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
                $(".btn_Adicionar").on('click', function () {
                    var oForm = $('#form1');
                    
                    showProcessandoAguarde();

                    oForm.attr('onsubmit', 'javascript:return true');
                    oForm.attr('action', 'tabgreves_incluir.php');
                    oForm.submit();
                });

                $(".btn_Alterar").click( function(event) {
                    var oForm   = jQuery('#form1');
                    var id      = $(this).attr('data-id');
                    
                    showProcessandoAguarde();

                    $('#id').val( id );
                    
                    oForm.attr('onsubmit', 'javascript:return true');
                    oForm.attr('action', 'tabgreves_alterar.php');
                    oForm.submit();
                });
                
                var tabela = $('#tabela_dados').DataTable({
                    "processing": true,
                    "ajax": {
                        "url": "tabgreves_lista.php",
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
            
            function DeleteGreves(greves)
            {
                (function($){ // recebendo como parâmetro na variável '$'
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
                                    label: "<p style='padding:0px 15px 0px 15px;margin:0px;'>Não</p>",
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
                                    url: "tabgreves.php",
                                    type: "POST",
                                    data: "autorizacao=true&id=" + dados

                                }).done(function(resultado) {
                                    console.log(resultado);
                                    hideProcessandoAguarde();
                                    mostraMensagem( 'Excluído o período de greve "' + dados + '" com sucesso.', 'success', 'tabgreves.php' );

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
     * @info Exibe formulário para inclusão de greves - períodos
     *
     * @param string $retorno Informação do resultado da gravação
     * @param array  $opcoes  Tipo a que pertence a Isenção
     * @return HTML
     */
    public function showFormularioIncluirGreve( $retorno=null )
    {
        $sLotacao        = $_SESSION["sLotacao"];
        $mensagemUsuario = $_SESSION["mensagem-usuario"];

        parent::setJSSelect2();
        parent::setSubTitulo( "Tabela de Greves - Inclusão" );

        // Topo do formulário
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML();

        $this->formularioJavaScript( 'Inclusão', $retorno, 'tabgreves_incluir.php' );
        $this->formularioHtml();
    }


    /**
     * @info Exibe formulário para alteração de greves - períodos
     *
     * @param string $retorno Informação do resultado da gravação
     * @param array  $opcoes  Tipo a que pertence
     * @return HTML
     */
    public function showFormularioAlterarGreve( $dados=null, $retorno=null )
    {
        $sLotacao        = $_SESSION["sLotacao"];
        $mensagemUsuario = $_SESSION["mensagem-usuario"];

        parent::setJSSelect2();
        parent::setSubTitulo( "Tabela de Greves - Alteração" );

        // Topo do formulário
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML();

        $this->formularioJavaScript( 'Alteração', $retorno, 'tabgreves_alterar.php' );
        $this->formularioHtml( "Alteração", $dados->fetch_object() );
    }
    
    
    /**
     * @info Formulário para manutenção de Isenção de Ponto
     *
     * @param string $dados  Dados de isenção de ponto
     * @return HTML
     */
    public function formularioHtml( $acao=null, $dados=null )
    {
        if (is_null($dados))
        {
            $dados = new stdClass();
            $dados->id        = "";
            $dados->descricao = "";
            $dados->inicio    = "";
            $dados->fim       = "";
            $dados->ativo     = "S";
            
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
                <input type="hidden" value="<?= $dados->id; ?>" name="id">
                
                <div class="row">
                    <div class="col-lg-2 col-md-2 col-xs-2 col-sm-2 margin-10">
                        <font class="ft_13_003">Código:</font>
                        &nbsp;<input type="text"
                                     id="codigo" name="codigo"
                                     class="form-control uppercase"
                                     size="6" maxlength="6"
                                     value="<?= $dados->id; ?>"
                                     <?= ($acao=='Alteração' ? ' readonly' : ''); ?>>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-4 col-xs-4 col-sm-4 margin-10">
                        <font class="ft_13_003">Descrição:</font>
                        &nbsp;<input type="text"
                                     id="nome" name="nome"
                                     class="form-control uppercase"
                                     size="42" maxlength="42"
                                     value="<?= $dados->descricao; ?>" style="width:400px;">
                    </div>
                </div>

                <?php if ($inclusao == true): ?>

                <input type='hidden' id="ativo" name="ativo" value="S">
                
                <?php else: ?>

                <div class="row">
                    <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3 margin-10">
                        <font class="ft_13_003">Ativo?</font>
                        <select id="ativo" name="ativo" class="form-control select2-single">
                            <option value='NÃO' <?= ($dados->ativo != 'S' ? ' selected' : ''); ?>>N&atilde;o</option>
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
        
        // Base do formulário
        parent::exibeCorpoBaseHTML();
        parent::exibeBaseHTML();
    }


    /**
     * @info Funções Javascript - Inclusão de Registro
     *
     * @param string $retorno
     * @param string $destino
     * @return script javascript
     */
    public function FormularioJavaScript( $acao='Inclusão', $retorno=null, $destino=null)
    {
        ?>
        <script>
            $(document).ready(function () {

                // Set the "bootstrap" theme as the default theme for all Select2
                // widgets.
                //
                // @see https://github.com/select2/select2/issues/2927
                $.fn.select2.defaults.set("theme", "bootstrap");

                var placeholder = "Selecione uma Opção";

                $(".select2-single").select2({
                    placeholder: placeholder,
                    width: null,
                    containerCssClass: ':all:'
                });

                // TESTE DO FORMULÁRIO INCLUSÃO
                <?php if ($retorno == "ja_existe"): ?>
                    mostraMensagem('Peíodo de Greve já cadastrado!', 'danger', "tabgreves.php");
                <?php endif; ?>

                // TESTE DO FORMULÁRIO ALTERAÇÃO
                <?php if ($retorno == "nao_existe"): ?>
                    mostraMensagem('Período de Greve não encontrado!', 'danger', "tabgreves.php");
                <?php endif; ?>

                <?php if ($retorno == "gravou"): ?>
                    mostraMensagem('<?= $acao; ?> realizada com sucesso!', 'success', "tabgreves.php");
                <?php endif; ?>

                <?php if ($retorno == "nao_gravou"): ?>
                    mostraMensagem('<?= $acao; ?> NÃO foi realizada!', 'danger', "tabgreves.php");
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

                var codigo = $("[name='id']").val();
                var texto  = $("[name='descricao']").val();

                // Verifica se código (sigla) foi informado
                if (codigo == "") {
                    mostraMensagem('Código é obrigatório!', 'warning');
                    return false;
                }

                if(texto == ""){
                    mostraMensagem('Texto descrição é obrigatório!', 'warning');
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
     * @param string $descricao Descrição do item
     * @return void
     */
    public function deleteView($retorno, $descricao)
    {
        switch($retorno)
        {
            case 'success':
                $tipo     = "success";
                $mensagem = "Excluído com sucesso o período de greve " . $descricao . ".";
                break;

            case 'warning':
                $tipo     = "warning";
                $mensagem = "Exclusão NÃO realizada, há Servidor(a) registrado com período de greve (" . $descricao . ").";
                break;

            case 'error':
                $tipo     = "danger";
                $mensagem = "NÃO houve sucesso na exclusão do período de greve " . $descricao . ".";
                break;
        }

        retornaInformacao( $mensagem, $tipo);
    }

} // END class TabGrevesPeriodosView
