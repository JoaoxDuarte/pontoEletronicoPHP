<?php
include_once("config.php");
include_once("comparecimento_consulta_medica_funcoes.php");

verifica_permissao("sRH ou chefia");

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    // Valores passados - encriptados
    $dados = explode(":|:", descriptografa($dadosorigem));
    $siape = getNovaMatriculaBySiape($dados[0]);
    $dia   = $dados[1];
    $idreg = $dados[2];
    $grupo = $dados[3]; // acompanhar ou homologar
}

$mes = dataMes($dia);
$ano = dataAno($dia);
$dias_mes = numero_dias_do_mes( $mes, $ano);

$sLotacao = $_SESSION["sLotacao"];
$mensagemUsuario = $_SESSION["mensagem-usuario"];


$oForm = new formPadrao();
$oForm->setSubTitulo("Cadastrar Comparecimento a Consulta Médica - Registros");
$oForm->setJS('js/fc_data.js');
$oForm->setJSDialogProcessando();
$oForm->setJSDatePicker();
$oForm->setJS('js/jquery.mask.min.js');

// Topo do formulário

$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

// instancia o banco de dados
$oDBase = new DataBase('PDO');

$oComprovanteServidor = selecionaServidoresConsultaMedicaLista($siape);
$total_registros = $oComprovanteServidor->num_rows();

$oServidor = selecionaServidor($siape);
$servidor = $oServidor->fetch_object()->nome_serv;


?>
<script>
    $(document).ready(function () {

        $('#date .input-group.date').datepicker({
            format: "dd/mm/yyyy",
            startDate: "<?= "01/".$mes."/".$ano; ?>",
            endDate: "<?= $dias_mes."/".$mes."/".$ano; ?>",
            maxViewMode: 0,
            language: "pt-BR",
            autoclose: true,
            todayHighlight: true,
            toggleActive: true,
            orientation: "bottom",
            datesDisabled: ['10/06/2018', '10/21/2018']
        }).on('show', function(ev){
            var $this = $(this); //get the offset top of the element
            var eTop  = $this.offset().top; //get the offset top of the element
            $("td.old.disabled.day").css('color', '#e9e9e9');
            $("td.new.disabled.day").css('color', '#e9e9e9');
            $("td.disabled.day").css('color', '#e9e9e9');
            $(".datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-bottom").css('top', (eTop+10));
        });

        $('#hora_ini').mask('00:00');
        $('#hora_fim').mask('00:00');
        $('#tempo_consulta').mask('00:00');
        $('#deslocamento').mask('00:00');
        //$('#date').mask('##/##/####');


        $("#btn-salvar-registro").on('click',function (e)
        {
            e.preventDefault();

            showProcessandoAguarde();

            // dados
            var dia            = $("[name='dia']").val();
            var data_hoje      = $("[name='data_hoje']").val();
            var hora_ini       = $("[name='hora_ini']").val();
            var hora_fim       = $("[name='hora_fim']").val();
            var tempo_consulta = $("[name='tempo_consulta']").val();
            //var deslocamento   = $("[name='deslocamento']").val();
            var servidor       = $("[name='siape']").val();
            var usuario        = $("[name='usuario']").val();

            //var dados = $('#form1').serialize();
            var dados = "dia="             + dia +
                        "&hora_ini="       + hora_ini +
                        "&hora_fim="       + hora_fim +
                        "&tempo_consulta=" + tempo_consulta +
                        "&servidor="       + servidor +
                        "&data_hoje="      + data_hoje +
                        "&usuario="        + usuario;

            $.ajax({
                url: "comparecimento_consulta_medica_ajax.php",
                type: "POST",
                data: dados,
                dataType: "json"

            }).done(function(resultado) {
                console.log(resultado.mensagem + ' | ' + resultado.tipo);
                mostraMensagem(resultado.mensagem, resultado.tipo, null, null);

                if (resultado.tipo == 'success')
                {
                    $("[name='dia']").val('');
                    $("[name='hora_ini']").val('');
                    $("[name='hora_fim']").val('');
                    $("[name='tempo_consulta']").val('');
                    //$("[name='deslocamento']").val('');
                }

                hideProcessandoAguarde();

            }).fail(function(jqXHR, textStatus ) {
                console.log("Request failed: " + textStatus);
                hideProcessandoAguarde();

            }).always(function() {
                console.log("completou");
                hideProcessandoAguarde();

            });
        });
    });
</script>

<div class="portlet-body form">

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <form id="form1" name="form1" method="POST">
        <input type="hidden" name="hdnips">
        <input type="hidden" name="salvar" value="">
        <input type="hidden" name="data_hoje" value="<?=  date('d/m/Y'); ?>">
        <input type="hidden" name="usuario" value="<?= removeOrgaoMatricula($_SESSION['sMatricula']); ?>" >
        <input type="hidden" name="dados" value="<?= tratarHTML($dadosorigem); ?>" >
        <div class="row">
            <div class="col-md-2">
                <p><b>ÓRGÃO: </b><?= tratarHTML(getOrgaoMaisSigla( $_SESSION['sLotacao'] )); ?></p>
            </div>
            <div class="col-md-7">
                <p><b>UORG: </b><?= tratarHTML(getUorgMaisDescricao( $_SESSION['sLotacao'] )); ?></p>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6">
                <font class="ft_13_003">Servidor:</font>
                <input type="text" class="form-control" value="<?= removeOrgaoMatricula($siape) . " - " . tratarHTML($servidor); ?>" readonly/>
                <input type="hidden" name="siape" class="hdnsiape" value="<?= tratarHTML($siape); ?>"/>
                <input type="hidden" name="tipo_solicitacao" value="<?= tratarHTML($_SESSION['tipo_solicitacao']); ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3" id="date">
                <font class="ft_13_003">Data:</font>
                <div class='input-group date'>
                    <input type='text' name="dia" placeholder="dd/mm/aaaa" id="dia" autocomplete="off" class="form-control"/>
                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6">
                <font class="ft_13_003">Horário Inicial:</font>
                <input type='text' name="hora_ini" placeholder="hh:mm" id="hora_ini" class="form-control" style="width:90px;"/>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6">
                <font class="ft_13_003">Horário Final:</font>
                <input type='text' name="hora_fim" placeholder="hh:mm" id="hora_fim" class="form-control" style="width:90px;"/>
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
                    <a class="btn btn-danger btn-block" id="btn-voltar"
                       href="javascript:window.location.replace('/sisref/<?= tratarHTML($_SESSION['voltar_nivel_2']); ?>')" role="button">
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
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
