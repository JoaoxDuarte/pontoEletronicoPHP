<?php

include_once("config.php");
include_once("hora_extra_autorizacao_funcoes.php");

verifica_permissao("sRH ou Chefia");

$data_hoje = soma_dias_a_data(date('Y-m-d'), 1);

$ano = dataAno($data_hoje);
$mes = dataMes($data_hoje);
$dia = dataDia($data_hoje);

$sLotacao = $_SESSION["sLotacao"];
$mensagemUsuario = $_SESSION["mensagem-usuario"];

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    $siape = $_REQUEST['siape'];
    $id    = $_REQUEST['id'];
    $setor = $_REQUEST['setor'];

    $servidor = getNomeServidor($siape);
    $setor    = getSetorServidor($siape);
}
else
{
    $dados_get = explode("&", base64_decode($dadosorigem));
    $siape     = explode("=", $dados_get[0])[1];
    $id        = explode("=", $dados_get[1])[1];

    $oDados = CarregaRegistrosHoraExtra($id);
    $servidor             = $oDados->nome;
    $start_date           = databarra($oDados->data_inicio);
    $end_date             = databarra($oDados->data_fim);
    $horas                = $oDados->horas;
    $acrescimo_autorizado = $oDados->acrescimo_autorizado;
    $documento            = $oDados->documento;
    $setor                = $oDados->setor;
}


$oForm = new formPadrao();
$oForm->setSubTitulo("Autorização de Serviços Extraordinários - Registro");
$oForm->setJSSelect2();
$oForm->setJSDatePicker();
$oForm->setJS('js/fc_data.js');
$oForm->setJS('js/jquery.mask.min.js');

// Topo do formulï¿½rio

$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<style>
.bootstrap-datetimepicker-widget {
position: inherit;
z-index:100000 !important;
}
</style>
<script>
$(document).ready(function () {

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
        startDate: "<?= $dia.'/'.$mes.'/'.$ano; ?>",
        endDate: "<?= '31/12/'.$ano; ?>",
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

    $('#horas').mask('00:00');

    $("#btn-salvar-registro").on('click',function (e)
    {
        e.preventDefault();

        showProcessandoAguarde();

        // dados
        var dados = $('#form1').serialize();

        console.log(dados);

        $.ajax({
            url: "hora_extra_autorizacao_registro_ajax.php",
            type: "POST",
            data: dados,
            dataType: "json"

        }).done(function(resultado) {
            console.log(resultado.mensagem + ' | ' + resultado.tipo);

            if (resultado.tipo == 'success')
            {
                $("[name='data_inicio']").val('');
                $("[name='data_fim']").val('');
                $("[name='horas']").val('');
                $("[name='acrescimo_autorizado']").val('N');
                $("[name='documento']").val('');
            }

            hideProcessandoAguarde();

            if (($("[name='id']").val() !== "") && (resultado.tipo == 'success'))
            {
                mostraMensagem(resultado.mensagem, resultado.tipo, "hora_extra_autorizacao.php");
            }
            else
            {
                mostraMensagem(resultado.mensagem, resultado.tipo);
            }

        }).fail(function(jqXHR, textStatus ) {
            console.log("Request failed: " + textStatus);
            hideProcessandoAguarde();
            mostraMensagem('Problema no processamento dos dados!', 'danger');

        }).always(function() {
            console.log("completou");
            hideProcessandoAguarde();

        });
    });
});
</script>

    <div class="portlet-body form">

        <form id="form1" name="form1" method="POST" onsubmit="return false;">
            <input type="hidden" value="<?= $siape; ?>" name="siape">
            <input type="hidden" value="<?= $id; ?>" name="id">
            <input type="hidden" value="<?= $setor; ?>" name="setor">
            <input type="hidden" value="<?= $start_date; ?>" name="data_inicio_anterior">
            <input type="hidden" value="<?= $end_date; ?>" name="data_fim_anterior">

            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6">
                    <font class="ft_13_003">Servidor:</font>
                    &nbsp;<input type="text" id="nome" name="nome" class="form-control" value="<?= tratarHTML($servidor); ?>" readonly>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6" id="dt-container">
                    <font class="ft_13_003">Data Inicial:</font>
                    <div class='input-group date'>
                        <input type='text' id="data_inicio" name="data_inicio" placeholder="dd/mm/aaaa" value="<?= tratarHTML($start_date); ?>" class="form-control" autocomplete="off"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6" id="dt-container">
                    <font class="ft_13_003">Data Final:</font>
                    <div class='input-group date'>
                        <input type='text' id="data_fim" name="data_fim" placeholder="dd/mm/aaaa" value="<?= tratarHTML($end_date); ?>" class="form-control" autocomplete="off"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-2 col-md-2 col-xs-2 col-sm-2">
                    <font class="ft_13_003">Total de Horas:</font>
                    <input type='text' name="horas" placeholder="hh:mm" id="horas" class="form-control" style="width:90px;" value="<?= tratarHTML($horas); ?>" />
                </div>
                <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3">
                    <font class="ft_13_003">Acréscimo Autorizado:</font>

                    <select class="form-control select2-single" name="acrescimo_autorizado" id="acrescimo_autorizado" style="width:20px;"/>
                        <option value='N' <?= ($acrescimo_autorizado != 'S' ? "selected" : ""); ?>> Não </option>
                        <option value='S' <?= ($acrescimo_autorizado == 'S' ? "selected" : ""); ?>> Sim </option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6">
                    <font class="ft_13_003">Documento:</font>
                    <input type='text' name="documento" id="documento" class="form-control" value="<?= tratarHTML($documento); ?>" />
                </div>
            </div>
            <div class="row">
                <br>
                <div class="form-group col-md-12 text-center">
                    <div class="col-md-2"></div>
                    <div class="col-md-2 col-xs-4 col-md-offset-2">
                        <a class="btn btn-success btn-block" id="btn-salvar-registro" role="button">
                            <span class="glyphicon glyphicon-ok"></span> Salvar
                        </a>
                    </div>
                    <div class="col-md-2 col-xs-4">
                        <a class="btn btn-danger btn-block" id="btn-voltar"
                           href="javascript:window.location.replace('/sisref/hora_extra_autorizacao.php')" role="button">
                            <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                        </a>
                    </div>
                    <div class="col-md-2"></div>
                </div>
            </div>
        </form>
    </div>

<?php
// Base do formulï¿½rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
