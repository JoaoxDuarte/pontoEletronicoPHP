<?php

include_once("config.php");
include_once("src/controllers/DadosServidoresController.php");

verifica_permissao("sAPS");

$_SESSION['tipo_solicitacao'] = $_GET['tiposolicitacao'];

$sLotacao = $_SESSION["sLotacao"];
$mensagemUsuario = $_SESSION["mensagem-usuario"];

// dados voltar
$_SESSION['voltar_nivel_2'] = "autorizacoes_usufruto.php";
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';
$_SESSION['voltar_nivel_5'] = '';

// mes anterior
$data = new trata_datasys();
$data_inicial = '01/' . $data->getMesAnterior() . '/' . $data->getAnoAnterior();
$data_final   = '31/12/' . $data->getAno();
    
// seleciona os registros para homologa��o
$oDados = new DadosServidoresController();


/** Valida��o se as datas informadas no cadastro est�o dentro do ciclo */
validacaoDasDatasInformadas();

/** Valida��o se as datas informadas n�o conflitam com as de outro ciclo */
validarDatasConfliteCiclo();

/** Servi�o de busca de usu�rios via ajax*/
buscaServidorCicloViaAjax();

/** Servi�o de busca de saldo do servidor via ajax*/
verificaSaldoDoServidor();

/** Servi�o de valida��o de saldo para libera��o de 
 *  autoriza��o de usufruto total via ajax */
verificaSaldoDoServidorParaUsufruto();

/** Valida��o dos dados informados e posterior grava��o */
validacaoDadosAutorizacaoUsufruto();


$oForm = new formPadrao();
$oForm->setJSDatePicker();
$oForm->setJSSelect2();
$oForm->setSubTitulo("Autoriza��o de usufruto " . $_SESSION['tipo_solicitacao']);

