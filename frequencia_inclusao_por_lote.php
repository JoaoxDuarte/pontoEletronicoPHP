<?php

// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("sAPS");

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    $dados   = explode(":|:", base64_decode($dadosorigem));
    
    if (count($dados) == 4)
    {
        // m�dulo acompanhar
        $cmd     = $dados[0];
        $orig    = $dados[1];
        $lotacao = $dados[2];
        $dia     = $dados[3];
        $modulo  = "Acompanhamento Di�rio";
    }
    else
    {
        // m�dulo homologar
        $cmd     = $dados[0];
        $lotacao = $dados[1];
        $dia     = $dados[2];
        $modulo  = "Homologa��o";
        $_SESSION['voltar_nivel_1'] = "frequencia_homologar.php?dados=".$dadosorigem;

        // Compet�ncia atual (m�s e ano)
        $data = new trata_datasys();
        $ano  = $data->getAnoHomologacao();
        $mes  = $data->getMesHomologacao();
        $dia  = "01/$mes/$ano";
        
    }
}


// dados voltar
//$_SESSION['voltar_nivel_1'] = $_SERVER['REQUEST_URI'];
$_SESSION['voltar_nivel_2'] = $_SERVER['REQUEST_URI'];
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';
$_SESSION['voltar_nivel_5'] = '';

// mes e ano
$mes = (validaData($dia) ? dataMes($dia) : date('m'));
$ano = (validaData($dia) ? dataAno($dia) : date('Y'));

// dados em sessao
$sMatricula = $_SESSION["sMatricula"]; // matricula do usu�rio logado


## classe para montagem do formulario padrao
#
$title = _SISTEMA_SIGLA_ . ' | ' . $modulo . ' de Frequ�ncia - Inclus�o por Lote';

$oForm = new formPadrao();
$oForm->setSubTitulo( $modulo . " de Frequ�ncia - Inclus�o por Lote ");
$oForm->setJSSelect2();
$oForm->setJSDatePicker();
$oForm->setCSS( "css/table_sorter.css" );
$oForm->setJS( "js/phpjs.js" );
$oForm->setJS( "js/jquery.tablesorter.js" );

// Topo do formul�rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML($width='1300px;');


listaInclusaoPorLote($lotacao, $mes, $ano);


// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

exit();

/* *****************************************************
 *                                                     *
 *               FUN��ES COMPLEMENTARES                *
 *                                                     *
 ***************************************************** */

/* @info  Formul�rio servidores do setor
 *
 * @param  string  $lotacao  Unidade para homologar
 * @param  string  $mes  M�s de homologa��o
 * @param  string  $ano  Ano de homologa��o
 * @return  void
 *
 * @author  Edinalvo Rosa
 */
