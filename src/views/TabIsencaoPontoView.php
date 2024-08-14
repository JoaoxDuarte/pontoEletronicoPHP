<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabIsencaoPontoView
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : tabisencao_ponto
 *       Suas descrições e características
 *
 * @Camada    - View
 * @Diretório - inc/models
 * @Arquivo   - TabIsencaoPontoView.php
 *
 * @author Edinalvo Rosa
 */
class TabIsencaoPontoView extends formPadrao
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
     * @info Monta <select> com tipos para isenção
     *
     * @param array $opcoes
     * @param string/null $tipo
     * @return HTML
     */
    public function listaTipoParaIsencao( $opcoes=null, $tipo=null )
    {
        $opcoes_tipo = "";

        if ( !is_null($opcoes) )
        {
            foreach($opcoes AS $value)
            {
                $selected = ($value == $tipo ? " selected" : "");
                $opcoes_tipo .= "<option value='".tratarHTML($value)."'".tratarHTML($selected).">".tratarHTML($value)."</option>";
            }

            ?>
            <select id="tipo" name="tipo" class="form-control select2-single">
                <?=  $opcoes_tipo; ?>
            </select>
            <?php
        }
    }


    /**
     * @info Monta <select> com lista das tabelas
     *
     * @param array $opcoes
     * @return HTML
     */
    public function listaTabelas( $opcoes=null )
    {
        $opcoes_tipo = "";

        if ( !is_null($opcoes) )
        {
            foreach($opcoes AS $key => $value)
            {
                $opcoes_tipo .= "<option value='".tratarHTML($key)."'>".tratarHTML($value)."</option>";
            }

            ?>
            <select id="tabelas" name="tabelas" class="form-control select2-single">
                <?= $opcoes_tipo; ?>
            </select>
            <?php
        }
    }


    /**
     * @info Exibe a lista de Isenção de Ponto
     *
     * @param object $dados
     * @return HTML
     */
    public function showListaIsencaoPonto( $dados=null )
    {
        parent::setCSS('css/new/sorter/css/theme.bootstrap_3.min.css');
        parent::setJS('css/new/sorter/js/jquery.tablesorter.min.js');
        parent::setSubTitulo( "Tabela de Isenção de Registro de Frequência" );

        // Topo do formulário
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML();

        $this->JavaScript();
        
        ?>
        <div class="container">

            <div class="row margin-10">

                <div class="col-md-10 col-md-offset-1">

                    <br>
                    <form name='form1' id='form1' method='POST' action='#' onsubmit='javascript:return false;'> 
                        <input type="hidden" name="id" id="id">
                    
                        <div class="row">
                            <div class="col-md-12 text-right margin-bottom-10">
                                <button type="button" class="btn btn-default btn_adicionar_isencao" style="padding-left:30px;padding-right:30px;">Adicionar</button>
                            </div>
                            <fieldset class="col-md-3" width='100%'>Total de <?= tratarHTML($dados->num_rows()); ?> registros.</fieldset>
                            <table id="myTable" class="table table-striped table-bordered table-hover text-center table-condensed tablesorter">
                                <thead>
                                <tr>
                                    <th class="text-center" style='vertical-align:middle;'>Código</th>
                                    <th class="text-center" style='vertical-align:middle;width:30%;'>Descrição</th>
                                    <th class="text-center" style='vertical-align:middle;'>Tipo</th>
                                    <th class="text-center" style='vertical-align:middle;'>Tabela</th>
                                    <th class="text-center" style='vertical-align:middle;'>Isenção<br>Obrigatória</th>
                                    <th class="text-center" style='vertical-align:middle;'>Ativo</th>
                                    <th class="text-center" style='vertical-align:middle;'>Ações</th>
                                </tr>
                                </thead>
                                <tbody>

                                <?php while ($configuracao = $dados->fetch_object()): ?>

                                    <tr>
                                        <td align='left'><?= tratarHTML($configuracao->codigo); ?></td>
                                        <td align='left'><?= tratarHTML($configuracao->texto); ?></td>
                                        <td align='left'><?= tratarHTML($configuracao->tipo); ?></td>
                                        <td align='left'><?= tratarHTML($configuracao->tabela); ?></td>
                                        <td align='ceneter'><?= ($configuracao->obrigatorio_isencao=='S'?'Sim':'Não'); ?></td>
                                        <td align='center'><?= ($configuracao->ativo=='S'?'Sim':'Não'); ?></td>

                                        <td align='right'>
                                            <div class="col-md-6">
                                                <a class="btn_Alterar" data-id='<?= tratarHTML($configuracao->id); ?>' style="cursor:pointer;"><img border='0' src='<?= _DIR_IMAGEM_; ?>edicao2.jpg' width='16' height='16' align='absmiddle' alt='Editar' title='Editar' /></a>
                                            </div>
                                            <div class="col-md-2">
                                                <a class='btn_Excluir' data-id='<?= tratarHTML($configuracao->id); ?>' data-item='<?= tratarHTML($configuracao->codigo). ' ' . tratarHTML($configuracao->texto); ?>' style="cursor:pointer;"><img border='0' src='<?= _DIR_IMAGEM_; ?>lixeira2.jpg' width='16' height='16' alt='Excluir' title='Excluir'></a>
                                            </div>
                                        </td>
                                    </tr>

                                <?php endwhile; ?>

                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
    public function JavaScript()
    {
        ?>
        <script>
            $(document).ready(function ()
            {
                $(".btn_adicionar_isencao").on('click', function () {
                    var oForm = $('#form1');
                    
                    showProcessandoAguarde();

                    oForm.attr('onsubmit', 'javascript:return true');
                    oForm.attr('action', 'tabisencao_ponto_incluir.php');
                    oForm.submit();
                });

                $(".btn_Alterar").click( function(event) {
                    var oForm   = jQuery('#form1');
                    var id      = $(this).attr('data-id');
                    
                    showProcessandoAguarde();

                    $('#id').val( id );
                    
                    oForm.attr('onsubmit', 'javascript:return true');
                    oForm.attr('action', 'tabisencao_ponto_alterar.php');
                    oForm.submit();
                });

                $(".btn_Excluir").click( function(event) {
                    var dados = $(this).attr('data-id');
                    var item  = $(this).attr('data-item');
                    
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
                                ExecutaExclusao(true,dados,item);
                            }
                        }
                    });
                });
                
                function ExecutaExclusao(apagar,dados,item)
                {
                    if (apagar){

                        showProcessandoAguarde();

                        $.ajax({
                            url: "tabisencao_ponto.php",
                            type: "POST",
                            data: "autorizacao=true&id=" + dados

                        }).done(function(resultado) {
                            console.log(resultado);
                            hideProcessandoAguarde();
                            mostraMensagem( 'Excluído o item "' + item + '" com sucesso.', 'success', 'tabisencao_ponto.php' );

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
        </script>
        <?php
    }


    /**
     * @info Exibe formulário para inclusão de Isenção de Ponto
     *
     * @param string $retorno Informação do resultado da gravação
     * @param array  $opcoes  Tipo a que pertence a Isenção
     * @return HTML
     */
    public function showFormularioIncluirIsencaoPonto( $retorno=null, $opcoes=null )
    {
        $sLotacao        = $_SESSION["sLotacao"];
        $mensagemUsuario = $_SESSION["mensagem-usuario"];

        parent::setJSSelect2();
        parent::setSubTitulo( "Cadastro da Isenção de Registro de Frequência" );

        // Topo do formulário
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML();

        $this->formularioJavaScript( 'Inclusão', $retorno, 'tabisencao_ponto_incluir.php' );
        $this->formularioHtml( null, $opcoes );
    }


    /**
     * @info Exibe formulário para alteração de Isenção de Ponto
     *
     * @param string $retorno Informação do resultado da gravação
     * @param array  $opcoes  Tipo a que pertence a Isenção
     * @return HTML
     */
    public function showFormularioAlterarIsencaoPonto( $dados=null, $retorno=null, $opcoes=null )
    {
        $sLotacao        = $_SESSION["sLotacao"];
        $mensagemUsuario = $_SESSION["mensagem-usuario"];

        parent::setJSSelect2();
        parent::setSubTitulo( "Alterar Cadastro de Isenção de Registro de Frequência" );

        // Topo do formulário
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML();

        $this->formularioJavaScript( 'Alteração', $retorno, 'tabisencao_ponto_alterar.php' );
        $this->formularioHtml( $dados->fetch_object(), $opcoes );
    }
    
    
    /**
     * @info Formulário para manutenção de Isenção de Ponto
     *
     * @param string $dados  Dados de isenção de ponto
     * @return HTML
     */
    public function formularioHtml( $dados=null, $opcoes=null )
    {
        if (is_null($dados))
        {
            $dados = new stdClass();
            $dados->id                  = "0";
            $dados->codigo              = "";
            $dados->texto               = "";
            $dados->tipo                = "";
            $dados->tabela              = "";
            $dados->obrigatorio_isencao = "N";
            $dados->ativo               = "S";
            
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
                    <div class="col-lg-4 col-md-4 col-xs-4 col-sm-4 margin-10">
                        <font class="ft_13_003">Código:</font>
                        &nbsp;<input type="text"
                                     id="codigo" name="codigo"
                                     class="form-control uppercase"
                                     size="20" maxlength="20"
                                     value="<?= $dados->codigo; ?>" style="width:400px;">
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-4 col-xs-4 col-sm-4 margin-10">
                        <font class="ft_13_003">Descrição:</font>
                        &nbsp;<input type="text"
                                     id="texto" name="texto"
                                     class="form-control uppercase"
                                     size="30" maxlength="30"
                                     value="<?= $dados->texto; ?>" style="width:400px;">
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-4 col-xs-4 col-sm-4 margin-10">
                        <font class="ft_13_003">Tipo:</font>
                        <?php $this->listaTipoParaIsencao( $opcoes, $dados->tipo ); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-4 col-xs-4 col-sm-4 margin-10">
                        <font class="ft_13_003">Isenção Obrigatória:</font>
                        <select id="obrigatorio_isencao" name="obrigatorio_isencao" class="form-control select2-single">
                            <option value='N' <?= ($dados->obrigatorio_isencao != 'S' ? ' selected' : ''); ?>>N&atilde;o</option>
                            <option value='S' <?= ($dados->obrigatorio_isencao == 'S' ? ' selected' : ''); ?>>Sim</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-4 col-xs-4 col-sm-4 margin-10">
                        <?php if ( $inclusao == true ): ?>
                            <input type="hidden" value="<?= $dados->ativo; ?>" name="ativo">
                        <?php else: ?>
                            <font class="ft_13_003">Ativo:</font>
                            <select id="ativo" name="ativo" class="form-control select2-single">
                                <option value='N' <?= ($dados->ativo != 'S' ? ' selected' : ''); ?>>N&atilde;o</option>
                                <option value='S' <?= ($dados->ativo == 'S' ? ' selected' : ''); ?>>Sim</option>
                            </select>
                        <?php endif; ?>
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
                               href="javascript:window.location.replace('tabisencao_ponto.php')" role="button">
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

                var placeholder = "Selecione uma Ocorrência";

                $(".select2-single").select2({
                    placeholder: placeholder,
                    width: null,
                    containerCssClass: ':all:'
                });

                // TESTE DO FORMULÁRIO INCLUSÃO
                <?php if ($retorno == "ja_existe"): ?>
                    mostraMensagem('Item já consta do Cadastro de Isenção de Frequência!', 'danger', "tabisencao_ponto.php");
                <?php endif; ?>

                // TESTE DO FORMULÁRIO ALTERAÇÃO
                <?php if ($retorno == "nao_existe"): ?>
                    mostraMensagem('Item não encontrado no Cadastro de Isenção de Frequência!', 'danger', "tabisencao_ponto.php");
                <?php endif; ?>

                <?php if ($retorno == "gravou"): ?>
                    mostraMensagem('<?= $acao; ?> realizada com sucesso!', 'success', "tabisencao_ponto.php");
                <?php endif; ?>

                <?php if ($retorno == "nao_gravou"): ?>
                    mostraMensagem('<?= $acao; ?> NÃO foi realizada!', 'danger', "tabisencao_ponto.php");
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
                var texto  = $("[name='texto']").val();

                // Verifica se código (sigla) foi informado
                if (codigo == "") {
                    mostraMensagem('Código (sigla) é obrigatório!', 'warning');
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

} // END class TabIsencaoPontoView