// Topo do formul�rio
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
    <script>
        $(document).ready(function () {

            // Set the "bootstrap" theme as the default theme for all Select2
            // widgets.
            //
            // @see https://github.com/select2/select2/issues/2927
            $.fn.select2.defaults.set("theme", "bootstrap");

            var placeholder = "Selecione um servidor";

            $(".select2-single").select2({
                placeholder: placeholder,
                width: '100%',
                containerCssClass: ':all:'
            });
            $('#date .input-group.date').datepicker({
                format: "dd/mm/yyyy",
                language: "pt-BR",
                orientation: "bottom",
                daysOfWeekDisabled: "0,6",
                startDate: "<?= $data_inicial; ?>",
                endDate: "<?= $data_final; ?>",
                autoclose: true,
                todayHighlight: true,
                toggleActive: true
            }).on('show', function(ev){
                var $this = $(this); //get the offset top of the element
                var eTop  = $this.offset().top; //get the offset top of the element
                $("td.old.disabled.day").css('color', '#e9e9e9');
                $("td.new.disabled.day").css('color', '#e9e9e9');
                $("td.disabled.day").css('color', '#e9e9e9');
                $(".datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-bottom").css('top', (eTop+10));
            });
        
            $('#date2 .input-group.date').datepicker({
                format: "dd/mm/yyyy",
                language: "pt-BR",
                orientation: "bottom",
                daysOfWeekDisabled: "0,6",
                startDate: "<?= $data_inicial; ?>",
                endDate: "<?= $data_final; ?>",
                autoclose: true,
                todayHighlight: true,
                toggleActive: true
            }).on('show', function(ev){
                var $this = $(this); //get the offset top of the element
                var eTop  = $this.offset().top; //get the offset top of the element
                $("td.old.disabled.day").css('color', '#e9e9e9');
                $("td.new.disabled.day").css('color', '#e9e9e9');
                $("td.disabled.day").css('color', '#e9e9e9');
                $(".datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-bottom").css('top', (eTop+10));
            });
            
            $(document).on('click', '#btn-salvar-autorizacao', function () {
                validateForm();
            });
            $(document).on('click', '.save', function () {
                submitForm();
            });
        });

        /**
         *  In�cio Vari�veis Globais
         */
        var parsed;
        var validaDates;
        var validaDatesConflite;
        var validaSaldo;
        var validaIfServidorHasBalance;

        /**
         * Fim Vari�veis Globais
         */


        /**
         * Inicia uma serie de valida��es
         */
        function validateForm() {

            showProcessandoAguarde();
                
            var datestart       = $("[name='data_inicio']").val();
            var dateend         = $("[name='data_final']").val();
            var servidor        = $("[name='siape']").val();
            var tiposolicitacao = $("[name='tipo_solicitacao']").val();


            // Verifica se o servidor foi informado
            if (servidor === "") {
                mostraMensagem("Servidor n�o informado!", 'warning');
                hideProcessandoAguarde();
                return false;
            }

            // Verifica se a data inicial foi informada
            if (datestart === "") {
                mostraMensagem("Data inicial � obrigat�ria!", 'warning');
                hideProcessandoAguarde();
                return false;
            }

            // Verifica se a data final foi informada
            if (dateend === "") {
                mostraMensagem("Data final � obrigat�ria!", 'warning');
                hideProcessandoAguarde();
                return false;
            }

            // Verifica se as datas n�o s�o de anos diferentes
            if (validateYears(datestart, dateend)) {
                mostraMensagem("O Ciclo precisa estar dentro de um mesmo ano!", 'warning');
                hideProcessandoAguarde();
                return false;
            }

            // Verifica se a data inicial � maior que a data final-
            if (ConverteParaData(datestart) > ConverteParaData(dateend)) {
                mostraMensagem("Data inicial n�o pode ser maior que a final!", 'warning');
                hideProcessandoAguarde();
                return false;
            }


            // Vefifica se o servidor possui saldo no banco de dados
            validateSaldoServidor();

            // Vefifica se o range de datas selecionado � v�lido.
            validateRangeDatesIntoCiclo();

            if (tiposolicitacao === 'total') {
                // Vefifica se o range de datas n�o conflitam com outra autoriza��o j� criada
                validateServerHasBalance();
            }

            // Verifica se o range de datas n�o conflitam com outra autoriza��o j� criada e caso a solicita��o seja de autoriza��o total, verifica se existe saldo
            validateDatasNotConflite();


            setTimeout(function () {

                if (tiposolicitacao === 'total') {
                    if (validaIfServidorHasBalance) {
                        mostraMensagem("Servidor n�o possui saldo suficiente para atender ao per�odo informado!", 'warning');
                        hideProcessandoAguarde();
                        return false;
                    }
                }

                if (validaDatesConflite) {
                    mostraMensagem("Datas conflitam com outra autoriza��o!", 'warning');
                    hideProcessandoAguarde();
                    return false;
                }

                if (validaIfServidorHasBalance) {
                    mostraMensagem("Servidor n�o informado!", 'warning');
                    hideProcessandoAguarde();
                    return false;
                }

                if (validaDates) {
                    mostraMensagem("Datas est�o fora do ciclo!", 'warning');
                    hideProcessandoAguarde();
                    return false;
                }

                if (validaSaldo) {
                    mostraMensagem("O servidor n�o possui saldo de banco de horas!", 'warning');
                    hideProcessandoAguarde();
                    return false;
                }
            
                hideProcessandoAguarde();

                if (tiposolicitacao === 'total') {
                    $("#confirmacao").modal();
                }else{
                    submitForm();
                }

            }, 2000);

        }

        /**
         * Valida o range das datas para n�o ser criada um novo ciclo em um periodo onde ja existe outro para o  mesmo �rg�o
         */

        function validateRangeDatesIntoCiclo() {
            var datestart = $("[name='data_inicio']").val();
            var dateend = $("[name='data_final']").val();
            var ciclo = $("[name='ciclo_id']").val();

            $.get(
                "autorizacao_usufruto.php",
                "datastart=" + datestart +
                "&dataend=" + dateend +
                "&ciclo=" + ciclo +
                "&validarDatas=true",
                function (data) {
                    parsed = JSON.parse(data);
                    if (parsed.success) {
                        return validaDates = parsed.bloqueia_cadastro;
                    }
                });
        }

        /**
         * Valida se o servidor possui saldo dispon�vel no banco de dados
         */

        function validateSaldoServidor() {
            var siape = $("#siape").val();
            $.get(
                "autorizacao_usufruto.php",
                "siape=" + siape +
                "&checkSaldo=true",
                function (data) {
                    parsed = JSON.parse(data);
                    if (parsed.success) {
                        return validaSaldo = parsed.bloqueia_cadastro;
                    }
                });
        }


        /**
         * Valida o range das datas para n�o se conflita com outra autoriza��o j� criada para o mesmo servidor
         */

        function validateDatasNotConflite(){
            var datestart = $("[name='data_inicio']").val();
            var dateend = $("[name='data_final']").val();
            var ciclo = $("[name='ciclo_id']").val();
            var siape = $("[name='siape']").val();

            $.get(
                "autorizacao_usufruto.php",
                "datastart=" + datestart +
                "&dataend=" + dateend +
                "&siape=" + siape +
                "&ciclo=" + ciclo +
                "&validarDatasConflite=true",
                function (data) {
                    parsed = JSON.parse(data);
                    if (parsed.success) {
                        return validaDatesConflite = parsed.bloqueia_cadastro;
                    }
                });
        }

        /**
         * Valida se o servidor possui saldo suficiente para receber uma autoriza��o total de usufruto baseado na data informada.
         */

        function validateServerHasBalance(){
            var datestart = $("[name='data_inicio']").val();
            var dateend = $("[name='data_final']").val();
            var siape = $("[name='siape']").val();
            var ciclo = $("[name='ciclo_id']").val();

            $.get(
                "autorizacao_usufruto.php",
                "datastart=" + datestart +
                "&dataend=" + dateend +
                "&siape=" + siape +
                "&ciclo=" + ciclo +
                "&validaServerHasBalancer=true",
                function (data) {
                    parsed = JSON.parse(data);
                    if (parsed.success) {
                        return validaIfServidorHasBalance = parsed.bloqueia_cadastro;
                    }
                });
        }


        /**
         * Formata a data para futuras valida��es
         */
        function ConverteParaData(data) {
            var dataArray = data.split('/');
            var novaData = new Date(dataArray[2], dataArray[1], dataArray[0]);

            return novaData;
        }

        /**
         * Valida se os anos das duas datas s�o iguais
         */
        function validateYears(firstdate, lastdate) {
            var first = firstdate.split("/");
            var last = lastdate.split("/");

            if (first[2] === last[2])
                return false;

            return true;
        }


        /**
         * Salvamento do form
         */

        function submitForm() 
        {
            $oForm = $('#form1');
            
            $oForm.attr( "onsubmit", "javascript:return true" );
            $oForm.attr( "action", "autorizacao_usufruto.php" );
            $oForm.submit();
        }

    </script>

    <!-- <h4 class=""><strong>Ciclos de Banco de Horas</strong></h4>-->


    <div class="portlet-body form">

        <div class="modal fade" id="confirmacao" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Confirma��o</h4>
                    </div>
                    <div class="modal-body">
                        <p>Tem certeza que deseja autorizar o usufruto total das horas do banco de horas para esse servidor?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default save" data-dismiss="modal">Ok</button>
                        <button type="button" class="btn btn-default cancel" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>

            </div>
        </div>

        <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

        <form id="form1" name="form1" method="POST" action="javascript:void(0);" onsubmit="javascript:return true">
            <input type="hidden" value="<?= tratarHTML($_GET['ciclo_id']); ?>" id="ciclo_id" name="ciclo_id">
            <input type="hidden" value="C" id="id_reg" name="id_reg">
            <input type="hidden" value="<?= $tiposolicitacao; ?>" id="tiposolicitacao" name="tiposolicitacao">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6">
                    <font class="ft_13_003">Servidores:</font>
                    
                    <?php $oDados->montaSelectDeServidoresUsufruto();?>
                    
                    <input type="hidden" name="tipo_solicitacao" value="<?= tratarHTML($_SESSION['tipo_solicitacao']); ?>">
                </div>
            </div>
            <div class="row margin-10">
                <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3" id="date">
                    <font class="ft_13_003">Data Inicial:</font>
                    <div class='input-group date' style="width:250px;">
                        <input type='text' name="data_inicio" id="data_inicio" placeholder="dd/mm/aaaa" autocomplete="off" class="form-control"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>
            
            <?php if ($tiposolicitacao !== "total"): ?>
            
                <div class="row margin-10">
                    <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3" id="date2">
                        <font class="ft_13_003">Data Final:</font>
                        <div class='input-group date' style="width:250px;">
                            <input type='text' name="data_final" id="data_final" placeholder="dd/mm/aaaa" autocomplete="off" class="form-control"/>
                            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>
            
            <?php endif; ?>
            
            <div class="row">
                <br>
                <div class="form-group col-md-12 text-center">
                    <div class="col-md-2"></div>
                    <div class="col-md-2 col-xs-4 col-md-offset-2">
                        <a class="btn btn-success btn-block" id="btn-salvar-autorizacao" role="button">
                            <span class="glyphicon glyphicon-ok"></span> Salvar
                        </a>
                    </div>
                    <div class="col-md-2 col-xs-4">
                        <a class="btn btn-danger btn-block" id="btn-voltar"
                           href="javascript:window.location.replace('/sisref/autorizacoes_usufruto.php')" role="button">
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
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();


