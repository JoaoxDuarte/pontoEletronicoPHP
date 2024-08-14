<?php

include_once("config.php");
include_once("comparecimento_gecc_funcoes.php");

verifica_permissao("sRH ou Chefia");

$ano = date('Y');

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

    $oDados = CarregaRegistrosGECC($id);
    $servidor             = $oDados->nome;
    $start_date           = databarra($oDados->data_ini);
    $end_date             = databarra($oDados->data_fim);
    $start_hora           = $oDados->hora_ini;
    $end_hora             = $oDados->hora_fim;
    $horas                = $oDados->horas;
    $acrescimo_autorizado = $oDados->acrescimo_autorizado;
    $setor                = $oDados->setor;
}


$oForm = new formPadrao();
$oForm->setSubTitulo("Gratificação por Encargo de Curso ou Concurso - Registro");
$oForm->setJS('js/fc_data.js');
$oForm->setJSDialogProcessando();
$oForm->setJSDatePicker();
$oForm->setJS('js/jquery.mask.min.js');
$oForm->setJS('js/phpjs.js');
$oForm->setJS('comparecimento_gecc_registro.js');

// Topo do formulário

$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<script>
$(document).ready(function () {

    $('#date .input-group.date').datepicker({
        format: "dd/mm/yyyy",
        startDate: "<?= '01/11/'.($ano-1); ?>",
        endDate: "<?= "31/12/".$ano; ?>",
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

    $('#date2 .input-group.date').datepicker({
        format: "dd/mm/yyyy",
        startDate: "<?= '01/11/'.($ano-1); ?>",
        endDate: "<?= "31/12/".$ano; ?>",
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
});
</script>

    <div class="portlet-body form">

        <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

        <form id="form1" name="form1" method="POST" onsubmit="javascript:return false;">
            <input type="hidden" name="siape"                value="<?= tratarHTML($siape); ?>">
            <input type="hidden" name="id"                   value="<?= tratarHTML($id); ?>">
            <input type="hidden" name="setor"                value="<?= tratarHTML($setor); ?>">
            <input type="hidden" name="data_inicio_anterior" value="<?= tratarHTML($start_date); ?>">
            <input type="hidden" name="data_fim_anterior"    value="<?= tratarHTML($end_date); ?>">
            <input type="hidden" name="dados"                value="">

            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6">
                    <font class="ft_13_003">Servidor:</font>
                    &nbsp;<input type="text" id="nome" name="nome" class="form-control" value="<?= tratarHTML($servidor); ?>" readonly>
                </div>
            </div>
            <div class="row margin-10">
                <div class="col-lg-2 col-md-2 col-xs-2 col-sm-2" id="date">
                    <font class="ft_13_003">Data Inicial:</font>
                    <div class='input-group date' style="width:150px;">
                        <input type='text' name="data_ini" placeholder="dd/mm/aaaa" value="<?= tratarHTML($start_date); ?>" id="data_ini" class="form-control"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
                <div class="col-lg-2 col-md-2 col-xs-2 col-sm-2" id="date2">
                    <font class="ft_13_003">Data Final:</font>
                    <div class='input-group date' style="width:150px;">
                        <input type='text' name="data_fim" placeholder="dd/mm/aaaa" value="<?=tratarHTML($end_date); ?>" id="data_fim" class="form-control"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="row margin-10">
                <div class="col-lg-2 col-md-2 col-xs-2 col-sm-2">
                    <font class="ft_13_003">Horário Inicial:</font>
                    <input type='text' name="hora_ini" placeholder="hh:mm" id="hora_ini" class="form-control" style="width:90px;"/>
                </div>
                <div class="col-lg-2 col-md-2 col-xs-2 col-sm-2">
                    <font class="ft_13_003">Horário Final:</font>
                    <input type='text' name="hora_fim" placeholder="hh:mm" id="hora_fim" class="form-control" style="width:90px;"/>
                </div>
                <div class="col-lg-2 col-md-2 col-xs-2 col-sm-2">
                    <font class="ft_13_003">Total de Horas:</font>
                    <input type='text' name="horas" placeholder="hh:mm" id="horas" class="form-control" style="width:90px;" value="<?= tratarHTML($horas); ?>" readonly />
                </div>
            </div>
            <div class="row margin-10">
                <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3">
                    <font class="ft_13_003">Acréscimo Autorizado:</font>
                    <select class="form-control ciclos" name="acrescimo_autorizado" id="acrescimo_autorizado" style="width:120px;"/>
                        <option value='N' <?= (empty($acrescimo_autorizado) || $acrescimo_autorizado == 'N' ? "selected" : ""); ?>> Não </option>
                        <option value='S' <?= ($acrescimo_autorizado == 'S' ? "selected" : ""); ?>> Sim </option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-7 col-md-7 col-xs-7 col-sm-7">
                    <font class="ft_13_003">Documento:</font>
                    <input type='text' name="documento" id="documento" class="form-control" value="<?= $documento; ?>" />
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
                           href="javascript:window.location.replace('/sisref/comparecimento_gecc.php')" role="button">
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