function listaInclusaoPorLote($lotacao, $mes, $ano)
{
    ##
    #  SELECIONA OS REGISTROS PARA HOMOLOGA��O
    #
    $registrosInclusao = seleciona_servidores($link, $lotacao, 'N', $ano . $mes, $homologacao=true);
    $num_rows           = $registrosInclusao->num_rows();

    listaInclusaoPorLoteJavascript($mes, $ano);

    ?>
    <div class="container margin-20" id="form-comparecimento">
        <div class="row margin-10">

            <?php listaInclusaoPorLoteBotaoVoltar(); ?>

            <div class="col-md-12" id="dados-funcionario">
                <div class="col-md-2">
                    <h5><strong>COMPET�NCIA</strong></h5>
                    <p><b><?= tratarHTML($mes) . '/' . tratarHTML($ano); ?></b></p>
                </div>
                <!-- Row Referente aos dados Setor do funcionario  -->
                <div class="col-md-10">
                    <div class="row">
                        <table class="table text-center">
                            <thead>
                                <tr>
                                    <th class="text-center" style='vertical-align:middle;'>�RG�O</th>
                                    <th class="text-center" style='vertical-align:middle;'>LOTA��O</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center text-nowrap"><h4><?= tratarHTML(getOrgaoMaisSigla( $lotacao )); ?></h4></td>
                                    <td class="text-center"><h4><?= tratarHTML(getUorgMaisDescricao( $lotacao )); ?></h4></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        </div>

        <form id='form1' name='form1' method='POST' onsubmit="javascript:return false;">

            <div class="form-group col-md-12">
                <div class="col-md-3 col-md-offset-1" style="padding-top:7px;text-align:right;">
                    <label class="control-label">C�digo da Ocorr�ncia</label>
                </div>
                <div class="col-md-8">
                    <?= montaSelectOcorrencias(
                            $valor       = '',
                            $tamdescr    = '',
                            $imprimir    = false,
                            $por_periodo = false,
                            $historico   = false,
                            $onchange    = '',
                            $grupo       = '',
                            $siape       = '',
                            $soGrupoOcorr = 'COVID19'
                        );
                    ?>
                </div>
            </div>

            <div class="form-group col-md-12" id="dt-container">
                <div class="col-md-3 col-md-offset-1" style="padding-top:7px;text-align:right;">
                    <label class="control-label">Data Inicial</label>
                </div>
                <div class="col-md-8">
                    <div class='col-lg-3 col-md-3 col-xs-3 col-sm-3 input-group date'>
                        <input type='text' id="data_inicio" name="data_inicio" placeholder="dd/mm/aaaa" class="form-control" autocomplete="off"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                    </div>
                </div>
            </div>

            <div class="form-group col-md-12" id="dt-container">
                <div class="col-md-3 col-md-offset-1" style="padding-top:7px;text-align:right;">
                    <label class="control-label">Data Final</label>
                </div>
                <div class="col-md-8">
                    <div class='col-lg-3 col-md-3 col-xs-3 col-sm-3 input-group date'>
                        <input type='text' id="data_fim" name="data_fim" placeholder="dd/mm/aaaa" class="form-control" autocomplete="off"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                    </div>
                </div>
            </div>
            
            <div class="row margin-10">
                <table class="table table-striped table-bordered text-center table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">SIAPE</th>
                            <th class="text-center">Nome</th>
                            <th class="text-center"><input type=checkbox name="selall" onClick="javascript:CheckAll();"><span id="checar">&nbsp;Marcar todos</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        if ($num_rows == 0)
                        {
                            //mensagem("Sem registro para homologa��o");
                            ?>
                            <tr>
                                <td class="text-center" colspan='5'>Sem registro para homologa��o</td>
                            </tr>
                            <?php
                        }
                        else
                        {
                            while ($pm = $registrosInclusao->fetch_object())
                            {
                                ?>
                                <tr>
                                    <td class='text-center'><?= tratarHTML(removeOrgaoMatricula($pm->mat_siape)); ?></td>
                                    <td class='text-left'><?= tratarHTML($pm->nome_serv); ?></td>
                                    <td class='col-md-2 text-center'><input type=checkbox name="C[]" value="<?= tratarHTML(base64_encode($pm->mat_siape)); ?>"></td>
                                </tr>
                                <?php
                            }
                        }
                    
                        ?>
                    </tbody>
                </table>
                <div class="form-group" style="position:relative;top:-15px;">
                        <fieldset style="border:1px solid white;text-align:left;margin:0px;">
                            <legend style="font-size:12px;padding:0px;margin:0px;"><b>&nbsp;Observa��o&nbsp;</b></legend>
                            <p style="padding:0px;margin:0px;">N�o ser�o alteradas ocorr�ncias que foram registradas pela Chefia imediata ou RH.</p>
                        </fieldset>
                    </div>

                <?php listaInclusaoPorLoteBotaoGravar(); ?>
            
                <?php listaInclusaoPorLoteBotaoVoltar(); ?>

            </div>

        </form>

    </div>
    <?php
}


/* @info  M�todos Javascript
 *
 * @param  vorid
 * @return  void
 *
 * @author  Edinalvo Rosa
 */
