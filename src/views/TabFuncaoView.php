<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");


/**
 * @class TabFuncaoView
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : tabfunc
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - View
 * @Diret�rio - src/views
 * @Arquivo   - TabFuncaoView.php
 *
 * @author Edinalvo Rosa
 */
class TabFuncaoView extends formPadrao
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
        parent::setSubTitulo( "Tabela de Fun��o - Manuten��o" );

        // Topo do formul�rio
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML('1250px');

        $this->JavaScript($retorno);
        
        ?>
        <form name='form1' id='form1' method='POST' action='#' onsubmit='javascript:return false;'> 
            <input type="hidden" name="id" id="id">
            <input type="hidden" name="acaoMetodo" id="acaoMetodo" value="<?= (empty($retorno) ? "incluir" : "excluir"); ?>">
                    
            <div class="row">
                <div class="col-md-12 text-left margin-25 margin-bottom-10">
                    <button type="button" class="btn btn-default btn_adicionar" style="padding-left:30px;padding-right:30px;">Adicionar</button>
                </div>
                            
                <table id="tabela_dados" class="display nowrap table-hover table table-striped table-bordered" role="grid" style="width:1050px;">
                    <thead>
                        <tr>
                            <th>A��es</th>
                            <th class="sorting">N�mero<br>da Fun��o</th>
                            <th class="sorting">C�digo<br>da Fun��o</th>
                            <th class="sorting">Nome da Fun��o</th>
                            <th class="sorting">UNIDADE</th>
                            <th class="sorting">UPAG</th>
                            <th class="sorting">Indica<br>Substituto?</th>
                            <th class="sorting">Respons�vel<br>pela UORG</th>
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
                $(".btn_adicionar").on('click', function () {
                    var oForm = $('#form1');
                    
                    showProcessandoAguarde();

                    $("#acaoMetodo").val( "incluir" );
                    
                    oForm.attr('onsubmit', 'javascript:return true');
                    oForm.attr('action', 'tabfuncao.php');
                    oForm.submit();
                });

                $(".btn_Alterar").click( function(event) {
                    var oForm   = jQuery('#form1');
                    var id      = $(this).attr('data-id');
                    
                    showProcessandoAguarde();

                    $("#acaoMetodo").val( "alterar" );

                    $('#id').val( id );
                    
                    oForm.attr('onsubmit', 'javascript:return true');
                    oForm.attr('action', 'tabfuncao.php');
                    oForm.submit();
                });
                
                var tabela = $('#tabela_dados').DataTable({
                    "processing": true,
                    "ajax": {
                        "url": "tabfuncao_lista.php",
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
            
            function funcaoAlterar(id)
            {
                var oForm = jQuery('#form1');
                
                $("#acaoMetodo").val( "alterar" );

                $('#id').val( id );
                    
                oForm.attr('onsubmit', 'javascript:return true');
                oForm.attr('action', 'tabfuncao.php');
                oForm.submit();
            }
            
            function Delete(codigo)
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
                                        ExecutaExclusao(true,codigo);
                                    }
                                }
                        });
                
                        function ExecutaExclusao(apagar,dados)
                        {
                            if (apagar){

                                showProcessandoAguarde();

                                $.ajax({
                                    url: "tabfuncao.php",
                                    type: "POST",
                                    data: "autorizacao=true&id=" + dados

                                }).done(function(resultado) {
                                    console.log(resultado);
                                    hideProcessandoAguarde();
                                    mostraMensagem( 'Exclu�da a fun��o "' + dados + '" com sucesso.', 'success', 'tabfuncao.php' );

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
    public function showFormularioIncluir( $retorno=null, $codigos_funcoes=null, $codigos_setores=null )
    {
        $sLotacao        = $_SESSION["sLotacao"];
        $mensagemUsuario = $_SESSION["mensagem-usuario"];

        parent::setJSSelect2();
        parent::setSubTitulo( "Tabela de Fun��o - Inclus�o" );

        // Topo do formul�rio
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML();

        $this->formularioJavaScript( 'Inclus�o', $retorno, 'tabfuncao.php' );
        $this->formularioHtml( $acao=null, $dados=null, $codigos_funcoes, $codigos_setores );
    }


    /**
     * @info Exibe formul�rio para altera��o de Isen��o de Ponto
     *
     * @param string $retorno Informa��o do resultado da grava��o
     * @param array  $opcoes  Tipo a que pertence a Isen��o
     * @return HTML
     */
    public function showFormularioAlterar( $dados_funcoes=null, $retorno=null, $codigos_funcoes=null, $codigos_setores=null )
    {
        $sLotacao        = $_SESSION["sLotacao"];
        $mensagemUsuario = $_SESSION["mensagem-usuario"];

        parent::setJSSelect2();
        parent::setSubTitulo( "Tabela de Fun��o - Altera��o" );

        // Topo do formul�rio
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML();

        $this->formularioJavaScript( 'Altera��o', $retorno, 'tabfuncao.php' );
        $this->formularioHtml( "Altera��o", $dados_funcoes, $codigos_funcoes, $codigos_setores );
    }
    
    
    /**
     * @info Formul�rio para manuten��o de Isen��o de Ponto
     *
     * @param string $dados  Dados de isen��o de ponto
     * @return HTML
     */
    public function formularioHtml( $acao=null, $dados_funcoes=null, $codigos_funcoes=null, $codigos_setores=null )
    {
        if (is_null($dados_funcoes))
        {
            $dados = new stdClass();
            $dados->NUM_FUNCAO = "";
            $dados->COD_FUNCAO = "";
            $dados->DESC_FUNC  = "";
            $dados->COD_LOT    = "";
            $dados->UPAG       = "";
            $dados->SIT_PAG    = "N";
            $dados->INDSUBS    = "S";
            $dados->RESP_LOT   = "S";
            $dados->ATIVO      = "S";
            
            $inclusao = true;
        }
        else
        {
            $inclusao = false;
            $dados = $dados_funcoes->fetch_object();
        }

        ?>
        <div class="portlet-body form">

            <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

            <form id="form1" name="form1" method="POST" action="#" onsubmit="javascript:retrun false;">
                <input type="hidden" value="<?= $dados->NUM_FUNCAO; ?>" name="id">
                <input type="hidden" name="acaoMetodo" id="acaoMetodo" value="<?= (is_null($acao) ? "incluir" : "alterar"); ?>">
                
                <?php if ($acao == 'Altera��o'): ?>
                
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6 margin-10">
                        <font class="ft_13_003">Fun��o:</font>
                        &nbsp;<input type="text"
                                     id="NUM_FUNCAO" name="NUM_FUNCAO"
                                     class="form-control"
                                     size="7" maxlength="7"
                                     value="<?= $dados->NUM_FUNCAO; ?>"
                                     readonly>
                    </div>
                </div>

                <?php else: ?>
                
                <input type="hidden" id="NUM_FUNCAO" name="NUM_FUNCAO" value="<?= $dados->NUM_FUNCAO; ?>">
                
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6 margin-10">
                        <font class="ft_13_003">Fun��o:</font>
                        &nbsp;<?php $this->CarregaSelectCodigoFuncoesSiape( $codigos_funcoes, $dados->COD_FUNCAO ); ?>
                    </div>
                </div>

                <!--
                <div class="row">
                    <div class="col-lg-4 col-md-4 col-xs-4 col-sm-4 margin-10">
                        <font class="ft_13_003">Nome da Fun��o:</font>
                        &nbsp;<input type="text"
                                     id="DESC_FUNC" name="DESC_FUNC"
                                     class="form-control uppercase"
                                     size="200" maxlength="200"
                                     placeholder="Digite descri��o da fun��o"
                                     value="<?= $dados->DESC_FUNC; ?>" style="width:400px;">
                    </div>
                </div>
                -->

                <div class="row">
                    <div class="col-lg-8 col-md-8 col-xs-8 col-sm-8 margin-10">
                        <font class="ft_13_003">UORG:</font>
                        &nbsp;<?php $this->CarregaSelectCodigoUnidadePorUpag( $codigos_setores, $dados->COD_LOT ); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3 margin-10">
                        <font class="ft_13_003">UPAG:</font>
                        &nbsp;<input type="text"
                                     id="UPAG" name="UPAG"
                                     class="form-control"
                                     size="14" maxlength="14"
                                     placeholder="C�digo da UPAG"
                                     value="<?= $_SESSION['upag']; ?>"
                                     readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3 margin-10">
                        <font class="ft_13_003">Indica Substituto?</font>
                        <select id="INDSUBS" name="INDSUBS" class="form-control select2-single">
                            <option value='N' <?= ($dados->INDSUBS != 'S' ? ' selected' : ''); ?>>N&atilde;o</option>
                            <option value='S' <?= ($dados->INDSUBS == 'S' ? ' selected' : ''); ?>>Sim</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3 margin-10">
                        <font class="ft_13_003">Respons�vel por UORG?</font>
                        <select id="RESP_LOT" name="RESP_LOT" class="form-control select2-single">
                            <option value='N' <?= ($dados->RESP_LOT != 'S' ? ' selected' : ''); ?>>N&atilde;o</option>
                            <option value='S' <?= ($dados->RESP_LOT == 'S' || ($acao != 'Altera��o') ? ' selected' : ''); ?>>Sim</option>
                        </select>
                    </div>
                </div>

                <?php if ($acao == 'Altera��o'): ?>

                <div class="row">
                    <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3 margin-10">
                        <font class="ft_13_003">Ativo?</font>
                        <select id="ATIVO" name="ATIVO" class="form-control select2-single">
                            <option value='N' <?= ($dados->ATIVO != 'S' ? ' selected' : ''); ?>>N&atilde;o</option>
                            <option value='S' <?= ($dados->ATIVO == 'S' || ($acao != 'Altera��o') ? ' selected' : ''); ?>>Sim</option>
                        </select>
                    </div>
                </div>

                <?php else: ?>
                
                <input type="hidden" id="ATIVO" name="ATIVO" value="S">
                
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
                               href="javascript:window.location.replace('tabfuncao.php')" role="button">
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
                <?php if ($retorno == "so_um_responsavel"): ?>
                    mostraMensagem('J� h� Fun��o respons�vel pela Unidade!', 'danger', "tabfuncao.php");
                <?php endif; ?>

                <?php if ($retorno == "ja_existe"): ?>
                    mostraMensagem('Fun��o j� cadastrada!', 'danger', "tabfuncao.php");
                <?php endif; ?>

                // TESTE DO FORMUL�RIO ALTERA��O
                <?php if ($retorno == "nao_existe"): ?>
                    mostraMensagem('Fun��o n�o encontrada!', 'danger', "tabfuncao.php");
                <?php endif; ?>

                <?php if ($retorno == "gravou"): ?>
                    mostraMensagem('<?= $acao; ?> realizada com sucesso!', 'success', "tabfuncao.php");
                <?php endif; ?>

                <?php if ($retorno == "nao_gravou"): ?>
                    mostraMensagem('<?= $acao; ?> N�O foi realizada!', 'danger', "tabfuncao.php");
                <?php endif; ?>

                <?php if ($retorno == "numero_funcao"): ?>
                    mostraMensagem('N�mero da Fun��o � obrigat�rio!');
                <?php endif; ?>

                <?php if ($retorno == "codigo_funcao"): ?>
                    mostraMensagem('C�digo da Fun��o � obrigat�rio!');
                <?php endif; ?>
                
                <?php if ($retorno == "descricao_funcao"): ?>
                    //mostraMensagem('Descri��o da Fun��o � obrigat�rio!');
                <?php endif; ?>
                
                <?php if ($retorno == "codigo_uorg"): ?>
                    mostraMensagem('C�digo da UORG � obrigat�rio!');
                <?php endif; ?>
                
                <?php if ($retorno == "codigo_upag"): ?>
                    //mostraMensagem('C�digo da UPAG � obrigat�rio!');
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

                var NUM_FUNCAO = $("[name='NUM_FUNCAO']").val();
                var COD_FUNCAO = $("[name='COD_FUNCAO']").val();
                var DESC_FUNC  = $("[name='DESC_FUNC']").val();
                var COD_LOT    = $("[name='COD_LOT']").val();
                var UPAG       = $("[name='UPAG']").val();
                var acao       = "<?= $acao; ?>"

                if (acao == "Altera��o" && NUM_FUNCAO == "") {
                    mostraMensagem('N�mero da Fun��o � obrigat�rio!', 'warning');
                    return false;
                }

                if (COD_FUNCAO == "") {
                    mostraMensagem('C�digo da Fun��o � obrigat�rio!', 'warning');
                    return false;
                }

                if (DESC_FUNC == "") {
                    mostraMensagem('Descri��o da Fun��o � obrigat�rio!', 'warning');
                    return false;
                }

                if (COD_LOT == "") {
                    mostraMensagem('C�digo da UORG � obrigat�rio!', 'warning');
                    return false;
                }

                if (UPAG == "") {
                    mostraMensagem('C�digo da UPAG � obrigat�rio!', 'warning');
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
                $mensagem = "Exclu�da com sucesso a fun��o " . $descricao . ".";
                break;

            case 'warning':
                $tipo     = "warning";
                $mensagem = "Exclus�o N�O realizada, h� Servidor(a) registrado com a Fun��o (" . $descricao . ").";
                break;

            case 'error':
                $tipo     = "danger";
                $mensagem = "N�O houve sucesso na exclus�o da fun��o " . $descricao . ".";
                break;
        }

        retornaInformacao( $mensagem, $tipo);
    }


    /**
     * @info Lista c�digos de fun��es (SIAPE)
     *
     * @param string/null $codigo
     * @param string/null $opcao_selecione
     * 
     * @return object C�digos das fun��es e dewscri��o
     */
    public function CarregaSelectCodigoFuncoesSiape( $dados, $codigo=NULL )
    {
        $registros = $dados->num_rows();
        
        ?>
        <select class="form-control select2-single" id="COD_FUNCAO" name="COD_FUNCAO">
            <option value=''>Selecione uma op��o</option>
            <?php

            while (list($id, $value, $descricao) = $dados->fetch_array())
            {
                $selected = ($value === $codigo ? " selected" : "");

                ?>
                <option value='<?= tratarHTML($value); ?>'<?= tratarHTML($selected); ?>><?= tratarHTML($value . " - " . $descricao); ?></option>
                <?php
            }

            ?>
        </select>
        <small style="font-size:9px;vertical-align:top;margin-top:0px;padding-left:3px;padding-top:0px;">(<?= number_format($registros, 0, ',', '.'); ?> registros)</small>
        <?php
    }


    /**
     * @info Lista c�digos de fun��es (SIAPE)
     *
     * @param string/null $codigo
     * @param string/null $opcao_selecione
     * 
     * @return object C�digos das fun��es e dewscri��o
     */
    public function CarregaSelectCodigoUnidadePorUpag( $dados, $codigo=NULL )
    {
        $registros = $dados->num_rows();

        ?>
        <select class="form-control select2-single" id="COD_LOT" name="COD_LOT">
            <option value=''>Selecione uma op��o</option>
            <?php

            while (list($value, $descricao) = $dados->fetch_array())
            {
                $selected = ($value === $codigo ? " selected" : "");

                ?>
                <option value='<?= tratarHTML($value); ?>'<?= tratarHTML($selected); ?>><?= tratarHTML($value . " - " . $descricao); ?></option>
                <?php
            }

            ?>
        </select>
        <small style="font-size:9px;vertical-align:top;margin-top:0px;padding-left:3px;padding-top:0px;">(<?= number_format($registros, 0, ',', '.'); ?> registros)</small>
        <?php
    }

} // END class TabCargoView