/* ******************************************** *
 *                                              *
 *            FUN��ES COMPLEMENTARES            *
 *                                              *
 * ******************************************** */

/*
 * @info Valida��o se as datas informadas no 
 *       cadastro est�o dentro do ciclo 
 * 
 * @param void
 * @result void
 */
function validacaoDasDatasInformadas()
{
    if($_GET['validarDatas']) 
    {
        echo validaRangeDatesIntoCiclo($_GET['datastart'], $_GET['dataend'] , $_GET['ciclo']);
        die;
    }
}

/*
 * @info Valida��o se as datas informadas n�o 
 *       conflitam com as de outro ciclo
 * 
 * @param void
 * @result void
 */
function validarDatasConfliteCiclo()
{
    if($_GET['validarDatasConflite']) 
    {
        echo validaRangeDatesNotConflite($_GET['datastart'], $_GET['dataend'] , $_GET['ciclo'] , $_GET['siape']);
        die;
    }
}

/*
 * @info Servi�o de busca de usu�rios via ajax
 * 
 * @param void
 * @result void
 */
function buscaServidorCicloViaAjax()
{
    if(!empty($_GET['getServidorByMatricula']))
    {
        // seleciona os registros para homologa��o
        $oDados = new DadosServidoresController();
        $dados  = $oDados->selecionaServidorPorMatricula($_GET['getServidorByMatricula']); 

        if(empty($dados)){
            echo "";
            die;
        }
    
        echo tratarHTML($dados->servidor);
        die;
    }
}