function listaInclusaoPorLoteJavascript($mes, $ano)
{
    $ultimo_dia = numero_dias_do_mes($mes, $ano);
    
    ?>
    <script>
        $(document).ready(function ()
        {
            // Set the "bootstrap" theme as the default theme for all Select2
            // widgets.
            //
            // @see https://github.com/select2/select2/issues/2927
            $.fn.select2.defaults.set("theme", "bootstrap");

            var placeholder = "Selecione uma unidade";

            $(".select2-single").select2({
                placeholder: placeholder,
                width: '100%',
                containerCssClass: ':all:'
            });

            $('#dt-container .input-group.date').datepicker({
                format: "dd/mm/yyyy",
                language: "pt-BR",
                startDate: "<?= '01/'.$mes.'/'.$ano; ?>",
                endDate: "<?= $ultimo_dia.'/'.$mes.'/'.$ano; ?>",
                autoclose: true,
                todayHighlight: true,
                toggleActive: true,
                orientation: "bottom auto",
                maxViewMode: 0,
                datesDisabled: ['10/06/2018', '10/21/2018']
            }).on('show', function(ev){
                var $this = $(this); //get the offset top of the element
                var eTop  = $this.offset().top; //get the offset top of the element
                $("td.old.disabled.day").css('color', '#e9e9e9');
                $("td.new.disabled.day").css('color', '#e9e9e9');
                $("td.disabled.day").css('color', '#e9e9e9');
                $(".datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-bottom").css('top', (eTop+10));
            });
        
            $('[data-load-frequencia-inclusao-por-lote-gravar]').on('click',function(e) {
                var $oForm = $("#form1");
                var $this  = $(this);
                var remote = $this.data('load-frequencia-inclusao-por-lote-gravar');
                
                console.log(remote);
                        
                showProcessandoAguarde();
        
                $oForm.attr("onsubmit", "javascript:return true;");
                $oForm.attr("action", remote);
                $oForm.submit();
            });

            $('[data-load-frequencia-inclusao-por-lote-voltar]').on('click',function(e) {
                var $this = $(this);
                var remote = $this.data('load-frequencia-inclusao-por-lote-voltar');

                e.preventDefault();

                window.location.replace( remote );
            });
        });

        function CheckAll()
        {
            var selall = $("input[name='selall']").prop( 'checked' );
            var cont   = 0;

            $("input[name='C[]'").each(function( i )
            {
                $(this).prop( 'checked', selall );
            });

            if (selall === false)
            {
                $('#checar').html( "&nbsp;Marcar todos" );
            }
            else
            {
                $('#checar').html( "&nbsp;Desmarcar todos" );
            }
        }
    </script>
    <?php
}


/* @info  Exibe bot�o gravar
 *
 * @param  void
 * @return  void
 *
 * @author  Edinalvo Rosa
 */
function listaInclusaoPorLoteBotaoGravar()
{
    ?>
    <div class="col-md-12">
        <div class="col-md-12 text-center">
            <a class="btn btn-success" href="javascript:void(0);" data-load-frequencia-inclusao-por-lote-gravar="frequencia_inclusao_por_lote_gravar.php" role="button">
                <span class="glyphicon glyphicon-ok"></span> Gravar Ocorr�ncia
            </a>
        </div>
    </div>
    <?php
}


/* @info  Exibe bot�o voltar
 *
 * @param  void
 * @return  void
 *
 * @author  Edinalvo Rosa
 */
function listaInclusaoPorLoteBotaoVoltar()
{
    ?>
    <div class="col-md-12">
        <div class="col-md-12 text-right">
            <a class="btn btn-danger" href="javascript:void(0);" data-load-frequencia-inclusao-por-lote-voltar="<?= $_SESSION['voltar_nivel_1']; ?>">
                <span class="glyphicon glyphicon-arrow-left"></span> Voltar
            </a>
        </div>
    </div>
    <?php
}

