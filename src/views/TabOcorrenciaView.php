<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( 'class_formpadrao.php' );


/**
 * @class TabOcorrenciaView
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : tabfunc
 *       Suas descrições e características
 *
 * @Camada    - View
 * @Diretório - src/views
 * @Arquivo   - TabOcorrenciaView.php
 *
 * @author Edinalvo Rosa
 */
class TabOcorrenciaView extends formPadrao
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
        parent::setSubTitulo( "Tabela de Ocorrência - Manutenção" );

        // Topo do formulário
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML('1250px');

        $this->JavaScript($retorno);
        
        ?>
        <form name='form1' id='form1' method='POST' action='#' onsubmit='javascript:return false;'> 
            <input type="hidden" name="id" id="id">
                    
            <div class="row">
                <div class="col-md-12 text-left margin-25 margin-bottom-10">
                    <button type="button" class="btn btn-default btn_adicionar" style="padding-left:30px;padding-right:30px;">Adicionar</button>
                </div>
                            
                <table id="tabela_dados" class="display nowrap table-hover table table-striped table-bordered" role="grid" style="width:1050px;">
                    <thead>
                        <tr>
                            <th>Seq.</th>
                            <th>Ações</th>
                            <th class="sorting">Código<br>da Ocorrência</th>
                            <th class="sorting">Nome da Ocorrência</th>
                            <th class="sorting">Responsável</th>
                            <th class="sorting">Sigla</th>
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
                $(".btn_adicionar").on('click', function () {
                    var oForm = $('#form1');
                    
                    showProcessandoAguarde();

                    oForm.attr('onsubmit', 'javascript:return true');
                    oForm.attr('action', 'tabocorrencia_incluir.php');
                    oForm.submit();
                });

                $(".btn_Alterar").click( function(event) {
                    var oForm   = jQuery('#form1');
                    var id      = $(this).attr('data-id');
                    
                    showProcessandoAguarde();

                    $('#id').val( id );
                    
                    oForm.attr('onsubmit', 'javascript:return true');
                    oForm.attr('action', 'tabocorrencia_alterar.php');
                    oForm.submit();
                });
                
                var tabela = $('#tabela_dados').DataTable({
                    "processing": true,
                    "ajax": {
                        "url": "tabocorrencia_lista.php",
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
            
            function Delete(codigo)
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
                                        ExecutaExclusao(true,codigo);
                                    }
                                }
                        });
                
                        function ExecutaExclusao(apagar,dados)
                        {
                            if (apagar){

                                showProcessandoAguarde();

                                $.ajax({
                                    url: "tabocorrencia.php",
                                    type: "POST",
                                    data: "autorizacao=true&id=" + dados

                                }).done(function(resultado) {
                                    console.log(resultado);
                                    hideProcessandoAguarde();
                                    mostraMensagem( 'Excluída a ocorrência "' + dados + '" com sucesso.', 'success', 'tabocorrencia.php' );

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
     * @info Exibe formulário para inclusão de Isenção de Ponto
     *
     * @param string $retorno Informação do resultado da gravação
     * @param array  $opcoes  Tipo a que pertence a Isenção
     * @return HTML
     */
    public function showFormularioIncluir( $retorno=null )
    {
        $sLotacao        = $_SESSION["sLotacao"];
        $mensagemUsuario = $_SESSION["mensagem-usuario"];

        parent::setJSSelect2();
        parent::setSubTitulo( "Tabela de Ocorrência - Inclusão" );

        // Topo do formulário
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML();

        $this->formularioJavaScript( 'Inclusão', $retorno, 'tabocorrencia_incluir.php' );
        $this->formularioHtml();
    }


    /**
     * @info Exibe formulário para alteração de Isenção de Ponto
     *
     * @param string $retorno Informação do resultado da gravação
     * @param array  $opcoes  Tipo a que pertence a Isenção
     * @return HTML
     */
    public function showFormularioAlterar( $dados=null, $retorno=null )
    {
        $sLotacao        = $_SESSION["sLotacao"];
        $mensagemUsuario = $_SESSION["mensagem-usuario"];

        parent::setJSSelect2();
        parent::setSubTitulo( "Tabela de Ocorrência - Alteração" );

        // Topo do formulário
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML();

        $this->formularioJavaScript( 'Alteração', $retorno, 'tabocorrencia_alterar.php' );
        $this->formularioHtml( "Alteração", $dados->fetch_object() );
    }


    /**
     * @info Exibe formulário para consulta
     *
     * @param result $dados
     * @return HTML
     */
    public function showFormularioVisualizar( $dados=null, $objDadosCampoENUM=null )
    {
        $sLotacao        = $_SESSION["sLotacao"];
        $mensagemUsuario = $_SESSION["mensagem-usuario"];

        parent::setJSSelect2();
        parent::setSubTitulo( "Tabela de Ocorrência - Visualizar" );

        // Topo do formulário
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML();

        $this->formularioHtmlVisualizar( "Visualizar", $dados->fetch_object() );
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
                <input type="hidden" value="<?= $dados->NUM_FUNCAO; ?>" name="id">
                
                <div class="row">
                    <div class="col-lg-2 col-md-2 col-xs-2 col-sm-2 margin-10">
                        <font class="ft_13_003">Número da Ocorrência:</font>
                        &nbsp;<input type="text"
                                     id="NUM_FUNCAO" name="NUM_FUNCAO"
                                     class="form-control"
                                     size="7" maxlength="7"
                                     value="<?= $dados->NUM_FUNCAO; ?>"
                                     <?= ($acao=='Alteração' ? ' readonly' : ''); ?>>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-2 col-md-2 col-xs-2 col-sm-2 margin-10">
                        <font class="ft_13_003">Código da Ocorrência:</font>
                        &nbsp;<input type="text"
                                     id="COD_FUNCAO" name="COD_FUNCAO"
                                     class="form-control uppercase"
                                     size="8" maxlength="8"
                                     value="<?= $dados->COD_FUNCAO; ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-4 col-xs-4 col-sm-4 margin-10">
                        <font class="ft_13_003">Nome da Ocorrência:</font>
                        &nbsp;<input type="text"
                                     id="DESC_FUNC" name="DESC_FUNC"
                                     class="form-control uppercase"
                                     size="200" maxlength="200"
                                     value="<?= $dados->DESC_FUNC; ?>" style="width:400px;">
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-2 col-md-2 col-xs-2 col-sm-2 margin-10">
                        <font class="ft_13_003">Código UORG:</font>
                        &nbsp;<input type="text"
                                     id="COD_LOT" name="COD_LOT"
                                     class="form-control"
                                     size="14" maxlength="14"
                                     value="<?= $dados->COD_LOT; ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-2 col-md-2 col-xs-2 col-sm-2 margin-10">
                        <font class="ft_13_003">Código UPAG:</font>
                        &nbsp;<input type="text"
                                     id="UPAG" name="UPAG"
                                     class="form-control"
                                     size="14" maxlength="14"
                                     value="<?= $dados->UPAG; ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-2 col-md-2 col-xs-2 col-sm-2 margin-10">
                        <font class="ft_13_003">Indica Substituto?</font>
                        <select id="INDSUBS" name="INDSUBS" class="form-control select2-single">
                            <option value='N' <?= ($dados->INDSUBS != 'S' ? ' selected' : ''); ?>>N&atilde;o</option>
                            <option value='S' <?= ($dados->INDSUBS == 'S' ? ' selected' : ''); ?>>Sim</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-2 col-md-2 col-xs-2 col-sm-2 margin-10">
                        <font class="ft_13_003">Subsídios?</font>
                        <select id="RESP_LOT" name="RESP_LOT" class="form-control select2-single">
                            <option value='N' <?= ($dados->RESP_LOT != 'S' ? ' selected' : ''); ?>>N&atilde;o</option>
                            <option value='S' <?= ($dados->RESP_LOT == 'S' ? ' selected' : ''); ?>>Sim</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-2 col-md-2 col-xs-2 col-sm-2 margin-10">
                        <font class="ft_13_003">Ativo?</font>
                        <select id="ATIVO" name="ATIVO" class="form-control select2-single">
                            <option value='N' <?= ($dados->ATIVO != 'S' ? ' selected' : ''); ?>>N&atilde;o</option>
                            <option value='S' <?= ($dados->ATIVO == 'S' ? ' selected' : ''); ?>>Sim</option>
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
                               href="javascript:window.location.replace('tabocorrencia.php')" role="button">
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
                    mostraMensagem('Ocorrência já cadastrada!', 'danger', "tabocorrencia.php");
                <?php endif; ?>

                // TESTE DO FORMULÁRIO ALTERAÇÃO
                <?php if ($retorno == "nao_existe"): ?>
                    mostraMensagem('Ocorrência não encontrada!', 'danger', "tabocorrencia.php");
                <?php endif; ?>

                <?php if ($retorno == "gravou"): ?>
                    mostraMensagem('<?= $acao; ?> realizada com sucesso!', 'success', "tabocorrencia.php");
                <?php endif; ?>

                <?php if ($retorno == "nao_gravou"): ?>
                    mostraMensagem('<?= $acao; ?> NÃO foi realizada!', 'danger', "tabocorrencia.php");
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

                var codigo = $("[name='NUM_FUNCAO']").val();
                var texto  = $("[name='DESC_FUNC']").val();

                // Verifica se código (sigla) foi informado
                if (codigo == "") {
                    mostraMensagem('Número da Ocorrência é obrigatório!', 'warning');
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
     * @info Formulário para manutenção de Isenção de Ponto
     *
     * @param string $dados  Dados de isenção de ponto
     * @return HTML
     */
    public function formularioHtmlVisualizar( $acao=null, $dados=null )
    {
        if (is_null($dados))
        {
            $dados = new stdClass();
            $dados->desc_ocorr = "";
            $dados->resp       = "";
            $dados->ativo      = "S";
            $dados->aplic      = "";
            $dados->implic     = "";
            $dados->prazo      = "";
            $dados->flegal     = "";
        }

        $responsavel       = array();
        $responsavel['AB'] = "RH / Chefia";
        $responsavel['RH'] = "Recurso Humanos";
        $responsavel['CH'] = "Chefia";
        $responsavel['SI'] = "SISREF";
        
        ?>
        <div class="portlet-body form">

            <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

            <style>
                .border { border: 1px solid #e5e5e5; text-align: center; }
            </style>
            <table class='table table-striped table-condensed text-center'>
                <tr>
                    <td>
                        <label>CÓDIGO</label>
                        <input name="siapecad" type="text" value="<?= tratarHTML($dados->siapecad); ?>" size="10"  class="form-control" readonly>
                    </td>
                    <td>
                        <label>DESCRIÇÃO DA OCORRÊNCIA</label>
                        <input name="sDescricao" type="text" value="<?= tratarHTML(trata_aspas($dados->desc_ocorr)); ?>" size="70"  class="form-control" readonly>
                    </td>
                    <td>
                        <label>RESPONSÁVEL</label>
                        <input name="resp" type="text" value="<?= tratarHTML($responsavel[$dados->resp]); ?>" size="15"  class="form-control" readonly>
                    </td>
                    <td>
                        <label>ATIVO</label>
                        <input name="sAtivo" type="text" value="<?= tratarHTML(($dados->ativo == 'S' ? 'Sim' : 'Não')); ?>" size="3"  class="form-control" readonly>
                    </td>
                </tr>
            </table>
            <table class='table table-striped table-condensed text-center'>
                <tr>
                    <td>
                        <label>SIGLA/ORIGEM</label>
                        <input name="cod_ocorr" type="text" value="<?= tratarHTML($dados->cod_ocorr); ?>" size="10"  class="form-control" readonly>
                    </td>
                    <td>
                        <label>SEM REMUNERAÇÃO</label>
                        <input name="semrem" type="text" value="<?=tratarHTML(($dados->semrem == 'S' ? 'Sim' : 'Não')); ?>" size="3"  class="form-control" readonly>
                    </td>
                    <td>
                        <label>ID SIAPECAD</label>
                        <input name="idsiapecad" type="text" value="<?= tratarHTML(($dados->idsiapecad == 'S' ? 'Sim' : 'Não')); ?>" size="3"  class="form-control" readonly>
                    </td>
                    <td>
                        <label>GRUPO</label>
                        <input name="grupo" type="text" value="<?= tratarHTML($dados->grupo); ?>" size="10"  class="form-control" readonly>
                    </td>
                </tr>
            </table>
            <table class='table table-striped table-condensed text-center'>
                <tr>
                    <td>
                        <label>APLICAÇÃO</label>
                        <textarea name=aplic cols=40 rows=10 class="form-control" disabled><?= tratarHTML(trata_aspas($dados->aplic)); ?></textarea>
                    </td>
                    <td>
                        <label>IMPLICAÇÃO</label>
                        <textarea name=implic cols=40 rows=10 class="form-control" disabled><?= tratarHTML(trata_aspas($dados->implic)); ?></textarea>
                    </td>
                    <td>
                        <label>PRAZOS</label>
                        <textarea name=prazo cols=30 rows=10 class="form-control" disabled><?= tratarHTML($dados->prazo); ?> </textarea>
                    </td>
                    <td>
                        <label>FUNDAMENTO LEGAL</label>
                        <textarea name=flegal cols=30 rows=10 class="form-control" disabled><?= tratarHTML(trata_aspas($dados->flegal)); ?></textarea>
                    </td>
                </tr>
            </table>
            
            <?php
            
            print $dados->grupo . '<br>';
            print $dados->tipo . '<br>';
            print $dados->situacao . '<br>';
            print $dados->justificativa . '<br>';
            print $dados->postergar_pagar_recesso . '<br>';
            print $dados->tratamento_debito . '<br>';
            print $dados->padrao . '<br>';
            print $dados->grupo_cadastral . '<br>';
            print $dados->agrupa_debito . '<br>';
            print $dados->grupo_ocorrencia . '<br>';
            print $dados->informar_horarios . '<br>';
            print $dados->vigencia_inicio . '<br>';
            print $dados->vigencia_fim . '<br>';
            
            ?>
            <div class="row">
                <br>
                <div class="form-group col-md-12 text-center">
                    <div class="col-md-5"></div>
                    <div class="col-md-2 col-xs-4">
                        <a class="btn btn-danger btn-block" id="btn-voltar"
                           href="javascript:window.location.replace('tabocorrencia.php')" role="button">
                            <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                        </a>
                    </div>
                    <div class="col-md-5"></div>
                </div>
            </div>
            
        </div>
        <?php
        
        // Base do formulário
        parent::exibeCorpoBaseHTML();
        parent::exibeBaseHTML();
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
                $mensagem = "Excluída com sucesso a Ocorrência " . $descricao . ".";
                break;

            case 'warning':
                $tipo     = "warning";
                $mensagem = "Exclusão NÃO realizada, há Servidor(a) registrado com a Ocorrência (" . $descricao . ").";
                break;

            case 'error':
                $tipo     = "danger";
                $mensagem = "NÃO houve sucesso na exclusão da Ocorrência " . $descricao . ".";
                break;
        }

        retornaInformacao( $mensagem, $tipo);
    }

    
    /**
     * @info Monta listbox com códigos de ocorrência
     *
     * @param  string  $valor       Valor para marcarcomo selecionado     
     * @param  integer $tamdescr    Largura do list box                   
     * @param  boolean $imprimir    Retornará como texto ou exibirá       
     * @param  boolean $por_periodo Indica se a ocorrencia eh por periodo 
     * @param  boolean $historico   Indica se a será exibida no histórico 
     * @param  string  $onchange    Função javascript para troca de opção ou seleção
     * @param  string  $grupo       Grupo de origem (acaompanhar/homologar/...)
     * @return string HTML
     */
    public function montaSelectOcorrencias($valor = '', $tamdescr = '', $imprimir = false, $onchange = '', $dados = null)
    {
        if ( !is_null($dados) )
        {
            $html  = '';
            $html .= '<SELECT id="ocor" name="ocor" size="1" class="form-control select2-single" title="Selecione a ocorrência!" ' . ($onchange == '' ? '' : 'onChange="' . $onchange . '"') . '>';

            while ($campo = $dados->fetch_array())
            {
                $selected = ($campo[0] == $valor ? ' selected' : "");

                $html .= '<option value="' . $campo[0] . '"';

                if ($campo[0] == '-----')
                {
                    $html .= $selected . '>Selecione uma opção</option>';
                }
                else
                {
                    $html .= $selected . '>' . $campo[0] . ' - ' . ($tamdescr == '' ? $campo[1] : substr($campo[1], 0, $tamdescr)) . '</option>';
                }
            }

            $html .= '</SELECT>';
            
            if ($imprimir == true)
            {
                echo $html;
            }
            else
            {
                return $html;
            }
        }
    }

} // END class TabOcorrenciaView