/*
 * @info Servi�o de busca de saldo do servidor via ajax
 * 
 * @param void
 * @result void
 */
function verificaSaldoDoServidor()
{
    if(!empty($_GET['checkSaldo'])){
        echo verifySaldo($_GET['siape']);
        die;
    }
}

/**  */
/*
 * @info Servi�o de valida��o de saldo para libera��o 
 *       de autoriza��o de usufruto total via ajax
 * 
 * @param void
 * @result void
 */
function verificaSaldoDoServidorParaUsufruto()
{
    if(!empty($_GET['validaServerHasBalancer'])){
        echo verifySaldoTypeAutorizationTotal($_GET);
        die;
    }
}
/** Valida��o dos dados informados e posterior grava��o */
function validacaoDadosAutorizacaoUsufruto()
{
    if(!empty($_POST))
    {
        $siape      = getPost('siape');       // siape
        $ciclo_id   = getPost('ciclo_id');    // Id do ciclo
        $start_date = getPost('data_inicio'); // Data Inicial sem formata��es
        $end_date   = getPost('data_final');  // Data Final sem formata��es
        
        $tiposolicitacao = getPost("tiposolicitacao"); // usufruto total ou parcial

        if ($tiposolicitacao === 'total')
        {
            $end_date = $start_date;
        }
        // Formata��o das datas
        $dateiniformated = conv_data($start_date);
        $datefimformated = conv_data($end_date);

        
        $bool = true; // Vari�vel de controle

        // Verifica se a data final foi informada
        if(empty($end_date) && $bool)
        {
            setMensagemUsuario('Data Final n�o informada!','danger');
            $bool = false;
        }

        // Verifica se as datas n�o s�o de anos diferentes
        if (validateYears($start_date , $end_date) && $bool) 
        {
            setMensagemUsuario('O Ciclo precisa estar dentro de um mesmo ano!','danger');
            $bool = false;
        }

        // Verifica se a data inicial � maior que a data final
        if ((strtotime($dateiniformated) > strtotime($datefimformated)) && $bool) 
        {
            setMensagemUsuario('Data inicial n�o pode ser maior que a final!','danger');
            $bool = false;
        }

        // Vefifica se o range de datas selecionado � v�lido
        $result = json_decode(validaRangeDatesIntoCiclo($start_date, $end_date, $ciclo_id));
        
        if($result->bloqueia_cadastro && $bool)
        {
            setMensagemUsuario('Data(s) est�(�o) fora do ciclo!','danger');
            $bool = false;
        }
        
        if($bool) 
        {
            if (create_or_update_autorizacao_usufruto($_POST) == true)
            {
                setMensagemUsuario('Autoriza��o de Usufruto realizada com sucesso!','success');
            }
            else
            {
                setMensagemUsuario('Autoriza��o de Usufruto N�O realizada!','danger');
            }
            replaceLink("autorizacoes_usufruto.php");
        }
    }
}
